<?php

/*
    locationdetailspopup.php

    Provides info box about a location. Typically generates the content of
    a popup window when user clicks a bus stop on a map or grid
*/

set_include_path(get_include_path().":../../../../lib:../../../../lib/classes");

include ("config.php");
include ("rtpiconnector.class.php");

// Create connection to RTPI database
$iconnex = new rtpiconnector();

if ( !$iconnex->connect(ICX_RTPI_DB_CONN_STRING_PDO, ICX_RTPI_DB_USER, ICX_RTPI_DB_PASSWORD) )
{
    echo "Failed to connect to Real Time Database\n";
    die;
}

global $_debug ;
$_debug = false;

$op = $iconnex->get_request_item("operator", "UNK");
$loc = $iconnex->get_request_item("location", "UNK");
$loc = preg_replace("/_.*/", "", $loc);
$user = $iconnex->get_request_item("user", "guest");

if (!$iconnex->setDirtyRead())
    return;

$crt = false;
$cop = false;
$cra = false;
$eq = false;

$ar = preg_split("/,/", $eq);

// -------------------------------------------------------
// Find locations on selected routes
// -------------------------------------------------------
$user = $iconnex->get_request_item("user", false );
if ( !$user )
{
	$user = "admin";
}

if ( $crt || $cop || true )
{
    $sql =
"
SELECT UNIQUE location.location_id, route.route_id, route_code, operator.operator_id, operator_code
FROM service_patt, service, route_visibility route, operator, location
WHERE service_patt.service_id = service.service_id
AND service.route_id  = route.route_id
AND route.operator_id  = operator.operator_id
AND location.location_id = service_patt.location_id
AND location.location_code = '$loc'
AND TODAY BETWEEN wef_date AND wet_date
AND usernm = '$user'
";

if ( $crt )$sql .= " AND route.route_id IN ($crt)";
if ( $cop )$sql .= " AND route.operator_id IN ($cop)";

$sql .= " INTO TEMP t_routeloc";

if (!($stmt = $iconnex->executeSQL($sql)))
    return;

$sql = "CREATE INDEX i_t_routeloc ON t_routeloc ( location_id );";
if (!($stmt = $iconnex->executeSQL($sql)))
    return;
}

if ( !get_stop_params($iconnex, "make") ) return;
if ( !get_stop_params($iconnex, "maxTextWidth") ) return;

// -------------------------------------------------------
// Extract List of shocks, bootups etc
// -------------------------------------------------------
$sql = "SELECT unit_build.build_id, unit_alert.message_type message_type, max(alert_time) last_alert,
count(*) alert_count 
FROM unit_build, display_point, unit_alert, outer message_type 
WHERE 1 = 1 
AND display_point.build_id = unit_build.build_id 
AND display_point.location_id in ( select location_id from t_routeloc )
AND unit_alert.build_id = unit_build.build_id 
AND message_type.msg_type = unit_alert.message_type 
AND date(alert_time) BETWEEN TODAY - 7 AND TODAY
AND unit_alert.message_type IN ('476', '481', '494', '493' ) 
GROUP BY 1,2
INTO TEMP t_events";
if (!($stmt = $iconnex->executeSQL($sql)))
    return;

// -------------------------------------------------------
// Extract report locations
// ------------------------------------------------------
$sql = "SELECT l.location_id, location_code location_code, l.bay_no bay_no, l.description description, ra.route_area_code route_area_code, latitude_degrees latitude_degrees, latitude_minutes latitude_minutes, latitude_heading latitude_heading, longitude_degrees longitude_degrees, longitude_minutes longitude_minutes, longitude_heading longitude_heading, u.build_code build_code, us.message_time message_time, us.sim_no sim_no, us.ip_address ip_address , t_stops_make.param_value make,
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


if (!($stmt = $iconnex->executeSQL($sql)))
    return;

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
if (!($stmt = $iconnex->executeSQL($sql)))
    return;

}

if ( !$shownoneq )
{
$sql = "DELETE FROM t_locs WHERE build_code IS NULL";
if (!($stmt = $iconnex->executeSQL($sql)))
    return;
}

// -------------------------------------------------------
// Fetch the routes each location resides on
// --------------------------------------------------------
$sql = "CREATE TEMP TABLE t_loconrt ( location_id INTEGER, routes CHAR(40) );";
if (!($stmt = $iconnex->executeSQL($sql)))
    return;

$sql =
"
SELECT UNIQUE location_id, route_code
FROM route_pattern, route
WHERE route_pattern.route_id = route.route_id
AND location_id IN ( SELECT location_id FROM t_locs )
ORDER BY location_id";

if (!($stmt = $iconnex->executeSQL($sql)))
    return;

$lastid="";
$rtes="";
while ( $line = $iconnex->fetch() )
{
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
    if (!($stmt = $iconnex->executeSQL($sql)))
        return;
}


$sql = "CREATE INDEX i_t_loconrt ON t_loconrt ( location_id );";
if (!($stmt = $iconnex->executeSQL($sql)))
        return;

function get_stop_params( $iconnex, $tp )
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
if (!($stmt = $iconnex->executeSQL($sql)))
        return false;

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
if (!($stmt = $iconnex->executeSQL($sql)))
        return false;


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
if (!($stmt = $iconnex->executeSQL($sql)))
        return false;


return true;
}

$sql = 
"
SELECT location_code location_code, bay_no bay_no, description description, route_area_code route_area_code, latitude_degrees latitude_degrees, latitude_minutes latitude_minutes, latitude_heading latitude_heading, longitude_degrees longitude_degrees, longitude_minutes longitude_minutes, longitude_heading longitude_heading, build_code build_code, message_time message_time, ip_address ip_address, route_code route, make make, last_impact last_impact, impact_count impact_count, last_bootup last_bootup, bootup_count bootup_count, last_active_hour last_active_hour, last_active_day last_active_day, operator_code operator_code, routes routes , sim_no
FROM t_routeloc,  t_locs left join t_loconrt on t_locs.location_id = t_loconrt.location_id 
WHERE 1 = 1                        
AND t_locs.location_id = t_routeloc.location_id  
ORDER BY  location_code 
";

// Also cursors may be used for example :-
if (!($stmt = $iconnex->executeSQL($sql)))
    return;

echo "<TABLE width=\"100%\">";
while ( $row = $iconnex->fetch() )
{
    $op = trim($row["operator_code"]);


  info_cell("Location Code", $row["location_code"]);
  info_cell("Name", $row["description"]);
  info_cell("Bay", $row["bay_no"]);
  info_cell("Inventory Id", $row["build_code"]);
  info_cell("Last Message", $row["message_time"]);
  info_cell("Last IP", $row["ip_address"]);
  info_cell("Make", $row["make"]);
  info_cell("SIM", $row["sim_no"]);
  info_cell("Last Impact", trim($row["last_impact"])." / ".$row["impact_count"]);
  info_cell(" ", " ");
  info_cell("Services", $row["routes"]);

  info_cell("View Timetable", popuplink("stoparrperf.xml","MANUAL_location=${loc}"));
  info_cell("Live Arrivals", rawpopuplink("index.php?r=webstop/ajax&locations=${loc}", "webstopwindow"));
  //info_cell("Send Message", popuplink("stopmessages.xml","MANUAL_location=${loc}", "PREPARE"));
  break;
}
echo "</TABLE>";


function rawpopuplink($link, $class = "expandwindow")
{
    $x = '<a class="'.$class.'" href="'.$link.'" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
    return $x;
}

function popuplink($report, $params, $mode = "EXECUTE")
{
    $x = '<a class="expandwindow" href="protected/extensions/reportico/run.php?xmlin='.$report.'&execute_mode='.$mode.'&target_format=HTML&target_show_body=1&clear_session=1&project=rti&'.$params.'" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
    return $x;
}

function info_cell($label, $value, $style = "")
{
    echo "<TR>";
    echo '<TD style="padding-top: 0; padding-bottom: 0px; font-weight: bold; width: 50%; '.$style.'">';
    echo $label;
    echo '</TD>';
    echo '<TD style="padding-top: 0; padding-bottom: 0; width: 50%; '.$style.'">';
    echo trim($value);
    echo '</TD>';
    echo "</TR>";
}

?>



