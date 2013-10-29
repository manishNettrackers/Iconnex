<?php

/**
** Class: EventStatisticTravelTime
** ------------------------------
**
** An event represeanting a travel time calculation
**
*/

class EventStatisticTravelTime extends EventStatistic
{
    public $locationFrom;
    public $locationTo;
    public $travelTime;
    public $timestamp;
}
?>
