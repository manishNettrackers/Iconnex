<?php
$crt = $_criteria["route"]->get_criteria_value("VALUE");
$cop = $_criteria["operator"]->get_criteria_value("VALUE");
$cra = $_criteria["routearea"]->get_criteria_value("VALUE");
$eq = $_criteria["equipped"]->get_criteria_value("VALUE");

$ar = preg_split("/,/", $eq);

// -------------------------------------------------------
// Find locations on selected routes
// -------------------------------------------------------
if ( $crt || $cop || true )
{
$sql =
"
SELECT UNIQUE location_id, route.route_id, route_code, operator.operator_id, operator_code
FROM service_patt, service, route, operator
WHERE service_patt.service_id = service.service_id
AND service.route_id  = route.route_id
AND route.operator_id  = operator.operator_id
AND TODAY BETWEEN wef_date AND wet_date
";

if ( $crt )$sql .= " AND route.route_id IN ($crt)";
if ( $cop )$sql .= " AND route.operator_id IN ($cop)";

$sql .= " INTO TEMP t_routeloc";

$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

$sql = "CREATE INDEX i_t_routeloc ON t_routeloc ( location_id );";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

}

if ( !get_stop_params($ds, "make") ) return;
if ( !get_stop_params($ds, "maxTextWidth") ) return;

// -------------------------------------------------------
// Extract List of shocks, bootups etc
// -------------------------------------------------------
$sql = "SELECT unit_build.build_id, unit_alert.message_type message_type, max(alert_time) last_alert,
count(*) alert_count 
FROM unit_build, display_point, unit_alert, outer message_type 
WHERE 1 = 1 
AND display_point.build_id = unit_build.build_id 
AND unit_alert.build_id = unit_build.build_id 
AND message_type.msg_type = unit_alert.message_type 
AND date(alert_time) BETWEEN TODAY - 7 AND TODAY
AND unit_alert.message_type IN ('476', '481', '494', '493' ) 
GROUP BY 1,2
INTO TEMP t_events";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

// -------------------------------------------------------
// Extract report locations
// ------------------------------------------------------
$sql = "SELECT l.location_id, location_code location_code, l.bay_no bay_no, l.description description, ra.route_area_code route_area_code, latitude_degrees latitude_degrees, latitude_minutes latitude_minutes, latitude_heading latitude_heading, longitude_degrees longitude_degrees, longitude_minutes longitude_minutes, longitude_heading longitude_heading, u.build_code build_code, us.message_time message_time, us.ip_address ip_address , t_stops_make.param_value make,
t_events1.last_alert last_impact, t_events1.alert_count impact_count, 
t_events2.last_alert last_bootup, t_events2.alert_count bootup_count ,
(INTERVAL(0) HOUR(4) TO HOUR + ( CURRENT - us.message_time )) || ''  last_active_hour,
(INTERVAL(0) DAY(4) TO DAY + ( CURRENT - us.message_time )) || ''  last_active_day
FROM location l,
     route_area ra,
     outer (display_point dp, unit_build u, t_stops_make, outer unit_status us, outer t_events  t_events1, outer t_events t_events2) 
WHERE 1 = 1  AND l.route_area_id = ra.route_area_id
  and l.location_id in ( select location_id from t_routeloc)
  and l.point_type = 'S'
  and l.location_id = dp.location_id
  and dp.display_type = 'B'
  and dp.build_id = u.build_id
  and t_stops_make.build_id = u.build_id
  and dp.build_id = us.build_id
  and dp.build_id = t_events1.build_id
  and t_events1.message_type = 476
  and dp.build_id = t_events2.build_id
    AND us.message_time > CURRENT - 200 UNITS DAY
  and t_events2.message_type = 113";

  //and u.build_code not matches '1001077*'  ";

if ( $cra ) $sql .= " AND ra.route_area_code IN ( $cra ) ";
if ( $cop || $crt )$sql .= " AND l.location_id IN (SELECT location_id FROM t_routeloc)";

$sql .=
" INTO TEMP t_locs";

$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

//Get tables of key stops values

// -------------------------------------------------------
// Filter equipped/non-equipped stops
// --------------------------------------------------------
$showeq = 1;
$shownoneq = 1;
if ( $eq && count($ar) == 1 )
{
   if ( $ar[0] == "'0'" )
      $shownoneq = 0;
   else
      $showeq = 0;
}

if ( !$showeq )
{
$sql = "DELETE FROM t_locs WHERE build_code IS NOT NULL";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

}

if ( !$shownoneq )
{
$sql = "DELETE FROM t_locs WHERE build_code IS NULL";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();
}

// -------------------------------------------------------
// Fetch the routes each location resides on
// --------------------------------------------------------
$sql = "CREATE TEMP TABLE t_loconrt ( location_id INTEGER, routes CHAR(40) );";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

$sql =
"
SELECT UNIQUE location_id, route_code
FROM route_pattern, route
WHERE route_pattern.route_id = route.route_id
AND location_id IN ( SELECT location_id FROM t_locs )
ORDER BY location_id";

$recordSet = $ds->Execute($sql)
   or die("$this->query_statement<br>Query failed : " . $_connection->ErrorMsg());

$lastid="";
$rtes="";
while (!$recordSet->EOF)
{
   $line = $recordSet->FetchRow();

   $locid = $line["location_id"];
   $rte = trim($line["route_code"]);

   if ( $lastid && $lastid != $locid )
   {
      $sql = "INSERT INTO t_loconrt VALUES ( $lastid, '$rtes');";
      $_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();
   }

   if ( !$lastid || $lastid != $locid )
      $rtes = "";

   if ( !$rtes )
      $rtes .= $rte;
   else
      $rtes .= "/".$rte;

   $lastid = $locid;
}

if ( $lastid )
{
   $sql = "INSERT INTO t_loconrt VALUES ( $lastid, '$rtes');";
   $_connection->Execute($sql) or
   print $sql." ".$_connection->ErrorMsg();
}


$sql = "CREATE INDEX i_t_loconrt ON t_loconrt ( location_id );";
$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

function get_stop_params( $in_conn, $tp )
{
$sql = "
select a.build_id,
a.build_code,
a.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d
where a.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and unit_type = 'BUSSTOP'
and param_value is not null
and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
and param_value != ''
and a.build_id in  ( select build_id from display_point )
INTO TEMP t_stops_$tp
;
";
if ( !$in_conn->Execute($sql))
{
	print $sql." ".$in_conn->ErrorMsg(); return false;
}

$sql = "
insert into t_stops_$tp
select unique
a.build_id,
a.build_code,
pa.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d,
unit_build pa
where 1 = 1
and a.build_parent = pa.build_id
and pa.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and param_value is not null
and param_value != ''
and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
and a.unit_type = 'BUSSTOP'
and a.build_id in  ( select build_id from display_point )
and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp )
";
if ( !$in_conn->Execute($sql))
{
	print $sql." ".$in_conn->ErrorMsg(); return false;
}


$sql = "
insert into t_stops_$tp
select unique
a.build_id,
a.build_code,
pa.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d,
unit_build pa, unit_build ppa
where 1 = 1
and a.build_parent = pa.build_id
and pa.build_parent = ppa.build_id
and ppa.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and param_value is not null
and param_value != ''
and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
and a.unit_type = 'BUSSTOP'
and a.build_id in  ( select build_id from display_point )
and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp );
";
if ( !$in_conn->Execute($sql))
{
	print $sql." ".$in_conn->ErrorMsg(); return false;
}


return true;
}

?>
