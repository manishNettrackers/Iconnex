<?php

/**
** Class: TaskStatisticsTravelTimes
** --------------------------------
**
** Runs the ad hoc statistics generator for all travel times
*/

class TaskStatisticsTravelTimes extends TaskStatisticsGenerator
{
     const DEFAULT_QUEUE_ID = 3005;
     const DEFAULT_QUEUE_NAME = "QSTATTT";

    function __construct($connector = false)
    {   
        parent::__construct($connector);
        $this->inboundQueue = false;
        $this->generator = false;
        $this->queueName = TaskStatisticsTravelTimes::DEFAULT_QUEUE_NAME;
        $this->defaultQueueId = TaskStatisticsTravelTimes::DEFAULT_QUEUE_ID;
        $this->generatorClass = "StatisticsTravelTimesGenerator";
    }

}
?>
