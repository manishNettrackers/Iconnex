<?php

require_once("Event.class.php");

/*
**  Class encapsulates an acknowledgement from sign when it receives
**  a text message or a command to clear a text message
**  Either message type contains the message id
*/
class EventSignMessageAck extends EventUnit
{
    public $timestamp;
    public $ack_message_id;

    function __construct($receipt_time, $timestamp, $initiator, $origin, $reference)
    {  
        parent::__construct($receipt_time, $timestamp, $initiator, $origin, $reference);
    }

    function process()
    {
        global $rtpiconnector;

        //
        //echo "EventSignMessageAck->process()\n";

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {   
            echo "EventSignMessageAck failed to get_active_item - cannot process event\n";
            return;
        }

        // Set Time of Active Item Message
        $unit_status->message_type = $this->message_type;
        $unit_status->message_time = $this->timestamp;

        // Handle acknowledgement

        $now = UtilityDateTime::currentTime();
        $signmessage = new UnitSignMessage($rtpiconnector);
        $signmessage->build_id = $this->active_item->unit_build->build_id;
        $signmessage->message_id = $this->ack_message_id;

        if ( $signmessage->load() )
        {
            // For received message acks we want to flag the receipt time
            if ( $this->message_type == 456 )
            {
                $signmessage->received = $now;
                $signmessage->save();
            }

            // For cleared message acks we want to remove the entry
            if ( $this->message_type == 458 )
            {
                $signmessage->received = $now;
                $signmessage->delete();
            }
        }
        else
        {
            echo " => Failed to handle ack $this->message_type from id: $signmessage->build_id msg $signmessage->message_id <BR>";
        }

        parent::process();
        //echo "EventSignMessageAck->process() done\n";
    }
}

?>
