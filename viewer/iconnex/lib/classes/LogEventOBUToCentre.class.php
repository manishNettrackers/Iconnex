<?php
/**
* LogEventOBUToCentre
*
* Datamodel for table log_event_obu_to_centre
*/

class LogEventOBUToCentre extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "log_entry_id" => new DataModelColumn($this->connector, "log_entry_id", "serial"),
            "receipt_time" => new DataModelColumn($this->connector, "receipt_time", "datetime"),
            "initiator_id" => new DataModelColumn($this->connector, "initiator_id", "integer"),
            "reference_id" => new DataModelColumn($this->connector, "reference_id", "integer"),
            "message_type" => new DataModelColumn($this->connector, "message_type", "integer"),
            "message_timestamp" => new DataModelColumn($this->connector, "message_timestamp", "datetime"),
            "gis_id" => new DataModelColumn($this->connector, "gis_id", "integer"),
            "latitude" => new DataModelColumn($this->connector, "latitude", "decimal", "12,5"),
            "longitude" => new DataModelColumn($this->connector, "longitude", "decimal", "12,5"),
            "location_id" => new DataModelColumn($this->connector, "location_id", "integer"),
            "log_id" => new DataModelColumn($this->connector, "log_id", "integer"),
            "text" => new DataModelColumn($this->connector, "text", "varchar", "32")
            );

        $this->tableName = "log_event_obu_to_centre";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("log_entry_id");
        parent::__construct($connector, $initialiserArray);

    }

    function logByEvent($event)
    {
        $initiator = new UnitBuild($this->connector);
        $initiator->build_code = $event->initiator;
        if (!$initiator->load(array("build_code")))
            echo "LogEventArrivalDeparture->logByEvent() failed to find build_id for initiator " . $event->initiator . "\n";

        $reference = new UnitBuild($this->connector);
        $reference->build_code = $event->reference;
        if (!$reference->load(array("build_code")))
            echo "LogEventArrivalDeparture->logByEvent() failed to find build_id for reference " . $event->reference . "\n";
        
        $this->log_entry_id = 0;
        $this->receipt_time = $event->receipt_time->format("Y-m-d H:i:s");
        $this->initiator_id = $initiator->build_id;
        $this->reference_id = $reference->build_id;
        $this->message_type = $event->message_type;
        $event->msg_timestamp = new DateTime();
        $this->message_timestamp = $event->msg_timestamp->format("Y-m-d H:i:s");
        $this->gis_id = 0;
        if (isset($event->gps_position))
        {
            $this->latitude = $event->gps_position->latitude;
            $this->longitude = $event->gps_position->longitude;
        }
        if (isset($event->location))
            $this->location_id = $event->location->location_id;
//        $this->log_id = 
        $this->text = $event->text;
        $this->add();
        if ($this->log_entry_id == 0)
        {
            echo "LogEventArrivalDeparture->logByEvent() failed to add record to log_arrival_departure\n";
            return;
        }

        $log_time = new LogTime($this->connector);
        $log_time->log_entry_id = 0;
        $log_time->receipt_time = $this->receipt_time;
        $log_time->initiator_id = $initiator->build_id;
        $log_time->reference_id = $reference->build_id;
        $log_time->message_type = $event->message_type;
        $log_time->message_timestamp = $this->message_timestamp;
        $log_time->log_id = $this->log_entry_id;
//        $log_time->text = 
        $log_time->add();
        if ($log_time->log_entry_id == 0)
        {
            echo "LogEventArrivalDeparture->logByEvent() failed to add record to log_time\n";
            return;
        }
    }
}
?>
