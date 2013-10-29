<?php 

$dfrom = $_criteria["date"]->get_criteria_value("VALUE");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$ftm = $_criteria["fromTime"]->get_criteria_value("VALUE");
$ttm = $_criteria["toTime"]->get_criteria_value("VALUE");
$rn = $_criteria["runningno"]->get_criteria_value("VALUE");
$dty = $_criteria["duty"]->get_criteria_value("VALUE");
$tp = $_criteria["trip"]->get_criteria_value("VALUE");

require_once('iconnex.php');
$iconnex = new iconnex($_pdo);

$user = $iconnex->getUser();

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dfrom) ) return false;
if ( !$iconnex->build_user_timetable($user, $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;


?>
