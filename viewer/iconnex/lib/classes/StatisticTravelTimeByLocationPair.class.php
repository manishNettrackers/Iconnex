<?php

/**
** Class: StatisticTravelTimeByLocationPair
** ------------------------------
**
** A value representing a travel time in a stats store
**
*/

class  StatisticTravelTimeByLocationPair
{
    public $id = 0;
    public $locationFrom;
    public $locationTo;
    public $travelTime;
    public $timestamp;
    public $averageTravelTime = 0;
    public $totalTravelTime = 0;
    public $observations = 0;


    /*
    * Takes a travel time for this travel pair and calculates a new average
    */
    function applyStatisticFromEvent($config, $event)
    {
        $secs = $event->travelTime;
        if (is_string($secs))
            $secs = UtilityDateTime::SecondsToIntervalString($secs);

        // Apply value to summary data                     
        $this->totalTravelTime += $secs;
        $this->observations++;
        $this->averageTravelTime = 
        $this->totalTravelTime / $this->observations;

        // Store statistic calculation
        $config->statsModel->stats_id = $this->id;
        $config->statsModel->config_id = $config->config_id;
        $config->statsModel->loc_from = $this->locationFrom;
        $config->statsModel->loc_to = $this->locationTo;
        $config->statsModel->avg_travel_time = $this->averageTravelTime;
        $config->statsModel->sum_travel_time = $this->totalTravelTime;
        $config->statsModel->sample_size = $this->observations;

        $this->saveStatisticModel($config, $config->statsModel);
    }

    /*
    * Updates the DB with the statistics
    */
    function saveStatisticModel($config, $statsModel)
    {
        // Store statistic calculation
        $statsModel->stats_id = $this->id;
        $statsModel->config_id = $config->config_id;
        $statsModel->loc_from = $this->locationFrom;
        $statsModel->loc_to = $this->locationTo;

        if ( $statsModel->stats_id == 0 )
        {
            $statsModel->load(array("config_id", "loc_from", "loc_to"));
        }

        $statsModel->avg_travel_time = $this->averageTravelTime;
        $statsModel->sum_travel_time = $this->totalTravelTime;
        $statsModel->sample_size = $this->observations;

        if ( $statsModel->stats_id == 0 )
        {
            $statsModel->add();
            $this->id = $statsModel->stats_id;
        }
        else
        {
            $statsModel->save();
        }
    }

    /*
    * Takes a travel time for this travel pair, removes it and calculates a new average
    */
    function unapplyStatisticFromRawModel($config, $model)
    {
        $secs = $model->travel_time;
        if (!is_numeric($secs))
            if (is_string($secs))
                $secs = UtilityDateTime::SecondsToIntervalString($secs);

        // Apply value to summary data                     
        $this->totalTravelTime -= $secs;
        $this->observations--;
        if ( $this->observations )
            $this->averageTravelTime = $this->totalTravelTime / $this->observations;
        else
            $this->averageTravelTime = 0;
        $this->saveStatisticModel($config, $config->statsModel);
    }

    /*
    * Takes a travel time for this travel pair and calculates a new average
    */
    function applyStatisticFromRawModel($config, $model)
    {
        $secs = $model->travel_time;
        if (!is_numeric($secs))
            if (is_string($secs))
                $secs = UtilityDateTime::SecondsToIntervalString($secs);

        // Apply value to summary data                     
        $this->totalTravelTime += $secs;
        $this->observations++;
        $this->averageTravelTime = 
        $this->totalTravelTime / $this->observations;
    }

    /*
    * Generates a statistic from an event
    */
    function FactoryForEvent($event)
    {
        // create pair statistic
        $stat = new StatisticTravelTimeByLocationPair();
        $stat->id = 0;
        $stat->locationFrom = $event->locationFrom;
        $stat->locationTo = $event->locationTo;
        $stat->averageTravelTime = false;
        $stat->totalTravelTime = false;
        $stat->observations = 0;

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
        echo $this->locationFrom." - ";
        echo $this->locationTo.": ";
        echo $this->averageTravelTime." = ";
        echo $this->totalTravelTime." / ";
        echo $this->observations."\n";
    }
}
?>
