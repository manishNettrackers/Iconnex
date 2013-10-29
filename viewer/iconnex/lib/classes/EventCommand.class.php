<?php

/**
** Class: EventCommand
** ------------------------------
**
** An event represeanting a command to an event handler to perform some action
**
*/

class EventCommand
{
    public $command;
    public $arguments;

    function process()
    {
        switch ( $this->command )
        {
            case MessageType::EVENT_BUILD_STATISICS_FROM_DATABASE:
                $this->event_handler->generator->rebuildDatabaseFromDataset();
                break;

            case MessageType::EVENT_INITIALIZE_STATISTICS:
                $this->event_handler->generator->initializeDataStorage();
                break;

            case MessageType::EVENT_SHOW_STATISTICS:
                $this->event_handler->generator->show(true, false);
                break;

            case MessageType::EVENT_SHOW_STATISTICS_RAW:
                $this->event_handler->generator->show(true, true);
                break;

            default:
                echo "Event $this->command unknown\n";
                break;
        }
    }
}
?>
