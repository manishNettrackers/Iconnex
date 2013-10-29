<?php

class SnapshotVehicleStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array ( 
            "vehicle_id" => new DataModelColumn ( $this->connector,  "vehicle_id", "integer" ),
            "message_time" => new DataModelColumn ( $this->connector,  "message_time", "datetime" ),
            "gpslat" => new DataModelColumn ( $this->connector,  "gpslat", "decimal" ),
            "gpslong" => new DataModelColumn ( $this->connector,  "gpslong", "decimal" ),  
            "vehicle_status" => new DataModelColumn ( $this->connector,  "vehicle_status", "char", 20 ),
            "message_type" => new DataModelColumn ( $this->connector,  "message_type", "char", 20 ), 
            "route_status" => new DataModelColumn ( $this->connector,  "route_status", "char", 20 ),
            "row_changed" => new DataModelColumn ( $this->connector,  "row_changed", "datetime" ),
            "row_status" => new DataModelColumn ( $this->connector,  "row_status", "char", "10" )
            );

        $this->tableName = "snapshot_vehicle_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array ( "vehicle_id" );

        parent::__construct($connector);
    }
}

?>
