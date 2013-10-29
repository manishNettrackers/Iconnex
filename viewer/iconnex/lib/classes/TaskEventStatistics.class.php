<?php

/**
** Class: TaskEventStatistics
** --------------------------------
**
** Receives a statistics event from external sources ( DAIP / UDP messsages etc )
** and populates statistics stores
*/

define("EVENT_MESSAGE", 1);


class TaskEventStatistics extends ScheduledTask
{
    private $inboundQueue = false;
    private $desired_msg_type = EVENT_MESSAGE;
    private $received_msg_type = false;
    private $max_msg_size = 4096;
    private $msg = false;

    private $generator = false;

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        // Prepare Connection
        $this->connector->setDirtyRead();
        $this->connector->executeSQL("SET LOCK MODE TO WAIT 10");

        /*
        ** Run Statistics Jobs
        */
        $this->calculate();

    }

    /*
     * @brief Loop forever reading events from the message queue and processing them
     */
    function calculate()
    {
        $generator = new StatisticsTravelTimesGenerator($this->connector);
        $generator->build();
    }
}
?>
