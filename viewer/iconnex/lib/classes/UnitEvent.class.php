<?php

interface IProcessable
{
    function process();
}

class Event implements IProcessable
{
    protected $active_item;

    public $event_handler; // Set by EventHandler to give access to live data arrays

    public $receipt_time;
    public $msg_timestamp;
    public $initiator; // unit_build.build_code
    public $origin; // system type, eg. stop, bus, despatcher
    public $reference; // unit_build.build_code (the unit the event should affect)

    // Additional elements required for unit_status
    public $ip_address;
    public $message_type;
    public $gps_position;
    public $soft_ver;
    public $sim_no;

    function __construct($receipt_time, $timestamp, $initiator, $origin, $reference)
    {
        $this->receipt_time = $receipt_time;
        $this->msg_timestamp = $timestamp;
        $this->initiator = $initiator;
        $this->origin = $origin;
        $this->reference = $reference;
    }

    function process()
    {
        echo "Event->process()\n";
        global $rtpiconnector;

        // Set active_item properties with any event values that are set.
        if ($this->ip_address)
            $this->active_item->ip_address = $this->ip_address;
        if ($this->conn_status)
            $this->active_item->conn_status = $this->conn_status;
        // Use receipt_time for message timestamp as we cannot trust the
        // timestamps in the messages from some units (eg. DAIP ETMs).
        if ($this->receipt_time)
            $this->active_item->message_time = $this->receipt_time;
        if ($this->message_type)
            $this->active_item->message_type = $this->message_type;
        if ($this->gps_position)
            $this->active_item->latest_gps_position = $this->gps_position;
        if ($this->soft_ver)
            $this->active_item->soft_ver = $this->soft_ver;
        if ($this->sim_no)
            $this->active_item->sim_no = $this->sim_no;

        // All events should update the reference unit's unit_status entry
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
        $unit_status->soft_ver = $this->active_item->soft_ver;
        $unit_status->sim_no = $this->active_item->sim_no;
        $unit_status->save();
    }
}

?>
