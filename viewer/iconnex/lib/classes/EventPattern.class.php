<?php
/**
* EventPattern
*
* Datamodel for table event_pattern
*
*/

class EventPattern extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "evprf_id" => new DataModelColumn ( $this->connector, "evprf_id", "integer" , false, false ),
            "event_id" => new DataModelColumn ( $this->connector, "event_id", "integer" , false, false ),
            "operational" => new DataModelColumn ( $this->connector, "operational", "char", 1, false, false ),
            "org_id" => new DataModelColumn ( $this->connector, "org_id", "integer" ),
            "org_working_holiday" => new DataModelColumn ( $this->connector, "org_working_holiday", "char", 1 ),
            );

        $this->tableName = "event_pattern";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("evprf_id", "event_id");
        parent::__construct($connector);


    }
}
?>
