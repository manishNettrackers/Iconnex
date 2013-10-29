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
$tp = $_criteria["trip"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dfdy = substr($dto, 1,2);
$dfmn = substr($dto, 4,2);
$dfyr = substr($dto, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );

// if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex-> create_temp_times($ftm, $ttm) ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dto) ) return false;
if ( !$iconnex->build_user_timetable($user, $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;

for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
   if ( $col->query_name == "actual_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "ymd" ) $col->attributes["column_title"] = "date";
   if ( $col->query_name == "hhmmss" ) $col->attributes["column_title"] = "time";
   if ( $col->query_name == "route_code" ) $col->attributes["column_title"] = "Route";
   if ( $col->query_name == "etm_trip_no" ) $col->attributes["column_title"] = "Trip";
   if ( $col->query_name == "trip_no" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "timetable_id" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "dest_long" ) $col->attributes["column_title"] = "Destination";
}

define('SW_JQDEF_timetabrange_operator_code_width', '40px');
define('SW_JQDEF_timetabrange_trip_no_width', '120px');
define('SW_JQDEF_timetabrange_start_time_hidden', false);
define('SW_JQDEF_timetabrange_trip_no_hidden', true);
define('SW_JQDEF_timetabrange_running_no_sorttype', 'text');
define('SW_JQDEF_timetabrange_running_no_stype', 'text');
define('SW_JQDEF_timetabrange_date_stype', 'text');
define('SW_JQDEF_timetabrange_route_code_stype', 'text');

define('SW_JQDEF_timetabrange_duty_no_minihide', true);
define('SW_JQDEF_timetabrange_trip_no_minihide', true);
define('SW_JQDEF_timetabrange_service_code_minihide', true);
define('SW_JQDEF_timetabrange_operator_code_minihide', true);
define('SW_JQDEF_timetabrange_event_code_minihide', true);
define('SW_JQDEF_timetabrange_runningno_minihide', true);
define('SW_JQDEF_timetabrange_running_board_minihide', true);
define('SW_JQDEF_timetabrange_operating_minihide', true);

?>
