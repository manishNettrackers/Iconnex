<?php

global $acknowledgementCounter;
$acknowledgementCounter = 22;
global $DAIPReturnSocket;
$DAIPReturnSocket = false;

/**
 * @brief Functionality for processing a DAIP Journey Details message
 */
class DAIPAcknowledgement extends DAIPEvent
{
    function __construct($context)
    {
        parent::__construct($context);
    }

    function process()
    {
        $this->sendAcknowledgement();
    }

    function sendAcknowledgement()
    {
        global $acknowledgementCounter;
        global $DAIPReturnSocket;
        global $gMessageCounter;
        $gMessageCounter++;
        $msg = NULL;

        // Message Time Stamp
        $now = gmdate("ymdHis");
        $year = substr($now, 0, 2);
        $month = substr($now, 2, 2);
        $date = substr($now, 4, 2);
        $hour = substr($now, 6, 2);
        $minute = substr($now, 8, 2);
        $second = substr($now, 10, 2);

        if ($this->context->ackRequired)
        {
            echo "DAIPAcknowledgement->sendAcknowledgement() acknowledgment message\n";
            // Wrapper Header
            $formatVersionFirstByte = 1;
            $formatVersionSecondByte = 16;

            // Acknowledgement Flags: 1 byte, bit-mapped
            // Bit 0 - always 1 "This is an Acknowledgement"
            // Bit 1 - Message Acknowledgement Flag
            //  - 0 = Message is not Acknowledged (Error in message)
            //  - 1 = Message is Acknowledged
            // Bit 2 - Reserved
            // Bit 3 - Live or Test/Evaluation Message
            //  - 0 = Live Message
            //  - 1 = Test Message
            // Bits 4-7 Reserved
            $acknowledgementFlags = 3;    // 00000001 = 1 => live positive acknowledgement
            if ($this->context->statusResponse != DAIPEvent::DAIP_NO_ERROR_CODE)
                $acknowledgementFlags = 1;    // 00000011 = 3 => live negative acknowledgement

            $acknowledgementCounter++;

            $messageCounterReference = $this->context->messageSequence;
            $sessionVehicleIdentifier = $this->context->originId;
            $msg = pack("a3a20nCCCCCCCCCCCCCCC",
                "ACK",
                $this->context->sourceAddress,
                $this->context->returnPort,
                $formatVersionFirstByte,
                $formatVersionSecondByte,
                $acknowledgementFlags,
                ($acknowledgementCounter & 0xFF00) >> 8,
                $acknowledgementCounter & 0xFF,
                ($messageCounterReference & 0xFF00) >> 8,
                $messageCounterReference & 0xFF,
                ($sessionVehicleIdentifier & 0xFF00) >> 8,
                $sessionVehicleIdentifier & 0xFF,
                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        }
        else
        {
            // #60 EventCentreToOBU
            echo "DAIPAcknowledgement->sendAcknowledgement() Error Notification with Error Number 1\n";

            // Wrapper Header
            $formatVersionFirst = 1;
            $formatVersionSecond = 16;
            $messageFlags = 0;
            $messageCounterFirst = ($gMessageCounter & 0xFF00) >> 8;
            $messageCounterSecond = $gMessageCounter & 0xFF;
            $sessionVehicleIdentifier = $this->context->originId;
            $optionalDataFields = 0;

            $messageID = 60;
            $len = 1;
            $type = 3;
            $code = 0;

            $msg = pack("a3a20nCCCCCCCCCCCCSCCCCCCCCCC",
                "ACK",
                $this->context->sourceAddress,
                $this->context->returnPort,
                $formatVersionFirst,
                $formatVersionSecond,
                $messageFlags,
                $messageCounterFirst,
                $messageCounterSecond,
                ($sessionVehicleIdentifier & 0xFF00) >> 8,
                $sessionVehicleIdentifier & 0xFF,
                ($optionalDataFields & 0xFF00) >> 8,
                $optionalDataFields & 0xFF,
                $messageID,
                ($gMessageCounter & 0xFF00) >> 8,    // Sequence ID
                $gMessageCounter & 0xFF,
                0, // Reference Sequence ID
                $type, // Message Type
                $code, // Message Code
                $len, // Message Parameters Length (Optional)
                $this->context->statusResponse, // Error Number
                hexdec($year),
                hexdec($month),
                hexdec($date),
                hexdec($hour),
                hexdec($minute),
                hexdec($second));
        }

        $DAIPReturnSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($DAIPReturnSocket, $msg, strlen($msg), 0, "10.8.1.254", 2065);
        socket_close($DAIPReturnSocket);
    }
}

?>
