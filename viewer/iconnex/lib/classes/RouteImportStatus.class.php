<?php
/**
* RouteImportStatus
*
* Datamodel for table route_import_status
*
*/

class RouteImportStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "import_id" => new DataModelColumn ( $this->connector, "import_id", "integer" ),
            "route_id" => new DataModelColumn ( $this->connector, "route_id", "integer" ),
            "import_time" => new DataModelColumn ( $this->connector, "import_time", "datetime" ),
            );

        $this->tableName = "route_import_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("import_id", "route_id");
        parent::__construct($connector);

    }
}
?>
