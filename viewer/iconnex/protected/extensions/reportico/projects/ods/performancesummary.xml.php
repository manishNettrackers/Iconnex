<?php

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$rbd = false;
$exc = false;
$vh = false;

$detail = "OP";

$debug = 0;

if ( $debug ) echo "sum $runsum<br>";

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);

$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );

$sql = "SET ISOLATION TO DIRTY READ;";
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



$sql="
select ih_performance_route.operator_id,
operator.operator_code,
day,
running_no runningno,
minveh minveh,
maxveh maxveh,
0 firstroute,
map map,
scheduled schcount,
sched_trackedrb schcountcalc,
sched_untrackedrb untrackedrbtrips,
tracked actcount,
untracked untracked,
tracked_little act01count,
tracked_well act06count,
tracked_badly trackedbadly,
start_gap start_gap,
mid_gap mid_gap,
end_gap end_gap,
nr_late_start notrun_rblater,
nr_skipped notrun_skipped,
nr_droppped_off notrun_droppedoff,
nr_tripentry detot,
nr_etm netot,
nr_gprs gptot,
nr_offroute ortot,
nr_other ottot,
msg_received tot_msgrec,
msg_offroute tot_msgoffroute,
msg_etmok total_etm_ok,
msg_etmfail total_etm_fail,
msg_heartbeat total_heartbeat,
msg_other total_other,
0 tracked_performance,
0 trip_performance,
0 board_performance,
0 boards_run
from
ih_performance_route, t_days, operator, route_param
WHERE ih_performance_route.dayno = t_days.day
AND route_param.route_id = ih_performance_route.route_id
AND pub_status = 'A'";

if ( $rbd )
    $sql .= " AND runningno in ( $rbd )";
if ( $rt )
    $sql .= " AND ih_performance_route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";

$sql .= "
and ih_performance_route.operator_id = operator.operator_id
into temp t_tripsum_prep
with no log
;
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

// In Exception mode clear out data with perfect running
if ( $vh )
{
$sql = "
DELETE FROM t_tripsum_prep
WHERE minveh not in ( 
select vehicle_id from vehicle
where vehicle_code in ( $vh ) )
or minveh is null;

";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

}



if ( $detail == "RB" )
{
    $runsumcol = "runningno";
    $mapsumcol = "map";
}
if ( $detail == "OP" )
{
    $runsumcol = "'ALL'";
    $mapsumcol = "' '";
}

$sql="
select operator_code,
operator_id,
day,
$runsumcol runningno,
$mapsumcol map,
sum(schcount) schcount,
sum(schcountcalc) schcountcalc,
sum(untrackedrbtrips) untrackedrbtrips,
sum(start_gap) start_gap,
sum(end_gap)  end_gap,
sum(mid_gap) mid_gap,
sum(actcount) actcount,
sum(act01count) act01count,
sum(act06count) act06count,
sum(trackedbadly) trackedbadly,
min(minveh) minveh, 
max(maxveh) maxveh, 
min(firstroute) firstroute,
sum(notrun_rblater) notrun_rblater,
sum(notrun_droppedoff) notrun_droppedoff,
sum(notrun_skipped) notrun_skipped,
sum(untracked) untracked,
sum(detot) detot,
sum(netot) netot,
sum(gptot) gptot,
sum(ortot) ortot,
sum(ottot) ottot,
sum(tot_msgrec) tot_msgrec,
sum(tot_msgoffroute) tot_msgoffroute ,
sum(total_etm_ok) total_etm_ok,
sum(total_etm_fail) total_etm_fail,
sum(total_heartbeat) total_heartbeat ,
sum(total_other) total_other,
0 tracked_performance,
0 trip_performance,
0 board_performance,
0 boards_run
from
t_tripsum_prep
group by 1,2,3,4,5
into temp t_tripsum
with no log
;
";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
UPDATE t_tripsum SET tracked_performance = ( act01count  / schcount ) * 100 WHERE actcount > 0; 
UPDATE t_tripsum SET trip_performance = ( act06count  / actcount ) * 100 WHERE actcount > 0; 
UPDATE t_tripsum SET board_performance = ( act06count  / schcountcalc ) * 100 WHERE schcountcalc > 0; 
UPDATE t_tripsum SET tracked_performance = 0 WHERE tracked_performance IS NULL; 
UPDATE t_tripsum SET trip_performance = 0 WHERE trip_performance IS NULL; 
UPDATE t_tripsum SET board_performance = 0 WHERE board_performance IS NULL; 

";
$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql=" 
UPDATE t_tripsum SET gptot = NULL WHERE gptot = 0; 
UPDATE t_tripsum SET ottot = NULL WHERE ottot = 0; 
UPDATE t_tripsum SET detot = NULL WHERE detot = 0; 
UPDATE t_tripsum SET ortot = NULL WHERE ortot = 0; 
UPDATE t_tripsum SET netot = NULL WHERE netot = 0; 
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
if ( $col->query_name == "day" ) $col->attributes["ColumnWidthHTML"] = "20%";
if ( $col->query_name == "start_time" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_no" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "gaps" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "trip_performance" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "board_performance" ) $col->attributes["column_display"] = "hide";
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
if ( $col->query_name == "runprog" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "netot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "ortot" ) $col->attributes["column_display"] = "hide";
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
if ( $col->query_name == "runprog" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "linkop" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "schedtot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "act01count" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "cum01perc" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "tracked_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "act01tot" ) $col->attributes["column_display"] = "hide";


}
}
else
{
for ($ct = 0; $ct < count($this->columns); $ct++)
{
   $col = $this->columns[$ct];
if ( $col->query_name == "runningno" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "schcount" ) $col->attributes["column_display"] = "hide";
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
if ( $col->query_name == "trip_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "board_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "notrun_rblater" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_droppedoff" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "notrun_skipped" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "map" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "tracked_badly" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "untrackedrbtrips" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "total_messages" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_offroute" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_etm_ok" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "bte_count" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_heartbeat" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "total_other" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "drtot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "gptot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "untracked" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "ottot" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "runprog" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "netot" ) $col->attributes["column_display"] = "show";
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
if ( $col->query_name == "schedtot" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "act01count" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "cum01perc" ) $col->attributes["column_display"] = "hide";
if ( $col->query_name == "tracked_performance" ) $col->attributes["column_display"] = "show";
if ( $col->query_name == "act01tot" ) $col->attributes["column_display"] = "hide";

}
}

?>
