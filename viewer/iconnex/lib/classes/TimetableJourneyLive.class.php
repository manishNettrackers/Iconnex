<?php

require_once("DataModel.class.php");

class TimetableJourneyLive extends DataModel
{
    private $log;

    public $timetable_journey = NULL;
    public $vehicle = NULL;
    public $visits = NULL;
    public $active_vehicle = NULL;

    function __construct($connector = false)
    {
        //Utility::memory_increase();
        $this->log = Logger::getLogger(__CLASS__);
//        $this->log->debug("__construct()");

        $this->columns = array (
            "fact_id" => new DataModelColumn($this->connector, "fact_id", "serial", false),
            "timetable_id" => new DataModelColumn($this->connector, "timetable_id", "integer"),
            "operator_id" => new DataModelColumn($this->connector, "operator_id", "integer"),
            "route_id" => new DataModelColumn($this->connector, "route_id", "integer"),
            "vehicle_id" => new DataModelColumn($this->connector, "vehicle_id", "integer"),
            "driver_id" => new DataModelColumn($this->connector, "driver_id", "integer"),
            "start_date_id" => new DataModelColumn($this->connector, "start_date_id", "integer"),
            "start_time_id" => new DataModelColumn($this->connector, "start_time_id", "integer"),
            "end_date_id" => new DataModelColumn($this->connector, "end_date_id", "integer"),
            "end_time_id" => new DataModelColumn($this->connector, "end_time_id", "integer"),
            "actual_start" => new DataModelColumn($this->connector, "actual_start", "datetime"),
            "actual_end" => new DataModelColumn($this->connector, "actual_end", "datetime"),
            "sched_duration" => new DataModelColumn($this->connector, "sched_duration", "interval"),
            "minimum_lateness" => new DataModelColumn($this->connector, "minimum_lateness", "interval"),
            "maximum_lateness" => new DataModelColumn($this->connector, "maximum_lateness", "interval"),
            "average_lateness" => new DataModelColumn($this->connector, "average_lateness", "interval"),
            "start_stop" => new DataModelColumn($this->connector, "start_stop", "integer"),
            "end_stop" => new DataModelColumn($this->connector, "end_stop", "integer"),
            "number_stops" => new DataModelColumn($this->connector, "number_stops", "integer"),
            "number_stops_sched" => new DataModelColumn($this->connector, "number_stops_sched", "integer"),
            "next_journey_fact" => new DataModelColumn($this->connector, "next_journey_fact", "integer"),
            "start_code" => new DataModelColumn($this->connector, "start_code", "char", 8),
            "driver2_id" => new DataModelColumn($this->connector, "driver2_id", "integer"),
            "curr_lateness" => new DataModelColumn($this->connector, "curr_lateness", "interval"),
            "trip_status" => new DataModelColumn($this->connector, "trip_status", "char", 1),
            "lost_count" => new DataModelColumn($this->connector, "lost_count", "integer"),
            "lost_status" => new DataModelColumn($this->connector, "lost_status", "char", 1)
            );
        $this->tableName = "timetable_journey_live";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("fact_id");

        parent::__construct($connector);

        $this->visits = array();
    }

    function initialise($event_journey_details, $timetable_journey, $vehicle)
    {
        global $rtpiconnector;
        if (!$event_journey_details)
        {
            $this->log->error("initialise() event_journey_details is not set");
            return false;
        }
        if (!$timetable_journey)
        {
            $this->log->error("initialise() timetable_journey is not set");
            return false;
        }
        if (!$vehicle)
        {
            $this->log->error("initialise() vehicle is not set");
            exit;
            return false;
        }

        $employee = new Employee($rtpiconnector);
        $employee->operator_id = $vehicle->operator_id;
        $employee->employee_code = $event_journey_details->driver_code;
        if (!$employee->load(array("operator_id", "employee_code")))
        {
            $employee->employee_id = 0;
            $employee->fullname = "Unknown";
            $employee->add();
        }

        $this->fact_id = 0;
        $this->timetable_id = $timetable_journey->timetable_id;
        $this->operator_id = $timetable_journey->operator_id;
        $this->route_id = $timetable_journey->route_id;
        $this->vehicle_id = $vehicle->vehicle_id;
        $this->driver_id = $employee->employee_id;
        $this->start_date_id = $event_journey_details->receipt_time->format("Ymd");
        $this->start_time_id = $event_journey_details->receipt_time->format("Hms");
//        $this->end_date_id = 
//        $this->end_time_id = 
        $this->actual_start = $event_journey_details->receipt_time->format("Y-m-d H:m:s");
//        $this->actual_end = NULL; // unknown
        $this->sched_duration = $timetable_journey->duration;
//        $this->minimum_lateness
//        $this->maximum_lateness
//        $this->average_lateness
//        $this->start_stop =  // this will be the sequence number of the first stop detected on the trip
//        $this->end_stop =  // this will be the sequence number of the last stop detected on the trip
//        $this->number_stops // this will be the number of stops actually detected on the trip
        $this->number_stops_sched = $timetable_journey->number_stops;
//        $this->next_journey_fact // this will be the fact_id of the CONT trip, once created
        $this->start_code = "REAL";
        if ( $vehicle->vehicle_code == "AUT" )
            $this->start_code = "AUT";
//        $this->driver2_id // this will be the id of a second driver if there is a changeover during this trip
//        $this->curr_lateness $timetable_journey->

        // Trip Status will be set to (P)ending for real time journeys started but not yet poisitoned on 
        // route via tracking algorithm. Scheduled Only journeys however will have status of (A)ctive
        // since they can be used immediately for prediction generation
        $this->trip_status = "P";

        if ( $vehicle->vehicle_code == "AUT" )
            $this->trip_status = "A";

//        $this->lost_count
//        $this->lost_status

        $this->vehicle = $vehicle;

        return true;
    }

    /**
     * @brief Build an array of timetable_visit_live objects for this
     * timetable_journey.
     * 
     * Loads timetable_visit_live records for this
     * timetable_journey.timetable_id. If no records are found then new records
     * are inserted based on the timetable_visit entries for this timetable_id.
     */
    function buildVisitsArray($tj)
    {
        global $rtpiconnector;
        $gotsome = false;

        $tvl_tmp = new TimetableVisitLive($rtpiconnector);
        $sql = "select * from timetable_visit_live
                where timetable_id = " . $this->timetable_id . "
                order by fact_id, sequence;";
        $tvls = $tvl_tmp->sqlToInstanceArray($sql, false);
        $visits_found = count($tvls);
        if ($visits_found <= 0)
        {
            $this->log->debug("buildVisitsArray() no visits found in timetable_visit_live for timetable_id " . $this->timetable_id . " - adding");
            if ($this->number_stops_sched <= 0)
            {
                $this->log->error("buildVisitsArray() number_stops_sched <= 0 for timetable_id " . $this->timetable_id . " => empty visits array");
                return false;
            }

            for ($i = 1; $i <= $this->number_stops_sched; $i++)
            {
                $tvl = new TimetableVisitLive($rtpiconnector);
                if (!$tvl->initialise($tj, $this, $i))
                {
                    $this->log->error("buildVisitsArray() Failed to initialise a visit - aborting");
                    return false;
                }
                $tvl->add();

                $this->visits[$tvl->sequence] = $tvl;

                if ($tvl->sequence <= 1)
                    continue;

                $this->visits[$tvl->sequence - 1]->next_visit = $this->visits[$tvl->sequence];
                $this->visits[$tvl->sequence]->prev_visit = $this->visits[$tvl->sequence - 1];
                $this->actual_end = $this->visits[$tvl->sequence]->departure_time;
                $this->maximum_lateness = 0;
                $this->minimum_lateness = 0;
                $this->average_lateness = 0;
                $this->number_stops = $ct;
            }

            return true;
        }

        // If visits were already in timetable_visit_live, then initialise them
        $this->log->debug("buildVisitsArray() loading " . $visits_found . " visits from db for timetable_id " . $this->timetable_id . ", journey fact_id " . $this->fact_id);
        foreach ($tvls as $key => $tvl)
        {
            $this->visits[$tvl->sequence] = $tvl;

            $tv = $tj->visits[$tvl->sequence];
            if (!$tv)
            {
                $this->log->warn("buildVisitsArray() failed to find a timetable_visit for timetable_journey->fact_id " . $tj->fact_id . ", sequence $sequence");
                continue;
            }

            $tvl->initialiseAfterLoad($tv, $this);

            // Set journey stats values
            $this->actual_end = $this->visits[$tvl->sequence]->departure_time;
            $this->maximum_lateness = 0;
            $this->minimum_lateness = 0;
            $this->average_lateness = 0;
            $this->number_stops = $ct;

            // Set next_visit and prev_visit in the linked array
            if ($tvl->sequence > 1)
            {
                $this->visits[$tvl->sequence - 1]->next_visit = $this->visits[$tvl->sequence];
                $this->visits[$tvl->sequence]->prev_visit = $this->visits[$tvl->sequence - 1];
            }
        }
        
        unset($tvls);
        return true;
    }

    /**
     * @brief Show a formatted summary of the object
     */
    function show()
    {
        $this->log->info("TimetableJourneyLive->show() fact_id " . $this->fact_id . ", number_stops_sched " . $this->number_stops_sched);
        $ct = 0;
        foreach ($this->visits as $k => $tjv)
        {   
            if (!isset($tjv))
                $this->log->error("TimetableJourneyLive->show() visit $k in visits array is not set");
            else
            {
                if (!isset($tjv->gps_position))
                    $this->log->error("TimetableJourneyLive->show() visit $k gps_position is not set");

                if (!isset($tjv->timetable_visit))
                    $this->log->error("TimetableJourneyLive->show() visit $k timetable_visit is not set");
            }
            
            break;
        }
    }

    /**
     * $brief Delete the live journey (remove all visits first)
     */
    function deleteJourneyAndVisits()
    {
        $ct = 1;
        foreach ( $this->visits as $k => $tjv )
        {
            if ( $ct == 1 )
                $tjv->delete(array("journey_fact_id"));
            break;
            unset($this->visits[$k]);

            $ct++;
        }

        $this->delete();
        SnapshotBoardStatus::flag_removed_journey($this->connector, $this->timetable_id, $this->vehicle_id);
    }

    /**
     * @brief For each bus stop on the journey which is configured as a bus stop display sign
     * pass through and generate prediction_display entries so the Predictiondistributor
     * can send them to displays
     */
    function generateJourneyPredictionsForSigns()
    {
        $ct = 1;

        // ... but not for journeys that are not active
        if ( $this->trip_status != "A" )
            return;
        
        // Build a wherec lause of locations in the trip...
        $where = "";
        foreach ( $this->visits as $k => $tjv )
        {
            if ( $ct == 1 )
                $where .= $tjv->location_id;
            else
                $where .= ",".$tjv->location_id;
            $ct++;
        }

        // Class for manipulating prediction table
        $prediction = new PredictionDisplay($this->connector);

        $sql = "SELECT tjl.fact_id, tvl.sequence, tvl.location_id, tdp.build_id, tdp.display_type, pd.prediction_id prediction_id,
                arrival_time, departure_time, tj.ext_timetable_id pub_ttb_id, 
                arrival_time_pub, departure_time_pub
                FROM timetable_journey_live tjl
                JOIN timetable_visit_live tvl ON tjl.fact_id = tvl.journey_fact_id
                JOIN timetable_journey tj ON tjl.timetable_id = tj.timetable_id
                JOIN t_display_point tdp ON tdp.location_id = tvl.location_id
                LEFT JOIN prediction_display pd ON pd.journey_fact_id = tvl.journey_fact_id AND pd.sequence = tvl.sequence
                WHERE tvl.location_id IN ( $where ) 
                AND tjl.fact_id = $this->fact_id
                ORDER BY location_id";

        $now = new DateTime();
        $now = $now->format("Y-m-d H:i:s");

        $stmt = $this->connector->executeSQL($sql);
        $prev_location = false;
        while ( $row = $stmt->fetch() )
        {
            // We have fetched a line for each location / build combination, we only set one prediction
            // however we load up the relevant visit with those builds for use when tracking starts
            $seq = $row["sequence"];
            if ( !$this->visits[$seq - 1]->display_points ) 
                $this->visits[$seq - 1]->display_points = array();
            $displayPoint = new TempDisplayPoint();
            $displayPoint->location_id = $row["location_id"];
            $displayPoint->build_id = $row["location_id"];
            $displayPoint->display_type = $row["display_type"];
            $this->visits[$seq - 1]->display_points[] = $displayPoint;

            // Only store one prediction per location
            if ( $prev_location == $row["location_id"] )
                continue;

            // Store the prediction    
            $prediction->prediction_id = $row["prediction_id"];
            $prediction->vehicle_id = $this->vehicle_id;
            $prediction->route_id = $this->route_id;
            $prediction->location_id = $row["location_id"];
            $prediction->journey_fact_id = $this->fact_id;
            $prediction->pub_ttb_id = $row["pub_ttb_id"];
            $prediction->sequence = $row["sequence"];
            $prediction->rtpi_eta_sent = $row["arrival_time"];
            $prediction->rtpi_etd_sent = $row["departure_time"];
            $prediction->pub_eta_sent = $row["arrival_time_pub"];
            $prediction->pub_etd_sent = $row["departure_time_pub"];
            if ( !$prediction->pub_eta_sent ) $prediction->pub_eta_sent = $prediction->rtpi_eta_sent;
            if ( !$prediction->pub_etd_sent ) $prediction->pub_etd_sent = $prediction->rtpi_etd_sent;
            $prediction->time_last_sent = false;
            $prediction->arr_dep_last_sent = false;
            $prediction->sch_rtpi_last_sent = false;
            $prediction->eta_last_sent = false;
            $prediction->etd_last_sent = false;
            $prediction->counted_down = "0";
            $prediction->time_generated = $now;

            // Create or update prediction depending on whether we have one
            if ( $prediction->prediction_id )
            {
                $prediction->save();
            }
            else
            {
                $prediction->prediction_id = 0;
                $prediction->add();
            }
            
            $prev_location = $prediction->location_id;
        }
    }
}

?>
