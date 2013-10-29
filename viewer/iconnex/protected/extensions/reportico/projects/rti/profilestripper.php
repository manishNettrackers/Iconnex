<?php 

include "../../../../../../lib/classes/iconnex.class.php";
include "../../../../../../lib/classes/DataModel.class.php";
include "../../../../../../lib/classes/EventProfile.class.php";

class iconnex
{
	public $pdo;
	public $stmt;
	public $debug = false;
	public $errorCode = 0;
	public $errorText = "";
	public $errorWithSQL = true;
    public $operationEventStatus = array();

function iconnex($pdo)
{
    $this->pdo = $pdo;

    global $g_debug_mode;
	if ( $g_debug_mode )
		$this->debug = 1;
	
}
    
function getUser()
{
    $user = session_request_item("user", false );
    if ( !$user )
        $user = "admin";

    return $user;
}
    

function executeSQL( $in_sql, $action_on_error = "LOG" )
{
	$this->errorCode = 0;
	$this->errorText = "Operation Successful";

	if ( $this->debug )
	{
		if ( !preg_match("/;/", $in_sql))
		$in_sql .= ";";
		echo $in_sql."<BR>";
	}

//echo "$sql<BR>";
    $this->stmt =  $this->pdo->query($in_sql);
    if ( !$this->stmt && $action_on_error != "CONTINUE" )
    {
//echo "ooops";
		$this->setPDOError($in_sql);
		if ( $action_on_error == "ERROR" )
		{
			$this->showPDOError($in_sql);
		}
        return ( $this->stmt);
    }

	if ( $this->debug )
	{
		echo "Rows Affected ".$this->stmt->rowCount()."<BR><BR>";
    }

//if ( $this->stmt )
//echo "ooops!";
    return $this->stmt;
}

function lastInsertId($tabname)
{
    $sql = "select DBINFO('sqlca.sqlerrd1') lastserial
        from systables a where tabname = '$tabname'";
    $ret = $this->executeSQL($sql, false);
    $row1 = $this->fetch();
    if ( !$row1 )
		return false;
    
    $modid = $row1["lastserial"];
	return $modid;
}


function dumpSQL ( $sql )
{   
        $this->show_debug ( $sql );
        $stat = $this->pdo->query($sql);
        if (!$stat)
        {   
            $info = $this->pdo->errorInfo();
            trigger_error("Error $sql<BR>".$this->pdo->errorCode()." occurred in SQL statement.<BR>".
            $info[2], E_USER_ERROR);
            return $stat;
        }
        else
        {   
            foreach($stat as $row) {
echo "<PRE>";
            print_r ($row);
echo "</PRE>";
            }
        }
        return $stat;
}

function show_debug  ( $txt )
{   
        if ( $this->debug )
        {   
            echo $txt . "<BR>";
        }
}




function fetch()
{
        $result = $this->stmt->fetch();
        return $result;
}

function close()
{
        $this->stmt = null;
}

function showPDOError($in_sql)
{
        $info = $this->pdo->errorInfo();
        $msg =  "$in_sql<BR>Error ".$info[1]."<BR>".
                $info[2];
        trigger_error("$msg");
}

function setPDOError($in_sql)
{
        $info = $this->pdo->errorInfo();
		$this->errorCode = $info[1];
echo "error ".$this->errorCode."<BR>";
		$this->errorText = $info[2];
		if ( $this->errorWithSQL )
			$this->errorText .= "\n\n".$in_sql;
}

function sql_temporary()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" ) return "TEMP";
    else return "TEMPORARY";
}

function with_no_log()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" ) return " WITH NO LOG";
    else return "";
}

function datetime_year_to_day()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" ) return " datetime year to day";
    else return "datetime";
}

function datetime_year_to_second()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" ) return " datetime year to second";
    else return "datetime";
}

function create_temp_times($in_from = "00:00:00", $in_to = "00:00:00")
{

    $sql = "
        CREATE ".$this->sql_temporary()." TABLE t_times
        (
        	from_time CHAR(8),
        	to_time CHAR(8)
        ) WITH NO LOG";
	$ret = $this->executeSQL($sql);
    if ( !$ret ) return $ret;

    $sql = "
        INSERT INTO t_times
        VALUES
        (
        $in_from,
        $in_to
        )
        ";
	return $this->executeSQL($sql);

}

function setDirtyRead()
{
    $sql = "Unknown";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
	    $sql = "SET ISOLATION TO DIRTY READ";
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
	    $sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
	return $this->executeSQL($sql);
}

function build_date_range_table ( $infrom, $into, $weekdays = false )
{
    $retval = true;

    $dfdy = substr($infrom, 1,2);
    $dfmn = substr($infrom, 4,2);
    $dfyr = substr($infrom, 7,4);
    $dtdy = substr($into, 1,2);
    $dtmn = substr($into, 4,2);
    $dtyr = substr($into, 7,4);

    $ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
    $ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );

    $sql = "CREATE ".$this->sql_temporary()." TABLE t_days ( day date, dtime ".$this->datetime_year_to_day()." , dayno integer );";
    if ( !$this->executeSQL( $sql ) ) return;

    $ptr = $ifrom;
    while ( $ptr <= $ito )
    {
        $dt = strftime ( "%d/%m/%Y", $ptr );
        $dtm = strftime ( "%Y-%m-%d", $ptr );

        if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
            $sql = "INSERT INTO t_days VALUES ( '".$dt."', '".$dtm."', 0 );";
        else
            $sql = "INSERT INTO t_days VALUES ( '".$dtm."', '".$dtm."', 0 );";
        if ( !$this->executeSQL( $sql ) ) return;
    
        $ptr = $ptr + ( 24 * 60 * 60 );
    };

    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
	{
    	$sql = "UPDATE t_days SET dayno = WEEKDAY(dtime) + 1";
    	if ( !$this->executeSQL( $sql ) ) return;
    
    	$sql = "UPDATE t_days SET dayno = 0 where dayno = 7";
    	if ( !$this->executeSQL( $sql ) ) return;
	}
	else
	{
    	$sql = "UPDATE t_days SET dayno = WEEKDAY(dtime)";
    	if ( !$this->executeSQL( $sql ) ) return;
	}

	if ( $weekdays )
	{
		$sql ="
		DELETE FROM t_days WHERE WEEKDAY(day) NOT IN ( $weekdays );";
		if ( !$this->executeSQL($sql ) ) return false;
	}


    return $retval;
}


function sql_substring( $col, $start, $offset)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
    {
        $offset = $start + $offset;
        $start += 1;
	    return  "${col} [${start}, ${offset}] ";
    }
    else
	    return  "substring ( $col, $start, $offset ) ";
}

function remove_excluded_date_trips()
{

$sql = "SELECT UNIQUE day, holiday_op, holiday_noop, org_working_op, org_working_noop, org_holiday_op, org_holiday_noop, special_days_op, special_days_noop
FROM t_timetable
";

$stmt = $this->executeSQL($sql);
if ( $stmt)
while ( $row = $stmt->fetch() )
{
    $generate_date = DateTime::createFromFormat("Y:m:d", $row["day"]);
    $hop = $this->isOperationalEvent("holiday_op", $generate_date, $row["holiday_op"], false);
    $hnoop = $this->isOperationalEvent("holiday_noop", $generate_date, $row["holiday_noop"], false, true);
    $owop = $this->isOperationalEvent("org_working_op", $generate_date, $row["org_working_op"], false);
    $ownoop = $this->isOperationalEvent("org_working_noop",$generate_date, $row["org_working_noop"], false, true);
    $ohop = $this->isOperationalEvent("org_holiday_op", $generate_date, $row["org_holiday_op"], false);
    $ohnoop = $this->isOperationalEvent("org_holiday_noop", $generate_date, $row["org_holiday_noop"], false, true);
    $spop = $this->isOperationalEvent("special_days_op", $generate_date, $row["special_days_op"], false);
    $spnoop = $this->isOperationalEvent("special_days_noop", $generate_date, $row["special_days_noop"], false, true);
}
}

function isOperationalEvent($type, $testdate, $evprf_id, $over_midnight = false, $testForNonOperation = false)
{
        if ( !$evprf_id )
            return 2;

        $key = "${type}_$evprf_id";
        if ( isset($this->operationEventStatus[$key]) )
            return $this->operationEventStatus[$key];

        $opprofile = new EventProfile($this->connector);
        $opprofile->evprf_id = $evprf_id;
        $opprofile->load();
        $opprofile->fetchEvents();
        $this->operationEventStatus[$key] = $opprofile->operationalOnDate($testdate, $testForNonOperation);
        return $this->operationEventStatus[$key];
}



function build_user_timetable($inuser, $rt, $op, $tp, $rn, $dty, $ftm = "00:00:00", $ttm = "23:59:59", $ttbid = false)
{

$retval = true;

$sql = "CREATE ".$this->sql_temporary()." TABLE t_timetable (
day date,
dtime ".$this->datetime_year_to_second().",
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
start_time datetime hour to second,
holiday_op integer,
holiday_noop integer,
org_working_op integer,
org_working_noop integer,
org_holiday_op integer,
org_holiday_noop integer,
special_days_op integer,
special_days_noop integer
) ".$this->with_no_log();
if ( !$this->executeSQL($sql) ) return false;

$sql = "
INSERT INTO t_timetable
SELECT t_days.day, t_days.dtime, route_code, service_code,
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
special_days_noop
FROM operator,route_visibility t_route,service, t_days, publish_tt,event_pattern,event


WHERE 1 = 1
AND usernm = '$inuser'
AND operator.operator_id = t_route.operator_id
AND t_route.route_id = service.route_id
AND publish_tt.service_id = service.service_id
and publish_tt.evprf_id   = event_pattern.evprf_id
and event_pattern.event_id   = event.event_id
and t_days.day between service.wef_date and service.wet_date
and t_days.dayno between rpdy_start and rpdy_end
AND start_time between $ftm and $ttm
";
if ( $rt )
    $sql .= " AND t_route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";
if ( $tp )
    $sql .= " AND publish_tt.trip_no in ( $tp )";
if ( $rn )
    $sql .= " AND publish_tt.runningno matches $rn";
if ( $dty )
    $sql .= " AND publish_tt.duty_no matches $dty";
if ( $ttbid )
    $sql .= " AND publish_tt.pub_ttb_id = $ttbid";

if ( !$this->executeSQL($sql) ) return false;

$sql="
CREATE INDEX ix_tttb ON t_timetable ( day, pub_ttb_id );
";
if ( !$this->executeSQL($sql) ) return false;

$this->remove_excluded_date_trips();

return $retval;
}

}


?>
