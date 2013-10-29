<?php

/**
** Class: StatisticRawTravelTimeByLocationPair
** ------------------------------
**
** A value representing a travel time in a stats store
**
*/

class  StatisticRawTravelTimeByLocationPair
{
    public $id = 0;
    public $locationFrom;
    public $locationTo;
    public $travelTime;
    public $timestamp;

    /*
    * Takes a travel time for this travel pair and adds its to the list
    */
    function applyStatisticFromEvent($config, $event)
    {
        $secs = $event->travelTime;
        if (is_string($secs))
            $secs = UtilityDateTime::SecondsToIntervalString($secs);
        $timestamp = UtilityDateTime::TimestampObjectToString($event->timestamp);

        // Store statistic calculation
        $config->rawModel->stats_id = 0;
        $config->rawModel->config_id = $config->config_id;
        $config->rawModel->loc_from = $this->locationFrom;
        $config->rawModel->loc_to = $this->locationTo;
        $config->rawModel->timestamp = $this->timestamp;
        $config->rawModel->travel_time = $this->travelTime;

        $config->rawModel->add();
        $this->id = $config->rawModel->stats_id;
    }

    /*
    * stores this raw statistic in the database
    */
    function applyToDatabase($config)
    {
        $timestamp = UtilityDateTime::TimestampObjectToString($event->timestamp);
        $this->travelTime += $secs;

        // Store statistic calculation
        $config->rawModel->stats_id = 0;
        $config->rawModel->config_id = $config->config_id;
        $config->rawModel->loc_from = $this->locationFrom;
        $config->rawModel->loc_to = $this->locationTo;
        $config->rawModel->timestamp = $this->timestamp;
        $config->rawModel->travel_time = $this->travelTime;

        $config->rawModel->add();
        $this->id = $config->rawModel->stats_id;
    }

    /*
    * stores this raw statistic in the database
    */
    function unapplyStatistic($config, $key)
    {
        // Store statistic calculation
        $config->rawModel->stats_id = $this->id;
        $config->rawModel->config_id = $config->config_id;
        $config->rawModel->loc_from = $this->locationFrom;
        $config->rawModel->loc_to = $this->locationTo;
        $config->rawModel->timestamp = $this->timestamp;
        $config->rawModel->travel_time = $this->travelTime;

        $this->id = $config->rawModel->stats_id;
        $config->statistics[$key]["stats"]->unapplyStatisticFromRawModel($config, $rawModel);
        $config->rawModel->delete();
    }

    /*
    * Generates a statistic from an event
    */
    function FactoryForEvent($event)
    {
        $secs = $event->travelTime;
        if (is_string($secs))
            $secs = UtilityDateTime::SecondsToIntervalString($secs);

        // create pair statistic
        $stat = new StatisticRawTravelTimeByLocationPair();
        $stat->id = 0;
        $stat->locationFrom = $event->locationFrom;
        $stat->locationTo = $event->locationTo;
        $stat->travelTime = $secs;
        $stat->timestamp = UtilityDateTime::TimestampObjectToString($event->timestamp);

        // If location codes specified convert them to ids
        if ( is_string($event->locationFrom) )
        {
            $locFrom = DataModel::rowFactory($this->connector, "Location", array("location_code" => $event->locationFrom));
            if ( $locFrom ) $stat->locationFrom = $locFrom->location_id;
        }

        // If location codes specified convert them to ids
        if ( is_string($event->locationTo) )
        {
            $locTo = DataModel::rowFactory($this->connector, "Location", array("location_code" => $event->locationTo));
            if ( $locTo ) $stat->locationTo = $locFrom->location_id;
        }
        return $stat;
    }

    /*
    * shows the contents of the statistic
    */
    function show()
    {
        echo $this->id." ";
        echo $this->timestamp.": ";
        echo $this->locationFrom." - ";
        echo $this->locationTo.": ";
        echo $this->travelTime;
        echo "\n";
    }
}
?>
