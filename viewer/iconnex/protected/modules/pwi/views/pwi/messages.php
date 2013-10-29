#!/usr/local/bin/php -q
<?php
set_include_path("../../../lib");
require_once("common.php");
global $conn;

date_default_timezone_set("Europe/London");

function messages($stop)
{
    $rid = false;

    // Open a connection to the database.
    if (!$conn = db_connect())
    {
        echo "Failed to connect to database\n";
        die;
    }

    $sql = "select message_text
from dcd_message, dcd_message_loc, location, display_point
where dcd_message_loc.build_id = display_point.build_id
and location.location_id = display_point.location_id
and location.location_code = \"" . $stop
. "\" and dcd_message.message_id = dcd_message_loc.message_id
and dcd_message_loc.display_flag = 1
and CURRENT between dcd_message_loc.display_time and dcd_message_loc.expiry_time";


    if (!($rid = executePDOQuery($sql, $conn)))
    {
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    $i = 0;
    while (true)
    {
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        if ($i++ == 0)
            echo "<messages>\n";
        echo "  <msg>" . trim($row["message_text"]) . "</msg>\n";
    }
    if ($i > 0)
        echo "</messages>\n";
}

if ($argc < 1)
    exit -1;

messages($argv[1]);
?>

