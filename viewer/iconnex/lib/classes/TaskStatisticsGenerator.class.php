<?php

/**
** Class: TaskStatisticsGenerator
** --------------------------------
**
** Runs the ad hoc statistics generator for all stats types
*/

class TaskStatisticsGenerator extends ScheduledTask
{
    const DEFAULT_QUEUE_ID = 3000;
    const DEFAULT_QUEUE_NAME = "QSTATS";

    protected $inboundQueue = false;
    public $generator = false;
    protected $queueName = TaskStatisticsGenerator::DEFAULT_QUEUE_NAME;
    protected $generatorClass = "unknown";
    protected $defaultQueueId = 3000;

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Listen for statistics events
    */
    function runTask()
    {
        $eventqueue = SystemKey::getTaskQueue($this->connector, $this->queueName, $this->defaultQueueId);
        $this->generator = new $this->generatorClass($this->connector);
        $this->generator->build();
        $receivedType = 0;
        $msg = false;
        $error = false;

        while (true)
        {   
            // The msg_receive call is blocking by default, so it will wait for a message.
            if (msg_receive($eventqueue, 0, $receivedType, 10000, $msg, true, 0, $error) === true)
            {
                $msg->event_handler = $this;
                $msg->process();
            }
            else
            {
                echo "EventHandler:$this->generator process_msg_queue() msg_receive failed with error $err\n";
            }
        }
    }
}
?>
