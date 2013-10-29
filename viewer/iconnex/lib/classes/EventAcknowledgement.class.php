<?php

require_once("Event.class.php");

/*
**  Class provides a generic class for sending acks and nacks in response to
**  messages such as DAIP messages, SIRI and internally generated ones
*/
class EventAcknowledgement extends EventUnit
{
    function __construct($context)
    {  
        $this->context = $context;
    }

    function process()
    {
        switch ($this->context->transportMethod)
        {
            case "DAIP":
                $ack = new DAIPAcknowledgement($this->context);
                $ack->process();
                break;

            default:
                echo "Unknown transport method ".$this->context->transportMethod." to acknowledge";
                return false;
        }
        return true;
    }
}

?>
