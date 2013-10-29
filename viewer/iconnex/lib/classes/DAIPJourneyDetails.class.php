<?php

set_include_path(get_include_path().":../lib:../lib/classes");

require_once("TimetableJourney.class.php");
require_once("TimetableJourneyLive.class.php");
require_once("EventJourneyDetails.class.php");

/**
 * @brief Functionality for processing a DAIP Journey Details message
 */
class DAIPJourneyDetails extends DAIPEvent
{
    private $log;

    public $peer = NULL;
    public $message_ref = 0;
    public $timestamp = 0;

    public $daip_vehicle;
    
    public $tjl;

    function __construct($context, $peer, $message_ref, $svid, $service_code, $running_board, $journey_number, $scheduled_start, $duty_number, $public_service_code, $direction, $depot_code, $driver_code, $first_stop_id, $destination_stop_id, $timestamp)
    {
        $this->log = Logger::getLogger(__CLASS__);

        $this->log->debug("Service Code (6 bytes): " . $service_code);
        $this->log->debug("Running Board (7 bytes): " . $running_board);
        $this->log->debug("Journey Number (8 bytes): " . $journey_number);
        $this->log->debug("Scheduled Start (2 bytes): " . $scheduled_start);
        $this->log->debug("Duty Number (6 bytes): " . $duty_number);
        $this->log->debug("Public Service Code (6 bytes): " . $public_service_code);
        $this->log->debug("Direction (1 byte): " . $direction);
        $this->log->debug("Depot Code (4 bytes): " . $depot_code);
        $this->log->debug("Driver Code (6 bytes): " . $driver_code);
        $this->log->debug("First Stop ID (12 bytes): " . $first_stop_id);
        $this->log->debug("Destination Stop ID (12 bytes): " . $destination_stop_id);
        $this->log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));

        parent::__construct($context);
        $this->peer = $peer;
        $this->message_ref = $message_ref;
        $this->service_code = $service_code;
        $this->running_board = $running_board;
        $this->journey_number = $journey_number;
        $this->scheduled_start = $scheduled_start;
        $this->duty_number = $duty_number;
        $this->public_service_code = $public_service_code;
        $this->direction = $direction;
        $this->depot_code = $depot_code;
        $this->driver_code = $driver_code;
        $this->first_stop_id = $first_stop_id;
        $this->destination_stop_id = $destination_stop_id;
        $this->timestamp = $timestamp;

        // Get vehicle details for this Session Vehicle Identifier
        $this->daip_vehicle = new DAIPVehicle();
        if (!$this->daip_vehicle->initialiseBySVID($svid))
        {
            $this->log->error("Failed to initialise DAIP vehicle with SVID $sessionVehicleIdentifier\n");
            $context->statusResponse = DAIPEvent::DAIP_UNKNOWN_SENDER;
            $ack = new EventAcknowledgement($context);
            $ack->process();
            return NULL;
        }
    }

    function process()
    {
        global $log;
        global $event_handler;

	// Unknown SVID 
	if ( !$this->daip_vehicle->vehicle_id )
	{
		echo $this->daip_veihcle->sessionVehicleIdentifier."!!";
		$this->errorStatus = DAIPEvent::DAIP_UNKNOWN_SENDER;
		return;
	}

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventJourneyDetails(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
        $event->context = $this->context;
        $event->ip_address = $this->peer->ip;
        $event->conn_status = "A";
        $event->service_code = $this->service_code;
        $event->public_service_code = $this->public_service_code;
        $event->running_board = $this->running_board;
        $event->duty_number = $this->duty_number;
        $event->journey_number = $this->journey_number;
        $event->scheduled_start = $this->scheduled_start;
        $event->direction = $this->direction;
        $event->depot_code = $this->depot_code;
        $event->driver_code = $this->driver_code;
        $event->first_stop_id = $this->first_stop_id;
        $event->destination_stop_id = $this->destination_stop_id;

        if (!msg_send ($event_handler, 1, $event, true, true, $msg_err))
            $log->error("Failed to send event to event_handler message queue");

    }
}

?>
