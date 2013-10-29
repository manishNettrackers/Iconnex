<?php

set_include_path(get_include_path().":../lib:../lib/classes");
require_once("GPSPosition.class.php");
require_once("EventPositionUpdate.class.php");

/**
 * @brief Functionality for processing a DAIP Position Update message
 */
class DAIPPositionUpdate extends DAIPEvent
{
    private $log;

    public $peer = NULL;
    public $message_ref = 0;
    public $message_quality;
    public $last_stop_id;
    public $distance_travelled_since_last_stop;
    public $timestamp = 0;
    public $schedule_deviation;
    public $gps_position;

    public $daip_vehicle = NULL;

    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    public static function createDAIPPositionUpdate($context, $peer, $sessionVehicleIdentifier, $message_ref, $latitude, $longitude, $bearing, $satellites_visible, $message_quality, $last_stop_id, $distance_travelled_since_last_stop, $timestamp)
    {
        $posUpdate = new self();

        $posUpdate->log->debug("latitude: " . $latitude);
        $posUpdate->log->debug("longitude: " . $longitude);
        $posUpdate->log->debug("bearing: " . $bearing);
        $posUpdate->log->debug("satellitesVisible: " . $satellites_visible);
        $posUpdate->log->debug("messageQuality: " . $message_quality);
        $posUpdate->log->debug("lastStopID: " . $last_stop_id);
        $posUpdate->log->debug("distanceTravelledSinceLastStop: " . $distance_travelled_since_last_stop);
        $posUpdate->log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));

        $posUpdate->context = $context;
        $posUpdate->peer = $peer;
        $posUpdate->message_ref = $message_ref;
        $posUpdate->gps_position = new GPSPosition();
        $posUpdate->gps_position->initialiseWithMilliarcsecs($latitude, $longitude);
        $posUpdate->gps_position->bearing = $bearing;
        $posUpdate->gps_position->satellites_visible = $satellites_visible;
        $posUpdate->gps_position->gps_time = $timestamp;
        $posUpdate->message_quality = $message_quality;
        $posUpdate->last_stop_id = $last_stop_id;
        $posUpdate->distance_travelled_since_last_stop = $distance_travelled_since_last_stop;
        $posUpdate->timestamp = $timestamp;

        $posUpdate->daip_vehicle = new DAIPVehicle();
        if (!$posUpdate->daip_vehicle->initialiseBySVID($sessionVehicleIdentifier))
        {
            echo "Failed to initialise DAIP vehicle with SVID $sessionVehicleIdentifier\n";
            $posUpdate->context->statusResponse = DAIPEvent::DAIP_UNKNOWN_SENDER;
            $ack = new EventAcknowledgement($posUpdate->context);
            $ack->process();
            return NULL;
        }

        return $posUpdate;
    }

    public static function createDAIPPositionUpdateBasic($context, $peer, $sessionVehicleIdentifier, $message_ref, $latitude, $longitude, $bearing, $scheduled_deviation, $timestamp)
    {
        $posUpdate = new self();

        $posUpdate->log->debug("latitude: " . $latitude);
        $posUpdate->log->debug("longitude: " . $longitude);
        $posUpdate->log->debug("bearing: " . $bearing);
        $posUpdate->log->debug("schedule_deviation: " . $schedule_deviation);

        $posUpdate->context = $context;
        $posUpdate->peer = $peer;
        $posUpdate->message_ref = $message_ref;
        $posUpdate->gps_position = new GPSPosition();
        $posUpdate->gps_position->initialiseWithMilliarcsecs($latitude, $longitude);
        $posUpdate->gps_position->bearing = $bearing;
        $posUpdate->gps_position->gps_time = $timestamp;
        $posUpdate->schedule_deviation = $schedule_deviation;
        $posUpdate->timestamp = $timestamp;

        return $posUpdate;
    }

    function process()
    {
        global $log;
        global $event_handler;

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventPositionUpdate(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
        $event->ip_address = $this->peer->ip;
        $event->conn_status = "A";
        $event->gps_position = $this->gps_position;
        $event->context = $this->context;

        if (!msg_send ($event_handler, 1, $event, true, true, $msg_err))
            $log->error("Failed to send event to event_handler message queue");
    }
}

?>
