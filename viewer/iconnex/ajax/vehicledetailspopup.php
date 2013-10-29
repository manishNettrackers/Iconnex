<?php

/*
    vehicledetailspopup.php

    Provides info box about a vehicle. Typically generates the content of
    a popup window when user clicks a bus on a map or grid
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
$veh = $iconnex->get_request_item("vehicle", "UNK");
$trip = $iconnex->get_request_item("tripid", "UNK");
$user = $iconnex->get_request_item("user", "guest");

if ( $veh == "Scheduled" )
    $veh = "AUT";

if (!$iconnex->setDirtyRead())
    return;

    $sql ="
SELECT vehicle_id, message_time, gpslat, -gpslong gpslong, 'On Route            ' vehicle_status, message_type, route_status, 
etm_time, etm_route, etm_duty_no, etm_trip_no, etm_direction, etm_status, message_type.description etm_info
FROM vehicle_visibility a, unit_status b, unit_status_rt c, operator o, outer message_type
WHERE a.build_id = b.build_id
AND c.route_action = message_type.msg_type
AND a.build_id = c.build_id
AND a.usernm = '$user'
AND a.vehicle_code = '$veh'
AND o.operator_code = '$op'
AND a.operator_id = o.operator_id
AND message_time > CURRENT - 1 UNITS DAY";

$sql .= " UNION
SELECT vehicle_id, extend(CURRENT, year to second) , 0,0, 'Timetabled' vehicle_status, 0 message_type, 'U' route_status, 
CURRENT YEAR TO SECOND,'','','','','', ''
FROM vehicle a
WHERE vehicle_code = 'AUT'
AND a.vehicle_code = '$veh'
"
;

$sql .= " INTO TEMP t_vehpos WITH  NO LOG
";
    if (!$iconnex->executeSQL($sql))
       return;

    //if (!$iconnex->dumpSQL("SELECT * FROM t_vehpos"))
       //return;
    $sql = "UPDATE t_vehpos SET vehicle_status = 'Not Tracking' WHERE route_status NOT IN ( 'R' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Waiting for Start' WHERE route_status IN ( 'P' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Stuck' WHERE route_status IN ( 'S' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Idle' WHERE route_status IN ( 'W' )";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql = "UPDATE t_vehpos SET vehicle_status = 'Off Line' WHERE message_time < CURRENT - 10 UNITS MINUTE";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.vehicle_id, b.fact_id, max(sequence) sequence
FROM timetable_visit_live a, timetable_journey_live b,  t_vehpos c
WHERE 1 = 1
AND a.journey_fact_id = b.fact_id
AND b.vehicle_id = c.vehicle_id
AND departure_time < CURRENT + 10 UNITS MINUTE
AND ( 
	departure_status IN ( 'A', 'P' ) OR  
	( sequence = 1 AND departure_status != 'C' ) OR
	( start_code = 'AUT' AND departure_time <= CURRENT )
)
AND start_code IN ( 'REAL', 'AUT' )
AND b.fact_id = $trip
GROUP BY 1,2
INTO TEMP t_maxtrip WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.fact_id, max(sequence) sequence
FROM timetable_visit_live a, timetable_journey_live b, t_vehpos c
WHERE 1 = 1
AND a.journey_fact_id = b.fact_id
AND ( ( sequence = 1 AND departure_time > CURRENT ) OR departure_time < CURRENT )
AND departure_time_pub IS NOT NULL
AND departure_time IS NOT NULL
AND b.vehicle_id = c.vehicle_id
AND date(departure_time) > '31/12/1899'
AND departure_status != 'C'
AND start_code IN ( 'REAL', 'AUT' )
GROUP BY 1
INTO TEMP t_maxlateness WITH NO LOG
";
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT a.fact_id, a.departure_time next_departure, a.departure_time_pub next_departure_time_pub,
(( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub )) || '' next_lateness,
a.sequence next_rpat, a.arrival_status, a.departure_status, 'RUNNING' start_status
FROM timetable_visit_live a, t_maxlateness b, location c
WHERE 1 = 1
AND a.fact_id = b.fact_id
AND a.sequence = b.sequence
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
SELECT b.vehicle_id, b.fact_id, a.location_id, a.sequence, arrival_status, departure_status, 
arrival_time, departure_time, departure_time_pub, (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
tj.route_code, runningno, f.duty_no, f.trip_no, f.etm_trip_no, operator_code, h.operator_id operator_id, g.service_id, e.route_id, start_code, trip_status, b.driver_id,
( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
- ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
c.location_code next_location,
c.description next_name,
employee_code, fullname
FROM timetable_visit_live a, timetable_journey_live b, timetable_journey tj, t_maxtrip d, route_visibility e, publish_tt f, service g, operator h, location c, outer employee w
WHERE 1 = 1
AND departure_time > CURRENT - 1 UNITS YEAR
AND a.journey_fact_id = b.fact_id
AND d.fact_id = a.journey_fact_id
AND d.sequence = a.sequence
AND b.timetable_id = tj.timetable_id
AND tj.ext_timetable_id = f.pub_ttb_id
AND departure_status != 'C'
AND f.service_id = g.service_id
AND g.route_id = e.route_id
AND usernm = '$user'
AND w.employee_id = b.driver_id
AND e.operator_id = h.operator_id
AND a.location_id = c.location_id";

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
SELECT t_notin.vehicle_id, 'Unknown', 0, operator.operator_code, operator.operator_id
FROM t_notin,  vehicle_visibility vehicle, operator
where t_notin.vehicle_id = vehicle.vehicle_id
and vehicle.operator_id = operator.operator_id
and usernm = '$user'
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

$sql = "
SELECT route_code route_code, gpslat latitude, gpslong longitude, runningno runningno, vehicle_code vehicle_code, lateness lateness, duty_no duty_no, t_trips.trip_no trip_no, departure_time departure_time, departure_time_pub departure_time_pub, t_trips.route_id route_id, operator_code operator_code, next_departure_time_pub next_departure_time_pub, next_departure next_departure, next_lateness next_lateness, next_latitude next_latitude, next_longitude next_longitude, t_trips.fact_id schedule_id, next_location next_location, next_name next_name, trip_status trip_status, vehicle_status vehicle_status, t_vehpos.message_time last_time, t_vehpos.message_type last_message, route_status route_status, start_status start_status, employee_code employee_code, fullname driver_name ,
etm_time, etm_route, etm_duty_no, t_trips.etm_trip_no, etm_direction, etm_status, etm_info
FROM vehicle, t_vehpos, t_trips, OUTER t_latenesses 
WHERE 1 = 1                        
AND vehicle.vehicle_id = t_vehpos.vehicle_id
AND vehicle.vehicle_id = t_trips.vehicle_id
AND t_trips.fact_id = t_latenesses.fact_id
";
    
    // Also cursors may be used for example :-
    if (!($stmt = $iconnex->executeSQL($sql)))
       return;

echo "<TABLE width=\"100%\">";
while ( $row = $iconnex->fetch() )
{
    $op = trim($row["operator_code"]);
    $vh = trim($row["vehicle_code"]);
    $sch = trim($row["schedule_id"]);
    $lateness = trim($row["lateness"]);
    $trp = trim($row["trip_no"]);
    $duty = trim($row["duty_no"]);
    if ( $duty == "NODUTY" )
        $duty = "None";
    $etrp = trim($row["etm_trip_no"]);
    if ( $etrp )
        $trp = $etrp;

    if ( $lateness < 0 )
    {
        $lateness = -$lateness;
        $lthr = floor($lateness / 3600 );
        $ltmn = floor (  ( $lateness - ( $lthr * 3600 ) ) / 60 ) ;
        $ltsc = $lateness - ( $lthr * 3600 ) - ( $ltmn * 60 );
        $lat = sprintf("%02d:%02d:%02d", $lthr, $ltmn, $ltsc);
    }
    else
    {
        $lthr = floor($lateness / 3600 );
        $ltmn = floor (  ( $lateness - ( $lthr * 3600 ) ) / 60 ) ;
        $ltsc = $lateness - ( $lthr * 3600 ) - ( $ltmn * 60 );
        $lat = sprintf("%02d:%02d:%02d", $lthr, $ltmn, $ltsc);
    }

    info_cell("Vehicle", $vh." (".$op.")", "color: #eeeeee; background-color: #222222;");
    info_cell("Route", $row["route_code"]);
    info_cell("Board", $row["runningno"]);
    info_cell("Duty", $duty);
    info_cell("Trip", $trp);
    //info_cell("Trip Type", $row["trip_type"]);
    info_cell("Status", $row["vehicle_status"]);
    info_cell("Last Seen", $row["last_time"]);
    //info_cell("Last Message", $row["last_message"]);
    info_cell("Lateness", $lat);
    info_cell("Last ETM", $row["etm_time"]);
    info_cell("ETM Details", "Rt:".trim($row["etm_route"])." Dt:".trim($row["etm_duty_no"])." Tr:". trim($row["etm_trip_no"]));
    if ( trim($row["etm_status"]) == "V" )
        info_cell("ETM OK", trim($row["etm_info"]));
    else
        info_cell("ETM Invalid ", trim($row["etm_info"]));
    info_cell(" ", " ");

    if ( isset ( $row["employee_code"] ) && $row["employee_code"]  )
        info_cell("Driver", $row["employee_code"]." - ".$row["driver_name"]);
    info_cell("View Trip", popuplink("odsactrtesched.xml","MANUAL_triptype=REAL&MANUAL_operator=${op}&MANUAL_schedule=${sch}"));
    if ( $vh != "AUT" )
        info_cell("View Today", popuplink("arcrte.xml","MANUAL_vehicle=${vh}"));
    info_cell("Next Stop", $row["next_name"]);
    //echo "<PRE>";
    //var_dump($row);
    //echo "</PRE>";
    break;
}
echo "</TABLE>";


function popuplink($report, $params)
{
    $x = '<a class="expandwindow" href="protected/extensions/reportico/run.php?xmlin='.$report.'&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&clear_session=1&project=rti&'.$params.'" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
    return $x;
}

function info_cell($label, $value, $style = "")
{
    echo "<TR>";
    echo '<TD style="padding-top: 0; padding-bottom: 0px; font-weight: bold; width: 50%; '.$style.'">';
    echo $label;
    echo '</TD>';
    echo '<TD style="padding-top: 0; padding-bottom: 0; width: 50%; '.$style.'">';
    echo $value;
    echo '</TD>';
    echo "</TR>";
}

?>



