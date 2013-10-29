<?php


/**
 * @base Identifies the context of an event ( DAIP message, SIRI message etc )
 * The context describes originator specific information like external sender code,
 * sender sequence number, network info for returning status info back
 */
class EventContext
{
    public $originId = false;
    public $transportMethod = false;
    public $sourceAddress = false;
    public $commsType = "UDP";
    public $messageSequence = false;
    public $ackRequired = false;
    public $statusResponse = false;
    public $socket = false;
}

?>
