<?php

require_once("DataModel.class.php");

class TimetableJourney extends DataModel
{
    public $visits = NULL;

    function __construct($connector = false)
    {
        $this->columns = array (
            "timetable_id" => new DataModelColumn($this->connector, "timetable_id", "serial"),
            "ext_timetable_id" => new DataModelColumn($this->connector, "ext_timetable_id", "integer"),
            "ttb_date_id" => new DataModelColumn($this->connector, "ttb_date_id", "integer"),
            "actual_date_id" => new DataModelColumn($this->connector, "actual_date_id", "integer"),
            "over_midnight" => new DataModelColumn($this->connector, "over_midnight", "integer"),
            "journey_pattern_id" => new DataModelColumn($this->connector, "journey_pattern_id", "integer"),
            "time_id" => new DataModelColumn($this->connector, "time_id", "integer"),
            "operator_id" => new DataModelColumn($this->connector, "operator_id", "integer"),
            "route_id" => new DataModelColumn($this->connector, "route_id", "integer"),
            "route_code" => new DataModelColumn($this->connector, "route_code", "char", 8),
            "duty_no" => new DataModelColumn($this->connector, "duty_no", "char", 10),
            "running_no" => new DataModelColumn($this->connector, "running_no", "char", 10),
            "trip_no" => new DataModelColumn($this->connector, "trip_no", "char", 10),
            "etm_trip_no" => new DataModelColumn($this->connector, "etm_trip_no", "char", 10),
            "start_time" => new DataModelColumn($this->connector, "start_time", "datetime"),
            "end_time" => new DataModelColumn($this->connector, "end_time", "datetime"),
            "duration" => new DataModelColumn($this->connector, "duration", "interval"),
            "direction" => new DataModelColumn($this->connector, "direction", "integer"),
            "number_stops" => new DataModelColumn($this->connector, "number_stops", "integer"),
            "next_timetable_id" => new DataModelColumn($this->connector, "next_timetable_id", "integer"),
            "next_journey_start" => new DataModelColumn($this->connector, "next_journey_start", "datetime"),
            "prev_timetable_id" => new DataModelColumn($this->connector, "prev_timetable_id", "integer"),
            "prev_journey_end" => new DataModelColumn($this->connector, "prev_journey_end", "datetime")
            );
        $this->tableName = "timetable_journey";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("timetable_id");

        parent::__construct($connector);

        $this->visits = array();
    }

    function loadByEvent($unit_build, $eventJourneyDetails)
    {
        global $rtpiconnector;

        $route_code = trim($eventJourneyDetails->service_code);

        $route = new Route($rtpiconnector);
        if ($route_code)
        {
            $route->route_code = $route_code;
            $route->operator_id = $unit_build->operator_id;
        }
        $route->load(array("route_code", "operator_id"));

        $trip_no = trim($eventJourneyDetails->journey_number);
        $scheduled_start = trim($eventJourneyDetails->scheduled_start);
        $timetable_id = trim($eventJourneyDetails->timetable_id);

        $now = new DateTime();
        $sql = "SELECT * FROM timetable_journey";
        $sql .= " WHERE CURRENT BETWEEN (start_time - INTERVAL(59) MINUTE TO MINUTE) AND (end_time + INTERVAL(59) MINUTE TO MINUTE)";

        // If event contains timetable_id we dont need any othe help
        if ($timetable_id)
            $sql .= " AND timetable_id = \"" . $timetable_id . "\"";
        else
        {
            if ($route->route_id)
                $sql .= " AND route_id = " . $route->route_id;
            if ($trip_no)
                $sql .= " AND trip_no = \"" . $trip_no . "\"";
            if ($scheduled_start)
                $sql .= " AND EXTEND(start_time, HOUR TO MINUTE) = DATETIME($scheduled_start) HOUR TO MINUTE";
        }

        $sql .= ";";

        $ret = $rtpiconnector->fetch1SQL($sql);

        if (!$ret)
        {
            echo "TimetableJourney->loadByEvent() NOTFOUND for $sql\n";
            return false;
        }

        $this->timetable_id = $ret["timetable_id"];
        $this->ext_timetable_id = $ret["ext_timetable_id"];
        $this->ttb_date_id = $ret["ttb_date_id"];
        $this->actual_date_id = $ret["actual_date_id"];
        $this->over_midnight = $ret["over_midnight"];
        $this->journey_pattern_id = $ret["journey_pattern_id"];
        $this->time_id = $ret["time_id"];
        $this->operator_id = $ret["operator_id"];
        $this->route_id = $ret["route_id"];
        $this->route_code = $ret["route_code"];
        $this->duty_no = $ret["duty_no"];
        $this->running_no = $ret["running_no"];
        $this->trip_no = $ret["trip_no"];
        $this->etm_trip_no = $ret["etm_trip_no"];
        $this->start_time = $ret["start_time"];
        $this->end_time = $ret["end_time"];
        $this->duration = $ret["duration"];
        $this->direction = $ret["direction"];
        $this->number_stops = $ret["number_stops"];
        $this->next_timetable_id = $ret["next_timetable_id"];
        $this->next_journey_start = $ret["next_journey_start"];
        $this->prev_timetable_id = $ret["prev_timetable_id"];
        $this->prev_journey_end = $ret["prev_journey_end"];

        return $this->buildVisitsArray();
    }

    function buildVisitsArray()
    {
        global $rtpiconnector;

        $tv_tmp = new TimetableVisit($rtpiconnector);
        $sql = "select * from timetable_visit
                where timetable_id = " . $this->timetable_id . "
                order by timetable_visit_id, sequence;";
        $tvs = $tv_tmp->sqlToInstanceArray($sql);

        if (!$tvs)
        {
            echo "TimetableJourney->buildVisitsArray() NOTFOUND for $sql\n";
            return false;
        }

        foreach ($tvs as $tv)
        {
            $tv->initialiseAfterLoad($tv);
            $this->visits[$tv->sequence] = $tv;
        }

        return true;
    }
}

?>
