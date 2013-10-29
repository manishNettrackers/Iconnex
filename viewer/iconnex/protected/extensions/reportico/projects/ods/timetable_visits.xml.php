<?php 

for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
   if ( $col->query_name == "actual_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "hhmmss" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "ymd" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "trip_no" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "running_no" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "duty_no" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "route_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "duration" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "number_stops" ) $col->attributes["column_display"] = "hide";

   if ( $col->query_name == "ymd" ) $col->attributes["column_title"] = "date";
   if ( $col->query_name == "hhmmss" ) $col->attributes["column_title"] = "time";
   if ( $col->query_name == "timetable_id" ) $col->attributes["column_display"] = "hide";
}

define('SW_JQDEF_timetable_hidden', true);



?>
