<?php

set_include_path(get_include_path().":../lib:../lib/classes");
require_once("DAIPVehicle.class.php");

/**
 * @brief Functionality for processing a DAIP Log Off Request message
 */
class DAIPLogOffRequest extends DAIPEvent
{
    private $log;

    public $peer = NULL;
    public $messageRef = 0;
    public $operator_code = "";
    public $vehicle_code = "";
    public $timestamp = 0;

    public $daip_vehicle = NULL;

    function __construct($context, $peer, $messageRef, $sessionVehicleIdentifier, $timestamp)
    {
        $this->log = Logger::getLogger(__CLASS__);

        $this->log->debug("Session Vehicle Identifier: " . $sessionVehicleIdentifier);
        $this->log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));

        parent::__construct($context);
        $this->peer = $peer;
        $this->messageRef = $messageRef;
        $this->sessionVehicleIdentifier = $sessionVehicleIdentifier;
        $this->timestamp = $timestamp;

        $this->daip_vehicle = new DAIPVehicle();
        if (!$this->daip_vehicle->initialiseBySVID($sessionVehicleIdentifier))
            $this->daip_vehicle = false;
    }

    function process()
    {
        global $log;
        global $rtpiconnector;
        global $event_handler;

        if (!$this->daip_vehicle)
        {
            $this->log->warn("No vehicle for session_id " . $this->sessionVehicleIdentifier);
            return;
        }

        $vehicle_session = new VehicleSession($rtpiconnector);
        $vehicle_session->vehicle_id = $this->daip_vehicle->vehicle_id;
        $vehicle_session->save();

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventLogOff(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
        $event->ip_address = $this->peer->ip;
        $event->conn_status = "A";

        if (!msg_send($event_handler, 1, $event, true, true, $msg_err))
            $log->error("Failed to send event to event_handler message queue");
    }
}

?>
