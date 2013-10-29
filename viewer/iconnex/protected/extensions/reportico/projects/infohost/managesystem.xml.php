<?php

require_once('iconnex.php');

$ac = $_criteria["action"]->get_criteria_value("VALUE");
$ac = (int)preg_replace("/'/","", $ac);

$rtp = $_criteria["rtpimode"]->get_criteria_value("VALUE");
$rtp = (int)preg_replace("/'/","", $rtp);

$iconnex = new iconnex($_pdo);

$mode = "";
$modert = "";
$stat = 1;
$iconnex->debug = 0;
if ( $ac == "3" )
{
    $mode = "disabled";
    $sql = "UPDATE system_key SET key_value = 'CLEAR' WHERE key_code = 'CLEARSYSTE'";
    $stat = $iconnex->executeSQL($sql ) ;
}

if ( $ac == "1" )
{
    $mode = "needing refresh";
    $sql = "UPDATE system_key SET key_value = 'REFRESH' WHERE key_code = 'CLEARSYSTE'";
    $stat = $iconnex->executeSQL($sql ) ;
}

if ( $ac == "2" )
{
    $mode = "needing prediction retransmissions";
    $sql = "UPDATE dcd_countdown SET time_last_sent = NULL";
    $stat = $iconnex->executeSQL($sql ) ;
}

if ( $rtp == "3" )
{
    $modert = "sending both real time and scheduled predictions";
    $sql = "UPDATE system_key SET key_value = 'FULL' WHERE key_code = 'PREDTYPE'";
    $stat = $iconnex->executeSQL($sql ) ;
}

if ( $rtp == "1" )
{
    $modert = "sending only scheduled predictions";
    $sql = "UPDATE system_key SET key_value = 'SCHONLY' WHERE key_code = 'PREDTYPE'";
    $stat = $iconnex->executeSQL($sql ) ;
}

if ( $rtp == "2" )
{
    $modert = "sending only real time predictions";
    $sql = "UPDATE system_key SET key_value = 'RTPIONLY' WHERE key_code = 'PREDTYPE'";
    $stat = $iconnex->executeSQL($sql ) ;
}


if ( !$stat )
{
    trigger_error( "No action was taken - please select an option", E_USER_ERROR); 
}
else 
{
    if ( $ac || $ac == "0" ) handle_debug("System flagged as $mode succesfully", SW_DEBUG_NONE);
    if ( $rtp  || $rtp == "0") handle_debug("System flagged as $modert succesfully", SW_DEBUG_NONE);
}



?>
