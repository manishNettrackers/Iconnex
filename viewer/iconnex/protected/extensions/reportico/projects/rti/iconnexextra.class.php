<?php 

include "../../../../../../lib/classes/iconnex.class.php";
include "../../../../../../lib/classes/DataModel.class.php";
include "../../../../../../lib/classes/DataModelColumn.class.php";
include "../../../../../../lib/classes/OperationalPeriod.class.php";
include "../../../../../../lib/classes/EventPattern.class.php";
include "../../../../../../lib/classes/EventProfile.class.php";

class iconnex_extra extends iconnex
{
	public $pdo;
	public $stmt;
	public $debug = false;
	public $errorCode = 0;
	public $errorText = "";
	public $errorWithSQL = true;
    public $operationEventStatus = array();

function iconnex_extra($pdo)
{
    parent::__construct($pdo);
}
    
function getUser()
{
    $user = session_request_item("user", false );
    if ( !$user )
        $user = "admin";

    return $user;
}
    

function remove_excluded_date_trips()
{

$sql = "CREATE TEMPORARY TABLE t_del ( delid integer ) ";
$stmt = $this->executeSQL($sql);
$sql = "SELECT UNIQUE rowid, day, dayno, start_dow, end_dow, pub_ttb_id, holiday_op, holiday_noop, org_working_op, org_working_noop, org_holiday_op, org_holiday_noop, special_days_op, special_days_noop, route_code
FROM t_timetable
";

$stmt = $this->executeSQL($sql);
if ( $stmt)
while ( $row = $stmt->fetch() )
{
    $generate_date = DateTime::createFromFormat("Y-m-d", $row["day"]);
    $hop = $this->isOperationalEvent("holiday_op", $generate_date, $row["holiday_op"], false, false, false);
    $hnoop = $this->isOperationalEvent("holiday_noop", $generate_date, $row["holiday_noop"], false, true, 2);
    $owop = $this->isOperationalEvent("org_working_op", $generate_date, $row["org_working_op"], false, false, false);
    $ownoop = $this->isOperationalEvent("org_working_noop",$generate_date, $row["org_working_noop"], false, true, 2);
    $ohop = $this->isOperationalEvent("org_holiday_op", $generate_date, $row["org_holiday_op"], false, false, false);
    $ohnoop = $this->isOperationalEvent("org_holiday_noop", $generate_date, $row["org_holiday_noop"], false, true, 2);
    $spop = $this->isOperationalEvent("special_days_op", $generate_date, $row["special_days_op"], false, false, false);
    $spnoop = $this->isOperationalEvent("special_days_noop", $generate_date, $row["special_days_noop"], false, true, 2);

    $dowop = false;

    if ( $row["dayno"] >= $row["start_dow"] && $row["dayno"] <= $row["end_dow"] )
    {
        $dowop = true;
    }


    //if ( $dowop ) echo "<BR>222<BR>";
//echo $generate_date->format("Y-m-d H:i:s"). " ".$row["org_working_op"]." = dop:$dowop hop:$hop || $hnoop || $owop || $ownoop || $ohop || $ohnoop || $spop || $spnoop";
    // Dont generate journey if any of the event falags are false
    //if (  ! $hop || !$hnoop || !$owop || !$ownoop || !$ohop || !$ohnoop || !$spop || !$spnoop  ) 
    if ( $dowop )
    {
    //echo "if ".$row["holiday_op"]." ! $dowop ( ".$row["dayno"]." >= ".$row["start_dow"]." && ".$row["dayno"]." <= ".$row["end_dow"]." ) <BR>";
        $res = $dowop || $hop || $owop || $spop;
        //echo "<BR>ALL  $dowop || $hop || $owop || $spop = $res<BR>";
    }

    $op = $dowop;
    if ( $row["holiday_op"] && $hop ) $op = true;
    if ( $row["org_working_op"] && !$owop ) $op = false;
    if ( $row["org_holiday_op"] && !$ohop ) $op = false;
    if ( $row["special_days_op"] && !$spop ) $op = false;

    $oponday = $dowop || $hop || $owop || $spop;
    $oponday = $op;
    if (  !$oponday || !$hnoop || !$ownoop || !$ohnoop  || !$spnoop  )
    {
        $res = $dowop || $hop || $owop || $spop ;
        $sql = "insert into t_del values (".$row["rowid"]." )";
        $this->executeSQL($sql);
    }
    //echo "<BR>";
  
}
        $sql = "DELETE FROM t_timetable WHERE rowid in ( select delid from t_del ) ";
        $this->executeSQL($sql);
}

function isOperationalEvent($type, $testdate, $evprf_id, $over_midnight = false, $testForNonOperation = false, $valueIfUnspecified = false)
{
        if ( !$evprf_id )
            return $valueIfUnspecified;

        $strdate = $testdate->format("Ymd");
        $key = "${type}_${evprf_id}_$strdate";
        if ( isset($this->operationEventStatus[$key]) )
            return $this->operationEventStatus[$key];

        $opprofile = new EventProfile($this);
        $opprofile->evprf_id = $evprf_id;
        $opprofile->load();
        $opprofile->fetchEvents();
        $this->operationEventStatus[$key] = $opprofile->operationalOnDate($testdate, $testForNonOperation, "Y-m-d");
//echo "new $key $type $evprf_id = ".$this->operationEventStatus[$key]."<BR>";
        return $this->operationEventStatus[$key];
}



function build_user_timetable($inuser, $rt, $op, $tp, $rn, $dty, $ftm = "00:00:00", $ttm = "23:59:59", $ttbid = false)
{

$retval = true;

$sql = "CREATE TEMPORARY TABLE t_timetable (
day date,
dayno integer,
dtime DATE,
route_code char(8),
service_code char(14),
route_id integer,
operator_code char(8),
service_id int,
pub_ttb_id int,
runningno char(5),
event_code char(8),
event_id int,
over_midnight char(1),
trip_no char(10),
duty_no char(6),
operator_id integer,
start_time VARCHAR(5),
holiday_op integer,
holiday_noop integer,
org_working_op integer,
org_working_noop integer,
org_holiday_op integer,
org_holiday_noop integer,
special_days_op integer,
special_days_noop integer,
start_dow integer,
end_dow integer
) ";
if ( !$this->executeSQL($sql) ) return false;

echo $sql."<BR>";

$sql = "
INSERT INTO t_timetable
SELECT t_days.day, t_days.dayno, t_days.dtime, route_code, service.description service_code,
t_route.route_id, operator.operator_code operator_code,
service.service_id, publish_tt.pub_ttb_id pub_ttb_id,
publish_tt.runningno runningno,
event.event_code event_code, event.event_id, publish_tt.over_midnight over_midnight, 
trip_no, duty_no, operator.operator_id, start_time,
holiday_op,
holiday_noop,
org_working_op,
org_working_noop,
org_holiday_op,
org_holiday_noop,
special_days_op,
special_days_noop,
rpdy_start, 
rpdy_end
FROM operator,route_visibility t_route,service, t_days, publish_tt,event_pattern,event


WHERE 1 = 1
AND usernm = '$inuser'
AND operator.operator_id = t_route.operator_id
AND t_route.route_id = service.route_id
AND publish_tt.service_id = service.service_id
and publish_tt.evprf_id   = event_pattern.evprf_id
and event_pattern.event_id   = event.event_id
and t_days.day between service.wef_date and service.wet_date

AND start_time between $ftm and $ttm
";
if ( $rt )
    $sql .= " AND t_route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";
if ( $tp )
    $sql .= " AND ( publish_tt.trip_no in ( $tp ) OR  publish_tt.etm_trip_no in ( $tp ) )";
if ( $rn )
    $sql .= " AND publish_tt.runningno matches $rn";
if ( $dty )
    $sql .= " AND publish_tt.duty_no matches $dty";
if ( $ttbid )
    $sql .= " AND publish_tt.pub_ttb_id = $ttbid";

if ( !$this->executeSQL($sql) ) return false;
echo $sql."<BR>";
$sql="
CREATE INDEX ix_tttb1 ON t_timetable ( pub_ttb_id );
";
if ( !$this->executeSQL($sql) ) return false;
echo $sql."<BR>";
$sql="
CREATE INDEX ix_tttb ON t_timetable ( day, pub_ttb_id );
";
if ( !$this->executeSQL($sql) ) return false;
echo $sql."<BR>";
$this->remove_excluded_date_trips();
//$this->dumpSQL("SELECT * from t_timetable");
//$this->dumpSQL("
//SELECT t_timetable.route_code route_code, publish_tt.trip_no trip_no, publish_tt.runningno runningno, publish_tt.start_time start_time, event.event_code event_code, t_timetable.day day, publish_tt.duty_no duty_no, t_timetable.operator_code operator_code, t_timetable.service_code service_code, publish_tt.pub_ttb_id id, etm_trip_no, t_timetable.holiday_op, t_timetable.holiday_noop, t_timetable.org_working_op, t_timetable.org_working_noop, t_timetable.org_holiday_op, t_timetable.org_holiday_noop, t_timetable.special_days_op, t_timetable.special_days_noop FROM t_timetable, publish_tt,event,event_pattern WHERE 1 = 1 AND t_timetable.pub_ttb_id = publish_tt.pub_ttb_id AND publish_tt.evprf_id = event_pattern.evprf_id AND event_pattern.event_id = event.event_id AND t_timetable.event_id = event.event_id ORDER BY t_timetable.day ASC, t_timetable.operator_code ASC, t_timetable.route_code ASC, event.event_code ASC, publish_tt.start_time ASC");
//die;

return $retval;
}

}


?>
