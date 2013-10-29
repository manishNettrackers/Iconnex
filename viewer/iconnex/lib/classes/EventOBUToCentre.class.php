<?php

require_once("Event.class.php");

class EventOBUToCentre extends EventUnit
{
    public $gps_position;
    public $text;

    function process()
    {
        global $rtpiconnector;

        echo "EventOBUToCentre->process()\n";
        $rtpiconnector->executeSQL("BEGIN WORK");
        $rtpiconnector->executeSQL("LOCK TABLE timetable_journey IN SHARE MODE");
        $rtpiconnector->executeSQL("LOCK TABLE timetable_visit IN SHARE MODE");

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {   
            $rtpiconnector->executeSQL("ROLLBACK WORK");
            echo "EventOBUToCentre failed to get_active_item - cannot process event\n";
            return;
        }

        parent::process();

        $log_event = new LogEventOBUToCentre($rtpiconnector);
        $log_event->logByEvent($this);

        $rtpiconnector->executeSQL("COMMIT WORK");
    }
}

?>
