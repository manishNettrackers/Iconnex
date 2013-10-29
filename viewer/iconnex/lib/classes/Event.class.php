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

    public $ip_address;
    public $conn_status;
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

    function show()
    {
        $now = UtilityDateTime::currentTime();

        if (!$this->receipt_time)
        {
            echo "Event->show() receipt_time not set";
        }
        if (!$this->msg_timestamp)
        {
            echo "Event->show() msg_timestamp not set";
        }
        //echo "MSG: " . $this->receipt_time->format("Y-m-d H:i:s") . " / " . $this->msg_timestamp->format("Y-m-d H:i:s") . " ";
        echo "MSG: ".   $this->receipt_time->format("Y-m-d H:i:s") . " ";

        echo $this->initiator." ";
        echo $this->origin." ";
        echo $this->reference." ";
        echo $this->ip_address." ";
        echo $this->message_type;
        echo "\n";
    }

    function process()
    {
        // Use receipt_time for message timestamp as we cannot trust the
        // timestamps in the messages from some units (eg. DAIP ETMs).
        if ($this->receipt_time)
            $this->active_item->message_time = $this->receipt_time;

        // Set active_item properties with any event values that are set.
        if ($this->ip_address)
            $this->active_item->ip_address = $this->ip_address;
        if ($this->conn_status)
            $this->active_item->conn_status = $this->conn_status;
        if ($this->message_type)
            $this->active_item->message_type = $this->message_type;
        if ($this->gps_position)
            $this->active_item->latest_gps_position = $this->gps_position;
        if ($this->soft_ver)
            $this->active_item->soft_ver = $this->soft_ver;
        if ($this->sim_no)
            $this->active_item->sim_no = $this->sim_no;

        //$now = $this->receipt_time->format("Y-m-d H:i:s");
        //$unittime = $this->msg_timestamp->format("Y-m-d H:i:s");
    }
}

?>
