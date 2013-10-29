<?php

//include "iconnex.php";
include "odsconnector.php";


$engine = new odsconnector($_pdo);

$vh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$rt = $_criteria["suburb"]->get_criteria_value("VALUE");
$rd = $_criteria["road"]->get_criteria_value("VALUE");
$dt = $_criteria["date"]->get_criteria_value("VALUE");
$tf = $_criteria["timefrom"]->get_criteria_value("VALUE");
$tt = $_criteria["timeto"]->get_criteria_value("VALUE");
$dr = $_criteria["driver"]->get_criteria_value("VALUE");
$dr = str_replace("'", "", $dr);

if ( !$dr  && $reporttitle == "Driver Summary")
{
    trigger_error ( "You must specify a driver id" );
    return;
}

$dfyr = substr($dt, 10, 4);
$dfmn = substr($dt, 15, 2);
$dfdy = substr($dt, 18, 2);

$dtyr = substr($dt, 27, 4);
$dtmn = substr($dt, 32, 2);
$dtdy = substr($dt, 35, 2);

$ifrom = mktime(0, 0, 0, $dfmn, $dfdy, $dfyr);
$ito = mktime(0, 0, 0, $dtmn, $dtdy, $dtyr);

$sql = "CREATE TEMPORARY TABLE t_paesa (
    driver_id INTEGER,
    employee_code CHAR(8),
    employee_name CHAR(30),
    avg_fuel_economy DECIMAL(7,3),
    distance_travelled INTEGER,
    max_accel DECIMAL(7,2),
    max_decel DECIMAL(7,2),
    avg_rpm INTEGER,
    avg_speed INTEGER,
    max_speed INTEGER);";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_paesa (
SELECT driver_dimension.driver_id, driver_dimension.employee_code,
driver_dimension.fullname,
avg(fuel_economy), sum(distance_travelled), max(max_accel), max(max_decel), avg(avg_rpm), avg(avg_speed), max(max_speed)
FROM vehicle_dimension, gis_dimension, date_dimension, time_dimension
JOIN telem_paesa_fact
LEFT JOIN driver_dimension on (driver_dimension.driver_id = telem_paesa_fact.driver_id) 
WHERE 1 = 1                                                    
AND telem_paesa_fact.vehicle_id = vehicle_dimension.vehicle_id
AND telem_paesa_fact.gis_id = gis_dimension.gis_id
AND telem_paesa_fact.date_id =  date_dimension.date_id
AND telem_paesa_fact.time_id = time_dimension.time_id
AND telem_paesa_fact.driver_id IS NOT NULL";

//if ($dr && strlen($dr) > 0)
    //$sql .= " AND driver_dimension.driver_id IN ($dr)";

$sql .= " GROUP BY driver_dimension.employee_code);";

$engine->executeSQL($sql);

$sql = "CREATE TEMPORARY TABLE t_paesb (
  driver_id INTEGER,
  total_ignition_time DECIMAL(7,2),
  avg_fuel_economy_per_trip DECIMAL(7,3),
  avg_distance_travelled_per_trip INTEGER,
  max_accel_per_trip DECIMAL(7,2),
  max_decel_per_trip DECIMAL(7,2),
  avg_rpm_per_trip INTEGER,
  avg_speed_per_trip INTEGER,
  max_speed_per_trip INTEGER);";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_paesb (
SELECT driver_dimension.driver_id,
sum(trip_time),
avg(fuel_economy),
avg(distance_travelled),
max(max_accel),
max(max_decel),
avg(avg_rpm),
avg(avg_speed),
max(max_speed)
FROM vehicle_dimension, gis_dimension, date_dimension, time_dimension
JOIN telem_paesb_fact
LEFT JOIN driver_dimension on (driver_dimension.driver_id = telem_paesb_fact.driver_id) 
WHERE 1 = 1                                                    
AND telem_paesb_fact.vehicle_id = vehicle_dimension.vehicle_id
AND telem_paesb_fact.gis_id = gis_dimension.gis_id
AND telem_paesb_fact.date_id =  date_dimension.date_id
AND telem_paesb_fact.time_id = time_dimension.time_id
AND telem_paesb_fact.driver_id IS NOT NULL";

//if ($dr && strlen($dr) > 0)
    //$sql .= " AND driver_dimension.driver_id IN ($dr)";

$sql .= " GROUP BY driver_dimension.employee_code);";
$engine->executeSQL($sql);

$sql = "CREATE TEMPORARY TABLE t_paese (
  driver_id INTEGER,
  trip_time INTEGER,
  idle_time INTEGER,
  harsh_brake INTEGER,
  heavy_accel INTEGER,
  coasting INTEGER
);";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_paese (
SELECT driver_dimension.driver_id,
sum(trip_time),
sum(idle_time),
sum(harsh_brake),
sum(heavy_accel),
sum(coasting)
FROM vehicle_dimension, gis_dimension, date_dimension, time_dimension
JOIN telem_paese_fact
LEFT JOIN driver_dimension on (driver_dimension.driver_id = telem_paese_fact.driver_id) 
WHERE 1 = 1                                                    
AND telem_paese_fact.vehicle_id = vehicle_dimension.vehicle_id
AND telem_paese_fact.gis_id = gis_dimension.gis_id
AND telem_paese_fact.date_id =  date_dimension.date_id
AND telem_paese_fact.time_id = time_dimension.time_id
AND telem_paese_fact.driver_id IS NOT NULL";

//if ($dr && strlen($dr) > 0)
    //$sql .= " AND driver_dimension.driver_id IN ($dr)";

$sql .= " GROUP BY driver_dimension.employee_code);";
$engine->executeSQL($sql);

$sql = "CREATE TEMPORARY TABLE t_paest (
  driver_id INTEGER,
  event_id INTEGER,
  count INTEGER,
  total_duration INTEGER
);";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_paest (
SELECT driver_dimension.driver_id, event_id, count(*), sum(duration)
FROM vehicle_dimension, gis_dimension, date_dimension, time_dimension
JOIN telem_paest_fact
LEFT JOIN driver_dimension on (driver_dimension.driver_id = telem_paest_fact.driver_id) 
WHERE 1 = 1                                                    
AND telem_paest_fact.vehicle_id = vehicle_dimension.vehicle_id
AND telem_paest_fact.gis_id = gis_dimension.gis_id
AND telem_paest_fact.date_id =  date_dimension.date_id
AND telem_paest_fact.time_id = time_dimension.time_id";

//if ($dr && strlen($dr) > 0)
    //$sql .= " AND driver_dimension.driver_id IN ($dr)";

$sql .= " GROUP BY driver_dimension.employee_code, event_id);";

$engine->executeSQL($sql);

$sql = "CREATE TEMPORARY TABLE t_events (
driver_id INTEGER,
heavy_foot INTEGER,
heavy_foot_duration INTEGER,
harsh_brake INTEGER,
harsh_brake_duration INTEGER,
excessive_idling INTEGER,
excessive_idling_duration INTEGER
);";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_events
(SELECT driver_id, 0, 0, 0, 0, 0, 0 FROM driver_dimension);";
$engine->executeSQL($sql);

$sql = "UPDATE t_events
    CROSS JOIN (
    select driver_id, count, total_duration
    FROM t_paest
    WHERE t_paest.event_id = 1
    ) AS t_join
SET harsh_brake = t_join.count, harsh_brake_duration = t_join.total_duration
WHERE t_events.driver_id = t_join.driver_id;";
$engine->executeSQL($sql);

$sql = "UPDATE t_events
    CROSS JOIN (
    select driver_id, count, total_duration
    FROM t_paest
    WHERE t_paest.event_id = 6
    ) AS t_join
SET heavy_foot = t_join.count, heavy_foot_duration = t_join.total_duration
WHERE t_events.driver_id = t_join.driver_id;";
$engine->executeSQL($sql);

$sql = "UPDATE t_events
    CROSS JOIN (
    select driver_id, count, total_duration
    FROM t_paest
    WHERE t_paest.event_id = 8
    ) AS t_join
SET excessive_idling = t_join.count, excessive_idling_duration = t_join.total_duration
WHERE t_events.driver_id = t_join.driver_id;";
$engine->executeSQL($sql);

$sql = "CREATE TEMPORARY TABLE t_driver_extremes_tmp (
max_max_speed decimal(7,2),
min_max_speed decimal(7,2),
avg_max_speed decimal(7,2),
max_avg_speed decimal(7,2),
min_avg_speed decimal(7,2),
avg_avg_speed decimal(7,2),
max_avg_fuel_economy decimal(7,2),
min_avg_fuel_economy decimal(7,2),
avg_avg_fuel_economy decimal(7,2),
max_harsh_brake decimal(7,2),
min_harsh_brake decimal(7,2),
avg_harsh_brake decimal(7,2),
max_heavy_accel decimal(7,2),
min_heavy_accel decimal(7,2),
avg_heavy_accel decimal(7,2),
max_heavy_foot decimal(7,2),
min_heavy_foot decimal(7,2),
avg_heavy_foot decimal(7,2),
max_heavy_foot_duration decimal(7,2),
min_heavy_foot_duration decimal(7,2),
avg_heavy_foot_duration decimal(7,2),
min_avg_rpm decimal(7,2)
    );";
$engine->executeSQL($sql);



$sql = "CREATE TEMPORARY TABLE t_driver_summary_tmp (
driver_id INTEGER,
employee_code CHAR(10),
employee_name CHAR(30),
distance_travelled int(11),
total_ignition_time int(11),
trip_time decimal(7,2),
max_accel decimal(7,2),
max_decel decimal(7,2),
avg_rpm int(11),
avg_speed int(11),
max_speed int(11),
avg_fuel_economy_per_trip decimal(7,2),
avg_distance_travelled_per_trip decimal(7,2),
max_accel_per_trip decimal(7,2),
max_speed_per_trip decimal(7,2),
max_decel_per_trip decimal(7,2),
avg_rpm_per_trip decimal(7,2),
avg_speed_per_trip decimal(7,2),
avg_fuel_economy decimal(7,2),
idle_time int(11),
harsh_brake int(11),
heavy_accel int(11),
coasting int(11),
heavy_foot int(11),
heavy_foot_duration int(11),
excessive_idling int(11), 
excessive_idling_duration  int(11),
score integer
    );";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary_tmp SELECT t_paesa.driver_id driver_id, employee_code employee_code, employee_name employee_name, distance_travelled distance_travelled, round(total_ignition_time) total_ignition_time, trip_time trip_time, max_accel max_accel, max_decel max_decel, avg_rpm avg_rpm, round((avg_speed* 0.621371192), 2) avg_speed, round((max_speed * 0.621371192), 2) max_speed, avg_fuel_economy_per_trip avg_fuel_economy_per_trip, avg_distance_travelled_per_trip avg_distance_travelled_per_trip, max_accel_per_trip max_accel_per_trip, round((max_speed_per_trip * 0.621371192), 2) max_speed_per_trip, max_decel_per_trip max_decel_per_trip, avg_rpm_per_trip avg_rpm_per_trip, round((avg_speed_per_trip * 0.621371192), 2) avg_speed_per_trip, avg_fuel_economy avg_fuel_economy, idle_time idle_time, t_events.harsh_brake harsh_brake, heavy_accel heavy_accel, coasting coasting, heavy_foot heavy_foot, heavy_foot_duration heavy_foot_duration, excessive_idling excessive_idling, excessive_idling_duration excessive_idling_duration , 0 
FROM t_paesa, t_paesb, t_paese, t_events 
WHERE 1 = 1                             
AND t_paesb.driver_id = t_paesa.driver_id  
AND t_paese.driver_id = t_paesa.driver_id  
AND t_events.driver_id = t_paesa.driver_id";
$engine->executeSQL($sql);


$sql = "INSERT INTO t_driver_extremes_tmp
            SELECT MAX(max_speed), MIN(max_speed), avg(max_speed), 
            MAX(avg_speed), MIN(avg_speed), avg(avg_speed), 
            MAX(avg_fuel_economy), MIN(avg_fuel_economy), avg(avg_fuel_economy), 
            MAX(harsh_brake), MIN(harsh_brake), avg(harsh_brake), 
            MAX(heavy_accel), MIN(heavy_accel), avg(heavy_accel), 
            MAX(heavy_foot), MIN(heavy_foot), avg(heavy_foot), 
            MAX(heavy_foot_duration), MIN(heavy_foot_duration), avg(heavy_foot_duration) , min(avg_rpm)
            FROM t_driver_summary_tmp";
$engine->executeSQL($sql);

$extremes = $engine->executeSQL("SELECT * FROM t_driver_extremes_tmp");
foreach ( $extremes as $row )
{
   $extremes = $row;
   break;
}
$stats = $engine->executeSQL("SELECT * FROM t_driver_summary_tmp");
foreach ( $stats as $row )
{
    $score = 0;
    if ( $row["max_speed"] == $extremes["min_max_speed"] )
        $score++;

    if ( $row["avg_rpm"] == $extremes["min_avg_rpm"] )
        $score++;

    if ($row["distance_travelled"] > 0)
    {
        $heavyaccelperkm = $row["heavy_accel"] / ($row["distance_travelled"] / 1000);
        $heavyfootperkm = $row["heavy_foot"] / ($row["distance_travelled"] / 1000);
    }
    else
    {
        $heavyaccelperkm = 0;
        $heavyfootperkm = 0;
    }

    if ($heavyaccelperkm < 1.5)
        $score += 1;

    if ($heavyfootperkm < 1.5)
        $score += 1;

    $sql = "UPDATE t_driver_summary_tmp
        SET score = $score
        WHERE driver_id = ". $row["driver_id"];
    $engine->executeSQL($sql);
}

/*
$sql = "UPDATE t_driver_summary_tmp
        SET score = score + 1
        WHERE 
        (
            t_driver_summary_tmp.max_speed = 
                    ( SELECT MIN(max_speed) FROM t_driver_extremes_tmp )
        );";
$engine->executeSQL($sql);



$sql = "UPDATE t_driver_summary_tmp
        SET score = score + 1
        WHERE 
        (
            t_driver_summary_tmp.avg_fuel_economy = 
                    ( SELECT MAX(avg_fuel_economy) FROM t_driver_extremes_tmp )
        );";
$engine->executeSQL($sql);
*/

if ( $reporttitle == "Driver Summary")
{


$sql = "CREATE TEMPORARY TABLE t_driver_summary (
    display_order INTEGER,
    name CHAR(30),
    measure CHAR(20),
    your_value DECIMAL(7,3),
    worst_value DECIMAL(7,3),
    best_value DECIMAL(7,3),
    average_value DECIMAL(7,3),
    score int(11)
    );";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  1, 'Maximum Speed', max_speed, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  2, 'Average Speed', avg_speed, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  3, 'Fuel Economy', avg_fuel_economy, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  4, 'Harsh Brakes', harsh_brake, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  5, 'Heavy Accelerations', heavy_accel, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  6, 'Heavy Footdowns', heavy_foot, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  7, 'Heavy Foot Duration', heavy_foot_duration, 0
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, score )
    SELECT employee_name,  98, ' ', 99
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "INSERT INTO t_driver_summary ( name, display_order, measure, your_value, score )
    SELECT employee_name,  99, 'Driving Score', score, 99
    FROM t_driver_summary_tmp
    WHERE 1 = 1
    AND driver_id IN ($dr)
    ";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(max_speed) max_val, MIN(max_speed) min_val, avg(max_speed) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Maximum Speed'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(avg_speed) max_val, MIN(avg_speed) min_val, avg(avg_speed) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Average Speed'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(avg_fuel_economy) max_val, MIN(avg_fuel_economy) min_val, avg(avg_fuel_economy) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Fuel Economy'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(harsh_brake) max_val, MIN(harsh_brake) min_val, avg(harsh_brake) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Harsh Brakes '";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(heavy_accel) max_val, MIN(heavy_accel) min_val, avg(heavy_accel) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Heavy Accelerations'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(heavy_foot) max_val, MIN(heavy_foot) min_val, avg(heavy_foot) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Heavy Footdowns'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(heavy_foot_duration) max_val, MIN(heavy_foot_duration) min_val, avg(heavy_foot_duration) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.max_val,
                best_value = extremes.min_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Heavy Foot Duration'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT MAX(score) max_val, MIN(score) min_val, avg(score) avg_val FROM t_driver_summary_tmp ) As extremes
            SET worst_value = extremes.min_val,
                best_value = extremes.max_val ,
                average_value = extremes.avg_val
            WHERE measure = 'Driving Score'";
$engine->executeSQL($sql);

$sql = "UPDATE t_driver_summary  Cross Join (
            SELECT t_driver_summary_tmp.score FROM t_driver_summary_tmp WHERE driver_id IN (".$dr.") ) As t_driver_summary_tmp 
            SET t_driver_summary.score = t_driver_summary_tmp.score
            ";
$engine->executeSQL($sql);
}

?>
