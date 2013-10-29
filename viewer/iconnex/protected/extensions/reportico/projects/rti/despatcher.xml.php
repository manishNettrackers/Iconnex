<?php

$op = $_criteria["operator"]->get_criteria_value("VALUE");
$rt = $_criteria["route"]->get_criteria_value("VALUE");


include("projects/rti/iconnexextra.class.php");
    $iconnex = new iconnex_extra($_pdo);

//$iconnex->debug = 1;

    if (!$iconnex->executeSQL("SET ISOLATION TO DIRTY READ"))
       return;

    $user = session_request_item("user", false );
    if ( !$user )
        $user = "admin";

    $sql ="
SELECT vehicle_id, message_time, gpslat, -gpslong gpslong, 'On Route            ' vehicle_status, message_type, route_status
FROM vehicle_visibility a, unit_status b, unit_status_rt c
WHERE a.build_id = b.build_id
AND a.build_id = c.build_id
AND a.usernm = '$user'
AND message_time > CURRENT - 1 UNITS DAY";

if ( $op ) $sql .= " and a.operator_id in ( $op )";

$sql .= " UNION
SELECT vehicle_id, extend(CURRENT, year to second) , 0,0, 'Scheduled' vehicle_status, 0 message_type, 'U' route_status
FROM vehicle a
WHERE vehicle_code = 'AUT'";

//if ( $op ) $sql .= " and a.operator_id in ( $op )";

$sql .= " INTO TEMP t_vehpos WITH  NO LOG
";
    if (!$iconnex->executeSQL($sql))
       return;


    $sql = "UPDATE t_vehpos SET vehicle_status = 'Not Tracking' WHERE vehicle_status != 'Scheduled' and route_status NOT IN ( 'R' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Waiting for Start' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'P' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Stuck' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'S' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Idle' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'W' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Off Line' WHERE vehicle_status != 'Scheduled' and message_time < CURRENT - 10 UNITS MINUTE";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.vehicle_id, b.schedule_id, max(rpat_orderby) rpat_orderby
FROM active_rt_loc a, active_rt b
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND departure_time < CURRENT + 10 UNITS MINUTE
AND ( 
	departure_status IN ( 'A', 'P' ) OR  
	( rpat_orderby = 1 AND departure_status != 'C' ) OR
	( start_code = 'AUT' AND departure_time <= CURRENT )
)
AND start_code IN ( 'REAL', 'AUT' )
GROUP BY 1,2
INTO TEMP t_maxtrip WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.schedule_id, max(rpat_orderby) rpat_orderby
FROM active_rt_loc a, active_rt b
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND ( ( rpat_orderby = 1 AND departure_time > CURRENT ) OR departure_time < CURRENT )
AND departure_time_pub IS NOT NULL
AND departure_time IS NOT NULL
AND date(departure_time) > '31/12/1899'
AND departure_status != 'C'
AND start_code IN ( 'REAL', 'AUT' )
GROUP BY 1
INTO TEMP t_maxlateness WITH NO LOG
";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT a.schedule_id, a.departure_time next_departure, a.departure_time_pub next_departure_time_pub,
(( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub )) || '' next_lateness,
a.rpat_orderby next_rpat, a.arrival_status, a.departure_status, 'RUNNING' start_status
FROM active_rt_loc a, t_maxlateness b, location c
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND a.rpat_orderby = b.rpat_orderby
AND departure_time_pub IS NOT NULL
AND departure_time IS NOT NULL
AND departure_status != 'C'
AND date(departure_time) > '31/12/1899'
AND date(departure_time_pub) > '31/12/1899'
AND a.location_id = c.location_id
INTO TEMP t_latenesses WITH NO LOG
";


//$iconnex->trace("<BR>start2");
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
UPDATE t_latenesses SET start_status = 'NOTLEFT'
WHERE next_rpat = 1 
AND departure_status = 'E'
";

    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
UPDATE t_latenesses SET start_status = 'LATEDEP'
WHERE next_rpat = 1 
AND departure_status = 'E'
AND next_departure > next_departure_time_pub + 2 UNITS MINUTE
";

    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.vehicle_id, b.schedule_id, a.location_id, a.rpat_orderby, arrival_status, departure_status, 
arrival_time, departure_time, departure_time_pub, (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
route_code, runningno, f.duty_no, f.trip_no, operator_code, h.operator_id operator_id, g.service_id, e.route_id, start_code, trip_status, b.employee_id,
( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
- ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
c.location_code next_location,
c.description next_name,
employee_code, fullname
FROM active_rt_loc a, active_rt b, t_maxtrip d, route_visibility e, publish_tt f, service g, operator h, location c, outer employee w
WHERE 1 = 1
AND departure_time > CURRENT - 1 UNITS YEAR
AND a.schedule_id = b.schedule_id
AND d.schedule_id = a.schedule_id
AND d.rpat_orderby = a.rpat_orderby
AND b.pub_ttb_id = f.pub_ttb_id
AND departure_status != 'C'
AND f.service_id = g.service_id
AND g.route_id = e.route_id
AND usernm = '$user'
AND w.employee_id = b.employee_id
AND e.operator_id = h.operator_id
AND a.location_id = c.location_id";

if ( $op )
    $sql .= " and e.operator_id in ( $op )";
if ( $rt )
    $sql .= " and e.route_id in ( $rt )";

$sql .= " INTO TEMP t_trips WITH NO LOG ";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;


    $sql ="
SELECT t_vehpos.vehicle_id, vehicle_status
FROM t_vehpos, vehicle_visibility vehicle
WHERE t_vehpos.vehicle_id NOT IN ( SELECT vehicle_id FROM t_trips )
AND t_vehpos.vehicle_id = vehicle.vehicle_id
AND vehicle_code != 'AUT'
AND usernm = '$user'
INTO TEMP t_notin WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
INSERT INTO t_trips ( vehicle_id, route_code, lateness, operator_code, operator_id )
SELECT t_notin.vehicle_id, 'N/A', 0, operator.operator_code, operator.operator_id
FROM t_notin,  vehicle_visibility vehicle, operator
where t_notin.vehicle_id = vehicle.vehicle_id
and vehicle.operator_id = operator.operator_id
and usernm = '$user'
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

$golap = "'Tooltips=Vehicle Code;Lateness Band;Route,'.
        'Type=Icon,'.
        'HotspotX=32,'.
        'HotspotY=42,'.
        'KeyField=Key,'.
        'ClickLink=ajax/vehicledetailspopup.php?tripid=<<Schedule Id>>&vehicle=<<Vehicle Code>>&operator=<<Operator Code>>,'.
        'Filters=Route;Vehicle Code;Vehicle Status;Lateness Band;Trip Type;Trip Status;Driver Name,'.
        'FilterMoveAlerts=Lateness Band>Very Early/Very Late,'.
        'RenderType=Despatcher,'.
        'RenderElements=Operator Code;Route;Vehicle Code;Trip Type;Trip Status;Lateness;Vehicle Status,'.
        'Metric=Lateness,'.
        'PosColor=#ff0000,'.
        'Negcolor=#0000ff,'.
        'ZeroColor=#00ff00,'.
        'MetricRangeLower=-300,'.
        'MetricRangeUpper=300'"; 
//echo $golap;
$this->add_assignment("golap", $golap, false );

for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
   if ( $col->query_name == "view_trip" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "view_today" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "next_location" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "next_name" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "start_status" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "employee_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "last_stop_lateness" ) $col->attributes["column_display"] = "hide";
}


?>



