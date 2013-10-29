<?php 

require_once('newconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$tpn = $_criteria["tripno"]->get_criteria_value("VALUE");
$rbd = $_criteria["runningno"]->get_criteria_value("VALUE");
$veh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$dty = $_criteria["duty"]->get_criteria_value("VALUE");
$fromtime = $_criteria["fromtime"]->get_criteria_value("VALUE");
$totime = $_criteria["totime"]->get_criteria_value("VALUE");


for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
   //if ( $col->query_name == "pub_ttb_id" ) $col->attributes["column_display"] = "hide";
   //if ( $col->query_name == "scheduled_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "event_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operation_date" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "over_midnight" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operator_id" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "act_sched" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "arc_sched" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "service_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "view_today" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "alloc_vehcode" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "key" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "journey_from" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "act_start" ) $col->attributes["column_display"] = "show";
   if ( $col->query_name == "runningno" ) $col->attributes["column_display"] = "show";
   if ( $col->query_name == "trip_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_status" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "trip_status" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "real_lateness" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "lateness_min" ) $col->attributes["column_display"] = "show";
   if ( $col->query_name == "xlabel" ) $col->attributes["column_display"] = "show";

}
//define('SW_JQDEF_snapshotcurrentboards_row_changed_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_day_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_start_time_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_row_status_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_status_hidden', false);
define('SW_JQDEF_snapshotcurrentboards_act_start_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_row_changed_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_vehicle_id_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_timetable_id_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_ttkey_hidden', true);
define('SW_JQDEF_snapshotcurrentboards_duty_no_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_trip_no_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_trip_no_sorttype', "int");
define('SW_JQDEF_snapshotcurrentboards_operator_code_stype', "text");
define('SW_JQDEF_snapshotcurrentboards_lateness_sorttype', "int");
define('SW_JQDEF_snapshotcurrentboards_operator_code_searchtype', "string");
define('SW_JQDEF_snapshotcurrentboards_trip_no_stype', "text");
define('SW_JQDEF_snapshotcurrentboards_status_searchtype', "string");
define('SW_JQDEF_snapshotcurrentboards_status_stype', "text");
define('SW_JQDEF_snapshotcurrentboards_next_duty_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_next_duty_time_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_end_time_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_running_no_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_lateness_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_diversion_minihide', true);
define('SW_JQDEF_snapshotcurrentboards_scheduled_start_width', '200px');

//define('SW_JQDEF_snapshotcurrentboards_graph_xlabelcol', "act_veh");
//define('SW_JQDEF_snapshotcurrentboards_graph_plotcol1', "lateness");
//define('SW_JQDEF_snapshotcurrentboards_graph_legend1', "Lateness");
//define('SW_JQDEF_snapshotcurrentboards_graph_plottype1', "stackedbar");
?>
