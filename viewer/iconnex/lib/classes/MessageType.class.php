<?php
/**
* MessageType
*
* Datamodel for table message_type
*
*/

class MessageType extends DataModel
{
    // RTPI legacy events
    const CMNO_ETM_INVALID_JOURNEY = 235;
    const CMNO_ETM_VALID_JOURNEY = 238;

    // Server generated events
    const SYSTEM_TASK_START = 2001;
    const SYSTEM_TASK_STOP = 2002;
    const SYSTEM_TASK_RECOVER = 2003;

    // Server Event Commands
    const EVENT_BUILD_STATISICS_FROM_DATABASE = 10001;
    const EVENT_INITIALIZE_STATISTICS = 10002;
    const EVENT_SHOW_STATISTICS = 10003;
    const EVENT_SHOW_STATISTICS_RAW = 10004;

    // Message Types representing external events
    const EMERGENCY_NEED_ASSISTANCE = 500000000;
    const EMERGENCY_NEED_ASSISTANCE_PHYSICAL_ASSAULT = 500000001;
    const EMERGENCY_NEED_ASSISTANCE_PASSENGER_SICK = 500000002;
    const EMERGENCY_ACCIDENT = 500000010;
    const EMERGENCY_ACCIDENT_NEED_EMERGENCY_SERVICES = 500000011;
    const EMERGENCY_OBSTRUCTION_NEED_TO_DIVERT = 500000020;
    const EMERGENCY_OBSTRUCTION_NEED_TO_DIVERT_REASON = 500000021;
    const EMERGENCY_DIVERTING = 500000030;
    const EMERGENCY_DIVERTING_REASON = 500000031;
    const EMERGENCY_ABANDONING_JOURNEY = 500000040;
    const EMERGENCY_ABANDONING_JOURNEY_REASON = 500000041;
    const EMERGENCY_CURTAILING_JOURNEY = 500000050;
    const EMERGENCY_CURTAILING_JOURNEY_REASON = 500000051;
// 500000060 .. 500002550 Not used
    const SERVICE_REQUEST_PMR_RADIO_SESSION_DRIVER = 500010000;
    const SERVICE_REQUEST_PMR_RADIO_SESSION_VEHICLE = 500010001;
    const SERVICE_REQUEST_PMR_RADIO_SESSION_RADIOID = 500010002;
    const SERVICE_ACCEPT_NEW_DUTY = 500010010;
    const SERVICE_UNABLE_TO_ACCEPT_NEW_DUTY = 500010020;
    const SERVICE_ACCEPT_REST_DAY = 500010030;
    const SERVICE_UNABLE_TO_ACCEPT_REST_DAY = 500010040;
    const SERVICE_ACCEPT_OVERTIME = 500010050;
    const SERVICE_UNABLE_TO_ACCEPT_OVERTIME = 500010060;
    const SERVICE_REQUEST_RELIEF = 500010070;
    const SERVICE_ACKNOWLEDGE_RECEIPT_OF_INCOMING_MESSAGE = 500010080;
// 500010090 .. 500012550 Not used

    const VEHICLE_STATUS_PUNCTURE = 500020000;
    const VEHICLE_STATUS_LOW_OIL_PRESSURE = 500020010;
    const VEHICLE_STATUS_HIGH_ENGINE_TEMP = 500020020;
    const VEHICLE_STATUS_PASSENGER_LOAD = 500020030;
// 500020040 .. 500022550 Not used
// 50003xxxx .. 50126xxxx Not used

    const FREE_FORMAT_TEXT_MESSAGE_TEXT_STRING = 501270000;
    const FREE_FORMAT_TEXT_MESSAGE_PREDEFINED_MESSAGE = 501270010;
// 501260020 .. 501262550 Not used

    const VEHICLE_LOCATION_DEPARTING_FROM_STOP = 501280000;
    const VEHICLE_LOCATION_CROSSING_TLP_TRIGGER_LINE = 501280010;
    const VEHICLE_LOCATION_ARRIVING_STOP = 501280020;
    const VEHICLE_LOCATION_OFF_ROUTE = 501280030;
    const VEHICLE_LOCATION_ON_ROUTE = 501280040;
    const VEHICLE_LOCATION_ON_DIVERSION_STOP = 501280050;
    const VEHICLE_LOCATION_DEPOT_EXIT = 501280060;
    const VEHICLE_LOCATION_DEPOT_ENTRY = 501280061;
// 501280070 .. 501282550 Not used

    const VEHICLE_STATUS_CONFIG_INFO = 501290000;
    const VEHICLE_STATUS_SERIAL_NO = 501290010;
// 501290020 .. 501292550 Not used
// 50130xxxx .. 50199xxxx Not used
// 50200xxxx .. 50239xxxx To be used by for proprietary codes without control
// 50240xxxx .. 50249xxxx To be used for testing and development of new codes
// 502500000 .. 50254255x To be uniquely assigned pairs of codes for supplier specific extensions
// 502550000 TBA
// 502552550 TBA

    function __construct($connector = false)
    {
        $this->columns = array (
            "msg_type" => new DataModelColumn ( $this->connector, "msg_type", "integer" ),
            "description" => new DataModelColumn ( $this->connector, "description", "char", 30 ),
            "ack_reqd" => new DataModelColumn ( $this->connector, "ack_reqd", "char", 1 ),
            "raise_alert" => new DataModelColumn ( $this->connector, "raise_alert", "smallint" ),
            "alert_status" => new DataModelColumn ( $this->connector, "alert_status", "char", 1 ),
            "email_address" => new DataModelColumn ( $this->connector, "email_address", "char", 30 )
            );

        $this->tableName = "message_type";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("msg_type");
        parent::__construct($connector);
    }
}
?>
