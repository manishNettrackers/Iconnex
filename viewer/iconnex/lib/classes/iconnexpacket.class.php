<?php

require_once("gps.utility.php");

/* 
** process_packet 
**
** Analyses Connexionz RTPI packets received from log files or from GPRS messages
** unpacks them and makes them available for processing
** 
*/
global $lastmessageType;
class iconnexpacket
{   
    public $debug = false;
    public $data;
    public $length;
    public $wrapper_type;
    public $messageType;
    public $operator_id;
    public $operator_code;
    public $vehicle_code;
    public $vehicle_id;
    public $build_code;
    public $sender;
    public $sendtime;
    public $address;
    public $messageContent;
    public $rtpiconnector;
    public $odsconnector;
    public $content;
    public $lastcontent;
    public $lastLogMessageTime = false;
    public $workingContent = array();

    public $messages = array (
           "101" => "CMNO_SETTIME",
           "102" => "CMNO_PINGREQUEST",
           "103" => "CMNO_PINGREPLY",
           "104" => "CMNO_REMOTEREBOOT",
           "105" => "CMNO_INITIALISATION",
           "106" => "CMNO_REGULAR_POLL",
           "107" => "CMNO_HEARTBEAT",
           "108" => "CMNO_GPS_HEARTBEAT_OFFROUTE",
           "109" => "CMNO_GPS_HEARTBEAT_BETWEENSTOPS",
           "110" => "CMNO_GPS_HEARTBEAT_ATSTOP",
           "111" => "CMNO_IDENTITY",
           "112" => "CMNO_GPS_HEARTBEAT_240",
           "114" => "CMNO_DIAG_SERVER_PING_REPLY",
           "115" => "CMNO_SHORT_TEXT",
           "116" => "CMNO_MSG_ACK",
           "117" => "CMNO_GPS_HEARTBEAT_DRIVER_MSG",
           "118" => "CMNO_TASK_DIAG_TEXT",
           "119" => "CMNO_DIAG_SWITCH",
           "120" => "CMNO_NETWORK_HEARTBEAT",
           "121" => "CMNO_NETWORK_HEARTBEAT_GPS",
           "122" => "CMNO_NETWORK_ENTER",
           "123" => "CMNO_NETWORK_EXIT",
           "124" => "CMNO_NETWORK_ACTIVITY",
           "125" => "CMNO_NETWORK_DISASSOCIATE",
           "126" => "CMNO_NETWORK_ASSOCIATE",
           "127" => "CMNO_APM_EXTERNAL_POWER_ON",
           "128" => "CMNO_APM_EXTERNAL_POWER_OFF",
           "129" => "CMNO_APM_POWER_UP",
           "130" => "CMNO_APM_POWER_DOWN_CHARGE",
           "131" => "CMNO_APM_POWER_DOWN_TIME",
           "132" => "CMNO_APM_POWER_STATUS",
           "133" => "CMNO_APM_BATTERIES_UNSTABLE",
           "134" => "CMNO_APM_BATTERIES_STABLE",
           "135" => "CMNO_PEOPLE_COUNT",
           "136" => "CMNO_OCCUPANCY",
           "137" => "CMNO_REPORT_OCCUPANCY",
           "140" => "CMNO_PEOPLE_COUNT_WITH_OCC",
           "201" => "CMNO_STARTROUTE",
           "202" => "CMNO_STARTROUTEREQUEST",
           "203" => "CMNO_ROUTEPINGREPLY",
           "203" => "CMNO_NODE",
           "204" => "CMNO_STOPROUTE",
           "205" => "CMNO_LATENESS",
           "206" => "CMNO_MOVEARRIVED",
           "207" => "CMNO_AUTOLATENESS",
           "208" => "CMNO_ROUTESTATUS",
           "209" => "CMNO_UPDATEROUTE",
           "210" => "CMNO_UPDATEROUTENOTIFY",
           "211" => "CMNO_STOPREPLY",
           "212" => "CMNO_MOVEDEPARTED",
           "213" => "CMNO_CONTINUETRIP",
           "214" => "CMNO_NOTRESTORING",
           "215" => "CMNO_ALWAYSREPORT",
           "216" => "CMNO_NEVERREPORT",
           "217" => "CMNO_ROUTERESTORE",
           "218" => "CMNO_CORRECTTRIP",
           "219" => "CMNO_ATSTOPAUTOLATENESS",
           "221" => "CMNO_SENDSTATUS_ECONOMY",
           "222" => "CMNO_SENDSTATUS_MED",
           "224" => "CMNO_TLP_SCOOT_DETECT",
           "226" => "CMNO_TLP_DETECT",
           "227" => "CMNO_STATION_APP_DETECT",
           "228" => "CMNO_VIRTUAL_POINT_DETECT",
           "229" => "CMNO_TLP_PRIORITY_REQUEST",
           "230" => "CMNO_TRAFFICLIGHT_PREEMPTION",
           "231" => "CMNO_MOVEARRIVING",
           "232" => "CMNO_MOVEARRIVED_JUMP",
           "233" => "CMNO_BUSSTUCKRESYNCH",
           "234" => "CMNO_BUSSTUCKKILL",
           "235" => "CMNO_ROUTEMAPFAIL",
           "236" => "CMNO_ROUTETTBFAIL",
           "237" => "CMNO_ROUTEPRFFAIL",
           "238" => "CMNO_ROUTEVALID",
           "239" => "CMNO_DIVERTMOVEFAIL",
           "240" => "CMNO_SENDSTATUS_240",
           "241" => "CMNO_MOVESTAGEFAIL",
           "242" => "CMNO_SENDSTATUS_242",
           "243" => "CMNO_SENDSTATUS_243",
           "244" => "CMNO_DRIVER_DETAILS",
           "245" => "CMNO_KAR_RADIO_ID",
           "246" => "CMNO_KAR_TEST",
           "247" => "CMNO_INVALIDCONTCORRECT",
           "248" => "CMNO_INVALIDCONTKILL",
           "249" => "CMNO_INVALIDCONTIGNORE",
           "250" => "CMNO_CORRECTCONT",
           "251" => "CMNO_RADIO_CLEARDOWN_REQUEST",
           "252" => "CMNO_SENDSTATUS_252",
           "253" => "CMNO_RADIO_CLEARDOWN_REQUEST_LOG",
           "260" => "CMNO_JUMPDEPARTURE",
           "261" => "CMNO_RESTORED_ARRIVE",
           "262" => "CMNO_RESTORED_DEPART",
           "113" => "BOOTUP",
           "138" => "???",
           "139" => "???",
           "476" => "DISPLAY IMPACT",
           "480" => "COSMOS DISPLAY OFF",
           "481" => "COSMOS DISPLAY OFF",
           "482" => "COSMOS DISPLAY OFF",
           "483" => "COSMOS DISPLAY OFF",
           "484" => "COSMOS DISPLAY ON",
           "485" => "COSMOS MANUAL OFF",
           "486" => "COSMOS MANUAL ON",
           "493" => "DISPLAY ONLINE",
           "494" => "DISPLAY OFFLINE",
           "513" => "???",
           "606" => "???",
           "805" => "GPRS SIM ID",
           "860" => "???",
           "861" => "???",
           "908" => "TIDYUP FAIL",
           "909" => "TIDYUP OK",
           "910" => "TIDYUIP START",
           "801" => "CMNO_GPRS_PPP_ATTACH",
           "802" => "CMNO_GPRS_PPP_FAIL",
           "803" => "CMNO_GPRS_CONNECT_FAIL",
           "804" => "CMNO_GPRS_PPP_RECONNECT",
           "806" => "CMNO_GPRS_QUIESCENT",
           "901" => "CMNO_STOPTASK",
           "902" => "CMNO_STARTTASK",
           "903" => "CMNO_GETTASKINFO",
           "904" => "CMNO_TASK_SUSPEND",
           "905" => "CMNO_JAVA_SUSPEND",
           "906" => "CMNO_TASK_INACTIVE",
           "907" => "CMNO_JAVA_ABSENT",
           "908" => "CMNO_TIDYUP_FAIL",
           "911" => "CMNO_TIDYUP_MOVE_FAIL",
           "912" => "CMNO_LOST_CONNECTIVITY"
            );

    const STRUCT_GPS_HEARTBEAT = "snetworkId/itimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds/cactualEstimate/c7padding/dspeed/igps_time/c4morepadding";

    const STRUCT_NETWORK_HEARTBEAT = "c2padding/itimeSent/c32essid/c20macAddress/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds/cactualEstimate/c3morepadding";

    const STRUCT_SHORT_TEXT = "c2padding/lunitId/itimeSent/c30textMessage/c2morepadding";
    const STRUCT_GPS_TEXT = "c2padding/lunitId/itimeSent/c30textMessage/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds/cactualEstimate/cmorepadding";

    const LEN_GPS_HEARTBEAT = 38;
    const LEN_NETWORK_HEARTBEAT = 70;
    const LEN_SHORT_TEXT = 42;
    const LEN_GPS_TEXT = 50;

    public $structlens = array (
        "111" => 42,
        "122" => iconnexpacket::LEN_GPS_HEARTBEAT,
        "123" => iconnexpacket::LEN_GPS_HEARTBEAT,
        "125" => iconnexpacket::LEN_NETWORK_HEARTBEAT,
        "126" => iconnexpacket::LEN_NETWORK_HEARTBEAT,
        "130" => iconnexpacket::LEN_GPS_TEXT,
        "131" => iconnexpacket::LEN_GPS_TEXT,
        "132" => iconnexpacket::LEN_GPS_TEXT,
        "133" => iconnexpacket::LEN_GPS_TEXT,
        "134" => iconnexpacket::LEN_GPS_TEXT,
        "135" => 22,
        "140" => 26,
        "224" => 22,
        "226" => 38,
        "229" => 54,
        "240" => 46,

        "113" => iconnexpacket::LEN_SHORT_TEXT,
        "138" => iconnexpacket::LEN_SHORT_TEXT,
        "139" => iconnexpacket::LEN_SHORT_TEXT,
        "260" => iconnexpacket::LEN_SHORT_TEXT,
        "476" => 10,
        "480" => iconnexpacket::LEN_SHORT_TEXT,
        "481" => iconnexpacket::LEN_SHORT_TEXT,
        "482" => iconnexpacket::LEN_SHORT_TEXT,
        "483" => iconnexpacket::LEN_SHORT_TEXT,
        "484" => iconnexpacket::LEN_SHORT_TEXT,
        "485" => iconnexpacket::LEN_SHORT_TEXT,
        "486" => iconnexpacket::LEN_SHORT_TEXT,
        "493" => iconnexpacket::LEN_SHORT_TEXT,
        "494" => iconnexpacket::LEN_SHORT_TEXT,
        "513" => iconnexpacket::LEN_SHORT_TEXT,
        "606" => iconnexpacket::LEN_SHORT_TEXT,
        "801" => iconnexpacket::LEN_SHORT_TEXT,
        "802" => iconnexpacket::LEN_SHORT_TEXT,
        "803" => iconnexpacket::LEN_SHORT_TEXT,
        "804" => iconnexpacket::LEN_SHORT_TEXT,
        "805" => iconnexpacket::LEN_SHORT_TEXT,
        "860" => iconnexpacket::LEN_SHORT_TEXT,
        "861" => iconnexpacket::LEN_SHORT_TEXT,
        "904" => iconnexpacket::LEN_SHORT_TEXT,
        "905" => iconnexpacket::LEN_SHORT_TEXT,
        "908" => iconnexpacket::LEN_SHORT_TEXT,
        "909" => iconnexpacket::LEN_SHORT_TEXT,
        "910" => iconnexpacket::LEN_SHORT_TEXT
    );
    public $structs = array (
        "2" => "SmessageType/cprojectId/ccustomerId/ImessageId/Ijunk/IinnerType/IsendTime/Isender/A*message_body",
        "8" => "SmessageType/SmessageId/cackRequired/cnetworkId/Sjunk/lsender/A20senderAddress/SprojectCode/ScustomerCode/A256message_body",
        "111" => "c2padding/iunitId/ItimeSent/a30textMessage/c2morepadding",
        "122" => iconnexpacket::STRUCT_GPS_HEARTBEAT,
        "123" => iconnexpacket::STRUCT_GPS_HEARTBEAT,
        "125" => iconnexpacket::STRUCT_NETWORK_HEARTBEAT,
        "126" => iconnexpacket::STRUCT_NETWORK_HEARTBEAT,
        "121" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "108" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "130" => iconnexpacket::STRUCT_GPS_TEXT,
        "131" => iconnexpacket::STRUCT_GPS_TEXT,
        "132" => iconnexpacket::STRUCT_GPS_TEXT,
        "133" => iconnexpacket::STRUCT_GPS_TEXT,
        "134" => iconnexpacket::STRUCT_GPS_TEXT,
        "135" => "c2padding/ImessageTime/Iin/Iout/cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",

        "140" => "c2padding/ImessageTime/Sin/Sout/StotalIn/StotalOut/Ioccupancy/cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        //"140" => "SmessageType/Sjunk/ltimeSent/Sin/Sout/StotalIn/StotalOut/Ioccupancy/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds"
        "224" => "smessageId/imessageTime/sTrafficSignalNumber/CmovementNumber/CtriggerPoint/slateness/Cpriority/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "226" => "smessageId/imessageTime/c8routeCode/itripNo/ifromLoc/itoLoc/ilocationCode/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "229" => "c2padding/imessageId/imessageTime/ilocationCode/iprotocol/iannouncement/imovementCode/ipriority/ilateness/imode/idiagFlag/ipriorityFlag/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        "240" => "Caction/Cdirection/CrouteCode1/CrouteCode2/CrouteCode3/CrouteCode4/idriverNumber/SdutyNumber/SrunningNumber/StripNumber/SlocationCode/ltimeRouteStarted/SvehicleCode/SsendTimeAddOn/SarrTimeAddOn/ScurrentLateness/dgpslat/dgpslong",
        "113" => iconnexpacket::STRUCT_SHORT_TEXT,
        "138" => iconnexpacket::STRUCT_SHORT_TEXT,
        "139" => iconnexpacket::STRUCT_SHORT_TEXT,
        "260" => iconnexpacket::STRUCT_SHORT_TEXT,
        "476" => "c2padding/ItimeSent/IimpactCounter",
        "483" => iconnexpacket::STRUCT_SHORT_TEXT,
        "480" => iconnexpacket::STRUCT_SHORT_TEXT,
        "481" => iconnexpacket::STRUCT_SHORT_TEXT,
        "482" => iconnexpacket::STRUCT_SHORT_TEXT,
        "483" => iconnexpacket::STRUCT_SHORT_TEXT,
        "484" => iconnexpacket::STRUCT_SHORT_TEXT,
        "485" => iconnexpacket::STRUCT_SHORT_TEXT,
        "486" => iconnexpacket::STRUCT_SHORT_TEXT,
        "493" => iconnexpacket::STRUCT_SHORT_TEXT,
        "494" => iconnexpacket::STRUCT_SHORT_TEXT,
        "513" => iconnexpacket::STRUCT_SHORT_TEXT,
        "606" => iconnexpacket::STRUCT_SHORT_TEXT,
        "801" => iconnexpacket::STRUCT_SHORT_TEXT,
        "802" => iconnexpacket::STRUCT_SHORT_TEXT,
        "803" => iconnexpacket::STRUCT_SHORT_TEXT,
        "804" => iconnexpacket::STRUCT_SHORT_TEXT,
        "805" => iconnexpacket::STRUCT_SHORT_TEXT,
        "860" => iconnexpacket::STRUCT_SHORT_TEXT,
        "861" => iconnexpacket::STRUCT_SHORT_TEXT,
        "904" => iconnexpacket::STRUCT_SHORT_TEXT,
        "905" => iconnexpacket::STRUCT_SHORT_TEXT,
        "908" => iconnexpacket::STRUCT_SHORT_TEXT,
        "909" => iconnexpacket::STRUCT_SHORT_TEXT,
        "910" => iconnexpacket::STRUCT_SHORT_TEXT
    );

    //public $structs = array (
        //"2" => "SmessageType/cprojectId/ccustomerId/ImessageId/Ijunk/IinnerType/IsendTime/Isender/A*message_body",
        ////"8" => "SmessageType/SmessageId/cackRequired/cnetworkId/Sjunk/lsender/A20senderAddress/SprojectCode/ScustomerCode/A256message_body",
        //"240" => "SmessageType/Caction/Cdirection/A4routeCode/idriverNumber/SdutyNumber/SrunningNumber/StripNumber/SlocationCode/ltimeRouteStarted/SvehicleCode/SsendTimeAddOn/SarrTimeAddOn/ScurrentLateness/dgpslat/dgpslong",
        //"121" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        //"108" => "SmessageType/Sjunk/ltimeSent/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds",
        //"140" => "SmessageType/Sjunk/ltimeSent/Sin/Sout/StotalIn/StotalOut/Ioccupancy/Cgps_lat_degrees/cgps_lat_minutes/Sgps_lat_seconds/Cgps_long_degrees/cgps_long_minutes/Sgps_long_seconds"
        //);

    function __construct($odsconnector, $rtpiconnector, $inData, $length)
    {
        $this->data = $inData;
        $this->length = $length;
        $this->content = $inData;
        $this->odsconnector = $odsconnector;
        $this->rtpiconnector = $rtpiconnector;
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
    ** If the packet has a build code then fetch the associated vehicle and operator
    */
    function identifyVehicleAndOperatorByBuild()
    {
        echo $this->build_code;
        echo $this->vehicle_code;
        if ( $this->build_code && !$this->vehicle_code )
        {
	        $vehicle_id = NULL;
	        if (!($record = $this->odsconnector->getVehicleRecordByBuildCode($this->build_code)))
	        {
		        echo "Unknown build_code $operator/$vehicle\n";
		        return false;
	        }
	        $this->operator_code = $record["operator_code"];
	        $this->vehicle_code = $record["vehicle_code"];
	        $this->vehicle_id = $record["vehicle_id"];
	        $this->operator_id = $record["operator_id"];
        }
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
        $this->odsconnector->import_gps_route_status($this);
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
        switch ( $exploded["messageType"] )
        {
            // Message from Route Tracker
            case 2: 
            {
                $exploded = unpack($this->structs[$exploded["messageType"]], $this->data);
                $this->sender = $exploded["sender"];
                $this->sendtime = $exploded["sendTime"];

                if ( $this->odsconnector->rtpiconnector )
                	if ( !$this->odsconnector->rtpiconnector->getOperatorFromBuildCode($this->sender, $this->operator_id, $this->operator_code) )
                	{
		
                    	echo "Failed to get operator for build $this->sender \n";
                    	return;
                	}
                $this->address = "N/A";
                $this->processServerJSONMessage($exploded);
                return;
                break;
            }

            // Message from bus/stop forwarded from RT server
            case 8: 
            {
                $exploded = unpack($this->structs[$exploded["messageType"]], $this->data);
                $this->sender = $exploded["sender"];
                if ( $this->odsconnector->rtpiconnector && !$this->odsconnector->rtpiconnector->getOperatorFromBuildCode($this->sender, $this->operator_id, $this->operator_code) )
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
        $this->messageContent = unpack("SmessageType/nint", $exploded["message_body"]);
        //echo "Wrapper: $this->wrapper_type /  ".$this->messageContent["messageType"]." From $this->sender / $this->address \n";
        switch ( $this->messageContent["messageType"] )
        {
            case 240: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->odsconnector->import_gps_240($this);
                break;
            }

            case 108: 
            case 121: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->odsconnector->import_gps_121($this);
                break;
            }

            case 140: 
            {
                $this->messageContent = unpack($this->structs[$this->messageContent["messageType"]], $exploded["message_body"]);
                $this->odsconnector->import_gps_140($this);
                break;
            }

            default:
                echo "Unknown Message Type ".$this->messageContent["messageType"]." \n";
        }

        return $retval;
    }

    /*
    ** Reads first few bytes to identify message type and sender
    */
    function identifyDataFromFile($fp)
    {
        $retval = false; 

        $this->messageType = false;
        $this->messageContent = false;
        $this->initializePacket();

        $data = fread($fp, 2);
        if ( !$data )
            return false;

        $exploded = unpack("SmessageType", $data);
        if ( !$exploded )
        {
            echo "Fail to read message type \n";
        }

        $this->messageType = $exploded["messageType"];

        global $lastmessageType;
        if ( array_key_exists ( $this->messageType, $this->structs ) )
        {
            $data = fread($fp, $this->structlens[$this->messageType]);
            //echo "MSG ". $this->messageType." L: ". $this->structlens[$this->messageType]."\n";
            $this->messageContent = unpack($this->structs[$this->messageType], $data);
            //echo "MSG FOUND! $lastmessageType ".$this->messageType."\n";
            return true;
        }
        else
        {
            echo "MSG NOT FOUND! $lastmessageType ".$this->messageType."\n";
            return false;
        }
        $lastmessageType = $this->messageType;
    }


    /*
    ** Stores the data in ODS fact tables
    */
    function processData()
    {
    }

}

include_once "iconnexpacket240.class.php";
include_once "iconnexpacket135.class.php";

?>
