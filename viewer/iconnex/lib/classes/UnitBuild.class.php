<?php

require_once("DataModel.class.php");

class UnitBuild extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array(
            "build_id" => new DataModelColumn($this->connector, "build_id", "serial"),
            "operator_id" => new DataModelColumn($this->connector, "operator_id", "integer", false),
            "build_code" => new DataModelColumn($this->connector, "build_code", "char", 10, false),
            "unit_type" => new DataModelColumn($this->connector, "unit_type", "char", 8, false),
            "description" => new DataModelColumn($this->connector, "description", "char", 20),
            "build_parent" => new DataModelColumn($this->connector, "build_parent", "integer"),
            "build_status" => new DataModelColumn($this->connector, "build_status", "char", 1),
            "version_id" => new DataModelColumn($this->connector, "version_id", "integer"),
            "build_notes1" => new DataModelColumn($this->connector, "build_notes1", "char", 40),
            "build_notes2" => new DataModelColumn($this->connector, "build_notes2", "char", 40),
            "build_type" => new DataModelColumn($this->connector, "build_type", "char", 1),
            "allow_logs" => new DataModelColumn($this->connector, "allow_logs", "smallint"),
            "allow_publish" => new DataModelColumn($this->connector, "allow_publish", "smallint")
            );
        $this->tableName = "unit_build";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("build_id");

        parent::__construct($connector, $initialiserArray);
    }
}

?>
