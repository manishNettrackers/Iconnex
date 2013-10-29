<?php

require_once("Event.class.php");
class EventUnit extends Event
{
    public $context = false;

    function __construct($receipt_time, $timestamp, $initiator, $origin, $reference)
    {
        parent::__construct($receipt_time, $timestamp, $initiator, $origin, $reference);
    }


    function process()
    {
        global $rtpiconnector;

        parent::process();

        // Update the reference unit's unit_status entry
        $unit_status = new UnitStatus($rtpiconnector);
        $unit_status->build_id = $this->active_item->unit_build->build_id;
        $unit_status->load(array("build_id"));
        $unit_status->ip_address = $this->active_item->ip_address;


        $unit_status->conn_status = $this->active_item->conn_status;
        $unit_status->message_time = $this->active_item->message_time->format("Y-m-d H:i:s");
        $unit_status->message_type = $this->active_item->message_type;
        if ($this->active_item->latest_gps_position)
        {
            $unit_status->gps_time = $this->active_item->latest_gps_position->gps_time->format("Y-m-d H:i:s");
            $unit_status->gpslat = $this->active_item->latest_gps_position->latitude;
            $unit_status->gpslong = $this->active_item->latest_gps_position->longitude;
            $unit_status->gpslat_str = $this->active_item->latest_gps_position->gpslat_str;
            $unit_status->gpslong_str = $this->active_item->latest_gps_position->gpslong_str;
        }
        $unit_status->gps_dup_ct = $this->active_item->gps_dup_ct;

        if ( $this->active_item->soft_ver )
        {
            $unit_status->soft_ver = $this->active_item->soft_ver;
        }
        $unit_status->sim_no = $this->active_item->sim_no;
        $unit_status->save();
    }
}

?>
