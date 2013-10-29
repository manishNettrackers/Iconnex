<?php
/**
* LogTime
*
* Datamodel for table log_time
*
*/

class LogTime extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "log_entry_id" => new DataModelColumn ( $this->connector, "log_entry_id", "serial"),
            "receipt_time" => new DataModelColumn ( $this->connector, "receipt_time", "datetime", false),
            "initiator_id" => new DataModelColumn ( $this->connector, "initiator_id", "integer", false),
            "reference_id" => new DataModelColumn ( $this->connector, "reference_id", "integer", false),
            "message_type" => new DataModelColumn ( $this->connector, "message_type", "integer"),
            "message_timestamp" => new DataModelColumn ( $this->connector, "message_timestamp", "datetime"),
            "log_id" => new DataModelColumn ( $this->connector, "log_id", "integer"),
            "text" => new DataModelColumn ( $this->connector, "text", "varchar", "32")
            );

        $this->tableName = "log_time";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("log_entry_id");
        parent::__construct($connector);
    }

    function logByEvent($event)
    {
        global $odsconnector;

        $initiator = new UnitBuild($this->connector);
        $initiator->build_code = $event->initiator;
        if (!$initiator->load(array("build_code")))
            echo "LogTime->logByEvent() failed to find build_id for initiator " . $event->initiator . "\n";

        $reference = new UnitBuild($this->connector);
        $reference->build_code = $event->reference;
        if (!$reference->load(array("build_code")))
            echo "LogTime->logByEvent() failed to find build_id for reference " . $event->reference . "\n";

        $this->log_entry_id = 0;
        $this->receipt_time = $event->receipt_time->format("Y-m-d H:i:s");
        $this->initiator_id = $initiator->build_id;
        $this->reference_id = $reference->build_id;
//        $this->message_type = $event->message_type;

        // UGLY
        $class_type = get_class($event);
        if ($class_type == "EventLogOn")
            $this->message_type = 606; // CMNO_BOOTUP => Log On
        if ($class_type == "EventLogOff")
            $this->message_type = 494; // CMNO_DISPLAY_OFFLINE => Log Off

        $this->message_timestamp = $event->msg_timestamp->format("Y-m-d H:i:s");
//        $this->log_id = NULL;
//        $this->text = 
        $this->add(); // This should get the log_entry_id to use in log_time.
        if ($this->log_entry_id == 0)
        {
            echo "LogTime->logByEvent() failed to add record to log_time\n";
            return;
        }
    }
}
?>
