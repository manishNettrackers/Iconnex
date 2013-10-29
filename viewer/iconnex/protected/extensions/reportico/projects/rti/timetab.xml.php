<?php


include "iconnexextra.class.php";

$iconnex = new iconnex_extra($_pdo);

$user = $iconnex->getUser();


$dfrom = $_criteria["date"]->get_criteria_value("VALUE");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$ftm = $_criteria["fromTime"]->get_criteria_value("VALUE");
$ttm = $_criteria["toTime"]->get_criteria_value("VALUE");
$rn = $_criteria["runningno"]->get_criteria_value("VALUE");
$dty = $_criteria["duty"]->get_criteria_value("VALUE");
$tp = $_criteria["trip"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex-> create_temp_times($ftm, $ttm) ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dfrom) ) return false;
if ( !$iconnex->build_user_timetable($user, $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;

?>
