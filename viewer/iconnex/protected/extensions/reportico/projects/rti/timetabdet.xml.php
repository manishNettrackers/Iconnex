<?php


include "iconnexextra.class.php";

$iconnex = new iconnex_extra($_pdo);

$user = $iconnex->getUser();


$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$ftm = $_criteria["fromTime"]->get_criteria_value("VALUE");
$ttm = $_criteria["toTime"]->get_criteria_value("VALUE");
$rn = $_criteria["runningno"]->get_criteria_value("VALUE");
$dty = $_criteria["duty"]->get_criteria_value("VALUE");
$tp = $_criteria["tripno"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dfdy = substr($dto, 1,2);
$dfmn = substr($dto, 4,2);
$dfyr = substr($dto, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex-> create_temp_times($ftm, $ttm) ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dto) ) return false;
if ( !$iconnex->build_user_timetable($user, $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;

for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
   if ( $col->query_name == "actual_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "ymd" ) $col->attributes["column_title"] = "date";
   if ( $col->query_name == "hhmmss" ) $col->attributes["column_title"] = "time";
   if ( $col->query_name == "timetable_id" ) $col->attributes["column_display"] = "hide";
}

define('SW_JQDEF_timetabrange_operator_code_width', '40px');
define('SW_JQDEF_timetabrange_trip_no_width', '120px');
define('SW_JQDEF_timetabrange_start_time_hidden', false);
define('SW_JQDEF_timetabrange_running_no_sorttype', 'text');
define('SW_JQDEF_timetabrange_running_no_stype', 'text');
define('SW_JQDEF_timetabrange_date_stype', 'text');
define('SW_JQDEF_timetabrange_route_code_stype', 'text');



?>
