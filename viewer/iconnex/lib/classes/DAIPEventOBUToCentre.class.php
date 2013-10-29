<?php

set_include_path(get_include_path().":../lib:../lib/classes");

/**
 * @brief Functionality for processing a DAIP Event (OBU to centre) message
 */
class DAIPEventOBUToCentre extends DAIPEvent
{
    private $log;

    public $peer = NULL;
    public $message_ref = 0;
    public $sequence_id;
    public $reference_sequence_id;
    public $gps_position;
    public $message_type;
    public $message_code;
    public $timestamp = 0;
    public $message_parameters = NULL;

    public $daip_vehicle = NULL;
    public $native_message_type = NULL;
    public $location = NULL;

    function __construct($context, $peer, $sessionVehicleIdentifier, $message_ref, $sequence_id, $reference_sequence_id, $latitude, $longitude, $message_type, $message_code, $message_parameters, $timestamp)
    {
        $this->log = Logger::getLogger(__CLASS__);
        
        $timestamp = new DateTime();
        $this->log->debug("Sequence ID: " . $sequence_id);
        $this->log->debug("Reference SequenceID: " . $reference_sequence_id);
        $this->log->debug("Latitude: " . $latitude);
        $this->log->debug("Longitude: " . $longitude);
        $this->log->debug("Message Type: " . $message_type);
        $this->log->debug("Message Code: " . $message_code);
        $this->log->debug("Message Parameters: " . $message_parameters);
        $this->log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));

        parent::__construct($context);
        $this->peer = $peer;
        $this->message_ref = $message_ref;
        $this->sequence_id = $sequence_id;
        $this->reference_sequence_id = $reference_sequence_id;
        $this->gps_position = new GPSPosition();
        $this->gps_position->initialiseWithMilliarcsecs($latitude, $longitude);
        $this->gps_position->bearing = $bearing;
        $this->gps_position->satellites_visible = $satellites_visible;
        $this->gps_position->gps_time = $timestamp;
        $this->message_type = $message_type;
        $this->message_code = $message_code;
        $this->message_parameters = $message_parameters;
        $this->timestamp = $timestamp;

        $this->daip_vehicle = new DAIPVehicle();
        if (!$this->daip_vehicle->initialiseBySVID($sessionVehicleIdentifier))
        {
            echo "Failed to initialise DAIP vehicle with SVID $sessionVehicleIdentifier\n";
            $daip_vehicle = NULL;
            $this->context->statusResponse = DAIPEvent::DAIP_UNKNOWN_SENDER;
            $ack = new EventAcknowledgement($this->context);
            $ack->process();
        }

        $this->decodeMessageType();
    }

    function decodeMessageType()
    {
        global $rtpiconnector;

        if ($this->message_type == 0)
        {
            $this->log->debug("Emergency Message");
            switch ($this->message_code)
            {
                case 0: $this->log->debug("Need Assistance"); break;
                case 1: $this->log->debug("Accident"); break;
                case 2: $this->log->debug("Obstruction Need to Divert"); break;
                case 3: $this->log->debug("Diverting"); break;
                case 4: $this->log->debug("Abandoning Journey"); break;
                case 5: $this->log->debug("Curtailing Journey"); break;
                default: $this->log->error("Invalid Emergency Message code " . $this->message_code . " (not used)"); break;
            }
        }
        else if ($this->message_type == 1)
        {
            $this->log->debug("Service Message");
            switch ($this->message_code)
            {
                case 0: $this->log->debug("Request PMR Radio session"); break;
                case 1: $this->log->debug("Accept New Duty"); break;
                case 2: $this->log->debug("Unable to Accept New Duty"); break;
                case 3: $this->log->debug("Accept Rest Day"); break;
                case 4: $this->log->debug("Unable to Accept Rest Day"); break;
                case 5: $this->log->debug("Accept Overtime"); break;
                case 6: $this->log->debug("Unable to Accept Overtime"); break;
                case 7: $this->log->debug("Request Relief"); break;
                case 8: $this->log->debug("Acknowledge Receipt of Incoming Message"); break;
                default: $this->log->debug("Invalid Service Message code " . $this->message_code . " (not used)"); break;
            }
        }
        else if ($this->message_type == 2)
        {
            $this->log->debug("Vehicle Status");
            switch ($this->message_code)
            {
                case 0: $this->log->debug("Puncture"); break;
                case 1: $this->log->debug("Low Oil Pressure"); break;
                case 2: $this->log->debug("High Engine Temp"); break;
                case 3: $this->log->debug("Passenger Load (should get a parameter with load value)"); break; // TODO
                default: $this->log->debug("Invalid Vehicle Status code " . $this->message_code . " (not used)"); break;
            }
        }
        else if ($this->message_type > 2
        && $this->message_type < 127)
        {
            $this->log->debug("Invalid message type " . $this->message_type . " (not used)");
        }
        else if ($this->message_type == 127)
        {
            $this->log->debug("Free Format Text Messages");
            switch ($this->message_code)
            {
                case 0: $this->log->debug("Text String #1: " . $this->message_parameters); break;
                case 1:
                    $msgCodeArr = unpack("C25/Ccode", $this->data);
                    $this->log->debug("Predefined message: " . $msgCodeArr["code"]);
                    break;
                default: $this->log->debug("Invalid Free Format Text Messages code " . $this->message_code . " (not used)"); break;
            }
        }
        else if ($this->message_type == 128)
        {
            $this->log->debug("Vehicle Location");
            switch ($this->message_code)
            {
                case 0:
                    $this->log->debug("Departing from Stop");
                    $this->native_message_type = MessageType::VEHICLE_LOCATION_DEPARTING_FROM_STOP;
                    $this->location = new Location($rtpiconnector);
                    $this->location->location_code = $this->message_parameters;
                    if (!$this->location->load(array("location_code")))
                    {
                        $this->log->warn("Failed to load location with location_code " . $this->message_parameters);
                    }

                    break;

                case 1:
                    $this->log->debug("Crossing TLP Trigger Line");
                    break;

                case 2:
                    $this->log->debug("Arriving Stop");
                    $this->native_message_type = MessageType::VEHICLE_LOCATION_ARRIVING_STOP;
                    break;

                case 3: $this->log->debug("Off Route"); break;
                case 4: $this->log->debug("On Route"); break;
                case 5: $this->log->debug("On Diversion Stop"); break;
                case 6: $this->log->debug("Depot Exit / Entry"); break;
                default: $this->log->debug("Invalid Vehicle Location code " . $this->message_code . " (not used)"); break;
            }
        }
        else if ($this->message_type == 129)
        {
            $this->log->debug("Vehicle Status");
            switch ($this->message_code)
            {
                case 0: $this->log->debug("Vehicle Configuration Information"); break;
                case 1: $this->log->debug("Vehicle Serial No Info"); break;
                default: $this->log->debug("Invalid Vehicle Status code " . $this->message_code . " (not used)"); break;
            }
        }
        else
        {
            $this->log->debug("Invalid message type " . $this->message_type . " (not used)");
        }
    }

    function process()
    {
        global $event_handler;
        global $log;

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventOBUToCentre(new DateTime(), $this->timestamp, $this->daip_vehicle->build_code, "Vehicle", $this->daip_vehicle->build_code);
        $event->ip_address = $this->peer->ip;
        $event->conn_status = "A";
        $event->context = $this->context;
        $event->gps_position = $this->gps_position;
        $event->message_type = $this->native_message_type;
        $event->text = $this->message_parameters;
        if (!msg_send($event_handler, 1, $event, true, true, $msg_err))
            $log->error("Failed to send event to event_handler message queue");
    }
}

?>
