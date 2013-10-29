<?php

/**
** Class: TaskJourneyStarter
** ------------------------------
**
** Passes through the daily schedule of trips and starts them in the live journey tables
** when current time reaches the point where a trip should be "made live"
** Trips started through this mechanism will be scheduled only "auto" journeys
** and will be be replaced by live vehicle journeys as trips are started by vehicles
**
** Trips are created in live in advance of their schedule start time as defined by the 
** service preemption flag in the prediction_parameter table
*/

class TaskJourneyStarter extends ScheduledTask
{

    private $routeTrackerQueue = false;
    private $eventHandlerQueue = false;

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        $this->getRouteTrackerQueue();
        $this->getEventHandlerQueue();
        while ( true )
        {
            $this->startjourneys();
            sleep(60);
        }
    }

    /*
    ** getEventHandlerQueue
    **
    ** Fetches queue id of Nimbus Event Handler server so we can
    ** start trips by sending journeys to this queue
    */
    function getEventHandlerQueue()
    {
        $this->eventHandlerQueue = SystemKey::getInboundQueue($this->connector);
        if ( !$this->eventHandlerQueue )
        {  
            echo "Inbound Queue not defined for message delivery - finishing\n";
            die;
        }
    }

    /*
    ** getRouteTrackerQueue
    **
    ** Fetches queue id of Centurion RTPI server so we can
    ** start trips by sending journeys to this queue
    */
    function getRouteTrackerQueue()
    {
        $syskey = new SystemKey();
        $syskey->connector = $this->connector;
        $syskey->key_code = "ARDBQ";
        if ( $syskey->load() )
            $this->routeTrackerQueue = msg_get_queue($syskey->key_value);
    }

    /*
    ** startJourneys
    **
    ** Main method for starting trips as "live journeys"
    */
    function startJourneys()
    {
        $now = new DateTime();
	    $now_hhmmss = $now->format("H:i:s");
	    $now_time = $now->format("Y-m-d H:i:s");

        $sql = 
		    "SELECT a.*, c.alloc_vehicle
			FROM auto_journey_schedule a, timetable_journey tjd, outer ( tt_mod c, tt_mod_trip d )
			WHERE start_status = 0
              AND a.timetable_id = tjd.timetable_id
              AND tjd.ext_timetable_id = d.pub_ttb_id
              AND c.mod_id = d.mod_id
              AND auto_start_time < '$now_time'
			ORDER BY a.scheduled_start_time";


        $liveEquivSql = 
			"SELECT schedule_id, start_code, b.vehiccompare_intle_id, vehicle_code
				INTO select_count
				FROM timetable_journey_live, vehicle b
				WHERE timetable_journey_live.timetable_id = wr_auto_journey_schedule.timetable_id
                  AND timetable_journey_live.vehicle_id = b.vehicle_id
				  AND curr_time - actual_start < compare_int";


        $stmt = $this->connector->executeSQL($sql);
        if ( !$stmt )
        {
            echo "Error in trip starting\n";
            return false;
        }
        
        $this->rows_affected = 0;

        while ( $row = $stmt->fetch() )
        {
			// We will not start an auto route if an existing route is being
			// run in Active Route table which matches route and trip and which
			// started +/- 2 hours of the autoroute start time
			$compare_int = "0 02:00:00";
            
            // Handle replace despatcher generated vehicle to board allocation
            /*
            if ( $alloc_vehicle ) {
                $select_count = 0;

                FOREACH c_trips_already INTO l_schedule_id, l_start_code, l_vehicle_id, l_vehicle_code
                    LET select_count = select_count + 1
                    
                    -- If trip already exists and the trip is alread being allocated to a vehicle
                    -- then remove the existing trip for later replacement
                    if ( $l_start_code == "AUT" }
                        echo "Replacing ". wr_auto_journey_schedule.timetable_id," AUTO trip with allocated vehicle for ", wr_auto_journey_schedule.scheduled_start, " ", wr_auto_journey_schedule.running_no
                        $l_status =  remove_timetable_journey_live_by_schedule(l_schedule_id)
                    ELSE
                        echo "Replacing ". l_vehicle_code," trip with allocated vehicle for ", wr_auto_journey_schedule.scheduled_start, " ", wr_auto_journey_schedule.running_no, " ", l_vehicle_id
                        $l_status =  remove_timetable_journey_live_by_vehicle(l_vehicle_id)
                    }
                }
            }
            */

            // Is trip already in live journey set? - if so dont start it
            $sql = "SELECT count(*) count
				    FROM timetable_journey_live
				    WHERE timetable_journey_live.timetable_id = ".$row["timetable_id"]." 
				    AND '$now_time' - actual_start < '$compare_int'";

            if ( !$ret = $this->connector->fetch1SQL($sql) )
            {
                echo "Failed to get live count for journey\n";
                return false;
            }
            $select_count = $ret["count"];

			if ( $select_count == 0 )
            {
                // Get Timetabler Journey realting to the automatic journey schedule entry
                $tjd = new TimetableJourney($this->connector);
                $tjd->timetable_id = $row["timetable_id"];
                if ( !$tjd->load() )
                {
                    echo "Failed to fetch timetable details for auto journey\n";
                    return false;
                }

                /*
				$log_msg = $tjd->route_code .
							  " ". $tjd->running_no .
							  " ". $tjd->trip_no .
							  " ". $tjd->start_time .
							  " ". $row["auto_start_time"] .
							  " ". $now_hhmmss ;
				echo $log_msg."\n";
                */

                //$this->rows_affected++;
                //if ( $this->routeTrackerQueue )
                    //$this->startJourneyWithRouteTracker($row, $tjd);

				// Let database know that this trip has been started
                $sql = " UPDATE auto_journey_schedule
					SET start_status = 1
					WHERE timetable_id = ".$row["timetable_id"];
                $this->connector->executeSQL($sql);

                // Build an Event and send it to the EventHandler's message queue.
                $now = new DateTime();
                $event = new EventJourneyDetails(new DateTime(), $now->getTimestamp(), "AUT", "Vehicle", "AUT");
                $event->ip_address = "127.0.0.1";
                $event->conn_status = "A";
                $event->service_code = $tjd->route_code;
                $event->public_service_code = $tjd->route_code;
                $event->running_board = $tjd->running_no;
                $event->duty_number = $tjd->duty_no;
                $event->journey_number = $tjd->trip_no;
                $event->scheduled_start = $tjd->start_time;
                $event->direction = $tjd->direction;
                $event->timetable_id = $tjd->timetable_id;
                $event->depot_code = "";
                $event->driver_code = "AUT";
                $event->first_stop_id = false;
                $event->destination_stop_id = false;

                echo "Start Service ".$event->service_code." B:".$event->running_board." D:".
                    $event->duty_number." J:".
                    $event->journey_number." ".
                    $event->scheduled_start." > ".
                    $row["auto_start_time"]."    Id:".
                    $tjd->timetable_id.
                    "\n";

                if (!msg_send ($this->eventHandlerQueue, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to event_handler message queue");
            }
			else
            {
			    // A real bus has started this route - flag it as started already
                $sql = " UPDATE auto_journey_schedule
					SET start_status = 1
					WHERE timetable_id = ".$row["timetable_id"];
                $this->connector->executeSQL($sql);
            }
				
	    }
        return true;
	}

    /**
    ** startJourneyWithRouteTracker
    **
    ** Packs up auto journey to start as a "start journey request" and sends it to 
    ** the Route Tracker Queue
    */
    function startJourneyWithRouteTracker($row, $tjd)
    {
        // Create trip starter message for Centurion Route Tracker task
        $date_string = $row["scheduled_start_time"];
        $addressSpec = "ALL";
        $messageType = 208;
        $messageId = 2;
        $structureType = 1;
        $action = 201;
        $routeStarted = 1;
        $routeSynchronizing = 0;
        $driverCode = "AUT";
        $vehicleCode = "AUT";
        //$vehicleCode = find_preallocated_vehicle(wr_auto_journey_schedule.operation_date, 
                                                    //wr_auto_journey_schedule.timetable_id,  wr_auto_journey_schedule.running_no)
        
        // If generating trip for pre-allocated vehicle then set driver to "DESP"
        // to flag it has been generated by despatcher allocation
        if ( $vehicleCode != "AUT" ) 
            $driverCode = "DESP";

        $tripNumber = $tjd->trip_no;
        if ( $tjd->etm_trip_no )
            $tripNumber = $tjd->etm_trip_no;
        $dutyNumber = $tjd->duty_no;
        $stageNumber = 1;
        $runningNumber = $tjd->running_no;
        $routeCode = $tjd->route_code;
        $orgUnit = "UNK";
        $direction = $tjd->direction;
        $startTime = "";
        $locationCode = 1;
        $previousLocationCode = 2;

        $startTime = DateTime::createFromFormat("Y-m-d H:i:s", $date_string);
        $timeRouteStarted = $startTime->getTimestamp();
        $timeAtLastStop = $startTime->getTimestamp();
        $timeBetweenStops = 0;
        $journeyId = $tjd->ext_timetable_id;
        $timetableLateness = 0;
        $currentLateness = 0;
        $statusTime = $timeAtLastStop;
        $diversionCode = "";

        $locationNo = 1;
        $previousLocationNo = 0;
        $gpslat = 0.0;
        $gpslong = 0.0;
        $crcLow = 0;
        $crcHigh = 0;

        //CALL set_global_route(wr_msg.*)
        //LET m_status = set_c_route_status()

        // Send message to database
        //$l_destination = "DEFAULT";
        //$m_status = status_to_queue(databaseq_id, l_destination, LENGTH(l_destination))

        $routeTrackerStart = pack("A3SSA10CxSCCA8A8A8A12A6A8A4A8A8CxiA12A12ixxxxixxxxiiixxxxSSddCC",
                "PHP ",
                $messageType,
                $messageId,
                $addressSpec,
                $structureType,
                $action,
                $routeStarted,
                $routeSynchronizing,
                $routeCode,
                $diversionCode,
                $driverCode,
                $vehicleCode,
                $tripNumber,
                $dutyNumber,
                $stageNumber,
                $runningNumber,
                $orgUnit,
                $direction,
                $journeyId,
                $locationCode,
                $previousLocationCode,
                $timeRouteStarted,
                $timeAtLastStop,
                $timetableLateness,
                $currentLateness,
                $statusTime,
                $locationNo,
                $previousLocationNo,
                $gpslat,
                $gpslong,
                $crcLow,
                $crcHigh
                );

        if ( $this->routeTrackerQueue )
        {
            if (!msg_send ($this->routeTrackerQueue, 1, $routeTrackerStart ) )
                $log->error("Failed to send event to route tracker message queue");
        }


    }

}
?>
