<?php

class iconnexpacket_240 extends iconnexpacket
{
    public $timetableVisits = false;

    public $debug = false;
    public $ignoreRoute = false;
    public $ignoreTrip = false;
    public $currentLocation = -1;
    public $driver_id = false;
    public $sourcefile = false;
    public $processed_found_trips = 0;
    public $processed_not_found_trips = 0;
    public $processed_pax_ct_in = 0;
    public $processed_pax_ct_out = 0;
    public $lastDriverNumber = false;
    public $lastDutyStart = false;

    function __construct($odsconnector, $rtpiconnector, $inData, $inLength)
    {
        parent::__construct($odsconnector, $rtpiconnector, $inData, $inLength);
    }

    function show()
    {
        var_dump($this->content);
    }

    function post_process()
    {
        // New trip started, process arrivals/departures for any prior one
        if ( $this->timetableVisits )
        {
            $this->commitActualJourney();
            $this->timetableVisits = false;
        }

        // At end of file store last driver run
        if ( $this->lastLogMessageTime )
        {
            $this->applyDriverEntryToDriverRunFact ($this->lastLogMessageTime, true);
        }

        echo " Trips Processed = ".$this->processed_found_trips . "/";
        echo " ".$this->processed_not_found_trips + $this->processed_found_trips;
        echo " Pax IN = ".$this->processed_pax_ct_in;
        echo " Pax OUT = ".$this->processed_pax_ct_out;
    }

    function process()
    {
        $action = $this->content["action"];
        $tripNumber = $this->content["tripNumber"];
        $route = sprintf("%c", $this->content["routeCode1"]);
        if ( $this->content["routeCode2"] ) $route = $route.sprintf("%c", $this->content["routeCode2"]);
        if ( $this->content["routeCode3"] ) $route = $route.sprintf("%c", $this->content["routeCode3"]);
        if ( $this->content["routeCode4"] ) $route = $route.sprintf("%c", $this->content["routeCode4"]);
        //var_dump($this->content);
        $l_action = $this->lastcontent["action"];
        $l_tripNumber = $this->lastcontent["tripNumber"];

        // Route Code seemd to be terminated with a weird character - strip it out
        //$fp = fopen("/tmp/fred", "w+" );
        //fputs($fp, $route);
        //fclose($fp);

        // Extract GPS
        $declat = $this->content["gpslat"];
        $declong = -$this->content["gpslong"];
        $eventid = $this->content["action"];
        $driverCode = $this->content["driverNumber"];

        // Extract Message Time
        $msgTimestamp = $this->content["timeRouteStarted"] + $this->content["sendTimeAddOn"];
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixtimestamp = $timestamp->format("Y-m-d H:i:s");
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");

        // Set other stuff
        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
        $mode =  "A";

        $route = preg_replace("/\s/", "", $route);

        // Find GIS Identifier for record
        $gisarr = array();
        $hash = false;
        $gisid = $this->odsconnector->processGeoItem($declat, $declong, $hash, $gisarr);

        $gisarr["true_course"] = -1;
        $gisarr["fixtime"] = $fixtime;
        $gisarr["fixdate"] = $fixdate;
        $gisarr["event_id"] = $eventid;
        $gisarr["bearing"] = $bearing;

        // New trip started
        if ( $action == 201 || 
            ($action == 206 && $tripNumber != $this->lastcontent["tripNumber"]) ||
            ($action == 212 && $tripNumber != $this->lastcontent["tripNumber"]) ||
            ($action == 232 && $tripNumber != $this->lastcontent["tripNumber"]) 
            )
        {
            // Clear last status content for a new trip
            $this->lastcontent = $this->content;

            // New trip started, process arrivals/departures for any prior one
            if ( $this->timetableVisits )
            {
                $this->commitActualJourney();
                $this->timetableVisits = false;
            }

            if ( $this->ignoreRoute == $route && $this->ignoreTrip == $tripNumber )
                return false;

            $this->ignoreRoute = false;
            $this->ignoreTrip = false;

            $seq = $this->content["locationCode"];

            // Fetch driver id 
            $this->driver_id = $this->odsconnector->getDriver ( $driverCode, $this->operator_code );

            // Fetch trip timetable data so we can tie it up with actuals
            $this->timetableVisits = $this->fetchTimetableJourneyForRouteTrip ( $this->operator_code, $fixdate, $fixtimestamp, $route, $tripNumber);
            if ( !$this->timetableVisits )
            {
                if ( $this->debug )
                    echo "Not found Op:$this->operator_code, Rt:$route, Tr:$tripNumber Sq:$seq Tm:$fixtimestamp\n";
                $this->ignoreRoute = $route;
                $this->ignoreTrip = $tripNumber;
                $this->processed_not_found_trips++;
                return false;
            }
            if ( $this->debug )
                echo "Found     Op:$this->operator_code, Rt:$route, Tr:$tripNumber Sq:$seq Tm:$fixtimestamp, ".$this->timetableVisits[0]["departure_time"]." ";
            $this->processed_found_trips++;

            // Set the journey first message time in the visits array .. this will be used to set journey actual start
            $this->timetableVisits[0]["timestamp"] = $timestamp;
        }

        if ( !$this->timetableVisits )
        {
            //echo "No current timetable id\n";
            return;
        }

        if ( $action == 201 || $action == 206 || $action == 212 || $action == 232 )
        {
            $locno = $this->content["locationCode"] - 1;
            $this->currentLocation = $locno;
            if ( !isset($this->timetableVisits[$locno] ) )
            {
                echo count( $this->timetableVisits); die;
                foreach ( $this->timetableVisits as $k => $v )
                    echo $k." ";
                echo "Cant match message with location order $locno\n";
                return;
            }
            $ttbvisit = $this->timetableVisits[$locno];

            if ( $action == 206 || $action == 232 )
            {
                $this->timetableVisits[$locno]["actual_arrival_time"] = $fixtimestamp;
                $this->timetableVisits[$locno]["actual_departure_time"] = false;
            }
            if ( $action == 212 )
            {
                $this->timetableVisits[$locno]["actual_departure_time"] = $fixtimestamp;
            }
            $this->lastcontent = $this->content;
        }

        if ( $action == 244 // driver entry
            || $action == 209 // driver update
            || $action == 238 // etm valid
            || $action == 235 // etm unknown trip info
            || $action == 236 // etm unknown trip for time of day
            || $action == 237 // etm route corruption
            )
        {
            //echo "\n ".$action." ";
            //echo $this->content["driverNumber"]."";
            ///var_dump ($this->content);
            $this->applyDriverEntryToDriverRunFact ($timestamp);
            
        }

        $this->lastLogMessageTime = $timestamp;


    }

    /*
    ** Finds the timetable journey for a given time, route and trip
    */
    function applyDriverEntryToDriverRunFact ($timestamp, $forceSave = false)
    {   
        if ( $this->lastDriverNumber && ( $forceSave || $this->lastDriverNumber != $this->content["driverNumber"] ))
        {
            //echo "driver Change $this->lastDriverNumber /".$this->content["driverNumber"]." ";
            $driver_id = $this->odsconnector->getDriver ( $this->lastDriverNumber, $this->operator_code );
            if ( $driver_id )
            {
                $startfixtimestamp = $this->lastDutyStart->format("Y-m-d H:i:s");
                $startfixdate = $this->lastDutyStart->format("Ymd");
                $startfixdatedmY = $this->lastDutyStart->format("dmY");
                $startfixtime = $this->lastDutyStart->format("His");
                $endstamp = clone $timestamp;
                $endstamp = $endstamp->Sub(new DateInterval("PT1S"));
                $endfixtimestamp = $endstamp->format("Y-m-d H:i:s");
                $endfixdate = $endstamp->format("Ymd");
                $endfixdatedmY = $endstamp->format("dmY");
                $endfixtime = $endstamp->format("His");
                $duration = $this->lastDutyStart->diff($endstamp);
                $duration_db = $duration->format("'%r%H:%I:%S'");
                $sql = "INSERT INTO `timetable_duty_run_fact` 
                    (             
                        sourcefile,
                        `fact_id`,
                        `operator_id`,
                        `vehicle_id`,
                        `driver_id`,
                        `start_date_id`,
                        `start_time_id`,
                        `end_date_id`,
                        `end_time_id`,
                        `actual_start`,
                        `actual_end`,
                        `duration` ) VALUES (
                    '$this->sourcefile', 0, $this->operator_id, $this->vehicle_id, $driver_id, $startfixdate,
                    $startfixtime, $endfixdate, $endfixtime, 
                    '$startfixtimestamp', '$endfixtimestamp', $duration_db);";
                $this->odsconnector->executeSQL($sql);
            }
            else
            {
                echo "Unknown Driver Code ".$this->lastDriverNumber."/".$this->operator_code."! ";
            }

        }
        if ( $this->lastDriverNumber != $this->content["driverNumber"] )
        {
            $this->lastDutyStart = $timestamp;
            $this->lastDriverNumber = $this->content["driverNumber"];
        }
    }

    /*
    ** Finds the timetable journey for a given time, route and trip
    */
    function fetchTimetableJourneyForRouteTrip ( $operator, $fixdate, $fixtimestamp, $route, $tripNumber)
    {

        $sql = "SELECT timetable_journey.timetable_id, 
                timetable_journey.route_id,
                timetable_journey.duration,
                timetable_visit.timetable_visit_id,
                timetable_visit.arrival_time,
                timetable_visit.departure_time,
                timetable_visit.timing_point,
                timetable_visit.sequence,
                timetable_visit.location_id,
                timetable_visit_fact.arrival_time actual_arrival_time,
                timetable_visit_fact.departure_time actual_departure_time,
                timetable_visit_fact.journey_fact_id existing_journey_fact,
                timetable_visit_fact.fact_id existing_visit_fact,
                gis_dimension.latitude,
                gis_dimension.longitude
                FROM timetable_journey 
                JOIN timetable_visit ON timetable_journey.timetable_id = timetable_visit.timetable_id
                JOIN location_dimension ON location_dimension.location_id = timetable_visit.location_id
                JOIN gis_dimension ON location_dimension.gis_id = gis_dimension.gis_id
                LEFT JOIN timetable_journey_fact ON timetable_journey.timetable_id = timetable_journey_fact.timetable_id
                    AND timetable_journey_fact.vehicle_id = $this->vehicle_id
                LEFT JOIN timetable_visit_fact ON timetable_journey_fact.fact_id = timetable_visit_fact.journey_fact_id
                AND timetable_visit_fact.sequence = timetable_visit.sequence
                WHERE 1 = 1
                AND route_code = '$route'
                AND trip_no = '$tripNumber'
                AND '$fixtimestamp' between SUBTIME(timetable_journey.actual_start, '00:30:00')  
                    and ADDTIME(timetable_journey.actual_end, '00:30:00')
                ORDER BY timetable_visit.sequence";
        $ret = $this->odsconnector->fetchAll($sql);
        /*if ( $ret || $route == "N26")
        {
            if ( count($ret) == 1 )
                echo $sql;
            foreach ( $ret as $k => $v )
            {
                echo $v["timetable_id"]." ".
                    $v["sequence"]." ".
                    $v["timing_point"]." ".
                    $v["arrival_time"]." ".
                    $v["departure_time"]." ";
                if ( $v["actual_arrival_time"] )
                {
                   echo $v["actual_arrival_time"];
                }
                echo "\n";
            }
        }*/
        return $ret;
    }

    /*
    ** Finds existing trip record for vehicle / trip / day
    */
    function fetchActualVehicleJourneyByTtbId ( $vehicle_id, $timetableid )
    {

        $sql = "SELECT *
                FROM timetable_journey_fact
                WHERE timetable_id = $timetableid
                AND vehicle_id = $vehicle_id
                ";
        $ret = $this->odsconnector->fetch1SQL($sql);
        return $ret;
    }

    /*
    ** REmove previous data loaded for file
    */
    function clearExistingData($filename)
    {
        $sql = "DELETE FROM timetable_duty_run_fact
            WHERE vehicle_id = $this->vehicle_id
            AND sourcefile = '$filename'";
        $ret = $this->odsconnector->executeSQL($sql);
        if ( !$ret )
        {
            echo "Error: Failed to clear duty runs\n";
            die;
        }

    }

    /*
    ** Commits array of vehicle arrivals/departures on a trip to the visit fact table
    */
    function commitActualJourney()
    {
         $timestamp = $this->timetableVisits[0]["timestamp"];
         $fixtimestamp = $timestamp->format("Y-m-d H:i:s");
         $fixdate = $timestamp->format("Ymd");
         $fixdatedmY = $timestamp->format("dmY");
         $fixtime = $timestamp->format("His");

         // Clear out an existing fact record for the journey
         $locno = $this->content["locationCode"];
         $ttbid = $this->timetableVisits[0]["timetable_id"];
         $routeid = $this->timetableVisits[0]["route_id"];

         /*
         if (  $actualjourney = $this->fetchActualVehicleJourneyByTtbId ( $this->vehicle_id, $ttbid ))
         {
             $sql = "DELETE FROM timetable_visit_fact 
                 WHERE journey_fact_id IN ( SELECT fact_id FROM timetable_journey_fact
                 WHERE vehicle_id = ".$this->vehicle_id."
                 AND timetable_id = $ttbid ) ";
             if ( !$ret = $this->odsconnector->executeSQL($sql) )
             {
                echo "Error in actual visit removal $this->vehicle_code, $fixtimestamp\n";
                return -1;
             }
             $sql = "DELETE FROM timetable_journey_fact 
                        WHERE vehicle_id = ".$this->vehicle_id."
                        AND timetable_id = $ttbid ";
             if ( !$ret = $this->odsconnector->executeSQL($sql) )
             {
                echo "Error in actual journey removal $this->vehicle_code, $fixtimestamp\n";
                return -1;
             }
        }
        */


        $sched_duration_db = "'".$this->timetableVisits[0]["duration"]."'";

        $number_stops_sched = count($this->timetableVisits);

        $driver_id_db = $this->driver_id ? $this->driver_id : "NULL";
         // First create journey fact record

        $existing_journey = $this->timetableVisits[0]["existing_journey_fact"];
        // Create journey if existing one doesnt exist
        if ( !$existing_journey )
        {
            $sql = "INSERT INTO `timetable_journey_fact` 
                    ( `fact_id`, `timetable_id`, `operator_id`, route_id, `vehicle_id`, `driver_id`, `start_date_id`,
                    `start_time_id`, `end_date_id`, `end_time_id`, 
                    `actual_start`, `actual_end`, `duration`, sched_duration, duration_variation, `start_stop`,
                    `end_stop`, `number_stops`, number_stops_sched ) VALUES (
                    0, $ttbid, $this->operator_id, $routeid, $this->vehicle_id, $driver_id_db, $fixdate,
                    $fixtime, $fixdate, $fixtime, 
                    '$fixtimestamp', '$fixtimestamp', NULL, $sched_duration_db, NULL, $locno, 
                    $locno, 0, $number_stops_sched);";
            if ( !$ret = $this->odsconnector->executeSQL($sql) )
            {
                echo "Error in actual journey creation $this->vehicle_code, $fixtimestamp\n";
                    return -1;
            }
            $journey_fact = $this->odsconnector->lastInsertId("timetable_journey_fact", "fact_id");
        }
        else
            $journey_fact = $existing_journey;


        $last_tp_id = false;
        $last_tp_departure = false;
        $last_loc_id = false;
        $last_departure = false;
        $firstlocno = 0;
        $lastlocno = 0;
        $start_departure = false;
        $end_arrival = false;
        $duration = false;
        $number_stops = 0;
        $min_lateness = "NA";
        $max_lateness = "NA";
        $sum_lateness = 0;
        $num_latenesses = 0;
       
        $locct = 0;
        foreach ( $this->timetableVisits as $k => $v )
        {
            $locct++;
            // If journey info already exists then we just update otherwise insert
            $existing_visit = $this->timetableVisits[$k]["existing_visit_fact"];


            $arrival_lateness = false;
            $departure_lateness = false;
            $arrival_time = false;
            $departure_time = false;
            $arrival_time_pub = false;
            $departure_time_pub = false;
            $travel_time = false;
            $travel_time_tp = false;
            $dwell_time = false;
            $timing_point = $v["timing_point"];

            if ( isset( $v["actual_arrival_time"] ) )
               $arrival_time = DateTime::createFromFormat('Y-m-d H:i:s', $v["actual_arrival_time"]);

            if ( isset ( $v["actual_departure_time"] ) )
               $departure_time = DateTime::createFromFormat('Y-m-d H:i:s', $v["actual_departure_time"]);

            if ( isset ( $v["arrival_time"] ) )
               $arrival_time_pub = DateTime::createFromFormat('Y-m-d H:i:s', $v["arrival_time"]);

            if ( isset ( $v["departure_time"] ) )
               $departure_time_pub = DateTime::createFromFormat('Y-m-d H:i:s', $v["departure_time"]);

            if ( !$start_departure && $departure_time )
            {
                $firstlocno = $v["sequence"];
                $start_departure = $departure_time;
            }

            if ( $arrival_time && $start_departure )
            {
                $lastlocno = $v["sequence"];
                $end_arrival = $arrival_time;
            }

            if ( $arrival_time || $departure_time )
                $number_stops++;

            if ( $arrival_time && $departure_time )
               $dwell_time = $arrival_time->diff($departure_time);

            if ( $arrival_time && $last_departure )
               $travel_time = $last_departure->diff($arrival_time);

            if ( $arrival_time && $arrival_time_pub )
                $arrival_lateness = $arrival_time_pub->diff($arrival_time);

            if ( $departure_time && $departure_time_pub )
                $departure_lateness = $departure_time_pub->diff($departure_time);

            // If early departure from start of trip occurs, assume this is an error
            // and blank the departure_time and lateness
            $departure_lateness_secs = false;
            if ( $departure_lateness )
            {
                $departure_lateness_secs = $departure_time->getTimestamp() - $departure_time_pub->getTimestamp();
            }
            $nolateness = false;
            if ( $k == 0 )
            {
                if ( $departure_lateness_secs < -60 )
                {
                    if ( $this->debug )
                        echo " E:".  $departure_lateness_secs ." ";
                    $departure_lateness = false;
                    $departure_time = false;
                    $start_departure = false;
                    $nolateness = true;
                }
            }
            if ( !$nolateness && $timing_point && $departure_time_pub && $departure_time )
            {
                $num_latenesses++;
                if ( $max_lateness != "NA" )
                {
                    if ( $departure_lateness > $max_lateness ) echo "G";
                    if ( $departure_lateness < $max_lateness ) echo "L";
                }

                $maxdiff = 0;
                $mindiff = 0;
                if ( $max_lateness != "NA" )
                {
                    $maxdiff = $this->interval_to_secs($max_lateness) - $this->interval_to_secs($departure_lateness);
                    $mindiff = $this->interval_to_secs($min_lateness) - $this->interval_to_secs($departure_lateness);
                }
                if ( $max_lateness  == "NA" || $maxdiff < 0 )
                    $max_lateness = $departure_lateness;
                if ( $min_lateness  == "NA" || $mindiff > 0 )
                    $min_lateness = $departure_lateness;

                $lsecs = $this->interval_to_secs($departure_lateness);
                $sum_lateness += $lsecs;
            }

            if ( $timing_point && $arrival_time && $last_tp_departure )
               $travel_time_tp = $last_tp_departure->diff($arrival_time);

            $last_tp_db = "NULL";
            if ( $timing_point && $last_tp_id)
                $last_tp_db = $last_tp_id;

            $arrival_time_db = ( $arrival_time ? $arrival_time->format("'Y-m-d H:i:s'") : "NULL" );
            $departure_time_db = ( $departure_time ? $departure_time->format("'Y-m-d H:i:s'") : "NULL" );
            $arrival_date_id_db = ( $arrival_time ? $arrival_time->format("Ymd") : "NULL" );
            $departure_date_id_db = ( $departure_time ? $departure_time->format("Ymd") : "NULL" );
            $arrival_time_id_db = ( $arrival_time ? $arrival_time->format("His") : "NULL" );
            $departure_time_id_db = ( $departure_time ? $departure_time->format("His") : "NULL" );
            $dwell_time_db = ( $dwell_time ? $dwell_time->format("'%r%H:%I:%S'") : "NULL" );
            $arrival_lateness_db = ( $arrival_lateness ? $arrival_lateness->format("'%r%H:%I:%S'") : "NULL" );
            $departure_lateness_db = ( $departure_lateness ? $departure_lateness->format("'%r%H:%I:%S'") : "NULL" );
            $travel_time_db = ( $travel_time ? $travel_time->format("'%r%H:%I:%S'") : "NULL" );
            $travel_time_tp_db = ( $travel_time_tp ? $travel_time_tp->format("'%r%H:%I:%S'") : "NULL" );
            $last_loc_db = ( $last_loc_id ? $last_loc_id : "NULL" );

            $locno = $v["sequence"];

            if ( $existing_journey && $existing_visit )
            {
                $sql = "UPDATE `timetable_visit_fact` SET 
                    `prev_id` = $last_loc_db, 
                    `prev_tp_id` = $last_tp_db, 
                    `timing_point` = ".$v["timing_point"].",
                    `arrival_date_id` = $arrival_date_id_db,
                    `departure_date_id` = $departure_date_id_db,
                    `arrival_time_id` = $arrival_time_id_db, 
                    `departure_time_id` = $departure_time_id_db,
                    arrival_time = $arrival_time_db,
                    departure_time = $departure_time_db, 
                    dwell_time = $dwell_time_db,
                    travel_time_loc = $travel_time_db,
                    travel_time_tp = $travel_time_tp_db, 
                    arrival_lateness = $arrival_lateness_db,
                    departure_lateness = $departure_lateness_db 
                    WHERE fact_id = $existing_visit";
                if ( !$ret = $this->odsconnector->executeSQL($sql) )
                {
                    echo "Error in actual journey creation $this->vehicle_code, $fixtimestamp\n";
                    return -1;
                }

            }
            else
            {
                $sql = "INSERT INTO `timetable_visit_fact` (
                    `fact_id`, `journey_fact_id`, `timetable_id`, `timetable_visit_id`,
                    `sequence`, `location_id`, `prev_id`, `prev_tp_id`, `timing_point`,
                    `arrival_date_id`, `departure_date_id`, `arrival_time_id`,
                    `departure_time_id`, arrival_time, departure_time,
                    dwell_time, travel_time_loc, travel_time_tp, arrival_lateness, departure_lateness
                    ) VALUES (
                    0, $journey_fact, $ttbid, ".$v["timetable_visit_id"].", 
                    ".$v["sequence"].", ".$v["location_id"].", $last_loc_db, $last_tp_db, ".$v["timing_point"].",
                    $arrival_date_id_db, $departure_date_id_db, $arrival_time_id_db, 
                    $departure_time_id_db, $arrival_time_db, $departure_time_db, 
                    $dwell_time_db, $travel_time_db, $travel_time_tp_db, 
                    $arrival_lateness_db, $departure_lateness_db )";
                if ( !$ret = $this->odsconnector->executeSQL($sql) )
                {
                    echo "Error in actual journey creation $this->vehicle_code, $fixtimestamp\n";
                    return -1;
                }

                $existing_visit = $this->odsconnector->lastInsertId("timetable_visit_fact", "fact_id");
            }


            // Process passenger counts associated with visit and ensure counts between stops
            // are allocated to the nearest stop
            if ( isset ( $v["passengers"] ))
            {
                foreach ( $v["passengers"] as $k1 => $v1 )
                {
                    /*
                    echo "$k $k1 ";
                    //echo "  ".$v["passengers"][$k1]["timestamp"]->format("Y-m-d H:i:s");
                    echo " In:".$v["passengers"][$k1]["in"];
                    echo " Out:".$v["passengers"][$k1]["out"];
                    echo " Agg In:".$v["passengers"][$k1]["agg_in"];
                    echo " Agg Out:".$v["passengers"][$k1]["agg_out"];
                    echo " Occ:".$v["passengers"][$k1]["occupancy"];
                    echo " Prein:".$v["passengers"][$k1]["pre_in"];
                    echo " Preout:".$v["passengers"][$k1]["pre_out"];
                    echo " bet:".$v["passengers"][$k1]["betweenstops"];
                    echo "";
                    */

                    $stopdist = metres_between_coords(
                                    $this->timetableVisits[$k]["latitude"], $this->timetableVisits[$k]["longitude"],
                                    $v["passengers"][$k1]["latitude"], $v["passengers"][$k1]["longitude"]);
                    //echo " To this : $stopdist ";

                    // If a count occurred when the bus thought it was between stops, allocated the
                    // count to the nearest stop
                    $addtothis = true;
                    if ( $v["passengers"][$k1]["betweenstops"] )
                    {
                        //$prevstopdist = 99999999;
                        //if ( isset ( $this->timetableVisits[$k - 1] ) )
                        //{
                            //$prevstopdist = metres_between_coords(
                                            //$this->timetableVisits[$k - 1]["latitude"], $this->timetableVisits[$k - 1]["longitude"],
                                            //$v["passengers"][$k1]["latitude"], $v["passengers"][$k1]["longitude"]);
                            //echo " Form Prev : $prevstopdist";
                        //}
                        if ( isset ( $this->timetableVisits[$k + 1] ) )
                        {
                            $nextstopdist = metres_between_coords(
                                            $this->timetableVisits[$k + 1]["latitude"], $this->timetableVisits[$k + 1]["longitude"],
                                            $v["passengers"][$k1]["latitude"], $v["passengers"][$k1]["longitude"]);
                            //echo " To   Next : $nextstopdist";
                            if ( $nextstopdist < $stopdist )
                            {
                                $addtothis = false;
                                if ( !isset($this->timetableVisits[$k + 1]["passengers"] ) )
                                    $this->timetableVisits[$k + 1]["passengers"] = array();
                                $this->timetableVisits[$k + 1]["passengers"][] = $v["passengers"][$k1];
                                $paxct = count($this->timetableVisits[$k + 1]["passengers"]) - 1;
                                $this->timetableVisits[$k + 1]["passengers"][$paxct] = $v["passengers"][$k1];
                                $this->timetableVisits[$k + 1]["passengers"][$paxct]["betweenstops"] = false;
                            }
                            else
                                $v["passengers"][$k1]["betweenstops"]  = false;
                        }
                    }
                }

                $totin = 0;
                $totout = 0;
                $occ = 0;
                $insrecordfortripsummary = -1;
                foreach ( $v["passengers"] as $k1 => $v1 )
                {
                    /*
                    echo "$k $k1 ";
                    //echo "  ".$v["passengers"][$k1]["timestamp"]->format("Y-m-d H:i:s");
                    echo " In:".$v["passengers"][$k1]["in"];
                    echo " Out:".$v["passengers"][$k1]["out"];
                    echo " Agg In:".$v["passengers"][$k1]["agg_in"];
                    echo " Agg Out:".$v["passengers"][$k1]["agg_out"];
                    echo " Occ:".$v["passengers"][$k1]["occupancy"];
                    echo " Prein:".$v["passengers"][$k1]["pre_in"];
                    echo " Preout:".$v["passengers"][$k1]["pre_out"];
                    echo " bet:".$v["passengers"][$k1]["betweenstops"];
                    echo "";
                    echo "\n";
                    */
                    $totin += $v["passengers"][$k1]["in"];   
                    $totout += $v["passengers"][$k1]["out"];   
                    if ( !$v["passengers"][$k1]["betweenstops"] )
                    {
                        $occ = $v["passengers"][$k1]["occupancy"];   
                        $insrecordfortripsummary = $k1;
                    }
                    $l_count_time = $v["passengers"][$k1]["timestamp"];
                    $in = $v["passengers"][$k1]["in"];
                    $out = $v["passengers"][$k1]["out"];
                    $occ = $v["passengers"][$k1]["occupancy"];
                    $gisid = $v["passengers"][$k1]["gisid"];
                    $lat = $v["passengers"][$k1]["latitude"];
                    $long = $v["passengers"][$k1]["longitude"];
                    $l_hour_int = $l_count_time->format("H");
                    $l_min_int = $l_count_time->format("i");
                    $l_timestamp = $l_count_time->format("Y-m-d H:i:s");
                    $l_date_id = $l_count_time->format("Ymd");
                    $l_time_id = $l_count_time->format("His");
                    $sql = " INSERT INTO people_count_fact (
                            fact_id, sourcefile, vehicle_id, driver_id,
                            journey_fact_id, visit_fact_id, gis_id, location_id, date_id,
                            time_id, timestamp, latitude, longitude, gpsAge,
                            speedKPH, bearing, in_count, out_count, total_in,
                            total_out, occupancy   ) VALUES (
                            0, '$this->sourcefile', $this->vehicle_id, $driver_id_db,
                            $journey_fact, $existing_visit, $gisid, ".$v["location_id"].", $l_date_id,
                            $l_time_id, '$l_timestamp', $lat, $long, 0, 0, 
                            0, $in, $out, $in, $out, $occ
                        )
                        ";
                    if ( !$ret = $this->odsconnector->executeSQL($sql) )
                    {
                        echo "Error in actual journey creation $this->vehicle_code, $fixtimestamp\n";
                        return -1;
                    }
                }

                // Insert total counts for the individual visit
                if ( $insrecordfortripsummary > -1 )
                {
                    $k1 = $insrecordfortripsummary;
                    if ( $this->debug )
                    {
                        echo "  PAX: $k $k1 ";
                        echo " Vis:".$existing_visit;
                        //echo "  ".$v["passengers"][$k1]["timestamp"]->format("Y-m-d H:i:s");
                        echo " In:".$totin;
                        echo " Out:".$totout;
                        echo " Occ:".$occ;
                        echo " bet:".$v["passengers"][$k1]["betweenstops"];
                        echo "";
                        echo "\n";
                    }
                    $this->processed_pax_ct_in += $totin;
                    $this->processed_pax_ct_out += $totout;
                    $l_count_time = $v["passengers"][$k1]["timestamp"];
                    $gisid = $v["passengers"][$k1]["gisid"];
                    $lat = $v["passengers"][$k1]["latitude"];
                    $long = $v["passengers"][$k1]["longitude"];
                    $l_hour_int = $l_count_time->format("H");
                    $l_min_int = $l_count_time->format("i");
                    $l_timestamp = $l_count_time->format("Y-m-d H:i:s");
                    $l_date_id = $l_count_time->format("Ymd");
                    $l_time_id = $l_count_time->format("His");
                    $sql = " INSERT INTO people_count_visit_fact (
                            fact_id, sourcefile, vehicle_id, driver_id,
                            journey_fact_id, visit_fact_id, gis_id, location_id, date_id,
                            time_id, timestamp, latitude, longitude, gpsAge,
                            speedKPH, bearing, in_count, out_count, total_in,
                            total_out, occupancy   ) VALUES (
                            0, '$this->sourcefile', $this->vehicle_id, $driver_id_db,
                            $journey_fact, $existing_visit, $gisid, ".$v["location_id"].", $l_date_id,
                            $l_time_id, '$l_timestamp', $lat, $long, 0, 0, 
                            0, $totin, $totout, $totin, $totout, $occ
                        )
                        ";
                    if ( !$ret = $this->odsconnector->executeSQL($sql) )
                    {
                        echo "Error in actual journey creation $this->vehicle_code, $fixtimestamp\n";
                        return -1;
                    }
                }
            }

            if ( $v["timing_point"] )
            {
                $last_tp_departure = false;
                if ( $departure_time )
                {
                    $last_tp_departure = $departure_time;
                }
                $last_tp_id = $v["location_id"];
            }

            $last_departure = false;
            if ( $departure_time )
            {
                $last_departure = $departure_time;
            }
            $last_loc_id = $v["location_id"];
        }

        // Now set actual journey times, number of stops visited
        if ( $firstlocno == 1 && $lastlocno == $number_stops_sched && $start_departure && $end_arrival )
        {
            $duration = $start_departure->diff($end_arrival);
            $duration_db = $duration->format("'%r%H:%I:%S'");
        }
        else
            $duration_db = "NULL";

        $end_arrival_db = ( $end_arrival ? $end_arrival->format("'Y-m-d H:i:s'") : "NULL" );

        $max_lateness_db = "NULL";
        $min_lateness_db = "NULL";
        $avg_lateness_db = "NULL";
        $totsecs = 0;
        if ( $max_lateness != "NA" )
        {
            $min_lateness_db = $min_lateness->format("'%r%H:%I:%S'");
            $max_lateness_db = $max_lateness->format("'%r%H:%I:%S'");
            $avg_lateness = round($sum_lateness / $num_latenesses);
            //echo " $sum_lateness / $num_latenesses = $avg_lateness ";
            $dt = new DateTime();
            $dt->setTimestamp($sum_lateness);
            if ( $sum_lateness < 0 )
                $avg_lateness_db = "'-".$dt->format("H:i:s")."'";
            else
                $avg_lateness_db = "'".$dt->format("H:i:s")."'";
            //echo "Min ".$min_lateness_db;
            //echo " Max ".$max_lateness_db;
            //echo " avg ".$avg_lateness_db."\n";
        }


        $sql = "UPDATE timetable_journey_fact SET 
                minimum_lateness = $min_lateness_db, 
                maximum_lateness = $max_lateness_db, 
                average_lateness = $avg_lateness_db, 
                actual_end = $end_arrival_db, 
                number_stops = $number_stops, 
                duration = $duration_db
            WHERE fact_id = $journey_fact";
        $sdep = " UNK ";
        $edep = " UNK ";
        if ( $start_departure )
            $sdep = $start_departure->format("H:i:s");
        if ( $end_arrival )
            $edep = $end_arrival->format("H:i:s");

        if ( $this->debug )
            echo " => $firstlocno-$lastlocno/$number_stops_sched  - duration $duration_db, number_stops = $number_stops / $number_stops_sched \n";
        if ( !$ret = $this->odsconnector->executeSQL($sql) )
        {
                echo "Error in actual journey finalisation $this->vehicle_code, $fixtimestamp\n";
                return -1;
        }


    }

    /*
    ** Converts a DateInterval value to pure seconds
    */
    function interval_to_secs($int)
    {
        //var_dump($int);
        $secs = 0;
        $extsec = $int->s;
        $extmin = $int->i;
        $exthr = $int->h;
        $invert = $int->invert;
        $x = $int->format("'%r%H:%I:%S'");
        $secs = ( $exthr * 3600 ) + ( $extmin * 60 ) + $extsec;
        if ( $invert )
            $secs = - $secs;
        return $secs;
    }
        
}

?>
