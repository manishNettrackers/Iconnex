<?php

/**
 * Class: TaskDAIP
 *
 * Receives RTIG DAIP messages from vehicles and processes them
 */

// Use Apache log4php for logging
require_once("log4php/Logger.php");
Logger::configure("log4php_config.xml");
$log = Logger::getLogger("EventLog");

// Get Event Handler Queue
global $event_handler;
$event_handler = msg_get_queue(3000);

$sessionVehicleID = 0; // global now, but should be per-vehicle?
$taskStartTime;

class TaskDAIP extends ScheduledTask
{
    var $acknowledgementCounter = 0; // global now, but should be per-vehicle?
    var $messageCounter = 0; // global now, but should be per-vehicle as it doesn't go that high?

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Starts the DAIP udp server 
    */
    function runTask()
    {
        global $taskStartTime;
        $taskStartTime = new DateTime();
        $taskStartTime->setTimezone(new DateTimeZone("UTC"));

        // Prepare Connection
        $this->connector->setDirtyRead();
        $this->connector->executeSQL("SET LOCK MODE TO WAIT 10");

        global $sessionVehicleID;
        $sql = "SELECT max(session_id) session_id from vehicle_session";
        $ret = $this->connector->fetch1SQL($sql);

        global $sessionVehicleID;
        $sessionVehicleID = 1;

        if ($ret)
            $sessionVehicleID = $ret["session_id"];

        // Start UDP engine on DAIP port
        $udpengine = new UDPEngine();
        $udpengine->expect = EXPECT_DAIP_PACKETS;
        $udpengine->listen();
    }
}

?>
