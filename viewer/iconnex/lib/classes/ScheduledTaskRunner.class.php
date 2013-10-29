<?php

class ScheduledTaskRunner
{
    public $tasks = array();
    public $connector;
    public $rtpiconnector;
    public $odsconnector;

    function __construct($connector)
    {
        global $odsconnector;
        global $rtpiconnector;

        $this->connector = $connector;
        $this->rtpiconnector = $odsconnector;
        $this->odsconnector = $rtpiconnector;
    }

    /*
    * @brief Perform a task action ( Stop all tasks, start tasks etc )
    */
    function actionTasks($mode, $task)
    {
        switch ( $mode )
        {
            /* Stop all tasks and remove them from schedule to never run again */
            case "HALTSYSTEM": 
                $this->stopTasks("MANUAL", false, false);
                break;
            case "HALT": 
                $this->stopTasks("MANUAL", $task, false);
                break;
            case "RESTART":
                $this->restartTasks(false, $task, false );
                break;
            case "RELOAD":
                $this->restartTasks(true, $task, true );
                break;
            case "CHECK":
                $this->checkTasks($task);
                break;
            default: 
                echo "Unknown action mode";
                break;
        }
    }

    /*
    * Rebuild scheduled task table
    */
    function initialiseTasks()
    {
        $task = new ScheduledTask($this->odsconnector);
        $task->dropTable(false);
        $task->createTable();
        $this->createTasks();
    }

    /**
    * @brief reviceStoppedTasks
    *
    * Restart all tasks of a single task that was stoppped
    * 
    */
    function reviveStoppedTasks ( $taskName = false )
    {
        $scheduledTask = new ScheduledTask($this->odsconnector);
        $tasks = $scheduledTask->selectAll(false, false, "class_name");

        foreach ( $tasks as $task )
        {
            if ( !$taskName  || $taskName == $task->class_name )
            {
                 if ( $task->status == "STOPPED" )
                 {
                    $task->status = "PENDING";
                    $task->save();
                 }
            }
        }
    }

    /**
    * @brief checkTasks
    *
    * Start any tasks that need to be started
    * 
    * @param reloadTasks - Reload the Scheduled Task with all scheduled tasks
    */
    function checkTasks ( $taskName = false )
    {
        $scheduledTask = new ScheduledTask($this->odsconnector);
        $tasks = $scheduledTask->selectAll(false, false, "class_name");

        foreach ( $tasks as $task )
        {
            if ( !$taskName  || $taskName == $task->class_name )
            {
                // Create Instance of Task
                $runAttemptTimestamp = new DateTime();
                $runAttemptTime = $this->connector->currentTimestampAsString();

                echo "Check task class $task->class_name at $runAttemptTime\n";
                $task->rtpiconnector = $this->rtpiconnector;
                $task->odsconnector = $this->odsconnector;
                $task->connector = $this->connector;

                // Is Task Scheduled To Run?
                $scheduledToRun = false;
                $scheduledToRun = $task->scheduledToRun($runAttemptTimestamp);
                if ( $scheduledToRun )
                    $task->startTask($runAttemptTimestamp);
            }
        }
    }

    /**
    * @brief restartTasks
    *
    * Stop all tasks and restart them. 
    * 
    * @param reloadTasks - Reload the Scheduled Task with all scheduled tasks
    */
    function restartTasks ( $reloadTasks = false, $taskName = false, $removeFromSchedule = false )
    {
        if ( $taskName )
        {
           $task = new ScheduledTask($this->connector);
           $this->stopTasks("MANUAL", $taskName, $removeFromSchedule);
           $task->class_name = $taskName; 
           if ( $reloadTasks )
           {
                $task->delete(array("class_name"));
                $this->createTasks($task->class_name);
           }
        }
        else if ( $reloadTasks )
        {
           $this->stopTasks("MANUAL", $taskName, $removeFromSchedule);
           $this->initialiseTasks();
        }
        else 
        {
           $this->stopTasks("MANUAL", $taskName, $removeFromSchedule);
        }

        // If task is supplied then ensure it exists in scheduled Task 
        if ( $taskName )
        {
           $task = new ScheduledTask($this->connector);
           $task->class_name = $taskName; 
           if ( !$task->load(array("class_name")) )
               $this->createTasks($task->class_name);
        }
        $this->reviveStoppedTasks($taskName);
        $this->checkTasks($taskName);
    }

    /**
    * @brief stopTasks
    *
    * Kill the pid of all or specified task and optionally allow task
    * to be removed from the schedule list for foreground debugging
    */
    function stopTasks ( $initiated_by, $taskName  = false, $removeFromSchedule = false )
    {
    //$this->odsconnector->debug = 1;
        $scheduledTask = new ScheduledTask($this->odsconnector);
        $tasks = $scheduledTask->selectAll(false, false, "class_name");

        $found = false;
        foreach ( $tasks as $task )
        {
            if ( !$taskName  || $taskName == $task->class_name )
            {
                $found = true;
                // Create Instance of Task
                $runAttemptTimestamp = new DateTime();
                $runAttemptTime = $this->connector->currentTimestampAsString();
    
                echo "Stop task class $task->class_name at $runAttemptTime ";
                $task->rtpiconnector = $this->rtpiconnector;
                $task->odsconnector = $this->odsconnector;
                $task->connector = $this->connector;
    
                // Is Task Scheduled To Run?
                $scheduledToRun = false;
                $scheduledToRun = $task->scheduledToRun($runAttemptTimestamp);

                $task->stopTask($runAttemptTimestamp);
                echo "\n";

                //if ( $removeFromSchedule )
                //{
                    //$task->delete();
                //}
            }
        }
        if ( !$found )
            echo "No tasks found matching <$taskName> for stopping\n";
    }

    function createTasks ( $task = false )
    {
        if ( !$task || $task == "TaskMessageX" ) $this->createTask ( "MessageX", "TaskMessageX", false, false, false, false, false, true );
        if ( !$task || $task == "TaskAutoJourneyScheduler" ) $this->createTask ( "Auto Journey Scheduler", "TaskAutoJourneyScheduler", false, "00:01:00", false, false, false );
        if ( !$task || $task == "TaskPredictionDistributor" ) $this->createTask ( "Prediction Distributor", "TaskPredictionDistributor", false, false, false, false, false, true );
        if ( !$task || $task == "TaskJourneyStatusSnapshot" ) $this->createTask ( "Journey Snapshot", "TaskJourneyStatusSnapshot", false, false, false, false, false, true );
        if ( !$task || $task == "TaskStopStatusSnapshot" ) $this->createTask ( "Stop Snapshot", "TaskStopStatusSnapshot", false, false, false, false, false, true );
        if ( !$task || $task == "TaskBoardStatusSnapshot" ) $this->createTask ( "Board Snapshot", "TaskBoardStatusSnapshot", false, false, false, false, false, true );
        if ( !$task || $task == "TaskJourneyStarter" ) $this->createTask ( "Auto Journey Starter", "TaskJourneyStarter", false, false, false, false, false, true );
        if ( !$task || $task == "TaskMessageDistributor" ) $this->createTask ( "Sign Message Distributor", "TaskMessageDistributor", false, false, false, false, false, true );
        if ( !$task || $task == "TaskUDPFeedRelay" ) $this->createTask ( "UDP Event Relay", "TaskUDPFeedRelay", false, false, false, false, false, true );
        if ( !$task || $task == "TaskEventHandler" ) $this->createTask ( "Event Handler", "TaskEventHandler", 3000, false, false, false, false, true );
        if ( !$task || $task == "TaskTimetableGenerator" ) $this->createTask ( "Daily Timetable Generator", "TaskTimetableGenerator", false, false, false, false, false, true );
        if ( !$task || $task == "TaskDAIP" ) $this->createTask ( "DAIP Daemon", "TaskDAIP", false, false, false, false, false, true );
        if ( !$task || $task == "TaskStatisticsTravelTimes" ) $this->createTask ( "Travel Time Stats", "TaskStatisticsTravelTimes", 3005, false, false, false, false, true );
    }

    function createTask ( $taskName, $className, $eventQueue, $triggerTime, $triggerEvery, $triggerOnTable, $triggerOnColumn, $background = false )
    {
        $task = new ScheduledTask($this->connector);
        $task->odsconnector = $this->odsconnector;
        $task->rtpiconnector = $this->rtpiconnector;
        $task->task_id = 0;
        $task->task_name = $taskName;
        $task->event_queue = $eventQueue;
        $task->class_name = $className;
        $task->background = $background;
        $task->trigger_time = $triggerTime;
        $task->trigger_every = $triggerEvery;
        $task->trigger_on_table = $triggerOnTable;
        $task->trigger_on_column = $triggerOnColumn;
        $task->status = false;
        $task->last_executed = false;
        $task->add();
    }
}

?>
