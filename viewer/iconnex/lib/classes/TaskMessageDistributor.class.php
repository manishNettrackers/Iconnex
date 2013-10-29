<?php

/**
** Class: TaskMessageDistributor
** --------------------------------
**
** Passes through all the messages scheduled for display on signs
** and delivers them
*/

class TaskMessageDistributor extends ScheduledTask
{
    private $tempDisplayPoint;
    private $tempPredictionServiceParam;
    private $tempPredictionStopParam;
    private $tempPredictionLocationParam;
    private $tempLocationMode;
    private $tempBuildAddress;
    private $resetSystem = "ACTIVE";

    private $keyOutboundQueue = false;
    private $keyXMLTLPQueue = false;
    private $keySurtronicSolarQueue = false;
    private $keyUDPServerQueue = false;
    private $keyDBQueue = false;
    private $scheduledListRefresh = false;
    private $delivery = false;

    private $bundles = false;

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {

        // Prepare Connection
        $this->connector->setDirtyRead();
        $this->connector->executeSQL("SET LOCK MODE TO WAIT 10");


        // Get outbound delivery queue
        $this->setOutboundQueue();

        while ( true )
        {
            // Build Required Temporary Tables on a periodic refresh basis
            $this->buildTemporaryTables();

            // Clear out dcd prediction history up to 2 hours ago
            $this->connector->executeSQL("DELETE FROM dcd_prediction WHERE send_time < CURRENT - 2 UNITS HOUR");
		
            // Update displays with messages
            $this->deliverMessages();

            sleep(10);
        }
    }

    /*
    ** Get access to outbound message queue
    */
    function setOutboundQueue()
    {  
        $this->outboundQueue = SystemKey::getOutboundQueue($this->connector);
        if ( !$this->outboundQueue )
        {  
            echo "Outbound Queue no defined for message delivery - finishing\n";
            die;
        }
    }

    /*
    ** buildTemporaryTables
    **
    ** reate temporary tables for storing
    ** route specific prediction parameters (display window etc )
    ** stop specific prediction params ( display window, countdown to arrival etc )
    **
    */
    function buildTemporaryTables()
    {
        // Build Working Stop Display Point Table
        if ( !$this->tempDisplayPoint )
            $this->tempDisplayPoint = new TempDisplayPoint($this->connector);
        $this->tempDisplayPoint->buildTable();

        // Build Service Specific Prediction Parameters
        //if ( !$this->tempPredictionServiceParam )
            //$this->tempPredictionServiceParam = new TempPredictionServiceParam($this->connector);
        //$this->tempPredictionServiceParam->buildTable();

        // Build Stop Specific Prediction Parameters
        if ( !$this->tempPredictionStopParam )
            $this->tempPredictionStopParam = new TempPredictionStopParam($this->connector);
        $this->tempPredictionStopParam->buildTable();

        // Build Location Specific Prediction Parameters
        //if ( !$this->tempPredictionLocationParam )
            //$this->tempPredictionLocationParam = new TempPredictionLocationParam($this->connector);
        //$this->tempPredictionLocationParam->buildTable();

        // Build Location Prediction Mode Table
        //if ( !$this->tempLocationMode )
            //$this->tempLocationMode = new TempLocationMode($this->connector);
        //$this->tempLocationMode->buildTable();

        // Build Build Last Ip Adress Table
        //if ( !$this->tempBuildAddress )
            //$this->tempBuildAddress = new TempBuildAddress($this->connector);
        //$this->tempBuildAddress->buildTable();
    }

    /**
    ** Foreach sign message pass through and deliver
    */
    function deliverMessages()
    {
    
	    $m_comp_int = "0 00:20:00";         // Only send to sign if alive in last 20 minutes
	    $m_preempt_int = 10 * 60;         // How long in advance of start time to we send to sign
	    //$m_pendtimeout_int = "00:01:00" 
	    $m_pendgiveup_int = 30 * 60;      // How long before we stop resending unreceived message
	    $m_dcdmesskeep_int = "0 00:30:00";  // How long to keep messages after expired

        // Initialize preloop parameters
        $last_send_build = false;
        $last_group = false;
        $last_build = false;
        $last_ip = false;
        $wr_dcd_message = array();
        $wr_dcd_message_loc = array();

        // Current Time
        $curr_timestamp = UtilityDateTime::currentTimestamp();
        $curr_time = UtilityDateTime::currentTime();

	    // Messages bundled up need to be linked to the first message in the bundle
	    // so that one acknowledgement for the bundle with mark all included messages
	    // as bundled
        $bundle = false;
	    $bundle_msg_id = 0;
        $l_bundles = false;
        $bundle_count = 0;
        $bundle_size = 30;

		$sql = "SELECT	UNIQUE feed, message_text, message_group, dcd_message_loc.*,
					t_display_point.build_id,
					unit_status.ip_address
				FROM dcd_message, dcd_message_loc, t_display_point, unit_status
				WHERE dcd_message_loc.message_id = dcd_message.message_id
				AND dcd_message_loc.build_id = t_display_point.build_id
				AND t_display_point.build_id = unit_status.build_id
				--AND ('$curr_time' - unit_status.message_time) < '$m_comp_int'
				AND ( received is null or message_sent IS NULL OR display_flag = 0 )
                ORDER BY t_display_point.build_id, message_group, feed";
        $c_dcd_msg_loc = $this->connector->executeSQL($sql);

        while ( $row = $c_dcd_msg_loc->fetch() )
        {
			// if ( $it's time for the message to be displayed, ) { we'll send it
			// to all the stops it needs to go to (from dcd_message_loc).
            $wr_dcd_message["message_text"] = trim($row["message_text"]);
            $wr_dcd_message["feed"] = trim($row["feed"]);
            $wr_dcd_message["message_group"] = trim($row["message_group"]);
            $wr_dcd_message["message_id"] = $row["message_id"];
            $wr_dcd_message_loc["message_id"] = $row["message_id"];
            $wr_dcd_message_loc["build_id"] = $row["build_id"];
            $wr_dcd_message_loc["creation_time"] = $row["creation_time"];
            $wr_dcd_message_loc["display_time"] = $row["display_time"];
            $wr_dcd_message_loc["expiry_time"] = $row["expiry_time"];
            $wr_dcd_message_loc["hold_time"] = $row["hold_time"];
            $wr_dcd_message_loc["interleave_mode"] = $row["interleave_mode"];
            $wr_dcd_message_loc["display_style"] = $row["display_style"];
            $wr_dcd_message_loc["activity_mode"] = $row["activity_mode"];
            $wr_dcd_message_loc["message_sent"] = $row["message_sent"];
            $wr_dcd_message_loc["display_flag"] = $row["display_flag"];
            $wr_dcd_message_loc["received"] = $row["received"];
            $wr_dcd_message_loc["bundled_with"] = trim($row["bundled_with"]);


            $w_build_id = $row["build_id"];
            $w_ip_address = trim($row["ip_address"]);

			$w_stop_build = $this->connector->fetch1ValueSQL(
                "SELECT build_code
				FROM unit_build
				WHERE build_id = $w_build_id");


            // Does the sign accept messages bundled up ?
			$l_bundles = false;
            $l_bundles = $this->connector->fetch1ValueSQL(
			            "SELECT param_value
		  	            FROM t_stop_param
		  	            WHERE param_desc = 'messageBundles' 
			            AND build_id = $w_build_id");

            $bundlesize = 30;
            if ( strlen($wr_dcd_message["message_text"]) == 0 ) {
                $wr_dcd_message["message_text"] = "junk";
            }

			// Send a message if it hasnt been sent yet or if it has not
			// not been acknowledged yet && $we are still within the giveup
			// threshold
            $curr_timestamp = UtilityDateTime::currentTimestamp();
            $curr_time = UtilityDateTime::currentTime();
            $curr = UtilityDateTime::currentTime();

			if ( $wr_dcd_message_loc["display_flag"] ) 
            {
                $display = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["display_time"]);
                $expiry = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["expiry_time"]);
                $sent = false;
                if ( $wr_dcd_message_loc["message_sent"] )
                    $sent = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["message_sent"]);

                if ( $display->getTimestamp() < $curr_timestamp + $m_preempt_int
                    && $expiry->getTimestamp() > $curr_timestamp
                    && (
                        !$wr_dcd_message_loc["message_sent"]
                        ||
                        (
                        $wr_dcd_message_loc["message_sent"] &&
                        !$wr_dcd_message_loc["received"] &&
                        $curr_timestamp - $sent->getTimestamp() < $m_pendgiveup_int
                        )
                        )
                    ) {
                    echo "$curr_time Message Sent ".$wr_dcd_message_loc["message_sent"];
                    echo "$curr_time received ".$wr_dcd_message_loc["received"];
                    echo $curr_time. " Send to ". $w_stop_build. " (". $w_ip_address. ") - ". $wr_dcd_message["message_group"]. "/". $wr_dcd_message["feed"]. " = ". 
                                substr($wr_dcd_message["message_text"],0,20)."\n"; 

                    // if the message is part of a group and th stop is configured to accept
                    // bundled messages then add message to mbundle, otherwise just
                    // send it as a singleton
                    if ( !$l_bundles || !$wr_dcd_message["message_group"] ) {
                        $m_status = $this->prep_dcd_msg_for_sending(0, $w_stop_build, $wr_dcd_message, $wr_dcd_message_loc);
                        if ( $m_status == 0 ) {
                            $m_status = $this->send_singleton_message($w_ip_address, $w_stop_build, $wr_dcd_message, $wr_dcd_message_loc);
                        }
                    } else {
                        //if (last_send_build  && $last_send_build != w_build_id ) ||
                        //( bundle_count > 6 ) ||
                        //( last_group  && $last_group != $wr_dcd_message["message_group"] ) ) {
                        if ( ( $last_send_build  && $last_send_build != $w_build_id ) ||
                                ( $bundle_count > $bundlesize ) ) {
                            end_bundle($bundle);
                            $m_status = $send_dcd_msg($last_ip, $last_build);
                        }
                        //if ( !$$last_send_build || $last_send_build != w_build_id OR last_group IS NULL OR 
                        //last_group != $wr_dcd_message["message_group"] || $bundle_count > 5 ) {
                        if ( !$last_send_build || $last_send_build != $w_build_id OR $bundle_count > $bundlesize ) {
                            $bundle_count = 0;
                            start_bundle($bundle);
                            $bundle_msg_id = $wr_dcd_message["message_id"];
                        }
                        $m_status = $this->prep_dcd_msg_for_sending($bundle_msg_id, $w_stop_build, $wr_dcd_message, $wr_dcd_message_loc);
                        $m_status = $this->add_message_to_bundle();
                        $last_send_build = $w_build_id;
                        $last_ip = $w_ip_address;
                        $bundle_count = $bundle_count + 1;
                        $last_build = $w_stop_build;
                    } 
				}
			} else {
                if ( $wr_dcd_message_loc["message_sent"]  ) {
                    // display_flag has changed to false, so send
                    // a message to clear the message from the display.
                    echo "clearing ". $wr_dcd_message_loc["message_sent"].  $wr_dcd_message["message_text"]."\n";
                    $m_status = $this->clear_dcd_msg($w_ip_address, $w_stop_build, $wr_dcd_message);
                }
                
                // For now, just delete the dcd_message_loc entry.
                // In the future, we may leave for re-enablement &&
                // allow a stale loop to archive expired messages.
                // DELETE FROM dcd_message_loc
                // WHERE $dcd_message_loc["message_id"] = $wr_dcd_message_loc["message_id"]
                // && $build_id = $w_build_id
            }
    
            $last_group = $wr_dcd_message["message_group"];
	    }

        // if ( $bundle needs sending, do so
        if ( $last_send_build  ) {
            end_bundle($bundle);
            $m_status = $send_dcd_msg($last_ip, $last_build);
        }

        // Now clear out any dcd_messages that are old $i["e"]. their effective
        // end times are older than
        $sql = "SELECT * from dcd_message 
                join dcd_message_loc on dcd_message.message_id = dcd_message_loc.message_id WHERE current - expiry_time > '$m_dcdmesskeep_int'";
        $c_loc = $this->connector->executeSQL($sql);
        while ( $row = $c_loc->fetcH() )
        {
            echo "DEL ", $row["message_id"]. ", ". $row["message_text"]. $row["location_id"]. $row["display_time"]. $row["expiry_time"]."\n";
        }

        $sql = "DELETE from dcd_message_loc WHERE current - expiry_time > '$m_dcdmesskeep_int'";
        $this->connector->executeSQL($sql);

        $sql = "DELETE FROM dcd_message WHERE message_id NOT IN ( select message_id from dcd_message_loc )";
        $this->connector->executeSQL($sql);

	    return false;

    }

    // -----------------------------------------------------------------------------
    // Function : prep_dcd_msg_for_sending
    // -----------------------------------------------------------------------------
    //
    // builds up a message from the current wr_dcd_message && $sets it up in the
    // runner for sending
    //
    // Parameters
    // ----------
    // Parm1		Description
    // -----------------------------------------------------------------------------
    function prep_dcd_msg_for_sending($bundled_with, $w_stop_build, $wr_dcd_message, $wr_dcd_message_loc)
    {
        $this->delivery = array();
        $this->delivery["messageType"] = 452;
        $this->delivery["addressId"] = $w_stop_build;
        $this->delivery["dcdMessageId"] = $wr_dcd_message["message_id"];
        $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["display_time"]);
        $this->delivery["displayTime"] = $timestamp->getTimestamp();
        $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["expiry_time"]);
        $this->delivery["expiryTime"] = $timestamp->getTimestamp();
        $this->delivery["holdTime"] = $wr_dcd_message_loc["hold_time"];
        $this->delivery["feed"] = $wr_dcd_message["feed"];
        $this->delivery["interleave_mode"] = $wr_dcd_message_loc["interleave_mode"];
        $this->delivery["display_style"] = $wr_dcd_message_loc["display_style"];
        $this->delivery["activity_mode"] = $wr_dcd_message_loc["activity_mode"];
        $this->delivery["messageText"] = "3333333333"; // $wr_dcd_message["message_text"];
        $this->delivery["messageText"] = $wr_dcd_message["message_text"];

        // The feed name is 10 characters long, but with new media displays 
        // this can be much longer so if ( $the feed is longer than 10 ) {
        // include the feed name in the text separated by a pipe symbol &&
        // set the feed name to (MESSAGE)
        if ( strlen($wr_dcd_message["feed"]) > 10 ) {
            $this->delivery["messageText"] = $wr_dcd_message["feed"]. "|". $this->delivery["messageText"];
            $this->delivery["feed"] = "(MESSAGE)";
            //echo "SWAPPED TO ", $this->delivery["messageText"]
        }

        //echo $this->delivery["feed"], ">", $this->delivery["messageText"]
	    // Get whether || $not an acknowledgment is currently required for
	    // CMNO_DCD_MESSAGE message_types.
	    $l_ack_reqd = $this->get_ack_reqd(452);

	    if ( $m_status == 0 ) {
		    if ( $m_status == 0 ) {
			    if ( $bundled_with != 0 ) {
                    $this->connector->executeSQL(
				    "UPDATE dcd_message_loc
					    SET ( bundled_with, message_sent ) = ( bundled_with, CURRENT )
                	    WHERE message_id = ".$wr_dcd_message["message_id"]."
					    AND build_id = ".$wr_dcd_message_loc["build_id"]);
			    } else {
                    $this->connector->executeSQL(
				    "UPDATE dcd_message_loc
					    SET message_sent = CURRENT
                	    WHERE message_id = ".$wr_dcd_message["message_id"]."
					    AND build_id = ".$wr_dcd_message_loc["build_id"]);
			    }
		    }
        }
	    return $m_status;
    }

    // -----------------------------------------------------------------------------
    // Function : send_singleton_message
    // -----------------------------------------------------------------------------
    //
    // Sends a dcd message out
    //
    // Parameters
    // ----------
    // Parm1		Description
    // -----------------------------------------------------------------------------
    function send_singleton_message($ip_address, $build_code, $wr_dcd_message, $wr_dcd_message_loc)
    {
        $junk1 = 0;
        $junk2 = 0;
        $junk3 = 0;
        $junk4 = 0;

        $msglen = strlen($this->delivery["messageText"]);
        //$msg = pack("A3Sa18SSSSSIIIIiiiIA11CCCA6${msglen}I",
        $msg = pack("A3Sa18SSSSSIIiiIa11aaaa${msglen}I",
            "PHP ",
            3,          // message type
            $ip_address, // destination
            1,          // repeats
            $msglen + 50,         // message length
            0,          // portNumber

            $this->delivery["messageType"],
            $junk1,
            $this->delivery["addressId"],
            $this->delivery["dcdMessageId"],
            //$junk2,
            $this->delivery["displayTime"],
            //$junk3,
            $this->delivery["expiryTime"],
            //$junk4,
            $this->delivery["holdTime"],
            $this->delivery["feed"],
            $this->delivery["interleave_mode"],
            $this->delivery["display_style"],
            $this->delivery["activity_mode"],
            $this->delivery["messageText"],
            0
            ) ;
        if ( $this->outboundQueue )
        {
            if (!msg_send ($this->outboundQueue, 1, $msg ) )
            {
                $this->text .= "Failed to send event to route tracker message queue";
                echo $this->text."\n";
            }
        }
	    return $m_status;
    }

    // -----------------------------------------------------------------------------
    // Function : clear_dcd_msg
    // -----------------------------------------------------------------------------
    //
    // Clears the current working dcd_message from the current working
    // dcd_message_loc by sending a message to clear the message
    // by dcdMessageId.
    //
    // Parameters
    // ----------
    // None
    // -----------------------------------------------------------------------------
    function clear_dcd_msg($ip_address, $w_stop_build, $wr_dcd_message)
    {
	    $m_dcd_clrmsg["messageType"] = 0;
	    $m_dcd_clrmsg["addressId"] = $w_stop_build;
	    $m_dcd_clrmsg["dcdMessageId"] = $wr_dcd_message["message_id"];

	    // Get whether || $not an acknowledgment is currently required for
	    // CMNO_DCD_CLRMESSAGE messages.
        $l_ack_reqd = $this->get_ack_reqd(455);

        $junk1 = 0;

        $msglen = strlen($this->delivery["messageText"]);
        //$msg = pack("A3Sa18SSSSSIIIIiiiIA11CCCA6${msglen}I",
        $msg = pack("A3Sa18SSSSSII",
            "PHP ",
            3,          // message type
            $ip_address, // destination
            1,          // repeats
            $destlen + 52,         // message length
            0,          // portNumber

            455,
            $junk1,
            $m_dcd_clrmsg["addressId"],
            $m_dcd_clrmsg["dcdMessageId"]
            ) ;

        if ( $this->outboundQueue )
        {
            if (!msg_send ($this->outboundQueue, 1, $msg ) )
            {
                $this->text .= "Failed to send event to route tracker message queue";
                echo $this->text."\n";
            }
        }
	    return $m_status;
    }

    /*
    **  Begin a message bundle
    */
    function start_bundle(&$bundle)
    {
        $this->bundles = array();
    }

    /*
    **  Create a stop binary messsage and add it to the bundle
    */
    function add_message_to_bundle(&$bundle)
    {
        $this->bundles[] = 
            pack ( "SSIA6SIIiCA${destlen}I",
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
        $this->delivery = array();
        $this->delivery["messageType"] = 0;
        $this->delivery["addressId"] = $w_stop_build;
        $this->delivery["dcdMessageId"] = $wr_dcd_message["message_id"];
        $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["display_time"]);
        $this->delivery["displayTime"] = $timestamp->getTimestamp();
        $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $wr_dcd_message_loc["expiry_time"]);
        $this->delivery["expiryTime"] = $timestamp->getTimestamp();
        $this->delivery["holdTime"] = $wr_dcd_message_loc["hold_time"];
        $this->delivery["feed"] = $wr_dcd_message["feed"];
        $this->delivery["interleave_mode"] = $wr_dcd_message_loc["interleave_mode"];
        $this->delivery["display_style"] = $wr_dcd_message_loc["display_style"];
        $this->delivery["activity_mode"] = $wr_dcd_message_loc["activity_mode"];
        $this->delivery["messageText"] = $wr_dcd_message["message_text"];
    }

    /* Descide whether the message type being sent requires an acknowledgement */
    function get_ack_reqd($msg_type)
    {
        $this->keyOutboundQueue = SystemKey::getKeyValue($this->connector, "AROBQ");
        $l_ack_reqd = $this->connector->fetch1ValueSQL(
			            "SELECT ack_reqd
		  	            FROM message_type
		  	            WHERE msg_type = $msg_type" );
        return $l_ack_reqd;
    }

}
