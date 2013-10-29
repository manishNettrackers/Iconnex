<?php

require_once("log4php/Logger.php");

class ScheduledTask extends DataModel
{
    public $rtpiconnector;

    function __construct($connector = false)
    {
        $this->columns = array ( 
            "task_id" => new DataModelColumn ( $this->connector, "task_id", "serial" ),
            "class_name" => new DataModelColumn ( $this->connector, "class_name", "varchar", 30 ),
            "background" => new DataModelColumn ( $this->connector, "background", "integer" ),
            "background_pid" => new DataModelColumn ( $this->connector, "background_pid", "integer" ),
            "event_queue" => new DataModelColumn ( $this->connector, "event_queue", "integer" ),
            "trigger_time" => new DataModelColumn ( $this->connector, "trigger_time", "datetimehourtoseconds" ),
            "trigger_every" => new DataModelColumn ( $this->connector, "trigger_every", "integer" ),
            "trigger_on_table" => new DataModelColumn ( $this->connector, "trigger_on_table", "char", "30" ),
            "trigger_on_column" => new DataModelColumn ( $this->connector, "trigger_on_column", "char", "30" ),
            "status" => new DataModelColumn ( $this->connector,  "status", "char", 10 ),
            "last_executed" => new DataModelColumn ( $this->connector, "last_executed", "datetime" ),
            "next_scheduled" => new DataModelColumn ( $this->connector, "next_scheduled", "datetime" ),
            "rows_affected" => new DataModelColumn ( $this->connector, "rows_affected", "integer" ),
            );
        $this->tableName = "scheduled_task";
        $this->dbspace = "centdbs";
        $this->className = "ScheduledTask";
        $this->keyColumns = array("task_id");

        parent::__construct($connector);
    }

    function runTask()
    {
        echo "Task $this->className has no runTask method defined.";
    }

    // Task is scheduled to run if is pending and
    // either not previously execute, has no next scheduled time
    // is due for a regular rerun, is due for a time specific run
    // is a background task that is not runnning
    function scheduledToRun($runAttemptTimestamp)
    {
        if ( $this->status == "STOPPED" )
            return false;

        if ( $this->background )
        {
            if ( !$this->background_pid )
                return true;

            if ( !$this->isRunningInBackground() )
            {
                return true;
            }
            else
            {
                // Task is not running but there is pid, means we want to 
                // restart process so kill pid and reschedule
                if ( $this->background_pid && $this->status == "PENDING" )
                {
                    if ( $this->stopTask() )
                        return true;
                }
            }
            return false;
        }

        if ( !$this->last_executed || !$this->next_scheduled )
            return true;

        if ( $this->status && $this->status != "PENDING" )
            return false;

        $scheduledToRun = false;
        if ( $this->next_scheduled )
        {
            $next = DateTime::CreateFromFormat("Y-m-d H:i:s", $this->next_scheduled);
            $nexts = $next->getTimeStamp();
            $nowts = $runAttemptTimestamp->getTimeStamp();
            if ( $nowts > $nexts )
            {
                $scheduledToRun = true;
            }

            if ( $this->trigger_time )
            {
                // Allow specific time of day triggers to occur only if current time is within
                // 10 minutes after them
                $thisTime = DateTime::CreateFromFormat("H:i:s", $this->trigger_time);
                if ( $nowts >  $thisTime->getTimeStamp() + 600 )
                {
                    $scheduledToRun = false;
                }
            }
        }

        return $scheduledToRun;
    }

    /** @brief startTask
    *
    * On starting a task, the event is logged, the task is started and the next
    * scheduled time, if applicable is calculated
    */
    function startTask($runAttemptTime)
    {
        // Log the start
        // If task was flagged as running then its here because the task has failed to 
        // restart it
        $status = $this->status;
        $log = new LogSystem($this->connector);
        if ( $status == "RUNNING" )
            $log->log($this->class_name, MessageType::SYSTEM_TASK_RECOVER, "Recovered");
        else
            $log->log($this->class_name, MessageType::SYSTEM_TASK_START, "Started");

        // Flag Task as running
        $this->status = "RUNNING";
        $this->next_scheduled = false;
        $this->save();

        // Run the Task
        echo "Task $this->class_name Scheduled For Execution - running\n";
        if ( $this->background )
        {
            $this->runTaskInBackground();
        }
        else
        {
            $this->runTask();
        }
        //sleep(2);

        // Calculate next run time
        if ( $this->trigger_every )
        {
            $now = new DateTime();
            $now->setTimeStamp($now->getTimeStamp() +  $this->trigger_every );
            $this->next_scheduled = $now->format("Y-m-d H:i:s");
        }

        if ( $this->trigger_time )
        {
            $thisTime = DateTime::CreateFromFormat("H:i:s", $this->trigger_time);
            $thisTime = $thisTime->add(new DateInterval("P1D"));
            $this->next_scheduled = $thisTime->format("Y-m-d H:i:s");
        }
        if ( $this->background )
        {
            $thisTime = new DateTime();
            $thisTime->setTimestamp($runAttemptTime->getTimestamp());
            $thisTime = $thisTime->add(new DateInterval("P10Y"));
            $this->next_scheduled = $thisTime->format("Y-m-d H:i:s");
        }


        $this->last_executed = $runAttemptTime->format("Y-m-d H:i:s");
        if ( !$this->background )
            $this->status = "PENDING";
        $this->save();

    }


    /**
    ** Is the task with the background pid running
    */
    function isRunningInBackground()
    {
        try{
            $result = shell_exec(sprintf("ps %d", $this->background_pid));
            if( count(preg_split("/\n/", $result)) > 2){
                return true;
            }
        }catch(Exception $e){}

        return false;
    }

    /**
    ** Stops a running task
    */
    function stopTask()
    {
        try{
            if ( !$this->background_pid )
            {
                echo "No ".$this->class_name." to kill";
                return true;
            }

            $log = new LogSystem($this->connector);
            $log->log($this->class_name, MessageType::SYSTEM_TASK_STOP, "Stopped");

            $result = shell_exec(sprintf("kill %s > /dev/null 2>&1", $this->background_pid));
            echo " Kill ".$this->background_pid ." .... ";
            sleep (1);

            if ( !$this->isRunningInBackground() )
            {
                echo " Killed ";
                $this->status = "STOPPED";
                $this->next_scheduled = false;
                $this->save();
                return true;
            }
            else
            {
                echo "Task Failed to stop!";
                return false;
            }
            $result = shell_exec(sprintf("kill ", $this->background_pid));
        }catch(Exception $e){}


        return false;
    }

    function runTaskInBackground()
    {
        $errfile = LOG_DIR.$this->class_name.".err";
        $logfile = LOG_DIR.$this->class_name.".log";
        $pidfile = TMP_DIR.$this->class_name.".pid";
        
        // Clear error File
        if ( file_exists($errfile) )
            unlink($errfile);

        // Run task process
        $cmd = sprintf("/usr/local/bin/php-cgi ".ROOT_DIR."bin/RunTask.php task=%s >> %s 2> %s & echo $! > %s", $this->class_name, $logfile, $errfile, $pidfile);
        $stat = exec($cmd);

        $error  = false;
        if ( file_exists($errfile) )
        {
            $error = file_get_contents($errfile);
        }
        if ( $error )
        {
            $this->background_pid = false;
            $this->status = "FAIL";
            echo "Task Error: $error\n";
        }
        else
           $this->background_pid = file_get_contents($pidfile);
    }

    static function taskEventQueue($connector, $taskName)
    {
        $task = new ScheduledTask($connector);
        $task->class_name = $taskName;
        $connector->debug = 1;
        $connector->debug = 0;
        if ( $task->load(array("class_name")) )
        {
            if ( !$task->event_queue )
            {
                echo "Task $taskName has no queue\n";
                return false;
            }
            if ( $task->status != "RUNNING" )
            {
                echo "Task $taskName is not running\n";
                return false;
            }
             return msg_get_queue($task->event_queue);

        }
        echo "Task $taskName unknown\n";
        return "false";

    }
}

?>
