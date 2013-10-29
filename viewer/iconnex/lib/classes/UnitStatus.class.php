<?php

require_once("DataModel.class.php");

class UnitStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array(
            "build_id" => new DataModelColumn($this->connector, "build_id", "integer", false, "not null"),
            "ip_address" => new DataModelColumn($this->connector, "ip_address", "char", 20),
            "conn_status" => new DataModelColumn($this->connector, "conn_status", "char", 1),
            "message_time" => new DataModelColumn($this->connector, "message_time", "datetime"),
            "message_type" => new DataModelColumn($this->connector, "message_type", "integer"),
            "gps_time" => new DataModelColumn($this->connector, "gps_time", "datetime"),
            "gpslat" => new DataModelColumn($this->connector, "gpslat", "decimal(8,6)"),
            "gpslong" => new DataModelColumn($this->connector, "gpslong", "decimal(9,6)"),
            "gpslat_str" => new DataModelColumn($this->connector, "gpslat_str", "char", 14),
            "gpslong_str" => new DataModelColumn($this->connector, "gpslong_str", "char", 15),
            "gps_dup_ct" => new DataModelColumn($this->connector, "gps_dup_ct", "integer"),
            "soft_ver" => new DataModelColumn($this->connector, "soft_ver", "char", 16),
            "sim_no" => new DataModelColumn($this->connector, "sim_no", "char", "20")
            );
        $this->tableName = "unit_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("build_id");

        parent::__construct($connector);
    }
}

?>
