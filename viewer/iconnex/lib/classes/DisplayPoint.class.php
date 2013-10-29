<?php

require_once("DataModel.class.php");

class DisplayPoint extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer" ),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "display_type" => new DataModelColumn ( $this->connector,  "display_type", "char", 1 ),
            "filename" => new DataModelColumn ( $this->connector,  "filename", "char", 15 ),
            "display_mode" => new DataModelColumn ( $this->connector,  "display_mode", "char", 1 ),
            "delivery_mode" => new DataModelColumn ( $this->connector,  "delivery_mode", "char", 5 ),
            "disabled" => new DataModelColumn ( $this->connector,  "disabled", "char", 1 ),
            );
        $this->tableName = "display_point";
        $this->dbspace = "centdbs";
        $this->keyColumns = array(...);
        

        parent::__construct($connector);
    }
}

?>

