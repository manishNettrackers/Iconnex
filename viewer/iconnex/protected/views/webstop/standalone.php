<?php

set_include_path("protected/config");
require_once("common.php");
include "webstop.php";
include "messages.php";

global $conn;

date_default_timezone_set("Europe/London");


if (webstop())
    webstop_display();
messages();
?>

