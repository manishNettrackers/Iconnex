<?php

class SnapshotStopStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array ( 
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer" ),
            "location_code" => new DataModelColumn ( $this->connector,  "location_code", "char", 12 ),
            "bay_no" => new DataModelColumn ( $this->connector,  "bay_no", "char", 12 ),
            "description" => new DataModelColumn ( $this->connector,  "description", "char", 50 ),
            "route_area_code" => new DataModelColumn ( $this->connector,  "route_area_code", "char", 20 ),
            "latitude" => new DataModelColumn ( $this->connector,  "latitude", "decimal", "8,6" ),
            "longitude" => new DataModelColumn ( $this->connector,  "longitude", "decimal", "9,6" ),
            "build_code" => new DataModelColumn ( $this->connector,  "build_code", "char", 10 ),
            "message_time" => new DataModelColumn ( $this->connector,  "message_time", "datetime" ),
            "ip_address" => new DataModelColumn ( $this->connector,  "ip_address", "char", 20 ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "route_code" => new DataModelColumn ( $this->connector,  "route_code", "char", 10 ),
            "make" => new DataModelColumn ( $this->connector,  "make", "char", 30 ),
            "last_impact" => new DataModelColumn ( $this->connector,  "last_impact", "datetime" ),
            "impact_count" => new DataModelColumn ( $this->connector,  "impact_count", "integer" ),
            "last_bootup" => new DataModelColumn ( $this->connector,  "last_bootup", "datetime" ),
            "bootup_count" => new DataModelColumn ( $this->connector,  "bootup_count", "integer" ),
            "last_active_hour" => new DataModelColumn ( $this->connector,  "last_active_hour", "integer" ),
            "last_active_day" => new DataModelColumn ( $this->connector,  "last_active_day", "integer" ),
            "operator_code" => new DataModelColumn ( $this->connector,  "operator_code", "char", 20 ),
            "routes" => new DataModelColumn ( $this->connector,  "routes", "varchar", 255 ),
            "bearing" => new DataModelColumn ( $this->connector,  "bearing", "char", 5 ),
            "row_changed" => new DataModelColumn ( $this->connector,  "row_changed", "datetime" ),
            "row_status" => new DataModelColumn ( $this->connector,  "row_status", "char", "10" )
            );

        $this->tableName = "snapshot_stop_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array ( "location_id", "route_id" );

        parent::__construct($connector);
    }

    function createIndexes()
    {
        $sql = "CREATE INDEX ix_snapshot_stop on $this->tableName ( location_code );";
        $ret = $this->connector->executeSQL($sql);
        return $ret;
    }
}

?>
