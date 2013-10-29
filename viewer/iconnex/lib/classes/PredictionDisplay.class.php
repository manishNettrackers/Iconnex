<?php
/**
* PredictionDisplay
*
* Datamodel for table prediction_display
*
*/

class PredictionDisplay extends DataModel
{
    public $text = "";

    // These reference instances are required to allow prediction to work
    public $location = false;
    public $vehicle = false;
    public $predictionParameters = false;
    public $stopBuild = false;
    public $vehicleBuild = false;
    public $bay_no = false;

    public $outboundQueue = false;

    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "prediction_id" => new DataModelColumn ( $this->connector, "prediction_id", "serial" ),
            "vehicle_id" => new DataModelColumn ( $this->connector, "vehicle_id", "integer" ),
            "route_id" => new DataModelColumn ( $this->connector, "route_id", "integer" ),
            "location_id" => new DataModelColumn ( $this->connector, "location_id", "integer" ),
            "journey_fact_id" => new DataModelColumn ( $this->connector, "journey_fact_id", "integer" ),
            "pub_ttb_id" => new DataModelColumn ( $this->connector, "pub_ttb_id", "integer" ),
            "sequence" => new DataModelColumn ( $this->connector, "sequence", "integer" ),
            "rtpi_eta_sent" => new DataModelColumn ( $this->connector, "rtpi_eta_sent", "datetime" ),
            "rtpi_etd_sent" => new DataModelColumn ( $this->connector, "rtpi_etd_sent", "datetime" ),
            "pub_eta_sent" => new DataModelColumn ( $this->connector, "pub_eta_sent", "datetime" ),
            "pub_etd_sent" => new DataModelColumn ( $this->connector, "pub_etd_sent", "datetime" ),
            "time_last_sent" => new DataModelColumn ( $this->connector, "time_last_sent", "datetime" ),
            "arr_dep_last_sent" => new DataModelColumn ( $this->connector, "arr_dep_last_sent", "char", 1 ),
            "sch_rtpi_last_sent" => new DataModelColumn ( $this->connector, "sch_rtpi_last_sent", "char", 1 ),
            "eta_last_sent" => new DataModelColumn ( $this->connector, "eta_last_sent", "datetime" ),
            "etd_last_sent" => new DataModelColumn ( $this->connector, "etd_last_sent", "datetime" ),
            "counted_down" => new DataModelColumn ( $this->connector, "counted_down", "smallint" ),
            "time_generated" => new DataModelColumn ( $this->connector, "time_generated", "datetime" ),
            );

        $this->tableName = "prediction_display";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("prediction_id" );
        parent::__construct($connector, $initialiserArray);

        $this->initialValues = false;

    }

    function setOutboundQueue()
    {
        $this->outboundQueue = SystemKey::getOutboundQueue($this->connector);
        if ( !$this->outboundQueue )
        {
            echo "Outbound Queue no defined for message delivery - finishing\n";
            die;
        }
    }

    function createIndexes()
    {
        $sql = "CREATE INDEX ix_pred_del_schord on $this->tableName ( journey_fact_id, sequence )";
        $ret = $this->connector->executeSQL($sql);

        $sql = "CREATE INDEX ix_pred_del_loc on $this->tableName ( location_id )";
        $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

/*
** send_countdown_if_appropriate
**
** checks whether a countdown should be sent to a sign ( is within display window, does not cause max num departures to
** be exceeded for sign etc ). If it is valid then send it
*/
function send_countdown_if_appropriate($location_code, $trip_type)
{
	$l_counter = 0;
    $do_send = true;
    $m_unit_send_ct = 0;
    $now = new DateTime();

    // Analyze the countdown parameters to work out which   
    // countdown value is applicable whether to send in hhMM format etc
    // && $return previous values
    $this->get_countdown_values();

    if ( $this->should_send ($trip_type)  )
    {
        // Reload countdown to check its counted down
	    if ( true )  //$this->load( array("shedule_id", "sequence") ) );
        {
            if ( !$this->counted_down )
            {
				switch ( $this->display_type )
                {
                    case "B":
                        $m_status = $this->send_cntdwn_msg();
                        break;
                    case "U":
                        $w_display_line = true;
                        $m_status = $this->send_cntdwn_msg();
                        break;
                    case "T" :
                        $m_status = $this->send_xmcountdownType($this->predictionParameters["countdown_dep_arr"]);
                        break;
                    case "S" :
                        $m_status = $this->send_surtronic_cntdwn_msg($this->predictionParameters["countdown_dep_arr"], $loccode);
                        break;
                    default:
                        // Not bus stop || $bus terminal  - Omnistop, RTIG-XML?
                        $m_status = 0;
                }
				if ( !$m_status ) {
                    $this->text = $this->text. " *FAIL* ";
				} else {
					$l_counter = $l_counter + 1;
				}
			} else {
				echo " Already counted down select failed with status". m_status."\n";
			}
        }
    }
    return $l_counter;
}

/*
** send_cntdwn_msg
**
** Build a countdown message for sending to a bus stop sign or a UDP feed receiver
** This is passed to the outbound queue of the MessageX Message Handler
*/
function send_cntdwn_msg()
{
    $current = new DateTime();
    $current->Sub(new Dateinterval("PT1H"));
	$sqldate = $current->format("Y-m-d H:i:s");
    $ipAddress = false;
    $stopparam = new TempPredictionStopParam($this->connector);
    $stopparam->build_id = $this->stopBuild->build_id;
    $send_count = 0;


    // Get IP to send message to - either last GPRS address in last hour or hard coded UDP receiver
    if ( $this->display_type == "B" )
    {
        $gprsStatus = new UnitStatus($this->connector);
        $gprsStatus->build_id = $this->stopBuild->build_id;
        $status = $gprsStatus->load(false, " and message_time > '$sqldate'");
        $status = $gprsStatus->load();
        if ( $status )
        {
            $ipAddress = $gprsStatus->ip_address;
            $connectTime = $gprsStatus->message_time;
        }
    }
    else
    {
        $stopparam->param_desc = "ipAddress";
        $status = $stopparam->load();
        if ( $status )
        {
            $ipAddress = $stopparam->ipAddress;
            $connectTime = $current->format("Y-m-d H:i:s");
        }
    }

    if ( !$ipAddress )
    {
        $this->text .= " - no current ip address for ".$this->stopBuild->build_code."\n";
	    return false;
    }

    // Dont send if ip address has been assigned to unit more recently
	if ( $this->display_type == "B" ) 
    {
        $gprsStatusDup = new UnitStatus($this->connector);
        $gprsStatusDup->ip_address = $ipAddress;
        $status = $gprsStatusDup->count(array("ip_address"), " and build_id != ".$this->stopBuild->build_id." AND message_time > '".$connectTime."'");
        //echo " build_id != ".$this->stopBuild->build_id." AND message_time > '".$connectTime."'\n";
        if ( $gprsStatusDup->selectCount > 0 )
        {
            $this->text .= " sedncntdown: ".$this->stopBuild->build_code." ip $ipAddress / $connectTime is in use by another unit";
            return false;
        }
    }

	$delivery = new PredictionDelivery($this->connector);
	$delivery->messageType = 0;
	$delivery->addressId = $this->stopBuild->build_code;
	$delivery->serviceCode = $this->service_code;
	$delivery->bay_no = $this->bay_no;

	$l_vehicle_code = $this->vehicle->vehicle_code;
	$l_build_code = $this->vehicleBuild->build_code;
	$l_build_id = $this->vehicleBuild->build_id;

                         

    // Send unitId of 0 if ( $this is an autoroute
	// || $if ( $this is a CONT && $the vehicle is already expected at the stop for a REAL route.
    if ( $l_vehicle_code == "AUT" || $this->sch_rtpi_last_sent == "P" ) 
			$delivery->unitId = 0;
	else
    {
        if ( !$this->vehicleBuild->build_code )
        {
		    $this->text .= "	send_cntdwn_msg: Failed to get build_code for vehicle_id ".  $this->vehicle->vehice_code;
			return false;
        }
		else 
        {
            $delivery->unitId = $this->vehicleBuild->build_code;
		}
	}

	$delivery->journeyId = $this->pub_ttb_id;

    if ( $this->predictionParameters->countdown_dep_arr == "A" ) 
        $time_string = $this->eta_last_sent;
    else
        $time_string = $this->etd_last_sent;

    $countdown_time_dt = DateTime::createFromFormat("Y-m-d H:i:s", $time_string);
	$delivery->countdownTime = $countdown_time_dt->getTimestamp();


    $stopparam->param_desc = "countdownMsgType";
	$countdownType = 470;
    if ( $stopparam->load() && $stopparam->param_value )
        $countdownType = $stopparam->param_value;

    $stopparam->param_desc = "destinationType";
	$destinationColumn = "dest_short1";
    if ( $stopparam->load() && $stopparam->param_value )
    {
        if ( $stopparam->param_value == "DEST50" )
            $destinationColumn = "dest_long";
    }

    $destination = new Destination($this->connector);
    $destination->dest_id = $this->dest_id;
    if ( !$destination->load() )
    {
        $this->text .= " Unable to fetch destination for id $this->dest_id";
    }
    if ( !$destination->dest_short1 && $destination->dest_long ) $destination->dest_short1 = $destination->dest_long;
    if ( !$destination->dest_short1 && $destination->terminal_text ) $destination->dest_short1 = $destination->terminal_text;
    if ( !$destination->dest_long && $destination->dest_short1 ) $destination->dest_long = $destination->dest_short1;
    
    $destinationText = $destination->$destinationColumn;
    if ( $countdownType == 450 )
        $destinationText = substr($destinationText, 0, 15);

    $delivery->cntdwn_msg_ver = $countdownType;

    // Get vehicle and operator details
    $delivery->wheelchairAccess = 0;
    $delivery->operatorId = 0;
    if ( $this->vehicle->vehicle_id != 0 && $this->vehicle->vehicle_code != "AUT" )
    {
        // We have a rela vehicle tracking the prediction trip, get missing vehicle and operator details
        // so we can get operator loc_prefix - not sure why?
        if ( !$this->vehicle->load() )
        {
            $this->text .= " Cant load vehicle for prediction";
            return false;
        }

        $operator = new Operator($this->connector);
        $operator->operator_id = $this->vehicle->operator_id;
        if ( !$operator->load() )
        {
            $this->text .= " Cant load operator for prediction";
            return false;
        }
        $delivery->wheelchairAccess = $this->vehicle->wheelchair_access;
        $delivery->operatorId = $operator->loc_prefix;
    }

	// Get whether || $not an acknowledgment is currently required for
	// countdown messages.
    //echo "TODO ackreqd\n";
	//$l_ack_reqd = $get_ack_reqd(450);

    $now = new DateTime();
    $delivery->id = 0;
    $delivery->messageType = $countdownType;
    $delivery->journey_fact_id = $this->journey_fact_id;
    $delivery->sequence = $this->sequencsequence;
    $delivery->send_time = $now->format("Y-m-d H:i:s"); 
    $delivery->pred_type = "C";
    $delivery->display_mode = $this->predictionParameters->countdown_dep_arr;
    $delivery->rtpi_eta_sent = $this->rtpi_eta_sent;
    $delivery->rtpi_etd_sent = $this->rtpi_etd_sent;
    $delivery->pub_eta_sent = $this->pub_eta_sent;
    $delivery->pub_eta_sent = $this->pub_etd_sent;
    $delivery->prediction = $time_string;

      	$this->setOutboundQueue();

	$this->text = $this->text.  ": >>> ".
			$ipAddress. " ".  
				$this->sch_rtpi_last_sent. " ". 
				$delivery->serviceCode. " ". 
				substr($destinationText, 0, 15). " ".
				substr($time_string,11,8);

	//$m_status = $set_c_countdown_message();
	if ( true )
    {
			switch($this->display_type)
            {
				case "U" :
					//$m_status = $message_to_queue(m_external_sys_id,w_ip_address,LENGTH(w_ip_address),0,w_stop_build,l_ack_reqd)
                    break;
    	    	case "B" :
                    $junk1 = 0;
                    $terminatingZero = 0;
                    $send_count++;
                    $destlen=strlen($destinationText);
                    //           I     RL T
                    if ( $delivery->messageType == 518 )
                    {
                        $msg = pack("A3Sa18SSSSSIA6SIIiCa10A${destlen}I",
                        "PHP ",
                        3,          // message type

                        $ipAddress, // destination
                        1,          // repeats
                        $destlen + 48,         // message length
                        0,          // portNumber

                        $delivery->messageType,
                        $junk1,
                        $delivery->addressId,
                        substr($delivery->serviceCode, 0, 6),
                        $delivery->operatorId,
                        $delivery->unitId,
                        $delivery->journeyId,
                        $delivery->countdownTime,
                        $delivery->wheelchairAccess,
                        $delivery->bay_no,
                        $destinationText,
                        $terminatingZero
                        );
                    }
                    else
                        $msg = pack("A3Sa18SSSSSIA6SIIiCA${destlen}I",
                        "PHP ",
                        3,          // message type

                        $ipAddress, // destination
                        1,          // repeats
                        $destlen + 38,         // message length
                        0,          // portNumber

                        $delivery->messageType,
                        $junk1,
                        $delivery->addressId,
                        substr($delivery->serviceCode, 0, 6),
                        $delivery->operatorId,
                        $delivery->unitId,
                        $delivery->journeyId,
                        $delivery->countdownTime,
                        $delivery->wheelchairAccess,
                        $destinationText,
                        $terminatingZero
                        );
echo " Tid $delivery->bay_no, $delivery->messageType, $delivery->addressId, $delivery->serviceCode, $delivery->operatorId, $delivery->unitId, $delivery->journeyId, $delivery->countdownTime, $delivery->wheelchairAccess, $destinationText\n";
                if ( $this->outboundQueue )
                {
                    if (!msg_send ($this->outboundQueue, 1, $msg ) )
                    {
                        $this->text .= "Failed to send event to route tracker message queue";
                        echo $this->text."\n";
                    }
                }
			}
	}

    if ( $send_count == 0 ) 
    {
        $this->text = $this->text. " UNSENT OUT OF DATE";
    }

    //echo "sent $send_count <BR>";
	return $send_count;

}


function clear_countdown_if_appropriate($trip_type = false)
{
	$l_counter = 0;
    $do_send = true;
    $m_unit_send_ct = 0;
    $now = new DateTime();

    // Analyze the countdown parameters to work out which   
    // countdown value is applicable whether to send in hhMM format etc
    // && $return previous values
    $this->get_countdown_values();

    if ( $this->should_send ($trip_type)  || $in_ttb_id )
    {
        // Reload countdown to check its counted down
	    if ( $this->load( array("journey_fact_id", "sequence") ) )
        {
            if ( !$this->counted_down )
            {
				switch ( $thislay_type )
                {
                    case "B":
                        if ( $in_ttb_id != 0 ) 
                            $m_status = $this->send_cleardown_aut(m_outboundq_id, in_ttb_id);
                        else
                            $m_status = $this->send_cleardown(m_outboundq_id, $vehicle_build_id);
                        break;
                    case "U":
                        $m_status = $send_cleardown_external_system(m_outboundq_id,$vehicle_build_id);
                        break;
                    default:
                       	// Not bus stop || $bus terminal  - Omnistop, XMLT, RTIG-XML?
                       	$m_status = 0;
                 }
                 $this->counted_down = 1;

                 if ( !$this->save(array("journey_fact_id", "sequence" ) ) )
                    echo "	send_messages: Failed to send cleardown message to ". $in_stop_build."\n";
                 else
					$l_counter = $l_counter + 1;
            }
			else
				echo " Already counted down select failed with status\n";
        }
	} 
    else 
    {
		$log_msg = UtilityDateTime::currentTime(). "	send_messages:  schedule:".
			$this->journey_fact_id. " ". $wr_act_rte["start_code"].
			" - not sending because  ". $in_stop_build. " has disabled ".
			$this->predictionParameters->disabled;
		echo $log_msg."\n";
	}

	return $l_counter;
}

// -----------------------------------------------------------------------------
// Function : should_send
// -----------------------------------------------------------------------------
//
// Decides whether || $not the times should be sent to a stop
// Will be true if
//    AUT bus has not been sent for 5 minutes
//    within display_window
//
// Parameters
// ----------
// None
// -----------------------------------------------------------------------------
function should_send($trip_type)
{

    // --------------------------------------------------------------------
	// Is the sign not enabled?
    // --------------------------------------------------------------------
    if ( $this->predictionParameters->disabled == "X" ) 
    {
        $this->text = $this->text. "DIS";
		return false;
    }

    // --------------------------------------------------------------------
    // Surtronic communications protocol displays (TCP)
    // --------------------------------------------------------------------
    if ( $this->display_type == "S" ) 
    {
        // Is the Surtronic sign registered && $ready?
        $sql = 
        "SELECT update_status, channel_number
            INTO l_update_status, l_channel
            FROM unit_status_sign
            WHERE build_id = ". $this->predictionParameters->build_id; 
        $row = $this->connector->fetch1($sql);
        if ( $this->connector->errorCode != 0 || $row["channel_number"] || $row["update_status"] != "T" )
        {
            $this->text = $this->text. "NOT_REGISTERED";
            return false;
        }
    }

    // --------------------------------------------------------------------
	// Is the arrival unsuitable for the delivery mode
    // --------------------------------------------------------------------
	if ( $this->predictionParameters->delivery_mode && $this->predictionParameters->delivery_mode != "RCA" ) {
		$l_delivery_code = substr($trip_type, 0, 1);
		if ( !strstr($this->predictionParameters->delivery_mode, $l_delivery_code) ) {
            $this->text = $this->text. "DIS";
            return false;
		}
	}

    $currtime = new Datetime();
    $eta_last_sent = DateTime::createFromFormat("Y-m-d H:i:s", $this->eta_last_sent);
    $etd_last_sent = DateTime::createFromFormat("Y-m-d H:i:s", $this->etd_last_sent);

    // --------------------------------------------------------------------
	// Is arrival time is within echo window || $has passed
    // --------------------------------_------------------------------------
    if ( $this->predictionParameters->countdown_dep_arr == "A" ) {
	    $l_comp_secs = $eta_last_sent->getTimestamp() - $currtime->getTimestamp();
    } else {
	    $l_comp_secs = $etd_last_sent->getTimestamp() - $currtime->getTimestamp();
    }

    //echo "HC WIN ";
    $this->predictionParameters->display_window = 7200;
	if ( $l_comp_secs > $this->predictionParameters->display_window || $l_comp_secs < -60 ) {
        if ( $l_comp_secs < -60 ) 
        {
            $this->text = $this->text. " ASSUME COUNTED_DOWN";
            $w_display_line = true;
            return false;
        } else {
            if ( $l_comp_secs < -60 ) {
                $w_display_line = false;
            }
		    $this->text = $this->text. "WIN". $l_comp_secs . "/". $this->predictionParameters->display_window;
		    return false;
        }
	}

    // --------------------------------------------------------------------
	// Has vehicle already arrived/departed, if ( $so clear it down
    // --------------------------------------------------------------------
    if ( 
        ( $this->predictionParameters->countdown_dep_arr == "A" && $this->arrival_status == "A"   ) ||
        ( $this->predictionParameters->countdown_dep_arr == "D" && $this->departure_status == "A" )   
        ) {
        $this->text = $this->text. " Already there Force Clear";
        return false;
    }

    $this->prediction_stop_info->vehicle_id = $this->vehicle_id;
    $this->prediction_stop_info->dest_id = $this->dest_id;
    $this->prediction_stop_info->route_id = $this->route_id;
    $this->prediction_stop_info->build_id = $this->stopBuild->build_id;

    // --------------------------------------------------------------------
	//  Does sign already have enough arrivals
    // --------------------------------------------------------------------
    if ( $this->prediction_stop_info->checkPrediction ("NUMARRS", $this->predictionParameters, $this->initialValues, $this ) != "OK" ) {
        if ( $this->time_last_sent  ) {
            $this->text = $this->text. " AA MA";
        } else {
            $this->text = $this->text. "TOO_MANY_ARRS";
            return false;
        }
    }

	// ------------------------------------------------------------------------
	//  Does sign already have enough arrivals for this destination
	// ------------------------------------------------------------------------
    if ( $this->prediction_stop_info->checkPrediction ("NUMARRSPERDEST", $this->predictionParameters, $this->initialValues, $this ) != "OK" ) {
        if ( $this->time_last_sent  ) {
            $this->text = $this->text. " AA MAD";
        } else {
            $this->text = $this->text. "TOO_MANY_ARRS_FOR_RT_DEST";
            $w_display_line = false;
            return false;
        }
    }

	// ------------------------------------------------------------------------
	// As the bus stop is only able to handle one set of RTPI info 
	// if ( $this arrival is the second || $more arrival of this vehicle at the
    // sign )  convert it to show published time
	// ------------------------------------------------------------------------
    if ( $this->prediction_stop_info->checkPrediction("DUPVEH", $this->predictionParameters, $this->initialValues, $this ) != "OK" ) {
        $this->text = $this->text. " DUPV->P";
        $this->vehicle->vehicle_code = "AUT";
        $this->vehicle->vehicle_id = 0;
    }

	// ------------------------------------------------------------------------
    // Ensure arrival 
	// For sequence/autoroute countdowns ensure resent every 5 minutes
	// ------------------------------------------------------------------------
	if ( $this->time_last_sent  ) {
        if ( $this->sch_rtpi_last_sent != $this->initialValues->sch_rtpi_last_sent ) {
            $this->text = $this->text. " ". $this->initialValues->sch_rtpi_last_sent. "->". $this->sch_rtpi_last_sent;
        } else {
	        if ( $trip_type == "AUT" ) {
                $curr = new DateTime();
                $tls = DateTime::createFromFormat("Y-m-d H:i:s", $this->time_last_sent);
		        $l_sincelast_int = $curr->getTimestamp() - $tls->getTimestamp();
		        $l_at_least_every = 180;
                if ( $l_sincelast_int > $l_at_least_every ) {
                    $this->text = $this->text. " $l_sincelast_int $l_at_least_every PFRC";
                } else {
                    $stat = $this->prediction_stop_info->checkPrediction("DELIVER", $this->predictionParameters, $this->initialValues, $this );

                    $this->prediction_stop_info->arr_no = 0;
                    $this->prediction_stop_info->add();
		            $this->text = $this->text. " AUTlast ". $this->time_last_sent;
                    $w_display_line = false;
                    return false;
		        }
            } else {
                // --------------------------------------------------------------------
	            //  Does prediction deviate enough from previous prediction
                // --------------------------------------------------------------------
                if ( $this->prediction_stop_info->checkPrediction ("HASCHANGEDENOUGH", $this->predictionParameters, $this->initialValues, $this ) != "OK" ) {
                    // Force every x  seconds
                    $curr = new DateTime();
                    $tls = DateTime::createFromFormat("Y-m-d H:i:s", $this->time_last_sent);
		            $l_sincelast_int = $curr->getTimestamp() - $tls->getTimestamp();
		            $l_at_least_every = 120;
                    if ($this->display_type == "S" ) {
                        $this->text = $this->text. " SNO_CHANGE";;
                        return false;;
                    } else {
		                if ( $l_sincelast_int > $l_at_least_every ) {
                            $this->text = $this->text. " FRC";
                        } else {
                            $this->text = $this->text. " NO_CHANGE";
                            return false;
                        }
                    }
                } else {
                    $this->text = $this->text. " ";
                }
    
                //this->prediction_stop_info->checkPrediction("DELIVER", $this->predictionParameters, $this->initialValues, $this ) != "OK" ) 
		        //$this->text = $this->text. " RTPlast ", UtilityDateTime::dateExtract($this->time_last_sent, "hour to second")
                //return false
	        }
        }
	}
    $this->prediction_stop_info->vehicle_id = $this->vehicle_id;
    $this->prediction_stop_info->build_id = $this->build_id;
    $this->prediction_stop_info->route_id = $this->route_id;
    $this->prediction_stop_info->dest_id = $this->dest_id;

    $stat = $this->prediction_stop_info->checkPrediction("DELIVER", $this->predictionParameters, $this->initialValues, $this );

    $this->prediction_stop_info->arr_no = 0;
    $this->prediction_stop_info->add();

	return 1;
}

// -----------------------------------------------------------------------------
// Function : get_countdown_values
// -----------------------------------------------------------------------------
//
// From the dcd_param work out whether countdown should be a
// departure/arrival value, a scheduled || $rtpi time or shown in HHMM 
//
// Parameters
// ----------
// None
// -----------------------------------------------------------------------------
function get_countdown_values()
{

    $this->initialValues = new self();
    foreach ( $this->columns as $k => $v )
    {
        $this->initialValues->$k = $this->$k;
    }
		
    $current = new DateTime();
    if ( $this->vehicle->vehicle_code == "AUT" ) {
        $this->vehicle->vehicle_id = 0;
        $this->sch_rtpi_last_sent = "P";       // for "Published"
        if ( $this->predictionParameters->countdown_dep_arr == "A" ) {
            if ( $this->pub_eta_sent  ) {
                $this->eta_last_sent = $this->pub_eta_sent;
            } else {
                $this->eta_last_sent = $this->rtpi_eta_sent;
            }
        } else {
            if ( $this->pub_etd_sent  ) {
                $this->etd_last_sent = $this->pub_etd_sent;
            } else {
                $this->etd_last_sent = $this->rtpi_etd_sent;
            }
        }
    } else {
        $this->sch_rtpi_last_sent = "R";       // for "Real-time"
        if ( $this->predictionParameters->countdown_dep_arr == "A" ) {
            $this->eta_last_sent = $this->rtpi_eta_sent;
        } else {
            $this->etd_last_sent = $this->rtpi_etd_sent;
        }

        $currtime = new Datetime();
        $eta_last_sent = DateTime::createFromFormat("Y-m-d H:i:s", $this->eta_last_sent);
        $etd_last_sent = DateTime::createFromFormat("Y-m-d H:i:s", $this->etd_last_sent);

//echo "PP VAL ", $this->predictionParameters->pred_pub_after, " ", $this->predictionParameters->disp_pub_after
        // if ( $vehicle more than x minutes away )  use published instead || $disp published instead
        if (  $this->predictionParameters->countdown_dep_arr == "D" ) {
            $l_comp_secs = $etd_last_sent->getTimestamp() - $current->getTimestamp();
        } else {
            $l_comp_secs = $eta_last_sent->getTimestamp() - $current->getTimestamp();
        }

        if ( $l_comp_secs > $this->predictionParameters->pred_pub_after ) {
            if (  $this->predictionParameters->countdown_dep_arr == "A" && $this->pub_eta_sent  ) {
                $this->text = $this->text. " SW->AP". $l_comp_secs. ">". $this->predictionParameters->pred_pub_after ;
                $this->sch_rtpi_last_sent = "P";       // "Published" instead of real-time
                $this->eta_last_sent = $this->pub_eta_sent;
            }
            if (  $this->predictionParameters->countdown_dep_arr == "D" && $this->pub_eta_sent  ) {
                $this->text = $this->text. " SW->DP". $l_comp_secs . ">". $this->predictionParameters->pred_pub_after;
                $this->sch_rtpi_last_sent = "P" ;      // "Published" instead of real-time
                $this->etd_last_sent = $this->pub_etd_sent;
            }
        }

        if ( $l_comp_secs > $this->predictionParameters->disp_pub_after && $this->sch_rtpi_last_sent != "P" ) {
            $this->text = $this->text. " SW->D";
            $this->sch_rtpi_last_sent = "P";
        }
    }

    if ( $this->predictionParameters->countdown_dep_arr == "D" ) {
        $this->text = $this->text.  UtilityDateTime::dateExtract($this->initialValues->etd_last_sent, "hour to second"). "-". $this->initialValues->sch_rtpi_last_sent;
    } else {
        $this->text = $this->text.  UtilityDateTime::dateExtract($this->initialValues->eta_last_sent, "hour to second"). "-". $this->initialValues->sch_rtpi_last_sent;
    }

}

}

?>

