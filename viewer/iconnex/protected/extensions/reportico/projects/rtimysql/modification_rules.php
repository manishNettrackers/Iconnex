
<?php 

function custom_project_modifications($pdo, $return_status)
{

logRequest();
        $dbview = get_request_item("dbview", "");
        if ( !$dbview )
        {
            $return_status["errstat"] = 400;
            $return_status["msgtext"] = "No data view name specified";
            return;
        }

        $oper = get_request_item("oper", "");
        if ( !$oper )
        {
            $return_status["errstat"] = 400;
            $return_status["msgtext"] = "Invalid operation passed";
			/// !!! awful fix must change for jqgrid first row save
            //$return_status["errstat"] = 0;
            //$return_status["msgtext"] = "Invalid operation passed";
            return;
        }

        $id = get_request_item("id", "");
        if ( $oper == "edit" && !$id )
        {
            $return_status["errstat"] = 400;
            $return_status["msgtext"] = "No id specified for update operation";
            return;
        }


        switch ( $dbview )
        {
            case "timetab1":
                custom_mod_timetab1($pdo,&$return_status);
                break;

            case "tripcancel":
                custom_mod_tripcancel($pdo,&$return_status);
                break;

            case "tripcancelbystop":
                custom_mod_tripcancel($pdo,&$return_status);
                break;

            case "displaypoint":
                custom_mod_displaypoint($pdo,&$return_status);
                break;

            case "timetablemonitor":
                custom_mod_timetablemonitor($pdo,&$return_status);
                break;

            case "specops":
                custom_mod_specops($pdo,&$return_status);
                break;

            default:
                $return_status["errstat"] = 400;
                $return_status["msgtext"] = "No dataset matched the specified view: $dbview";
        }


        return;

}


function custom_mod_displaypoint ($pdo, &$status)
{
    $oper = get_request_item("oper", "");
    $user = get_request_item("user", "");
    $keyid = get_request_item("id", "");
    $build_code = get_request_item("Build_Code", "");
    $cancel = get_request_item("Cancel", "");
	
	if ( ! $keyid )
	{
        $status["errstat"] = 400;
        $status["msgtext"] = "No build/location specified";
        return;
	}

    $ar = preg_split("/_/", $keyid);

    if ( !$user || $user == "undefined" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unknown user - You are not authorised to perform this operation";
        return;
    }

    if ( $user == "guest" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Guest users are not authorised to perform this operation";
        return;
    }

    $arr = explode("_",$keyid);
	$location_id = 0;
	$build_id = 0;
	if ( count($arr) == 2 )
	{
        $location_id = $arr[0];
        $build_id = $arr[1];
	}
	if ( count($arr) == 1 )
	{
        $location_id = $arr[0];
	}

    $sql = "select build_id 
        from unit_build where unit_type = 'BUSSTOP' AND build_code = '" . $build_code . "'";
    $ret = $pdo->executeSQL($sql, false);
    if ( !$ret )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to validate build<BR><BR>".$pdo->getErrorMessage();
        return;
    }
    $row1 = $pdo->fetch();
    if ( !$row1 )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Invald Build Code Specified";
        return;
    }

	IF ( $build_id )
	{
		$sql = "UPDATE display_point SET build_id = ". $row1["build_id"] ." WHERE location_id = $location_id AND build_id = $build_id";
	}
	else
	{
		$sql = "INSERT INTO display_point (location_id, build_id ) VALUES ( $location_id , $build_id )";
	}

    $ret = $pdo->executeSQL($sql, true);
    if ( !$ret )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to update display point <BR><BR>".$pdo->getErrorMessage();
        return;
    }
}

function custom_mod_timetablemonitor ($pdo, &$status)
{
    $oper = get_request_item("oper", "");
    $user = get_request_item("user", "");
    $user = "dbmaster";
    $tripids = get_request_item("ids", "");
    $cancel = get_request_item("Cancel", "");

    if ( !$user || $user == "undefined" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unknown user - You are not authorised to perform this operation";
        return;
    }

    //if ( $user == "guest" )
    //{
        //$status["errstat"] = 400;
        //$status["msgtext"] = "Guest users are not authorised to perform this operation";
        //return;
    //}

    if ( !$cancel )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "No cancellation mode specified";
        return;
    }

    if ( !$tripids )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "No trip specified";
        return;
    }

    //$arr = explode("_",$id);
	//if ( count($arr) != 2 )
	//{
        //$status["errstat"] = 400;
        //$status["msgtext"] = "Invalid primary key specification";
        ////return;
	//}
    $ret = $pdo->executeSQL("BEGIN WORK", true);
    $tripex = explode ( ",", $tripids[0]);
    foreach ( $tripex as $trip )
    {
	    if ( $cancel == "Yes" )
	    {
		    $sql = 
		        "insert into tt_mod ( mod_id, user_id, mod_time, pub_ttb_id, mod_type, mod_status, wef_date, wet_date)
			    select 0, userid, CURRENT, $trip, 'C', 'P', TODAY, TODAY
			    from cent_user
			    where usernm = '".$user."'";
	    }
	    else
	    {
		    $sql = 
		    "insert into tt_mod ( mod_id, user_id, mod_time, pub_ttb_id, mod_type, mod_status, wef_date, wet_date)
			select 0, userid, CURRENT, $trip, 'A', 'P', TODAY, TODAY
			    from cent_user
			where usernm = '".$user."'";
        }

        $ret = $pdo->executeSQL($sql, true);
        if ( !$ret )
        {
            $ret = $pdo->executeSQL("ROLLBACK WORK", true);
            $status["errstat"] = 400;
            $status["msgtext"] = "Unable to create modification<BR><BR>".$pdo->getErrorMessage();
            return;
        }
    }
//echo $sql;
//showstuffsql($status, $sql);
//return;
//$f = fopen("/tmp/stuff.txt", "a+");
//fwrite($f, $sql);
//fclose($f);
    $ret = $pdo->executeSQL("COMMIT WORK", true);

}

function custom_mod_tripcancel ($pdo, &$status)
{
    $oper = get_request_item("oper", "");
    $user = get_request_item("user", "");
    $tripid = get_request_item("id", "");
    $cancel = get_request_item("Cancel", "");

    if ( !$user || $user == "undefined" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unknown user - You are not authorised to perform this operation";
        return;
    }

    if ( $user == "guest" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Guest users are not authorised to perform this operation";
        return;
    }

    if ( !$cancel )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "No cancellation mode specified";
        return;
    }

    if ( !$tripid )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "No trip specified";
        return;
    }

    //$arr = explode("_",$id);
	//if ( count($arr) != 2 )
	//{
        //$status["errstat"] = 400;
        //$status["msgtext"] = "Invalid primary key specification";
        ////return;
	//}

	if ( $cancel == "Yes" )
	{
		$sql = 
		"insert into tt_mod ( mod_id, user_id, mod_time, pub_ttb_id, mod_type, mod_status, wef_date, wet_date)
			select 0, userid, CURRENT, $tripid, 'C', 'P', TODAY, TODAY
			from cent_user
			where usernm = '".$user."'";
	}
	else
	{
		$sql = 
		"insert into tt_mod ( mod_id, user_id, mod_time, pub_ttb_id, mod_type, mod_status, wef_date, wet_date)
			select 0, userid, CURRENT, $tripid, 'A', 'P', TODAY, TODAY
			from cent_user
			where usernm = '".$user."'";
	}

    $ret = $pdo->executeSQL($sql, true);
//echo $sql;
//showstuffsql($status, $sql);
//return;
//$f = fopen("/tmp/stuff.txt", "a+");
//fwrite($f, $sql);
//fclose($f);
    if ( !$ret )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to create modification<BR><BR>".$pdo->getErrorMessage();
        return;
    }
}

function custom_mod_timetab1 ($pdo, &$status)
{
    $oper = get_request_item("oper", "");
    $user = get_request_item("user", "");
    $newtime = get_request_item("Pub_Time", "");
    if ( !$newtime )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Published Time not specified";
        return;
    }

    if ( !$user )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "You are not authorised to perform this operation";
        return;
    }

    if ( !preg_match("/\d\d:\d\d:\d\d/", $newtime) )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Time must be in the format HH:MM:SS";
        return;
    }

    $id = get_request_item("id", "");
    $arr = explode("_",$id);
	if ( count($arr) != 2 )
	{
        $status["errstat"] = 400;
        $status["msgtext"] = "Invalid primary key specification";
        return;
	}

    $sql = 
        "insert into tt_mod ( mod_id, user_id, mod_time, pub_ttb_id, mod_type, mod_status, wef_date, wet_date)
        select 0, userid, CURRENT, ".$arr[0].", 'M', 'P', CURRENT - 1 UNITS YEAR, CURRENT + 1 UNITS YEAR
        from cent_user
        where usernm = '".$user."'";
    $ret = $pdo->executeSQL($sql, true);
    if ( !$ret )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to create modification<BR><BR>".$pdo->getErrorMessage();
        return;
    }

    $sql = "select DBINFO('sqlca.sqlerrd1') lastserial
        from systables a where tabname = 'tt_mod'";
    $ret = $pdo->executeSQL($sql, false);
    $row1 = $pdo->fetch();
    if ( !$row1 )
    {
        echo "Failed to create modification";
        $status["errstat"] = 400;
        $status["msgtext"] = "Failed to create modification<BR><BR>".$pdo->getErrorMessage();
        die;
    }
    $modid = $row1["lastserial"];

    //echo "got  mod $modid";
    //echo "<BR>";
    foreach ( $modtimes as $k => $v )
    {
        $sql = 
            "insert into tt_mod_times
            (
                mod_id,
                rpat_orderby,
                arrival_time,
                departure_time
            )
            values ( $modid, ".$v["order"].", '".$v["arrival"]."', '".$v["departure"]."' );";
        if ( !executePDOQuery($sql, $ds) ) 
        {
            echo "Fail $sql";
            return false;
        }
    }
}


function custom_mod_specops ($pdo, &$status)
{
    $oper = get_request_item("oper", "");
    $user = get_request_item("user", "");
    $user = "dbmaster";
    $tripids = get_request_item("ids", "");
    $mapto = get_request_item("map_to_day_type", "");

    if ( !$user || $user == "undefined" )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "Unknown user - You are not authorised to perform this operation";
        return;
    }

    //if ( $user == "guest" )
    //{
        //$status["errstat"] = 400;
        //$status["msgtext"] = "Guest users are not authorised to perform this operation";
        //return;
    //}

    if ( !$mapto )
    {
        $status["errstat"] = 400;
        $status["msgtext"] = "No day type specified to map to";
        return;
    }

    $id = get_request_item("id", "");
    $arr = explode("_",$id);
	if ( count($arr) != 3 )
	{
        $status["errstat"] = 400;
        $status["msgtext"] = "Invalid primary key specification";
        return;
	}

    $caldate = $arr[0];
    //$dty = substr($caldate, 6, 4);
    //$dtm = substr($caldate, 3, 2);
    //$dtd = substr($caldate, 0, 2);
    //$caldate = $dty."-".$dtm."-".$dtd;
    $operator = $arr[1];
    $route = $arr[2];
    if ( !$route ) $route = "0";

    $ret = $pdo->executeSQL("BEGIN WORK", true);
   
    $sql = "select * from event where event_tp =1 and operator_id = $operator and spdt_start = '$caldate'";
    $ret = $pdo->executeSQL($sql, false);

    if ( !$ret )
    {  
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to validate event date<BR><BR>".$pdo->getErrorMessage();
        return;
    }
    $row1 = $pdo->fetch();
    $evid = 0;
    if ( !$row1 )
    {
        $sql = "insert into event ( operator_id, event_id, event_code, event_desc, event_tp,
                        spdt_start, spdt_end )
                        values ( $operator, 0, '$caldate', 'Date $caldate', 1, '$caldate', '$caldate' )";
//echo "<BR>".$sql;
        $ret = $pdo->executeSQL($sql, true);
        if ( !$ret )
        {  
            $status["errstat"] = 400;
            $status["msgtext"] = "Unable to create mapping for from event date<BR><BR>".$pdo->getErrorMessage();
            return;
        }

        $evid = $pdo->last_serial();
    }
    else
    {
        $evid = $row1["event_id"];
    }

    $sql = "select * from special_op where op_event = $evid and operator_id = $operator and route_id = $route ";
    $ret = $pdo->executeSQL($sql, false);
    if ( !$ret )
    {  
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to validate map to event date<BR><BR>".$pdo->getErrorMessage();
        return;
    }
    $row1 = $pdo->fetch();
    if ( !$row1 )
    {
        $sql = "insert into special_op (  operator_id , route_id , service_id , op_event , map_event) 
                        values ( $operator, $route, 0, $evid, $mapto )";
    }
    else
    {
        $sql = "update special_op  set map_event = $mapto 
                    where operator_id = $operator
                    and route_id = $route
                    and op_event = $evid";
    }
    $ret = $pdo->executeSQL($sql, false);
    if ( !$ret )
    {  
        $status["errstat"] = 400;
        $status["msgtext"] = "Unable to set mapping for new event<BR><BR>".$pdo->getErrorMessage();
        return;
    }

//showstuffsql($status, $sql);
//return;
//$f = fopen("/tmp/stuff.txt", "a+");
//fwrite($f, $sql);
//fclose($f);
    $ret = $pdo->executeSQL("COMMIT WORK", true);

}


function custom_mod_timetables ($con, $modtype, $modtrip,$return_status)
{
    $keyid = 
    get_trip_mods($con, $modtype, $modtrip, $modtimes);


$modtrip = 0;
$modtype = "NONE";
$modtimes = array();
apply_trip_mods($ds, $modtype, $modtrip, $modtimes);

if ( $modtype == "DELETETRIP" )
    echo "Trip Deleted";
else
    echo "Trip Modified";

die;
}


function removeTrip( $ds, $tripid)
{

$sql = "SET ISOLATION TO DIRTY READ";
if ( !executePDOQuery($sql, $ds) ) return false;

echo $sql;
$sql = 
"insert into tt_mod
  (
    mod_id,
    user_id,
    mod_time,
    pub_ttb_id,
    mod_type,
    mod_status
  )
select 0, userid, CURRENT, $tripid, 'M', 'P'
from cent_user
where usernm = USER";
//if ( !executePDOQuery($sql, $ds) ) return false;


return true;

}

function apply_trip_mods($conn, &$modtype, &$modtrip, &$modtimes)
{

    $sql = "SET ISOLATION TO DIRTY READ";
    if ( !$conn->executeSQL($sql, $ds) ) return false;

    if ( $modtype == "DELETETRIP" )
    {
        $sql = 
        "insert into tt_mod
        (
            mod_id,
            user_id,
            mod_time,
            pub_ttb_id,
            mod_type,
            mod_status,
            wef_date, 
            wet_date
        )
        select 0, userid, CURRENT, $modtrip, 'K', 'P', CURRENT - 1 UNITS YEAR, CURRENT + 1 UNITS YEAR
        from cent_user
        where usernm = USER";
    }
    if ( $modtype == "MODIFY" )
    {
        $sql = 
        "insert into tt_mod
        (
            mod_id,
            user_id,
            mod_time,
            pub_ttb_id,
            mod_type,
            mod_status,
            wef_date, 
            wet_date
        )
        select 0, userid, CURRENT, $modtrip, 'M', 'P', CURRENT - 1 UNITS YEAR, CURRENT + 1 UNITS YEAR
        from cent_user
        where usernm = USER";
    }
    if ( !executePDOQuery($sql, $ds) ) return false;

    $sql = "select DBINFO('sqlca.sqlerrd1') lastserial
        from systables a where tabname = 'tt_mod'";
    $rid1 = executePDOQuery($sql, $ds );
    $row1 = fetchPDO ($rid1, "NEXT");
var_dump($row1);
    if ( !$row1 )
    {
        echo "Failed to create modification";
        die;
    }
    $modid = $row1["lastserial"];
echo " mod $modid";
    //echo "got  mod $modid";
    //echo "<BR>";
    
    foreach ( $modtimes as $k => $v )
    {
        $sql = 
            "insert into tt_mod_times
            (
                mod_id,
                rpat_orderby,
                arrival_time,
                departure_time
            )
            values ( $modid, ".$v["order"].", '".$v["arrival"]."', '".$v["departure"]."' );";
        if ( !executePDOQuery($sql, $ds) ) 
        {
            echo "Fail $sql";
            return false;
        }
    }
}

function get_trip_mods($con, &$modtype, &$modtrip, &$modtimes)
{

    $modtype = "NONE";

    $tmodtimes = array();
    for ( $i = 0; $i < 100; $i++ )
    {
        $tmodtimes[] = array (
                "arrival" => false,
                "departure" => false
                );
    }
    foreach ( $_REQUEST as $k => $v )
    {
        if ( preg_match( "/^apply_/", $k ) )
        {
            $modtype = "MODIFY";
            $ar = preg_split("/_/", $k);
            $modtrip = $ar[1];
        }

        if ( preg_match( "/^deletetrip_/", $k ) )
        {
            $modtype = "DELETETRIP";
            $ar = preg_split("/_/", $k);
            $modtrip = $ar[1];
        }

        if ( preg_match( "/^modarr_/", $k ) )
        {
            $ar = preg_split("/_/", $k);
            $ord = $ar[2];
            $tmodtimes[$ord - 1]["order"] = $ord;
            $tmodtimes[$ord - 1]["arrival"] = $v;
        } 
        if ( preg_match( "/^moddep_/", $k ) )
        {
            $ar = preg_split("/_/", $k);
            $ord = $ar[2];
            $tmodtimes[$ord - 1]["departure"] = $v;
        } 
    }

    foreach ( $tmodtimes as $k => $v )
    {
        if ( $v["arrival"] )
        {
            $modtimes[] = $v;
            //echo $v["order"]."-".$v["arrival"]."-".$v["departure"]."<br>";
        }
    }
}

function logRequest()
{
	$f = fopen ( "/tmp/req", "w+");
	$str = "";
	foreach ( $_REQUEST as $k => $y )
	{
	$str .= "$k = $y\n";
	}
	fwrite($f, "ooooo");
	fwrite($f, $str);
	fclose($f);
}
function showStuffsql(&$status, $sql)
{
	$str = "";
	foreach ( $_REQUEST as $k => $y )
	{
	$str .= "$k = $y<BR>";
	}
    	$keyid = "ee";
    $status["errstat"] = 567;
    $status["msgtext"] = "Oh my rod there is an error1<BR>$sql<BR>$str";
}
function showStuff(&$status)
{
	$str = "";
	foreach ( $_REQUEST as $k => $y )
	{
	$str .= "$k = $y<BR>";
	}
    	$keyid = "ee";
    $status["errstat"] = 567;
    $status["msgtext"] = "Oh my rod there is an error1<BR>$str";
}

?>
