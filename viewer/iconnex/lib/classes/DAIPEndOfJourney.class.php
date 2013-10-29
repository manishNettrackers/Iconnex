<?php

set_include_path(get_include_path().":../lib:../lib/classes");

/**
 * @brief Functionality for processing a DAIP End Of Journey message
 */
class DAIPEndOfJourney extends DAIPEvent
{
    public $peer = NULL;
    public $messageRef = 0;
    public $service_code;
    public $journey_number;
    public $scheduled_start;
    public $public_service_code;
    public $direction;
    public $timestamp = 0;

    function __construct($context, $peer, $messageRef, $service_code, $journey_number, $scheduled_start, $public_service_code, $direction, $timestamp)
    {
        global $log;
        $log->debug("Service Code: " . $service_code);
        $log->debug("Journey Number: " . $journey_number);
        $log->debug("Journey Scheduled Start Time: " . $scheduled_start);
        $log->debug("Public Service Code: " . $public_service_code);
        $log->debug("Direction: " . $direction);
        $log->debug("Timestamp: " . $timestamp->format('Y-m-d H:i:s'));
    
        $this->peer = $peer;
        $this->service_code = $service_code;
        $this->journey_number = $journey_number;
        $this->scheduled_start = $scheduled_start;
        $this->public_service_code = $public_service_code;
        $this->direction = $direction;
        $this->timestamp = $timestamp;

        parent::__construct($context);
    }

    function process()
    {
        // TODO
    }
}

?>
