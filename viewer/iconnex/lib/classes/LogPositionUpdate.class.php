<?php
/**
 * LogPositionUpdate
 *
 * Datamodel for table log_position_update
 */

class LogPositionUpdate extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "log_entry_id" => new DataModelColumn($this->connector, "log_entry_id", "serial"),
            "receipt_time" => new DataModelColumn($this->connector, "receipt_time", "datetime", false),
            "initiator_id" => new DataModelColumn($this->connector, "initiator_id", "integer", false),
            "reference_id" => new DataModelColumn($this->connector, "reference_id", "integer", false),
            "message_type" => new DataModelColumn($this->connector, "message_type", "integer"),
            "message_timestamp" => new DataModelColumn($this->connector, "message_timestamp", "datetime"),
            "gis_id" => new DataModelColumn($this->connector, "gis_id", "integer", false),
            "latitude" => new DataModelColumn($this->connector, "latitude", "decimal(12,5)"),
            "longitude" => new DataModelColumn($this->connector, "longitude", "decimal(12,5)"),
            "bearing" => new DataModelColumn($this->connector, "bearing", "integer"),
            "log_id" => new DataModelColumn($this->connector, "log_id", "integer"),
            "text" => new DataModelColumn($this->connector, "text", "varchar", "32")
            );

        $this->tableName = "log_position_update";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("log_entry_id");
        parent::__construct($connector);
    }

    function logByEvent($event)
    {
        //$event->msg_timestamp = new DateTime(); // PPP

        global $odsconnector;

        $initiator = new UnitBuild($this->connector);
        $initiator->build_code = $event->initiator;
        if (!$initiator->load(array("build_code")))
            echo "LogPositionUpdate->logByEvent() failed to find build_id for initiator " . $event->initiator . "\n";

        $reference = new UnitBuild($this->connector);
        $reference->build_code = $event->reference;
        if (!$reference->load(array("build_code")))
            echo "LogPositionUpdate->logByEvent() failed to find build_id for reference " . $event->reference . "\n";

        $this->log_entry_id = 0;
        $this->receipt_time = $event->receipt_time->format("Y-m-d H:i:s");
        $this->initiator_id = $initiator->build_id;
        $this->reference_id = $reference->build_id;
        $this->message_type = $event->message_type;
        $this->message_timestamp = $event->msg_timestamp->format("Y-m-d H:i:s");
//        $this->log_id = NULL;
        $gisarr = array();
        $hash = false;

        //$gisid = $odsconnector->processGeoItem($event->gps_position->latitude, $event->gps_position->longitude, $hash, $gisarr);
        //$this->gis_id = $gisid;
        $this->latitude = $event->gps_position->latitude;
        $this->longitude = $event->gps_position->longitude;
        $this->bearing = $event->gps_position->bearing;
        $this->gis_id = 0;
        $this->text = $event->gps_position->plottableString;
        $this->add(); // This should get the log_entry_id to use in log_time.
        if ($this->log_entry_id == 0)
        {
            echo "LogPositionUpdate->logByEvent() failed to add record to log_position_update\n";
            return;
        }

        $log_time = new LogTime($this->connector);
        $log_time->log_entry_id = 0;
        $log_time->receipt_time = $this->receipt_time;
        $log_time->initiator_id = $initiator->build_id;
        $log_time->reference_id = $reference->build_id;
//        $log_time->message_type = $event->message_type;
        $log_time->message_type = 121; // CMNO_NETWORK_HEARTBEAT_GPS => log_id refers to log_position_update table UGLY
        $log_time->message_timestamp = $this->message_timestamp;
        $log_time->log_id = $this->log_entry_id;
        $log_time->text = $event->gps_position->plottableString;
        $log_time->add();
        if ($log_time->log_entry_id == 0)
        {
            echo "LogPositionUpdate->logByEvent() failed to add record to log_time\n";
            return;
        }
    }
}
?>
