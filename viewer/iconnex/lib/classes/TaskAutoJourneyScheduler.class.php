<?php

/**
** Class: TaskAutoJourneyScheduler
** ------------------------------
**
** Produces a list of all trips that will be started as "AUTO" trips
** that are created in the table auto_trip_schedule. Journeys in this
** table are passed to the Event Handler to start 
** live journeys in timetable_journey_live. If no vehicle tracks this
** trip then they are left as scheduled only live trips and used
** for prediction generation
** 
*/
class TaskAutoJourneyScheduler extends ScheduledTask
{

    /*
    ** runTask
    **
    ** when run as a scheduled task
    ** Generates automatic trip starting schedule
    */
    function runTask()
    {
        // This task requires DMY4/ date format for informix
        //putenv("DBDATE=DMY4");
        //$_ENV["DBDATE"] = "DMY4/";

        // Create the auto_journey_schedule table
        $ajs = new AutoJourneySchedule($this->connector);
        $ajs->dropTable();
        $ajs->createTable();

        // Clear out the auto_journey_schedule table
        if ( !$this->connector->executeSQL ("BEGIN WORK" ) )
            return false;

        $sql = "LOCK TABLE auto_journey_schedule in EXCLUSIVE MODE";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "DELETE FROM auto_journey_schedule";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        if ( !$this->connector->executeSQL ("COMMIT WORK" ) )
            return false;

        // Generate auto trip start schedule for next x days
        $buildDate = new DateTime();
        for ( $ct = 0; $ct < 2; $ct++ )
        {
            if ( !$this->buildAutoJourneySchedule($buildDate) )
                return false;
            $buildDate = $buildDate->Add(new Dateinterval("P1D"));
        }

        return true;

    }


    /*
    ** buildAutoJourneySchedule
    **
    ** Reads Timetable Journey Dimension for todays trips
    ** and build a list of them with times they should be scheduled for starting
    ** in Event Handler
    */
    function buildAutoJourneySchedule( $date )
    {
        $timeutil = new UtilityDateTime();

        $ymd = $date->format("Ymd");
        $dbdate = $this->connector->syntax_datetime_to_db_date($date);

        $buildTime = new DateTime();
        $builddmyhms = $buildTime->format("Y-m-d H:I:s");

        if ( !$this->connector->executeSQL ("BEGIN WORK" ) )
            return false;

        // Create Route Specific Prediction Parameters so we can extract auto trip starting times
        $tps = new TempPredictionServiceParam($this->connector);
        if ( !$tps->buildTable() )
        {
            echo "Route Specific Prediction Parameters Build Failed\n";
            return false;
        }

        echo "Build for ". $date->format("Y-m-d"). "\n";

        $sql = "LOCK TABLE auto_journey_schedule in EXCLUSIVE MODE";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "DELETE FROM auto_journey_schedule WHERE ". $this->connector->syntax_where_date_of_date_time("scheduled_start_time") . " = '$dbdate'";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "SELECT timetable_id, next_timetable_id, start_time, t_prediction_service_param.autort_preempt || '' autort_preempt
                FROM timetable_journey tjd
                JOIN route_param ON route_param.route_id = tjd.route_id
                LEFT JOIN t_prediction_service_param ON t_prediction_service_param.route_id = tjd.route_id
                WHERE tjd.actual_date_id = $ymd";

        $ajs = new AutoJourneySchedule($this->connector);
        $ajs->addPrepare();

        $fetchstmt = $this->connector->executeSQL($sql);

        if ( !$fetchstmt )
            return false;

        $this->rows_affected = 0;

	    // Build new auto_journey_schedule
        while ( $row = $fetchstmt->fetch() )
        {
            $ajs->timetable_id = $row["timetable_id"];
            $ajs->next_timetable_id = $row["next_timetable_id"];
            $startTime = DateTime::createFromFormat("Y-m-d H:i:s", $row["start_time"]);
            $autoStartTime = DateTime::createFromFormat("Y-m-d H:i:s", $row["start_time"]);

            $prempt_interval = $timeutil->HHMMSSToDateInterval($row["autort_preempt"]);
            $autoStartTime->Sub(new DateInterval($prempt_interval));
			$ajs->scheduled_start_time = $startTime->format("Y-m-d H:i:s");
			$ajs->auto_start_time = $autoStartTime->format("Y-m-d H:i:s");

            // Flag trip as started if before now
			$ajs->start_status = 0;
            $now = new DateTime();

            if ( $startTime->getTimeStamp() < $now->getTimeStamp() - 3600 )
            {
			    $ajs->start_status = 1;
            }

            $ajs->addBindPrepareParameters();
            $ajs->add();
            $this->rows_affected++;
        }

        if ( !$this->connector->executeSQL ("COMMIT WORK" ) )
            return false;

    }
}
?>
