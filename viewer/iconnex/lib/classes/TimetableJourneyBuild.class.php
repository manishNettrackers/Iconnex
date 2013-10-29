<?php
/**
* TimetableJourneyBuild
*
* Datamodel for table daily_timetable_instance
*
*/

class TimetableJourneyBuild extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "date_id" => new DataModelColumn ( $this->connector, "date_id", "integer" ),
            "created" => new DataModelColumn ( $this->connector, "created", "datetime" ),
            );

        $this->tableName = "daily_timetable_instance";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("date_id");
        parent::__construct($connector);

    }
}
?>
