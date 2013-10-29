<?php

/**
** Class: TaskPredictionDistributor
** --------------------------------
**
** Passes through all the arrival/departure times relevant to bus stops
** and distributes the countdown information to signs over the radio network, 
** and to third parties listening on UD ports etc
*/

class TaskPredictionDistributor extends ScheduledTask
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


        // Fetch Keys
        $this->keyOutboundQueue = SystemKey::getKeyValue($this->connector, "AROBQ");
        $this->keyXMLTLPQueue = SystemKey::getKeyValue($this->connector, "XMLTOBQ");
        $this->keySurtronicSolarQueue = SystemKey::getKeyValue($this->connector, "SURTOBQ");
        $this->keyUDPServerQueue = SystemKey::getKeyValue($this->connector, "UDPSERV");
        $this->keyDBQueue = SystemKey::getKeyValue($this->connector, "ARDBQ");

        // Initialize System refresh state
        $refreshSystemStatus = new SystemKey($this->connector);
        $refreshSystemStatus->key_code = "CLEARSYSTE";
        $refreshSystemStatus->load();
        if ( $refreshSystemStatus->key_value == "REFRESH" )
        {
           $refreshSystemStatus->key_value = "CLEARSYSTE";
           $refreshSystemStatus->save();
        }
        $this->scheduledListRefresh = new DateTime();

        while ( true )
        {

            // work out whether we need system reset
            $refreshSystemStatus->load();

            $reset_system  = $refreshSystemStatus->key_value;

            // Build Required Temporary Tables on a periodic refresh basis
            $this->buildTemporaryTables();

            // Clear out dcd prediction history up to 2 hours ago
            $this->connector->executeSQL("DELETE FROM dcd_prediction WHERE send_time < CURRENT - 2 UNITS HOUR");
		
		    // Now generate any trips to be cancelled || $allocated within the timetable
		    // modifications tables
		    // TODO generate_timetable_mods()

            // Update displays with messages
            $this->deliverPredictions();

            // Clear out old predictions that are well passed their clear date
		    //PredictionDisplay::clearStalePredictions();

		    // DCD messaging now moved into dedicated thread
		    //$m_status = $dcd_messages() 
		    // TODO $m_status = $display_point_files() // files for DynaStop, WebStop etc. 
		    // TODO $m_status = $display_point_omnistop() // times for Bus Station Displays
		    // TODO $m_status = $generate_omnistop() // Create bus station display
											// content

            if ( $reset_system == "CLEAR" || $reset_system == "REFRESH" ) {
                echo "CLEARING EVERYTHING\n";
                $tempDisplayPoint->lastRefresh = false;
                $tempPredictionServiceParam->lastRefresh = false;
                $tempPredictionStopParam->lastRefresh = false;
                $tempPredictionLocationParam->lastRefresh = false;
                $tempLocationMode->lastRefresh = false;
                $this->connector->executeSQL("DELETE FROM dcd_omnistop");
                $this->connector->executeSQL("DELETE FROM prediction_display");
                $this->connector->executeSQL("DELETE FROM timetable_journey_live_duty");
                $this->connector->executeSQL("DELETE FROM timetable_journey_live_lost");
                $this->connector->executeSQL("DELETE FROM timetable_visit_live");
                $this->connector->executeSQL("DELETE FROM timetable_journey_live");
            }

            // if ( $there has been a regeneration of timetable_journey ) { refresh autort
            if ( $reset_system != "CLEAR" ) 
            {
                $ttbuild = new TimetableJourneyBuild($this->connector);
                $newimports = $ttbuild->count(array("none"), " AND created > ".$this->scheduledListRefresh->format("'Y-m-d H:i:s'"));
                if ( $newimports > 0 )
                {
                    echo " ********   New imports occurred - REFRESH Required ***** \n";
                    $reset_system = "REFRESH";
                }
            }
		
		    // generate/run autort_sched
		    if ( $reset_system == "REFRESH" ) 
            {
                if ( $reset_system == "REFRESH" ) {
                    $sql = "UPDATE scheduled_task SET ( last_executed, next_scheduled ) = ( NULL, NULL ) WHERE class_name = 'TaskTimetableGenerator'";
                    $this->connector->executeSQL($sql);
                    $sql = "UPDATE scheduled_task SET ( last_executed, next_scheduled ) = ( NULL, NULL ) WHERE class_name = 'TaskAutoJourneyScheduler'";
                    $this->connector->executeSQL($sql);
                }
            }

            $refreshSystemStatus->key_value = "ACTIVE";
            $refreshSystemStatus->save();
            sleep(10);
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
        if ( !$this->tempPredictionServiceParam )
            $this->tempPredictionServiceParam = new TempPredictionServiceParam($this->connector);
        $this->tempPredictionServiceParam->buildTable();

        // Build Stop Specific Prediction Parameters
        if ( !$this->tempPredictionStopParam )
            $this->tempPredictionStopParam = new TempPredictionStopParam($this->connector);
        $this->tempPredictionStopParam->buildTable();

        // Build Location Specific Prediction Parameters
        if ( !$this->tempPredictionLocationParam )
            $this->tempPredictionLocationParam = new TempPredictionLocationParam($this->connector);
        $this->tempPredictionLocationParam->buildTable();

        // Build Location Prediction Mode Table
        if ( !$this->tempLocationMode )
            $this->tempLocationMode = new TempLocationMode($this->connector);
        $this->tempLocationMode->buildTable();

        // Build Build Last Ip Adress Table
        if ( !$this->tempBuildAddress )
            $this->tempBuildAddress = new TempBuildAddress($this->connector);
        $this->tempBuildAddress->buildTable();

        // Clear out old predictions
        $sql = "DELETE FROM prediction_display WHERE journey_fact_id NOT IN ( SELECT fact_id FROM timetable_journey_live )";
        $this->connector->executeSQL($sql);
    }

    /**
    ** Foreach display sign, prediction receiver, find all vehicle arrivals/departures and
    ** and send relvant countdowns to them
    */
    function deliverPredictions()
    {
	    $startTime = new DateTime();
        //$this->connector->dumpSQL("SELECT * FROM t_stop_param");
        //die;
        //$this->connector->dumpSQL("SELECT * FROM t_prediction_param");
        //die;
	    $sql = 
	    "SELECT t_location_mode.location_id, 
				t_location_mode.location_code, 
				t_location_mode.bay_no, 
				route.route_code, 
                vehicle.vehicle_code,
				prediction_display.*, 
				t_prediction_param.*, 
                unit_build.build_id vehicle_build_id, 
                unit_build.build_code vehicle_build_code, 
                service_patt.dest_id, 
                stop_build.build_code, 
                stop_build.build_id,
                start_code,
                arrival_status, 
                departure_status,
                publish_tt.trip_no,
                publish_tt.etm_trip_no,
                trip_status,
                t_display_point.display_type,
                arrival_status,
                departure_status,
                service.description service_name,
                vehicle.vehicle_id,
                vehicle.vehicle_code

			FROM prediction_display
            LEFT JOIN t_location_mode ON prediction_display.location_id = t_location_mode.location_id
            JOIN timetable_journey_live ON prediction_display.journey_fact_id = timetable_journey_live.fact_id
            JOIN timetable_visit_live ON prediction_display.journey_fact_id = timetable_visit_live.journey_fact_id AND prediction_display.sequence = timetable_visit_live.sequence
            JOIN route ON prediction_display.route_id = route.route_id
            JOIN vehicle ON prediction_display.vehicle_id = vehicle.vehicle_id
			LEFT JOIN route_param ON prediction_display.route_id = route_param.route_id
			JOIN t_prediction_param ON prediction_display.route_id = t_prediction_param.route_id AND prediction_display.location_id = t_prediction_param.location_id 
            JOIN unit_build stop_build ON stop_build.build_id = t_prediction_param.build_id
            --JOIN t_stop_param ON t_stop_param.build_id = stop_build.build_id
            JOIN t_display_point ON t_prediction_param.build_id = t_display_point.build_id AND t_display_point.location_id = t_prediction_param.location_id
            JOIN publish_tt ON prediction_display.pub_ttb_id = publish_tt.pub_ttb_id
            JOIN service ON publish_tt.service_id = service.service_id
            JOIN service_patt ON service_patt.service_id = service.service_id AND service_patt.rpat_orderby = prediction_display.sequence
			LEFT JOIN unit_build ON vehicle.build_id = unit_build.build_id
			WHERE 1 = 1
            AND counted_down = 0
		--AND t_location_mode.location_code = '049004685144'
	    --AND  stop_build.build_code = '2002093053'
            AND timetable_visit_live.arrival_status != 'C'
            AND timetable_visit_live.departure_status != 'C'
            AND stop_build.unit_type != 'OMNISTOP'
			AND rtpi_status != 'N'
			ORDER BY t_location_mode.location_id, 
					 prediction_display.rtpi_etd_sent,
                     prediction_display.journey_fact_id,
                     prediction_display.sequence,
                     t_prediction_param.build_id";
        if ( !( $stmt = $this->connector->executeSQL($sql) ) )
        {
            echo "Error Fetching Prediction Countdowmns\n";
        }


	    $lastPredictionDisplay = false;
        $last_sent_sch = false;
        $last_sent_order = false;
        $lct = 0;

        $outboundQueue = false;

        $prediction_stop_info = new TempCountdown($this->connector);

        while ( $row = $stmt->fetch() )
        {
            foreach ( $row as $k => $v )
            {
                $row [$k] = trim($v);
            }

            // if ( $the location has changed && $there was a successful send to that location
            // flag it as having been dealt with and store the prediction actually sent
            if ( $lastPredictionDisplay )
            if ( $lastPredictionDisplay->journey_fact_id  && $last_sent_sch 
            && ($lastPredictionDisplay->journey_fact_id != $row["journey_fact_id"] ||
                $lastPredictionDisplay->sequence != $row["sequence"]) 
            && $last_sent_sch == $lastPredictionDisplay->journey_fact_id 
            && $last_sent_order == $lastPredictionDisplay->sequence
            ) {
                $lastPredictionDisplay->time_last_sent = UtilityDateTime::currentTime();
                $lastPredictionDisplay->save();
            }

            // Create Location, Vehicle, Vehicle Build, Stop Build and Prediction Parameter object ..
            $location = new Location($this->connector, $row);
            $vehicle = new Vehicle($this->connector, $row);

            $stopBuild = new UnitBuild($this->connector);
            $stopBuild->build_id = $row["build_id"];
            $stopBuild->build_code = $row["build_code"];

            $vehicleBuild = new UnitBuild($this->connector);
            $vehicleBuild->build_id = $row["vehicle_build_id"];
            $vehicleBuild->build_code = $row["vehicle_build_code"];

            // Create an object for the prediction and link in the vehicle, location, builds  and params
            $predictionDisplay = new PredictionDisplay($this->connector, $row);
            $predictionDisplay->vehicleBuild = $vehicleBuild;
            $predictionDisplay->stopBuild = $stopBuild;
            $predictionDisplay->location = $location;
            $predictionDisplay->vehicle = $vehicle;
            $predictionDisplay->display_type = $row["display_type"];
            $predictionDisplay->arrival_status = $row["arrival_status"];
            $predictionDisplay->departure_status = $row["departure_status"];
            $predictionDisplay->bay_no = $row["bay_no"];
            $predictionDisplay->dest_id = $row["dest_id"];
            $predictionDisplay->vehicle_id = $row["vehicle_id"];
            $predictionDisplay->vehicle_code = $row["vehicle_code"];
            $predictionDisplay->service_code = $row["service_name"];

            $predictionParameters = new PredictionParameter($this->connector, $row);
            $predictionDisplay->predictionParameters = $predictionParameters;
            $predictionDisplay->prediction_stop_info = $prediction_stop_info;


            $w_display_line = true;

            $lct = $lct + 1;
            if ( !$lastPredictionDisplay || $lastPredictionDisplay->location_id != $location->location_id ) {
                $display_ct = 0;
                $display_hdr = UtilityDateTime::currentTime(). "\n ***** update_displays for LOCATION ". $location->location_code;
                $prediction_stop_info->dropTable("CONTINUE");
                $prediction_stop_info->createTable();
            }

            $display_debug = true;
            $txt = UtilityDateTime::currentTime(). " ". $lct. " ". "L:". $location->location_code. " B:". $stopBuild->build_code;
            $txt = $txt.
                    " V:". $vehicle->vehicle_code.
                    " B:". $row["bay_no"].
                    " F:". $row["journey_fact_id"].
                    " C:". $row["pub_ttb_id"].
                    " O:". $row["sequence"].
                    " R:". $row["route_code"]. 
                    " E:". $row["etm_trip_no"];
                    " T:". $row["trip_no"];

            // ---------------------------------------------------
            // In order to not allow trip end points to echo times
            // for arrival && $departure of two successive trips
            // Ensure no arrival times are sent to last point
            // in trip if ( $dcd_param indicates Departure Type
            // This should probably be extended to not send departures
            // where dcd_param is set to arrival
            // ---------------------------------------------------
            $sql = 
                "SELECT MAX(sequence)
                FROM timetable_visit_live
                WHERE journey_fact_id = ".$predictionDisplay->journey_fact_id;
            $l_last_stop = $this->connector->fetch1ValueSQL($sql);
            if ( $l_last_stop && $l_last_stop == $predictionDisplay->sequence ) {
                $txt = $txt. "	Skipping last stop or failed to get last stop";
                //echo $txt."\n";
                $lastPredictionDisplay = $predictionDisplay;
                continue;
            }


            if ( $l_last_stop == $predictionDisplay->sequence ) {
                echo "DEB ". $row["start_code"]. " / ". $l_last_stop. $predictionDisplay->sequence. " ". $txt;
            }

            // ------------------------------------------------------------
            // Set cleardown mode to departure for the first stop on a trip
            // ------------------------------------------------------------
            if ( $predictionDisplay->sequence == 1 && ( !$predictionParameters->countdown_dep_arr || $predictionParameters->countdown_dep_arr != "D" ) ) {
                $txt = $txt.  "!LOC1->D";
                $predictionParameters->countdown_dep_arr = "D";
            }

            if ( $row["trip_status"] == "A" && ( $this->resetSystem == "ACTIVE" )  ) {
                if ( !$row["counted_down"] ) {
                    if ( $predictionParameters->countdown_dep_arr == "A" ) {
                        $txt = $txt. " ". UtilityDateTime::dateExtract($row["rtpi_eta_sent"], "hour to second"). "/";
                    } else {
                        $txt = $txt. " ". UtilityDateTime::dateExtract($row["rtpi_etd_sent"], "hour to second"). "/";
                    }

                    $m_status = $predictionDisplay->send_countdown_if_appropriate($location->location_code, $row["start_code"]);
      	    //$predictionDisplay->setOutboundQueue();

                    // test whether countdown should be sent and if it should.. send it!
                    if ( !$m_status ) {
                        $txt = $txt." ".$predictionDisplay->text;
                        $txt = $txt. " IGNORED";
                        $w_display_line = false ;
                    } else {
                        $txt = $txt." ".$predictionDisplay->text;
                        $txt = $txt. " YES";
                        $last_sent_sch = $row["journey_fact_id"];
                        $last_sent_order = $row["sequence"];

                        // This record will contain the details of the prediction last sent 
                        // So we can update prediction_display with the last sent time, type etc
                        // when the prediction_display record changes in the main foreach
                        $row_sent = $row;
                    }
                }
            } else {
                echo "CANCELLED". $rows["location_code"];
                if ( $row["vehicle_code"] == "AUT" ) {
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], $row["pub_ttb_id"]);
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], $row["pub_ttb_id"]);
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], $stopBuild, $row["pub_ttb_id"]);
                } else {
                    echo "clearing down!!!!!!!!";
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], 0);
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], 0);
                    $l_status = $predictionDisplay->clear_countdown_if_appropriate($rows["location_code"], 0);
                }
                
                // Trip is not active - clear the displays.
                if ( !$l_status ) {
                    $txt = $txt. " NOT ACTIVE -> STALE";
                    if ( $this->resetSystem == "YES" ) {
                           echo "RESET REMOVE";
                        //$this->connector->executeSQL("DELETE FROM dcd_omnistop WHERE journey_fact_id = ".$predictionDisplay->journey_fact_id." AND sequence  = ".$predictionDisplay->sequence);
                        $this->connector->executeSQL("DELETE FROM prediction_display WHERE journey_fact_id = ".$predictionDisplay->journey_fact_id." AND sequence  = ".$predictionDisplay->sequence);
                    }
                } else {
                    //$this->connector->executeSQL("DELETE FROM dcd_omnistop WHERE journey_fact_id = ".$predictionDisplay->journey_fact_id." AND sequence  = ".$predictionDisplay->sequence);
                    $this->connector->executeSQL("DELETE FROM prediction_display WHERE journey_fact_id = ".$predictionDisplay->journey_fact_id." AND sequence  = ".$predictionDisplay->sequence);
                }
            }

            if ( ( $display_debug || $debug ) && $w_display_line ) {
                if ( $display_ct == 0 ) {
                    echo $display_hdr."\n";
                }
                $display_ct = $display_ct + 1;
                echo $txt."\n";
            }

            $lastPredictionDisplay = $predictionDisplay;
	    }

        // If the location has changed and there was a successful send to that location
        // flag it as having been dealt with
        if ( $lastPredictionDisplay )
        if ( $lastPredictionDisplay->journey_fact_id && $last_sent_sch && ($lastPredictionDisplay->journey_fact_id != $row["journey_fact_id"] )
            && $last_sent_sch == $lastPredictionDisplay->journey_fact_id
            && $last_sent_order == $lastPredictionDisplay->sequence)
        {
            //$lastPredictionDisplay->dump();
            $lastPredictionDisplay->save();
        }

	    $endTime = new DateTime();
	    $generatedTime = $endTime->getTimestamp() - $startTime->getTimestamp();
	    echo "Time to Generate Stop Times: ". $generatedTime."\n\n";
    
	    return true;
    }
}
?>
