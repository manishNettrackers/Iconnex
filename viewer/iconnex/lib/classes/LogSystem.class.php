<?php
/**
 * LogSystem
 *
 * Datamodel for table log_system
 */

class LogSystem extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "log_entry_id" => new DataModelColumn($this->connector, "log_entry_id", "serial"),
            "event_time" => new DataModelColumn($this->connector, "event_time", "datetime", false),
            "task" => new DataModelColumn($this->connector, "task", "char", "30"),
            "message_type" => new DataModelColumn($this->connector, "message_type", "integer"),
            "log_id" => new DataModelColumn($this->connector, "log_id", "integer"),
            "text" => new DataModelColumn($this->connector, "text", "varchar", "32")
            );

        $this->tableName = "log_system";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("log_entry_id");
        parent::__construct($connector);
    }

    /**
    * @brief log
    *
    * Logs a system event ( Start Task etc )
    */
    function log($task, $message_type, $text)
    {
        $log = new LogSystem($this->connector);
        $log->log_entry_id = 0;
        $log->event_time = UtilityDateTime::currentTime();
        $log->task = substr($task, 4);
        $log->message_type = $message_type;
        $log->log_id = 0;
        $log->text = $text;
        $log->add();
    }
}
?>
