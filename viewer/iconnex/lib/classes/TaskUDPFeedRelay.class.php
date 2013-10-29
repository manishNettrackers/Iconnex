<?php

/**
** Class: TaskEventHandler
** --------------------------------
**
** Receives evetn messages from external sources ( DAIP / UDP messsages etc )
** and processes them by updating the database
*/

class TaskUDPFeedRelay extends ScheduledTask
{
    private $udpPort = false;
    private $event_handler = false;

    public $data;
    public $length;
    public $wrapper_type;
    public $operator_id;
    public $operator_code;
    public $build_type;
    public $sender;
    public $sendtime;
    public $socket;
    public $address;
    public $messageContent;
    public $last_gps_fact_real_time_housekeeping = 0;

    public $structs = array (
        "2" => "SmessageType/cprojectId/ccustomerId/ImessageId/Ijunk/IinnerType/IsendTime/Isender/A*message_body",
        "7" => "SmessageType/cprojectId/ccustomerId/SmessageId/cackRequired/cnetworkId/Sjunk/lsender/A256message_body",
        "8" => "SmessageType/SmessageId/cackRequired/cnetworkId/Sjunk/lsender/A20senderAddress/SprojectCode/ScustomerCode/A256message_body",
        "240" => "SmessageType/Caction/Cdirection/A4routeCode/idriverNumber/SdutyNumber/SrunningNumber/StripNumber/SlocationCode/ltimeRouteStarted/SvehicleCode/SsendTimeAddOn/SarrTimeAddOn/ScurrentLateness/dgpslat/dgpslong",
        "121" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "113" => "SmessageType/Sjunk/lbuild/ltimeSent/a20software_version",
        "107" => "SmessageType/Sjunk/ltimeSent",
        "456" => "SmessageType/Sjunk/lunitId/IdcdMessageId",
        "458" => "SmessageType/Sjunk/lunitId/IdcdMessageId",
        "108" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "140" => "SmessageType/Sjunk/ltimeSent/Sin/Sout/StotalIn/StotalOut/Ioccupancy/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds"
        );



    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        // Prepare Object
        $this->msg_queue_key = $msg_queue_key;
        $this->active_list = new ActiveList();
        $this->tj_list = new TimetableJourneyList();
        $this->tjl_list = new TimetableJourneyLiveList();

        // Prepare Connection
        $this->connector->setDirtyRead();
        $this->connector->executeSQL("SET LOCK MODE TO WAIT 10");

        // Get outbound delivery queue
        $this->getUDPListener();

        // Get inbound event handler
        $this->event_handler = SystemKey::getInboundQueue($this->connector);

        // Create RTPI UDP receiver socker
        $this->socket = stream_socket_server("udp://0.0.0.0:".$this->udpPort, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$this->socket) {
            die("$errstr ($errno)");
        }

        /*
        ** Handle Events
        */
        $this->handleEvents();

    }

    /*
    ** getUDPListenerPort
    **
    ** Fetches udp port to listen for UDP messages on
    */
    function getUDPListener()
    {
        $this->udpPort = SystemKey::getUDPListenerPort($this->connector);
        if ( !$this->udpPort )
        {
            echo "UDP Listener Port not defined for message delivery - finishing\n";
            die;
        }
    }

    /*
    ** Sets packet variables to blank
    */
    function initializePacket()
    {
        $this->wrapper_type = false;
        $this->sender = false;
        $this->address = false;
    }

    /*
    ** Reads first few bytes to identify message type and sender
    */
    function processServerJSONMessage($message)
    {
        $txt = $message["message_body"];
        $txt = preg_replace("/}.*/", "}", $txt);
        echo $txt."\n";
        $this->messageContent = json_decode($txt);
        $this->this->connector->import_gps_route_status($this);
    }

    /*
    ** Reads first few bytes to identify message type and sender
    */
    function identifyData()
    {
        $retval = false; 

        $this->initializePacket();

        $exploded = unpack("SmessageType", $this->data);

        // Find out the wrapper type
        $messageBody = false;
        $this->wrapper_type = $exploded["messageType"];
        //echo "Wrapper Type : ".$this->wrapper_type."<BR>";
        switch ( $exploded["messageType"] )
        {
            // Message from Route Tracker
            case 2: 
            {
                $exploded = unpack($this->structs[$exploded["messageType"]], $this->data);
                $this->sender = $exploded["sender"];
                $this->sendtime = $exploded["sendTime"];

                if ( $this->this->connector->this->connector )
                	if ( !$this->this->connector->this->connector->getOperatorFromBuildCode($this->sender, $this->operator_id, $this->operator_code, $this->build_type) )
                	{
		
                    	echo "Failed to get operator for build $this->sender \n";
                    	return;
                	}
                $this->address = "N/A";
                $this->processServerJSONMessage($exploded);
                return;
                break;
            }

            // Message from Field unit
            case 7: 
            {
                $exploded = unpack($this->structs[$exploded["messageType"]], $this->data);
                $this->sender = $exploded["sender"];
                if ( $this->this->connector->this->connector && !$this->this->connector->this->connector->getOperatorFromBuildCode($this->sender, $this->operator_id, $this->operator_code, $this->build_type) )
                {
                    echo "Failed to get operator for build $this->sender \n";
                    return;
                }
                $this->address = $exploded["senderAddress"];
                break;
            }

            // Message from bus/stop forwarded from RT server
            case 8: 
            {
                $exploded = unpack($this->structs[$exploded["messageType"]], $this->data);
                $this->sender = $exploded["sender"];
                if ( $this->this->connector->this->connector && !$this->this->connector->this->connector->getOperatorFromBuildCode($this->sender, $this->operator_id, $this->operator_code, $this->build_type) )
                {
                    echo "Failed to get operator for build $this->sender \n";
                    return;
                }
                $this->address = $exploded["senderAddress"];
                break;
            }

            default:
                $exploded = array ( "message_body" => $this->data );
        }


        // Now extract the main message
        $currentTime = UtilityDateTime::currentTime();
        $this->messageContent = unpack("SmessageType/nint", $exploded["message_body"]);
        //echo "Wrapper: $this->wrapper_type /  ".$this->messageContent["messageType"]." From $this->operator_code $this->build_type: $this->sender / $this->address \n";

        // Strip out weird ip characters
        for ( $ct = 0; $ct < strlen($this->address); $ct++ )
        {   
            $ch = substr($this->address, $ct, 1);
            if ( !preg_match("/[\.0-9]/", $ch ) )
            {   
                $this->address = substr($this->address, 0, $ct + 1);
                //echo "\nbad $this->address! $ch $ct\n";
            }
        }


        switch ( $this->messageContent["messageType"] )
        {
            case 456: 
            case 458: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->address = trim($this->address);

                $timestamp = new DateTime();
                //$timestamp->setTimestamp($this->messageContent["timeSent"]);

                printf("$currentTime Sign Message ACK: ".$this->messageContent["messageType"]." - $this->sender ".$timestamp->format("Y-m-d H:i:s")."\n");
                $event = new EventSignMessageAck(new DateTime(), $timestamp, $this->sender, "Stop", $this->sender);
                $event->ip_address = $this->address;
                $event->message_type = $this->messageContent["messageType"];
                $event->timestamp = $timestamp;
                $event->ack_message_id = $this->messageContent["dcdMessageId"];

                if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to this->event_handler message queue");
                break;
            }
                
            case 113: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->address = trim($this->address);

                $timestamp = new DateTime();
                $timestamp->setTimestamp($this->messageContent["timeSent"]);

                $event = new EventStopInitialise(new DateTime(), $timestamp, $this->sender, "Stop", $this->sender);
                $event->ip_address = $this->address;
                $event->conn_status = "A";
                $event->message_type = $this->messageContent["messageType"];
                printf("$currentTime Bus Stop start up: $this->sender ".$timestamp->format("Y-m-d H:i:s")." $this->address  Type event->message_type "."\n");
                $event->timestamp = $timestamp;
                $event->software_version = $this->messageContent["software_version"];

                if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to this->event_handler message queue");
                break;
            }
                
            case 107: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->address = trim($this->address);

                $timestamp = new DateTime();
                $timestamp->setTimestamp($this->messageContent["timeSent"]);

                $event = new EventHeartbeat(new DateTime(), $timestamp, $this->sender, "Stop", $this->sender);
                $event->ip_address = $this->address;
                printf("$currentTime Bus Stop heart beat: $this->sender ".$timestamp->format("Y-m-d H:i:s")." $this->address  Type event->message_type "."\n");
                $event->conn_status = "A";
                $event->message_type = $this->messageContent["messageType"];
                $event->timestamp = $timestamp;

                if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to this->event_handler message queue");
                break;
            }
                

            case 240: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                //$this->this->connector->import_gps_240($this);

                //$this->address = "10.0.0.1";
                $this->address = trim($this->address);
                if ( !preg_match("/[0-9]$/", $this->address )) $this->address = substr ( $this->address, 0, strlen($this->address) - 1);
                if ( !preg_match("/[0-9]$/", $this->address )) $this->address = substr ( $this->address, 0, strlen($this->address) - 1);
                if ( !preg_match("/[0-9]$/", $this->address )) $this->address = substr ( $this->address, 0, strlen($this->address) - 1);


                $rt =  trim($this->messageContent["routeCode"]);
                if ( $rt != "2" && $rt != "17" && $rt != "15" )
                break;
                echo "$this->address ".$this->address." $this->sender ".$this->messageContent["routeCode"]." ".$this->messageContent["tripNumber"]."\n";
                $timestamp = new DateTime();
                $timestamp->setTimestamp($this->messageContent["timeRouteStarted"] + $this->messageContent["sendTimeAddOn"]);
                $event = new EventJourneyDetails(new DateTime(), $timestamp, $this->sender, "Vehicle", $this->sender);
                $event->ip_address = $this->address;
                $event->conn_status = "A";
                $event->service_code = $this->messageContent["routeCode"];
                $event->public_service_code = $this->messageContent["routeCode"];
                $event->running_board = $this->messageContent["runningNumber"];
                $event->duty_number = $this->messageContent["dutyNumber"];
                $event->journey_number = $this->messageContent["tripNumber"];
                $event->scheduled_start = false;
                $event->direction = false;
                $event->depot_code = false;
                $event->driver_code = $this->messageContent["driverNumber"];
                $event->first_stop_id = false;
                $event->destination_stop_id = false;

                if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to this->event_handler message queue");

                $gps_position = new GPSPosition();
                $gps_position->latitude =  $this->messageContent["gpslat"];
                $gps_position->longitude =  $this->messageContent["gpslong"];
                $gps_position->bearing = "N";
                $gps_position->gps_time = $timestamp;

                // Build an Event and send it to the EventHandler's message queue.  
                $event = new EventPositionUpdate(new DateTime(), $timestamp, $this->sender, "Vehicle", $this->sender);
                $event->ip_address = $this->address;
                $event->conn_status = "A";
                $event->gps_position = $gps_position;
                if (!msg_send ($this->event_handler, 1, $event, true, true, $msg_err))
                    $log->error("Failed to send event to this->event_handler message queue");
 
                break;
            }

            case 108: 
            case 121: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->this->connector->import_gps_121($this);
                break;
            }

            case 140: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->this->connector->import_gps_140($this);
                break;
            }

            default:
                //echo "Unknown Message Type ".$this->messageContent["messageType"]." \n";
        }

        return $retval;
    }

    /* 
    ** process_packet 
    **
    ** Breaks up a binary message and processes it
    ** 
    */
    function process_packet($data, $len)
    {
        //$packet = new iconnex_packet();
        //$packet->this->connector = $this->connector;
        $this->data = $data;
        $this->length = $len;
        $this->identifyData();
    }

    function housekeeping()
    {
        $now = new DateTime();
        $hhmmss = $now->getTimestamp();
        $ymd = $now->format("Ymd");

        if ( $hhmmss - $this->last_gps_fact_real_time_housekeeping > 3600 )
        {
            $sql = "DELETE FROM gps_fact_real_time WHERE date_id < $ymd - 5";
            $this->connector->executeSQL($sql);
            $this->_last_gps_fact_real_time_housekeeping = $hhmmss;
        }
    }

    /*
    ** Process UDP Event Messages
    */
    function handleEvents()
    {
        $peer = "";
        do {
            $pkt = stream_socket_recvfrom($this->socket, 2048, 0, $peer);
            //    $this->connector->executeSQL("LOCK TABLE timetable_journey IN SHARE MODE");
            $this->connector->executeSQL("BEGIN WORK");
            $this->housekeeping();
            $this->process_packet($pkt, 1025);
            $this->connector->executeSQL("COMMIT WORK");
        } while ($pkt !== false);
    }

    /*
    ** Get access to inbound message queue for reading
    */
    function setInboundQueue()
    {  
        $this->inboundQueue = SystemKey::getInboundQueue($this->connector);
        if ( !$this->inboundQueue )
        {  
            echo "Inbound Queue no defined for message delivery - finishing\n";
            die;
        }
    }

}
?>
