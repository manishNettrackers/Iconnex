<?php

$crt = $_criteria["results"]->get_criteria_value("VALUE", false);
//var_dump($crt);

$title = $crt;

//switch ( $crt )
//{
	//case "maxspeed": $title = "Max Speed"; break;
	//case "avgspeed": $title = "Avg Speed"; break;
	//case "economy": $title = "Fuel Economy"; break;
	//default: $title = "Max Speed";
//}
//echo $title;


for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
   if ( $col->query_name == "output_metric" ) $col->attributes["column_title"] = $title;
}

?>

