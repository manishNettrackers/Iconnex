<?php

define("EVENT_MESSAGE", 1);
include("Event.class.php");

class EventHandler
{
    private $msg_queue_key = false;
    private $msg_queue = false;
    private $desired_msg_type = EVENT_MESSAGE;
    private $received_msg_type = false;
    private $max_msg_size = 4096;
    private $msg = false;

    public $active_list;
    public $tj_list;
    public $tjl_list;

    public $tempDisplayPoint = false;

    /**
     * @brief constructor
     */
    function __construct($msg_queue_key)
    {
        $this->msg_queue_key = $msg_queue_key;
        $this->active_list = new ActiveList();
        $this->tj_list = new TimetableJourneyList();
        $this->tjl_list = new TimetableJourneyLiveList();
    }

    function start()
    {
//        global $rtpiconnector;
//        $rtpiconnector->executeSQL("delete from timetable_visit_live;");
//        $rtpiconnector->executeSQL("delete from timetable_journey_live;");
        $this->build_temporary_tables();

        // load journeys from timetable_journey_live, then create the
        // active_list using the journeys in the tjl_list
        //$this->connecto->debug = 1;
        $this->tjl_list->load($this->tj_list);
        $this->active_list->load($this->tj_list, $this->tjl_list);

        $this->msg_queue = msg_get_queue($this->msg_queue_key);
        $this->process_msg_queue();
    }

    /*
     * @brief Loop forever reading events from the message queue and processing them
     */
    function process_msg_queue()
    {
        $err = NULL;

        // Loop forever listening on the socket and processing packets
        while (true)
        {
            // Build Temporary Tables relevant ot the event handler on a periodic refresh basis
            $this->build_temporary_tables();

            // The msg_receive call is blocking by default, so it will wait for a message.
            if (msg_receive($this->msg_queue, $this->desired_msg_type, $this->received_msg_type, $this->max_msg_size, $this->msg, true, 0, $err) === true)
            {
                if ($this->received_msg_type == $this->desired_msg_type)
                {
                    $this->msg->event_handler = $this;
                    $this->msg->process();
                }
                else
                    echo "ERROR: Received invalid message type from queue...\n";
            }
            else
            {
                echo "EventHandler process_msg_queue() msg_receive failed with error $err\n";
                sleep(1);
            }

            // Perform periodic stale journey clear out
            $this->tjl_list->clearStaleJourneys();

//            echo "PPP After Read Memory : ".Utility::memory_increase()."\n";

        }
    }

    /*
    ** Create initial temporary tables for use by EventHandler services
    */
    function build_temporary_tables()
    {
        global $rtpiconnector;
        // Build Working Stop Display Point Table
        if ( !$this->tempDisplayPoint )
            $this->tempDisplayPoint = new TempDisplayPoint($rtpiconnector);
        $this->tempDisplayPoint->buildTable();
    }
}
?>
