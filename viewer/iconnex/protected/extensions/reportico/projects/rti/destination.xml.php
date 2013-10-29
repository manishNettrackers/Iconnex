<?php


for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
   if ( $col->query_name == "operator_id" ) $col->attributes["column_display"] = "hide";
}

define('SW_JQDEF_destination_dest_id_hidden', true);
define('SW_JQDEF_destination_dest_code_hidden', true);
define('SW_JQDEF_destination_trip_no_width', '120px');
define('SW_JQDEF_destination_start_time_hidden', false);
define('SW_JQDEF_destination_running_no_sorttype', 'text');
define('SW_JQDEF_destination_running_no_stype', 'text');
define('SW_JQDEF_destination_date_stype', 'text');
define('SW_JQDEF_destination_route_code_stype', 'text');
define('SW_JQDEF_destination_destination_code_editable', true);
define('SW_JQDEF_destination_sign_30_char_editable', true);
define('SW_JQDEF_destination_long_name_editable', true);
define('SW_JQDEF_destination_short_name_editable', true);

define('SW_JQDEF_destination_custom_button1', "Help");
define('SW_JQDEF_destination_custom_button1_ids', "dest_id");
define('SW_JQDEF_destination_custom_button2', "Help");
define('SW_JQDEF_destination_custom_button2_ids', "dest_id");
define('SW_JQDEF_destination_custom_button3', "Help");
define('SW_JQDEF_destination_custom_button3_ids', "dest_id");



?>
