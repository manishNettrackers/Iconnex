<?php

include "odsconnector.php";


$dow = $_criteria["dow"]->get_criteria_value("VALUE");
$veh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$sub = $_criteria["suburb"]->get_criteria_value("VALUE");
$rd = $_criteria["road"]->get_criteria_value("VALUE");
$timefrom = $_criteria["timefrom"]->get_criteria_value("VALUE");
$timeto = $_criteria["timeto"]->get_criteria_value("VALUE");
$date = $_criteria["date"]->get_criteria_value("VALUE");
$dr1 = $_criteria["date"]->get_criteria_value("RANGE1");
$dr2 = $_criteria["date"]->get_criteria_value("RANGE2");
$grouping = $_criteria["grouping"]->get_criteria_value("VALUE");

//$drc1 = substr($dr1, 7,4)."-".substr($dr1, 4,2)."-".substr($dr1,1,2);
//$drc2 = substr($dr2, 7,4)."-".substr($dr2, 4,2)."-".substr($dr2,1,2);
//$dsr1 = substr($dr1, 9,2)."-".substr($dr1, 6,2)."-".substr($dr1,1,4);
//$dsr2 = substr($dr2, 9,2)."-".substr($dr2, 6,2)."-".substr($dr2,1,4);


$ods = new odsconnector($_pdo);

$sql = "CREATE TEMPORARY TABLE t_results
(
  geohash CHAR(20),
  ymd CHAR(10),
  hhmmss CHAR(8),
  hour_no CHAR(3),
  dow_no CHAR(3),
  dow_name CHAR(10),
  addr_suburb VARCHAR(30),
  addr_road VARCHAR(30),
  speed_mph DECIMAL(7,2),
  latitude DECIMAL(12,5),
  longitude DECIMAL(12,5)
)";

$ret = $ods->executeSQL($sql);

$daycol = "ymd";
$hrcol = "hour_no";
$dowcol = "dow_no";
$downamecol = "dow_name";

if ( preg_match ("/1/", $grouping ) )
{
    $hrcol = "\"Any\"";
}

if ( preg_match ("/2/", $grouping ) )
{
    $daycol = "\"Any\"";
    $dowcol = "\"Any\"";
    $downamecol = "\"Any\"";
}

//echo date("H:i:s")."<BR>";
if ( $ret )
{
    $sql = "INSERT INTO t_results
  ( geohash, ymd, hhmmss, hour_no, dow_no, dow_name, addr_suburb, addr_road, speed_mph, latitude, longitude )
SELECT geohash, $daycol, hhmmss, $hrcol, $dowcol, $downamecol, addr_suburb, addr_road, speed_mph, latitude, longitude
FROM gps_fact, vehicle_dimension, gis_dimension, date_dimension, time_dimension 
WHERE 1 = 1                                            
AND gps_fact.vehicle_id = vehicle_dimension.vehicle_id
AND gps_fact.gis_id = gis_dimension.gis_id
AND gps_fact.date_id =  date_dimension.date_id
AND gps_fact.time_id = time_dimension.time_id";

    if ( $veh ) $sql .= " AND vehicle_dimension.vehicle_id IN ( $veh )";
    if ( $sub ) $sql .= " AND addr_suburb IN ( $sub )";
    if ( $rd ) $sql .= " AND addr_road IN ( $rd )";
    if ( $dow ) $sql .= " AND dow_name IN ( $dow )";

    $sql .= " AND hhmmss >= $timefrom";
    $sql .= " AND hhmmss <= $timeto";
    $sql .= " AND ymd  $date";
$ret = $ods->executeSQL($sql);

//echo date("H:i:s")."<BR>";
$sql = "CREATE INDEX ix_t_res ON t_results ( geohash, addr_suburb );";
$ret = $ods->executeSQL($sql);

//echo date("H:i:s")."<BR>";
}




?>
