<?php

/**
** Class: EventStatistic
** ---------------------
**
** An event represeanting a statistics event which causes a modification of
** statistic stores in memory and in database
**
*/

class EventStatistic
{
    function process()
    {
        // Key if location from and to pairs 
        $key = $this->locationFrom."-".$this->locationTo;

        foreach ( $this->event_handler->generator->configs as $config )
        {
            $config->handleStatisticEvent($key, $this);
        }
    }
}
?>
