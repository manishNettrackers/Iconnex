<?php 

for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
   if ( $col->query_name == "actual_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "ymd" ) $col->attributes["column_title"] = "date";
   if ( $col->query_name == "hhmmss" ) $col->attributes["column_title"] = "time";
   if ( $col->query_name == "timetable_id" ) $col->attributes["column_display"] = "hide";
}

define('SW_JQDEF_timetable_start_time_hidden', true);
define('SW_JQDEF_timetable_running_no_sorttype', 'text');
define('SW_JQDEF_timetable_running_no_stype', 'text');
define('SW_JQDEF_timetable_date_stype', 'text');
define('SW_JQDEF_timetable_route_code_stype', 'text');



?>
