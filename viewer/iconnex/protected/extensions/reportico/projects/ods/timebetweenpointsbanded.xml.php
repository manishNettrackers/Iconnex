<?php

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$wkd = $_criteria["weekday"]->get_criteria_value("VALUE");
$timeband1 = $_criteria["timeband1"]->get_criteria_value("VALUE");
$timeband2 = $_criteria["timeband2"]->get_criteria_value("VALUE");
$timeband3 = $_criteria["timeband3"]->get_criteria_value("VALUE");
$timeband4 = $_criteria["timeband4"]->get_criteria_value("VALUE");
$timeband5 = $_criteria["timeband5"]->get_criteria_value("VALUE");
$timeband6 = $_criteria["timeband6"]->get_criteria_value("VALUE");
$locfrom = $_criteria["startpoint"]->get_criteria_value("VALUE");
$locto = $_criteria["endpoint"]->get_criteria_value("VALUE");



$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );



if ( !rpt_setDirtyRead($ds) ) return;

// Storee Criteria
$sql = "CREATE TEMP TABLE t_criteria
		(
		operator char(10),
		route   char(10),
		from_date char(10),
		to_date   char(10)
		) WITH NO LOG
	";
if ( !rpt_executePDOQuery($ds, $sql ) ) return;

$fdfrom = substr("$dfrom", 7,4). "-". substr("$dfrom", 4,2). "-". substr("$dfrom", 1,2);
$fdto = substr("$dto", 7,4). "-". substr("$dto", 4,2). "-". substr("$dto", 1,2);
$insop = preg_replace("/'/", "", $op );
$insrt = preg_replace("/'/", "", $rt );
$sql = "INSERT INTO t_criteria
		( operator, route, from_date, to_date)
		VALUES
		( '$insop', '$insrt', '$fdfrom', '$fdto' )
	";
if ( !rpt_executePDOQuery($ds, $sql ) ) return;



$sql="
SELECT route.*
FROM route, cent_user 
WHERE 1 = 1 
AND (
route.operator_id = cent_user.operator_id
OR cent_user.operator_id IS NULL )
AND cent_user.usernm = USER
INTO TEMP t_route WITH NO LOG
";
if ( !rpt_executePDOQuery($ds, $sql ) ) return;
//echo $sql."<BR><BR>";

if ( !rpt_build_day_range_table ( $ds, $ifrom, $ito, $wkd ) ) return;
if ( !rpt_build_timetable_from_locs($ds, $op, $rt, $locfrom, $locto ) ) return;
//if ( !rpt_build_pubtimes_from_timetable($ds, $loc, false ) ) return;


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

if ( !rpt_executePDOQuery($ds, $sql ) ) return;

// Remove early departures which may skew the results
$sql = "DELETE FROM t_actloc1 
WHERE rpat_orderby = 1
AND departure_time  < departure_time_pub - 1 UNITS MINUTE";
if ( !rpt_executePDOQuery($ds, $sql ) ) return;

$sql = 
"
SELECT t_actloc1.schedule_id, 
archive_rt_loc.location_id,
archive_rt_loc.arrival_time, 
archive_rt_loc.departure_time,
archive_rt_loc.arrival_time_pub,
archive_rt_loc.departure_time_pub
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


if ( !rpt_executePDOQuery($ds, $sql ) ) return;

if ( !rpt_build_intervals($ds) ) return;
$timeband1 = preg_replace("/'/", "", $timeband1 );
$timeband2 = preg_replace("/'/", "", $timeband2 );
$timeband3 = preg_replace("/'/", "", $timeband3 );
$timeband4 = preg_replace("/'/", "", $timeband4 );
$timeband5 = preg_replace("/'/", "", $timeband5 );
$timeband6 = preg_replace("/'/", "", $timeband6 );
if ( !rpt_build_banded_intervals($ds, "t_bandint1", $timeband1, $timeband2 ) ) return;
if ( !rpt_build_banded_intervals($ds, "t_bandint2", $timeband2, $timeband3 ) ) return;
if ( !rpt_build_banded_intervals($ds, "t_bandint3", $timeband3, $timeband4 ) ) return;
if ( !rpt_build_banded_intervals($ds, "t_bandint4", $timeband4, $timeband5 ) ) return;
if ( !rpt_build_banded_intervals($ds, "t_bandint5", $timeband5, $timeband6 ) ) return;
if ( !rpt_build_minute_list($ds) ) return;
if ( !rpt_build_results($ds) ) return;

// Set column headers from banding
foreach ($this->graphs as $key => $val)
{
	$val->plots[0]["legend"] = substr($timeband1,0,5)."-".substr($timeband2,0,5);
	$val->plots[1]["legend"] = substr($timeband2,0,5)."-".substr($timeband3,0,5);
	$val->plots[2]["legend"] = substr($timeband3,0,5)."-".substr($timeband4,0,5);
	$val->plots[3]["legend"] = substr($timeband4,0,5)."-".substr($timeband5,0,5);
	$val->plots[4]["legend"] = substr($timeband5,0,5)."-".substr($timeband6,0,5);
}
for ($ct = 0; $ct < count($this->columns); $ct++)
{
	$col = $this->columns[$ct];

	if ( $col->query_name == "band1_ct" ) $col->attributes["column_title"] = substr($timeband1,0,5)."-".substr($timeband2,0,5);
	if ( $col->query_name == "band2_ct" ) $col->attributes["column_title"] = substr($timeband2,0,5)."-".substr($timeband3,0,5);
	if ( $col->query_name == "band3_ct" ) $col->attributes["column_title"] = substr($timeband3,0,5)."-".substr($timeband4,0,5);
	if ( $col->query_name == "band4_ct" ) $col->attributes["column_title"] = substr($timeband4,0,5)."-".substr($timeband5,0,5);
	if ( $col->query_name == "band5_ct" ) $col->attributes["column_title"] = substr($timeband5,0,5)."-".substr($timeband6,0,5);
}

function rpt_build_intervals($in_conn)
{
$sql = 
"SELECT t_actloc1.location_id locfrom, t_actloc2.location_id locto,
t_actloc1.departure_time,
t_actloc2.arrival_time, 
INTERVAL(0) SECOND(9) TO SECOND + t_actloc2.arrival_time - t_actloc1.departure_time duration
FROM t_actloc1, t_actloc2
WHERE 1 = 1             
AND t_actloc1.schedule_id = t_actloc2.schedule_id
INTO TEMP t_intervals WITH NO LOG;";


if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

return true;

}

function rpt_build_results($in_conn)
{
$sql = 
"
SELECT t_mins.locfrom, t_mins.locto, 
t_mins.duration_mins,
SUM(t_bandint1.marker) band1_ct,
SUM(t_bandint2.marker) band2_ct,
SUM(t_bandint3.marker) band3_ct,
SUM(t_bandint4.marker) band4_ct,
SUM(t_bandint5.marker) band5_ct
FROM t_mins, 
OUTER t_bandint1,
OUTER t_bandint2,
OUTER t_bandint3,
OUTER t_bandint4,
OUTER t_bandint5
WHERE t_mins.locfrom = t_bandint1.locfrom
AND t_mins.locfrom = t_bandint2.locfrom
AND t_mins.locfrom = t_bandint3.locfrom
AND t_mins.locfrom = t_bandint4.locfrom
AND t_mins.locfrom = t_bandint5.locfrom
AND t_mins.locto = t_bandint1.locto
AND t_mins.locto = t_bandint2.locto
AND t_mins.locto = t_bandint3.locto
AND t_mins.locto = t_bandint4.locto
AND t_mins.locto = t_bandint5.locto
and t_mins.duration_mins = t_bandint1.duration_mins
and t_mins.duration_mins = t_bandint2.duration_mins
and t_mins.duration_mins = t_bandint3.duration_mins
and t_mins.duration_mins = t_bandint4.duration_mins
and t_mins.duration_mins = t_bandint5.duration_mins
GROUP BY 1, 2, 3
INTO TEMP t_results WITH NO LOG";

if ( !rpt_executePDOQuery($in_conn , $sql ) ) return false;
if ( !rpt_executePDOQuery($in_conn , "UPDATE t_results SET band1_ct = 0 WHERE band1_ct IS NULL" ) ) return false;
if ( !rpt_executePDOQuery($in_conn , "UPDATE t_results SET band2_ct = 0 WHERE band2_ct IS NULL" ) ) return false;
if ( !rpt_executePDOQuery($in_conn , "UPDATE t_results SET band3_ct = 0 WHERE band3_ct IS NULL" ) ) return false;
if ( !rpt_executePDOQuery($in_conn , "UPDATE t_results SET band4_ct = 0 WHERE band4_ct IS NULL" ) ) return false;
if ( !rpt_executePDOQuery($in_conn , "UPDATE t_results SET band5_ct = 0 WHERE band5_ct IS NULL" ) ) return false;
return true;


}

function rpt_build_minute_list($in_conn)
{
$sql = 
"SELECT UNIQUE locfrom, locto, duration_mins
FROM t_bandint1
UNION ALL
SELECT UNIQUE locfrom, locto, duration_mins
FROM t_bandint2
UNION ALL
SELECT UNIQUE locfrom, locto, duration_mins
FROM t_bandint3
UNION ALL
SELECT UNIQUE locfrom, locto, duration_mins
FROM t_bandint4
UNION ALL
SELECT UNIQUE locfrom, locto, duration_mins
FROM t_bandint5
INTO TEMP t_mins WITH NO LOG";

if ( !rpt_executePDOQuery($in_conn , $sql ) ) return false;
return true;
}

function rpt_build_banded_intervals($in_conn, $temptable, $fromtime, $totime)
{
$sql = 
"SELECT locfrom, locto, '$fromtime-$totime' band_label, 
( INTERVAL(00) MINUTE(3) TO MINUTE + duration ) || '' duration_mins, count(*)  marker
FROM t_intervals
WHERE 1 = 1             
AND EXTEND  ( departure_time, HOUR TO SECOND ) BETWEEN '$fromtime' AND '$totime'
GROUP BY 1, 2, 3, 4
INTO TEMP $temptable WITH NO LOG";



if ( !rpt_executePDOQuery($in_conn , $sql ) ) return false;

return true;

}

function rpt_build_timetable_from_locs($in_conn, $op, $rt, $fromloc, $toloc)
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
if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

/*
$sql = preg_replace("/{LOCATIONS}/", $loc_to, $sqltemp".
	" INTO TEMP t_timetable_tos WITH NO LOG ";
if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;


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
    return $in_conn->Execute($sql);
*/
	return true;
}




?>
