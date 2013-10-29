<?php

    $rt = $_criteria["route"]->get_criteria_value("VALUE");
    $op = $_criteria["operator"]->get_criteria_value("VALUE");
    $eq = $_criteria["equipped"]->get_criteria_value("VALUE");

    $ar = preg_split("/,/", $eq);

    include("projects/rti/iconnex.php");
    $iconnex = new iconnex($_pdo);

    // -------------------------------------------------------
    // Find locations on selected routes
    // -------------------------------------------------------
    $user = session_request_item("user", false );
    if ( !$user )
    {
	    $user = "admin";
    }

    $opwhere = "";
    $rtwhere = "";
    $eqwhere = "";
    if ( $op ) $opwhere = " and operator.operator_id in ( $op )";
    if ( $rt ) $rtwhere = " and route.route_id in ( $rt )";

    $shownoneq = false;
    $showeq = false;

    if ( $eq && count($ar) == 1 )
    {
        if ( $ar[0] == "'1'" )
            $shownoneq = 1;
        else
            $showeq = 1;
    }
    if ( $showeq ) $eqwhere = " and build_code is not null";

    $sql ="
            SELECT  location_code as stopkey, bay_no bay_no, description stop_name, route_area_code route_area_code, latitude latitude, longitude, build_code build_code, message_time message_time, ip_address ip_address, route_code route, make make, last_impact last_impact, impact_count impact_count, last_bootup last_bootup, bootup_count bootup_count, last_active_hour last_active_hour, last_active_day last_active_day, operator_code operator_code, routes routes, bearing  
            FROM snapshot_stop_status
            JOIN  route_visibility route ON snapshot_stop_status.route_id = route.route_id
            JOIN  operator ON operator.operator_id = route.operator_id
            WHERE usernm = '$user'

            ";
    //if (!$iconnex->executeSQL($sql))
       //return;


    //$golap = 'Tooltips=Routes;Location;Description,'.
        //'Type=Icon,'.
        //'HotspotX=8,'.
        //'HotspotY=8,'.
        //'KeyField=Location,'.
        //'ClickLink=ajax/locationdetailspopup.php?location=<<Location>>,'.
        //'Filters=Make;Activity Status;Equipped;Route;Description,'.
        //'RenderType=DespatcherStop,'.
        //'RenderElements=Description;Routes;Vehicle Code;Make;Impact Count;Activity Status;Equipped';

    //$this->add_assignment('golap', $golap, false );

    for ($ct = 0; $ct < count($this->columns); $ct++)
    {
        $col = $this->columns[$ct];
        if ( $col->query_name == "last_active_hour" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "last_impact" ) $col->attributes["column_display"] = "hide";
        //if ( $col->query_name == "impact_count" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "last_active_day" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "last_bootup" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "bootup_count" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "operator_code" ) $col->attributes["column_display"] = "hide";
        if ( $col->query_name == "view_timetable" ) $col->attributes["column_display"] = "hide";
        //if ( $col->query_name == "route" ) $col->attributes["column_display"] = "hide";
    }
?>
