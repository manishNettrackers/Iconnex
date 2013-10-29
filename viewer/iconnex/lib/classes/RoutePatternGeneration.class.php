<?php
/**
* RoutePatternGeneration
*
* Datamodel for table route_import_status
*
*/

class RoutePatternGeneration extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "route_id" => new DataModelColumn ( $this->connector, "route_id", "integer" ),
            "generate_time" => new DataModelColumn ( $this->connector, "generate_time", "datetime" ),
            );

        $this->tableName = "route_pattern_generation";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("route_id");
        parent::__construct($connector);

    }
}
?>
