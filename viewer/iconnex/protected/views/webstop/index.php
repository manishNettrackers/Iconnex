<?php
$this->breadcrumbs=array(
	'Webstop',
);?>


<?php

set_include_path("../../../lib");
require_once("common.php");
include "webstop.php";

global $conn;

date_default_timezone_set("Europe/London");


if (webstop())
    webstop_display();

?>

