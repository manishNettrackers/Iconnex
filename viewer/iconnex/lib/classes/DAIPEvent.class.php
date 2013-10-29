<?php

/**
 * @base DAIP Event
 */
class DAIPEvent
{
    // DAIP Error Number codes (DAIP 1.1 section 3.2.9)
    const DAIP_NO_ERROR_CODE = 0;
    const DAIP_UNKNOWN_SENDER = 1; // e.g. SVID is unknown
    const DAIP_UNKNOWN_RUNNING_Board = 2;
    const DAIP_UNKNOWN_OPERATOR_CODE = 3;
    const DAIP_UNKNOWN_VEHICLE_ID = 4;
    const DAIP_UNKNOWN_SERVICE_CODE = 5;
    const DAIP_UNKNOWN_JOURNEY_NUMBER = 6;
    const DAIP_UNKNOWN_EVENT_RECEIVED = 7;
    const DAIP_UNKNOWN_EVENT_DATA_SENT_WITH_KNOWN_EVENT = 8;
    const DAIP_CONFIG_INFORMATION_NOT_SUPPORTED = 9;
    const DAIP_SERIAL_NO_INFORMATION_NOT_SUPPORTED = 10;
    const DAIP_DUPLICATE_VEHICLE_ID = 11;
    const DAIP_NO_SVID_AVAILABLE = 12;
    const DAIP_CORRUPT_MESSAGE_RECEIVED = 13;
//14 .. 127 Reserved_codes
//128..255  Implementation_defined_codes

    public $errorStatus = DAIP_NO_ERROR_CODE;

    public $context = false;

    function __construct($context)
    {
        $this->context = $context;
    }
}

?>
