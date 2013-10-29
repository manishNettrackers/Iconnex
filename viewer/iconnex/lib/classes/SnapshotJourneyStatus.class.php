<?php

class SnapshotJourneyStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array ( 
            "vehicle_id" => new DataModelColumn ( $this->connector,  "vehicle_id", "integer" ),
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer" ),
            "service_id" => new DataModelColumn ( $this->connector,  "service_id",  "integer" ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "fact_id" => new DataModelColumn ( $this->connector,  "fact_id", "integer" ),
            "location_id" => new DataModelColumn ( $this->connector,  "location_id",  "integer" ),
            "rpat_orderby" => new DataModelColumn ( $this->connector,  "rpat_orderby", "integer" ),
            "arrival_status" => new DataModelColumn ( $this->connector,  "arrival_status", "char", 1 ),
            "departure_status" => new DataModelColumn ( $this->connector,  "deaprture_status", "char", 1 ),
            "arrival_time" => new DataModelColumn ( $this->connector,  "arrival_time", "datetime" ),
            "departure_time" => new DataModelColumn ( $this->connector,  "departure_time","datetime" ),
            "departure_time_pub" => new DataModelColumn ( $this->connector,  "departure_time_pub","datetime" ),
            "lateness" => new DataModelColumn ( $this->connector,  "lateness", "integer" ),
            "service_code" => new DataModelColumn ( $this->connector,  "service_code", "char", 20 ),
            "route_code" => new DataModelColumn ( $this->connector,  "route_code", "char", 20 ),
            "running_no" => new DataModelColumn ( $this->connector,  "running_no", "char", 20 ),
            "duty_no" => new DataModelColumn ( $this->connector,  "duty_no", "char", 20 ),
            "trip_no" => new DataModelColumn ( $this->connector,  "trip_no", "char", 20 ),
            "operator_code" => new DataModelColumn ( $this->connector,  "operator_code", "char", 20 ),
            "start_code" => new DataModelColumn ( $this->connector,  "start_code", "char", 20 ),
            "trip_status" => new DataModelColumn ( $this->connector,  "trip_status", "char", 20 ),
            "curr_latitude" => new DataModelColumn ( $this->connector,  "curr_latitude", "decimal", "8,6" ),
            "curr_longitude" => new DataModelColumn ( $this->connector,  "curr_longitude", "decimal", "9,6" ),
            "prev_latitude" => new DataModelColumn ( $this->connector,  "prev_latitude", "decimal", "8,6" ),
            "prev_longitude" => new DataModelColumn ( $this->connector,  "prev_longitude", "decimal", "9,6" ),
            "next_latitude" => new DataModelColumn ( $this->connector,  "next_latitude", "decimal", "8,6" ),
            "next_longitude" => new DataModelColumn ( $this->connector,  "next_longitude", "decimal", "9,6" ),
            "next_location" => new DataModelColumn ( $this->connector,  "next_location", "char", 20 ),
            "next_name" => new DataModelColumn ( $this->connector,  "next_name", "char", 60 ),
            "employee_id" => new DataModelColumn ( $this->connector,  "employee_id", "integer" ),
            "employee_code" => new DataModelColumn ( $this->connector,  "employee_code", "char", 20 ),
            "employee_name" => new DataModelColumn ( $this->connector,  "employee_name", "char", 30 ),
            "next_departure" => new DataModelColumn ( $this->connector,  "next_departure","datetime" ),
            "next_departure_time_pub" => new DataModelColumn ( $this->connector,  "next_departure_time_pub","datetime" ),
            "next_lateness" => new DataModelColumn ( $this->connector,  "next_lateness", "integer" ),
            "next_rpat" => new DataModelColumn ( $this->connector,  "next_rpat", "integer" ),
            "start_status" => new DataModelColumn ( $this->connector,  "start_status", "char", 20 ),
            "next_stop_bearing" => new DataModelColumn ( $this->connector,  "next_stop_bearing", "integer" ),
            "row_changed" => new DataModelColumn ( $this->connector,  "row_changed", "datetime" ),
            "row_status" => new DataModelColumn ( $this->connector,  "row_status", "char", "10" )
            );

        $this->tableName = "snapshot_journey_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array ( "vehicle_id", "fact_id" );

        parent::__construct($connector);
    }
}

?>
