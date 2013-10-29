<?php
/**
 * StatsTravelAverageXXX
 *
 * Datamodel for table stats_travel_avg
 */

class StatsTravelAverageXXX extends StatsTable
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "stats_id" => new DataModelColumn($this->connector, "stats_id", "serial"),
            "config_id" => new DataModelColumn($this->connector, "config_id", "integer"),
            "rule_id" => new DataModelColumn($this->connector, "rule_id", "integer"),
            "loc_from" => new DataModelColumn($this->connector, "loc_from", "integer"),
            "loc_to" => new DataModelColumn($this->connector, "loc_to", "integer"),
            "avg_travel_time" => new DataModelColumn($this->connector, "avg_travel_time", "decimal"),
            "sum_travel_time" => new DataModelColumn($this->connector, "sum_travel_time", "integer"),
            "sample_size" => new DataModelColumn($this->connector, "sample_size", "integer"),
            );

        $this->tableName = "stats_travel_avg_XXXX";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("stats_id");
        parent::__construct($connector);
    }

}
?>
