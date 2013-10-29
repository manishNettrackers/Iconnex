<?php

require_once("Event.class.php");

/*
**  Class encapsulates a neart beat message event normally sent by
**  a bus stop display to indicate that it is alive to the server
**  Encapsulates the event functionality required which is to update
**  the unit builds lart alive time message to the time sent in the message
*/
class EventStopInitialise extends EventUnit
{
    public $timestamp;
    public $software_version;

    function __construct($receipt_time, $timestamp, $initiator, $origin, $reference)
    {  
        parent::__construct($receipt_time, $timestamp, $initiator, $origin, $reference);
    }

    function process()
    {
        global $rtpiconnector;

        //
        //echo "EventHeartbeat->process()\n";

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {   
            echo "EventHeartbeat failed to get_active_item - cannot process event\n";
            return;
        }
        $this->active_item->soft_ver = $this->software_version;

        // Set Time of Active Item Message
        $unit_status->message_type = $this->message_type;
        $unit_status->message_time = $this->timestamp;
        $unit_status->soft_ver = $this->software_version ;

        parent::process();
        //echo "EventHeartbeat->process() done\n";
    }
}

?>
