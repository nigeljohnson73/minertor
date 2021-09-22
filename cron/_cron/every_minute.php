<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");
// Check for header: "X-Appengine-Cron: true"
if (@$_SERVER ["SERVER_NAME"] != "localhost" && @$_SERVER ["HTTP_X_FORWARDED_FOR"] != "0.1.0.2") {
	logger ( LL_SYS, "I don't know who you are" );
	exit ();
}

DebugStore::tidyUp ();

// Remove any hung jobs
// TODO: should probably go into system tick
JobStore::tidyUp ();

// TODO: Move this to the system tick handling so it's called every 10 seconds... once it's tuned
transactionToBlock ();

InfoStore::getInstance ()->setInfo ( cronMinuteDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Minutely Housekeeing complete\n";

if (strtoupper ( InfoStore::getInstance ()->getInfo ( "switch_load_test_transactions", "DISABLED" ) ) == strtoupper ( switchEnabled () )) {
	InfoStore::getInstance ()->setInfo ( "switch_load_test_transactions", "DISABLED" );
	DebugStore::log ( "Starting test transaction load" );
	InfoStore::setBlockBusy ( "YES" );

	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_WRN );

	$test = transactionsPerBlock ();
	$c = 0;
	$le = false;
	global $demo_to_wallet;
	for($i = 0; $i < $test; $i ++) {
		$t = new Transaction ( coinbaseWalletId (), $demo_to_wallet, 1 / $test, minerRewardLabel () . " TESTING" );
		if ($t->sign ( coinbasePrivateKey () )) {
			if (TransactionStore::getInstance ()->insert ( $t->unload () )) {
				$c += 1;
			}
		}
		if (($i > 0) && ($i % 100 == 0)) {
			DebugStore::log ( "Test transaction load count: " . $i . "/" . $test );
		}
	}
	$logger->setLevel ( $ll );
	DebugStore::log ( "Complete: Loaded " . $c . " test transactions" );
	InfoStore::setBlockBusy ( "NO" );
}
?>