<?php
/**
* Route
*
* Datamodel for table route
*
*/

class Route extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "route_id" => new DataModelColumn($this->connector, "route_id", "serial"),
            "route_code" => new DataModelColumn($this->connector, "route_code", "char", 20),
            "operator_id" => new DataModelColumn($this->connector, "operator_id", "integer" , false),
            "description" => new DataModelColumn($this->connector, "description", "char", 30),
            "outbound_desc" => new DataModelColumn($this->connector, "outbound_desc", "char", 40),
            "inbound_desc" => new DataModelColumn($this->connector, "inbound_desc", "char", 40),
            );

        $this->tableName = "route";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("route_id");
        parent::__construct($connector);
    }
}
?>
