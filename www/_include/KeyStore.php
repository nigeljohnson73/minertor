<?php
include_once(__DIR__ . "/FileStore.php");

use Elliptic\EC;

class KeyStore extends FileStore {

	protected function __construct() {
		logger(LL_DBG, "KeyStore::KeyStore()");

		parent::__construct("KeyStore");
	}

	protected static function genFilename($email) {
		if (usingGae()) {
			return hash("sha1", strtolower(getDataNamespace() . "_" . $email));
		} else {
			return hash("sha1", strtolower($email));
		}
	}

	public static function getKeys($email) {
		// $store = self::getInstance ();
		logger(LL_DBG, "KeyStore::getKeys('" . $email . "')");
		$filename = self::genFilename($email);
		$json = parent::getContents($filename);
		if (!$json || strlen($json) == 0) {
			logger(LL_DBG, "KeyStore::getKeys('" . $email . "'): No keys exist, creating new set");
			$kp = self::genKeyPair();
			self::putKeys($email, $kp->public, $kp->private);
			return $kp;
		}
		return json_decode($json);
	}

	public static function putKeys($email, $public, $private) {
		// return KeyStore::getInstance ()->_putKeys ( $email, $public, $private );
		logger(LL_DBG, "KeyStore::putKeys('" . $email . "', ...)");
		$filename = self::genFilename($email);
		$value = self::genKeyPair($public, $private);
		$contents = json_encode($value);
		return parent::putContents($filename, $contents);
	}

	public static function deleteKeys($email) {
		logger(LL_DBG, "KeyStore::deleteKeys('" . $email . "')");
		$filename = self::genFilename($email);
		return parent::delete($filename);
	}

	public static function genKeyPair($pubKey = null, $privKey = null) {
		$value = new StdClass();
		if ($pubKey === null || $privKey === null) {
			$ec = new EC('secp256k1');
			$key = $ec->genKeyPair();
			$value->public = $key->getPublic('hex');
			$value->private = $key->getPrivate('hex');
		} else {
			$value->public = $pubKey;
			$value->private = $privKey;
		}
		return $value;
	}

	// Returns the signature hash you cn validate with the public key
	public static function sign($hash, $privKey) {
		$ec = new EC('secp256k1');
		$sk = $ec->keyFromPrivate($privKey, 'hex');
		return $sk->sign($hash);
	}
}

// function __testKeyStore() {
// global $logger;
// $ll = $logger->getLevel ();
// $logger->setLevel ( LL_DBG );

// $store = KeyStore::getInstance ();
// $email = "test@testy.com";
// $pubKey = "publick_key";
// $privKey = "privat_key";
// $store->putKeys ( $email, $pubKey, $privKey );

// $keys = $store->getKeys ( $email );
// print_r ( $keys );
// $store->deleteKeys ( $email );

// $logger->setLevel ( $ll );
// }
function __getOverlordKeys($namespace = null) {
	if (usingGae()) {
		if (!class_exists("OverrideKeyStore")) {

			class OverrideKeyStore extends KeyStore {

				public function __construct($namespace) {
					logger(LL_DBG, "KeyStore::KeyStore()");
					$this->storage = null;
					$this->bucket = null;
					$this->bucket_name = strtolower($namespace . "_KeyStore");
					$this->options = [];
					$this->init();
				}
			}
		}

		global $logger;
		global $data_namespace;

		$dns = getDataNamespace();
		$data_namespace = $namespace ?? $dns;
		$ll = $logger->getLevel();
		$logger->setLevel(LL_DBG);

		$store = new OverrideKeyStore($data_namespace);
		echo "Keys for '" . coinbaseName() . "'\n";

		$keys = $store->getKeys(coinbaseName());
		print_r($keys);

		$logger->setLevel($ll);
		$data_namespace = $dns;
	} else {
		$keys = KeyStore::getKeys(coinbaseName());
		echo "Keys for '" . coinbaseName() . "'\n";
		print_r($keys);
	}
}
