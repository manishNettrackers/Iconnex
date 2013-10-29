<?php

/**
** Class: StatisticsTravelTimesGenerator
** ------------------------------
**
** Generates averages of travel times between bus stops.
** Stores this in stats_travel_times
**
*/

class StatisticsTravelTimesGenerator extends StatisticsGenerator
{
    public $statisticType = "UNKNOWN";

    /*
    ** caclulate
    **
    */
    function calculate()
    {
        echo "Calculate\n";
    }

    /**
    * Create a hardcoded set of travel time stats/config tables for 
    * assembling various stats around a particular theme
    */
    function build()
    {
        parent::build();

        $config = new StatsConfig($this->connector);
        $config->metric_type = "TravelAverage";
        $config->period_type = "30days";
        $config->config_id = 0;
        $config->stale_after = UtilityDateTime::SecondsToIntervalString(3600 * 24 * 30);
        $config->period_from = false;
        $config->period_to = false;
        $config->statisticClass = "StatisticTravelTimeByLocationPair";
        $config->maintain_raw = true;
        $config->rawData = &$this->rawData;
        $config->rawStatisticClass = "StatisticRawTravelTimeByLocationPair";
        $config->datamodel = new StatsTravelAverageXXX($this->connnector);

        $rule = new StatsRule($this->connector);
        $rule->time_period_from = "00:00:00";
        $rule->time_period_to = "02:00:00";
        $config->rules[] = $rule;

        $config->createConfigAndRules();

        $this->configs[] = $config;

        $this->buildDatasetFromDatabase();
    }
}
?>
