<?php

require_once('iconnex.php');

$hg = $_criteria["daygroup"]->get_criteria_value("VALUE");
$sgroup = $_criteria["servgroup"]->get_criteria_value("VALUE");
$tgroup = $_criteria["tripgroup"]->get_criteria_value("VALUE");
$dgroup = $_criteria["dirgroup"]->get_criteria_value("VALUE");
$direction = $_criteria["direction"]->get_criteria_value("VALUE");
$sm = $_criteria["showminmax"]->get_criteria_value("VALUE");
$wkd = $_criteria["weekday"]->get_criteria_value("VALUE");
$dr1 = $_criteria["daterange"]->get_criteria_value("RANGE1");
$dr2 = $_criteria["daterange"]->get_criteria_value("RANGE2");
$drc1 = substr($dr1, 7,4)."-".substr($dr1, 4,2)."-".substr($dr1,1,2);
$drc2 = substr($dr2, 7,4)."-".substr($dr2, 4,2)."-".substr($dr2,1,2);
$dsr1 = substr($dr1, 9,2)."-".substr($dr1, 6,2)."-".substr($dr1,1,4);
$dsr2 = substr($dr2, 9,2)."-".substr($dr2, 6,2)."-".substr($dr2,1,4);

$latetol = $_criteria["latetol"]->get_criteria_value("VALUE");
$earlytol = $_criteria["earlytol"]->get_criteria_value("VALUE");
$crt = $_criteria["route"]->get_criteria_value("VALUE");
$service = $_criteria["service"]->get_criteria_value("VALUE");
$cop = $_criteria["operator"]->get_criteria_value("VALUE");
//$crb = $_criteria["runningno"]->get_criteria_value("VALUE");
//$cvh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$ctp = $_criteria["tripno"]->get_criteria_value("VALUE");


$sql = 
"SELECT location.location_id, location.location_code location_code, route.route_id, route.route_code route_code, archive_rt.actual_start actual_start, publish_tt.trip_no trip_no, publish_tt.runningno runningno, employee.employee_code employee_code, vehicle.vehicle_code vehicle_code, location.description description, route_area.route_area_code route_area_code, service_patt.direction, service.service_id, archive_rt_loc.rpat_orderby rpat_orderby, archive_rt_loc.arrival_time arrival_time, archive_rt_loc.departure_time departure_time, archive_rt_loc.departure_time_pub departure_time_pub, publish_time.pub_time pub_time, archive_rt.schedule_id schedule_id, publish_tt.start_time start_time, archive_rt_loc.actual_est actual_est, archive_rt_loc.departure_status departure_status, archive_rt_loc.arrival_status arrival_status, publish_tt.duty_no duty_no, archive_rt_loc.departure_time - archive_rt_loc.arrival_time wait_time,  
archive_rt_loc.departure_time - archive_rt_loc.departure_time_pub lateness,";

if ( $hg != "'H'" )
{
  $sql = $sql. "'ALL' pubhour,";
  $sql .= "'00' critmint, '23' critmaxt,";
}
else
{
  $sql = $sql. "extend(archive_rt_loc.departure_time, hour to hour) pubhour,";
  $sql .= "extend(archive_rt_loc.departure_time, hour to hour) critmint, extend(archive_rt_loc.departure_time, hour to hour) critmaxt,";
}

if ( $hg != "'D'" )
{
$sql = $sql. "'ALL' pubdate,";
$sql .= "'$drc1' critmind, '$drc2' critmaxd,";
}
else
{
$sql = $sql.
"extend(archive_rt_loc.departure_time_pub, year to day) pubdate,";
$sql .= "extend(archive_rt_loc.departure_time_pub, year to day) critmind, extend(archive_rt_loc.departure_time_pub, year to day) critmaxd,";
}

$sql = $sql.
"
extend(archive_rt_loc.departure_time_pub, month to month) pubmonth
FROM operator,route_for_user route,service,archive_rt,
archive_rt_loc,t_times,publish_tt,destination,
service_patt,employee,vehicle,location,route_profile,route_area,outer publish_time 
WHERE 1 = 1 
AND archive_rt.route_id = route.route_id
AND service.route_id = route.route_id
AND service_patt.dest_id = destination.dest_id
AND service.service_id = publish_tt.service_id
AND operator.operator_id = route.operator_id
AND archive_rt.profile_id = route_profile.profile_id
AND archive_rt.profile_id = publish_tt.rtpi_prof_id
AND archive_rt.employee_id = employee.employee_id
AND archive_rt.vehicle_id = vehicle.vehicle_id
AND archive_rt_loc.schedule_id = archive_rt.schedule_id
AND archive_rt.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.service_id = service_patt.service_id
AND archive_rt_loc.rpat_orderby = service_patt.rpat_orderby
AND archive_rt_loc.location_id = service_patt.location_id
AND archive_rt_loc.location_id = location.location_id
AND route_area.route_area_id = location.route_area_id
AND publish_time.location_id = archive_rt_loc.location_id
AND publish_tt.pub_ttb_id = publish_time.pub_ttb_id
--AND ( archive_rt_loc.rpat_orderby > 1 or departure_time > departure_time_pub - 1 units minute )
AND actual_est != 'C' 
AND arrival_status != 'C'
AND departure_time_pub IS NOT NULL
AND departure_status != 'C'
AND departure_status = 'A'
AND arrival_status = 'A'
and (extend(departure_time, hour to second) -
extend(departure_time_pub, hour to second) < $latetol and
extend(departure_time, hour to second) -
extend(departure_time_pub, hour to second) > $earlytol)
";

$sql = $sql.
" AND date(actual_start) BETWEEN $dr1 AND $dr2";

if ( $service )$sql .= " AND service.service_id IN ($service)";
if ( $crt )$sql .= " AND route.route_id IN ($crt)";
if ( $crt )$sql .= " AND archive_rt.route_id IN ($crt)";
if ( $cop )$sql .= " AND operator.operator_id IN ($cop)";
if ( $ctp )$sql .= " AND publish_tt.trip_no IN ($ctp)";
if ( $wkd )$sql .= " AND weekday(actual_start) IN ($wkd)";

$sql = $sql.
"
 AND extend(archive_rt_loc.arrival_time, hour to second)  BETWEEN t_times.from_time and
t_times.to_time 
INTO TEMP t_rtpi WITH NO LOG";

if ( !rpt_executePDOQuery($ds, $sql ) ) return false;

if ( !$dgroup )
{
$dgroupcol = "'ALL' dirgroup";
}
else
{
$dgroupcol = "t_rtpi.direction || '' dirgroup";
}

if ( !$tgroup )
{
$tgroupcol = "'ALL' tripgroup";
}
else
{
$tgroupcol = "start_time || '' tripgroup";
}

if ( $sgroup != "'G'" )
{
$sql = "
SELECT 'ALL' service_code, t_rtpi.route_id route_id, t_rtpi.pubdate, t_rtpi.pubhour, 
route_pattern.direction,
$tgroupcol,
$dgroupcol,
route_pattern.display_order,
route_pattern.location_id,
critmind,critmaxd,critmint,critmaxt,
";

if ( $sm == "'Y'" )
$sql .= "min(lateness) minlate, max(lateness) maxlate,"; 
else
$sql .= "min(interval(0) second(9) to second) minlate, min(interval(0) second(9) to second) maxlate,";

$sql .= 
" avg(lateness) avglate, count(*) cntlate
FROM t_rtpi, route_pattern
WHERE 1 = 1     
AND t_rtpi.route_id = route_pattern.route_id
AND t_rtpi.location_id = route_pattern.location_id
AND t_rtpi.direction = route_pattern.direction
AND lateness between '-0 01:00:00' and '0 01:00:00'";

if ( $direction )
{
     $sql .= " AND t_rtpi.direction IN ( $direction )";
}


$sql .= 
" GROUP BY 1, 2, 3, 4, 5, 6,7,8,9,10, 11,12,13
INTO TEMP t_summary WITH NO LOG
";

}
else
{
$sql = "
SELECT service_code, t_rtpi.route_id route_id, t_rtpi.pubdate, t_rtpi.pubhour, 
service_patt.direction,
$tgroupcol,
$dgroupcol,
service_patt.rpat_orderby display_order,
service_patt.location_id,
critmind,critmaxd,critmint,critmaxt,
";

if ( $sm == "'Y'" )
$sql .= "min(lateness) minlate, max(lateness) maxlate,"; 
else
$sql .= "min(interval(0) second(9) to second) minlate, min(interval(0) second(9) to second) maxlate,";

$sql .= 
" avg(lateness) avglate, count(*) cntlate
FROM t_rtpi, service_patt, service, route
WHERE 1 = 1     
AND t_rtpi.service_id = service_patt.service_id
AND t_rtpi.rpat_orderby = service_patt.rpat_orderby
AND service_patt.service_id = service.service_id
AND service.route_id = route.route_id
AND t_rtpi.location_id = service_patt.location_id
AND lateness between '-0 01:00:00' and '0 01:00:00'";

if ( $direction )
{
     $sql .= " AND direction IN ( $direction )";
}


$sql .= 
" GROUP BY 1, 2, 3, 4, 5, 6,7,8,9,10, 11,12,13
INTO TEMP t_summary WITH NO LOG
";



}


if ( !rpt_executePDOQuery($ds, $sql ) ) return false;



?>