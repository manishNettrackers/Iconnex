<?php

require_once("Event.class.php");

class EventPositionUpdate extends EventUnit
{
    public $gps_position = null;

    function process()
    {
        echo "EventPositionUpdate->process()\n";
        global $rtpiconnector;

        $rtpiconnector->executeSQL("BEGIN WORK");
        $rtpiconnector->executeSQL("LOCK TABLE log_time IN SHARE MODE");
        $rtpiconnector->executeSQL("LOCK TABLE log_position_update IN SHARE MODE");
        $log_position_update = new LogPositionUpdate($rtpiconnector);
        $log_position_update->logByEvent($this);

        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {   
            echo "EventPositionUpdate->process() failed to get_active_item\n";
            return;
        }

        parent::process();

        $this->active_item->updatePosition($this->gps_position);
        $this->active_item->update($this);
//        $this->active_item->show();

        // Acknowledgment to sender of message
        if ($this->context->ackRequired)
        {
            $ack = new EventAcknowledgement($this->context);
            $ack->process();
        }

        $rtpiconnector->executeSQL("COMMIT WORK");
//        echo "EventPositionUpdate->process() done\n";
    }
}

?>
