<?php

require_once("Event.class.php");

class EventLogOff extends EventUnit
{
    function process()
    {
        global $rtpiconnector;
        $rtpiconnector->executeSQL("BEGIN WORK");

        $log_time = new LogTime($rtpiconnector);
        $log_time->logByEvent($this);

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {
            echo "EventLogOff->process() failed to get_active_item - cannot process event\n";
            return;
        }

        parent::process();

        $rtpiconnector->executeSQL("COMMIT WORK");
    }
}

?>
