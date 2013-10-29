<?php
	ob_start();
?>
<?php
    date_default_timezone_set(@date_default_timezone_get());

    //include_once ("../../../../../../lib/config.php" );
	require_once("config.php");


	error_reporting(E_ALL);

	ini_set("memory_limit","800M");
	require_once('reportico.php');
	$a = new reportico();
	$a->allow_maintain = "FULL";
	$a->allow_debug = true;
	$a->embedded_report = true;
	$a->execute();
?>
<?php
// print out footer information
	ob_end_flush();
?>
