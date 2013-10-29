<?php
/**
* VehicleSession
*
* Datamodel for table vehicle_session
*
*/

class VehicleSession extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "vehicle_id" => new DataModelColumn($this->connector, "vehicle_id", "integer", false),
            "session_id" => new DataModelColumn($this->connector, "session_id", "integer")
            );

        $this->tableName = "vehicle_session";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("vehicle_id");
        parent::__construct($connector);
    }
}
?>
