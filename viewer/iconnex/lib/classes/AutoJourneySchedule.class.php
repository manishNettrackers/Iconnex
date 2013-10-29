<?php

class AutoJourneySchedule extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array ( 
            "timetable_id" => new DataModelColumn ( $this->connector,  "timetable_id", "integer" ),
            "next_timetable_id" => new DataModelColumn ( $this->connector,  "next_timetable_id", "integer" ),
            "scheduled_start_time" => new DataModelColumn ( $this->connector,  "scheduled_start_time", "datetime" ),
            "auto_start_time" => new DataModelColumn ( $this->connector,  "auto_start_time", "datetime" ),
            "start_status" => new DataModelColumn ( $this->connector,  "start_status", "smallint" ),
            //"operationDate" => new DataModelColumn ( $this->connector,  "operation_date", "date" ),
            );

        $this->tableName = "auto_journey_schedule";
        $this->dbspace = "centdbs";
        $this->className = "AutoJourneySchedule";
        $this->keyColumns = array ( "timetable_id" );

        parent::__construct($connector);
    }

    function runTask()
    {
        echo "Task $this->className has no runTask method defined.";
    }

    function createIndexes()
    {
        $sql = "CREATE INDEX ix_autort_sched_ttb on $this->tableName ( timetable_id );";
        $ret = $this->connector->executeSQL($sql);

        return $ret;
    }
}

?>
