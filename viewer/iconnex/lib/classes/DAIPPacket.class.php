<?php

set_include_path(get_include_path().":../lib:../lib/classes");
include("DAIPLogOnRequest.class.php");
include("DAIPLogOffRequest.class.php");
include("DAIPJourneyDetails.class.php");
include("DAIPPositionUpdate.class.php");
include("DAIPEndOfJourney.class.php");
include("DAIPEventOBUToCentre.class.php");

define("DAIP_ACKNOWLEDGEMENT", 1);
define("DAIP_LOG_ON_REQUEST", 10);
define("DAIP_LOG_OFF_REQUEST", 11);
define("DAIP_LOG_ON_RESPONSE", 20);
define("DAIP_JOURNEY_DETAILS", 30);
define("DAIP_JOURNEY_DETAILS_BASIC", 31);
define("DAIP_JOURNEY_DETAILS_ENHANCED", 32);
define("DAIP_END_OF_JOURNEY", 39);
define("DAIP_POSITION_UPDATE", 40);
define("DAIP_POSITION_UPDATE_BASIC", 41);
define("DAIP_POSITION_UPDATE_ENHANCED", 42);
define("DAIP_EVENT_OBU_TO_CENTRE", 50);
define("DAIP_EVENT_CENTRE_TO_OBU", 60);
define("DAIP_ENQUIRY", 255);

global $gMessageCounter;

/**
 * @brief Breaks up a binary DAIP message and processes it
 */
class DAIPPacket
{   
    private $log;

    public $data;
    public $length;
    public $peer;
    public $formatVersion;
    public $sessionVehicleIdentifier;
    public $optionalDataFields;
    public $messageContent;
    public $acknowledgementFlag;
    public $messageOrigin;
    public $connector = false;

    function __construct($inData, $length, $peer)
    {
        $this->log = Logger::getLogger(__CLASS__);

        $this->data = $inData;
        $this->length = $length;
        $this->peer = $peer;

        $this->acknowledgementFlag = true;
    }

    function process()
    {
        // Check for a DAIP Ack from another process first which begins ack
        $bits = unpack("A3header", $this->data);
        if ($bits["header"] == "ACK")
        {
            $datalen = $this->length - 3 - 20 - 2;
            $bits = unpack("A3header/A20ip/nport/", $this->data);
            $ackmsg = substr($this->data, 25, $datalen);

            $this->log->debug("DAIPPacket->process() ACK TX " . $bits["ip"] . ":" . 5000 . " " . bin2hex($ackmsg));
            socket_sendto($this->peer->socket, $ackmsg, strlen($ackmsg), 0, $bits["ip"], 5000);
            return;
        }

        // Check for a DAIP Centre TO OBU Message
        if ($bits["header"] == "DRM")
        {
            $datalen = $this->length - 4 - 20 - 1;
            $bits = unpack("A3header/Nsessionid/A20ip/Nport/Ntype/Ncode/Ndest/A100message", $this->data);
            $this->sendCentreToOBU($this->peer->socket, $bits["sessionid"], $bits["ip"], $bits["port"], $bits["type"], $bits["code"], $bits["dest"], $bits["message"]);
            return;
        }

        // Check for a Enquiry message
        if ($bits["header"] == "ENQ")
        {
            $datalen = $this->length - 4 - 20 - 1;
            $bits = unpack("A3header/Nsessionid/A20ip/Nport/Ntype/Ncode/A100message", $this->data);
            $this->sendEnquiry($this->peer->socket, $bits["sessionid"], $bits["ip"], $bits["port"], $bits["type"], $bits["code"], $bits["message"]);
            return;
        }

        // Event Context
        $context = new EventContext();
        $context->transportMethod = "DAIP";
        $context->sourceAddress = $this->peer->ip;
        $context->commsType = "UDP";
        $context->returnPort = 5000;
        $context->socket = $this->peer->socket;

        // A DAIP message wrapper starts with the protocol/format version identifier
        echo "\n";
        $this->log->debug($this->peer->ip. " RX " . $this->peer->ip . ":" . $this->peer->port . " " . bin2hex($this->data));
        $exploded = unpack("CformatVersionFirst/CformatVersionSecond", $this->data);

        // Format Version No.
        $nibble1 = ($exploded["formatVersionFirst"] & 0xF0) >> 4;
        $nibble2 = $exploded["formatVersionFirst"] & 0x0F;
        $nibble3 = ($exploded["formatVersionSecond"] & 0xF0) >> 4;
        $nibble4 = $exploded["formatVersionSecond"] & 0x0F;
        $this->formatVersion = "$nibble1$nibble2.$nibble3$nibble4";
        $this->log->debug($this->peer->ip. " Format Version No. (4 nibbles = 2 bytes): " . $this->formatVersion);
        switch ($this->formatVersion)
        {
            case "01.00":
                $wrapper = unpack("CformatVersionFirst/CformatVersionSecond/CmessageFlags/CmessageCounterFirst/CmessageCounterSecond/CvehicleFirst/CvehicleSecond/CoptionalDataFieldsFirst/CoptionalDataFieldsSecond", $this->data);
                break;

            case "01.10":
                $wrapper = unpack("CformatVersionFirst/CformatVersionSecond/CmessageFlags/CmessageCounterFirst/CmessageCounterSecond/CvehicleFirst/CvehicleSecond/CoptionalDataFieldsFirst/CoptionalDataFieldsSecond", $this->data);
                break;

            default:
                $this->log->warn("Got unrecognised version in packet header - dropping packet");
                return;
        }

	
        // Message Flags
        $isAcknowledgement = false;
        $this->log->debug($this->peer->ip. " Message Flags (1 byte): " . $wrapper["messageFlags"]);
        if ($wrapper["messageFlags"] != 0)
        {
            // Bit 0
            if ($wrapper["messageFlags"] & 0x01)
            {
                $isAcknowledgement = true;

                // Bit 1
                if ($wrapper["messageFlags"] & 0x02)
                    $this->log->debug($this->peer->ip. " Message is acknowledged");
                else
                    $this->log->debug($this->peer->ip. " Message is not acknowledged (Error in message)");
            }
            else
            {
                $this->log->debug($this->peer->ip. " This is a message");
                // Bit 1
                if ($wrapper["messageFlags"] & 0x02)
                {
                    $this->log->debug($this->peer->ip. " Acknowledgement is required");
                    $context->ackRequired = true;
                }
                else
                {
                    $this->log->debug($this->peer->ip. " No acknowledgement required");
                    $context->ackRequired = false;
                }
            }

            // Bit 2 - Reserved

            // Bit 3
            if ($wrapper["messageFlags"] & 0x04)
                $this->log->debug($this->peer->ip. " This is a test message");

            // Bits 4-7 Reserved
        }

        $ackCounter = 0;
        if ($isAcknowledgement)
        {
            $wrapper = unpack("CformatVersionFirst/CformatVersionSecond/CmessageFlags/CackCounterFirst/CackCounterSecond/CmessageCounterFirst/CmessageCounterSecond/CvehicleFirst/CvehicleSecond", $this->data);
            $ackCounter = ($wrapper["ackCounterFirst"] << 8) + $wrapper["ackCounterSecond"];
            $this->log->debug($this->peer->ip. " Ack Counter (2 bytes): $ackCounterReference");
        }

        // Message Counter
        $messageCounterReference = ($wrapper["messageCounterFirst"] << 8) + $wrapper["messageCounterSecond"];
        $this->log->debug($this->peer->ip. " Message Counter (2 bytes): $messageCounterReference");
        $context->messageSequence = $messageCounterReference;

        // Session Vehicle Identifier
        $this->sessionVehicleIdentifier = ($wrapper["vehicleFirst"] << 8) + $wrapper["vehicleSecond"];
        $this->log->debug($this->peer->ip. " Session Vehicle Identifier (2 bytes): " . $this->sessionVehicleIdentifier);
        $context->originId = $this->sessionVehicleIdentifier;

        // Optional Data Fields
        $this->optionalDataFields = ($wrapper["optionalDataFieldsFirst"] << 8) + $wrapper["optionalDataFieldsSecond"];
        $this->log->debug($this->peer->ip. " Optional Data Fields (2 bytes): " . $this->optionalDataFields);

        // If this is an ack message then there is no payload
        $messageID = false;
        if ($isAcknowledgement)
            $messageID = DAIP_ACKNOWLEDGEMENT;
        else
        {
            // Message Payload
            $this->messageContent = unpack("C9header/CmessageID", $this->data);
            $messageID = $this->messageContent["messageID"];
            $this->log->debug($this->peer->ip. " Message ID (1 byte): " . $this->messageContent["messageID"]);
        }

        // Process message
        switch ($messageID)
        {
            case DAIP_ACKNOWLEDGEMENT: 
                $this->log->info("OBU Acknowledgement");
                $this->messageContent = unpack("C9header/Cyear/Cmonth/Cdate/Chour/Cminute/Csecond/CerrorCode", $this->data);
                $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 9, 6)));
                $msg = "DAIP ACK: ".$timestamp->format("Y-m-d H:i:s").
                    " AckFlags:".$wrapper["messageFlags"].
                    " AckCtr:".$ackCounter.
                    " MsgRef:".$messageCounterReference.
                    " SVID:".$this->sessionVehicleIdentifier;
                $this->log->info($msg);
                break;

            case DAIP_LOG_ON_REQUEST: 
                $this->log->info("Log On Request");
                if ($this->optionalDataFields == 0)
                {
                    $this->messageContent = unpack("C9header/CmessageID/a9operatorID/a7vehicleID", $this->data);
                    $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 26)), new DateTimeZone("GMT"));
                }
                else if ($this->optionalDataFields == 49152) // 11000000 00000000
                {
                    $this->messageContent = unpack("C9header/CmessageID/a9operatorID/a7vehicleID/CobuIdLength", $this->data);
                    $obuIdLen = $this->messageContent["obuIdLength"];
                    $this->log->debug($this->peer->ip . " OBU ID Length (1 byte): " . $obuIdLen);
                    $optFields = unpack("C27leader/a" . $obuIdLen . "obuId", $this->data);
                    $this->log->debug($this->peer->ip . " OBU ID (" . $obuIdLen . " bytes): " . $optFields["obuId"]);
                    $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 27 + $obuIdLen)), new DateTimeZone("GMT"));
                }
                else
                {
                    $this->log->debug("process() Failed to parse optionalDataFields");
                    break;
                }
                $this->log->debug($this->peer->ip . " Timestamp (6 bytes): " . $timestamp->format("Y-m-d H:i:s"));

                // Avoid responding to buffered messages
                global $taskStartTime;
                if ($timestamp < $taskStartTime)
                {
                    $this->log->warn("process() Dropping buffered Log On Request message");
                    break;
                }

                $logonrequest = new DAIPLogOnRequest($context, $this->peer, $messageCounterReference,
                    $this->messageContent["operatorID"],
                    $this->messageContent["vehicleID"],
                    $timestamp);
                $logonrequest->process();
                break;

            case DAIP_LOG_OFF_REQUEST: 
                $this->log->info("Log Off Request");

                $this->messageContent = unpack("C9header/CmessageID/CsessionIDFirst/CsessionIDSecond/Cyear/Cmonth/Cdate/Chour/Cminute/Csecond", $this->data);

                // Session Vehicle Identifier is repeated in payload of a Log
                // Off Request message. If the one in the payload doesn't match
                // the one in the wrapper then give a negative acknowledgement.
                $sessionVehicleIdentifier = ($this->messageContent["sessionIDFirst"] << 8) + $this->messageContent["sessionIDSecond"];
                if ($sessionVehicleIdentifier != $this->sessionVehicleIdentifier)
                {
                    $this->log->warn("Session Vehicle Identifier in message payload $sessionVehicleIdentifier does not match the one in message wrapper " . $this->sessionVehicleIdentifier);
                    $this->acknowledgementFlag = false;
                }
                else
                {
                    $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 12)));

                    $logoffrequest = new DAIPLogOffRequest($context, $this->peer, $messageCounterReference,
                        $sessionVehicleIdentifier,
                        $timestamp);

                    $logoffrequest->process();
                }

                $this->sendAcknowledgement($messageCounterReference, $sessionVehicleIdentifier, $this->acknowledgementFlag);
                break;

            case DAIP_JOURNEY_DETAILS:
                $this->log->info("Journey Details");
                $context->ackRequired = true;

                $this->messageContent = unpack("C9header/CmessageID/a6serviceCode/a7runningBoard/a5journeyNumber/CscheduledStartHour/CscheduledStartMinute/a6dutyNumber/a6publicServiceCode/Cdirection/a4depotCode/a6driverCode/a12firstStopID/a12destinationStopID", $this->data);
                $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 77)));
                $scheduled_start = bin2hex(substr($this->data, 28, 1)) . ":" . bin2hex(substr($this->data, 29, 1));

                $journeydetails = new DAIPJourneyDetails($context, $this->peer, $messageCounterReference, $this->sessionVehicleIdentifier,
                    $this->messageContent["serviceCode"],
                    $this->messageContent["runningBoard"],
                    $this->messageContent["journeyNumber"],
                    $scheduled_start,
                    $this->messageContent["dutyNumber"],
                    $this->messageContent["publicServiceCode"],
                    $this->messageContent["direction"],
                    $this->messageContent["depotCode"],
                    $this->messageContent["driverCode"],
                    $this->messageContent["firstStopID"],
                    $this->messageContent["destinationStopID"],
                    $timestamp);
                $journeydetails->process();
                break;

            case DAIP_JOURNEY_DETAILS_BASIC:
                $this->log->info("Journey Details Basic");
                $context->ackRequired = true;

                $this->messageContent = unpack("C9header/CmessageID/C6serviceCode/C7runningBoard/C5journeyNumber/CscheduledStartHour/CscheduledStartMinute/C6dutyNumber/C6publicServiceCode/Cdirection", $this->data);
                $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 43)));

                $journeydetails = new DAIPJourneyDetails($context, $this->peer, $messageCounterReference,
                    $this->messageContent["serviceCode"],
                    $this->messageContent["runningBoard"],
                    $this->messageContent["journeyNumber"],
                    $this->messageContent["scheduledStartHour"] . $this->messageContent["scheduledStartMinute"],
                    $this->messageContent["dutyNumber"],
                    $this->messageContent["publicServiceCode"],
                    $this->messageContent["direction"],
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    $timestamp);
                $journeydetails->process();
                break;

            case DAIP_JOURNEY_DETAILS_ENHANCED:
                $this->log->warn("Journey Details Enhanced - not yet specified as of version 1.1, so ignore this");
                break;

            case DAIP_END_OF_JOURNEY:
                $this->log->info("End of Journey");

                $this->messageContent = unpack("C9header/CmessageID/a6serviceCode/a5journeyNumber/CscheduledStartHour/CscheduledStartMinute/a6publicServiceCode/Cdirection", $this->data);
                $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 30)));

                $endofjourney = new DAIPEndOfJourney($context, $this->peer, $messageCounterReference,
                    $this->messageContent["serviceCode"],
                    $this->messageContent["journeyNumber"],
                    $this->messageContent["scheduledStartHour"] . $this->messageContent["scheduledStartMinute"],
                    $this->messageContent["publicServiceCode"],
                    $this->messageContent["direction"],
                    $timestamp);
                $endofjourney->process();

                $this->sendAcknowledgement($messageCounterReference, $this->sessionVehicleIdentifier, $this->acknowledgementFlag);
                break;

            case DAIP_POSITION_UPDATE:
                $this->log->info("Position Update");

                $this->messageContent = unpack("C9header/CmessageID/Nlatitude/Nlongitude/Cbearing/CsatellitesVisible/CmessageQuality/ClastStopID/CdistanceTravelledSinceLastStopFirst/CdistanceTravelledSinceLastStopSecond", $this->data);

                // Latitude and Longitude are stored as milliarcsecs with
                // negative values for south of equator and west of meridian
                $firstbit = ($this->messageContent["latitude"] & 0x80000000) >> 31;
                if ($firstbit == 1)
                    $lat = 0 - (0xFFFFFFFF - $this->messageContent["latitude"]);
                else
                    $lat = $this->messageContent["latitude"];

                if ($lat == 0x7FFFFFFF)
                    $lat = NULL;

                $firstbit = ($this->messageContent["longitude"] & 0x80000001) >> 31;
                if ($firstbit == 1)
                    $lng = 0 - (0xFFFFFFFF - $this->messageContent["longitude"]);
                else
                    $lng = $this->messageContent["longitude"];

                if ($lng == 0x7FFFFFFF)
                    $lng = NULL;

                switch ($this->formatVersion)
                {
                    case "01.00":
                    $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 24)));
                        break;

                    case "01.10":
            echo "11\n";
                    $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 25)));
                        break;

                    default:
                        // Unrecognised version at this point shouldn't be possible as we checked the version above
                        return;
                }

                $positionupdate = DAIPPositionUpdate::createDAIPPositionUpdate($context, $this->peer,
                    $this->sessionVehicleIdentifier,
                    $messageCounterReference,
                    $lat,
                    $lng,
                    $this->messageContent["bearing"] * 2,
                    $this->messageContent["satellitesVisible"],
                    $this->messageContent["messageQuality"],
                    $this->messageContent["lastStopID"],
                    ($this->messageContent["distanceTravelledSinceLastStopFirst"] << 8)
                        + $this->messageContent["distanceTravelledSinceLastStopSecond"],
                    $timestamp);

                if (!$positionupdate)
                    return;

//                $positionupdate->context = $context;
                $positionupdate->process();
                break;

            case DAIP_POSITION_UPDATE_BASIC:
                $this->log->info("Position Update Basic");

                switch ($this->formatVersion)
                {
                    case "01.00":
                        $this->messageContent = unpack("C9header/CmessageID/Nlatitude/Nlongitude/Cbearing", $this->data);
                        $scheduleDeviation = NULL;
                        $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 19)));
                        break;

                    case "01.10":
                        $this->messageContent = unpack("C9header/CmessageID/Nlatitude/Nlongitude/Cbearing/CscheduledDeviation", $this->data);
                        $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, 20)));
                        break;

                    default:
                        // Unrecognised version at this point shouldn't be possible as we checked the version above
                        return;
                }

                // Latitude and Longitude are stored as milliarcsecs with
                // negative values for south of equator and west of meridian
                $firstbit = ($this->messageContent["latitude"] & 0x80000000) >> 31;
                if ($firstbit == 1)
                    $lat = 0 - (0xFFFFFFFF - $this->messageContent["latitude"]);
                else
                    $lat = $this->messageContent["latitude"];

                if ($lat == 0x7FFFFFFF)
                    $lat = NULL;

                $firstbit = ($this->messageContent["longitude"] & 0x80000000) >> 31;
                if ($firstbit == 1)
                    $lng = 0 - (0xFFFFFFFF - $this->messageContent["longitude"]);
                else
                    $lng = $this->messageContent["longitude"];

                if ($lng == 0x7FFFFFFF)
                    $lng = NULL;

                $positionupdate = DAIPPositionUpdate::createDAIPPositionUpdateBasic($context, $this->peer, $messageCounterReference,
                    $lat,
                    $lng,
                    $this->messageContent["bearing"] * 2,
                    $scheduleDeviation,
                    $timestamp);
                $positionupdate->context = $context;
                $positionupdate->process();
                break;

            case DAIP_POSITION_UPDATE_ENHANCED:
                $this->log->warn("Position Update Enhanced - not yet specified as of version 1.1, so ignore this");
                break;

            case DAIP_EVENT_OBU_TO_CENTRE:
                $this->log->info("Event OBU to centre");
                $context->ackRequired = true;
                $native_message_type = 0;

                // Byte 21 will wither be a free text message length or the timestamp depending on
                // whether test message is supplied or not. If message length is exactly 30 bytes then
                // we assume no free text message
                if ($this->length == 30)
                {
                    $timestampPositionIndex = (24);

                    $this->messageContent = unpack("C9header/CmessageID/Cj1/Cj2/Cj3/Cj4/Nlatitude/Nlongitude/CmessageType/CmessageCode", $this->data);
                    $this->messageContent["messageParametersData"] = "No message";
                }
                else
                {
                    $paramLenArr = unpack("C24/CmessageParametersLength", $this->data);
                    $messageParametersLength = $paramLenArr["messageParametersLength"];
                    if ($messageParametersLength > 255)
                    {
                            $this->log->error("Message Parameters Length $messageParametersLength > 255");
                            break;
                    }

                    $timestampPositionIndex = (24 + 1 + $messageParametersLength);

                    // messageParametersData taken as ASCII here but unpacked
                    // differently below depending upon message type
                    $this->messageContent = unpack("C9header/CmessageID/CmessageID/Cj1/Cj2/Cj3/Nlatitude/Nlongitude/CmessageType/CmessageCode/CmessageParametersLength/a" . $messageParametersLength . "messageParametersData", $this->data);
                }

                $timestamp = DateTime::createFromFormat("ymdHis", bin2hex(substr($this->data, $timestampPositionIndex)));

                // Latitude and Longitude are stored as milliarcsecs with
                // negative values for south of equator and west of meridian
                $firstbit = ($this->messageContent["latitude"] & 0x80000000) >> 31;
                if ($firstbit == 1)
                    $lat = 0 - (0xFFFFFFFF - $this->messageContent["latitude"]);
                else
                    $lat = $this->messageContent["latitude"];

                if ($lat == 0x7FFFFFFF)
                    $lat = NULL;

                $firstbit = ($this->messageContent["longitude"] & 0x80000000) >> 31;
                if ($firstbit == 1)
                    $lng = 0 - (0xFFFFFFFF - $this->messageContent["longitude"]);
                else
                    $lng = $this->messageContent["longitude"];

                if ($lng == 0x7FFFFFFF)
                    $lng = NULL;

                $event = new DAIPEventOBUToCentre($context, $this->peer,
                    $this->sessionVehicleIdentifier,
                    $messageCounterReference,
                    ($this->messageContent["sequenceIDFirst"] << 8)
                        + $this->messageContent["sequenceIDSecond"],
                    ($this->messageContent["referenceSequenceIDFirst"] << 8)
                        + $this->messageContent["referenceSequenceIDSecond"],
                    $lat,
                    $lng,
                    $this->messageContent["messageType"],
                    $this->messageContent["messageCode"],
                    $this->messageContent["messageParametersData"],
                    $timestamp);
                
                if (isset($event->daip_vehicle))
                {
                    $event->process();
                    $this->sendAcknowledgement($messageCounterReference, $this->sessionVehicleIdentifier, $this->acknowledgementFlag);
                }
                break;

            case DAIP_EVENT_CENTRE_TO_OBU:
                $this->log->error("Event centre to OBU - shouldn't receive these on the server - sending simulation");
                $this->sendCentreToOBU(127, 1, "Hello");
                break;

            case DAIP_ENQUIRY:
                $this->log->error("Enquiry - shouldn't receive these on the server");
                $this->sendAcknowledgement($messageCounterReference, $this->sessionVehicleIdentifier, $this->acknowledgementFlag);
                break;

            default:
                $this->log->error("Unrecognised Message ID - ignoring");
                break;
        }
        return;
    }

    function sendAcknowledgement($messageCounterReference, $sessionVehicleIdentifier, $acknowledgedFlag)
    {
        global $acknowledgementCounter;

        // Wrapper Header
        $formatVersionFirstByte = 1;
        $formatVersionSecondByte = 16;

        // Acknowledgement Flags: 1 byte, bit-mapped
        // Bit 0 - always 1 "This is an Acknowledgement"
        // Bit 1 - Message Acknowledgement Flag
        //  - 0 = Message is not Acknowledged (Error in message)
        //  - 1 = Message is Acknowledged
        // Bit 2 - Reserved
        // Bit 3 - Live or Test/Evaluation Message
        //  - 0 = Live Message
        //  - 1 = Test Message
        // Bits 4-7 Reserved
        $acknowledgementFlags = 3;    // 00000011 = 3 => live positive acknowledgement

        $acknowledgementCounter++;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        $msg = pack("CCCCCCCCCCCCCCCC",
            $formatVersionFirstByte,
            $formatVersionSecondByte,
            $acknowledgementFlags,
            ($acknowledgementCounter & 0xFF00) >> 8,
            $acknowledgementCounter & 0xFF,
            ($messageCounterReference & 0xFF00) >> 8,
            $messageCounterReference & 0xFF,
            ($sessionVehicleIdentifier & 0xFF00) >> 8,
            $sessionVehicleIdentifier & 0xFF,
            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second),
            0);

        $this->log->debug("DAIPPacket->sendAcknowledgment() TX " . $this->peer->ip . ":" . $this->peer->port . " " . bin2hex($msg));
        socket_sendto($this->peer->socket, $msg, strlen($msg), 0, $this->peer->ip, $this->peer->port);
    }

    function sendEnquiry($socket, $sessid, $ip, $port, $type, $code, $params)
    {
        echo "DAIPPacket->sendEnquiry()\n";

        global $gMessageCounter;
        $gMessageCounter++;

        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 2;
        $messageCounterFirst = ($gMessageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $gMessageCounter & 0xFF;
        $optionalDataFields = 0;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        $scheduled_hour = substr($scheduled_start, 11, 2);
        $scheduled_minute = substr($scheduled_start, 14, 2);

        $params = trim($params);
        $len = strlen($params);
        $this->sessionVehicleIdentifier = $sessid;

        $messageID = 255;

        $optionalDataFields = 0;
        $msg = pack("CCCCCCCCCCCCCCCCCC",
            $formatVersionFirst,
            $formatVersionSecond,
            $messageFlags,
            $messageCounterFirst,
            $messageCounterSecond,
            ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
            $this->sessionVehicleIdentifier & 0xFF,
            ($optionalDataFields & 0xFF00) >> 8,
            $optionalDataFields & 0xFF,

            $messageID,
            ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
            $this->sessionVehicleIdentifier & 0xFF,

            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second));

        echo "Centre To OBU TX " . bin2hex($msg) . "\n";
        socket_sendto($socket, $msg, strlen($msg), 0, $ip, 5000); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        //socket_recvfrom($socket, $buf, 512, 0, $from, $port);
        //echo "RX " . bin2hex($buf) . "\n";
    }

    function sendNonAcknowledgement($messageCounterReference, $sessionVehicleIdentifier, $acknowledgedFlag)
    {
        global $acknowledgementCounter;

        // Wrapper Header
        $formatVersionFirstByte = 1;
        $formatVersionSecondByte = 16;

        // Acknowledgement Flags: 1 byte, bit-mapped
        // Bit 0 - always 1 "This is an Acknowledgement"
        // Bit 1 - Message Acknowledgement Flag
        //  - 0 = Message is not Acknowledged (Error in message)
        //  - 1 = Message is Acknowledged
        // Bit 2 - Reserved
        // Bit 3 - Live or Test/Evaluation Message
        //  - 0 = Live Message
        //  - 1 = Test Message
        // Bits 4-7 Reserved
        $acknowledgementFlags = 1;    // 00000011 = 3 => live positive acknowledgement

        $acknowledgementCounter++;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        $msg = pack("CCCCCCCCCCCCCCCC",
            $formatVersionFirstByte,
            $formatVersionSecondByte,
            $acknowledgementFlags,
            ($acknowledgementCounter & 0xFF00) >> 8,
            $acknowledgementCounter & 0xFF,
            ($messageCounterReference & 0xFF00) >> 8,
            $messageCounterReference & 0xFF,
            ($sessionVehicleIdentifier & 0xFF00) >> 8,
            $sessionVehicleIdentifier & 0xFF,
            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second),
        $acknowledgedFlag);

        echo "Centre To OBU TX " . bin2hex($msg) . "\n";
        socket_sendto($socket, $msg, strlen($msg), 0, $ip, 5000); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        //socket_recvfrom($socket, $buf, 512, 0, $from, $port);
        //echo "RX " . bin2hex($buf) . "\n";
    }

    function sendCentreToOBU($socket, $sessid, $ip, $port, $type, $code, $dest, $params)
    {
        global $gMessageCounter;
        $gMessageCounter++;

        echo "DAIPPacket->sendCentreToOBU()\n";

        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 2;
        $messageCounterFirst = ($gMessageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $gMessageCounter & 0xFF;
        $optionalDataFields = 0;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        $scheduled_hour = substr($scheduled_start, 11, 2);
        $scheduled_minute = substr($scheduled_start, 14, 2);

        $params = trim($params);
        $len = strlen($params);
        $this->sessionVehicleIdentifier = $sessid;

        $messageID = 60;

        if ($params)
        {
            $optionalDataFields = 0xC000; // 1100000000000000 => 2 optional data fields

            switch ($type)
            {
                case 3: // Notification
                    if ($code == 0) // Error Notification
                    {
                        $this->log->debug("sendCentreToOBU() Error Notification with Error Number 1");
                        $len = 1;

                        $msg = pack("CCCCCCCCCCCCSCCCCa${len}CCCCCC",
                        $formatVersionFirst,
                        $formatVersionSecond,
                        $messageFlags,
                        $messageCounterFirst,
                        $messageCounterSecond,
                        ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
                        $this->sessionVehicleIdentifier & 0xFF,
                        ($optionalDataFields & 0xFF00) >> 8,
                        $optionalDataFields & 0xFF,
                        $messageID,
                        ($gMessageCounter & 0xFF00) >> 8,    // Sequence ID
                        $gMessageCounter & 0xFF,
                        0, // Reference Sequence ID
                        $type, // Message Type
                        $code, // Message Code
                        $len, // Message Parameters Length (Optional)
                        1, // Error Number TODO configurable error number
                        hexdec($year),
                        hexdec($month),
                        hexdec($date),
                        hexdec($hour),
                        hexdec($minute),
                        hexdec($second)
                        );
                    }
                    else
                        $this->log->error("sendCentreToOBU() invalid Notification message code $code");

                case 127: // Free Format Text Messages
                    if ($code == 0) // Acknowledge Receipt of Outgoing Message
                    {
                        echo "#60 type 127 code 0 TODO Acknowledge Receipt of Outgoing Message\n";
                    }
                    else if ($code == 1) // Text String #1
                    {
                        $msg = pack("CCCCCCCCCCCCSCCCCa${len}CCCCCC",
                        $formatVersionFirst,
                        $formatVersionSecond,
                        $messageFlags,
                        $messageCounterFirst,
                        $messageCounterSecond,
                        ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
                        $this->sessionVehicleIdentifier & 0xFF,
                        ($optionalDataFields & 0xFF00) >> 8,
                        $optionalDataFields & 0xFF,
                        $messageID,
                        ($gMessageCounter & 0xFF00) >> 8, // Sequence ID
                        $gMessageCounter & 0xFF,
                        0, // Reference Sequence ID
                        $type, // Message Type
                        $code, // Message Code
                        $len + 1, // Message Parameters Length (Optional)
                        $dest, // Destination
                        $params, // Message Parameters Data (Optional)
                        hexdec($year),
                        hexdec($month),
                        hexdec($date),
                        hexdec($hour),
                        hexdec($minute),
                        hexdec($second)
                        );
                        break;
                    }
                    else if ($code == 2) // Predefined message
                    {
                        $len = 2;

                        $msg = pack("CCCCCCCCCCCCSCCCCCCCCCCC",
                        $formatVersionFirst,
                        $formatVersionSecond,
                        $messageFlags,
                        $messageCounterFirst,
                        $messageCounterSecond,
                        ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
                        $this->sessionVehicleIdentifier & 0xFF,
                        ($optionalDataFields & 0xFF00) >> 8,
                        $optionalDataFields & 0xFF,
                        $messageID,
                        ($gMessageCounter & 0xFF00) >> 8,    // Sequence ID
                        $gMessageCounter & 0xFF,
                        0, // Reference Sequence ID
                        $type, // Message Type
                        $code, // Message Code
                        $len, // Message Parameters Length (Optional)
                        1, // Predefined message to be displayed TODO configurable message codes
                        $dest, // Destination
                        hexdec($year),
                        hexdec($month),
                        hexdec($date),
                        hexdec($hour),
                        hexdec($minute),
                        hexdec($second)
                        );
                        break;
                    }
                    else
                        $this->log->error("sendCentreToOBU() invalid Free Format Text Messages message code $code");

                    break;

                default:
                    $msg = pack("CCCCCCCCCCCCSCCCa${len}CCCCCC",
                        $formatVersionFirst,
                        $formatVersionSecond,
                        $messageFlags,
                        $messageCounterFirst,
                        $messageCounterSecond,
                        ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
                        $this->sessionVehicleIdentifier & 0xFF,
                        ($optionalDataFields & 0xFF00) >> 8,
                        $optionalDataFields & 0xFF,
                        $messageID,
                        ($gMessageCounter & 0xFF00) >> 8,    // Sequence ID
                        $gMessageCounter & 0xFF,
                        0, // Reference Sequence ID
                        $type, // Message Type
                        $code, // Message Code
                        $len, // Message Parameters Length (Optional)
                        $params, // Message Parameters Data (Optional)
                        hexdec($year),
                        hexdec($month),
                        hexdec($date),
                        hexdec($hour),
                        hexdec($minute),
                        hexdec($second)
                    );
                    break;
            }
        }
        else
        {
            $msg = pack("CCCCCCCCCCCSSCCCCCCCC",
            $formatVersionFirst,
            $formatVersionSecond,
            $messageFlags,
            $messageCounterFirst,
            $messageCounterSecond,
            ($this->sessionVehicleIdentifier & 0xFF00) >> 8,
            $this->sessionVehicleIdentifier & 0xFF,
            ($optionalDataFields & 0xFF00) >> 8,
            $optionalDataFields & 0xFF,

            $messageID,
            $gMessageCounter,
            0,
            $type,
            $code,

            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second));
        }

        echo "Centre To OBU TX " . bin2hex($msg) . "\n";
        socket_sendto($socket, $msg, strlen($msg), 0, $ip, 5000); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        //socket_recvfrom($socket, $buf, 512, 0, $from, $port);
        //echo "RX " . bin2hex($buf) . "\n";
    }

}

?>
