<?php 

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$at = "'A','P'";
$tol = $_criteria["tolerance"]->get_criteria_value("VALUE");
$tol2 = $_criteria["tolerance2"]->get_criteria_value("VALUE");
$snap = "2";
$runsum = "4";
$rbd = false;
$exc = true;
$str = false;
$uv = false;
$vh = false;

$user = session_request_item("user", false );
if ( !$user )
    $user = "admin";

if ( $vh )
{
    $rbd = "'NONE'";
}

$debug = 0;


$detail = "TRIP";
if ( preg_match ( "/1/", $runsum ) )
    $detail = "TRIP";
if ( preg_match ( "/2/", $runsum ) )
    $detail = "RB";
if ( preg_match ( "/3/", $runsum ) )
    $detail = "OP";
if ( preg_match ( "/4/", $runsum ) )
    $detail = "ROUTE";

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);
$snap = preg_replace("/'/", "", $snap);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );

$sql = "SET ISOLATION TO DIRTY READ;";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
select vehicle_code, c.build_id, version, max(a.pub_id) pub_id
from vehicle_visibility b, unit_build c, soft_ver d, publication e, outer unit_publish a
where a.build_id = b.build_id
and b.build_id = c.build_id
and c.version_id = d.version_id
and tidyup_status = 'S'
and e.pub_id = a.pub_id
and usernm = '$user'
group by 1, 2, 3
into temp t_lastpub with no log;
";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = 
"select vehicle_code, a.build_id, version, max(a.pub_id) pub_id
from unit_publish a, vehicle_visibility b, unit_build c, soft_ver d, publication e
where a.build_id = b.build_id
and b.build_id = c.build_id
and c.version_id = d.version_id
and e.pub_id = a.pub_id
and usernm = '$user'
group by 1, 2, 3
into temp t_shouldpub;";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "update t_lastpub
set ( pub_id ) = ( 0 )
where pub_id is null";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql = "
SELECT b.vehicle_id vehicle_id, date(h.pub_start_time) - date(i.pub_start_time) outofdate, 
today - max(date(load_wlan_time)) timesincewlan, today - date(message_time) timesincealive
FROM vehicle_visibility b, unit_build c, soft_ver d, unit_publish e, t_shouldpub f, t_lastpub g, publication h, outer publication i, operator j, unit_status k, outer unit_log_hist a 
WHERE 1 = 1                                      
AND a.build_id = b.build_id
and b.build_id = c.build_id
and c.version_id = d.version_id
and e.build_id = c.build_id
and f.build_id = c.build_id
and g.build_id = c.build_id
and e.pub_id = f.pub_id
and f.pub_id = h.pub_id
and g.pub_id = i.pub_id
and c.build_id = k.build_id
and message_time > CURRENT - 10 UNITS DAY
and j.operator_id = c.operator_id 
and usernm = '$user'
GROUP BY 1, 2,4
INTO TEMP t_pub
";

$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "UPDATE t_pub SET outofdate = NULL WHERE outofdate = 0";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "UPDATE t_pub SET timesincealive = NULL WHERE timesincealive = 0";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "CREATE TEMP TABLE t_ttb_days ( day date );";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "CREATE TEMP TABLE t_days ( day date );";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$ptr = $ifrom;
while ( $ptr <= $ito )
{
    $dt = strftime ( "%d/%m/%Y", $ptr );

    $sql = "INSERT INTO t_days VALUES ( '".$dt."' );";
    $ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

    $ptr = $ptr + ( 24 * 60 * 60 );
}

$ptr = $ifrom;
while ( $ptr <= $ito + ( 24 * 60 * 60 ) )
{
    $dt = strftime ( "%d/%m/%Y", $ptr );

    $sql = "INSERT INTO t_ttb_days VALUES ( '".$dt."' );";
    $ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

    $ptr = $ptr + ( 24 * 60 * 60 );
}

$sql = "SELECT day, vehicle_id, COUNT(*) screendowns
FROM vehicle a, unit_build b, unit_alert c, t_days d
WHERE a.build_id = b.build_id
AND b.build_id = c.build_id
AND date(alert_time) = d.day
AND message_type = '513'
GROUP BY 1,2
INTO TEMP t_screendown
";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql = "
SELECT route_code, service_code,
route.route_id, operator.operator_code operator_code,
service.service_id, publish_tt.pub_ttb_id pub_ttb_id,
runningno,
event.event_code event_code, t_days.day day, event.event_id,
operator.operator_id, start_time, (notes[1,1]::integer) over_midnight
FROM operator,route_visibility route,service, t_days, publish_tt,event_pattern,event
WHERE 1 = 1
AND operator.operator_id = route.operator_id
AND route.route_id = service.route_id
AND publish_tt.service_id = service.service_id
and publish_tt.evprf_id   = event_pattern.evprf_id
and event_pattern.event_id   = event.event_id
and t_days.day between service.wef_date and service.wet_date
and usernm = '$user'
and weekday(t_days.day) between rpdy_start and rpdy_end
and 
	( current - 20 units minute  > extend(start_time, year to second) 
	or
	date(current) > t_days.day)
and notes matches '*'
and event_tp = 3
";
if ( $snap )
    $sql .= "and extend(start_time, year to second) > current - $snap units hour";
if ( $rbd )
    $sql .= " AND publish_tt.runningno in ( $rbd )";
if ( $rt )
    $sql .= " AND route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";

$sql .="
INTO TEMP t_timetable;
";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX ix_tttb ON t_timetable ( pub_ttb_id );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
SELECT t_days.day day, route.operator_id, service.service_id, count(*) trip_total
FROM operator,cent_user,route,service,service_patt, t_days
WHERE 1 = 1
AND ( operator.operator_id = cent_user.operator_id OR cent_user.operator_id IS NULL )
AND cent_user.usernm = USER
AND operator.operator_id = route.operator_id
AND route.route_id = service.route_id
AND service_patt.service_id = service.service_id
AND t_days.day between wef_date and wet_date
group by 1, 2, 3
into temp t_servct with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
SELECT 'H' runstat, route.route_id, 
    CASE
        WHEN
            extend(start_time, hour to hour) -
            extend(actual_start, hour to hour) between \"-3\" and \"3\"
            THEN date(actual_start) - (notes::integer)
        WHEN
            extend(start_time, hour to hour) -
            extend(actual_start, hour to hour) < \"-20\"
            and (notes::integer) = 0
            THEN date(actual_start) + 1
        WHEN
            extend(start_time, hour to hour) -
            extend(actual_start, hour to hour) > \"20\"
            and (notes::integer) = 0
            THEN date(actual_start) - 1
        ELSE date(actual_start)
    END day,
         route.operator_id, a.schedule_id,
         a.pub_ttb_id, c.vehicle_id, 1 trip_count, count(*) act_total,
         min(arrival_time) minarr, max(arrival_time) maxarr,
         min(rpat_orderby) minord, max(rpat_orderby) maxord
FROM archive_rt a, archive_rt_loc b, vehicle c, t_ttb_days, route, publish_tt
WHERE a.schedule_id = b.schedule_id
AND a.vehicle_id = c.vehicle_id
AND a.route_id = route.route_id
AND a.pub_ttb_id  = publish_tt.pub_ttb_id
AND a.pub_ttb_id IN ( SELECT pub_ttb_id
FROM t_timetable )
AND ( actual_est IN ( $at ) 
    OR arrival_status IN ( $at )
    OR departure_status IN ( $at )
)
AND date(actual_start) = t_ttb_days.day
GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
UNION ALL
SELECT 'C' runstat, route.route_id, t_ttb_days.day, route.operator_id, a.schedule_id,
         a.pub_ttb_id, c.vehicle_id, 1 trip_count, count(*) act_total,
         min(arrival_time) minarr, max(arrival_time) maxarr, min(rpat_orderby) minord,
         max(rpat_orderby) maxord
FROM active_rt a, active_rt_loc b, vehicle c, t_ttb_days, route, publish_tt
WHERE a.schedule_id = b.schedule_id
AND a.vehicle_id = c.vehicle_id
AND a.route_id = route.route_id
AND a.pub_ttb_id  = publish_tt.pub_ttb_id
AND a.pub_ttb_id IN ( SELECT pub_ttb_id
FROM t_timetable )
AND start_code = 'REAL'
AND date(actual_start) = t_ttb_days.day
GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
INTO TEMP t_act with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="CREATE INDEX ix_tact ON t_act ( pub_ttb_id );";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="CREATE INDEX ix_tact1 ON t_act ( operator_id, day );";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

if ( $runsum )
    $runsumcol = "'ALL'";
else
    $runsumcol = "runningno";

$routesumcol = "'ALL'";


$sql="
SELECT operator_code, operator_id, day, runningno, route_id, pub_ttb_id, start_time, 0 notrun_rblater, 0 notrun_droppedoff, 0 notrun_skipped, count(*) schcount, max(event_id) event_id 
FROM t_timetable 
GROUP BY 1, 2, 3, 4, 5, 6, 7 
INTO TEMP t_runsch with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX i_t_runsch ON t_runsch ( operator_id, runningno, day );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
SELECT operator_id, runningno, day, route_id, min(start_time) minstart, max(start_time) maxstart
FROM t_act, publish_tt
WHERE t_act.pub_ttb_id = publish_tt.pub_ttb_id
GROUP BY 1, 2, 3, 4
INTO TEMP t_rbends WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
UPDATE t_runsch
SET ( notrun_rblater ) = ( 1 )
WHERE start_time <
        ( SELECT minstart
          FROM t_rbends
                WHERE t_rbends.operator_id = t_runsch.operator_id
                AND t_rbends.day = t_runsch.day
                AND t_rbends.runningno = t_runsch.runningno
                AND t_rbends.route_id = t_runsch.route_id
        );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
UPDATE t_runsch
SET ( notrun_droppedoff ) = ( 1 )
WHERE start_time >
        ( SELECT maxstart
          FROM t_rbends
                WHERE t_rbends.operator_id = t_runsch.operator_id
                AND t_rbends.day = t_runsch.day
                AND t_rbends.runningno = t_runsch.runningno
                AND t_rbends.route_id = t_runsch.route_id
        );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

/*
$sql="
UPDATE t_runsch
SET ( notrun_skipped ) = ( 1 )
WHERE pub_ttb_id NOT IN 
        ( SELECT pub_ttb_id 
          FROM t_act
                WHERE t_act.operator_id = t_runsch.operator_id
                AND t_act.day = t_runsch.day
        )
AND notrun_droppedoff = 0
AND notrun_rblater = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
*/

$sql="
select t_runsch.rowid rw, t_act.pub_ttb_id
from t_runsch, outer t_act
WHERE t_act.operator_id = t_runsch.operator_id
AND t_act.day = t_runsch.day
AND t_act.pub_ttb_id = t_runsch.pub_ttb_id
INTO TEMP t_nrtrips WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
select rw
from t_nrtrips
WHERE pub_ttb_id is null
INTO TEMP t_nrtrips1 WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
UPDATE t_runsch SET ( notrun_skipped ) = ( 1 ) WHERE rowid IN ( SELECT rw FROM t_nrtrips1 )
AND notrun_droppedoff = 0 AND notrun_rblater = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
SELECT runstat, t_act.operator_id, t_act.day, t_act.pub_ttb_id, 0 lastveh, 0 firstveh, publish_tt.runningno, start_time, t_act.route_id, sum(act_total) act_total, count(*) actcount, min(vehicle_id) minveh, max(vehicle_id) maxveh ,max(maxord) maxord, min(minord) minord, max(maxarr) maxarr, min(minarr) minarr FROM t_act, publish_tt, t_servct, service WHERE t_act.pub_ttb_id = publish_tt.pub_ttb_id AND publish_tt.service_id = t_servct.service_id AND publish_tt.service_id = service.service_id AND t_act.day = t_servct.day GROUP BY 1,2,3,4,5,6,7,8,9 INTO TEMP t_runact with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX i_t_runact ON t_runact ( operator_id, runningno, day );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX i_t_runact1 ON t_runact ( pub_ttb_id );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
update t_runact set ( act_total, actcount ) = ( act_total / actcount, 1 ) 
where actcount > 1;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
SELECT t_act.operator_id, t_act.day, t_act.pub_ttb_id, count(*) act06count
FROM t_act, publish_tt, t_servct, service
WHERE t_act.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.service_id = t_servct.service_id
AND publish_tt.service_id = service.service_id
AND t_act.day = t_servct.day
AND act_total/trip_total > $tol
GROUP BY 1,2,3 INTO TEMP t_act06 with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX i_t_act06 ON t_act06 ( day, pub_ttb_id );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();

$sql="
SELECT t_act.operator_id, t_act.day, t_act.pub_ttb_id, count(*) act01count
FROM t_act, publish_tt, t_servct, service
WHERE t_act.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.service_id = t_servct.service_id
AND publish_tt.service_id = service.service_id
AND t_act.day = t_servct.day
AND act_total/trip_total > $tol2
GROUP BY 1,2,3 INTO TEMP t_act01 with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
CREATE INDEX i_t_act01 ON t_act01 ( day, pub_ttb_id );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();

if ( $debug ) echo $sql."<br><br>";

if ( $detail == "TRIP" )
{
    $runsumcol = "publish_tt.runningno";
    $routesumcol = "t_runsch.route_id";
    $tripsumcol = "t_runsch.pub_ttb_id";
    $daycol = "'ALL'";
    $daycol2 = "'ALL'";
}
if ( $detail == "RB" )
{
    $tripsumcol = "0";
    $routesumcol = "route_id";
    $runsumcol = "runningno";
    $daycol = "t_tripsum_prep.day";
    $daycol2 = "t_opmap.day";
}
if ( $detail == "OP" )
{
    $tripsumcol = "0";
    $runsumcol = "'ALL'";
    $routesumcol = "0";
    $daycol = "'ALL'";
    $daycol2 = "'ALL'";
}

if ( $detail == "ROUTE" )
{
    $tripsumcol = "0";
    $runsumcol = "runningno";
    $routesumcol = "0";
    $daycol = "'ALL'";
    $daycol2 = "'ALL'";
}

if ( $detail == "RB" || $detail == "OP" || $detail == "ROUTE" )
{
$sql="
select t_runsch.operator_code,
t_runsch.operator_id,
t_runsch.day,
t_runsch.route_id,
publish_tt.runningno || '        ' runningno,
0 pub_ttb_id,
sum(schcount) schcount,
sum(schcount) schcountcalc,
0 untrackedrbtrips,
sum(act_total) act_total,
sum(trip_total) trip_total,
sum(minord - 1) start_gap,
sum(trip_total - maxord)  end_gap,
sum(trip_total - act_total - ( minord - 1 ) - ( trip_total - maxord )) mid_gap,
sum(actcount) actcount,
sum(act01count) act01count,
sum(act06count) act06count,
sum(actcount - act06count) trackedbadly,
min(minveh) minveh, 
max(maxveh) maxveh, 
sum(notrun_rblater) notrun_rblater,
sum(notrun_droppedoff) notrun_droppedoff,
sum(notrun_skipped) notrun_skipped,
0 trip_performance,
0 board_performance,
0 boards_run
from
t_runsch, publish_tt, t_servct, outer ( t_runact, outer t_act06, outer t_act01 )
where t_runsch.operator_id = t_runact.operator_id
and t_runsch.day = t_runact.day
and t_runsch.pub_ttb_id = t_runact.pub_ttb_id
and t_runsch.pub_ttb_id = publish_tt.pub_ttb_id
and publish_tt.service_id = t_servct.service_id
and t_runsch.day = t_servct.day
and t_runact.day = t_act06.day
and t_runact.operator_id = t_act06.operator_id
and t_runact.pub_ttb_id = t_act06.pub_ttb_id
and t_runact.day = t_act01.day
and t_runact.operator_id = t_act01.operator_id
and t_runact.pub_ttb_id = t_act01.pub_ttb_id
group by 1,2,3,4,5,6
into temp t_tripsum_prep
with no log
;
";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
update t_tripsum_prep set ( schcountcalc, notrun_rblater, notrun_droppedoff, notrun_skipped ) = ( 0, 0, 0, 0 )
where actcount = 0 or actcount is null;";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
update t_tripsum_prep set untrackedrbtrips = schcount
where schcountcalc = 0;";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


}
else
{
$sql="
select t_runsch.operator_code,
t_runsch.operator_id,
t_runsch.day,
$routesumcol route_id,
$runsumcol runningno,
$tripsumcol pub_ttb_id,
sum(schcount) schcount,
sum(schcount) schcountcalc,
sum(schcount - schcount) untrackedrbtrips,
sum(act_total) act_total,
sum(trip_total) trip_total,
sum(minord - 1) start_gap,
sum(trip_total - maxord)  end_gap,
sum(trip_total - act_total - ( minord - 1 ) - ( trip_total - maxord )) mid_gap,
sum(actcount) actcount,
sum(act01count) act01count,
sum(act06count) act06count,
sum(actcount - act06count) trackedbadly,
min(minveh) minveh, 
max(maxveh) maxveh, 
sum(notrun_rblater) notrun_rblater,
sum(notrun_droppedoff) notrun_droppedoff,
sum(notrun_skipped) notrun_skipped,
0 trip_performance,
0 board_performance,
0 boards_run
from
t_runsch, publish_tt, t_servct, outer ( t_runact, outer t_act06 )
where t_runsch.operator_id = t_runact.operator_id
and t_runsch.day = t_runact.day
and t_runsch.pub_ttb_id = t_runact.pub_ttb_id
and t_runsch.pub_ttb_id = publish_tt.pub_ttb_id
and publish_tt.service_id = t_servct.service_id
and t_runsch.day = t_servct.day
and t_runact.day = t_act06.day
and t_runact.operator_id = t_act06.operator_id
and t_runact.pub_ttb_id = t_act06.pub_ttb_id
and t_runact.day = t_act01.day
and t_runact.operator_id = t_act01.operator_id
and t_runact.pub_ttb_id = t_act01.pub_ttb_id
group by 1,2,3,4,5,6
into temp t_tripsum_prep
with no log
;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

$sql="
update t_tripsum_prep set ( start_gap, end_gap, act_total ) = ( trip_total , end_gap, 0 ) where act_total is null;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
$sql="
update t_tripsum_prep set ( actcount ) = ( 0 ) where actcount is null;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( act01count ) = ( 0 ) where act01count is null;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( act06count ) = ( 0 ) where act06count is null;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( trip_performance) = ( ( act06count * 100 ) / actcount ) where actcount > 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( board_performance) = ( ( act06count * 100 ) / schcountcalc ) where actcount > 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( boards_run ) = ( ( actcount * 100 ) / schcountcalc ) where actcount > 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( notrun_rblater ) = ( NULL )
WHERE notrun_rblater = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( trackedbadly ) = ( NULL )
WHERE trackedbadly = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( untrackedrbtrips ) = ( NULL )
WHERE untrackedrbtrips = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
update t_tripsum_prep set ( notrun_droppedoff ) = ( NULL )
WHERE notrun_droppedoff = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
update t_tripsum_prep set ( notrun_skipped ) = ( NULL )
WHERE notrun_skipped = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql = "
CREATE TEMP TABLE t_opmap
(
 operator_id INTEGER,
  day         DATE,
  runningno   CHAR(10),
  route_id    CHAR(10),
  minveh      INTEGER,
  maxveh      INTEGER,
  tp00        INTEGER, tp01 INTEGER, tp02 INTEGER, tp03 INTEGER,
  tp04        INTEGER, tp05 INTEGER, tp06 INTEGER, tp07 INTEGER,
  tp08        INTEGER, tp09 INTEGER, tp10 INTEGER, tp11 INTEGER,
  tp12        INTEGER, tp13 INTEGER, tp14 INTEGER, tp15 INTEGER,
  tp16        INTEGER, tp17 INTEGER, tp18 INTEGER, tp19 INTEGER,
  tp20        INTEGER, tp21 INTEGER, tp22 INTEGER, tp23 INTEGER,
  op00        INTEGER, op01 INTEGER, op02 INTEGER, op03 INTEGER,
  op04        INTEGER, op05 INTEGER, op06 INTEGER, op07 INTEGER,
  op08        INTEGER, op09 INTEGER, op10 INTEGER, op11 INTEGER,
  op12        INTEGER, op13 INTEGER, op14 INTEGER, op15 INTEGER,
  op16        INTEGER, op17 INTEGER, op18 INTEGER, op19 INTEGER,
  op20        INTEGER, op21 INTEGER, op22 INTEGER, op23 INTEGER,
  dr00        INTEGER, dr01 INTEGER, dr02 INTEGER, dr03 INTEGER,
  dr04        INTEGER, dr05 INTEGER, dr06 INTEGER, dr07 INTEGER,
  dr08        INTEGER, dr09 INTEGER, dr10 INTEGER, dr11 INTEGER,
  dr12        INTEGER, dr13 INTEGER, dr14 INTEGER, dr15 INTEGER,
  dr16        INTEGER, dr17 INTEGER, dr18 INTEGER, dr19 INTEGER,
  dr20        INTEGER, dr21 INTEGER, dr22 INTEGER, dr23 INTEGER,
  de00        INTEGER, de01 INTEGER, de02 INTEGER, de03 INTEGER,
  de04        INTEGER, de05 INTEGER, de06 INTEGER, de07 INTEGER,
  de08        INTEGER, de09 INTEGER, de10 INTEGER, de11 INTEGER,
  de12        INTEGER, de13 INTEGER, de14 INTEGER, de15 INTEGER,
  de16        INTEGER, de17 INTEGER, de18 INTEGER, de19 INTEGER,
  de20        INTEGER, de21 INTEGER, de22 INTEGER, de23 INTEGER,
  ne00        INTEGER, ne01 INTEGER, ne02 INTEGER, ne03 INTEGER,
  ne04        INTEGER, ne05 INTEGER, ne06 INTEGER, ne07 INTEGER,
  ne08        INTEGER, ne09 INTEGER, ne10 INTEGER, ne11 INTEGER,
  ne12        INTEGER, ne13 INTEGER, ne14 INTEGER, ne15 INTEGER,
  ne16        INTEGER, ne17 INTEGER, ne18 INTEGER, ne19 INTEGER,
  ne20        INTEGER, ne21 INTEGER, ne22 INTEGER, ne23 INTEGER,
  or00        INTEGER, or01 INTEGER, or02 INTEGER, or03 INTEGER,
  or04        INTEGER, or05 INTEGER, or06 INTEGER, or07 INTEGER,
  or08        INTEGER, or09 INTEGER, or10 INTEGER, or11 INTEGER,
  or12        INTEGER, or13 INTEGER, or14 INTEGER, or15 INTEGER,
  or16        INTEGER, or17 INTEGER, or18 INTEGER, or19 INTEGER,
  or20        INTEGER, or21 INTEGER, or22 INTEGER, or23 INTEGER,
  gp00        INTEGER, gp01 INTEGER, gp02 INTEGER, gp03 INTEGER,
  gp04        INTEGER, gp05 INTEGER, gp06 INTEGER, gp07 INTEGER,
  gp08        INTEGER, gp09 INTEGER, gp10 INTEGER, gp11 INTEGER,
  gp12        INTEGER, gp13 INTEGER, gp14 INTEGER, gp15 INTEGER,
  gp16        INTEGER, gp17 INTEGER, gp18 INTEGER, gp19 INTEGER,
  gp20        INTEGER, gp21 INTEGER, gp22 INTEGER, gp23 INTEGER,
  mp00        CHAR(1), mp01 CHAR(1), mp02 CHAR(1), mp03 CHAR(1),
  mp04        CHAR(1), mp05 CHAR(1), mp06 CHAR(1), mp07 CHAR(1),
  mp08        CHAR(1), mp09 CHAR(1), mp10 CHAR(1), mp11 CHAR(1),
  mp12        CHAR(1), mp13 CHAR(1), mp14 CHAR(1), mp15 CHAR(1),
  mp16        CHAR(1), mp17 CHAR(1), mp18 CHAR(1), mp19 CHAR(1),
  mp20        CHAR(1), mp21 CHAR(1), mp22 CHAR(1), mp23 CHAR(1),
        optmp       INTEGER,
        mptmp       CHAR(1),
        map         CHAR(24),
        detot       INTEGER,
        drtot       INTEGER,
        gptot       INTEGER,
        ortot       INTEGER,
        netot       INTEGER,
        ottot       INTEGER
) WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
INSERT INTO t_opmap
( operator_id, day, runningno, route_id,
        mp00, mp01, mp02, mp03,
        mp04, mp05, mp06, mp07,
        mp08, mp09, mp10, mp11,
        mp12, mp13, mp14, mp15,
        mp16, mp17, mp18, mp19,
        mp20, mp21, mp22, mp23,
        dr00, dr01, dr02, dr03,
        dr04, dr05, dr06, dr07,
        dr08, dr09, dr10, dr11,
        dr12, dr13, dr14, dr15,
        dr16, dr17, dr18, dr19,
        dr20, dr21, dr22, dr23,
        or00, or01, or02, or03,
        or04, or05, or06, or07,
        or08, or09, or10, or11,
        or12, or13, or14, or15,
        or16, or17, or18, or19,
        or20, or21, or22, or23,
        de00, de01, de02, de03,
        de04, de05, de06, de07,
        de08, de09, de10, de11,
        de12, de13, de14, de15,
        de16, de17, de18, de19,
        de20, de21, de22, de23,
        ne00, ne01, ne02, ne03,
        ne04, ne05, ne06, ne07,
        ne08, ne09, ne10, ne11,
        ne12, ne13, ne14, ne15,
        ne16, ne17, ne18, ne19,
        ne20, ne21, ne22, ne23,
        gp00, gp01, gp02, gp03,
        gp04, gp05, gp06, gp07,
        gp08, gp09, gp10, gp11,
        gp12, gp13, gp14, gp15,
        gp16, gp17, gp18, gp19,
        gp20, gp21, gp22, gp23,
        detot,      
        drtot, gptot, ottot
)
SELECT UNIQUE operator_id, day, runningno || '        ', route_id, '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.', '.',
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0
FROM t_runsch;
CREATE INDEX i_t_opmap ON t_opmap ( operator_id, runningno, day );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;

$sql = "
UPDATE t_opmap SET optmp = ( SELECT count(*) FROM t_runsch WHERE t_runsch.operator_id = t_opmap.operator_id
        AND t_runsch.runningno = t_opmap.runningno
        AND t_runsch.route_id = t_opmap.route_id
        AND t_runsch.day = t_opmap.day AND extend(start_time, hour to hour) = '$sval');
UPDATE t_opmap SET op$sval = optmp WHERE optmp > 0 AND optmp IS NOT NULL;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}


for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;

$sql = "
UPDATE t_opmap SET optmp = ( SELECT count(*) FROM t_runact WHERE t_runact.operator_id = t_opmap.operator_id
        AND t_runact.runningno = t_opmap.runningno
        AND t_runact.route_id = t_opmap.route_id
        AND t_runact.day = t_opmap.day AND extend(start_time, hour to hour) = '$sval');
UPDATE t_opmap SET op$sval = op$sval - optmp WHERE optmp > 0 AND optmp IS NOT NULL;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}

$sql = "
UPDATE t_opmap SET optmp = ( SELECT untrackedrbtrips FROM t_tripsum_prep WHERE t_tripsum_prep.operator_id = t_opmap.operator_id
        AND t_tripsum_prep.runningno = t_opmap.runningno
        AND t_tripsum_prep.route_id = t_opmap.route_id
        AND t_tripsum_prep.day = t_opmap.day 
        AND t_tripsum_prep.untrackedrbtrips IS NOT NULL
        AND t_tripsum_prep.untrackedrbtrips > 0 );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
$sql = "
UPDATE t_opmap SET op$sval = 0 WHERE optmp > 0 AND optmp IS NOT NULL;";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}


for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
$sql = "
UPDATE t_opmap SET mp$sval = ':' WHERE op$sval = 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
$sql = "
UPDATE t_opmap SET mp$sval = '!' WHERE op$sval > 0;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

$sql = "
UPDATE t_opmap SET minveh = ( SELECT min(minveh) FROM t_runact WHERE t_runact.operator_id = t_opmap.operator_id
        AND t_runact.runningno = t_opmap.runningno
        AND t_runact.route_id = t_opmap.route_id
        AND t_runact.day = t_opmap.day);
UPDATE t_opmap SET maxveh = ( SELECT max(maxveh) FROM t_runact WHERE t_runact.operator_id = t_opmap.operator_id
        AND t_runact.runningno = t_opmap.runningno
        AND t_runact.route_id = t_opmap.route_id
        AND t_runact.day = t_opmap.day);
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
create temp table t_hours
(
        hrdt datetime hour to hour,
        hrno integer
)
with no log;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
insert into t_hours values ( '00', 0 );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
insert into t_hours values ( '01', 1 );
insert into t_hours values ( '02', 2 );
insert into t_hours values ( '03', 3 );
insert into t_hours values ( '04', 4 );
insert into t_hours values ( '05', 5 );
insert into t_hours values ( '06', 6 );
insert into t_hours values ( '07', 7 );
insert into t_hours values ( '08', 8 );
insert into t_hours values ( '09', 9 );
insert into t_hours values ( '10', 10 );
insert into t_hours values ( '11', 11 );
insert into t_hours values ( '12', 12);
insert into t_hours values ( '13', 13 );
insert into t_hours values ( '14', 14 );
insert into t_hours values ( '15', 15 );
insert into t_hours values ( '16', 16 );
insert into t_hours values ( '17', 17 );
insert into t_hours values ( '18', 18 );
insert into t_hours values ( '19', 19 );
insert into t_hours values ( '20', 20 );
insert into t_hours values ( '21', 21 );
insert into t_hours values ( '22', 22 );
insert into t_hours values ( '23', 23 );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
SELECT UNIQUE minveh vehicle_id, build_id
FROM t_opmap, vehicle
WHERE minveh IS NOT NULL
AND t_opmap.minveh = vehicle.vehicle_id
UNION
SELECT UNIQUE maxveh vehicle_id, build_id
FROM t_opmap, vehicle
WHERE maxveh IS NOT NULL
AND t_opmap.maxveh = vehicle.vehicle_id
INTO TEMP t_vehs;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";



if ( $uv )
{
$sql = "
SELECT UNIQUE vehicle_id, unit_build.build_id, t_days.day, minveh actveh
FROM vehicle, unit_build, t_days, outer t_tripsum_prep
WHERE vehicle.build_id = unit_build.build_id
AND t_tripsum_prep.minveh = vehicle.vehicle_id
AND t_tripsum_prep.day = t_days.day
UNION ALL
SELECT UNIQUE vehicle_id, unit_build.build_id, t_days.day, maxveh actveh
FROM vehicle, unit_build, t_days, outer t_tripsum_prep
WHERE vehicle.build_id = unit_build.build_id
AND t_tripsum_prep.maxveh = vehicle.vehicle_id
AND t_tripsum_prep.day = t_days.day
INTO TEMP t_veh_days;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
DELETE FROM t_veh_days WHERE actveh IS NOT NULL
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
INSERT INTO t_tripsum_prep
select operator_code, operator.operator_id, day, 0, '?' || vehicle.vehicle_code, 0, 
999, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, vehicle.vehicle_id, 0, 0, 0, 0, 0, 0, 0
FROM t_veh_days, vehicle, operator, unit_build
WHERE 1 = 1
AND t_veh_days.vehicle_id = vehicle.vehicle_id
AND vehicle.build_id = unit_build.build_id
AND operator.operator_id = vehicle.operator_id
";

if ( !$vh )
{
$sql .= " AND operator.operator_id IN ( SELECT UNIQUE operator_id FROM t_runsch )
";
}

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
INSERT INTO t_opmap
( operator_id, day, runningno, minveh,
        op00, op01, op02, op03,
        op04, op05, op06, op07,
        op08, op09, op10, op11,
        op12, op13, op14, op15,
        op16, op17, op18, op19,
        op20, op21, op22, op23,
        mp00, mp01, mp02, mp03,
        mp04, mp05, mp06, mp07,
        mp08, mp09, mp10, mp11,
        mp12, mp13, mp14, mp15,
        mp16, mp17, mp18, mp19,
        mp20, mp21, mp22, mp23,
        dr00, dr01, dr02, dr03,
        dr04, dr05, dr06, dr07,
        dr08, dr09, dr10, dr11,
        dr12, dr13, dr14, dr15,
        dr16, dr17, dr18, dr19,
        dr20, dr21, dr22, dr23,
        or00, or01, or02, or03,
        or04, or05, or06, or07,
        or08, or09, or10, or11,
        or12, or13, or14, or15,
        or16, or17, or18, or19,
        or20, or21, or22, or23,
        de00, de01, de02, de03,
        de04, de05, de06, de07,
        de08, de09, de10, de11,
        de12, de13, de14, de15,
        de16, de17, de18, de19,
        de20, de21, de22, de23,
        ne00, ne01, ne02, ne03,
        ne04, ne05, ne06, ne07,
        ne08, ne09, ne10, ne11,
        ne12, ne13, ne14, ne15,
        ne16, ne17, ne18, ne19,
        ne20, ne21, ne22, ne23,
        gp00, gp01, gp02, gp03,
        gp04, gp05, gp06, gp07,
        gp08, gp09, gp10, gp11,
        gp12, gp13, gp14, gp15,
        gp16, gp17, gp18, gp19,
        gp20, gp21, gp22, gp23,
        detot,      
        drtot, gptot, ottot
)
SELECT UNIQUE vehicle.operator_id, day, '?' || vehicle.vehicle_code, 
vehicle.vehicle_id,
1, 1, 1, 1, 1, 1, 1, 1, 
1, 1, 1, 1, 1, 1, 1, 1, 
1, 1, 1, 1, 1, 1, 1, 1, 
'!', '!', '!', '!', '!', '!', '!', '!', 
'!', '!', '!', '!', '!', '!', '!', '!', 
'!', '!', '!', '!', '!', '!', '!', '!', 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0, 0, 0, 0, 0, 
0, 0, 0, 0
FROM t_veh_days, vehicle, unit_build
WHERE 1 = 1
AND t_veh_days.vehicle_id = vehicle.vehicle_id
AND vehicle.build_id = unit_build.build_id
";

if ( !$vh )
{
$sql .= " AND vehicle.operator_id IN ( SELECT UNIQUE operator_id FROM t_runsch )
";
}

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";




$sql = "
INSERT INTO t_vehs
SELECT UNIQUE vehicle_id, vehicle.build_id
FROM vehicle, unit_build
WHERE vehicle.build_id = unit_build.build_id
AND vehicle_id NOT IN ( SELECT vehicle_id FROM t_vehs )
";

if ( !$vh )
{
$sql .= "
AND vehicle.operator_id IN ( SELECT UNIQUE operator_id FROM t_runsch )
";
}

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}




$sql = "
SELECT t_days.day, t_hours.hrdt, vehicle_id, 
msg_rec, 
msg_offroute,
msg_onroute,
msg_etm,
msg_etmfail,
msg_heartbeat,
msg_other
FROM t_days, t_hours, t_vehs, outer unit_message_hr
WHERE date(unit_message_hr.dayno) = day
AND unit_message_hr.day_hour = t_hours.hrdt
AND t_vehs.build_id = unit_message_hr.build_id
INTO TEMP t_vehstats WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
CREATE INDEX i_t_vehstats ON t_vehstats ( vehicle_id, day, hrdt );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";



$sql = "
UPDATE t_vehstats SET msg_rec = 0 WHERE msg_rec IS NULL;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


// Flag GPRS Gaps
for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
  $sql = "
UPDATE t_opmap SET optmp = ( SELECT SUM(msg_rec)
        FROM t_vehstats WHERE ( t_vehstats.vehicle_id = t_opmap.minveh or t_vehstats.vehicle_id = t_opmap.maxveh )
        AND t_vehstats.day = t_opmap.day 
        AND t_vehstats.hrdt = '$sval' );
UPDATE t_opmap SET gp$sval = op$sval WHERE optmp < 5 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET ( tp$sval, op$sval ) = ( 10002, op$sval - gp$sval ) WHERE optmp < 5 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET mp$sval = 'G' WHERE tp$sval = 10002;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

// Flag Driver Entry Issues
for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
  $sql = "
UPDATE t_opmap SET optmp = ( SELECT SUM(msg_etmfail)
        FROM t_vehstats WHERE ( t_vehstats.vehicle_id = t_opmap.minveh or t_vehstats.vehicle_id = t_opmap.maxveh )
        AND t_vehstats.day = t_opmap.day 
        AND t_vehstats.hrdt = '$sval' );
UPDATE t_opmap SET de$sval = op$sval WHERE optmp > 5 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET ( tp$sval, op$sval ) = ( 10003, op$sval - de$sval ) WHERE optmp > 5 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET mp$sval = 'd' WHERE tp$sval = 10003;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

// Flag Driver Entry Issues
for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
  $sql = "
UPDATE t_opmap SET optmp = ( SELECT SUM(msg_etmfail+msg_etm)
        FROM t_vehstats WHERE ( t_vehstats.vehicle_id = t_opmap.minveh or t_vehstats.vehicle_id = t_opmap.maxveh )
        AND t_vehstats.day = t_opmap.day 
        AND t_vehstats.hrdt = '$sval' );
UPDATE t_opmap SET ne$sval = op$sval WHERE optmp = 0 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET ( tp$sval, op$sval ) = ( 10004, op$sval - ne$sval ) WHERE optmp = 0 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET mp$sval = 'E' WHERE tp$sval = 10004;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

// Flag OffRoute Issues
for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;
  $sql = "
UPDATE t_opmap SET optmp = ( SELECT SUM(msg_offroute)
        FROM t_vehstats WHERE ( t_vehstats.vehicle_id = t_opmap.minveh or t_vehstats.vehicle_id = t_opmap.maxveh )
        AND t_vehstats.day = t_opmap.day 
        AND t_vehstats.hrdt = '$sval' );
UPDATE t_opmap SET or$sval = op$sval WHERE optmp > 10 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET ( tp$sval, or$sval ) = ( 10005, op$sval - or$sval ) WHERE optmp > 10 AND optmp IS NOT NULL AND op$sval > 0;
UPDATE t_opmap SET mp$sval = 'O' WHERE tp$sval = 10005;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}






for ( $ct = 0; $ct < 24; $ct ++ )
{
  $sval = "$ct";
  if ( strlen($sval) == 1 )
  $sval = "0".$sval;

$sql = "
UPDATE t_opmap SET op$sval = 0 WHERE op$sval IS NULL;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}

// Prepate statusmap
$sql = "
UPDATE t_opmap SET map =
        mp00 || mp01 || mp02 || mp03 || mp04 || mp05 || mp06 || mp07 || mp08 || mp09 ||
        mp10 || mp11 || mp12 || mp13 || mp14 || mp15 || mp16 || mp17 || mp18 || mp19 ||
        mp20 || mp21 || mp22 || mp23;
UPDATE t_opmap SET drtot =
        dr00 + dr01 + dr02 + dr03 + dr04 + dr05 + dr06 + dr07 + dr08 + dr09 +
        dr10 + dr11 + dr12 + dr13 + dr14 + dr15 + dr16 + dr17 + dr18 + dr19 +
        dr20 + dr21 + dr22 + dr23;
UPDATE t_opmap SET ottot =
        op00 + op01 + op02 + op03 + op04 + 
        op05 + op06 + op07 + op08 + op09 +
        op10 + op11 + op12 + op13 + op14 + 
        op15 + op16 + op17 + op18 + op19 +
        op20 + op21 + op22 + op23;
UPDATE t_opmap SET gptot =
        gp00 + gp01 + gp02 + gp03 + gp04 + 
        gp05 + gp06 + gp07 + gp08 + gp09 +
        gp10 + gp11 + gp12 + gp13 + gp14 + 
        gp15 + gp16 + gp17 + gp18 + gp19 +
        gp20 + gp21 + gp22 + gp23;
UPDATE t_opmap SET ortot =
        or00 + or01 + or02 + or03 + or04 + 
        or05 + or06 + or07 + or08 + or09 +
        or10 + or11 + or12 + or13 + or14 + 
        or15 + or16 + or17 + or18 + or19 +
        or20 + or21 + or22 + or23;
UPDATE t_opmap SET netot =
        ne00 + ne01 + ne02 + ne03 + ne04 + 
        ne05 + ne06 + ne07 + ne08 + ne09 +
        ne10 + ne11 + ne12 + ne13 + ne14 + 
        ne15 + ne16 + ne17 + ne18 + ne19 +
        ne20 + ne21 + ne22 + ne23;
UPDATE t_opmap SET detot =
        de00 + de01 + de02 + de03 + de04 + 
        de05 + de06 + de07 + de08 + de09 +
        de10 + de11 + de12 + de13 + de14 + 
        de15 + de16 + de17 + de18 + de19 +
        de20 + de21 + de22 + de23;
UPDATE t_opmap SET map = NULL WHERE minveh IS NULL;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "
SELECT $daycol day, operator_id, runningno, sum(msg_rec) tot_msgrec, sum(msg_offroute) tot_msgoffroute,
sum(msg_onroute) total_onroute,
sum(msg_etm) total_etm_ok,
sum(msg_etmfail) total_etm_fail,
sum(msg_heartbeat) total_heartbeat,
sum(msg_other) total_other
FROM t_tripsum_prep, t_vehstats
WHERE t_tripsum_prep.day = t_vehstats.day
AND (
t_tripsum_prep.minveh = t_vehstats.vehicle_id OR
t_tripsum_prep.maxveh = t_vehstats.vehicle_id )
GROUP BY 1, 2, 3
INTO TEMP t_rbstats WITH NO LOG
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

// In Exception mode clear out data with perfect running
if ( $exc )
{
$sql = "
DELETE FROM t_tripsum_prep 
WHERE act06count = schcount
AND act06count IS NOT NULL
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}

if ( $str && $detail == "RB" )
{
$sql = "
DELETE FROM ih_performance_route
WHERE dayno IN ( SELECT day FROM t_days )
AND operator_id IN ( SELECT operator_id FROM t_tripsum_prep );
INSERT INTO ih_performance_route
SELECT
t_tripsum_prep.operator_id,
t_tripsum_prep.day,
t_tripsum_prep.runningno,
t_tripsum_prep.minveh,
t_tripsum_prep.maxveh,
t_tripsum_prep.route_id,
t_opmap.map,
t_tripsum_prep.schcount,
t_tripsum_prep.schcountcalc,
t_tripsum_prep.untrackedrbtrips,
t_tripsum_prep.actcount,
t_tripsum_prep.schcount - t_tripsum_prep.actcount,
t_tripsum_prep.act01count,
t_tripsum_prep.act06count,
actcount - act06count,
t_tripsum_prep.start_gap,
t_tripsum_prep.mid_gap,
t_tripsum_prep.end_gap,
t_tripsum_prep.notrun_rblater,
t_tripsum_prep.notrun_skipped,
t_tripsum_prep.notrun_droppedoff,
t_opmap.detot,
t_opmap.netot,
t_opmap.gptot,
0,
0,
t_opmap.ortot,
t_opmap.ottot,
tot_msgrec,
tot_msgoffroute,
total_etm_ok,
total_etm_fail,
total_heartbeat,
total_other,
t_pub1.outofdate,
t_pub1.timesincewlan,
t_pub1.timesincealive,
t_pub2.outofdate,
t_pub2.timesincewlan,
t_pub2.timesincealive
FROM t_tripsum_prep, t_opmap, outer t_rbstats, outer t_pub t_pub1, outer t_pub t_pub2
WHERE t_tripsum_prep.operator_id = t_opmap.operator_id
AND t_tripsum_prep.day = t_opmap.day
AND t_tripsum_prep.route_id = t_opmap.route_id
AND t_tripsum_prep.runningno = t_opmap.runningno
AND t_tripsum_prep.day = t_rbstats.day
AND t_tripsum_prep.operator_id = t_rbstats.operator_id
AND t_tripsum_prep.runningno = t_rbstats.runningno  
AND t_tripsum_prep.minveh = t_pub1.vehicle_id  
AND t_tripsum_prep.maxveh = t_pub2.vehicle_id 
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

if ( $detail == "RB" || $detail == "OP" || $detail == "ROUTE" )
{
$sql="
select operator_code,
operator_id,
$daycol day,
$runsumcol runningno, 
$routesumcol route_id,
0 pub_ttb_id,
sum(schcount) schcount,
sum(schcountcalc) schcountcalc,
sum(schcount - schcountcalc) untrackedrbtrips,
sum(act_total) act_total,
sum(trip_total) trip_total,
sum(start_gap) start_gap,
sum(end_gap)  end_gap,
sum(mid_gap) mid_gap,
sum(actcount) actcount,
sum(act06count) act06count,
sum(actcount - act06count) trackedbadly,
min(minveh) minveh, 
max(maxveh) maxveh, 
sum(notrun_rblater) notrun_rblater,
sum(notrun_droppedoff) notrun_droppedoff,
sum(notrun_skipped) notrun_skipped,
sum(schcount - actcount) untracked,
0 trip_performance,
0 board_performance,
0 boards_run
from
t_tripsum_prep
group by 1,2,3,4,5,6
into temp t_tripsum
with no log
;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}
else
{
$sql="
select operator_code,
operator_id,
'ANY' day,
runningno runningno, route_id,
pub_ttb_id pub_ttb_id,
sum(schcount) schcount,
sum(schcountcalc) schcountcalc,
sum(schcount - schcountcalc) untrackedrbtrips,
sum(act_total) act_total,
sum(trip_total) trip_total,
sum(start_gap) start_gap,
sum(end_gap)  end_gap,
sum(mid_gap) mid_gap,
sum(actcount) actcount,
sum(act06count) act06count,
sum(actcount - act06count) trackedbadly,
min(minveh) minveh, 
max(maxveh) maxveh, 
sum(notrun_rblater) notrun_rblater,
sum(notrun_droppedoff) notrun_droppedoff,
sum(notrun_skipped) notrun_skipped,
sum(schcount - act06count) untracked,
0 trip_performance,
0 board_performance,
0 boards_run 
from t_tripsum_prep
group by 1,2,3,4,5,6
into temp t_tripsum
with no log
;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

if ( $detail == "OP" )
{
$sql="
update t_opmap set runningno = 'ALL';
update t_opmap set map = ' * ';
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

$sql="
SELECT operator_id,
  $daycol2 day,
  runningno,
  min(map) map,
  sum(drtot) drtot,
  sum(detot) detot,
  sum(gptot) gptot,
  sum(netot) netot,
  sum(ortot) ortot,
  sum(ottot) ottot
FROM t_opmap
GROUP BY 1, 2, 3
INTO TEMP t_opmap_sum WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
UPDATE t_opmap_sum SET drtot = NULL WHERE drtot = 0; 
UPDATE t_opmap_sum SET gptot = NULL WHERE gptot = 0; 
UPDATE t_opmap_sum SET ottot = NULL WHERE ottot = 0; 
UPDATE t_opmap_sum SET detot = NULL WHERE detot = 0; 
UPDATE t_opmap_sum SET ortot = NULL WHERE ortot = 0; 
UPDATE t_opmap_sum SET netot = NULL WHERE netot = 0; 
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
UPDATE t_tripsum SET trip_performance = ( act06count  / actcount ) * 100 WHERE actcount > 0; 
UPDATE t_tripsum SET board_performance = ( act06count  / schcountcalc ) * 100 WHERE schcountcalc > 0; 
UPDATE t_tripsum SET trip_performance = 0 WHERE trip_performance IS NULL; 
UPDATE t_tripsum SET board_performance = 0 WHERE board_performance IS NULL; 

";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

if ( $vh )
{
$sql="
SELECT rowid rw 
FROM t_tripsum
WHERE 
( minveh IS NOT NULL AND
minveh IN ( SELECT vehicle_id FROM VEHICLE
WHERE vehicle_code NOT IN ( $vh )
))
OR minveh IS NULL
OR ( maxveh IS NOT NULL
AND maxveh > 0 
AND maxveh IN ( SELECT vehicle_id FROM VEHICLE
WHERE vehicle_code NOT IN ( $vh )
) )
INTO TEMP t_delveh WITH NO LOG;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql="
DELETE FROM t_tripsum WHERE rowid IN ( SELECT rw FROM t_delveh );
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";
}

$sql="
SELECT t_tripsum.runningno runningno, t_tripsum.schcount schcount, t_tripsum.actcount actcount, veh1.vehicle_code vehicle_code, veh2.vehicle_code minvehcode, act06count act06count, t_tripsum.operator_code operator_code, pbroute.route_code pb_route, t_tripsum.day day, publish_tt.start_time || '' start_time, publish_tt.trip_no trip_no, start_gap || '/' || mid_gap || '/' || end_gap gaps, trip_performance trip_performance, board_performance board_performance, t_tripsum.schcountcalc schcountcalc, notrun_rblater notrun_rblater, notrun_droppedoff notrun_droppedoff, notrun_skipped notrun_skipped, map map, trackedbadly tracked_badly, untrackedrbtrips untrackedrbtrips, tot_msgrec total_messages, tot_msgoffroute total_offroute, total_etm_ok total_etm_ok, total_etm_fail bte_count, total_heartbeat total_heartbeat, total_other total_other, detot drtot, gptot gptot, untracked untracked, ottot ottot, netot netot, ortot ortot, round(untracked * 100 / schcount) perc_untracked, round(untrackedrbtrips * 100 / schcount) perc_untrackedrb, round(trackedbadly * 100 / schcount) perc_tracked_badly, round(detot * 100 / schcount) perc_etm_entry, round(gptot * 100 / schcount) perc_no_gprs, round(netot * 100 / schcount) perc_no_etm, round(ortot * 100 / schcount) perc_offroute, round(ottot * 100 / schcount) perc_unknown, round(actcount * 100 / schcount) perc_tracked, round(act06count * 100 / schcount) perc_trackedwell, round(schcountcalc * 100 / schcount) trackable, t_pub1.outofdate outofdate1, t_pub1.timesincewlan no_wlan1, t_pub1.timesincealive no_gprs1, t_pub2.outofdate outofdate2, t_pub2.timesincewlan no_wlan2, t_pub2.timesincealive no_gprs2, t_sd1.screendowns screendowns1, t_sd2.screendowns screendowns2 FROM t_tripsum, outer route pbroute, outer ( vehicle veh1, vehicle veh2, outer (unit_build, gprs_mapping) ), outer publish_tt, outer t_opmap_sum, outer t_rbstats, outer t_pub t_pub1, outer t_pub t_pub2, outer t_screendown t_sd1, outer t_screendown t_sd2 WHERE 1 = 1 AND t_tripsum.maxveh = veh1.vehicle_id AND t_tripsum.minveh = veh2.vehicle_id AND t_tripsum.route_id = pbroute.route_id AND veh1.build_id = unit_build.build_id AND unit_build.build_id = gprs_mapping.build_id AND t_tripsum.pub_ttb_id = publish_tt.pub_ttb_id AND t_tripsum.operator_id = t_opmap_sum.operator_id AND t_tripsum.day = t_opmap_sum.day AND t_tripsum.runningno = t_opmap_sum.runningno AND t_tripsum.day = t_rbstats.day AND t_tripsum.operator_id = t_rbstats.operator_id AND t_tripsum.runningno = t_rbstats.runningno AND t_tripsum.minveh = t_pub1.vehicle_id AND t_tripsum.maxveh = t_pub2.vehicle_id AND t_tripsum.minveh = t_sd1.vehicle_id AND t_tripsum.maxveh = t_sd2.vehicle_id INTO TEMP t_results WITH NO LOG";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
SELECT 'Screens Down' category, runningno, schcount, actcount, vehicle_code, minvehcode, act06count, operator_code, pb_route, day, start_time, trip_no, 
gaps, trip_performance, board_performance, schcountcalc, notrun_rblater, notrun_droppedoff, notrun_skipped,
 map, tracked_badly, untrackedrbtrips, total_messages, total_offroute, total_etm_ok, bte_count, total_heartbeat,
 total_other, drtot, gptot, untracked, ottot, netot, ortot, perc_untracked, perc_untrackedrb, perc_tracked_badly,
 perc_etm_entry, perc_no_gprs, perc_no_etm, perc_offroute, perc_unknown, perc_tracked, perc_trackedwell, trackable,
 outofdate1, no_wlan1, no_gprs1, outofdate2, no_wlan2, no_gprs2, screendowns1, screendowns2 
FROM t_results 
 WHERE screendowns1 > 2
INTO TEMP t_summ_results WITH NO LOG";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
INSERT INTO t_summ_results
SELECT 'No Wlan' category, runningno, schcount, actcount, vehicle_code, minvehcode, act06count, operator_code, pb_route, day, start_time, trip_no, 
gaps, trip_performance, board_performance, schcountcalc, notrun_rblater, notrun_droppedoff, notrun_skipped,
 map, tracked_badly, untrackedrbtrips, total_messages, total_offroute, total_etm_ok, bte_count, total_heartbeat,
 total_other, drtot, gptot, untracked, ottot, netot, ortot, perc_untracked, perc_untrackedrb, perc_tracked_badly,
 perc_etm_entry, perc_no_gprs, perc_no_etm, perc_offroute, perc_unknown, perc_tracked, perc_trackedwell, trackable,
 outofdate1, no_wlan1, no_gprs1, outofdate2, no_wlan2, no_gprs2, screendowns1, screendowns2 
FROM t_results 
WHERE outofdate1 > 3
";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


if ( $detail == "OP" )
{
for ($ct = 0; $ct < count($this->columns); $ct++)
{

   $col = $this->columns[$ct];
if ( $col->query_name == "runningno" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "schcount" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "actcount" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "vehicle_code" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "minvehcode" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "act06count" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "pb_route" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "day" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "start_time" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_no" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "gaps" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "board_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "notrun_rblater" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_droppedoff" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_skipped" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "map" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "tracked_badly" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "untrackedrbtrips" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_messages" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_offroute" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_etm_ok" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "bte_count" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_heartbeat" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_other" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "drtot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "gptot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "untracked" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "ottot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "runprog" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "netot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "ortot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_untracked" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_untrackedrb" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_tracked_badly" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_etm_entry" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_no_gprs" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_no_etm" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_offroute" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_unknown" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_tracked" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_trackedwell" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "schedtot" ) $col->attributes["column_display"] = "show";

}
}
else
{
for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
if ( $col->query_name == "runningno" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "schcount" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "actcount" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "vehicle_code" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "minvehcode" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "act06count" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "pb_route" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "day" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "start_time" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_no" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "gaps" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_performance" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "board_performance" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_rblater" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_droppedoff" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_skipped" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "map" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "tracked_badly" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "untrackedrbtrips" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "total_messages" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_offroute" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_etm_ok" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "bte_count" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "total_heartbeat" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_other" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "drtot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "gptot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "untracked" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "ottot" ) $col->attributes["column_display"] = "shoshow";
if ( $col->query_name == "runprog" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "netot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "ortot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "perc_untracked" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_untrackedrb" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_tracked_badly" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_etm_entry" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_no_gprs" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_no_etm" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_offroute" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_unknown" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_tracked" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "perc_trackedwell" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "schedtot" ) $col->attributes["column_display"] = "show";

}
}



?>
