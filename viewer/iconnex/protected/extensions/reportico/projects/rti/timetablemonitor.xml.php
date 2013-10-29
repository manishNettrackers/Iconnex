<?php 

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$tpn = $_criteria["tripno"]->get_criteria_value("VALUE");
$rbd = $_criteria["runningno"]->get_criteria_value("VALUE");
$veh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$dty = $_criteria["duty"]->get_criteria_value("VALUE");
$fromtime = $_criteria["fromtime"]->get_criteria_value("VALUE");
$totime = $_criteria["totime"]->get_criteria_value("VALUE");

$chkShowAllocated = $_criteria["showAllocated"]->get_criteria_value("VALUE");
$chkShowDutyChanges = $_criteria["showDutyChanges"]->get_criteria_value("VALUE");
$chkShowDiverted = $_criteria["showDiverted"]->get_criteria_value("VALUE");
$chkShowCancelled = $_criteria["showCancelled"]->get_criteria_value("VALUE");
$chkShowCurrent = $_criteria["showCurrent"]->get_criteria_value("VALUE");
$chkShowLateJourneys = $_criteria["showLateJourneys"]->get_criteria_value("VALUE");

$iconnex = new iconnex($_pdo);

$user = $iconnex->getUser();

//$iconnex->debug = true;
if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dfrom) ) return false;
if ( !$iconnex->build_user_timetable($user, $rt, $op, $tpn, $rbd, $dty, $fromtime, $totime, false) ) return false;


$fetchOk = true;


if ($fetchOk)
{
$sql = "SELECT a.pub_ttb_id, a.day, b.operation_date, b.scheduled_start, " .
    " c.start_code, d.vehicle_code, " .
    " e.start_code act_start, f.vehicle_code act_veh, " .
    " e.scheduled_start journey_from, " .
    " e.next_pub_time journey_to, " .
    " e.actual_start, " .
    " e.trip_status, " .
    " '       ' active_status, " .
    " e.next_pub_ttb, " .
    " e.next_pub_time, " .
    " c.schedule_id eff_sched, " .
    " e.schedule_id act_sched, " .
    " c.schedule_id arc_sched" .
    " FROM t_timetable a, autort_sched b, outer ( archive_rt c, vehicle d ), outer ( active_rt e, vehicle f ) " .
    " WHERE  a.pub_ttb_id = b.pub_ttb_id " .
    " AND " .
    " ( " .
    "         ( a.day = b.operation_date " .
    "             and over_midnight = '0' ) " .
    "         or " .
    "         ( a.day = b.operation_date - 1 " .
    "             and over_midnight = '1' ) " .
    " ) " .
    " AND b.pub_ttb_id = c.pub_ttb_id " .
    " AND date(c.actual_start) = operation_date " .
    " AND c.vehicle_id = d.vehicle_id " .
    " AND b.pub_ttb_id = e.pub_ttb_id " .
    " AND date(e.actual_start) = operation_date " .
    " AND e.vehicle_id = f.vehicle_id ";

    $sql .= " INTO TEMP t_ttb_status WITH NO LOG";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
    $sql = " UPDATE t_ttb_status SET eff_sched = act_sched WHERE act_sched IS NOT NULL";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = " SELECT schedule_id, journey_from, journey_to, '          ' performance, MIN(departure_time_pub) pub_start, MAX(arrival_time_pub) pub_end, MIN(arrival_time) rtpi_start, MAX(arrival_time) rtpi_end" .
    " FROM t_ttb_status, active_rt_loc " .
    " WHERE t_ttb_status.act_sched = active_rt_loc.schedule_id " .
    " AND act_sched IS NOT NULL " .
    " AND arrival_status != 'C' " .
    " AND departure_status != 'C' " .
    " GROUP BY 1, 2, 3, 4 " .
    " INTO TEMP t_actdet WITH NO LOG";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = " UPDATE t_actdet" .
    " SET ( rtpi_start, rtpi_end ) = ( pub_start, pub_end ) " .
    " WHERE rtpi_start IS NULL ";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = " UPDATE t_actdet" .
    " SET ( performance ) = ( 'LATE' ) " .
    " WHERE rtpi_end > journey_to ";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( act_start, act_veh ) = ( 'DONE', vehicle_code ) " .
    " WHERE act_start IS NULL " .
    " AND start_code IS NOT NULL";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( active_status ) = ( 'CURRENT' ) " .
    " WHERE actual_start IS NOT NULL " .
    " AND journey_to IS NOT NULL " .
    " AND CURRENT BETWEEN journey_from AND journey_to";
$sql = "UPDATE t_ttb_status SET ( active_status ) = ( 'CURRENT' ) " .
    " WHERE act_sched IN ( SELECT schedule_id FROM t_actdet " .
        " WHERE CURRENT BETWEEN rtpi_start AND rtpi_end )" .
        " AND act_sched IS NOT NULL";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( act_veh, act_start ) = ( NULL, 'SCH' ) " .
    " WHERE act_start = 'AUT'";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( act_start ) = ( 'RUNNING' ) " .
    " WHERE act_start = 'REAL'";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( active_status, act_start ) = ( 'CURLATE', 'CURLATE' ) " .
    " WHERE act_sched IN ( SELECT schedule_id FROM t_actdet " .
        " WHERE CURRENT BETWEEN rtpi_start AND rtpi_end " .
        " AND rtpi_end > journey_to )" .
        " AND act_sched IS NOT NULL"
        ;
    if (!$iconnex->executeSQL($sql)) return;
}


if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( act_start ) = ( 'NEXT' ) " .
    " WHERE act_start = 'CONT'";
    if (!$iconnex->executeSQL($sql)) return;
}
if ($fetchOk)
{
$sql = "UPDATE t_ttb_status SET ( trip_status ) = ( NULL ) " .
    " WHERE trip_status = 'A'";
    if (!$iconnex->executeSQL($sql)) return;
}

if ($fetchOk)
{
$sql = "SELECT UNIQUE wef_date, wet_date, b.pub_ttb_id " .
   " FROM tt_mod a, tt_mod_trip b " .
   " WHERE a.mod_id = b.mod_id " .
   " AND a.location_id IS NOT NULL " .
   " INTO TEMP t_diversions WITH NO LOG";
    if (!$iconnex->executeSQL($sql)) return;
}


if ($fetchOk)
{
$sql = "SELECT UNIQUE alloc_vehicle, vehicle_code alloc_vehcode, wef_date, wet_date, b.pub_ttb_id " .
  " FROM tt_mod a, tt_mod_trip b, vehicle c " .
  " WHERE a.mod_id = b.mod_id " .
  " AND mod_type = 'V' " .
  " AND a.alloc_vehicle = c.vehicle_id " .
  " INTO TEMP t_allocveh WITH NO LOG";
    if (!$iconnex->executeSQL($sql)) return;
}

    $sql ="
SELECT vehicle_id, message_time, gpslat, -gpslong gpslong
FROM vehicle a, unit_status b
WHERE a.build_id = b.build_id
AND message_time > CURRENT - 10 UNITS MINUTE";

if ($op)
{
    $sql .= " AND a.operator_id IN (" . $op . ")";
}

$sql .= "
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
FROM active_rt_loc a, active_rt b, t_vehpos
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND b.vehicle_id = t_vehpos.vehicle_id
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
SELECT b.vehicle_id, b.schedule_id, max(rpat_orderby) rpat_orderby
FROM active_rt_loc a, active_rt b, t_vehpos
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND b.vehicle_id = t_vehpos.vehicle_id
AND start_code IN ( 'REAL', 'AUT' )
GROUP BY 1,2
INTO TEMP t_laststop WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

    $sql ="
SELECT b.schedule_id, max(rpat_orderby) rpat_orderby
FROM active_rt_loc a, active_rt b, t_vehpos
WHERE 1 = 1
AND b.vehicle_id = t_vehpos.vehicle_id
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
arrival_time, departure_time, departure_time_pub, 
(( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
(( INTERVAL(0) MINUTE(9) TO MINUTE ) + ( departure_time - departure_time_pub ) ) || '' lateness_min,
route_code, runningno, f.duty_no, f.trip_no, operator_code, h.operator_id, g.service_id, e.route_id, start_code, trip_status, employee_id,
( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
- ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
c.location_code next_location,
c.description next_name, r.rpat_orderby maxord
FROM active_rt_loc a, active_rt b, t_maxtrip d, route e, publish_tt f, service g, operator h, location c, t_laststop r
WHERE 1 = 1
AND a.schedule_id = b.schedule_id
AND d.schedule_id = a.schedule_id
AND b.schedule_id = r.schedule_id
AND d.rpat_orderby = a.rpat_orderby
AND b.pub_ttb_id = f.pub_ttb_id
AND departure_status != 'C'
AND f.service_id = g.service_id
AND g.route_id = e.route_id
AND e.operator_id = h.operator_id
AND a.location_id = c.location_id
INTO TEMP t_trips WITH NO LOG
";
    // Also cursors may be used for example :-
    if (!$iconnex->executeSQL($sql))
       return;

// Set lateness to zero if bus has already finished the trip its on or if bus is early but at first stop
$sql = "
update t_trips set ( lateness, lateness_min ) = ( 0, 0 )
where  rpat_orderby = maxord";
    if (!$iconnex->executeSQL($sql))
       return;

$sql = "
update t_trips set ( lateness, lateness_min ) = ( 0, 0 )
where  rpat_orderby = 1 and lateness < 0 ";
    if (!$iconnex->executeSQL($sql))
       return;

$diversionLink = "outer";
if ($chkShowDiverted)
    $diversionLink = "";

$cancelledLink = "outer ( tt_mod, tt_mod_trip )," ;
if ($chkShowCancelled)
    $cancelledLink = " tt_mod, tt_mod_trip, ";
$nexttripLink = "outer";
if ($chkShowDutyChanges)
    $nexttripLink = "";
$allocvehLink = "outer";
if ($chkShowAllocated)
    $allocvehLink = "";
$latenessLink = "outer";
if ($chkShowLateJourneys)
    $latenessLink = "";


$sql = "SELECT active_status, scheduled_start , t_timetable.pub_ttb_id, t_timetable.day, over_midnight, t_timetable.operator_code," .
    " t_timetable.route_code, service_code, t_timetable.start_time, event_code, t_timetable.trip_no, " .
    " t_timetable.runningno, t_timetable.duty_no, next_pub.duty_no next_duty, next_pub.start_time next_duty_time, act_veh, operation_date, t_ttb_status.trip_status, t_timetable.operator_id, " .
    " act_start, mod_type, mod_status, t_ttb_status.journey_from, extend(t_ttb_status.journey_to, hour to second) journey_to, act_sched, arc_sched, t_diversions.pub_ttb_id diversion, alloc_vehcode, pub_start, pub_end, rtpi_start, rtpi_end, performance, lateness, lateness_min, departure_time - departure_time_pub || ''  real_lateness" .
    " from t_timetable, t_ttb_status, " . $cancelledLink . $nexttripLink . " publish_tt next_pub, " . $allocvehLink . " t_allocveh, " . $diversionLink . " t_diversions, outer t_actdet, $latenessLink t_trips " . 
    " where t_timetable.day = t_ttb_status.day " .
    " and t_timetable.pub_ttb_id = t_ttb_status.pub_ttb_id" .
    " and t_ttb_status.operation_date = tt_mod.wef_date" .
    " and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id" .
    " and t_ttb_status.eff_sched = t_trips.schedule_id" .
    " and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id" .
    " and tt_mod.location_id IS NULL" .
    " and tt_mod.mod_id = tt_mod_trip.mod_id" .
    " and t_ttb_status.next_pub_ttb = next_pub.pub_ttb_id" .
    " and t_timetable.duty_no <> next_pub.duty_no" .
    " and t_ttb_status.operation_date between t_diversions.wef_date and t_diversions.wef_date" .
    " and t_ttb_status.pub_ttb_id = t_diversions.pub_ttb_id" .
    " and t_ttb_status.operation_date between t_allocveh.wef_date and t_allocveh.wef_date" .
    " and t_ttb_status.pub_ttb_id = t_allocveh.pub_ttb_id" .
    " and t_ttb_status.act_sched = t_actdet.schedule_id";

if ($chkShowDutyChanges)
    $sql .= " and next_pub.duty_no is not null ";

if ($chkShowCancelled)
    $sql .= " and mod_type = 'C'";

if ($chkShowDiverted)
    $sql .= " and t_diversions.pub_ttb_id > 0";

if ($chkShowCurrent)
    $sql .= " and active_status IN ( 'CURRENT', 'CURLATE', 'CUREARLY' )";

if ( $chkShowLateJourneys)
    $sql .= " and ( lateness < -1200 OR lateness > 300 )";
    //$sql .= " and ( active_status IN ( 'CURLATE', 'CUREARLY' ) OR lateness < -60 OR lateness > 300 )";

$sql .= " INTO TEMP t_results WITH NO LOG";


if (!$iconnex->executeSQL($sql)) return;
//if (chkShowDutyChanges.Checked)
    //$sql .= " ORDER BY next_pub.start_time, scheduled_start ";
//else
    //$sql .= " ORDER BY scheduled_start ";



for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
   //if ( $col->query_name == "pub_ttb_id" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "scheduled_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "event_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operation_date" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "over_midnight" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "operator_id" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "act_sched" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "arc_sched" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "service_code" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "view_today" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "alloc_vehcode" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "key" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "journey_from" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "act_start" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "runningno" ) $col->attributes["column_display"] = "show";
   if ( $col->query_name == "trip_type" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "mod_status" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "trip_status" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "real_lateness" ) $col->attributes["column_display"] = "hide";
   if ( $col->query_name == "lateness_min" ) $col->attributes["column_display"] = "show";
   if ( $col->query_name == "xlabel" ) $col->attributes["column_display"] = "show";

}

?>
