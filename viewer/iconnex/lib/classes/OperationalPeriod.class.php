<?php
/**
* OperationalPeriod
*
* Datamodel for table event
*
*/

class OperationalPeriod extends DataModel
{
    const DATE_PERIOD_OP = 1;
    const REPEATED_DAY_OP = 2;
    const DAY_OF_WEEK_OP = 3;

    function __construct($connector = false)
    {
        $this->columns = array (
            "operator_id" => new DataModelColumn ( $this->connector, "operator_id", "integer", false, false ),
            "event_id" => new DataModelColumn ( $this->connector, "event_id", "serial" ),
            "event_code" => new DataModelColumn ( $this->connector, "event_code", "char", 40 ),
            "event_desc" => new DataModelColumn ( $this->connector, "event_desc", "char", 30 ),
            "event_tp" => new DataModelColumn ( $this->connector, "event_tp", "char", 1 ),
            "spdt_start" => new DataModelColumn ( $this->connector, "spdt_start", "date" ),
            "spdt_end" => new DataModelColumn ( $this->connector, "spdt_end", "date" ),
            "rpdt_start" => new DataModelColumn ( $this->connector, "rpdt_start", "datetimemonthtoday" ),
            "rpdt_end" => new DataModelColumn ( $this->connector, "rpdt_end", "datetimemonthtoday" ),
            "rpdy_start" => new DataModelColumn ( $this->connector, "rpdy_start", "smallint" ),
            "rpdy_end" => new DataModelColumn ( $this->connector, "rpdy_end", "smallint" ),
            );

        $this->tableName = "event";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("event_id");
        parent::__construct($connector);

    }
}
?>
