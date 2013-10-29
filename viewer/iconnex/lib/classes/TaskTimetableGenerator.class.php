<?php

global $workingdaystmt;

$workingdaystmt = false;

/**
** Class: TaskTimetableGenerator
** ------------------------------
**
** Passes through the timetable records as stored in the normalised
** publish_tt/vehicle_journey tables and generates timetable_journey
** and timetable_visit records for a range of days.
**
*/

class TaskTimetableGenerator extends ScheduledTask
{

    var $workingdaystmt = false;
    var $ttvinsstmt = false;

    // Array to store, for each
    // event profile in the table an indicator of whether it runs the timetable
    // generation day, so we dont have to keep looking it ups
    public $operationalEventStatus = array();
    public $thereareroutes = false;

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {

        while ( true )
        {

        $lastttb = $this->odsconnector->fetch1SQL("SELECT max(ttb_date_id) lastgenerated FROM timetable_journey");
        if ( !$lastttb )
        {
            echo "Cant retrieve latest timetable entry";
            return false;
        }


        $until = new DateTime();
        $until->Add(new DateInterval("P4D"));
        $today = new DateTime();
        $generate_from = DateTime::createFromFormat("Ymd", $lastttb["lastgenerated"]);
        if ( !$generate_from )
        {
            $generate_from = new DateTime();
            $generate_from = $generate_from->Sub(new DateInterval("P1D"));
        }
        $todaydiff = $generate_from->diff($today);
        $todaydiff = $today->diff($generate_from);
        $daydiff = $todaydiff->format("%r%d");
        if ( $daydiff < 0 )
        {
            $generate_from = new DateTime();
            $generate_from->sub(new DateInterval("P1D"));
        }

        echo "Generate from ".$generate_from->format("Y-m-d")." to ".$until->format("Y-m-d")."\n";
        $ct = 0;
        while ( true )
        {
            $generate_from = $generate_from->Add(new DateInterval("P1D"));
            $todaydiff = $generate_from->diff($until);
            $daydiff = $todaydiff->format("%r%d");

            $generate_date = $generate_from->format("Y-m-d");

            // Clear out day event matching elements
            $this->operationEventStatus= array();

            echo "\nGenerate Timetables for $generate_date\n";
            $this->thereareroutes = false;
            if ( !$this->routes_to_regenerate($generate_from) )
            {
                echo "Cant generate list to regenerate";
                return false;
            }

            if ( $this->thereareroutes )
            {
                $this->odsconnector->executeSQL("BEGIN WORK");
                //$this->odsconnector->executeSQL("LOCK TABLE timetable_journey IN SHARE MODE");
                //$this->odsconnector->executeSQL("LOCK TABLE timetable_visit IN SHARE MODE");
                if ( !$this->populate_daily_timetable( $generate_from, true) )
                {
                    $this->odsconnector->executeSQL("ROLLBACK WORK");
                    echo "ROLLBACK!!!!\n";
                    return false;
                }

                $status = $this->calculate_trip_predecessors_and_successors( $generate_from, true );
                if ( !$status )
                {
                    $this->odsconnector->executeSQL("ROLLBACK WORK");
                    echo "ROLLBACK!!!!\n";
                    return false;
                }
    
                $id_date = $generate_from->format("Ymd");
                $sql = "INSERT INTO daily_timetable_instance VALUES ( $id_date, CURRENT )";
                if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
                $this->odsconnector->executeSQL("COMMIT WORK");
            }
    
            if ( $ct > 5 || $daydiff <= 0 )
                break;
        }

        sleep (60);

        }

    }

    /*
    ** routes_to_regenerate
    **
    ** If wew are populating timetable_journey records for a day
    ** that already has journeys calculated then we only
    ** want to regenerate journeys for routes that have been imported recently
    */
    function routes_to_regenerate( $generate_date )
    {
        //$this->odsconnector->debug = 1;
        //$this->rtpiconnector->debug = 1;

        $sql = "DROP TABLE t_genroutes";
        $this->odsconnector->executeSQL($sql, "CONTINUE");
        $this->rtpiconnector->executeSQL($sql, "CONTINUE");
        $sql = "CREATE TEMP TABLE t_genroutes ( route_id integer ) WITH NO LOG;";
        if ( !$this->odsconnector->executeSQL($sql) )
            return false;
        if ( !$this->rtpiconnector->executeSQL($sql) )
            return false;

        $id_date = $generate_date->format("Ymd");
        $ymd_date = $generate_date->format("Y-m-d H:i:s");

        // If not generated for today .. generate for all routes
        $build = new TimetableJourneyBuild($this->rtpiconnector);
        $exists = $build->sqlToSingleRowInstance("SELECT * FROM daily_timetable_instance WHERE date_id = $id_date");
        if ( !$exists )
        {
            $this->thereareroutes = true;
            $sql = "INSERT INTO t_genroutes SELECT route_id FROM route";
            $ret = $this->rtpiconnector->executeSQL($sql);
            $ret = $this->odsconnector->executeSQL($sql);
            return $ret;
        }
        
        $imp = new RouteImportStatus($this->rtpiconnector);

        $ar = $imp->sqlToInstanceArray("
                        SELECT route_id, max(import_time) import_time 
                        FROM route_import_status
                        WHERE route_import_status.import_time > '".$exists->created."'
                        GROUP BY 1
                        ");

        foreach ( $ar as $v )
        {
            $v->dump();
            $this->thereareroutes = true;
            $sql = "insert into t_genroutes VALUES ( ".$v->route_id. ")";
            if ( !$this->rtpiconnector->executeSQL($sql) ) return false;
            if ( !$this->odsconnector->executeSQL($sql) ) return false;
        }

        return true;

    }


    /*
    ** populate_timetable_fact
    **
    ** Reads the realtime database to extractall timetables valid between the selected
    ** dates and populates the timetable fact table
    */
    function populate_daily_timetable( $generate_date, $replace = false)
    {
        $l_ndayno = 0;
        $l_nprevdayno = 0;
        $l_route_id = false;
        $l_route_code = false;
        $l_route_last = false;
        $w_service_id = false;
        $wr_trips = array();
        $lr_autort_sched = array();
        $daystart = 0;
        $dayend	= 0;
        $over_midnight = 0;
        $c_date_string = "";

        $visit_insert_ct = 0;


        $ymd_date = $generate_date->format('Y-m-d');
        $dmy_date = $generate_date->format('d/m/Y');
        $id_date = $generate_date->format('Ymd');
        $l_yesterday = DateTime::createFromFormat('Y-m-d', $ymd_date );
        $l_yesterday = $l_yesterday->Sub(new DateInterval("P1D"));
        $l_prevdate = $l_yesterday->format('d/m/Y');

        $l_dayno = DateTime::createFromFormat('Y-m-d', $ymd_date )->format('N');
        if ( $l_dayno == 7 ) $l_dayno = 0;
        $l_dayno++;

        $l_nprevdayno = $l_yesterday->format('N');
        if ( $l_nprevdayno == 7 ) $l_nprevdayno = 0;
        $l_nprevdayno++;
        echo "Build for ". $ymd_date. "(". $l_dayno. ") Yesterday $l_prevdate (". $l_nprevdayno. ") \n";
            

        $current_instance = $this->odsconnector->fetch1SQL("SELECT * FROM daily_timetable_instance WHERE date_id = $id_date");
        if ( $current_instance && !$replace )
        {
            echo "Timetable already exists for date id $id_date\n";
            return;
        }

        $sql = "DROP TABLE t_journeys_del";
        $stmt = $this->odsconnector->executeSQL( $sql, "CONTINUE" );

        $sql = "CREATE TEMP TABLE t_journeys_del ( timetable_id INTEGER ) WITH NO LOG";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        $sql = "INSERT INTO t_journeys_del SELECT timetable_id FROM timetable_journey WHERE ttb_date_id = $id_date 
            AND route_id in ( select route_id from t_genroutes )";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
//        $this->odsconnector->trace("PPP Built journeys to clear out");

        $sql = "LOCK TABLE timetable_visit IN SHARE MODE";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        $sql = "LOCK TABLE timetable_journey IN SHARE MODE";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        $sql = "DELETE FROM timetable_visit WHERE timetable_id in (SELECT timetable_id FROM t_journeys_del)";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
//        $this->odsconnector->trace("PPP Deleted Matching Timetable Visits");

        $sql = "DELETE FROM timetable_visit_live WHERE timetable_id in (SELECT timetable_id FROM t_journeys_del)";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
//        $this->odsconnector->trace("PPP Deleted Matching Timetable Live Visit");

        $sql = "DELETE FROM timetable_journey_live WHERE timetable_id in (SELECT timetable_id FROM t_journeys_del)";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
//        $this->odsconnector->trace("PPP Deleted Matching Timetable Live Journeys");

        $sql = "DELETE FROM timetable_journey WHERE timetable_id in (SELECT timetable_id FROM t_journeys_del)";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
//        $this->odsconnector->trace("PPP Deleted Matching Timetable Journeys");

        $sql = "DELETE FROM daily_timetable_instance WHERE date_id = $id_date";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        $now = new DateTime();
        $now = $now->format("Y-m-d H:i:s");


        // Cursor fetches all published services for which to
        // generate autort_sched entries
        $sql = 
            " SELECT operator.operator_id, route.route_id, service.service_id, route_code, service_code
                FROM operator, route, service,route_param, t_genroutes
                WHERE route.route_id = service.route_id
                AND t_genroutes.route_id = route.route_id
                AND operator.operator_id = route.operator_id
                AND route_param.route_id = route.route_id  ".
                //"AND service_code = 'JP1'".
                // "AND route.route_code = 'SER171'".
                //"AND route.route_code = '100'".
                " AND '$ymd_date' BETWEEN service.wef_date AND service.wet_date 
                ORDER BY service.service_id";

        if ( !($stmt = $this->rtpiconnector->executeSQL( $sql )) ) return false;

        // Cursor fetches all published trips for each service_id
        $sql =  
            "SELECT publish_tt.*, operator.operator_id, operator.operator_code, route.route_code 
                FROM publish_tt 
                JOIN service ON publish_tt.service_id = service.service_id
                JOIN route ON service.route_id = route.route_id
                JOIN operator ON route.operator_id = operator.operator_id
                WHERE publish_tt.service_id = :service_id
                --AND trip_no matches '*op'
                ORDER BY evprf_id, publish_tt.pub_ttb_id, orun_code, runningno, trip_no, duty_no, publish_tt.pub_ttb_id";
        if ( !($publist = $this->rtpiconnector->prepareSQL( $sql ))) return false;

        // Cursor fetches all published trips for each service_id
        $sql =  
            "SELECT service_patt.rpat_orderby, rtpi.location_id rtpi_loc, rtpi.travel_time || '' rtpi_travel, rtpi.wait_time || '' rtpi_wait, ".
                "pub.location_id pub_loc, pub.travel_time || '' pub_travel, pub.wait_time || '' pub_wait, pub.timing_point
                FROM service_patt 
                JOIN location ON location.location_id = service_patt.location_id JOIN route_location rtpi ON rtpi.rpat_orderby = service_patt.rpat_orderby AND rtpi.profile_id = :rtpi_prof_id
                LEFT JOIN route_location pub ON pub.rpat_orderby = service_patt.rpat_orderby AND pub.profile_id = :pub_prof_id
                WHERE service_patt.service_id = :service_id
                ORDER BY service_patt.rpat_orderby";

        if ( !($pubvisitlist = $this->rtpiconnector->prepareSQL( $sql ))) return false;

        $lr_autort_sched = false;

        // Build new autort_sched
        $l_route_last = false;
        $l_event_last = false;
        $l_daystart_last = false;
        $l_dayend_last = false;
        $rolling_arrival_time = false;
        $cur_pub_id = 0;
        $ct = 0;
        $this->odsconnector->trace("Start Timetable Generation");


        // DataModel 
        $ttv = new TimetableVisit($this->odsconnector);
        //$ttv->addPrepare();

        $this->rows_affected++;

        while ( $row = $stmt->fetch() )
        {
            $l_operator_id = $row["operator_id"];
            $l_route_id = $row["route_id"];
            $w_service_id = $row["service_id"];
            $l_route_code = trim($row["route_code"]);
            $l_service_code = trim($row["service_code"]);
            $l_route_last = $l_route_code;

            $l_ndayno = $l_dayno;
            //$l_ndayno = $get_special_op_mapping($l_dayno - 1, $l_route_id, $today) + 1;
            if ( !$l_ndayno )
            {
                echo " - Ignoring ", $l_route_code;
                continue;
            }

            // Commented out because this stops todays ttb showing if yesterday was a holiday
            // $l_nprevdayno = $get_special_op_mapping(l_prevdayno - 1, l_route_id, today - 1) + 1
            // if l_nprevdayno is null {
                // echo " - Prev Day Ignoring ", $l_route_code
                // continue foreach
            // }

            $daystart = 0;
            $dayend = 0;
            $cur_pub_id = 0;
            $arrival_ct = 0;
            $loopct = 0;
            $validtripforcreation = false;
            $journey_start_time = false;
            //echo "SERVICE $w_service_id\n";
            $journey_count_act = 0;
            $journey_count = 0;

            $publist->execute(array(":service_id" => $w_service_id));
            while ( $wr_trips = $publist->fetch() )
            {
                $loopct ++;
//echo $wr_trips["trip_no"]." ";
//echo $wr_trips["running_no"]." ";
////echo $wr_trips["start_time"]." ";
//echo "\n";

                $journey_count++;
                // Set trip end time after it is calculated
                if ( $arrival_ct )
                        $this->set_trip_end_time($journey_start_time, $rolling_arrival_time, $cur_pub_id, $arrival_ct );

                $validtripforcreation = false;

                $arrival_ct = 0;
                if ( !$wr_trips["duty_no"] )
                    $wr_trips["duty_no"] = "0";
        
                // Calculate what type of days the journey operates on
                $dowop = $this->isOperationalEvent("dow", $generate_date, $wr_trips["evprf_id"], $wr_trips["over_midnight"], false, false, false);
                $hop = $this->isOperationalEvent("holiday_op", $generate_date, $wr_trips["holiday_op"], false, false, false);
                $hnoop = $this->isOperationalEvent("holiday_noop", $generate_date, $wr_trips["holiday_noop"], false, true, 2);
                $owop = $this->isOperationalEvent("org_working_op", $generate_date, $wr_trips["org_working_op"], false, false, false);
                $ownoop = $this->isOperationalEvent("org_working_noop",$generate_date, $wr_trips["org_working_noop"], false, true, 2);
                $ohop = $this->isOperationalEvent("org_holiday_op", $generate_date, $wr_trips["org_holiday_op"], false, false, false);
                $ohnoop = $this->isOperationalEvent("org_holiday_noop", $generate_date, $wr_trips["org_holiday_noop"], false, true, 2);
                $spop = $this->isOperationalEvent("special_days_op", $generate_date, $wr_trips["special_days_op"], false, false, false);
                $spnoop = $this->isOperationalEvent("special_days_noop", $generate_date, $wr_trips["special_days_noop"], false, true, 2);

                // Trip operates if operational on DOW unless it :-
                //  operates on holiday and its holidays or
                //  operates on a school term time and it is one or
                //  operates on a school holiday and it is one or
                //  operates on a special day and it is one
                $op = $dowop;
                if ( $wr_trips["holiday_op"] && $hop ) $op = true;
                if ( $wr_trips["org_working_op"] && !$owop ) $op = false;
                if ( $wr_trips["org_holiday_op"] && !$ohop ) $op = false;
                if ( $wr_trips["special_days_op"] && !$spop ) $op = false;

                $oponday = $op;
                if (  !$oponday || !$hnoop || !$ownoop || !$ohnoop  || !$spnoop  )
                {
                    $res = $dowop || $hop || $owop || $spop ;
                    continue;
                }

                $l_id_last = $wr_trips["pub_ttb_id"];

                $vehicle_journeys["operator_id"] = $l_operator_id;
                $vehicle_journeys["route_id"] = $l_route_id;
                $vehicle_journeys["duty_no"] = trim($wr_trips["duty_no"]);
                $vehicle_journeys["etm_trip_no"] = trim($wr_trips["etm_trip_no"]);
                $vehicle_journeys["trip_no"] = trim($wr_trips["trip_no"]);
                if ( $vehicle_journeys["etm_trip_no"] )
                {
                    $vehicle_journeys["trip_no"] = trim($wr_trips["etm_trip_no"]);
                }

                $vehicle_journeys["running_no"] = trim($wr_trips["runningno"]);
                $vehicle_journeys["orun_code"] = trim($wr_trips["orun_code"]);
                $vehicle_journeys["operation_date"] = $ymd_date;
        
                $c_date_string = $ymd_date . " ". $wr_trips["start_time"] ;
                $x = DateTime::createFromFormat('Y-m-d H:i:s', $c_date_string );
                $y = DateTime::createFromFormat('Y-m-d H:i:s', $c_date_string );
                $ttbdateid = $x->format('Ymd');
                $opdateid = $x->format('Ymd');
                $vehicle_journeys["scheduled_start"] = DateTime::createFromFormat('Y-m-d H:i:s', $c_date_string )->format('Y-m-d H:i:s');
                if ( $over_midnight ) 
                {
                    $vehicle_journeys["scheduled_start"] = $x->Add(new DateInterval("P1D"))->format('Y-m-d H:i:s');
                    $opdateid = $y->Add(new DateInterval("P1D"))->format('Ymd');
                }
                $vehicle_journeys["time_id"] = DateTime::createFromFormat('Y-m-d H:i:s', $c_date_string )->format('His');
                $vehicle_journeys["direction"] = $wr_trips["direction"];
                $vehicle_journeys["start_status"] = 0;
                $vehicle_journeys["profile_id"] = $wr_trips["pub_prof_id"];
                $vehicle_journeys["pub_ttb_id"] = $wr_trips["pub_ttb_id"];
        
                // Add this trip to the schedule
                //echo //$vehicle_journeys["route_id"]," ",
                    //$vehicle_journeys["duty_no"]," ",
                    //$vehicle_journeys["trip_no"]," ",
                    //$vehicle_journeys["running_no"]
                                
                // Check for a duplicate trip
                if (    ( 
                    $lr_autort_sched["trip_no"] &&
                    $lr_autort_sched["trip_no"] == $vehicle_journeys["trip_no"] &&
                    $lr_autort_sched["route_id"] == $vehicle_journeys["route_id"] &&
                    $lr_autort_sched["orun_code"] == $vehicle_journeys["orun_code"] 
                    )
                    &&
                    $lr_autort_sched["running_no"] == $vehicle_journeys["running_no"] &&
                    $lr_autort_sched["duty_no"] == $vehicle_journeys["duty_no"] ) 
                {
                        echo "Duplicate found ";
        //				echo $vehicle_journeys["route_id"],  " ",
        //				vehicle_journeys.trip_no,  " ",
        //				vehicle_journeys.duty_no,  " ",
        //				vehicle_journeys.trip_no
                } else {
                }
                    
                $sql = "INSERT INTO timetable_journey
                    (
                            timetable_id, ext_timetable_id, ttb_date_id, actual_date_id,
                            over_midnight, journey_pattern_id, time_id, 
                            operator_id, route_id, service_id, route_code, 
                            duty_no, running_no, trip_no, etm_trip_no,
                            start_time, end_time, direction, number_stops
                    )
                    VALUES 
                    (
                            0, ".$vehicle_journeys["pub_ttb_id"].", $ttbdateid, $opdateid,
                            ".$over_midnight.", 0, ".$vehicle_journeys["time_id"].",
                            ".$vehicle_journeys["operator_id"].", ".$vehicle_journeys["route_id"].", ".$w_service_id.", '".$l_route_code."', 
                            '".$vehicle_journeys["duty_no"]."', '".$vehicle_journeys["running_no"]."', '".
                            $vehicle_journeys["trip_no"]."', '".
                            $vehicle_journeys["etm_trip_no"]."', 
                            '".$vehicle_journeys["scheduled_start"]."', '".$vehicle_journeys["scheduled_start"]."', 0, 0
                    )";
                if ( !( $this->odsconnector->executeSQL( $sql )) ) 
                {
                    echo "faile";
                    return false;
                }
                $journey_count_act++;
                $cur_pub_id = $this->odsconnector->lastInsertId("timetable_journey", "timetable_id");
       
                $prev_loc = 0;
                $prev_tp = 0;
                $time_from_prev_tp = new DateInterval("PT0S");
                $time_from_prev = new DateInterval("PT0S");
                $journey_start_time = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $rolling_pub_arrival_time = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $rolling_pub_departure_time = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $rolling_arrival_time = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $rolling_departure_time = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $last_tp_departure = DateTime::createFromFormat('Y-m-d H:i:s', $vehicle_journeys["scheduled_start"]);
                $validtripforcreation = true;

                if ( !$validtripforcreation )
                    continue;

                $pubvisitlist->execute(
                        array(
                            ":service_id" => $w_service_id,
                            ":rtpi_prof_id" => $wr_trips["rtpi_prof_id"],
                            ":pub_prof_id" => $wr_trips["pub_prof_id"]
                            ));
                $visitct = 0;

                while ( $wr_pub_visit = $pubvisitlist->fetch() )
                {
                    $visitct++;

                    $l_ord = $wr_pub_visit["rpat_orderby"];
                    $l_loc = $wr_pub_visit["rtpi_loc"];
                    $l_travel = trim($wr_pub_visit["rtpi_travel"]);
                    $l_wait = trim($wr_pub_visit["rtpi_wait"]);
                    $l_pub_travel = trim($wr_pub_visit["pub_travel"]);
                    $l_pub_wait = trim($wr_pub_visit["pub_wait"]);
                    $l_timingpoint = $wr_pub_visit["timing_point"];
        
                    if ( $l_timingpoint == "N" )
                        $l_timingpoint = "0";
                    if ( $l_timingpoint == "Y" )
                        $l_timingpoint = "1";
                    if ( !$l_timingpoint )
                        $l_timingpoint = "0";
        
                    if ( $l_pub_travel )
                        $l_timingpoint = "1";
            
                    $l_travelint = $this->stringToInterval($l_travel);
                    $l_waitint = $this->stringToInterval($l_wait);
       
                    //var_dump($rolling_arrival_time); echo "\n";
                    if ( $l_ord == 0 )
                    {
                        // nothing
                    }
                    else
                    {
                        $rolling_arrival_time = $rolling_departure_time->add($l_travelint);
                        $rolling_departure_time->setTimestamp($rolling_arrival_time->getTimestamp());
                        $rolling_departure_time->add($l_waitint);
                    }
                    $arr = $rolling_arrival_time;
                    $dep = $rolling_departure_time;
        
                    $arr_dateid = $rolling_arrival_time->format("Ymd");
                    $dep_dateid = $rolling_departure_time->format("Ymd");
                    $arr_timeid = $rolling_arrival_time->format("His");
                    $dep_timeid = $rolling_departure_time->format("His");
                    $arr_timestamp = $rolling_arrival_time->format("Y-m-d H:i:s");
                    $dep_timestamp = $rolling_departure_time->format("Y-m-d H:i:s");
                    $dwelltime = null;
                    $traveltime = $l_travelint->format("%H:%I:%S");
                    $traveltimetp = null;
                    $layover = "0";
        
                    $l_pub_travelint = false;
                    $l_pub_waitint = false;
                    if ( $l_ord == 1 || ( $prev_tp && $l_pub_travel ) )
                    {
                        $l_pub_travelint = $this->stringToInterval($l_pub_travel);
                        $l_pub_waitint = $this->stringToInterval($l_pub_wait);
                        $rolling_pub_arrival_time = $rolling_pub_arrival_time->add($l_pub_travelint);
                        $rolling_pub_departure_time->setTimestamp($rolling_pub_arrival_time->getTimestamp());
                        $rolling_pub_departure_time->add($l_waitint);
                        $traveltimetp = $l_pub_travelint->format("%H:%I:%S");
                        $dwelltime = $l_pub_waitint->format("%H:%I:%S");
                    }

/*
                    if ( !$this->ttvinsstmt )
                    {
                        $sql = "INSERT INTO timetable_visit 
                            (
                            timetable_visit_id, timetable_id, sequence, location_id,
                            prev_id, prev_tp_id, timing_point, arrival_date_id,
                            departure_date_id, arrival_time_id, departure_time_id,
                            arrival_time, departure_time, dwell_time, travel_time_loc,
                            travel_time_tp, layover 
                            ) VALUES (
                            0, :cur_pub_id, :l_ord, :l_loc,
                            :prev_loc, :prev_tp, :l_timingpoint, :arr_dateid,
                            :dep_dateid, :arr_timeid, :dep_timeid, 
                            :arr_timestamp, :dep_timestamp, :dwelltime, :traveltime,
                            :traveltimetp, :layover
                            )";
                        $sql = "INSERT INTO timetable_visit 
                            (
                            timetable_visit_id, timetable_id, sequence, location_id,
                            prev_id, prev_tp_id, timing_point, arrival_date_id,
                            departure_date_id, arrival_time_id, departure_time_id,
                            arrival_time, departure_time, dwell_time
                            ) VALUES (
                            0, :cur_pub_id, :l_ord, :l_loc,
                            :prev_loc, :prev_tp, :l_timingpoint, :arr_dateid,
                            :dep_dateid, :arr_timeid, :dep_timeid,
                            :arr_timestamp, :dep_timestamp, :dwelltime
                            )";
                        $this->ttvinsstmt = $this->odsconnector->prepareSQL($sql);
                    }
     //echo "cur_pub_id: ".  $cur_pub_id."\n";
     //echo "l_ord: ".  $l_ord."\n";
     //echo "l_loc: ".  $l_loc."\n";
     //echo "prev_loc: ".  $prev_loc."\n";
     //echo "prev_tp: ".  $prev_tp."\n";
     //echo "l_timingpoint: ".  $l_timingpoint."\n";
     //echo "arr_dateid: ".  $arr_dateid."\n";
     //echo "dep_dateid: ".  $dep_dateid."\n";
     //echo "arr_timeid: ".  $arr_timeid."\n";
     //echo "dep_timeid: ".  $dep_timeid."\n";
     //echo "arr_timestamp: ".  $arr_timestamp."\n";
     //echo "dep_timestamp: ".  $dep_timestamp."\n";
     //echo "traveltime: ".  $traveltime."\n";
     //echo "traveltimetp: ".  $traveltimetp."\n";
     //echo "layover: ".  $layover."\n";
$dwelltime="0:00:000";
     echo "dwelltime: ".  $dwelltime."\n";
$tms = array ( 
"0000", 
"0 00:00:00 ", 
"  0   ", 
"0 00:00:00", 
"00 00:00:00.000", 
"0-0 0 00:00:00", 
"0 00:00:00", 
"0 00:00:00", 
"0 00:00:00", 
null );
foreach ( $tms as $v )
{
             $this->odsconnector->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             $stat = false;
             try {
$dwelltime=$v;
echo $dwelltime. " = ";

                    $this->ttvinsstmt->bindValue(":cur_pub_id", $cur_pub_id, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":l_ord", $l_ord, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":l_loc", $l_loc, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":prev_loc", $prev_loc, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":prev_tp", $prev_tp, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":l_timingpoint", $l_timingpoint, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":arr_dateid", $arr_dateid, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":dep_dateid", $dep_dateid, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":arr_timeid", $arr_timeid, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":dep_timeid", $dep_timeid, PDO::PARAM_INT);
                    $this->ttvinsstmt->bindValue(":arr_timestamp", $arr_timestamp);
                    $this->ttvinsstmt->bindValue(":dep_timestamp", $dep_timestamp);
                    $this->ttvinsstmt->bindValue(":dwelltime", $dwelltime, PDO::PARAM_STR);
                    //$this->ttvinsstmt->bindValue(":traveltime", $traveltime);
                    //$this->ttvinsstmt->bindValue(":traveltimetp", $traveltimetp);
                    //$this->ttvinsstmt->bindValue(":layover", $layover, PDO::PARAM_INT);

                    $this->ttvinsstmt->execute();
                    echo "row ct i".$this->ttvinsstmt->rowcount()."\n";
             }
             catch ( Exception $ex )
             {  
                echo "ERROR\n";
             }
}
die;
echo "oo";die;
                    die;

                    if ( $visit_insert_ct++ > 200 )
                    {
                        $this->ttvinsstmt = false;
                        $visit_insert_ct = 0;
                    }
                    //$this->ttvinsstmt = false;
                    $this->ttvinsstmt->execute();
                    */
                    $ttv->timetable_visit_id = 0;
                    $ttv->timetable_id = $cur_pub_id ;
                    $ttv->sequence = $l_ord ;
                    $ttv->location_id = $l_loc ;
                    $ttv->prev_id = $prev_loc ;
                    $ttv->prev_tp_id = $prev_tp ;
                    $ttv->timing_point = $l_timingpoint ;
                    $ttv->arrival_date_id = $arr_dateid ;
                    $ttv->departure_date_id = $dep_dateid ;
                    $ttv->arrival_time_id = $arr_timeid ;
                    $ttv->departure_time_id = $dep_timeid ;
                    $ttv->arrival_time = $arr_timestamp;
                    $ttv->departure_time = $dep_timestamp;
                    $ttv->dwell_time = $dwelltime;
                    $ttv->travel_time_loc = $traveltime;
                    $ttv->travel_time_tp = $traveltimetp;
                    $ttv->layover = $layover ;
                    $ttv->add();

                    $arrival_ct = $l_ord;
        
                    $prev_loc = $l_loc;
                    if ( $l_pub_travel ) // && is a timing point
                    {
                        $prev_tp = $l_loc;
                        $last_tp_departure = $rolling_departure_time;
                    }
                }
                //$this->odsconnector->trace("$ttbdateid / $opdateid ".
                                ////" Rt:".$vehicle_journeys["route_id"].
                                //" Rt:".$l_route_code.
                                //" Sv:".$l_service_code.
                                //" Rn:".$vehicle_journeys["running_no"].
                                //" Dt:".$vehicle_journeys["duty_no"].
                                //" Trip:".$vehicle_journeys["trip_no"].
                                //" Time id:".$vehicle_journeys["time_id"].
                                //" Time :".$vehicle_journeys["scheduled_start"]." dow $daystart - $dayend"." visits = $visitct");
            }
            if ( $journey_count_act > 0 )
            {
                $this->odsconnector->trace("Route $l_route_code / $l_service_code - journeys = $journey_count_act / $journey_count");
                $this->rows_affected += $journey_count_act;
                if ( $this->task_id ) $this->save();
            }
            //if ( $loopct ) echo "PPP loop ".$row["service_code"]." ".$loopct."\n";
            // Set trip end time after it is calculated
            if ( $arrival_ct )
                $this->set_trip_end_time( $journey_start_time, $rolling_arrival_time, $cur_pub_id, $arrival_ct );
        }
        return true;
    }

    /*
    ** calculate_running_board_sequences
    **
    ** Passes through all trips for day by running baord order in order
    ** to set the previous and next trip relevant to each trip.
    */
    function calculate_trip_predecessors_and_successors( $generate_date )
    {

        $l_ndayno = 0;
        $l_nprevdayno = 0;
        $l_route_id = false;
        $l_route_code = false;
        $l_route_last = false;
        $w_service_id = false;
        $wr_trips = array();
        $lr_autort_sched = array();
        $daystart = 0;
        $dayend	= 0;
        $over_midnight = 0;
        $c_date_string = "";


        $ymd_date = $generate_date->format('Y-m-d');
        $dmy_date = DateTime::createFromFormat('Y-m-d', $ymd_date )->format('d/m/Y');
        $dmy_date_id = DateTime::createFromFormat('Y-m-d', $ymd_date )->format('Ymd');

        echo "Calculate Running board order for ". $ymd_date . "\n";

        $sql = "DROP TABLE t_timetable";
        $stmt = $this->odsconnector->executeSQL( $sql, "CONTINUE" );

        // Cursor fetches all running boards for day
        $sql = $this->odsconnector->syntax_create_temp_table_from_select("t_timetable", 
            " SELECT operator_id, running_no, timetable_id, start_time, end_time
                FROM timetable_journey
                WHERE ttb_date_id = $dmy_date_id");
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;
        
        $sql = "CREATE INDEX t_ix_timetable ON t_timetable ( operator_id, running_no )";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        // Cursor fetches all running boards for day
        $sql = 
            " SELECT DISTINCT operator_id, running_no
                FROM t_timetable
                ORDER BY 1, 2";
        if ( !($stmt = $this->odsconnector->executeSQL( $sql )) ) return false;

        // Cursor fetches all published trips for each service_id
        $sql =  
            "SELECT timetable_id, start_time, end_time
                FROM t_timetable
                WHERE operator_id = :operator_id
                AND running_no = :running_no
                ORDER BY  2, 3";
        if ( !($triplist = $this->odsconnector->prepareSQL( $sql ))) return false;

        $this->odsconnector->trace("Start Running Board Analysis");
        while ( $row = $stmt->fetch() )
        {
            $l_operator_id = $row["operator_id"];
            $l_running_no = trim($row["running_no"]);

            $triplist->execute(array(
                                ":operator_id" => $l_operator_id,
                                ":running_no" => $l_running_no
                                ));

            $last_trip_id = false;
            $last_trip_end = false;
            $loopct = 0;
            while ( $wr_trips = $triplist->fetch() )
            {
                $loopct ++;

                $trip_id = $wr_trips["timetable_id"];
                $trip_start = $wr_trips["start_time"];
                $trip_end = $wr_trips["end_time"];
        
                    
                if ( $last_trip_id )
                {
                    $sql = "UPDATE timetable_journey
                        SET ( prev_timetable_id, prev_journey_end)
                        =
                        ( $last_trip_id, '$last_trip_end' )
                        WHERE timetable_id = $trip_id ";
                    if ( !( $this->odsconnector->executeSQL( $sql )) ) 
                    {
                        echo "Failed to set trip's previous trip\n";
                        die;
                    }

                    $sql = "UPDATE timetable_journey
                        SET ( next_timetable_id, next_journey_start)
                        =
                        ( $trip_id, '$trip_start' )
                        WHERE timetable_id = $last_trip_id ";
                    if ( !( $this->odsconnector->executeSQL( $sql )) ) 
                    {
                        echo "Failed to set trip's previous trip\n";
                        die;
                    }
                }

                $last_trip_id = $trip_id;
                $last_trip_end = $trip_end;
            }
            //$this->odsconnector->trace("Finished Running Board Analysis RB $l_operator_id / $l_running_no .. trips = $loopct ");
        }
        $this->odsconnector->trace("Finished Running Board Analysis");
        return true;
    }

    /**
    ** isOperationalEvent
    **
    ** Searches journey operational profile to see if journey is operational on generation date
    */
    function isOperationalEvent($type, $testdate, $evprf_id, $over_midnight = false, $testForNonOperation = false, $valueIfUnspecified = false)
    {
        if ( !$evprf_id )
            return $valueIfUnspecified;

        $strdate = $testdate->format("Ymd");
        $key = "${type}_${evprf_id}_$strdate";
        if ( isset($this->operationEventStatus[$key]) )
            return $this->operationEventStatus[$key];

        $opprofile = new EventProfile($this->odsconnector);
        $opprofile->evprf_id = $evprf_id;
        $opprofile->load();
        $opprofile->fetchEvents();
        $this->operationEventStatus[$key] = $opprofile->operationalOnDate($testdate, $testForNonOperation, "Y-m-d");
//echo "new $key $type $evprf_id = ".$this->operationEventStatus[$key]."<BR>";
        return $this->operationEventStatus[$key];
    }

    function workingdays($f_evprf_id, &$f_rpdy_start, &$f_rpdy_end, $debug = false)
    {
          $f_rpdy_start  = 0;
          $f_rpdy_end  = 0;
          $f_dyarr = array();
          $f_indx = 0 ;

       for ( $f_indx = 1; $f_indx < 8; $f_indx++ )
          $f_dyarr[$f_indx] = 0;

        if ( !$this->workingdaystmt )
        {
            $sql = "
            select rpdy_start,rpdy_end
            from event_pattern,event
            where event_pattern.event_id = event.event_id
            and event.event_tp = 3
            and operational= 'Y'
            and event_pattern.evprf_id = :f_evprf_id
            ";
            $this->workingdaystmt = $this->rtpiconnector->prepareSQL($sql);
        }
       
       $this->workingdaystmt->execute(array(":f_evprf_id" => $f_evprf_id));
       while ( $row = $this->workingdaystmt->fetch() )
       {
          $f_rpdy_start = $row["rpdy_start"];
          $f_rpdy_end = $row["rpdy_end"];
          for ( $f_indx = $f_rpdy_start; $f_indx <= $f_rpdy_end; $f_indx++ )
          {
             $f_dyarr[$f_indx + 1] = 1;
          }
       }
        if ( $debug )
        {
            echo " $f_rpdy_start  $f_rpdy_end \n";
            var_dump($f_dyarr);
        }

       //echo $f_evprf_id."\n";
       //var_dump($f_dyarr);
       $f_rpdy_start = false;
       $f_rpdy_end = false;
       for ( $f_indx = 1; $f_indx < 8; $f_indx++ )
       {
          if ( $f_dyarr[$f_indx] == 1 ) 
          {
             if ( !$f_rpdy_start ) {
                $f_rpdy_start = $f_indx;
             }
          }
          else
          {
             if ( !$f_rpdy_end && $f_rpdy_start ) 
             {
                $f_rpdy_end = $f_indx - 1;
                break;
             }
          }
       }

       if ( !$f_rpdy_end ) {
          $f_rpdy_end = 7;
       }

       return;
    }

    function stringToInterval($interval)
    {
        $secs = strtotime($interval) - strtotime('TODAY') ;

        $hr = floor($secs / 3600); 
        $rem = $secs - ( $hr * 3600 ) ;
        $mn = floor($rem / 60);
        $sc = $rem - ( $mn * 60 ) ;

        $timestring = "PT${hr}H${mn}M${sc}S";
        $retval = new DateInterval($timestring);

        return $retval;

    }

    function set_trip_end_time ( $starttime, $endtime, $pubid, $arrival_ct )
    {
        if ( $pubid )
        {
            $duration = $endtime->diff($starttime);
            $duration = $duration->format("'%H:%I:%S'");
            $sql = "UPDATE timetable_journey SET 
                    end_time = ".$endtime->format("'Y-m-d H:i:s'").", 
                    duration = $duration,
                    number_stops = $arrival_ct
                    WHERE timetable_id = $pubid";
            if ( !( $this->odsconnector->executeSQL( $sql )) ) 
            {
                echo "faile";
                return false;
            }
        }
        return true;
    }

}
?>
