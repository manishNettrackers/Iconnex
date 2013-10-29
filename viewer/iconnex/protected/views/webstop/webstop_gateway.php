<?php 

global $conn;

global $wr_countdowns;
global $wr_operator;
global $wr_dcd_param;
global $wr_destination;
global $wr_dcd_route;
global $wr_dcd_op;
global $wr_dcd_countdown;
global $wr_old_countdown;
global $wr_act_rte;
global $wr_vehicle;
global $wr_location;
global $wr_service;
global $wr_act_rte_loc;
global $wr_route;
global $w_atco_code;
global $w_naptan_code;
global $m_dcd_countdown;

global $locations;
global $in_locations;
global $in_mode;
global $in_show_veh;
global $in_show_auts;
global $w_display_line;
global $m_status;
global $m_comp_int;
global $g_full_debug;
global $txt;
        

$wr_countdowns       = array(
                        "location" => array(),
                        "arrivals" => array(),
                        );
$wr_operator         = array();
$wr_dcd_param        = array();
$wr_destination      = array();
$wr_dcd_route        = array();
$wr_dcd_op           = array();
$wr_dcd_countdown    = array();
$wr_old_countdown    = array();
$wr_act_rte          = array();
$wr_vehicle          = array();
$wr_location         = array();
$wr_service          = array();
$wr_act_rte_loc      = array();
$wr_route            = array();
$w_atco_code         = "";
$w_naptan_code       = "";

$locations           = "";
$in_locations        = "";
$in_mode             = "";
$in_show_veh         = "";
$in_show_auts        = "";
$w_display_line      = 0;
$m_status            = 0;
$m_comp_int           = "";
$txt                  = "";
$g_full_debug         = false;

// -----------------------------------------------------------------------------
// Function : ws
// -----------------------------------------------------------------------------
//
// Parameters
// ----------
// $in_locations List oif ( $location codes, separated by commas, to show buses for
// $in_mode      Should be admin or stop and determines what is displayed
// $in_show_veh  Flag oif ( $whether or not to show vehicle codes ("1" shows them)
// $in_show_auts Flag oif ( $whether or not to show autoroutes ("1" shows them)
// -----------------------------------------------------------------------------
function webstop()
{
    global $conn;
    global $g_full_debug;
echo "oo";
    $msg                 = "";
    $nobus_page          = "";
    $f_dbsname           = "";
    $m_t_dcdrp_refresh   = "";

    $conn = db_connect();

    if ( !executePDOQuery( "SET ROLE centrole", $conn ) ) 
        return false;

    // Parse parameters
    if ( !($in_locations = get_request_item("locations")) )
    {
        echo "Parameters:  location,location stop|admin show_veh[0|1] show_auts[0|1]";
        die;
    }
    if ( get_request_item("debug") )
    {
        $g_full_debug = true;
    }
            
    $in_mode = get_request_item("mode");
    $in_show_veh = get_request_item("show_veh");
    $in_show_auts = get_request_item("show_auts");

    if ( !executePDOQuery( "SET ISOLATION TO DIRTY READ", $conn ) ) return false;

    // Use library to create report to screen
    $m_t_dcdrp_refresh =  build_dcd_param_webstop(get_current_time()->sub(new DateInterval("P1Y")), "m_auth_dbs", $in_locations);
    $m_status = get_location_details($in_locations);
    $m_status = webstop_countdowns($in_locations, "WEBSTOP");
    //echo "#<!C0MPLETE-->";

    return true;
}

// -----------------------------------------------------------------------------
// Function : webstop_countdowns
// -----------------------------------------------------------------------------
//
// Passes through all arrivals for sign
// a prepare countdown for them
//
// Parameters
// ----------
// $in_location - if ( $supplied will generate countdown for the specified location
// $in_mode     - Will be one oif ( :
//                  WEBSTOP - Generates info suitable for webstop display
//                  SIGNS   - Generates info and sends to stop signs
// -----------------------------------------------------------------------------
function webstop_countdowns($in_location, $in_mode)
{
    global $conn;
    global $wr_vehicle;
    global $wr_dcd_countdown;
    global $wr_destination;
    global $wr_act_rte;
    global $wr_service;
    global $wr_route;
    global $wr_operator;
    global $wr_act_rte_loc;
    global $txt;
    global $g_full_debug;

    $l_location_id   = 0;
    $l_update_disp   = 0;
    $l_cntdown_time   = "";
    $l_last_stop      = 0;
    $loccode         = "";
    $c_status        = 0;
    $old_veh_id       = 0;
    $old_loc_id       = 0;
    $sql_str          = "";
    $display_debug    = 0;
    $debug            = 0;
    $sql_str1         = "";
    $l_display_window  = 0;
    $ch_status         = "";
    $l_t1              = "";
    $l_t2             = "";
    $l_i2           = "";
    $i_t3             = "";
    $lct              = 0;
    $sel_str          = "";
    $last_sent_sch   = 0;
    $last_sent_order = 0;
    $l_status        = 0;
    $lr_dcd_param   = array(
                        "operator_id" => 0,
                        "route_id" => 0,
                        "location_id" => 0,
                        "build_id" => 0,
                        "display_type" => "",
                        "day_of_week" => 0,
                        "wef_time" => "",
                        "wet_time" => "",
                        "max_arrivals" => 0,
                        "max_dest_arrivals" => 0,
                        "pred_pub_after" => 0,
                        "disp_pub_after" => 0,
                        "display_window" => 0,
                        "countdown_dep_arr" => "",
                        "delivery_mode" => "",
                        "update_thresh_low" => 0,
                        "update_thresh_high" => 0,
                        "loop_sleep" => 0,
                        "disabled" => ""
                       );

    $sql = '
    SELECT location.location_id, 
                location.location_code, 
                service_patt.dest_id, 
                    arrival_status, departure_status,
                    route.route_code, 
                    route.route_id, 
                    service.description servdesc, 
                    active_rt.route_id, 
                    active_rt.trip_status, 
                    active_rt.start_code, 
                    active_rt.schedule_id, 
                    active_rt.trip_no, 
                    active_rt.vehicle_id, 
                    active_rt_loc.rpat_orderby, 
                    active_rt.pub_ttb_id, 
                    active_rt_loc.arrival_time, 
                    active_rt_loc.departure_time, 
                    active_rt_loc.arrival_time_pub,
                    active_rt_loc.departure_time_pub,
                    active_rt_loc.arrival_status,
                    active_rt_loc.departure_status,
                    service_patt.dest_id,
                    vehicle.vehicle_id,
                    vehicle.vehicle_code,
                    vehicle.wheelchair_access,
                    operator.operator_id,
                    t_dcd_param.operator_id,
                    t_dcd_param.route_id dp_route_id,
                    t_dcd_param.location_id,
                    t_dcd_param.build_id,
                    t_dcd_param.day_of_week,
                    t_dcd_param.wef_time,
                    t_dcd_param.wet_time,
                    t_dcd_param.max_arrivals,
                    t_dcd_param.max_dest_arrivals,
                    t_dcd_param.pred_pub_after,
                    t_dcd_param.disp_pub_after,
                    t_dcd_param.display_window,
                    t_dcd_param.countdown_dep_arr,
                    t_dcd_param.delivery_mode,
                    t_dcd_param.update_thresh_low,
                    t_dcd_param.update_thresh_high,
                    t_dcd_param.loop_sleep,
                    t_dcd_param.disabled
            FROM active_rt, active_rt_loc, publish_tt, service, service_patt, route, operator, vehicle,
                    t_dcd_param, location
            WHERE 1 = 1
            AND active_rt_loc.schedule_id = active_rt.schedule_id
            AND location.location_id = active_rt_loc.location_id
            AND active_rt.vehicle_id = vehicle.vehicle_id
            AND active_rt.route_id = route.route_id
            AND active_rt_loc.actual_est != "C"
            AND route.operator_id = operator.operator_id
            AND route.route_id = t_dcd_param.route_id
            AND active_rt_loc.location_id = t_dcd_param.location_id
            AND service.service_id = service_patt.service_id
            AND active_rt_loc.rpat_orderby = service_patt.rpat_orderby
            AND active_rt.pub_ttb_id = publish_tt.pub_ttb_id
            AND publish_tt.service_id = service.service_id
            ORDER BY location.location_id, 
                     active_rt_loc.departure_time,
                     active_rt_loc.schedule_id,
                     active_rt_loc.rpat_orderby';
    // DISPLAY " "
    $old_veh_id = false;
    $old_loc_id = false;
    $last_sent_sch = false;
    $last_sent_order = false;
    $wr_dcd_countdown = array();
    $wr_old_countdown = array();

    // Consider each active_rt relevant to the current display_point
    // to decide whether or not to send a message
    $lct = 0;
    $l_t1 = get_current_time();

    $display_debug = false;
    if ( !($rid = executePDOQuery($sql, $conn )) ) return false;

    $row = fetchPDO ($rid, "NEXT");
    $rowct = 0;
    while(is_array($row))
    {
            $w_display_line = true;
            $l_location_id = $row["location_id"];
            $loccode = $row["location_code"];
            $wr_route["route_code"] = $row["route_code"];
            $wr_route["route_id"] = $row["route_id"];

            // Generate dcd_countodwn record from activer toeu details as may not be one
            // already for this location

            $wr_route["route_code"] = $row["route_code"]; 
            $wr_act_rte["schedule_id"] = $row["schedule_id"]; 
            $wr_act_rte["trip_no"] = $row["trip_no"]; 
            $wr_act_rte["vehicle_id"] = $row["vehicle_id"]; 
            $wr_act_rte["pub_ttb_id"] = $row["pub_ttb_id"]; 
            $wr_act_rte["trip_status"] = $row["trip_status"]; 
            $wr_act_rte["start_code"] = $row["start_code"]; 
            $wr_act_rte_loc["rpat_orderby"] = $row["rpat_orderby"]; 
            $wr_act_rte_loc["arrival_time"] = $row["arrival_time"]; 
            $wr_act_rte_loc["departure_time"] = $row["departure_time"]; 
            $wr_act_rte_loc["arrival_time_pub"] = $row["arrival_time_pub"];
            $wr_act_rte_loc["departure_time_pub"]	= $row["departure_time_pub"];
            $wr_act_rte_loc["arrival_status"] = $row["arrival_status"]; 
            $wr_act_rte_loc["departure_status"] = $row["departure_status"]; 
            $wr_service["description"] = $row["servdesc"]; 

            $wr_dcd_countdown = array();
            $wr_dcd_countdown["schedule_id"] = $wr_act_rte["schedule_id"];
            $wr_dcd_countdown["rpat_orderby"] = $wr_act_rte_loc["rpat_orderby"];
            $wr_dcd_countdown["rtpi_eta_sent"] = $wr_act_rte_loc["arrival_time"];
            $wr_dcd_countdown["rtpi_etd_sent"] = $wr_act_rte_loc["departure_time"];
            $wr_dcd_countdown["pub_eta_sent"] = $wr_act_rte_loc["arrival_time_pub"];
            $wr_dcd_countdown["pub_etd_sent"] = $wr_act_rte_loc["departure_time_pub"];
            $wr_destination["dest_id"] = $row["dest_id"]; 

            $wr_vehicle["vehicle_id"] = $row["vehicle_id"];
            $wr_vehicle["vehicle_code"] = $row["vehicle_code"];
            $wr_vehicle["wheelchair_access"] = $row["wheelchair_access"];
            $wr_operator["operator_id"] = $row["operator_id"];
            $dcd_param["operator_id"] = $row["operator_id"];
            $dcd_param["route_id"] = $row["route_id"];
            $dcd_param["location_id"] = $row["location_id"];
            $dcd_param["build_id"] = $row["build_id"];
            $dcd_param["day_of_week"] = $row["day_of_week"];
            $dcd_param["wef_time"] = $row["wef_time"];
            $dcd_param["wet_time"] = $row["wet_time"];
            $dcd_param["max_arrivals"] = $row["max_arrivals"];
            $dcd_param["max_dest_arrivals"] = $row["max_dest_arrivals"];
            $dcd_param["pred_pub_after"] = $row["pred_pub_after"];
            $dcd_param["disp_pub_after"] = $row["disp_pub_after"];
            $dcd_param["display_window"] = $row["display_window"];
            $dcd_param["countdown_dep_arr"] = $row["countdown_dep_arr"];
            $dcd_param["delivery_mode"] = $row["delivery_mode"];
            $dcd_param["update_thresh_low"] = $row["update_thresh_low"];
            $dcd_param["update_thresh_high"] = $row["update_thresh_high"];
            $dcd_param["loop_sleep"] = $row["loop_sleep"];
            $dcd_param["disabled"] = $row["disabled"];

            $lr_dcd_param["operator_id"] = $row["operator_id"];
            $lr_dcd_param["route_id"] = $row["route_id"];
            $lr_dcd_param["location_id"] = $row["location_id"];
            $lr_dcd_param["build_id"] = $row["build_id"];
            if ( !$lr_dcd_param["build_id"] )
                $lr_dcd_param["build_id"] = 0;
            $lr_dcd_param["day_of_week"] = $row["day_of_week"];
            $lr_dcd_param["wef_time"] = $row["wef_time"];
            $lr_dcd_param["wet_time"] = $row["wet_time"];
            $lr_dcd_param["max_arrivals"] = $row["max_arrivals"];
            $lr_dcd_param["max_dest_arrivals"] = $row["max_dest_arrivals"];
            $lr_dcd_param["pred_pub_after"] = $row["pred_pub_after"];
            $lr_dcd_param["disp_pub_after"] = $row["disp_pub_after"];
            $lr_dcd_param["display_window"] = $row["display_window"];
            $lr_dcd_param["countdown_dep_arr"] = $row["countdown_dep_arr"];
            $lr_dcd_param["delivery_mode"] = $row["delivery_mode"];
            $lr_dcd_param["update_thresh_low"] = $row["update_thresh_low"];
            $lr_dcd_param["update_thresh_high"] = $row["update_thresh_high"];
            $lr_dcd_param["loop_sleep"] = $row["loop_sleep"];
            $lr_dcd_param["disabled"] = $row["disabled"];

            $lct = $lct + 1;
            if ( !$old_loc_id || $old_loc_id != $l_location_id ) {
                $ch_status =  sign_countdowns_ws ("CREATE", 0, 0, 0, $lr_dcd_param, $wr_dcd_countdown, $wr_dcd_countdown);
            }

            $txt = ic_format_time(get_current_time(), 'Y-m-d H:i:s'). " ". $lct . " ". "L:". trim($loccode) ;
            $txt .=  " S:". trim($wr_vehicle["vehicle_code"]). 
                    " C:". trim($wr_act_rte["pub_ttb_id"]).
                    " O:". trim($wr_dcd_countdown["rpat_orderby"]).
                    " R:". trim($wr_route["route_code"]). 
                    " T:". trim($wr_act_rte["trip_no"]);

            // //-------------------------------------------------
            // In order to not allow trip end points to display times
            // for arrival and departure oif ( $two successive trips
            // Ensure no arrival times are sent to last point
            // in trip if ( $dcd_param indicates Departure Type
            // This should probably be extended to not send departures
            // where dcd_param is set to arrival
            // //-------------------------------------------------
            $sql = "SELECT MAX(rpat_orderby) last_stop
                FROM active_rt_loc
                WHERE active_rt_loc.schedule_id = ".$wr_act_rte["schedule_id"];
            $row1 = executePDOQueryScalar( $sql, $conn );
           
            $l_last_stop = $row1["last_stop"]; 
            if ( !$row1 || ( $l_last_stop == $wr_dcd_countdown["rpat_orderby"] && $wr_act_rte["start_code"] != "CONT" ) )
            {
                $txt .=  "   Skipping - Last stop of failed to get last stop";
                $old_veh_id = $wr_act_rte["vehicle_id"];
                $old_loc_id = $l_location_id;
                $wr_old_countdown = $wr_dcd_countdown;
                $row = fetchPDO ($rid, "NEXT");
                continue;
            }
            // ------------------------------------------------------------
            // Set cleardown mode to departure for the first stop on a trip
            // ------------------------------------------------------------
            if ( $wr_dcd_countdown["rpat_orderby"] == 1 && ( !$lr_dcd_param["countdown_dep_arr"] || $lr_dcd_param["countdown_dep_arr"] != "D" ) ) {
                $txt = trim($txt).  "!LOC1->D";
                $lr_dcd_param["countdown_dep_arr"] = "D";
            }

            if ( $wr_act_rte["trip_status"] =="A" ) {

                if ( $lr_dcd_param["countdown_dep_arr"] == "A" ) {
                    $txt = trim($txt). " ". date_to_format($wr_dcd_countdown["rtpi_eta_sent"], "H:i:s"). "/";
                } else {
                    $txt = trim($txt). " ". date_to_format($wr_dcd_countdown["rtpi_etd_sent"], "H:i:s"). "/";
                }

                // send message and update
                $m_status = get_countdown($loccode, "COUNTDOWN", $lr_dcd_param);
                if ( $m_status == 0 ) {
                    $txt = trim($txt). " IGNORED!!";
                   } else {
                    $txt = trim($txt). " YES!!";
                    $last_sent_sch = $wr_dcd_countdown["schedule_id"];
                    $last_sent_order = $wr_dcd_countdown["rpat_orderby"];
                }


            }

            if ( $g_full_debug || ( $display_debug AND $w_display_line ) ) {
                echo trim($txt)."<BR>";
            }
            $old_veh_id = $wr_act_rte["vehicle_id"];
            $old_loc_id = $l_location_id;
            $wr_old_countdown = $wr_dcd_countdown;

            $row = fetchPDO ($rid, "NEXT");
        $rowct++;
    }

    $l_t2 = get_current_time();
    $i_t3 = $l_t2->diff($l_t1);
    //echo "Time to Generate Stop Times: ". $i_t3->format("%s");

    return 0;
}

// -----------------------------------------------------------------------------
// Function : get_countdown_values
// -----------------------------------------------------------------------------
//
// From the dcd_param work out whether countdown should be a
// departure/arrival value, a scheduled or rtpif ( $time or shown in HHMM 
//
// Parameters
// ----------
// None
// -----------------------------------------------------------------------------
function get_countdown_values($in_dcd_param)
{
        
    $ch_status              = "";
    $l_comp_secs            = 0;
    $l_delivery_code        = "";
    global $txt;

    global $wr_old_countdown;
    global $wr_dcd_countdown;
    global $wr_vehicle;

    $wr_dcd_countdown["eta_last_sent"] = "";
    $wr_dcd_countdown["etd_last_sent"] = "";
    $wr_old_countdown = $wr_dcd_countdown;
    if ( $wr_vehicle["vehicle_code"] == "AUT" ) {
        $wr_vehicle["vehicle_id"] = 0;
        $wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
        if ( $in_dcd_param["countdown_dep_arr"] == "A" ) {
            if ( !$wr_dcd_countdown["pub_eta_sent"] ) {
                $wr_dcd_countdown["eta_last_sent"] = $wr_dcd_countdown["pub_eta_sent"];
            } else {
                $wr_dcd_countdown["eta_last_sent"] = $wr_dcd_countdown["rtpi_eta_sent"];
            }
        } else {
            if ( !$wr_dcd_countdown["pub_etd_sent"] ) {
                $wr_dcd_countdown["etd_last_sent"] = $wr_dcd_countdown["pub_etd_sent"];
            } else {
                $wr_dcd_countdown["etd_last_sent"] = $wr_dcd_countdown["rtpi_etd_sent"];
            }
        }
    } else {
        $wr_dcd_countdown["sch_rtpi_last_sent"] = "R";
        if ( $in_dcd_param["countdown_dep_arr"] == "A" ) {
            $wr_dcd_countdown["eta_last_sent"] = $wr_dcd_countdown["rtpi_eta_sent"];
        } else {
            $wr_dcd_countdown["etd_last_sent"] = $wr_dcd_countdown["rtpi_etd_sent"];
        }
//display "PP VAL ", $in_dcd_param["pred_pub_after"], " ", $in_dcd_param["disp_pub_after"]
        // if ( $vehicle more than x minutes away ) { use published instead or disp published instead
        //$wr_dcd_countdown["eta_etd_last_sent"] = $wr_dcd_countdown["eta_etd_last_sent"] + 10 UNITS MINUTE
		$tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["etd_last_sent"]);
		if ($tmpval == NULL)
			$tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["eta_last_sent"]);

		$m_comp_int = $tmpval->diff(get_current_time());

        $l_comp_secs = ic_HHMMSS_to_Seconds($m_comp_int);

        if ( $l_comp_secs > $in_dcd_param["pred_pub_after"] ) {
            if ( $in_dcd_param["countdown_dep_arr"] == "A" && !$wr_dcd_countdown["pub_eta_sent"] ) {
                $txt = trim($txt). " SW->AP". $l_comp_secs. ">". $in_dcd_param["pred_pub_after"];
                $wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
                $wr_dcd_countdown["eta_last_sent"] = $wr_dcd_countdown["pub_eta_sent"];
            }
            if (  $in_dcd_param["countdown_dep_arr"] == "D" && !$wr_dcd_countdown["pub_eta_sent"] ) {
                $txt = trim($txt). " SW->DP". $l_comp_secs. ">". $in_dcd_param["pred_pub_after"];
                $wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
                $wr_dcd_countdown["etd_last_sent"] = $wr_dcd_countdown["pub_etd_sent"];
            }
        }

        $tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["etd_last_sent"]);
        if ( $tmpval == NULL )
        {
    		if ( $wr_vehicle["vehicle_code"] != "AUT" ) {
            	$wr_dcd_countdown["sch_rtpi_last_sent"] = "R";
			}
			else
				$wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
        }
        else
        {
            $interval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["etd_last_sent"])->diff(get_current_time());
            $l_comp_secs = ic_HHMMSS_to_Seconds($interval);
            if ( $l_comp_secs > $in_dcd_param["disp_pub_after"] && $wr_dcd_countdown["sch_rtpi_last_sent"] <> "P" ) {
                $txt = trim($txt). " SW->D";
                $wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
            }
			if ( $wr_vehicle["vehicle_code"] != "AUT" )
				$wr_dcd_countdown["sch_rtpi_last_sent"] = "R";
		else
				$wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
        }
    }

			if ( $wr_vehicle["vehicle_code"] == "AUT" )
				$wr_dcd_countdown["sch_rtpi_last_sent"] = "P";
    $txt = trim($txt)." ".date_to_format($wr_old_countdown["etd_last_sent"], "His"). "-". $wr_dcd_countdown["sch_rtpi_last_sent"];

}

// -----------------------------------------------------------------------------
// Function : should_show
// -----------------------------------------------------------------------------
//
// Decides whether or not the times should be sent to a stop
// Will be true if
//    AUT bus has not been sent for 5 minutes
//    within display_window
//
// Parameters
// ----------
// None
// -----------------------------------------------------------------------------
function should_show($in_dcd_param)
{
    $l_display_mode  = "";
    $loccode         = "";
        
    $ch_status               = "";
    $l_comp_secs             = 0;
    $curr_time               = "";
    $l_delivery_code         = "";

    global $wr_act_rte;
    global $wr_route;
    global $wr_vehicle;
    global $wr_act_rte_loc;
    global $wr_dcd_countdown;
    global $wr_old_countdown;
    global $wr_destination;
    global $txt;

    // --------------------------------------------------------------------
    // Is the sign not enabled?
    // --------------------------------------------------------------------
    if ( $in_dcd_param["disabled"] == "X" ) {
        $txt .= "DIS";
        return false;
    }
    // --------------------------------------------------------------------
    // Is the arrival unsuitable for the delivery mode
    // --------------------------------------------------------------------
    if ( strlen ($in_dcd_param["delivery_mode"]) > 0 && $in_dcd_param["delivery_mode"] != "RCA" ) {
        $l_delivery_code = substr($wr_act_rte["start_code"], 0, 1);
        if ( !strstr($in_dcd_param["delivery_mode"], $l_delivery_code) ) {
            $txt .= "UNS";
            return false;
        }
    }

    $curr_time = get_current_time();

    // --------------------------------------------------------------------
    // Is arrival time is within display window or has passed
    // --------------------------------------------------------------------
    $tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["etd_last_sent"]);
    if ($tmpval == FALSE)
		$tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["eta_last_sent"]);
    if ($tmpval == FALSE)
		$tmpval = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["rtpi_etd_sent"]);

    $m_comp_int = $tmpval->diff($curr_time);
    $l_comp_secs = ic_HHMMSS_to_Seconds($m_comp_int);
    if ( $l_comp_secs > $in_dcd_param["display_window"] || $l_comp_secs < -60 ) {
        if ( $l_comp_secs < -60 ) {
            $txt .= " ASSUMED COUNTED_DOWN";
            $w_display_line = true;
            return false;
        } else {
            if ( $l_comp_secs < -60 ) {
                $w_display_line = true;
            }
            $txt .=  "WIN". $l_comp_secs. "/". $in_dcd_param["display_window"];
            return false;
        }
    }

    // --------------------------------------------------------------------
    // Is arrival time is within display window or has passed
    // --------------------------------------------------------------------
    $m_comp_int = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["rtpi_etd_sent"])->diff($curr_time);
    $l_comp_secs = ic_HHMMSS_to_Seconds($m_comp_int);
    if ( $l_comp_secs > $in_dcd_param["display_window"] || $l_comp_secs < -60 ) {
        if ( $l_comp_secs < -60 ) {
            $txt .=  " ASSUME COUNTED_DOWN";
            $w_display_line = true;
            return false;
        } else {
            if ( $l_comp_secs < -60 ) {
                $w_display_line = true;
            }
            $txt .=  "WIN". $l_comp_secs. "/". $in_dcd_param["display_window"];
            return false;
        }
    }

    // --------------------------------------------------------------------
    // Has vehicle already arrived/departed, if ( $so clear it down
    // --------------------------------------------------------------------
    if ( ( 
        $in_dcd_param["countdown_dep_arr"] == "A" && $wr_act_rte_loc["arrival_status"] == "A"  OR
        $in_dcd_param["countdown_dep_arr"] == "D" && $wr_act_rte_loc["departure_status"] == "A"  
        ) ) {
        $txt .=  " Already there Force Clear";
        return false;
    }

    // --------------------------------------------------------------------
    //  Does sign already have enough arrivals
    // --------------------------------------------------------------------
    if ( sign_countdowns_ws ("NUMARRS", $wr_vehicle["vehicle_id"], $wr_destination["dest_id"], $wr_route["route_id"], $in_dcd_param, $wr_old_countdown, $wr_dcd_countdown) <> "OK" ) {
        $txt .=  "TOO_MANY_ARRS";
        return false;
    }

    // --------------------------------------------------------------------
    //  Does sign already have enough arrivals
    // --------------------------------------------------------------------
    if ( sign_countdowns_ws ("NUMARRSPERDEST", $wr_vehicle["vehicle_id"], $wr_destination["dest_id"], $wr_route["route_id"], $in_dcd_param, $wr_old_countdown, $wr_dcd_countdown) <> "OK" ) {
        $txt .=  "TOO_MANY_ARRS_FOR_RT_DEST";
        $w_display_line = true;
        return false;
    }

    // ---------------------------------------------------
    // As the bus stop is only able to handle one set oif ( RTPif ( $info 
    // if ( $this arrival is the second or more arrival oif ( $this vehicle at the
    // sign ) { convert ito show published time
    // ---------------------------------------------------
    if ( sign_countdowns_ws("DUPVEH", $wr_vehicle["vehicle_id"], $wr_destination["dest_id"], $wr_route["route_id"], $in_dcd_param, $wr_old_countdown, $wr_dcd_countdown) <> "OK" ) {
        $txt .=  " DUPV->P";
        $wr_vehicle["vehicle_code"] = "AUT";
        $wr_vehicle["vehicle_id"] = 0;
    }

    $ch_status = sign_countdowns_ws("DELIVER", $wr_vehicle["vehicle_id"], $wr_destination["dest_id"], $wr_route["route_id"], $in_dcd_param, $wr_old_countdown, $wr_dcd_countdown);

    return 1;

}

// -----------------------------------------------------------------------------
// Function : get_countdown
// -----------------------------------------------------------------------------
// Gets information to display on webstpo for a specific countdown
// -----------------------------------------------------------------------------
function get_countdown($loccode, $send_mode, $in_dcd_param)
{

    global $m_dcd_countdown;
    global $wr_vehicle;
    global $wr_countdowns;

    $sel_str         = "";
    $l_counter       = 0;
    $l_delivery_code = "";
    $l_connect_date  = "";
    $l_counted_down  = "";
    $do_send         = 0;

    $l_counter = 0;
    $do_send = true;

    // Analyze the countdown parameters to work out which   
    // countdown value is applicable whether to send in hhMM format etc
    // and return previous values
    get_countdown_values($in_dcd_param);
    if ( should_show ($in_dcd_param) ) {
        $m_status = get_countdown_for_display($in_dcd_param);
        format_web_stop_data($in_dcd_param);

        // Dont show AUT values that are in the past
        $m_now1 =  get_current_time()->modify("-30 minutes");

        $m_comp_int = ic_HHMMSS_to_Seconds(DateTime::createFromFormat('Y-m-d H:i:s', $m_dcd_countdown["countdown_time"])->diff($m_now1));
        if ( $m_comp_int > 0 && 0 == 1) {
           return 1;
        }
        $l_counter = $l_counter + 1;

        $wr_countdowns["arrivals"][] = array(
            "service_code" => $m_dcd_countdown["service_code"],
            "destination1" => $m_dcd_countdown["destination_text"], 
            "destination2" => $m_dcd_countdown["destination_text"],
            "eta" => $m_dcd_countdown["display_mins"],
            "pub" => $m_dcd_countdown["output_pub_hhmm"],
            "time" => $m_dcd_countdown["countdown_time"],
            "vehicle_code" => $wr_vehicle["vehicle_code"]
            );
    }

    return $l_counter;

} 

function get_countdown_for_display($in_dcd_param)
{
    global $conn;
    global $wr_service;
    global $wr_vehicle;
    global $wr_dcd_countdown;
    global $wr_act_rte;
    global $wr_operator;
    global $m_dcd_countdown;

    $time_string = "";
    $l_vehicle_code  = "";
    $l_wheelchair_access = 0;
    $l_ack_reqd      = 0;
    $compare_date    = "";
    $compare_int     = "";
    $sel_str         = "";
    $sel_str2        = "";
    $l_connect_date  = "";
    $l_param_value   = "";
    $l_cntdwn_msg    = 0;
    $l_dest_column   = "";
    $l_count         = 0;
    $l_log_time      = "";
    $l_send_ct       = 0;

    $m_dcd_countdown["message_type"] = 0;
    $m_dcd_countdown["service_code"] = $wr_service["description"];

    $l_vehicle_code = $wr_vehicle["vehicle_code"];

    // Send unitId oif ( 0 if ( $this is an autoroute
    // or if ( $this is a CONT and the vehicle is already expected at the stop for a REAL route
    if ( $l_vehicle_code == "AUT" || $wr_dcd_countdown["sch_rtpi_last_sent"] == "P" ) {
        $m_dcd_countdown["unit_id"] = 0;
    }
    
    $m_dcd_countdown["journey_Id"] = $wr_act_rte["pub_ttb_id"];

    if ( $in_dcd_param["countdown_dep_arr"] == "A" ) {
        $time_string = $wr_dcd_countdown["eta_last_sent"];
        $l_log_time = $wr_dcd_countdown["eta_last_sent"];
        $m_dcd_countdown["countdown_time"] = $wr_dcd_countdown["eta_last_sent"];
    } else {
        $time_string = $wr_dcd_countdown["etd_last_sent"];
        $l_log_time = $wr_dcd_countdown["etd_last_sent"];
        $m_dcd_countdown["countdown_time"] = $wr_dcd_countdown["etd_last_sent"];
    }

    $l_dest_column = "dest_long";
    $sql =
                    "SELECT ". trim($l_dest_column). " destination".
                    " FROM destination, service_patt, publish_tt". 
                    " WHERE destination.dest_id = service_patt.dest_id". 
                    " AND service_patt.rpat_orderby = ". $wr_dcd_countdown["rpat_orderby"]. 
                    " AND service_patt.service_id = publish_tt.service_id".
                    " AND publish_tt.pub_ttb_id = ". $wr_act_rte["pub_ttb_id"];
    
    $row1 = executePDOQueryScalar( $sql, $conn );
    $m_dcd_countdown["destination_text"] = $row1["destination"];

    $m_dcd_countdown["cntdwn_msg_ver"] = $l_cntdwn_msg;
    $m_dcd_countdown["wheelchair_access"] = $wr_vehicle["wheelchair_access"];
    $m_dcd_countdown["operator_id"] = $wr_operator["operator_id"];

    return 0;

}

// ----------------------------------------------------------------------------
// Function : build_dcd_param_webstop
// -----------------------------------------------------------------------------
//
// Creates temporary table containing, for each route the dcd_parameters
// (display_window, update thresholds etc) to be used taking into
// account the global dcd_parametes, the operator specific ones and the 
// route specific ones
//   
// Parameters
// ----------
// None
// ----------------------------------------------------------------------------
function build_dcd_param_webstop($l_t_dp_refresh, $l_auth_dbs, $l_location_code)
{
    $f_current_time  = "";
    $f_interval      = "";
    $l_build_id      = 0;
    $l_unit_type     = "";
    $l_build_code    = "";
    $l_param_val     = "";
    $l_sel           = "";
    $l_dcd_param     = array();
    $l_now_hhmmss    = "";
    $field_ct        = 0;
    $l_sql           = "";
    $field_clause    = "";
    $where_clause    = "";
    $value_clause    = "";

    $f_interval = new DateInterval("P10M");
    $f_current_time = get_current_time();

    global $conn;

    if ( ic_HHMMSS_to_Seconds($f_current_time->diff($l_t_dp_refresh)) > ic_HHMMSS_to_Seconds($f_interval) ) {

        // DISPLAY "Building DCD Parameters 
        executePDOQuery("DROP TABLE t_dcd_param", $conn, true );


        $sql = "CREATE TEMP TABLE t_dcd_param
            (
                operator_id integer,
                route_id integer,
                location_id integer,
                build_id integer,
                display_type char(1),
                day_of_week integer,
                wef_time datetime hour to second,
                wet_time datetime hour to second,
                max_arrivals integer,
                max_dest_arrivals integer,
                pred_pub_after integer,
                disp_pub_after integer,
                display_window integer,
                countdown_dep_arr char(1),
                delivery_mode char(5),
                update_thresh_low integer,
                update_thresh_high integer,
                loop_sleep integer,
                disabled char(1)
            ) WITH NO LOG";
       if ( !executePDOQuery($sql, $conn, false ) ) 
           return false;

       // build table containing every combination oif ( $operator/route/location/build

       $sql = '
        INSERT INTO t_dcd_param
                (
                operator_id,
                route_id,
                location_id,
                display_type,
                max_arrivals,
                max_dest_arrivals,
                pred_pub_after,
                disp_pub_after,
                display_window,
                countdown_dep_arr,
                delivery_mode,
                update_thresh_low,
                update_thresh_high,
                loop_sleep )
            SELECT UNIQUE a.operator_id, a.route_id, 
                e.location_id, 
                    "B",
                    9,
                    9,
                    3600,
                    3600,
                    0,
                    "A",
                    "RCA",
                    0,
                    0,
                    30
            FROM route a, service b, service_patt c, location e
            WHERE a.route_id = b.route_id
            AND b.service_id = c.service_id
            AND c.location_id = e.location_id
            AND TODAY BETWEEN wef_date AND wet_date
            AND c.location_id = e.location_id
            AND (  e.location_code MATCHES "'.$l_location_code.'" )';

       if ( !executePDOQuery( $sql, $conn ) ) 
           return false;

       $sql = "
            SELECT dcd_param.*
             FROM dcd_param
             WHERE dcd_param.build_id IS NULL
             UNION ALL
            SELECT dcd_param.*
              FROM dcd_param, unit_build
              WHERE dcd_param.build_id = unit_build.build_id
                    and build_code = 'WEBSTOP'
             ORDER BY level";
       if ( !( $rid = executePDOQuery($sql, $conn ) )) return false;

       $row = fetchPDO ($rid, "NEXT");
       while(is_array($row))
       {
            $l_dcd_param = $row;

            $field_clause = "";
            $value_clause = "";
            $field_ct = 0;

            if ( $l_dcd_param["max_arrivals"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "max_arrivals";
                $value_clause = trim($value_clause). $l_dcd_param["max_arrivals"];
            }
   
            if ( $l_dcd_param["max_dest_arrivals"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "max_dest_arrivals";
                $value_clause = trim($value_clause). $l_dcd_param["max_dest_arrivals"];
            }
  
            if ( $l_dcd_param["pred_pub_after"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "pred_pub_after";
                $value_clause = trim($value_clause). $l_dcd_param["pred_pub_after"];
            }
 
            if ( $l_dcd_param["disp_pub_after"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause).",";
                    $value_clause = trim($value_clause).",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "disp_pub_after";
                $value_clause = trim($value_clause). $l_dcd_param["disp_pub_after"];
            }

            if ( $l_dcd_param["display_window"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "display_window";
                $value_clause = trim($value_clause). $l_dcd_param["display_window"];
            }

            if ( $l_dcd_param["countdown_dep_arr"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "countdown_dep_arr";
                $value_clause = trim($value_clause). "'". trim($l_dcd_param["countdown_dep_arr"]). "'";
            }

            if ( $l_dcd_param["delivery_mode"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "delivery_mode";
                $value_clause = trim($value_clause). "'". trim($l_dcd_param["delivery_mode"]). "'";
            }

            if ( $l_dcd_param["update_thresh_low"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "update_thresh_low";
                $value_clause = trim($value_clause). $l_dcd_param["update_thresh_low"];
            }

            if ( $l_dcd_param["update_thresh_high"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "update_thresh_high";
                $value_clause = trim($value_clause). $l_dcd_param["update_thresh_high"];
            }

            if ( $l_dcd_param["disabled"]) {
                if ( $field_ct > 0 ) {    
                    $field_clause = trim($field_clause). ",";
                    $value_clause = trim($value_clause). ",";
                }
                $field_ct = $field_ct + 1;
                $field_clause = trim($field_clause). "disabled";
                $value_clause = trim($value_clause). "'". trim($l_dcd_param["disabled"]). "'";
            }

            // if ( LENGTH ( field_clause ) == 0 ) {
                // DISPLAY "NOT SETTING"
            // }

            $where_clause = " WHERE 1 = 1";

            if ( $l_dcd_param["operator_id"]) {
                $where_clause = trim($where_clause). 
                    " AND operator_id = ". $l_dcd_param["operator_id"];
            }

            if ( $l_dcd_param["route_id"]) {
                $where_clause = trim($where_clause). 
                    " AND route_id = ". $l_dcd_param["route_id"];
            }

            if ( $l_dcd_param["location_id"] ) {
                    $where_clause = trim($where_clause). 
                    " AND location_id = ". $l_dcd_param["location_id"];
            }

            // Not relevant for Webstop
            // if ( $l_dcd_param["build_id"] IS NOT NULL ) {
                // $where_clause = trim($where_clause), 
                    // " AND build_id = ", l_dcd_param["build_id"]
            // }

            if ( $l_dcd_param["day_of_week"]) {
                if ( get_current_time()->format("w") != $l_dcd_param["day_of_week"] ) {
                    echo "IGNORING DOW SPECIFIER ". $l_dcd_param["day_of_week"]. " vs ". get_current_time()->date_format("%w"); 
                    $row = fetchPDO ($rid, "NEXT");
                    continue;
                }
            }

            if ( ( $l_dcd_param["wef_time"] && !$l_dcd_param["wet_time"] ) ||
                ( $l_dcd_param["wef_time"] && !$l_dcd_param["wet_time"] ) ) {
                echo "INVALID DCD EFFECTIVE TIMES". $l_dcd_param["wef_time"]. "/". $l_dcd_param["wet_time"];
                $row = fetchPDO ($rid, "NEXT");
                continue;
            }

            if ( $l_dcd_param["wef_time"] && $l_dcd_param["wet_time"]) {
                $l_now_hhmmss = ic_format_time(get_current_time(), 'Hi');
                if ( $l_now_hhmmss < $l_dcd_param["wef_time"] || $l_now_hhmmss > $l_dcd_param["wet_time"] ) {
                    //echo "Current ". $l_now_hhmmss. " OUTSIDE ". $l_dcd_param["wef_time"]. "/". $l_dcd_param["wet_time"]. " ignoring<BR>";
                    $row = fetchPDO ($rid, "NEXT");
                    continue;
                }
            }

            if ( strlen ($field_clause) == 0 ) {
                //echo "IGNORED NOTHING TO SET"."<BR>";
                $row = fetchPDO ($rid, "NEXT");
                continue;
            }
            $sql = "UPDATE t_dcd_param SET ( ". trim($field_clause). ") = ( ".
                    trim($value_clause). ") ". trim($where_clause);
            if ( !executePDOQuery($sql, $conn ) ) return false;
            $row = fetchPDO ($rid, "NEXT");

    }

    $sql = "CREATE INDEX i_t_dcd_param ON t_dcd_param (route_id)";
    if ( !executePDOQuery($sql, $conn ) ) return false;
    $sql = "CREATE INDEX i_t_dcd_param2 ON t_dcd_param (build_id)";
    if ( !executePDOQuery($sql, $conn ) ) return false;
    $sql = "CREATE INDEX i_t_dcd_param3 ON t_dcd_param (location_id)";
    if ( !executePDOQuery($sql, $conn ) ) return false;
    
    $l_t_dp_refresh = get_current_time();
    $row = fetchPDO ($rid, "NEXT");
    }

    return $l_t_dp_refresh;
    
} 

function format_web_stop_data($in_dcd_param)
{
      $in_locations         = "";
      $in_dep_arr_mode      = "";
      $in_show_auts         = "";
      $in_report_file       = "";
      $locations            = "";
      $curr_time            = "";
      $working_time         = "";
      $l_filename           = "";
      $base_dir             = "";
      $out_path             = "";
      $out_file             = "";
      $cmd_str              = "";
      $exit_val             = 0;
      $route_sel_str        = "";
      $loc_sel_str          = "";
      $w_service_id         = 0;
      $w_service_desc       = "";
      $wr_active_rt         = array();
      $w_location_id        = 0;
      $l_vehicle_code       = "";
      $est_dep_arr          = "";
      $pub_time             = "";
      $pub_dep_time         = "";
      $dep_arr_status       = "";
      $loc_area             = "";
      $loc_description      = "";
      $destination          = "";
      $l_prev_order         = "";
      $l_prev_etd           = "";
      $l_passed_prev        = 0;
      $passed_prev_int      = "";
      $l_rpat_order         = "";
      $wait_time            = "";
      $wait_time_all_secs   = 0;
      $wait_time_mins       = 0;
      $wait_time_secs       = 0;
      $display_mins         = 0;
      $l_last_stop          = "";
      $l_status             = 0;
      $est_hr               = "";
      $est_mn               = "";

      $curr_time = get_current_time();

      global $wr_dcd_countdown;
      global $m_dcd_countdown;

      if ( $in_dcd_param["countdown_dep_arr"] == "A" ) {
          $working_time = $wr_dcd_countdown["eta_last_sent"];
      } else {
          $working_time = $wr_dcd_countdown["etd_last_sent"];
      }

      // Calculate various values for display times
      // Actual display time_string is decided in the report
      $wait_time = DateTime::createFromFormat('Y-m-d H:i:s', $working_time)->diff($curr_time);
      $wait_time_all_secs = ic_HHMMSS_to_Seconds($wait_time);

        // include information about buses which have arrived/departed
        // the stop up to halif ( $an hour ago
      if ( $wait_time_all_secs > 30 ) {
         // First get the wait_time in total seconds
         $wait_time_all_secs = ic_HHMMSS_to_Seconds($wait_time);

         // Split the total seconds into minutes and seconds;
         $wait_time_mins = floor($wait_time_all_secs / 60);
         $wait_time_secs = $wait_time_all_secs - ($wait_time_mins * 60);

         // Round up the seconds to get the minutes to display
         if ( $wait_time_secs > 30 ) {
            $display_mins = $wait_time_mins + 1;
         } else {
            $display_mins = $wait_time_mins;
         }

            // The minimum we display is 1 minute
         if ( $display_mins == 0 ) {
            $display_mins = 1;
         }

        // Convert the publish departure into simpler format
        //$m_dcd_countdown["output_pub_hhmm"] = convert_pub_time(pub_time);
      }

      $m_dcd_countdown["output_pub_hhmm"] = "";
      if ( $wr_dcd_countdown["sch_rtpi_last_sent"] == "R" ) {
        $m_dcd_countdown["display_mins"] = $display_mins. "m";
        if ( $wait_time_all_secs < 30 ) {
           $m_dcd_countdown["display_mins"] = "Due";
        }
        $est_hr_dt = DateTime::createFromFormat('Y-m-d H:i:s', $wr_dcd_countdown["pub_etd_sent"]);
        // TODO figure out what could cause this
        if ($est_hr_dt == NULL) {
            $est_hr = "To";
            $est_mn = "Do";
        }
        else {
            $est_hr = $est_hr_dt->format('H');
            $est_mn = $est_hr_dt->format('i');
        }
        $m_dcd_countdown["output_pub_hhmm"] = $est_hr. $est_mn;
      } else {
         $est_hr = DateTime::createFromFormat('Y-m-d H:i:s', $working_time )->format('H');
         $est_mn = DateTime::createFromFormat('Y-m-d H:i:s', $working_time )->format('i');
         $m_dcd_countdown["output_pub_hhmm"] = $est_hr. $est_mn;
         $m_dcd_countdown["display_mins"] = "P";
         if ( $wait_time_all_secs < -30 ) {
            $m_dcd_countdown["display_mins"] = "D";
         }
      }


      //$m_dcd_countdown["display_mins"] = display_mins
   
}

function get_location_details($in_loc)
{

    global $conn;
    global $wr_location;
    global $wr_countdowns;
    global $w_atco_code;
    global $w_naptan_code;


    $sql = "SELECT location.description, atco_code, naptan_code
        FROM location, outer stop
        WHERE stop.atco_code = location.location_code
        AND location.location_code = '".trim($in_loc)."'";
//			OR stop.atco_code = '1980' || location.location_code)
    $row1 = executePDOQueryScalar( $sql, $conn );
    $wr_location["description"] = trim($row1["description"]);
    $w_atco_code = trim($row1["atco_code"]);
    $w_naptan_code = trim($row1["naptan_code"]);
    $wr_countdowns["location"] = array(
            "description" => $wr_location["description"],
            "atco" => $w_atco_code, 
            "naptan" => $w_naptan_code,
            "time" => ic_format_time(get_current_time(), 'H:i:s'));

    return 0;
}
function sign_countdowns_ws ($in_mode, $in_vehicle, $in_dest, $in_route, $in_dcd_param, $in_old_countdown, $in_new_countdown)
{

    $l_count             = 0;
    $changed             = 0;
    $tmp_interval        = 0;
    global $conn;

    if ( $in_mode == "CREATE" ) {
        executePDOQuery( "DROP TABLE t_countdowns", $conn, true );
        $sql = "CREATE TEMP TABLE t_countdowns
            (
                arr_no      SERIAL,
                vehicle_id  INTEGER,
                build_id    INTEGER,
                route_id    INTEGER,
                dest_id     INTEGER
            )
            WITH NO LOG";
        if ( !executePDOQuery( $sql, $conn ) ) return "FAIL";
        return "OK";
    }

    if ( $in_mode == "DELIVER" ) {
        $sql = "INSERT INTO t_countdowns
                VALUES (
                    0, $in_vehicle, ".$in_dcd_param["build_id"].", $in_route, $in_dest 
                )";
        if ( !executePDOQuery($sql, $conn ) ) return "FAIL";
        return "OK";
    }

    if ( $in_mode == "NUMARRS" ) {
        $sql = "SELECT COUNT(*) cnt FROM t_countdowns";
        $row1 = executePDOQueryScalar( $sql, $conn );
        $l_count =$row1["cnt"];
          //WHERE build_id = $in_dcd_param["build_id"]
        if ( $l_count >= $in_dcd_param["max_arrivals"] ) {
            return "TOOMANY";
        } else { 
            return "OK";
        }
    }

    if ( $in_mode == "NUMARRSPERDEST" ) {
        $sql = "SELECT COUNT(*) cnt
          FROM t_countdowns
          WHERE route_id = $in_route
            AND dest_id = $in_dest";
        $row1 = executePDOQueryScalar( $sql, $conn );
        $l_count =$row1["cnt"];
            //AND build_id = $in_dcd_param["build_id"]
        if ( $l_count >= $in_dcd_param["max_dest_arrivals"] ) {
            return "TOOMANY";
        } else { 
            return "OK";
        }
    }

    if ( $in_mode == "DUPVEH" ) {
        if ( $in_vehicle == 0 ) {
            return "OK";
        }
        $sql = "SELECT COUNT(*)  cnt
          FROM t_countdowns
         WHERE vehicle_id = $in_vehicle";
        $row1 = executePDOQueryScalar( $sql, $conn );
        $l_count =$row1["cnt"];
        if ( $l_count > 0 ) {
            return "DUPVEH";
        } else { 
            return "OK";
        }
    }
 
    if ( $in_mode == "HASCHANGEDENOUGH" ) {
        $changed = false;
        if ( !$changed ) {
            if ( $in_dcd_param["countdown_dep_arr"] == "A" ) {
                $tmp_interval = ic_HHMMSS_to_Seconds( DateTime::createFromFormat('Y-m-d H:i:s', $in_new_countdown["eta_last_sent"])->diff(  DateTime::createFromFormat('Y-m-d H:i:s', $in_old_countdown["eta_last_sent"])) );
            } else {
                $tmp_interval = ic_HHMMSS_to_Seconds( DateTime::createFromFormat('Y-m-d H:i:s', $in_new_countdown["etd_last_sent"])->diff(  DateTime::createFromFormat('Y-m-d H:i:s', $in_old_countdown["etd_last_sent"]) ));
            }
        
            if ( $in_dcd_param["update_thresh_low"] > $tmp_interval || $in_dcd_param["update_thresh_high"] < $tmp_interval ) {
                //DISPLAY " => Tolerance allows countdown update ",
                    //$in_dcd_param["update_thresh_low"] using "-<<<<<&", " < ",
                    //tmp_interval using "-<<<<<&", " < ",
                    //$in_dcd_param["update_thresh_high"] using "-<<<<<&"
                $changed = true;
            } else {
                $sql = "INSERT INTO t_countdowns
                    VALUES (
                        0, $in_vehicle, ".$in_dcd_param["build_id"].", $in_route, $in_dest 
                    )";
                if ( !executePDOQuery($conn, $sql ) ) return "FAIL";
                //DISPLAY " => Tolerance prevents countdown update ",
                    //$in_dcd_param["update_thresh_low"] using "-<<<<<&", " < ",
                    //tmp_interval using "-<<<<<&", " < ",
                    //$in_dcd_param["update_thresh_high"] using "-<<<<<&"
                $changed = false;
            }
        }

        if ( $changed == true ) {
            return "OK";
        } else {
            return "NOTCHANGED";
        }
    }

    // DISPLAY "INVALID SIGN_COUNTDOWN CODE"
    return "INVALID";

}

function webstop_display_json()
{
    global $wr_countdowns;

    header('Content-Type: text/json');
    echo json_encode($wr_countdowns);
}

function webstop_display()
{
    global $wr_countdowns;
    global $g_full_debug;


    if ( $g_full_debug )
        header('Content-Type: text/html');
    else
        header('Content-Type: text/xml');
    echo "<webstop>";
    echo "  <atco_code>".$wr_countdowns["location"]["atco"]."</atco_code>";
    echo "  <naptan_code>".$wr_countdowns["location"]["naptan"]."</naptan_code>";
    echo "  <common_name>".$wr_countdowns["location"]["description"]."</common_name>";

    $ct = 0;

    foreach ( $wr_countdowns["arrivals"] as $k => $v )
    {
        $route = $v["service_code"];
        $dest1 = $v["destination1"];
        $dest2 = $v["destination2"];
        $duein = $v["eta"];
        $pub = $v["pub"];
        $vehcd = $v["vehicle_code"];
        if ( $duein != "D" )
        {
            if ( $duein == "P" || $vehcd == "AUT")
            {
                $duein=substr($pub, 0, 2).":".substr($pub, 2, 2);
                $mins=1;
            }
            else
                $mins=preg_replace("/m.*/", "", $duein);
            

            if ( $mins > 59 )
            {
                continue;
            }

            if ( $ct == 0 )
                echo "<calls>";
            echo "<call>";
            echo "<route>$route</route>";
            echo "<destination>$dest1</destination>";
            echo "<eta>$duein</eta>";
            echo "</call>";
            $ct++;
        }

    }
    if ( $ct > 0 )
        echo "</calls>";
  
    echo "</webstop>";

}

echo "oo";
webstop();
?>
