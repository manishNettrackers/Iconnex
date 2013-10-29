<?php

require_once("Event.class.php");

class EventStats extends Event
{
    function process()
    {
        $this->active_item = $this->event_handler->active_list->show();
        parent::process();
    }
}

?>
