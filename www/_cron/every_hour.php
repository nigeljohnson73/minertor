<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");
// Check for header: "X-Appengine-Cron: true"
if (@$_SERVER ["SERVER_NAME"] != "localhost" && @$_SERVER ["HTTP_X_FORWARDED_FOR"] != "0.1.0.2") {
	logger ( LL_SYS, "I don't know who you are" );
	exit ();
}

// TODO: re-enable these after fixcing them
UserStore::tidyUp ();
UserStore::requestRevalidations ();

InfoStore::getInstance ()->setInfo ( cronHourDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Hourly Housekeeing complete\n";
?>