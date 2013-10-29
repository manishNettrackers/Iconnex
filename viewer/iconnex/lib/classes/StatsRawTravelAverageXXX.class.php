<?php
/**
 * StatsRawTravelAverageXXX
 *
 * Datamodel for table stats_travel_avg
 */

class StatsRawTravelAverageXXX extends StatsTable
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "stats_id" => new DataModelColumn($this->connector, "stats_id", "serial"),
            "config_id" => new DataModelColumn($this->connector, "config_id", "integer"),
            "rule_id" => new DataModelColumn($this->connector, "rule_id", "integer"),
            "loc_from" => new DataModelColumn($this->connector, "loc_from", "integer"),
            "loc_to" => new DataModelColumn($this->connector, "loc_to", "integer"),
            "timestamp" => new DataModelColumn($this->connector, "timestamp", "datetime"),
            "travel_time" => new DataModelColumn($this->connector, "travel_time", "integer"),
            );

        $this->tableName = "stats_raw_travel_avg_XXXX";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("stats_id");
        parent::__construct($connector);
    }

    /*
    * Load raw travel times into memory store
    */
    function applyToInMemoryStatisticConfigs($config)
    {
        if ( ! $this->travel_time )
            return;

        $data = new StatisticRawTravelTimeByLocationPair();
        $data->id = $this->stats_id;
        $data->locationFrom = $this->loc_from;
        $data->locationTo = $this->loc_to;
        $data->timestamp = $this->timestamp;
        $data->travelTime = $this->travel_time;
        $key = $this->loc_from. "-". $this->loc_to;
        if ( !isset($config->statistics[$key]) )
        {
            // create new key entry
            $config->statistics[$key] = array();
            $config->statistics[$key]["data"] = array();
            $config->statistics[$key]["stats"] = new StatisticTravelTimeByLocationPair();
            $config->statistics[$key]["stats"]->locationFrom = $this->loc_from;
            $config->statistics[$key]["stats"]->locationTo = $this->loc_to;
            $config->statistics[$key]["stats"]->timestamp = $this->timestamp;
            $key = $this->loc_from. "-". $this->loc_to;
        }
        $config->statistics[$key]["data"][] = $data;
        $config->statistics[$key]["stats"]->applyStatisticFromRawModel($config, $this);
    }

}
?>
