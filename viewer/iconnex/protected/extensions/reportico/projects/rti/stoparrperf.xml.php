<?php

$dfrom = $_criteria["daterange"]->get_criteria_value("RANGE1");
$dto = $_criteria["daterange"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$loc = $_criteria["location"]->get_criteria_value("VALUE");
$ftm = $_criteria["fromTime"]->get_criteria_value("VALUE");
$ttm = $_criteria["toTime"]->get_criteria_value("VALUE");
$rn = false;
$dty = false;
$tp = false;

include "iconnexextra.class.php";


$iconnex = new iconnex_extra($_pdo);
$user = $iconnex->getUser();


if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex-> create_temp_times($ftm, $ttm) ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dfrom) ) return false;
//if ( !$iconnex->build_user_timetable($user, $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;

$sql="
SELECT day, timetable_journey.timetable_id, timetable_visit.location_id, 
  arrival_time, 
  departure_time,
  sequence, 
  timetable_journey.route_id,
  ext_timetable_id,
  running_no,
  etm_trip_no trip_no,
  duty_no
FROM timetable_journey 
join route_visibility ON timetable_journey.route_id = route_visibility.route_id
join timetable_visit on timetable_journey.timetable_id = timetable_visit.timetable_id
join t_days on timetable_visit.departure_date_id = t_days.date_id
join time_dimension on departure_time_id = time_dimension.time_id
WHERE timetable_visit.sequence < number_stops
AND hhmmss between $ftm and $ttm
AND usernm = '$user'
";
if ($loc)
    $sql .= "AND timetable_visit.location_id IN ( $loc )";
$sql .= "
INTO TEMP t_pubtime WITH NO LOG;
";
if ( !$iconnex->executeSQL($sql) )
    return false;



$sql = "
SELECT t_pubtime.day,
   timetable_journey_fact.timetable_id,
   timetable_journey_fact.driver_id,
   timetable_journey_fact.vehicle_id,
   timetable_journey_fact.actual_start,
timetable_visit_fact.journey_fact_id,
timetable_visit_fact.sequence,
timetable_visit_fact.location_id,
timetable_visit_fact.arrival_time_pub,
timetable_visit_fact.arrival_time,
timetable_visit_fact.arrival_status,
timetable_visit_fact.departure_time_pub,
timetable_visit_fact.departure_time,
timetable_visit_fact.departure_status,
timetable_visit_fact.departure_lateness
   FROM timetable_journey_fact, timetable_visit_fact, t_pubtime
   WHERE 1 = 1
   AND t_pubtime.day = date(timetable_journey_fact.actual_start)
   AND timetable_journey_fact.timetable_id = t_pubtime.timetable_id
   AND timetable_journey_fact.fact_id = timetable_visit_fact.journey_fact_id
   AND timetable_visit_fact.sequence = t_pubtime.sequence
AND arrival_status != 'C'
AND departure_status != 'C'
UNION
SELECT t_pubtime.day,
   timetable_journey_live.timetable_id,
   timetable_journey_live.driver_id,
   timetable_journey_live.vehicle_id,
   timetable_journey_live.actual_start,
timetable_visit_live.journey_fact_id,
timetable_visit_live.sequence,
timetable_visit_live.location_id,
timetable_visit_live.arrival_time_pub,
timetable_visit_live.arrival_time,
timetable_visit_live.arrival_status,
timetable_visit_live.departure_time_pub,
timetable_visit_live.departure_time,
timetable_visit_live.departure_status,
timetable_visit_live.departure_lateness
   FROM timetable_journey_live, timetable_visit_live, t_pubtime
   WHERE 1 = 1
   AND t_pubtime.day = date(timetable_journey_live.actual_start)
   AND timetable_journey_live.timetable_id = t_pubtime.timetable_id
   AND timetable_journey_live.fact_id = timetable_visit_live.journey_fact_id
   AND timetable_visit_live.sequence = t_pubtime.sequence
AND departure_status != 'C'
AND arrival_status != 'C'
AND departure_status != 'C'
INTO TEMP t_acttime WITH NO LOG;
";
if ( !$iconnex->executeSQL($sql) )
    return false;


$sql="
CREATE INDEX ix_tatb ON t_acttime ( day, timetable_id );
";
if ( !$iconnex->executeSQL($sql) )
    return false;

define('SW_CONFIG_stoparrperf_http_no_data_message', 'No Departures Found. Bus Stop is not a timing point with scheduled departures.');


?>
