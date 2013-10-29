<?php

include("common.php");
include("rtpiconnector.class.php");
require_once("EventPositionUpdate.class.php");

/**
 * @brief Parses a SIRI XML message
 */
class SIRIPacket
{   
    public $data;

    function __construct($inData)
    {
        $this->data = $inData;
        $this->datetime = new DateTime();
        $this->buildXMLTime();
        $this->event_handler = msg_get_queue(3000);
        $this->timestamp = $this->datetime;

        // Create connection to RTPI database
        $this->rtpiconnector = new rtpiconnector();
        if (!$this->rtpiconnector->connect(ICX_RTPI_DB_CONN_STRING, ICX_RTPI_DB_USER, ICX_RTPI_DB_PASSWORD))
        {
            echo "Failed to connect to Real Time Database\n";
            $this->rtpiconnector = false;
        }
        $this->rtpiconnector->executeSQL("SET ISOLATION TO DIRTY READ");
        $this->rtpiconnector->executeSQL("SET LOCK MODE TO WAIT 10");
    }

    function buildXMLTime($datetime = NULL)
    {
        if ($datetime)
            $phpTime = $datetime->format("Y-m-d H:i:s");
        else
            $phpTime = $this->datetime->format("Y-m-d H:i:s");

        $currentTime = strtotime($phpTime);
        $offsetString = 'Z'; // No need to calculate offset, as default timezone is already UTC
        if (date_default_timezone_get() != 'UTC')
        {
            $timezone = new DateTimeZone(date_default_timezone_get());
            $offset = $timezone->getOffset(new DateTime($phpTime));
            $offsetHours = round(abs($offset) / 3600);
            $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
            $offsetString = ($offset < 0 ? '-' : '+')
                . ($offsetHours < 10 ? '0' : '') . $offsetHours
                . ':'
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
        }

        $xmlTime = date('Y-m-d\TH:i:s', $currentTime) . $offsetString;

        if (!$datetime)
            $this->xmlTime = $xmlTime;

        return ($xmlTime);
    }

    function process()
    {
        if (isset($this->data->CheckStatusRequest))
        {
            $this->parseCheckStatusRequest();
        }
        else if (isset($this->data->ServiceDelivery))
        {
            $this->parseServiceDelivery();
        }
        else
        {
            file_put_contents("php://stderr", "Error - unsupported XML\n");
            die;
        }

        return;
    }

    function parseCheckStatusRequest()
    {
    /*    $xml = '<?xml version="1.0" encoding="utf-8"?>'; // Not mandatory */

        $xml = '<Siri xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.kizoom.com/standards/siri/schema/1.3/siri.xsd" version="1.3" xmlns="http://www.siri.org.uk/siri">';
        $xml .= '<CheckStatusResponse>';
        $xml .= '<ResponseTimestamp>' . $this->xmlTime . '</ResponseTimestamp>';
        // Note that CEN/TS 15531-2:2007 page 60 defines RequestorRef here, but the 1.3 schema defines ProcucerRef
//        $xml .= '<ProducerRef>CONNEXIONZUK</ProducerRef>';
        $xml .= '<Status>true</Status>';
//        $xml .= '<ServiceStartedTime>' . $this->xmlTime . '</ServiceStartedTime>';
        $xml .= '</CheckStatusResponse>';
        $xml .= '</Siri>';
        echo "$xml\n";
        file_put_contents("php://stderr", "TX $xml\n");

        // Not sure if this exec is required but Ticketer said they weren't getting the response above...
        // exec was used because the request takes ages to respond and the script is timed out with
        // Timeout waiting for output from CGI script /usr/local/bin/php-cgi
//        exec("php /opt/centurion/users/zeriv/trunk/ods/bin/SIRICheckStatusResponse.php > /dev/null &");

        die;
    }

    function parseServiceDelivery()
    {
        foreach ($this->data->ServiceDelivery as $delivery)
        {
            if (isset($delivery->Status))
            {
                if ($delivery->Status == "false")
                    die;
            }

            if (isset($delivery->VehicleMonitoringDelivery))
            {
                foreach ($delivery->VehicleMonitoringDelivery as $vehicleMonitoringDelivery)
                {
                    $this->parseVehicleMonitoringDelivery($vehicleMonitoringDelivery);
                }
            }
            else
                file_put_contents("php://stderr", "No VehicleMonitoringDelivery in ServiceDelivery!\n");
        }
    }

    function parseVehicleMonitoringDelivery($delivery)
    {
        if (isset($delivery->VehicleActivity))
        {
            foreach ($delivery->VehicleActivity as $vehicleActivity)
            {
                $this->parseVehicleActivity($vehicleActivity);
            }
        }
        else
            file_put_contents("php://stderr", "No VehicleActivity in VehicleMonitoringDelivery!\n");
    }

    function parseVehicleActivity($vehicleActivity)
    {
        if (!isset($vehicleActivity->MonitoredVehicleJourney))
        {
            file_put_contents("php://stderr", "No MonitoredVehicleJourney in VehicleActivity!\n");
            die;
        }

        if (isset($vehicleActivity->RecordedAtTime))
        {
            $this->timestamp = DateTime::createFromFormat("Y-m-d?H:i:sT", $vehicleActivity->RecordedAtTime);
        }

        $this->parseMonitoredVehicleJourney($vehicleActivity->MonitoredVehicleJourney);
    }

    function parseMonitoredVehicleJourney($journey)
    {
        $gps_position = new GPSPosition();
        if (isset($journey->VehicleLocation))
            $gps_position->initialiseWithLatLong((string)$journey->VehicleLocation->Latitude, (string)$journey->VehicleLocation->Longitude);
        $gps_position->bearing = (string)$journey->Bearing;
        $gps_position->satellites_visible = -1;
        $gps_position->gps_time = $this->timestamp;

        $vehicle = $this->getVehicleByRef($journey->VehicleRef);
        if (!$vehicle)
        {
            file_put_contents("php://stderr", "Error - failed to find vehicle for VehicleRef " . $journey->VehicleRef . "\n");
            die;
        }

        $unit_build = new UnitBuild($this->rtpiconnector);
        $unit_build->build_id = $vehicle->build_id;
        $unit_build->load();

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventPositionUpdate($this->datetime, $this->timestamp, $unit_build->build_code, "Vehicle", $unit_build->build_code);
        $event->conn_status = "A";
        $event->gps_position = $gps_position;

        if (!msg_send($this->event_handler, 1, $event, true, true, $msg_err))
            file_put_contents("php://stderr", "Failed to send event to event_handler message queue");

        // Build an Event and send it to the EventHandler's message queue.
        $event = new EventJourneyDetails($this->datetime, $this->timestamp, $unit_build->build_code, "Vehicle", $unit_build->build_code);
        $event->conn_status = "A";
        $event->service_code = (string)$journey->LineRef;
        $event->public_service_code = (string)$journey->PublishedLineName;
//        $event->running_board = 
        $event->duty_number = (string)$journey->BlockRef;
        $event->direction = (string)$journey->DirectionRef;
        if (isset($journey->Extensions->Operational->TicketMachine))
        {
//            <TicketMachineServiceCode>QA01_07</TicketMachineServiceCode>
            $event->journey_number = (string)$journey->Extensions->Operational->TicketMachine->JourneyCode;
        }

        $strings = explode("_", $journey->FramedVehicleJourneyRef->DatedVehicleJourneyRef);
        $event->scheduled_start = $strings[2] . ":" . $strings[3];
//        $event->depot_code = $this->depot_code;
//        $event->driver_code = $this->driver_code;
//        $event->first_stop_id = $this->first_stop_id;
//        $event->destination_stop_id = $this->destination_stop_id;

        if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
            file_put_contents("php://stderr", "Failed to send event to event_handler message queue");
    }

    function getVehicleByRef($vehicle_ref)
    {
        $vehicle = new Vehicle($this->rtpiconnector);
        $vehicle->vehicle_reg = str_replace("_", " ", trim($vehicle_ref));
        if (!$vehicle->load(array("vehicle_reg")))
        {
            // VehicleRef might be OperatorCode_FleetNo
            if (!strstr($vehicle_ref, "-"))
            {
                file_put_contents("php://stderr", "No '-' character found in vehicle_ref");
                return false;
            }

            $strings = explode("-", $vehicle_ref);
            $operator_code = $strings[0];
            $vehicle_code = $strings[1];
            if (strlen($vehicle_code) == 0)
            {
                file_put_contents("php://stderr", "No vehicle after '-' character in vehicle_ref");
                return false;
            }

            $operator = new Operator($this->rtpiconnector);
            $operator->operator_code = $operator_code;
            if (!$operator->load(array("operator_code")))
            {
                file_put_contents("php://stderr", "Failed to load operator $operator_code");
                return false;
            }

            $vehicle->operator_id = $operator->operator_id;
            $vehicle->vehicle_code = $vehicle_code;

            if (!$vehicle->load(array("operator_id", "vehicle_code")))
            {
                file_put_contents("php://stderr", "Failed to load vehicle for operator_id " . $vehicle->operator_id . " vehicle_code " . $vehicle->vehicle_code);
                return false;
            }
        }

        return $vehicle;
    }
}
?>
