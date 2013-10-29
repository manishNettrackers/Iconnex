<?php
/**
 * StatsRules
 *
 * Datamodel for table stats_rules
 */

class StatsRule extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "config_id" => new DataModelColumn($this->connector, "config_id", "integer"),
            "rule_id" => new DataModelColumn($this->connector, "rule_id", "serial"),
            "date_period_from" => new DataModelColumn($this->connector, "date_period_from", "datetime"),
            "date_period_to" => new DataModelColumn($this->connector, "date_period_to", "datetime"),
            "time_period_from" => new DataModelColumn($this->connector, "time_period_from", "datetimehourtosecond"),
            "time_period_to" => new DataModelColumn($this->connector, "time_period_to", "datetimehourtosecond"),
            "dow_from" => new DataModelColumn($this->connector, "dow_from", "integer"),
            "dow_to" => new DataModelColumn($this->connector, "dow_to", "integer"),
            "bank_holiday" => new DataModelColumn($this->connector, "bank_holiday", "integer"),
            );

        $this->tableName = "stats_rule";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("rule_id");
        parent::__construct($connector);
    }
}
?>
