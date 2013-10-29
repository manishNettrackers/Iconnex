<?php

global $gMessageCounter;
$gMessageCounter = 0;

class DAIPVehicleSimulator
{
    private $sessionVehicleIdentifier = 0;
    private $socket = NULL;
    private $messageCounter = 1;
    private $timetable_journey = NULL;
    private $timetable_journey_live = NULL;
    private $vehicle = NULL;
    
    function __construct()
    {
        if (!($this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
            echo "Failed to create socket\n";
    }

    function logon()
    {
        echo "DAIPVehicleSimulator->logon()\n";
        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 0;
        $messageCounterFirst = ($this->messageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $this->messageCounter & 0xFF;
        $sessionVehicleIdentifier = 0;
//        $optionalDataFields = 0;
        $optionalDataFields = 49152; // 11000000 00000000

        // Log On Message
        $messageID = 10;
        $operatorCodeFirst = 0x41;
        $operatorCodeSecond = 0x53;
        $operatorCodeThird = 0x48;
        $operatorCodeFourth = 0x00;
        $operatorCodeFifth = 0x00;
        $operatorCodeSixth = 0x00;
        $operatorCodeSeventh = 0x00;
        $operatorCodeEighth = 0x00;
        $operatorCodeNinth = 0x00;
        $vehicleCodeFirst = 0x39;
        $vehicleCodeSecond = 0x39;
        $vehicleCodeThird = 0x39;
        $vehicleCodeFourth = 0x39;
        $vehicleCodeFifth = 0x00;
        $vehicleCodeSixth = 0x00;
        $vehicleCodeSeventh = 0x00;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

//        $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC",
        $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC",
            $formatVersionFirst,
            $formatVersionSecond,
            $messageFlags,
            $messageCounterFirst,
            $messageCounterSecond,
            ($sessionVehicleIdentifier & 0xFF00) >> 8,
            $sessionVehicleIdentifier & 0xFF,
            ($optionalDataFields & 0xFF00) >> 8,
            $optionalDataFields & 0xFF,
            $messageID,
            $operatorCodeFirst,
            $operatorCodeSecond,
            $operatorCodeThird,
            $operatorCodeFourth,
            $operatorCodeFifth,
            $operatorCodeSixth,
            $operatorCodeSeventh,
            $operatorCodeEighth,
            $operatorCodeNinth,
            $vehicleCodeFirst,
            $vehicleCodeSecond,
            $vehicleCodeThird,
            $vehicleCodeFourth,
            $vehicleCodeFifth,
            $vehicleCodeSixth,
            $vehicleCodeSeventh,
                4,
                0x31, 0x32, 0x33, 0x34,
            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second));

        echo "TX " . bin2hex($msg) . "\n";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "127.0.0.1", 2065); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        socket_recvfrom($this->socket, $buf, 512, 0, $from, $port);
        echo "RX " . bin2hex($buf) . "\n";
        $response = unpack("C9header/CmessageID/CvehicleFirst/CvehicleSecond", $buf);
        if ($response["messageID"] != 20)
        {
            echo "Got invalid response to logon request\n";
            return;
        }
        $this->sessionVehicleIdentifier = ($response["vehicleFirst"] << 8) + $response["vehicleSecond"];
        echo "DAIPVehicleSimulator->logon() Got SVID " . $this->sessionVehicleIdentifier . "\n";
    }

    function go($running_no)
    {
        echo "DAIPVehicleSimulator->go() for running_no $running_no\n";
        global $rtpiconnector;

        $day = new DateTime();
        $ymd = $day->format("Ymd");

        $sql = "
            select timetable_journey.timetable_id,
                timetable_journey.route_code,
                timetable_journey.running_no,
                timetable_journey.trip_no,
                timetable_journey.start_time,
                timetable_journey.duty_no,
                timetable_journey.direction,
                startloc.location_code start_loc_code,
                endloc.location_code end_loc_code
            from timetable_journey,
                timetable_visit tvdstart,
                timetable_visit tvdend,
                location startloc,
                location endloc
            where date(start_time) = TODAY
            and current between start_time and end_time
            and timetable_journey.running_no = '$running_no'
            and tvdstart.timetable_id = timetable_journey.timetable_id
            and tvdstart.sequence = 1
            and tvdend.timetable_id = timetable_journey.timetable_id
            and tvdend.sequence = timetable_journey.number_stops
            and startloc.location_id = tvdstart.location_id
            and endloc.location_id = tvdend.location_id
            order by start_time";

        $stmt = $rtpiconnector->executeSQL($sql);
        if (!$stmt)
        {
            echo "DAIPVehicleSimulator go() failed to find valid current trip for running_no $running_no\n";
            return false;
        }

        while ($row = $stmt->fetch())
        {
            echo "DAIPVehicleSimulator->go() with timetable_id " . $row["timetable_id"] . "\n";

            $event = new EventJourneyDetails(new DateTime(), new DateTime(), "1011910002", "Vehicle", "1011910002");
            $event->ip_address = "127.0.0.1";
            $event->conn_status = "A";
            $event->service_code = $row["route_code"];
            $event->public_service_code = $row["route_code"];
            $event->running_board = $row["running_no"];
            $event->duty_number = $row["duty_no"];
            $event->journey_number = $row["trip_no"];
            $event->scheduled_start = $row["start_time"];
            $event->direction = $row["direction"];
            $event->depot_code = "BLAH";
            $event->driver_code = "9999";
            $event->first_stop_id = $row["start_loc_code"];
            $event->destination_stop_id = $row["end_loc_code"];

            $timetable_journey = new TimetableJourney($rtpiconnector);
            $timetable_journey->timetable_id = $row["timetable_id"];
            if (!$timetable_journey->load())
            {
                echo "DAIPVehicleSimulator->go() failed to load timetable_journey for timetable_id $timetable_id\n";
                return false;
            }
            $timetable_journey->buildVisitsArray();

            $vehicle = new Vehicle($rtpiconnector);
            $vehicle->vehicle_code = "0000";
            if (!$vehicle->load(array("vehicle_code")))
            {
                echo "DAIPVehicleSimulator->go() failed to load vehicle\n";
                return false;
            }

            $this->timetable_journey_live = new TimetableJourneyLive($rtpiconnector);
            if (!$this->timetable_journey_live->initialise($event, $timetable_journey, $vehicle))
            {
                echo "TimetableJourneyLiveList->getMatchingJourney() failed to initialise timetable_journey_live for event\n";
                return false;
            }

            $this->timetable_journey_live->add();

            if (!$this->timetable_journey_live->buildVisitsArray($timetable_journey))
            {
                echo "TimetableJourneyLiveList->getMatchingJourney() failed buildVisitsArray for timetable_journey_live - not adding to list\n";
                return false;
            }

            $this->journeyDetails($row["route_code"], $row["running_no"], $row["duty_no"], $row["trip_no"], $row["start_time"], $row["direction"], $row["start_loc_code"], $row["end_loc_code"]);

            return ($row["timetable_id"]);
        }
    }

    function journeyDetails($service_code, $running_board, $duty_number, $journey_number, $scheduled_start, $direction, $first_stop_id, $last_stop_id)
    {
        echo "DAIPVehicleSimulator->journeyDetails()\n";
        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 0;
        $messageCounterFirst = ($this->messageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $this->messageCounter & 0xFF;
        $sessionVehicleIdentifier = 0;
        $optionalDataFields = 0;

        // Journey Details
        $messageID = 30;
        $depot_code = "DEPO";
        $driver_code = "SIM";

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

        $msg = pack("CCCCCCCCCCa6a7a5CCa6a6Ca4a6a12a12CCCCCC",
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
            trim($service_code),
            trim($running_board),
            trim($journey_number),
            hexdec($scheduled_hour),
            hexdec($scheduled_minute),
            trim($duty_number),
            trim($service_code),
            $direction,
            trim($depot_code),
            trim($driver_code),
            trim($first_stop_id),
            trim($last_stop_id),
            hexdec($year),
            hexdec($month),
            hexdec($date),
            hexdec($hour),
            hexdec($minute),
            hexdec($second));

        echo "TX " . bin2hex($msg) . "\n";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "127.0.0.1", 2065); 
        $this->messageCounter++;

//        $from = '';
//        $port = 0;
//        socket_recvfrom($this->socket, $buf, 512, 0, $from, $port);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!socket_bind($sock, '0.0.0.0', 5000)) die("ERROR: Unable to bind socket");
        if (!socket_set_block($sock))
        {
            socket_close($sock);
            die('ERROR: Unable to set blocking mode for socket');
        }
        $len = socket_recvfrom($sock, $buf, 65535, 0, $clientIP, $clientPort);
        echo "RX " . bin2hex($buf) . "\n";
        $messageContent = unpack("C2version/CacknowledgementFlags", $buf);
        echo "DAIPVehicleSimulator->journeyDetails() Got acknowledgement flags " . $messageContent["acknowledgementFlags"] . "\n";
    }

    /**
     * Sends a DAIP Enquiry Message to the port
     */
    function Enquiry($vehicle, $type, $code, $params)
    {
        echo "DAIPVehicleSimulator->Enquiry()\n";
        global $rtpiconnector;

        $sess = new DAIPVehicle($rtpiconnector);

        $sessid = $sess->getSessionIdByVehicle($vehicle);
        if (!$sessid)
        {
            echo "Error vehicle session not current for $vehicle\n";
            return false;
        }

        $messageCounterReference = $this->context->messageSequence;
        $sessionVehicleIdentifier = $this->context->originId;
        $msg = pack("a3Na20NNNa100",
            "ENQ",
            $sessid,
            "10.40.0.1",
            5000,
            $type,
            $code,
            $params
            );

        global $DAIPReturnSocket;
        $DAIPReturnSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        echo("TX " . $this->context->sourceAddress . ":" . $this->context->returnPort. " " . bin2hex($msg))."\n";
        socket_sendto($DAIPReturnSocket, $msg, strlen($msg), 0, "10.8.1.254", 2065);
        //socket_sendto($DAIPReturnSocket, $msg, strlen($msg), 0, $this->context->sourceAddress, $this->context->returnPort);
        socket_close($DAIPReturnSocket);
    
        return;
    }

    /**
     * Sends a DAIP Centre To OBU Message to the port
     */
    function CentreToOBU($vehicle, $type, $code, $dest, $params)
    {
        global $rtpiconnector;
    
        $sess = new DAIPVehicle($rtpiconnector);

        $sessid = $sess->getSessionIdByVehicle($vehicle);
        if (!$sessid)
        {
            echo "Error vehicle session not current for $vehicle\n";
            return false;
        }

        $messageCounterReference = $this->context->messageSequence;
        $sessionVehicleIdentifier = $this->context->originId;
        $msg = pack("a3Na20NNNNa100",
            "DRM",
            $sessid,
            "10.40.0.1",
            5000,
            $type,
            $code,
            $dest,
            $params
            );

        global $DAIPReturnSocket;
        $DAIPReturnSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        echo("TX " . $this->context->sourceAddress . ":" . $this->context->returnPort. " " . bin2hex($msg))."\n";
        socket_sendto($DAIPReturnSocket, $msg, strlen($msg), 0, "10.8.1.254", 2065);
        //socket_sendto($DAIPReturnSocket, $msg, strlen($msg), 0, $this->context->sourceAddress, $this->context->returnPort);
        socket_close($DAIPReturnSocket);
    
        echo "PPP DAIPVehicleSimulator->CentreToOBU() return\n";
        return;

        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 0;
        $messageCounterFirst = ($this->messageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $this->messageCounter & 0xFF;
        $sessionVehicleIdentifier = $this->sessionVehicleIdentifier;
        if ($sessionVehicleIdentifier == 0)
            $sessionVehicleIdentifier = 1479;

        $optionalDataFields = 0;

        // Journey Details
        $messageID = 60;

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

        $len = strlen($params);
        global $gMessageCounter;
        $gMessageCounter = 0;
        $sessionVehicleIdentifier = $this->sessionVehicleIdentifier;
        if ($sessionVehicleIdentifier == 0)
            $sessionVehicleIdentifier = 1479;

        if ($params)
        {
            $msg = pack("CCCCCCCCCCSSCCCa${len}CCCCCC",
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
                strlen($params),
                $params,

                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        }
        else
        {
echo "NOP\n";
            $msg = pack("CCCCCCCCCCSSCCCCCCCCC",
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
        echo "Centre2OBU TX " . bin2hex($msg) . "\n";
        echo "SENDING $msg ";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "10.40.0.1", 5000); 
        $this->messageCounter++;

        //$from = '';
        //$port = 0;
        //socket_recvfrom($this->socket, $buf, 512, 0, $from, $port);
        //echo "RX OBU2Centre" . bin2hex($buf) . "\n";
    }

    /**
     * Sends a DAIP OBU to Centre Message to the port
     */
    function OBUToCentre($type, $code, $params = false)
    {
        echo "DAIPVehicleSimulator->OBUToCentre()\n";
        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 0;
        $messageCounterFirst = ($this->messageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $this->messageCounter & 0xFF;
        $sessionVehicleIdentifier = $this->sessionVehicleIdentifier;
        if ($sessionVehicleIdentifier == 0)
            $sessionVehicleIdentifier = 1479;
        $optionalDataFields = 0;

        // Journey Details
        $messageID = 50;

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

        $len = strlen($params);
        if ($params)
            $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCa${len}CCCCCC",
                $formatVersionFirst,
                $formatVersionSecond,
                $messageFlags,
                $messageCounterFirst,
                $messageCounterSecond,
                ($sessionVehicleIdentifier & 0xFF00) >> 8,
                $sessionVehicleIdentifier & 0xFF,
                ($optionalDataFields & 0xFF00) >> 8,
                $optionalDataFields & 0xFF,

                $messageID,
                0,
                0,
                0,
                0,
                0x0b,
                0xb3,
                0x85,
                0xe7,
                0xff,
                0xac,
                0x57,
                0xc0,

                $type,
                $code,
                strlen($params),
                $params,

                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        else
            $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCC",
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

                0x0b,
                0xb3,
                0x85,
                0xe7,
                0xff,
                0xac,
                0x57,
                0xc0,

                $type,
                $code,

                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));

        echo "DAIPVehicleSimulator->OBUToCentre() TX " . bin2hex($msg) . "\n";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "127.0.0.1", 2065); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        socket_recvfrom($this->socket, $buf, 512, 0, $from, $port);
        echo "DAIPVehicleSimulator->OBUToCentre() RX " . bin2hex($buf) . "\n";
    }

    /**
     * Sends a DAIP Position Update message to the DAIP socket
     *
     * If no gps_position is specified, a hard-coded one is used.
     * If one gps_position is specified, that one is used.
     * If two are specified, a point half way between them is used.
     */
    function positionUpdate($gps_position = NULL, $gps_position2 = NULL)
    {
        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        if (!$gps_position)
        {
            $sessionVehicleIdentifier = $this->sessionVehicleIdentifier;
            if ($sessionVehicleIdentifier == 0)
                $sessionVehicleIdentifier = 1479;

            $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCCCCCC",
                0x01,
                0x00,
                0x00,
                ($this->messageCounter & 0xFF00) >> 8,
                $this->messageCounter & 0xFF,
                ($sessionVehicleIdentifier & 0xFF00) >> 8,
                $sessionVehicleIdentifier & 0xFF,
                0x00,
                0x00,
                0x28,
                0x0b,
                0xb3,
                0x85,
                0xe7,
                0xff,
                0xac,
                0x57,
                0xc0,
                0xa2,
                0x08,
                0x01,
                0x0a,
                0x00,
                0xff,
                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        }
        else
        {
            $sessionVehicleIdentifier = $this->sessionVehicleIdentifier;
            if ($sessionVehicleIdentifier == 0)
                $sessionVehicleIdentifier = 1479;

            $pos = $gps_position;
            if ($gps_position2)
                $pos = $gps_position->getMidPoint($gps_position2);

            echo "DAIPVehicleSimulator->positionUpdate() "
                . " lat " . $pos->latitude_milliarcsecs
                . " lng " . $pos->longitude_milliarcsecs
                . " str " . $pos->plottableString . "\n";

            $last_lng_byte = $pos->longitude_milliarcsecs & 0xFF;
            if ($pos->longitude_milliarcsecs < 0)
                $last_lng_byte--;
            $last_lat_byte = $pos->latitude_milliarcsecs & 0xFF;
            if ($pos->latitude_milliarcsecs < 0)
                $last_lat_byte--;

            $msg = pack("CCCCCCCCCCCCCCCCCCCCCCCCCCCCCC",
                0x01, 0x00, // format version no.
                0x00, // message flags
                ($this->messageCounter & 0xFF00) >> 8, $this->messageCounter & 0xFF,
                ($sessionVehicleIdentifier & 0xFF00) >> 8, $sessionVehicleIdentifier & 0xFF,
                0x00, 0x00, // optional data fields
                0x28, // message type
                ($pos->latitude_milliarcsecs & 0xFF000000) >> 24, // latitude in milliarcsecs
                ($pos->latitude_milliarcsecs & 0xFF0000) >> 16,
                ($pos->latitude_milliarcsecs & 0xFF00) >> 8,
//                ($pos->latitude_milliarcsecs & 0xFF),
                $last_lat_byte,
                ($pos->longitude_milliarcsecs & 0xFF000000) >> 24, // longitude in milliarcsecs
                ($pos->longitude_milliarcsecs & 0xFF0000) >> 16,
                ($pos->longitude_milliarcsecs & 0xFF00) >> 8,
//                ($pos->longitude_milliarcsecs & 0xFF),
                $last_lng_byte,
                0xa2, // bearing
                0x08, // number of satellites visible
                0x01, // position quality
                0x0a, // last stop index
                0x00, 0xff, // distance travelled to stop
                hexdec($year), // message timestamp
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        }

        echo "TX " . bin2hex($msg) . "\n";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "127.0.0.1", 2065); 
        $this->messageCounter++;
    }

    function logoff()
    {
        // Wrapper Header
        $formatVersionFirst = 1;
        $formatVersionSecond = 16;
        $messageFlags = 0;
        $messageCounterFirst = ($this->messageCounter & 0xFF00) >> 8;
        $messageCounterSecond = $this->messageCounter & 0xFF;
        $optionalDataFields = 0;

        // Log Off Message
        $messageID = 11;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

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

        echo "Logoff TX " . bin2hex($msg) . "\n";
        socket_sendto($this->socket, $msg, strlen($msg), 0, "127.0.0.1", 2065); 
        $this->messageCounter++;

        $from = '';
        $port = 0;
        socket_recvfrom($this->socket, $buf, 512, 0, $from, $port);
        echo "Logoff RX " . bin2hex($buf) . "\n";
    }

    function positionSequence($timetable_id)
    {
        echo "DAIPVehicleSimulator->positionSequence()\n";
        $this->timetable_journey_live->show();
        foreach ($this->timetable_journey_live->visits as $sequence => $tvl)
        {
            if (!$tvl->prev_visit)
                $this->positionUpdate($tvl->gps_position);
            else
            {
                $this->positionUpdate($tvl->prev_visit->gps_position, $tvl->gps_position);
                sleep(10);
                $this->positionUpdate($tvl->gps_position);
            }

            sleep(10);
        }
        return true;
    }
}

?>
