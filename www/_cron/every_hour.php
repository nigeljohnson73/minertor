<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");
// Check for header: "X-Appengine-Cron: true"

// UserStore::tidyUp ();
// UserStore::requestRevalidations ();

InfoStore::getInstance ()->setInfo ( cronHourDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Hourly Housekeeing complete\n";
?>
