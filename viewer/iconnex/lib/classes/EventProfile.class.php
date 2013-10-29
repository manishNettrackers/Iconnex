<?php
/**
* EventProfile
*
* Datamodel for table event_profile
*
*/

class EventProfile extends DataModel
{
    private $operationDays = array();

    function __construct($connector = false)
    {
        $this->columns = array (
            "evprf_id" => new DataModelColumn ( $this->connector, "evprf_id", "serial" ),
            "description" => new DataModelColumn ( $this->connector, "description", "char", 40 ),
            );

        $this->tableName = "event_profile";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("evprf_id");
        parent::__construct($connector);
    }


    function fetchEvents()
    {
        $sql = "SELECT 
                event_pattern.operational, 
                event_pattern.org_id, 
                event_pattern.org_working_holiday,
                event.operator_id,
                event.event_id,
                event.event_code,
                event.event_desc,
                event.event_tp,
                event.spdt_start,
                event.spdt_end,
                event.rpdt_start,
                event.rpdt_end,
                event.rpdy_start,
                event.rpdy_end
                FROM event_profile
                JOIN event_pattern ON event_profile.evprf_id = event_pattern.evprf_id
                JOIN event ON event.event_id = event_pattern.event_id
                WHERE event_profile.evprf_id = $this->evprf_id";
        $stmt = $this->connector->executeSQL($sql);
        
        while ( $row = $stmt->fetch() )
        {
            $evt = new OperationalPeriod($this->connector);
            $evt->operator_id = $row["operator_id"];
            $evt->event_id = $row["event_id"];
            $evt->event_code = $row["event_code"];
            $evt->event_desc = $row["event_desc"];
            $evt->event_tp = $row["event_tp"];
            $evt->spdt_start = $row["spdt_start"];
            $evt->spdt_end = $row["spdt_end"];
            $evt->rpdt_start = $row["rpdt_start"];
            $evt->rpdt_end = $row["rpdt_end"];
            $evt->rpdy_start = $row["rpdy_start"];
            $evt->rpdy_end = $row["rpdy_end"];

            $ep = new EventPattern();
            $ep->evprf_id = $this->evprf_id;
            $ep->event_id = $evt->event_id;
            $ep->operational = $row["operational"];
            $ep->org_id = $row["org_id"];
            $ep->org_working_holiday = $row["org_working_holiday"];

            $this->operationDays[] = 
                    array (
                        "pattern" => $ep,
                        "event" => $evt,
                        );
        }
    }

    /**
    ** Checks if this event profile covers the passed date
    */
    function operationalOnDate($testdate, $testForNonOperation = false, $dmyformat = "Y/m/d")
    {
        $operational = false;
        foreach ($this->operationDays as $v )
        {
            $opperiod = $v["event"];

            // Day of Week Check
            if ( $opperiod->event_tp == OperationalPeriod::DAY_OF_WEEK_OP )
            {
                $testdow = $testdate->format("w");
                //echo "$v->eventCode $testdow >=  $opperiod->rpdy_start && $testdow <= $opperiod->rpdy_end \n";
                if ( $testdow >= $opperiod->rpdy_start && $testdow <= $opperiod->rpdy_end )
                {
                    $operational = true;
                }
            }

            // Date Range Check
            if ( $opperiod->event_tp == OperationalPeriod::DATE_PERIOD_OP )
            {
                //echo "$dmyformat .. ".$opperiod->spdt_start;

                //echo $opperiod->spdt_start."\n";
                $startdate = DateTime::createFromFormat($dmyformat, $opperiod->spdt_start);
                $enddate = DateTime::createFromFormat($dmyformat, $opperiod->spdt_end);
                $startymd = $startdate->format("Ymd");
                $endymd = $enddate->format("Ymd");
                $testymd = $testdate->format("Ymd");
                //echo "$opperiod->event_code $testymd >=  $startymd, $endymd\n";
                if ( $testymd >= $startymd && $testymd <= $endymd )
                {
                    $operational = true;
                }
            }

            // Date Range Check
            if ( $opperiod->event_tp == OperationalPeriod::REPEATED_DAY_OP )
            {
                //echo "$dmyformat .. ".$opperiod->rpdt_start;
                $startdate = DateTime::createFromFormat("d-m", $opperiod->rpdt_start);
                $enddate = DateTime::createFromFormat("d-m", $opperiod->rpdt_end);
                $startymd = $startdate->format("Ymd");
                $endymd = $enddate->format("Ymd");
                $testymd = $testdate->format("Ymd");
                //echo "$opperiod->event_code $testymd >=  $startymd, $endymd\n";
                if ( $testymd >= $startymd && $testymd <= $endymd )
                {
                    $operational = true;
                }
            }
        }

        if ( $testForNonOperation )
        {
            $operational = !$operational;
        }

        return $operational;
    }
}
?>
