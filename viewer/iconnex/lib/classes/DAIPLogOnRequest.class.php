<?php

set_include_path(get_include_path().":../lib:../lib/classes");
require_once("DAIPVehicle.class.php");
require_once("EventLogOn.class.php");
require_once("DAIPEvent.class.php");

/**
 * @brief Functionality for processing a DAIP Log On Request message
 */
class DAIPLogOnRequest extends DAIPEvent
{
    private $log;
    private $error_code;

    public $peer = NULL;
    public $messageRef = 0;
    public $operator_code = "";
    public $vehicle_code = "";
    public $timestamp = 0;

    public $daip_vehicle = NULL;

    function __construct($context, $peer, $messageRef, $operator_code, $vehicle_code, $timestamp)
    {
        $this->log = Logger::getLogger(__CLASS__);

        $timestamp = new DateTime();

	    // Temporarily map operator ASH to ASES to allow tracking TODO
	    if ($operator_code == "ASH")
        {
            echo "DAIPLogOnRequest->__contruct() mapping operator ASH to ASES\n";
            $operator_code = "ASES";
        }
	
        $this->log->debug("Operator ID: " . $operator_code);
        $this->log->debug("Vehicle ID: " . $vehicle_code);
        $this->log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));

        parent::__construct($context);
        $this->peer = $peer;
        $this->messageRef = $messageRef;
        $this->daip_vehicle = new DAIPVehicle();
        if (!$this->daip_vehicle->initialiseByOperatorAndVehicle($operator_code, $vehicle_code))
        {
            switch ($this->daip_vehicle->error_status)
            {
                case UNKNOWN_OPERATOR:
                    $this->error_code = DAIPEvent::DAIP_UNKNOWN_OPERATOR_CODE;
                    break;

                case UNKNOWN_VEHICLE:
                    $this->error_code = DAIPEvent::DAIP_UNKNOWN_VEHICLE_CODE;
                    break;

                //case UNKNOWN_BUILD:
                //case UNKNOWN_ERROR:
                //case NO_ERROR:
                default:
                    $this->error_code = DAIPEvent::DAIP_NO_ERROR_CODE;
                    break;
            }
            $this->daip_vehicle = NULL;
        }
        else
            $this->daip_vehicle->newSessionVehicleIdentifier();

        $this->log->debug("Allocated ".$vehicle_code." SVID: " . $this->daip_vehicle->sessionVehicleIdentifier);
        $this->timestamp = $timestamp;
    }

    function process()
    {
        global $messageCounter;
        global $event_handler;

        /**
         * Send a DAIP Log On Response
         */
        if (!$this->daip_vehicle)
            $sessionVehicleIdentifier = 0;
        else
            $sessionVehicleIdentifier = $this->daip_vehicle->sessionVehicleIdentifier;

        $messageCounter++;
        $now = gmdate("ymdHis");

        $msg = pack("CCCCCCCCCCCCCCCCCCC",
            // Acknowledgement Header
            1, // $formatVersionFirstByte
            16, // $formatVersionSecondByte
            0, // $messageFlags 00000000 for Live Message
            ($messageCounter & 0xFF00) >> 8,
            $messageCounter & 0xFF,
            ($sessionVehicleIdentifier & 0xFF00) >> 8,
            $sessionVehicleIdentifier & 0xFF,
            0, // $optionalFieldsFirstByte
            0, // $optionalFieldsSecondByte

            // Payload
            DAIP_LOG_ON_RESPONSE, // $messageID Log On Response 20 (14 Hex)
            ($sessionVehicleIdentifier & 0xFF00) >> 8,
            $sessionVehicleIdentifier & 0xFF,

            // Error Code
            $this->error_code,

            // Timestamp
            hexdec(substr($now, 0, 2)),
            hexdec(substr($now, 2, 2)),
            hexdec(substr($now, 4, 2)),
            hexdec(substr($now, 6, 2)),
            hexdec(substr($now, 8, 2)),
            hexdec(substr($now, 10, 2)));

        $this->log->debug("DAIPLogOnRequest->process() TX " . $this->peer->ip . ":" . $this->peer->port . " " . bin2hex($msg));
        socket_sendto($this->peer->socket, $msg, strlen($msg), 0, $this->peer->ip, $this->peer->port);

        if (!$this->daip_vehicle)
            return;

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventLogOn(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
        $event->ip_address = $this->peer->ip;
        $event->conn_status = "A";

        if (!msg_send($event_handler, 1, $event, true, true, $msg_err))
            $this->log->error("Failed to send event to event_handler message queue");

        // Tell the secondary server about this SVID
/* Should only be enabled on primary server
        if ($this->daip_vehicle)
        {
            $event = new EventVehicleSession(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
            $event->ip_address = $this->peer->ip;
            $event->conn_status = "A";
            $event->sessionVehicleIdentifier = $this->daip_vehicle->sessionVehicleIdentifier;

            if (!msg_send($event_handler, 1, $event, true, true, $msg_err))
                $this->log->error("Failed to send event to event_handler message queue");
        }
*/
    }
}

?>
