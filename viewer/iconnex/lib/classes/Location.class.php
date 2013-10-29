<?php
/**
* Location
*
* Datamodel for table location
*
*/

class Location extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "location_id" => new DataModelColumn ( $this->connector, "location_id", "serial" ),
            "location_code" => new DataModelColumn ( $this->connector, "location_code", "char", 12 , false ),
            "gprs_xmit_code" => new DataModelColumn ( $this->connector, "gprs_xmit_code", "smallint" ),
            "point_type" => new DataModelColumn ( $this->connector, "point_type", "char", 1 , false ),
            "route_area_id" => new DataModelColumn ( $this->connector, "route_area_id", "integer" , false, false ),
            "description" => new DataModelColumn ( $this->connector, "description", "char", 40 , false ),
            "public_name" => new DataModelColumn ( $this->connector, "public_name", "char", 50 ),
            "receive" => new DataModelColumn ( $this->connector, "receive", "smallint" ),
            "latitude_degrees" => new DataModelColumn ( $this->connector, "latitude_degrees", "smallint" ),
            "latitude_minutes" => new DataModelColumn ( $this->connector, "latitude_minutes", "decimal(8,4)" ),
            "latitude_heading" => new DataModelColumn ( $this->connector, "latitude_heading", "char", 1 ),
            "longitude_degrees" => new DataModelColumn ( $this->connector, "longitude_degrees", "smallint" ),
            "longitude_minutes" => new DataModelColumn ( $this->connector, "longitude_minutes", "decimal(8,4)" ),
            "longitude_heading" => new DataModelColumn ( $this->connector, "longitude_heading", "char", 1 ),
            "geofence_radius" => new DataModelColumn ( $this->connector, "geofence_radius", "decimal(10,2)" ),
            "pass_angle" => new DataModelColumn ( $this->connector, "pass_angle", "smallint" ),
            "gazetteer_code" => new DataModelColumn ( $this->connector, "gazetteer_code", "char", 1 ),
            "gazetteer_id" => new DataModelColumn ( $this->connector, "gazetteer_id", "char", 8 ),
            "place_id" => new DataModelColumn ( $this->connector, "place_id", "integer" ),
            "district_id" => new DataModelColumn ( $this->connector, "district_id", "integer" ),
            "arriving_addon" => new DataModelColumn ( $this->connector, "arriving_addon", "integer" ),
            "exit_addon" => new DataModelColumn ( $this->connector, "exit_addon", "integer" ),
            "bay_no" => new DataModelColumn ( $this->connector, "bay_no", "char", 8 ),
            "bearing" => new DataModelColumn ( $this->connector, "bearing", "decimal(16)" ),
            );

        $this->tableName = "location";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("location_id");
        parent::__construct($connector, $initialiserArray);
    }
}
?>
