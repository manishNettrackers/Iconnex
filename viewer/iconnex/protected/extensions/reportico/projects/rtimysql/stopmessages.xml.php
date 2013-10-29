<?php



$dfrom = $_criteria["fromdate"]->get_criteria_value("VALUE");
$dto = $_criteria["todate"]->get_criteria_value("VALUE");
$tfrom = $_criteria["fromtime"]->get_criteria_value("VALUE");
$tto = $_criteria["totime"]->get_criteria_value("VALUE");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$loc = $_criteria["location"]->get_criteria_value("VALUE");
$signtype = $_criteria["sign_type"]->get_criteria_value("VALUE");

$conflict = $_criteria["msgconflict"]->get_criteria_value("VALUE");
$message = $_criteria["msgtext"]->get_criteria_value("VALUE");
$tftline1 = $_criteria["infoline1"]->get_criteria_value("VALUE");
$tftline2 = $_criteria["infoline2"]->get_criteria_value("VALUE");
$tftline3 = $_criteria["infoline3"]->get_criteria_value("VALUE");
$tftroute = $_criteria["tftroute"]->get_criteria_value("VALUE");
$tftfull = $_criteria["tftfull"]->get_criteria_value("VALUE");
$msgline1 = $_criteria["msgline1"]->get_criteria_value("VALUE");
$msgline2 = $_criteria["msgline2"]->get_criteria_value("VALUE");
$msgline3 = $_criteria["msgline3"]->get_criteria_value("VALUE");
$msgname = $_criteria["msgname"]->get_criteria_value("VALUE");
$modgroup = $_criteria["modgroup"]->get_criteria_value("VALUE");
$clrmsg = $_criteria["clearmsg"]->get_criteria_value("VALUE");
$scroll3 = $_criteria["scroll3"]->get_criteria_value("VALUE");


$gloc = false;
$grte = false;
$gday = false;

if ( $msgname )
{
    if ( strlen($msgname) > 15 )
    {
        trigger_error ("<b>Msg Group Name must not be longer than 15 characters</b>");
        return;
    }
}


$conflictMode = "NONE";
if ( preg_match("/1/", $conflict )) $conflictMode = "NONE";
if ( preg_match("/2/", $conflict )) $conflictMode = "LEAVE";
if ( preg_match("/3/", $conflict )) $conflictMode = "OVERRIDE";
if ( preg_match("/4/", $conflict )) $conflictMode = "ADD";

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);
$tfrom = substr($tfrom, 1,5).":00";
$tto = substr($tto, 1,5).":00";
$ifrom = $dfyr."-".$dfmn."-".$dfdy." ".$tfrom;
$ito = $dtyr."-".$dtmn."-".$dtdy." ".$tto;


if ( $msgline1 == "'BLANK'" ) $msgline1 = "''";
if ( $msgline2 == "'BLANK'" ) $msgline2 = "''";
if ( $msgline3 == "'BLANK'" ) $msgline3 = "''";
if ( $tftline1 == "'BLANK'" ) $tftline1 = "''";
if ( $tftline2 == "'BLANK'" ) $tftline2 = "''";
if ( $tftline3 == "'BLANK'" ) $tftline3 = "''";
if ( $tftroute == "'BLANK'" ) $tftroute = "''";
if ( $tftfull == "'BLANK'" ) $tftfull = "''";

// ----------------------------------------
// Start Work
$sql = "BEGIN WORK";
if ( !executePDOQuery($sql, $ds) ) return;

// ----------------------------------------
// SET ISOLATION
$sql = "SET ISOLATION TO DIRTY READ";
if ( !executePDOQuery($sql, $ds) ) return;

//Get list of non-Infotec signs which are targettable
if ( !get_non_infotec_signs($ds, "make") ) return;
if ( !get_non_infotec_signs($ds, "maxTextWidth") ) return;

if ( $rt || $loc || $op )
{
$sql = "SELECT UNIQUE a.location_id
FROM service_patt a, service b, display_point c, route d
WHERE a.service_id = b.service_id
AND b.route_id = d.route_id
AND a.location_id = c.location_id
AND c.build_id IN ( SELECT build_id FROM t_stops_make )";

if ( $rt ) {
    $sql .= " AND d.route_id IN ( $rt )";
}
if ( $op ) {
    $sql .= " AND d.operator_id IN ( $op )";
}
if ( $loc ) {
    $sql .= " AND c.location_id IN ( $loc )";
}

$sql .= "INTO TEMP t_mloc";

if ( !executePDOQuery($sql, $ds) ) return;
}


// ----------------------------------------
// Build List of Locations to populate
$sql = "
SELECT UNIQUE 
    a.build_id, t_stops_make.param_value make, t_stops_maxTextwidth.param_value width
FROM display_point a, unit_build b, t_stops_make, t_stops_maxTextwidth 
WHERE 1 = 1
AND a.build_id = b.build_id
AND a.build_id = t_stops_make.build_id
AND a.build_id = t_stops_maxTextWidth.build_id
";

if ( $loc || $rt || $op )
    $sql .= "AND a.location_id IN ( SELECT location_id FROM t_mloc )";

if ($signtype)
    $sql .= "AND t_stops_make.param_value IN ( $signtype )";

$sql .= " INTO TEMP t_build WITH NO LOG";
if ( !executePDOQuery($sql, $ds) ) return;



// ----------------------------------
// Alter Existing Message Text
if ( $message != "" )
{
    if ( $msgline1 == "" )
    {
        trigger_error ("<b>To update message text you must specify some new text in Message Line 1</b>");
        return;
    }

    if ( $msgline1 != "" )
    {
        $sql = "
        UPDATE dcd_message 
        SET ( message_text ) = ( $msgline1 )
        WHERE message_id IN ( $message )";
        if ( !executePDOQuery($sql, $ds) ) return;
    }

    /*$sql = "
    UPDATE dcd_message_loc 
    SET ( display_time, expiry_time, message_sent, received ) = 
    ( '".$ifrom."', '".$ito."', NULL, NULL )
    WHERE message_id = $message";
    if ( !executePDOQuery($sql, $ds) ) return;

    if ( $clrmsg )
        {
        $sql = "
        UPDATE dcd_message_loc 
        SET ( display_flag ) = ( 0 )
        WHERE message_id = $message";
        if ( !executePDOQuery($sql, $ds) ) return;
    }
    */
}

// ----------------------------------
// Alter Existing Message
if ( $modgroup != "" )
{

    if ( $clrmsg )
    {
        $sql = "
        UPDATE dcd_message_loc 
        SET ( display_flag ) = ( 0 )
        WHERE message_id IN ( SELECT message_id FROM dcd_message WHERE message_group = $modgroup )";
    }
    else
    {
        $sql = "
        UPDATE dcd_message_loc 
        SET ( display_time, expiry_time, message_sent, received ) = 
        ( '".$ifrom."', '".$ito."', NULL, NULL )
        WHERE message_id IN ( SELECT message_id FROM dcd_message WHERE message_group = $modgroup )";
    }
    if ( !executePDOQuery($sql, $ds) ) return;
}

if ( $message== "" && $msgname == "" && ( 
                $tftroute != "" || $tftfull != "" ||
                $tftline1 != "" || $tftline2 != "" || $tftline3 != "" ||
                $msgline1 != "" || $msgline2 != "" || $msgline3 != "" ) )
{
    trigger_error ("<b>When sending new messages the message group name must be specified</b>");
    return;
}


// ----------------------------------
// Enter Info Line 1
if ( $tftline1 != "" && $message == "" )
{
    if ( !build_display_message($ds, 1, "INFOLINE<LINE>", $msgname, $tftline1, $conflictMode, $scroll3, $ifrom, $ito, false, true))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message TFT Line 1 Message</b>");
        return;
    }
}

// ----------------------------------
// Enter Info Line 2
if ( $tftline2 != "" && $message == "" )
{
    if ( !build_display_message($ds, 2, "INFOLINE<LINE>", $msgname, $tftline2, $conflictMode, $scroll3, $ifrom, $ito, false, true))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message TFT Line 2 Message</b>");
        return;
    }
}

// ----------------------------------
// Enter Info Line 3
if ( $tftline3 != "" && $message == "" )
{
    if ( !build_display_message($ds, 3, "INFOLINE<LINE>", $msgname, $tftline3, $conflictMode, $scroll3, $ifrom, $ito, false, true))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message TFT Line 3 Message</b>");
        return;
    }
}

// ----------------------------------
// Enter TFT Timetable Cover Message
if ( $tftroute != "" && $message == "" )
{
    if ( !build_display_message($ds, 0, "ROUTEMSG", $msgname, $tftroute, $conflictMode, $scroll3, $ifrom, $ito, false, true))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new TFT Timetable Cover Message</b>");
        return;
    }
}

// ----------------------------------
// Enter TFT Full Screen Cover Message
if ( $tftfull != "" && $message == "" )
{
    if ( !build_display_message($ds, 0, "FULLSCREEN", $msgname, $tftfull, $conflictMode, $scroll3, $ifrom, $ito, false, true))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new TFT Full Screen Message</b>");
        return;
    }
}

// ----------------------------------
// Enter New Message Line 1
if ( $msgline1 != "" && $message == "" )
    if ( !build_display_message($ds, 1, "LINE<LINE>HIGH", $msgname, $msgline1, $conflictMode, $scroll3, $ifrom, $ito, true, false))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message 1</b>");
        return;
    }
// ----------------------------------
// Enter New Message Line 2
if ( $msgline2 != "" && $message == "" )
    if ( !build_display_message($ds, 2, "LINE<LINE>HIGH", $msgname, $msgline2, $conflictMode, $scroll3, $ifrom, $ito, true, false))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message 2</b>");
        return;
    }
// ----------------------------------
// Enter New Message Line 3
if ( $msgline3 != "" && $message == "" )
    if ( !build_display_message($ds, 3, "LINE<LINE>HIGH", $msgname, $msgline3, $conflictMode, $scroll3, $ifrom, $ito, true, false))
    {
        executePDOQuery("ROLLBACK WORK", $ds);
        trigger_error ("<b>Failed to create new message 3</b>");
        return;
    }


// ----------------------------------------
// Start Work
$sql = "COMMIT WORK";
if ( !executePDOQuery($sql, $ds) ) return;


function executePDOQuery( $in_sql, &$in_conn )
{
//echo $in_sql."<BR><BR>";
        $rid =  $in_conn->Execute($in_sql);

        if ( !$rid )
        {
                $msg = "<br>$in_sql<br>".$in_conn->ErrorMsg();
                trigger_error("$msg");
                return ( $rid);
        }
        return $rid;
}

function fetchPDO( $in_stmt, $in_type = "NEXT" )
{
        $result = $in_stmt->FetchRow();
        return $result;
}

function showPDOError( $in_conn )
{
        $info = $in_conn->errorInfo();
        echo "Error ".$info[1]."<BR>".
                $info[2];
}

function get_non_infotec_signs( $in_conn, $tp )
{
$sql = "
select a.build_id,
a.build_code,
a.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d
where a.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and unit_type = 'BUSSTOP'
and param_value is not null
and param_value not in ( 'BDIS', 'Infotec', 'Infotec (LX800)' )
and param_value != ''
and a.build_id in  ( select build_id from display_point )
INTO TEMP t_stops_$tp
;
";
if ( !executePDOQuery($sql, $in_conn) ) return false;

$sql = "
insert into t_stops_$tp
select unique
a.build_id,
a.build_code,
pa.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d,
unit_build pa
where 1 = 1
and a.build_parent = pa.build_id
and pa.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and param_value is not null
and param_value != ''
and param_value not in ( 'BDIS', 'Infotec', 'Infotec (LX800)' )
and a.unit_type = 'BUSSTOP'
and a.build_id in  ( select build_id from display_point )
and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp )
";
if ( !executePDOQuery($sql, $in_conn) ) return false;

$sql = "
insert into t_stops_$tp
select unique
a.build_id,
a.build_code,
pa.build_code parent,
param_desc,
param_value,
a.unit_type
from unit_build a, unit_param b, component c, parameter d,
unit_build pa, unit_build ppa
where 1 = 1
and a.build_parent = pa.build_id
and pa.build_parent = ppa.build_id
and ppa.build_id = b.build_id
and b.component_id = c.component_id
and b.param_id = d.param_id
and component_code = 'STOPDISPLAYDEVICE'
and ( param_desc = '$tp' )
and param_value is not null
and param_value != ''
and param_value not in ( 'BDIS', 'Infotec', 'Infotec (LX800)' )
and a.unit_type = 'BUSSTOP'
and a.build_id in  ( select build_id from display_point )
and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp );
";
if ( !executePDOQuery($sql, $in_conn) ) return false;


return true;
}

function build_display_message ( $ds, $lineno, $feedmask, $msgname, $newmsg, $conflictMode, $scroll3, $ifrom, $ito, $centred, $tft )
{

    $feed = preg_replace("/<LINE>/", $lineno, $feedmask);

    $sql = "select unique width from t_build";

    if ( $tft )
        $sql .= " WHERE make = 'TFT'";
    else
        $sql .= " WHERE make != 'TFT'";

    $rid0 = executePDOQuery($sql, $ds );
    if ( !$rid0 ) return false;
    $fail = false;
    $newmsg = trim($newmsg);
    while ( $row0 = fetchPDO ($rid0, "NEXT") )
    { 
         $width = $row0["width"];
         
         if ( strlen($newmsg) - 2 > $width && !( $lineno == 3 && $scroll3 ) )
         {
             $msg = "<b>Message ";
             $msg .= trim($newmsg);
             $msg .= " too long for $width character wide signs ";
             $msg .= "</b>";
             trigger_error ($msg);
             return false;
         }

    // Get Centred message
    $cenmsg = substr($newmsg, 1, strlen($newmsg) - 2);
    if ( $centred )
        if ( strlen($newmsg) > 2 )
        {
            $cenmsg = substr($newmsg, 1, strlen($newmsg) - 2);
            if ( strlen($cenmsg) < $width - 1 )
            {
                $padamt = (int)( $width - strlen($cenmsg)) / 2;
                $pad = str_repeat(" ", $padamt);
                $cenmsg = "$pad$cenmsg";
            }
        }
        else
            $cenmsg = "";
    $cenmsg = "'$cenmsg'";

    // ----------------------------------------
    // Remove Existing Emergency Messages
    // ----------------------------------------
    $sql = "SELECT message_text, a.message_id, d.location_id, location_code, d.description, c.build_id
    FROM dcd_message_loc a, dcd_message b, display_point c, location d, t_build e
    WHERE a.message_id = b.message_id
    AND a.build_id = c.build_id
    AND c.location_id = d.location_id
    AND c.build_id = e.build_id
    AND display_flag = 1
    AND feed = '$feed'
    AND e.width = $width
    INTO TEMP t_duploc";
    if ( !executePDOQuery($sql, $ds) ) return;

    $sql = "select * FROM t_duploc";
    $rid1 = executePDOQuery($sql, $ds );
    if ( !$rid1 ) return false;
    $fail = false;
    while ( $row1 = fetchPDO ($rid1, "NEXT") )
    { 
     $msg = "<b>Message \"";
     $msg .= trim($row1["message_text"]);
     $msg .= "\" already showing at ";
     $msg .= trim($row1["description"]);
     $msg .= ".</b>";
     $msg .= "<BR><br>Use the <b>Apply New Message</b> to force overriding or <b>Retain Existing Message</b> to leave what is on the stop already";
     

     if ( $conflictMode == "OVERRIDE" )
     {
          $sql = "UPDATE dcd_message_loc set display_flag = 0 
               WHERE build_id = ".$row1["build_id"]."
               AND message_id IN ( SELECT message_id FROM dcd_message WHERE feed = '$feed' )
               ";
          if ( !executePDOQuery($sql, $ds) ) return false;
     }
     if ( $conflictMode == "LEAVE" )
     {
          $sql = "DELETE FROM t_build WHERE build_id = ".$row1["build_id"];
          if ( !executePDOQuery($sql, $ds) ) return false;
     }
     if ( $conflictMode == "NONE" )
     {
         $fail = true;
         trigger_error ($msg);         
     }
    }

    if ( $fail )
       return false;

    $sql = "DROP TABLE t_duploc";
    if ( !executePDOQuery($sql, $ds) ) return false;

    // ----------------------------------------
    // Work out whether we have any locs left to create mesages for
    $sql = "select count(*) bcnt FROM t_build where width = $width";
    $rid1 = executePDOQuery($sql, $ds );
    if ( !$rid1 ) return;
    $row1 = fetchPDO ($rid1, "NEXT");
    if ( !$row1 )
    {
        trigger_error ("Failed to fetch message id");
        return false;
    }
    $loccnt = $row1["bcnt"];

    if ( $loccnt > 0 )
    {

    // ----------------------------------------
    // Remove Existing Emergency Messages
    $sql = "UPDATE dcd_message_loc set display_flag = 0 WHERE message_id IN ( SELECT message_id FROM dcd_message WHERE message_group = $msgname AND feed = '$feed' AND build_id IN (SELECT build_id FROM t_build where width = $width))";

    if ( !executePDOQuery($sql, $ds) ) return false;

    // ----------------------------------------
    // Create Emergency Message
    $sql = "
    INSERT INTO dcd_message ( message_id, feed, message_text, message_group )
    VALUES (
    0, '$feed', ".$cenmsg.", $msgname
    )";

    if ( !executePDOQuery($sql, $ds) ) return false;

    $sql = "select DBINFO('sqlca.sqlerrd1') lastserial
        from systables a where tabname = 'dcd_message'";
    $rid1 = executePDOQuery($sql, $ds );
    if  ( ! $rid1 )
    {
        return false;
    }
    $row1 = fetchPDO ($rid1, "NEXT");
    if ( !$row1 )
    {
        trigger_error ("Failed to fetch message id");
        return false;
    }

    $tbid = $row1["lastserial"];

    // ----------------------------------------
    // Create Emergency Message
    $sql = "
    INSERT INTO dcd_message_loc 
    (
    message_id,
    build_id,
    creation_time,
    display_time,
    expiry_time,
    hold_time,
    interleave_mode,
    display_style,
    activity_mode,
    display_flag
    )
    SELECT UNIQUE
        $tbid,
        a.build_id,
        CURRENT,
        '$ifrom',
        '$ito',
        5,
        'NONE',
        'IMMEDIATE',
        'A',
        1
    FROM display_point a, t_build b
    WHERE a.build_id = b.build_id
    AND b.width = $width
    ";
    if ( !executePDOQuery($sql, $ds) ) return false;
    }
    }

    return true;
}


?>