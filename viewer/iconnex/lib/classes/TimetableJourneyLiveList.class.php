<?php

class TimetableJourneyLiveList
{
    private $log;

    public $list;

    /* 
    ** How long to keep scheduled journeys for 
    ** journeys held for specified time after actual/predicted 
    ** departure at end stop
    */
    public $scheduledTripKeepTime = 1800;

    /* 
    ** How long to keep realtime journeys for 
    ** journeys held for specified time after actual/predicted 
    ** departure at end stop
    */
    public $realTripKeepTime = 3600;

    /*
    ** Indicates time and frequency of last stale clear run
    */
    public $timeofLastStaleClear;
    public $lastStaleClearFrequency = 20;

    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
        $this->list = array();
    }

    function load($tj_list)
    {
        global $rtpiconnector;

        $tjl_tmp = new TimetableJourneyLive($rtpiconnector);
        $sql = "select * from timetable_journey_live
                order by vehicle_id, start_date_id, start_time_id";
        $tjls = $tjl_tmp->sqlToInstanceArray($sql);

        if (!$tjls)
        {
            $this->log->info("load() no journeys to load in timetable_journey_live");
            return true;
        }

        $ct = 0;
        foreach ($tjls as $tjl)
        {
            $tj = $tj_list->getJourneyByTimetableID($tjl->timetable_id);
            if (!$tj)
            {
                $this->log->error("load() failed to get TimetableJourney for live timetable_id " . $tjl->timetable_id);
                exit;
                continue;
            }
            $tjl->timetable_journey = $tj;
            $vehicle = new Vehicle($rtpiconnector);
            $vehicle->vehicle_id = $tjl->vehicle_id;
            if (!$vehicle->load())
            {
                $this->log->error("load() failed to find vehicle with vehicle_id " . $tjl->vehicle_id);
                exit;
                continue;
            }
            $tjl->vehicle = $vehicle;

            if (!$tjl->buildVisitsArray($tj))
            {
                $this->log->error("load() failed to buildVisitsArray for live journey");
                exit;
                continue;
            }

            $ct++;
            $this->add($tjl);

            $tjl->generateJourneyPredictionsForSigns();
        }

        return true;
    }

    function add($timetable_journey_live)
    {
        $this->list[$timetable_journey_live->fact_id] = $timetable_journey_live;
    }

    function delete($fact_id)
    {
        unset($this->list[$fact_id]);
    }

    function show()
    {
        $this->log->info("show() ... ");
        foreach ($this->list as $key => $value)
        {
            $this->log->info("  fact_id $key");
            $value->show();
        }
    }

    function getMatchingJourney($event_journey_details, $timetable_journey, $vehicle)
    {
        global $rtpiconnector;

        if (!$timetable_journey)
        {
            $this->log->error("getMatchingJourney() timetable_journey passed is not set");
            return false;
        }
        if (!$vehicle)
        {
            $this->log->error("getMatchingJourney() vehicle passed is not set");
            return false;
        }

        foreach ($this->list as $ttb_id => $tjl)
        {
            if (!$tjl->vehicle)
            {
                $this->log->warn("getMatchingJourney() vehicle not set in live journey (fact_id " . $tjl->fact_id . ") - skipping");
                continue;
            }
            if ($tjl->timetable_id == $timetable_journey->timetable_id
            && $tjl->vehicle->vehicle_id == $vehicle->vehicle_id)
                return $tjl;
        }

        $timetable_journey_live = new TimetableJourneyLive($rtpiconnector);
        $timetable_journey_live->timetable_id = $timetable_journey->timetable_id;
        if (!$timetable_journey_live->load(array("timetable_id")))
        {
            if (!$timetable_journey_live->initialise($event_journey_details, $timetable_journey, $vehicle))
            {
                $this->log->error("getMatchingJourney() failed to initialise timetable_journey_live for event");
                return false;
            }

            $timetable_journey_live->add();
        }
        else
        {
            $timetable_journey_live->vehicle = $vehicle;
            $timetable_journey_live->timetable_journey = $timetable_journey;
        }

        if (!$timetable_journey_live->buildVisitsArray($timetable_journey))
        {
            $this->log->error("getMatchingJourney() failed buildVisitsArray for timetable_journey_live - not adding to list");
            return false;
        }

        $timetable_journey_live->generateJourneyPredictionsForSigns();

        $this->add($timetable_journey_live);

        return $timetable_journey_live;
    }

    /**
     * @brief Get the first REAL journey in the list for a given vehicle_id
     */
    function getJourneyForVehicleID($vehicle_id)
    {
        if (!$vehicle_id)
            return false;

        foreach ($this->list as $ttb_id => $tjl)
        {
            if ($tjl->vehicle_id = $vehicle_id)
            {
                if ($tjl->start_code == "REAL")
                    return $tjl;
            }
        }

        return false;
    }

    /**
     * @brief Clear out stale AUT and real time journeys
     * from the list and from the database
     *
     * TODO: Real Time vehicles
     */
    function clearStaleJourneys()
    {
        // Only clear stale journeys every x seconds
        $now = UtilityDateTime::currentTimestamp();
        if ( $now - $this->timeofLastStaleClear < $this->lastStaleClearFrequency )
        {
            return;
        }
        $this->timeofLastStaleClear = $now;

        $ct = 0;
        foreach ($this->list as $ttb_id => $tjl)
        {
            // For scheduled vehicles, remove from list and database
            // if predicted end time is less than current time
            // less scheduled trip keep time
            $end = DateTime::createFromFormat("Y-m-d H:i:s", $tjl->actual_end);
            $timeSinceJourneyEnd = $now - $end->getTimestamp();

            if ( $timeSinceJourneyEnd > $this->scheduledTripKeepTime )
            {
                $tjl->deleteJourneyAndVisits();
                $ct++;
                unset($this->list[$ttb_id]);
            }
        }
        gc_collect_cycles();

	    // Also clear out any journeys that are very old if missed by event handler
        global $rtpiconnector;
        $sql = "DROP TABLE t_journeys_del";
        $stmt = $rtpiconnector->executeSQL( $sql, "CONTINUE" );

        $sql = "CREATE TEMP TABLE t_journeys_del ( fact_id INTEGER ) WITH NO LOG";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;

        $sql = "INSERT INTO t_journeys_del SELECT fact_id FROM timetable_journey_live WHERE actual_start < CURRENT - 5 UNITS HOUR";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
//        $rtpiconnector->trace("PPP Built journeys to clear out");

        $sql = "DELETE FROM timetable_visit_live WHERE journey_fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
//        $rtpiconnector->trace("PPP Deleted Matching Timetable Live Visit");

        $sql = "DELETE FROM timetable_journey_live WHERE fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
//        $rtpiconnector->trace("PPP Deleted Matching Timetable Live Journeys");

        // Also clear out any journeys that are very old if missed by event handler
        global $rtpiconnector;
        $sql = "DROP TABLE t_journeys_del";
        $stmt = $rtpiconnector->executeSQL( $sql, "CONTINUE" );

        $sql = "CREATE TEMP TABLE t_journeys_del ( fact_id INTEGER ) WITH NO LOG";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;

        $sql = "INSERT INTO t_journeys_del SELECT fact_id FROM timetable_journey_live WHERE actual_start < CURRENT - 5 UNITS HOUR";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Built journeys to clear out");

        $sql = "DELETE FROM timetable_visit_live WHERE journey_fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Deleted Matching Timetable Live Visit");

        $sql = "DELETE FROM timetable_journey_live WHERE fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Deleted Matching Timetable Live Journeys");

        // Also clear out any journeys that are very old if missed by event handler
        global $rtpiconnector;
        $sql = "DROP TABLE t_journeys_del";
        $stmt = $rtpiconnector->executeSQL( $sql, "CONTINUE" );

        $sql = "CREATE TEMP TABLE t_journeys_del ( fact_id INTEGER ) WITH NO LOG";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;

        $sql = "INSERT INTO t_journeys_del SELECT fact_id FROM timetable_journey_live WHERE actual_start < CURRENT - 5 UNITS HOUR";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Built journeys to clear out");

        $sql = "DELETE FROM timetable_visit_live WHERE journey_fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Deleted Matching Timetable Live Visit");

        $sql = "DELETE FROM timetable_journey_live WHERE fact_id in (SELECT fact_id FROM t_journeys_del)";
        if ( !($stmt = $rtpiconnector->executeSQL( $sql )) ) return false;
        $rtpiconnector->trace("Deleted Matching Timetable Live Journeys");

        return false;
    }
}

?>
