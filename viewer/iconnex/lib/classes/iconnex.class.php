<?php 

/**
 * iconnex.class.php
 *
 * Database Connectivity Class wrapper surrounding a PDO connection.
 * Provides methods for executing queries, SQL error handling, connection
 * debugging, syntax generation for database abstraction
 * Also establishes and manages PDO connections.
 *
 * @category   lib
 * @author     Peter Deed
 * @copyright  2012 Connexionz UK
 */

class iconnex
{
	public $pdo;
	public $stmt;
	public $debug = false;
	public $errorCode = 0;
	public $errorText = "";
	public $errorWithSQL = true;
	public $lastTrace = 0;

function iconnex($pdo = false)
{
    if ( $pdo )
        $this->pdo = $pdo;

    global $g_debug_mode;
	if ( $g_debug_mode )
		$this->debug = 1;
	
}

function connect($connect_string, $user, $password)
{
    try {
        if ($this->pdo = new PDO($connect_string, $user, $password) )
        {
            $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->executeSQL("SET LOCK MODE TO WAIT 5");
            return true;
        }
        else
        {
            return false;
        }
    }
    catch ( PDOException $ex )
    {
        echo "Failed to connect to ".$connect_string." - \n";
        print($ex->GetMessage());
        printf("\n");
        return false;
    }

}

    
function memory_usage()
{
    echo "Memory: ".memory_get_usage()." / ".memory_get_peak_usage()."\n";
}

function prepareSQL( $in_sql, $action_on_error = "ERROR" )
{
	$this->errorCode = 0;
	$this->errorText = "Operation Successful";

	if ( $this->debug )
	{
		if ( !preg_match("/;/", $in_sql))
		$in_sql .= ";";
		echo $in_sql."<BR>\n";
	}

    $stmt =  $this->pdo->prepare($in_sql);
    if ( !$stmt && $action_on_error != "CONTINUE" )
    {
		$this->setPDOError($in_sql);
		if ( $action_on_error == "ERROR" )
		{
			$this->showPDOError($in_sql);
		}
        return ( $stmt);
    }

	if ( $this->debug )
	{
        if ( !$stmt )
            echo "Unable to Show affected rows <BR>\n";
        else
		    echo "Rows Affected ".$stmt->rowCount()."<BR>\n<BR>\n";
    }

//if ( $this->stmt )
//echo "ooops!";
    return $stmt;
}

function executeSQL( $in_sql, $action_on_error = "ERROR" )
{
	$this->errorCode = 0;
	$this->errorText = "Operation Successful";

	if ( $this->debug )
	{
		if ( !preg_match("/;/", $in_sql))
		$in_sql .= ";";
		echo $in_sql."<BR>\n";
	}

    $this->stmt =  $this->pdo->query($in_sql);
    if ( !$this->stmt && $action_on_error != "CONTINUE" )
    {
		$this->setPDOError($in_sql);
		if ( $action_on_error == "ERROR" )
		{
			$this->showPDOError($in_sql);
		}
        return ( $this->stmt);
    }

	if ( $this->debug )
	{
        if ( !$this->stmt )
            echo "Unable to Show affected rows <BR>\n";
        else
		    echo "Rows Affected ".$this->stmt->rowCount()."<BR>\n<BR>\n";
    }

//if ( $this->stmt )
//echo "ooops!";
    return $this->stmt;
}

function lastInsertId($tabname, $colname = false)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return $this->pdo->lastInsertID();
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return $this->pdo->lastInsertID($tabname."_".$colname."_seq");
    else
    {
        $sql = "select DBINFO('sqlca.sqlerrd1') lastserial
            from systables a where tabname = '$tabname'";
        $ret = $this->executeSQL($sql, false);
        $row1 = $this->fetch(PDO::FETCH_ASSOC);
        if ( !$row1 )
		    return false;

        $modid = $row1["lastserial"];

	    return $modid;
    }
}

function valueToDBValue($value, $type="number")
{
    if ( !$value && strlen($value) == 0 )
        return "NULL";
    if ( $type =="number" )
        return $value;
    return "'".$value."'";
}


function syntax_datetime_to_db_date($dt)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
    {
        if ( getenv  ( "DBDATE" ) == "Y4MD-" )
            return $dt->format("Y-m-d");
        if ( getenv  ( "DBDATE" ) == "Y4MD/" )
            return $dt->format("Y/m/d");
        else
            return $dt->format("d/m/Y");
    }
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return $dt->format("Y-m-d");
    else
        return $dt->format("Y-m-d");
}

function syntax_where_date_of_date_time($column)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "date($column)";
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "date($column)";
    else
        return "date($column)";
}

function syntax_create_temp_table_from_select($tabname, $sql = false)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return $sql. " INTO TEMP ".$tabname. " WITH NO LOG ";
    else
        return "CREATE TEMPORARY TABLE $tabname AS ".$sql;
}

function syntax_insert_serial($tabname = false, $colname = false)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "nextval('".$tabname."_".$colname."_seq')";
    else
        return "0";
}

function pdo_field($field)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return strtoupper($field);
    else
        return $field;
}

function currentTimestampAsString()
{
    $now = new DateTime();
    return $now->format("Y-m-d H:i:s");
}

function syntax_timestamp_column()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "datetime year to second";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "timestamp default null";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "timestamp";
    else
    {
        echo "syntax_timestamp: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function trace($txt)
{
    $f = microtime();

    $arr = preg_split("/ /", $f);
    $ms = $arr[0];
    $secs = $arr[1];
    $millis = $ms + $secs;
    $dur = $millis - $this->lastTrace;
    echo "$txt ".$dur."\n";
    $this->lastTrace = $millis;
}

function syntax_time_interval_column()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "interval hour to second";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "time";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "time";
    else
    {
        echo "syntax_time_interval_column: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_in_dbspace($dbspace)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return " in $dbspace";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "";
    else
    {
        echo "syntax_in_dbspace: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_bigint()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "INTEGER";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "INTEGER";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "bigint";
    else
    {
        echo "syntax_time_interval_column: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_datetime_hour_to_second_column()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "datetime hour to second";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "datetime";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "datetime";
    else
    {
        echo "syntax_datetime_hour_to_second_column: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_datetime_column()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "datetime year to second";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "datetime";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "datetime";
    else
    {
        echo "syntax_time_interval_column: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_create_serial()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "SERIAL";
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "SERIAL";
    else if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "INTEGER AUTO_INCREMENT";
    else
    {
        echo "syntax_create_serial: no syntax for db driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function syntax_default_null()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        return "";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "pgsql" )
        return "default null";
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "mysql" )
        return "default null";
    else
    {
        echo "syntax_time_interval_column: no syntax for driver ".$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	die;
    }
}

function dumpSQL ( $sql )
{   
        $this->show_debug ( $sql );
        $stat = $this->pdo->query($sql);
        if (!$stat)
        {   
            $info = $this->pdo->errorInfo();
            trigger_error("Error $sql<BR>\n".$this->pdo->errorCode()." occurred in SQL statement.<BR>\n".
            $info[2], E_USER_ERROR);
            return $stat;
        }
        else
        {   
            foreach($stat as $row) {
//echo "<PRE>";
            echo "============================\n";
            foreach ( $row as $k => $v )
                if ( !is_numeric($k) )
                    echo " [$k] => $v\n";
//echo "</PRE>";
            }
        }
        return $stat;
}

function show_debug  ( $txt )
{   
        if ( $this->debug )
        {   
            echo $txt . "<BR>\n";
        }
}

function fetch()
{
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}

function get_request_item($in_val, $in_default = false, $in_default_condition = true)
{
    global $argv;
    $ret = false;
    if ( isset($argv) && is_array($argv) )
    {
        if ( $argv && is_array($argv) )
        {
            foreach ( $argv as $k => $v )
            {
                $arr = explode("=", $v);
                if ( count($arr) > 1 )
                {
                    if ( $arr[0] == $in_val )
                        $ret = $arr[1];
                }
            }
        }
    }
    else
    {
        if ( array_key_exists($in_val, $_REQUEST) )
            $ret =  $_REQUEST[$in_val];
        else
            $ret =  false;

    }
    if ( $in_default && $in_default_condition && !$ret )
        $ret = $in_default;

    return ( $ret );
}

function fetchAll ( $sql )
{      
    $stat = $this->executeSQL ( $sql );
    if ( $stat ) 
        return ( $stat->fetchAll() );
    else
        return false;
}

function fetch1SQL ( $sql )
{      
    $stat = $this->executeSQL ( $sql );
    if ( $stat ) 
        return ( $stat->fetch(PDO::FETCH_ASSOC) );
    else
        return false;
}

function fetch1ValueSQL ( $sql )
{      
    $stat = $this->executeSQL ( $sql );
    if ( $stat ) 
    {
        $row = false;
        if ( $row = $stat->fetch(PDO::FETCH_NUM) )
            return ($row[0]);
        else
            return false;
    }
    else
        return false;
}

function close()
{
        $this->stmt = null;
}

function showPDOError($in_sql)
{
        $code = $this->pdo->errorCode();
        $info = $this->pdo->errorInfo();
        $msg =  "$in_sql<BR>\nError $code: ".$info[0]." ".$info[1]."yyyyy<BR>\n".
                $info[2];
        echo $msg."\n";
        //trigger_error("$msg");
}

function setPDOError($in_sql)
{
        $info = $this->pdo->errorInfo();
		$this->errorCode = $info[1];
		$this->errorText = $info[2];
		if ( $this->errorWithSQL )
			$this->errorText .= "\n\n".$in_sql;
}

function syntax_temporary()
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" ) return "TEMP";
    else return "TEMPORARY";
}

function syntax_with_no_log()
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
        CREATE ".$this->syntax_temporary()." TABLE t_times
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

function build_date_range_table ( $infrom, $into, $format = "dmyq" )
{
    $retval = true;

    if ( $format == "ymd" )
    {
        $dfdy = substr($infrom, 8,2);
        $dfmn = substr($infrom, 5,2);
        $dfyr = substr($infrom, 0,4);
        $dtdy = substr($into, 8,2);
        $dtmn = substr($into, 5,2);
        $dtyr = substr($into, 0,4);
    }

    if ( $format == "dmyq" )
    {
        $dfdy = substr($infrom, 1,2);
        $dfmn = substr($infrom, 4,2);
        $dfyr = substr($infrom, 7,4);
        $dtdy = substr($into, 1,2);
        $dtmn = substr($into, 4,2);
        $dtyr = substr($into, 7,4);
    }

    $ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
    $ito = mktime ( 0, 0, 0, $dtmn, $dtdy, $dtyr );

    $sql = "CREATE ".$this->syntax_temporary()." TABLE t_days ( date_id integer, day date, dtime ".$this->datetime_year_to_day()." , dayno integer );";
    if ( !$this->executeSQL( $sql ) ) return;

    $ptr = $ifrom;
    while ( $ptr <= $ito )
    {
        $dt = strftime ( "%d/%m/%Y", $ptr );
        $dtm = strftime ( "%Y-%m-%d", $ptr );
        $dtid = strftime ( "%Y%m%d", $ptr );

        if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
            $sql = "INSERT INTO t_days VALUES ( $dtid, '".$dt."', '".$dtm."', 0 );";
        else
            $sql = "INSERT INTO t_days VALUES ( $dtid, '".$dtm."', '".$dtm."', 0 );";
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

function sqlslashes( $value)
{
    if ( $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
	    return  preg_replace("/'/", "''", $value);
    else
	    return  preg_replace("/'/", "\\'", $value);
}

function build_user_timetable($inuser, $rt, $op, $tp, $rn, $dty, $ftm = "00:00:00", $ttm = "23:59:59", $ttbid = false)
{

$retval = true;

$sql = "CREATE ".$this->syntax_temporary()." TABLE t_timetable (
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
start_time datetime hour to second
) ".$this->syntax_with_no_log();
if ( !$this->executeSQL($sql) ) return false;

$sql = "
INSERT INTO t_timetable
SELECT t_days.day, t_days.dtime, route_code, service_code,
t_route.route_id, operator.operator_code operator_code,
service.service_id, publish_tt.pub_ttb_id pub_ttb_id,
publish_tt.runningno runningno,
event.event_code event_code, event.event_id, ".$this->sql_substring("notes", 0, 1)." over_midnight, 
trip_no, duty_no, operator.operator_id, start_time
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

/*
** Converts a string to its representation in  insert and update statements .. if its blank
** it returns NULL ptjerwise returns quoted representation
*/
function stringToDbValue($s)
{
    if (strlen($s) <= 0
    || $s == null)
        return "null";

    return "'$s'";
}

function affectedRows()
{
    return $this->stmt->rowCount();
}

}

?>
