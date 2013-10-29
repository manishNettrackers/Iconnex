<?php

include("projects/rti/iconnex.php");
    $iconnex = new iconnex($_pdo);

$iconnex->debug = 1;
    if (!$iconnex->executeSQL("SET ISOLATION TO DIRTY READ"))
       return;

    $sql ="
SELECT vehicle_id, message_time, gpslat, -gpslong gpslong
FROM vehicle a, unit_status b
WHERE a.build_id = b.build_id
AND message_time > CURRENT - 10 UNITS MINUTE
UNION
SELECT vehicle_id, extend(CURRENT, year to second) , 0,0
FROM vehicle a
WHERE vehicle_code = 'AUT'
INTO TEMP t_vehpos WITH  NO LOG
";
    // Also cursors may be used for example :-
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
AND departure_time < CURRENT
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
a.rpat_orderby next_rpat
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

    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.vehicle_id, b.schedule_id, a.location_id, a.rpat_orderby, arrival_status, departure_status, 
arrival_time, departure_time, departure_time_pub, (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
route_code, runningno, f.duty_no, f.trip_no, operator_code, h.operator_id, g.service_id, e.route_id, start_code, trip_status, employee_id, l.direction,
g.description service_name, location_code,
( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
- ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
c.location_code next_location,
c.description next_name
FROM active_rt_loc a, active_rt b, t_maxtrip d, route e, publish_tt f, service g, operator h, service_patt i, route_pattern l, location c
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND d.schedule_id = a.schedule_id
AND d.rpat_orderby = a.rpat_orderby
AND b.pub_ttb_id = f.pub_ttb_id
AND departure_status != 'C'
AND f.service_id = g.service_id 
AND g.service_id = i.service_id
AND i.rpat_orderby = 1
AND a.location_id = c.location_id
AND g.route_id = e.route_id
AND e.operator_id = h.operator_id
AND g.route_id = l.route_id
AND i.rpat_id = l.rpat_id
INTO TEMP t_trips WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

?>




