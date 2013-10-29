<?php 

require_once('iconnex.php');

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$op = $_criteria["operator"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);

$debug = false;

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

$sql = "CREATE TEMP TABLE t_ttb_days ( day date );";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$ptr = $ifrom;
while ( $ptr <= $ito + ( 24 * 60 * 60 ) )
{
    $dt = strftime ( "%d/%m/%Y", $ptr );

    $sql = "INSERT INTO t_ttb_days VALUES ( '".$dt."' );";
    $ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

    $ptr = $ptr + ( 24 * 60 * 60 );
}



$sql = "SELECT day, vehicle_id, COUNT(*) screendowns, min(alert_time) min_sdtime, max(alert_time) max_sdtime
FROM vehicle a, unit_build b, unit_alert c, t_days d
WHERE a.build_id = b.build_id
AND b.build_id = c.build_id
AND date(alert_time) = d.day
AND message_type = '513'
AND message_text matches '1*'
";

if ( $op )
    $sql .= " AND a.operator_id in ( $op ) ";

$sql .= " 
GROUP BY 1,2
INTO TEMP t_screendown1
";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";

$sql = "SELECT day, vehicle_id, COUNT(*) screendowns, min(alert_time) min_sdtime, max(alert_time) max_sdtime
FROM vehicle a, unit_build b, unit_alert c, t_days d
WHERE a.build_id = b.build_id
AND b.build_id = c.build_id
AND date(alert_time) = d.day
AND message_type = '513'
AND message_text matches '2*'
";

if ( $op )
    $sql .= " AND a.operator_id in ( $op ) ";

$sql .= " 
GROUP BY 1,2
INTO TEMP t_screendown2 ";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql = "
SELECT b.vehicle_id vehicle_id, today - date(message_time) timesincealive
FROM vehicle b, unit_build c, unit_status k, operator j
WHERE 1 = 1                                      
and b.build_id = c.build_id
and c.build_id = k.build_id
and j.operator_id = c.operator_id 
and ( b.vehicle_id in (select vehicle_id from t_screendown1 )
or  b.vehicle_id in (select vehicle_id from t_screendown2 ) )
INTO TEMP t_veh
";
$ds->Execute($sql) or print $ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
SELECT  operator_code,  vehicle_code, timesincealive, 
t_screendown1.screendowns alerts_screen_1, extend(t_screendown1.min_sdtime, hour to second) first_msg_1,   extend(t_screendown1.max_sdtime, hour to second) last_msg_1,
t_screendown2.screendowns alerts_screen_2, extend(t_screendown2.min_sdtime, hour to second) first_msg_2,   extend(t_screendown2.max_sdtime, hour to second) last_msg_2
FROM operator, vehicle, t_veh, outer t_screendown1, outer t_screendown2
WHERE 1 = 1                                                  
AND vehicle.operator_id = operator.operator_id
AND vehicle.vehicle_id = t_veh.vehicle_id
AND t_veh.vehicle_id = t_screendown1.vehicle_id
AND t_veh.vehicle_id = t_screendown2.vehicle_id
INTO TEMP t_results WITH NO LOG
";

$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
if ( $debug ) echo $sql."<br><br>";


$sql="
DELETE FROM t_results
WHERE screendowns1 IS NULL
AND screendowns2 IS NULL
";

//$ds->Execute($sql) or print $sql."<br>".$ds->ErrorMsg();
//if ( $debug ) echo $sql."<br><br>";

?>

