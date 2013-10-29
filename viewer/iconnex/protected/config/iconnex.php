<?php 

class iconnex
{
	public $pdo;
	public $stmt;
	public $debug = false;
	public $errorCode = 0;
	public $errorText = "";
	public $errorWithSQL = true;

function iconnex($pdo)
{
    $this->pdo = $pdo;

    global $g_debug_mode;
	if ($g_debug_mode)
		$this->debug = 1;
}

function executeSQL($in_sql, $action_on_error = "LOG")
{
	$this->errorCode = 0;
	$this->errorText = "Operation Successful";

	if ( $this->debug )
	{
		if ( !preg_match("/;/", $in_sql))
		$in_sql .= ";";
		echo $in_sql."<BR>";
	}

    $this->stmt = $this->pdo->query($in_sql);
    if ( !$this->stmt && $action_on_error != "CONTINUE" )
    {
		$this->setPDOError($in_sql);
		if ( $action_on_error == "ERROR" )
		{
			$this->showPDOError($in_sql);
		}
        return ($this->stmt);
    }

	if ( $this->debug )
	{
		echo "Rows Affected ".$this->stmt->rowCount()."<BR><BR>";
    }

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


function dumpSQL($sql)
{   
        $this->show_debug($sql);
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

function fetchFirstResultValue()
{
        $result = $this->stmt->fetch();
        $resultValue = $result[0];
        return $resultValue;
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

function create_temp_times(&$in_from = "00:00:00", &$in_to = "00:00:00")
{
    $sql = "
        CREATE ".$this->sql_temporary()." TABLE t_times
        (
            from_time CHAR(8),
            to_time CHAR(8)
        ) WITH NO LOG";
	$ret = $this->executeSQL($sql);
    if (!$ret) return $ret;

    if ($in_from == "'HALFHOURAGO'")
    {
        $date = new DateTime();
        $date = $date->sub(new DateInterval('PT30M'));
        $in_from = date_format($date, '\'H:i:s\'');
    }

    if ($in_to == "'HALFHOURFROMNOW'")
    {
        $date = new DateTime();
        $date = $date->add(new DateInterval('PT30M'));
        $in_to = date_format($date, '\'H:i:s\'');
    }

    $sql = "
        INSERT INTO t_times
        VALUES
        (
            $in_from,
            $in_to
        )";
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

function build_date_range_table ( $infrom, $into )
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
pub_prof_id integer,
rtpi_prof_id integer
) ".$this->with_no_log();
if ( !$this->executeSQL($sql) ) return false;

$sql = "
INSERT INTO t_timetable
SELECT t_days.day, t_days.dtime, route_code, service_code,
t_route.route_id, operator.operator_code operator_code,
service.service_id, publish_tt.pub_ttb_id pub_ttb_id,
publish_tt.runningno runningno,
event.event_code event_code, event.event_id, ".$this->sql_substring("notes", 0, 1)." over_midnight, 
trip_no, duty_no, operator.operator_id, start_time, pub_prof_id, rtpi_prof_id
FROM operator,route_visibility t_route,service, t_days, publish_tt,event_pattern,event
WHERE 1 = 1
AND usernm = 'dbmaster'
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

return $retval;
}

}


?>
