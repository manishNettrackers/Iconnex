<?php

require_once("Event.class.php");

class EventJourneyDetails extends EventUnit
{
    public $service_code;
    public $public_service_code;
    public $running_board;
    public $duty_number;
    public $journey_number;
    public $scheduled_start;
    public $direction;
    public $depot_code;
    public $driver_code;
    public $first_stop_id;
    public $destination_stop_id;
    public $timetable_id;
    public $validJourneyDetails = false;

    function process()
    {
        global $rtpiconnector;

//        echo "EventJourneyDetails->process()\n";
        $rtpiconnector->executeSQL("BEGIN WORK");
        $rtpiconnector->executeSQL("LOCK TABLE timetable_journey IN SHARE MODE");
        $rtpiconnector->executeSQL("LOCK TABLE timetable_visit IN SHARE MODE");

/*
        $rtpiconnector->executeSQL("LOCK TABLE log_time IN SHARE MODE");
        $rtpiconnector->executeSQL("LOCK TABLE log_journey_details IN SHARE MODE");
        $log_journey_details = new LogJourneyDetails($rtpiconnector);
        $log_journey_details->logByEvent($this);
*/

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {   
            $rtpiconnector->executeSQL("ROLLBACK WORK");
            echo "EventJourneyDetails failed to get_active_item - cannot process event\n";
            return;
        }

        parent::process();

        // Action Journey Info
        $this->context->statusResponse = 0;
        $this->validJourneyDetails = true;
        $etmStatus = MessageType::CMNO_ETM_INVALID_JOURNEY;
        if ( !$this->active_item->changeJourney($this->active_item->unit_build, $this, $this->event_handler->tj_list, $this->event_handler->tjl_list) )
        {
                $this->validJourneyDetails = false;
                $this->context->statusResponse = DAIPEvent::DAIP_UNKNOWN_JOURNEY_NUMBER;
                $etmStatus = MessageType::CMNO_ETM_VALID_JOURNEY;
        }

        //$this->validJourneyDetails = true;
        //$this->context->statusResponse = DAIPEvent::DAIP_NO_ERROR_CODE;
        //$this->validJourneyDetails = false;
        //$this->context->statusResponse = DAIPEvent::DAIP_UNKNOWN_JOURNEY_NUMBER;

        if ($this->active_item->unit_build->build_code != "AUT")
        {
	        // Log Journey Status
            echo "J1\n";
            $log_journey_details = new LogJourneyDetails($rtpiconnector);
            echo "J2\n";
            $log_journey_details->logByEvent($this);
            echo "J3\n";

            // Store latest ETM status
            $unit_status_etm = new UnitStatusETM($rtpiconnector);
            echo "J4\n";
            $this->active_item->unit_build->dump();
            $unit_status_etm->storeETMStatus($this->active_item, $this, $etmStatus);
            echo "J5\n";

	        // Acknowledge to sender of message
	        $ack = new EventAcknowledgement($this->context);
	        $ack->process();
        }

        $rtpiconnector->executeSQL("COMMIT WORK");
//        echo "EventJourneyDetails->process() done\n";
    }
}

?>
