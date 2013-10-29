<?php

$dfrom = $_criteria[&quot;daterange&quot;]-&gt;get_criteria_value(&quot;RANGE1&quot;);
$dto = $_criteria[&quot;daterange&quot;]-&gt;get_criteria_value(&quot;RANGE2&quot;);
$rt = $_criteria[&quot;route&quot;]-&gt;get_criteria_value(&quot;VALUE&quot;);
$op = $_criteria[&quot;operator&quot;]-&gt;get_criteria_value(&quot;VALUE&quot;);
$loc = $_criteria[&quot;location&quot;]-&gt;get_criteria_value(&quot;VALUE&quot;);
$ftm = $_criteria[&quot;fromTime&quot;]-&gt;get_criteria_value(&quot;VALUE&quot;);
$ttm = $_criteria[&quot;toTime&quot;]-&gt;get_criteria_value(&quot;VALUE&quot;);

echo "oo";
require_once('iconnex.php');
$iconnex = new iconnex($_pdo);

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dfrom) ) return false;
if ( !$iconnex->build_user_timetable("dbmaster", $rt, $op, $tp, $rn, $dty, $ftm, $ttm) ) return false;

$sql=&quot;
select service.service_id, max(rpat_orderby) maxord
from service, service_patt
where 1 = 1
and service.service_id = service_patt.service_id
and today &lt;= wet_date
group by 1
into temp t_maxord;
&quot;;
$ds-&gt;Execute($sql) or print $sql.&quot;&lt;br&gt;&quot;.$ds-&gt;ErrorMsg();


$sql=&quot;
SELECT day, t_timetable.pub_ttb_id, publish_time.location_id, 
  extend(pub_time, year to second) -
  extend(current, year to day) + extend(dtime, year to second) +
  over_midnight units day
  exp_time,
  publish_time.rpat_orderby, pub_time
FROM t_timetable, publish_time, publish_tt, service_patt, t_maxord
WHERE t_timetable.pub_ttb_id = publish_time.pub_ttb_id
AND t_timetable.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.service_id = service_patt.service_id
AND service_patt.service_id = t_maxord.service_id
AND service_patt.rpat_orderby &lt; t_maxord.maxord
AND pub_time between $ftm and $ttm
AND publish_time.rpat_orderby = service_patt.rpat_orderby
&quot;;
if ($loc)
$sql .= &quot;AND publish_time.location_id IN ( $loc )&quot;;
$sql .= &quot;
INTO TEMP t_pubtime WITH NO LOG;
&quot;;
$ds-&gt;Execute($sql) or print $sql.&quot;&lt;br&gt;&quot;.$ds-&gt;ErrorMsg();



$sql = &quot;
SELECT t_pubtime.day,
   archive_rt.pub_ttb_id,
   archive_rt.employee_id,
   archive_rt.vehicle_id,
   archive_rt.actual_start,
   archive_rt_loc.*
   FROM archive_rt, archive_rt_loc, t_pubtime
   WHERE 1 = 1
   AND t_pubtime.day = date(archive_rt.actual_start)
   AND archive_rt.pub_ttb_id = t_pubtime.pub_ttb_id
   AND archive_rt.schedule_id = archive_rt_loc.schedule_id
   AND archive_rt_loc.rpat_orderby = t_pubtime.rpat_orderby
AND actual_est != 'C'
AND arrival_status != 'C'
AND departure_status != 'C'
UNION
SELECT t_pubtime.day,
   active_rt.pub_ttb_id,
   active_rt.employee_id,
   active_rt.vehicle_id,
   active_rt.actual_start,
active_rt_loc.schedule_id,
active_rt_loc.rpat_orderby,
active_rt_loc.location_id,
active_rt_loc.actual_est,
active_rt_loc.arrival_time_pub,
active_rt_loc.arrival_time,
active_rt_loc.arrival_status,
active_rt_loc.departure_time_pub,
active_rt_loc.departure_time,
active_rt_loc.departure_status,
active_rt_loc.lateness
   FROM active_rt, active_rt_loc, t_pubtime
   WHERE 1 = 1
   AND t_pubtime.day = date(active_rt.actual_start)
   AND active_rt.pub_ttb_id = t_pubtime.pub_ttb_id
   AND active_rt.schedule_id = active_rt_loc.schedule_id
   AND active_rt_loc.rpat_orderby = t_pubtime.rpat_orderby
AND actual_est != 'C'
AND arrival_status != 'C'
AND departure_status != 'C'
INTO TEMP t_acttime WITH NO LOG;
&quot;;
$ds-&gt;Execute($sql) or print $sql.&quot;&lt;br&gt;&quot;.$ds-&gt;ErrorMsg();


$sql=&quot;
CREATE INDEX ix_tatb ON t_acttime ( day, pub_ttb_id );
&quot;;
$ds-&gt;Execute($sql) or print $sql.&quot;&lt;br&gt;&quot;.$ds-&gt;ErrorMsg();


?>
