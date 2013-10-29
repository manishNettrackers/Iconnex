<?php 

function rpt_executePDOQuery( $in_conn, $in_sql )
{
		//echo $in_sql."<BR><BR>";
        $rid =  $in_conn->Execute($in_sql);

        if ( !$rid )
        {
                $msg = "<br>$in_sql<br>".$in_conn->ErrorMsg();
                trigger_error("$msg");
                return ( $rid);
        }
        return $rid;
}

function rpt_fetchPDO( $in_stmt, $in_type = "NEXT" )
{
        $result = $in_stmt->FetchRow();
        return $result;
}

function rpt_showPDOError( $in_conn )
{
        $info = $in_conn->errorInfo();
        echo "Error ".$info[1]."<BR>".
                $info[2];
}

function rpt_setDirtyRead($in_conn)
{
	$sql = "SET ISOLATION TO DIRTY READ";
	return $in_conn->Execute($sql);
}



function rpt_build_timetable($in_conn, $op, $rt, $rb = false, $dt = false, 
				$tp = false)
{
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
	return ( rpt_executePDOQuery($in_conn, $sql ) );

	$sql = "CREATE INDEX ix_tttb ON t_timetable ( day, pub_ttb_id );";
	if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

	$sql = "CREATE INDEX ix_tttb2 ON t_timetable ( pub_ttb_id );";
	if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

}

function rpt_build_day_range_table($in_conn, $fromday, $today, $weekdays = false )
{
	$sql = "CREATE TEMP TABLE t_days ( day date, dtime datetime year to day ) with no log;";
	if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

	$ptr = $fromday;
	while ( $ptr <= $today )
	{
    	$dt = strftime ( "%d/%m/%Y", $ptr );
    	$dtm = strftime ( "%Y-%m-%d", $ptr );
	
    	$sql = "INSERT INTO t_days VALUES ( '".$dt."', '".$dtm."' );";
		if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;
	
    	$ptr = $ptr + ( 24 * 60 * 60 );
	};

	if ( $weekdays )
	{
		$sql ="
		DELETE FROM t_days WHERE WEEKDAY(day) NOT IN ( $weekdays );";
		if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;
	}

	return true;
}

function rpt_build_pubtimes_from_timetable ( $in_conn, $loc = false, $startonly = false )
{
	$sql="
SELECT day, t_timetable.pub_ttb_id, t_timetable.route_id, route_location.location_id,
  route_location.rpat_orderby
FROM t_timetable, route_location, publish_tt, service_patt, t_lastlocs
WHERE t_timetable.pub_prof_id = route_location.profile_id
AND t_timetable.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.service_id = service_patt.service_id
AND route_location.rpat_orderby = service_patt.rpat_orderby
AND service_patt.service_id = t_lastlocs.service_id
AND service_patt.rpat_orderby < t_lastlocs.rpat_orderby
";
	if ($startonly)
   		$sql .= "AND route_location.rpat_orderby = 1";
	if ($loc)
   		$sql .= "AND route_location.location_id IN ( $loc )";
	$sql .= "
		INTO TEMP t_pubtime WITH NO LOG;";
	if ( !rpt_executePDOQuery($in_conn, $sql ) ) return false;

}

?>
