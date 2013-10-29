<?php
/**
* LogJourneyDetails
*ge_
* Datamodel for table log_journey_details
*
*/

class LogJourneyDetails extends DataModel
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
            "service_code" => new DataModelColumn($this->connector, "service_code", "varchar", "6"),
            "public_service_code" => new DataModelColumn($this->connector, "public_service_code", "varchar", "6"),
            "running_board" => new DataModelColumn($this->connector, "running_board", "varchar", "7"),
            "duty_number" => new DataModelColumn($this->connector, "duty_number", "varchar", "6"),
            "journey_number" => new DataModelColumn($this->connector, "journey_number", "varchar", "5"),
            "scheduled_start" => new DataModelColumn($this->connector, "scheduled_start", "char", 5),
            "direction" => new DataModelColumn($this->connector, "direction", "smallint"),
            "depot_code" => new DataModelColumn($this->connector, "depot_code", "varchar", "4"),
            "driver_code" => new DataModelColumn($this->connector, "driver_code", "varchar", "6"),
            "first_stop_id" => new DataModelColumn($this->connector, "first_stop_id", "varchar", "12"),
            "destination_stop_id" => new DataModelColumn($this->connector, "destination_stop_id", "varchar", "12"),
            "log_id" => new DataModelColumn($this->connector, "log_id", "integer"),
            "text" => new DataModelColumn($this->connector, "text", "varchar", "32"),
            "status" => new DataModelColumn($this->connector, "status", "integer", false)
            );

        $this->tableName = "log_journey_details";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("log_entry_id");
        parent::__construct($connector);
    }

    function logByEvent($event)
    {
        $initiator = new UnitBuild($this->connector);
        $initiator->build_code = $event->initiator;
        if (!$initiator->load(array("build_code")))
            echo "LogJourneyDetails->logByEvent() failed to find build_id for initiator " . $event->initiator . "\n";

        $reference = new UnitBuild($this->connector);
        $reference->build_code = $event->reference;
        if (!$reference->load(array("build_code")))
            echo "LogJourneyDetails->logByEvent() failed to find build_id for reference " . $event->reference . "\n";
        
        $this->log_entry_id = 0;
        $this->receipt_time = $event->receipt_time->format("Y-m-d H:i:s");
        $this->initiator_id = $initiator->build_id;
        $this->reference_id = $reference->build_id;
        if ($event->validJourneyDetails)
        	$this->message_type = MessageType::CMNO_ETM_VALID_JOURNEY;
        else
        	$this->message_type = MessageType::CMNO_ETM_INVALID_JOURNEY;

        $event->msg_timestamp = new DateTime();
        $this->message_timestamp = $event->msg_timestamp->format("Y-m-d H:i:s");
//        $this->gis_id =
        $this->service_code = $event->service_code;
        $this->public_service_code = $event->public_service_code;
        $this->running_board = $event->running_board;
        $this->duty_number = $event->duty_number;
        $this->journey_number = $event->journey_number;
        $this->scheduled_start = $event->scheduled_start;
        $this->direction = $event->direction;
        $this->depot_code = $event->depot_code;
        $this->driver_code = $event->driver_code;
        $this->first_stop_id = $event->first_stop_id;
        $this->destination_stop_id = $event->destination_stop_id;

        $this->status = $event->context->statusResponse;
//        $this->log_id = 
//        $this->text = 
        $this->add();
        if ($this->log_entry_id == 0)
        {
            echo "LogJourneyDetails->logByEvent() failed to add record to log_journey_details\n";
            return;
        }

        $log_time = new LogTime($this->connector);
        $log_time->log_entry_id = 0;
        $log_time->receipt_time = $this->receipt_time;
        $log_time->initiator_id = $initiator->build_id;
        $log_time->reference_id = $reference->build_id;
//        $log_time->message_type = $event->message_type;
        $log_time->message_type = 244; // CMNO_DRIVER_DETAILS => log_id refers to log_journey_details table UGLY
        $log_time->message_timestamp = $this->message_timestamp;
        $log_time->log_id = $this->log_entry_id;
//        $log_time->text = 
        $log_time->add();

        if ($log_time->log_entry_id == 0)
        {
            echo "LogJourneyDetails->logByEvent() failed to add record to log_time\n";
            return;
        }
    }
}
?>
