<?php

define('TRAVELINFO_FEED', "Rail.Reading.txt");
define('BBC_FEED', "bbc.xml");
define('OBU_INFO_FEED', "OBU.Info.txt");

function db_connect()
{
	$conn_id = false;
	$conn_host_name = "127.0.0.1";
#	$conn_server = "ods";
	$conn_server = "ods";
	$conn_protocol = "onsoctcp";
	$conn_database = "centurion";
	$conn_username = "dbmaster";
	$conn_password = "m1lt0n";
	$cnstr =
		"informix:".
		"host=".$conn_host_name."; ".
		"server=".$conn_server."; ".
		"protocol=".$conn_protocol."; ".
		"database=".$conn_database;

	try 
	{
		$conn_id = new PDO($cnstr, $conn_username, $conn_password);
	}
	catch ( PDOException $ex )
	{
    	echo "DB Connection Error : ";
		echo $ex->getCode()."\n";
		echo $ex->getMessage()."\n";
		return $conn_id;
	}

	$conn_id->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);

	if ( ! $conn_id ){
		return $conn_id;
	}

	$rid = executePDOQuery( "set role centrole", $conn_id );
	if  ( ! $rid ) {
		return $conn_id;
	}

	$rid = executePDOQuery("SET ISOLATION TO DIRTY READ", $conn_id);
	if ( ! $rid ) {
		return $conn_id;
	}

	$rid = executePDOQuery("SET LOCK MODE TO WAIT 5", $conn_id);
	if  ( ! $rid ) {
		return $conn_id;	
	}
	
	return $conn_id;
}

function executePDOQuery( $in_sql, $in_conn, $in_error_continue = false )
{

//echo $in_sql.";<BR>";
//if ($in_error_continue )
	$rid = $in_conn->query($in_sql);
    if ( !$in_error_continue )
    {
	    $stat = $in_conn->errorCode();
	    if ( (int)$stat != 0 )
	    {
            echo $in_sql."\n";
		    showPDOError($in_conn);
            die;
	    }
    }
	return $rid;
}

function fetchPDO( $in_stmt, $in_type = "NEXT" )
{
	$result = $in_stmt->fetch();
	return $result;
}

function showPDOError( $in_conn )
{
	$info = $in_conn->errorInfo();
	echo "Error ".$info[1]."<BR>".
		$info[2];
}

function executePDOQueryScalar( $in_sql, $in_conn )
{
	if ( ! ( $rid = executePDOQuery( $in_sql, $in_conn )) )
	{
		echo "error die";
		die;
	}

	if ( !( $row = fetchPDO($rid)) < 0  )
   	{
        echo $in_sql."\n";
       	showPDOError($conn);
       	die;
   	} 

	return ( $row );

}


// ---------------------------------------------------------
// Converts ISO8601 date format to yyyy-mm-dd hh:mm:ss format
// ---------------------------------------------------------
function xmltime2datetime($in)
{
    //echo $in;
    //012345678901234567890123456789
    //2008-06-25T16:16:00.0000+01:00
    //$yr = substr($in, 0, 4);
    //$mt = substr($in, 5, 2);
    //$dy = substr($in, 8, 2);
    //$hr = substr($in, 11, 2);
    //$mn = substr($in, 14, 2);
    //$sc = substr($in, 17, 2);
    //$tz = substr($in, 25, 2);
    $tm = date_create("$in");
    $tm = date_format($tm, "Y/m/d H:i:s");
    //echo date_format($tm, "\n\nY-m-d\TH:i:s\Z\n\n");
    return $tm;
}

function get_request_item($in_val, $in_default = false, $in_default_condition = true)
{
    if ( array_key_exists($in_val, $_REQUEST) )
        $ret =  $_REQUEST[$in_val];
    else
        $ret =  false;

    if ( $in_default && $in_default_condition && !$ret )
        $ret = $in_default;

    return ( $ret );
}

function get_current_time()
{
    $now = new DateTime();
    return $now;
}


function ic_format_time($time, $format)
{
    return $time->format($format);
}

function date_to_format($time, $format)
{
    if ( !$time )
        return ("<NONE>");
    $tm = DateTime::createFromFormat('Y-m-d H:i:s', $time);
    return $tm->format($format);
}

function ic_HHMMSS_to_seconds($interval)
{
    $val = ($interval->y * 365 * 24 * 60 * 60) +
               ($interval->m * 30 * 24 * 60 * 60) +
               ($interval->d * 24 * 60 * 60) +
               ($interval->h * 60 *60) +
               ($interval->i * 60 ) +
               $interval->s;
    if ( !$interval->invert )
        return -1 * $val;
    else
        return $val;
}


?>
