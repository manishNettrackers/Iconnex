<?php

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$wkd = $_criteria["weekday"]->get_criteria_value("VALUE");
$grp = $_criteria["grouping"]->get_criteria_value("VALUE");
$lowlat = $_criteria["lowerlateness"]->get_criteria_value("VALUE");
$uplat = $_criteria["upperlateness"]->get_criteria_value("VALUE");
$latetol = $_criteria["latetol"]->get_criteria_value("VALUE");
$locfrom = $_criteria["startpoint"]->get_criteria_value("VALUE");
$locto = $_criteria["endpoint"]->get_criteria_value("VALUE");
$duration = $_criteria["duration"]->get_criteria_value("VALUE");
$duration = preg_replace ( "/'/", "", $duration );
$duration = sprintf("% 5s", $duration );


$iconnex = new iconnex($_pdo);
$user = $iconnex->getUser();

$gloc = false;
$grte = false;
$gday = false;
if ( preg_match("/L/", $grp )) $gloc = true;
if ( preg_match("/R/", $grp )) $grte = true;
if ( preg_match("/D/", $grp )) $gday = true;


$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );



if ( !$iconnex->setDirtyRead($iconnex) ) return;

$sql="
SELECT route.*
FROM route_visibility route
WHERE 1 = 1 
AND usernm = '$user'
INTO TEMP t_route WITH NO LOG
";
if ( !$iconnex->executeSQL($sql ) ) return;
//echo $sql."<BR><BR>";

if ( !$iconnex->build_date_range_table ( $dfrom, $dto, $wkd ) ) return;
if ( !rpt_build_timetable_from_locs($iconnex, $op, $rt, $locfrom, $locto ) ) return;
//if ( !rpt_build_pubtimes_from_timetable($iconnex, $loc, false ) ) return;


$sql = 
"
SELECT archive_rt.schedule_id, 
archive_rt_loc.location_id,
archive_Rt_loc.rpat_orderby,
arrival_time, 
archive_rt.pub_ttb_id, 
departure_time,
arrival_time_pub,
departure_time_pub,
employee_id,
vehicle_id,
actual_start
FROM t_timetable_froms,
archive_rt,archive_rt_loc
WHERE 1 = 1  
AND archive_rt_loc.schedule_id = archive_rt.schedule_id
AND archive_rt.pub_ttb_id = t_timetable_froms.pub_ttb_id
AND departure_status = 'A'
AND archive_rt_loc.location_id in ( $locfrom )
AND date(actual_start)  = t_timetable_froms.day
";

$sql .= "
INTO TEMP t_actloc1 WITH NO LOG
";

if ( !$iconnex->executeSQL($sql ) ) return;


$sql = 
"
SELECT t_actloc1.schedule_id, 
archive_rt_loc.location_id,
archive_rt_loc.arrival_time, 
archive_rt_loc.departure_time,
archive_rt_loc.arrival_time_pub,
archive_rt_loc.departure_time_pub,
( INTERVAL(00) MINUTE(4) TO MINUTE + ( archive_rt_loc.arrival_time - t_actloc1.departure_time ) ) || '' duration
FROM t_actloc1, archive_rt_loc
WHERE 1 = 1  
AND archive_rt_loc.schedule_id = t_actloc1.schedule_id
AND arrival_status = 'A'
AND archive_rt_loc.rpat_orderby > t_actloc1.rpat_orderby
";

if ( $locto )
	$sql .= "AND archive_rt_loc.location_id in ( $locto )";
$sql .= "
INTO TEMP t_actloc2 WITH NO LOG
";




if ( !$iconnex->executeSQL($sql ) ) return;


if ( trim($duration) )
{
	$duration = sprintf("% 5d", $duration);
	$sql = " DELETE FROM t_actloc2 WHERE duration != '$duration'; ";
	if ( !$iconnex->executeSQL($sql ) ) return;
}

function rpt_build_timetable_from_locs($iconnex, $op, $rt, $fromloc, $toloc)
{

$sqltemp = "
SELECT t_days.day, t_days.dtime, t_route.route_id, 
service.service_id, publish_tt.pub_ttb_id pub_ttb_id, 
notes[1,1] over_midnight, service_patt.location_id,
service_patt.rpat_orderby
FROM operator,t_route,service, t_days, publish_tt, event, event_pattern, service_patt
WHERE 1 = 1
AND operator.operator_id = t_route.operator_id
AND t_route.route_id = service.route_id
AND publish_tt.service_id = service.service_id
and t_days.day between service.wef_date and service.wet_date
AND publish_tt.evprf_id = event_pattern.evprf_id
AND event_pattern.event_id = event.event_id
and event.event_tp = 3
and weekday(t_days.day) between rpdy_start and rpdy_end
and service.service_id = service_patt.service_id
and service_patt.location_id in ( {LOCATIONS} )
and ( current > extend(start_time, year to second) or
date(current) > t_days.day)
";
if ( $rt )
    $sqltemp .= " AND t_route.route_id in ( $rt )";
if ( $op )
    $sqltemp .= " AND operator.operator_id in ( $op )";

$sql = preg_replace("/{LOCATIONS}/", $fromloc, $sqltemp).
	" INTO TEMP t_timetable_froms WITH NO LOG ";
if ( !$iconnex->executeSQL($sql ) ) return false;

/*
$sql = preg_replace("/{LOCATIONS}/", $loc_to, $sqltemp".
	" INTO TEMP t_timetable_tos WITH NO LOG ";
if ( !$iconnex->executeSQL($sql ) ) return false;


$sql = "
SELECT t_days.day, t_days.dtime, route_code, service_code,
t_route.route_id, operator.operator_code operator_code,
service.service_id, publish_tt.pub_ttb_id pub_ttb_id, notes[1,1] over_midnight, publish_tt.pub_prof_id
FROM operator,t_route,service, t_days, publish_tt, event, event_pattern
WHERE 1 = 1
AND operator.operator_id = t_route.operator_id
AND t_route.route_id = service.route_id
AND publish_tt.service_id = service.service_id
and t_days.day between service.wef_date and service.wet_date
AND publish_tt.evprf_id = event_pattern.evprf_id
AND event_pattern.event_id = event.event_id
and event.event_tp = 3
and weekday(t_days.day) between rpdy_start and rpdy_end
and ( current > extend(start_time, year to second) or
date(current) > t_days.day)
";
if ( $rt )
    $sql .= " AND t_route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";

$sql .="
INTO TEMP t_timetable WITH NO LOG
";
    return $iconnex->Execute($sql);
*/
	return true;
}


?>
