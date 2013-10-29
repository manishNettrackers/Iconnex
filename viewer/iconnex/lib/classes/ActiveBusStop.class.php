<?php

require_once("ActiveItem.class.php");

class ActiveBusStop extends ActiveItem
{
    private $log;

    public $location = NULL;
    public $display_point = NULL;

    function __construct($build_code, $tj_list, $tjl_list)
    {
        global $rtpiconnector;

        parent::__construct($build_code);

        // ActiveBusStop relates to tracking vehicle so retrieve the data
        //$this->location = new Location($rtpiconnector);
        //$this->display_point = new DisplayPoint($rtpiconnector);
        //$this->display_point->build_id = $this->unit_build->build_id;
        
        // TODO: Load location/display_point if required
        //if (!$this->vehicle->load(array("build_id")))
        //{
            //echo "ActiveBusStop->__construct() Failed to load vehicle for build $build_code\n";
            //exit;
            //return false;
        //}
        
        // Find Unit Status record, if not found create one
        $ip_change = false;
        $unit_status = new UnitStatus($rtpiconnector);
        $unit_status->build_id = $this->unit_build->build_id;
        if ($unit_status->load())
        {
            if  ( $unit_status->ip_address != $this->ip_address )
                $ip_change = true;
            $gps_position = new GPSPosition($rtpiconnector);
            $gps_position->initialiseWithLatLong($unit_status->gpslat, $unit_status->gpslong);
            $gps_position->gps_time = new DateTime($unit_status->gps_time);
            $this->latest_gps_position = $gps_position;
        }
        else
        {
            // Add new unit status - time/conection status will be set late in the event
            $unit_status->build_id = $this->unit_build->build_id;
            $unit_status->add();
        }

        // If there has been an IP address change then predictions to bus stops need to be refreshed
        if ( $ip_change )
        {
            echo " => IP Address Change .. Refresh Signs\n";

            $build_id = $this->unit_build->build_id;

            // Resend all Sign Messages
            $rtpiconnector->executeSQL("UPDATE dcd_message_loc SET ( received, message_sent ) = ( NULL, NULL ) WHERE build_id = $build_id");

            // Resend all Countdowns
            $rtpiconnector->executeSQL("
                UPDATE prediction_display 
                SET time_last_sent = NULL
                WHERE location_id in ( SELECT location_id FROM display_point WHERE build_id = $build_id )");
        }
    }

    function show()
    {
        echo "Bus Stop " . $this->build_code . " " . $this->message_time->format("Y-m-d H:i:s") . "\n";
    }
}

?>
