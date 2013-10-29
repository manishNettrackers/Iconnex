<?php
/**
* TimetableVisit
*
* Datamodel for table timetable_visit
*
*/

class TimetableVisit extends DataModel
{
    public $arrival_time_datetime = NULL;
    public $departure_time_datetime = NULL;
    public $dwell_time_dateinterval = NULL;
    public $travel_time_loc_dateinterval = NULL;
    public $travel_time_tp_dateinterval = NULL;

    function __construct($connector = false)
    {
        $this->columns = array (
            "timetable_visit_id" => new DataModelColumn($this->connector, "timetable_visit_id", "serial"),
            "timetable_id" => new DataModelColumn($this->connector, "timetable_id", "integer"),
            "sequence" => new DataModelColumn($this->connector, "sequence", "integer"),
            "location_id" => new DataModelColumn($this->connector, "location_id", "integer"),
            "prev_id" => new DataModelColumn($this->connector, "prev_id", "integer"),
            "prev_tp_id" => new DataModelColumn($this->connector, "prev_tp_id", "integer"),
            "timing_point" => new DataModelColumn($this->connector, "timing_point", "integer"),
            "arrival_date_id" => new DataModelColumn($this->connector, "arrival_date_id", "integer"),
            "departure_date_id" => new DataModelColumn($this->connector, "departure_date_id", "integer"),
            "arrival_time_id" => new DataModelColumn($this->connector, "arrival_time_id", "integer"),
            "departure_time_id" => new DataModelColumn($this->connector, "departure_time_id", "integer"),
            "arrival_time" => new DataModelColumn($this->connector, "arrival_time", "datetime"),
            "departure_time" => new DataModelColumn($this->connector, "departure_time", "datetime"),
            "dwell_time" => new DataModelColumn($this->connector, "dwell_time", "interval"),
            "travel_time_loc" => new DataModelColumn($this->connector, "travel_time_loc", "interval"),
            "travel_time_tp" => new DataModelColumn($this->connector, "travel_time_tp", "interval"),
            "layover" => new DataModelColumn($this->connector, "layover", "integer")
            );

        $this->tableName = "timetable_visit";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("timetable_visit_id");
        parent::__construct($connector);
    }

    function initialiseAfterLoad($tv)
    {
        $this->timetable_visit = $tv;

        // Fix dodgy intervals from Informix driver
        if (preg_match("/.*:0$/", $this->dwell_time) == 1)
        {
            echo "TODO fixing dwell_time " . $this->dwell_time . "\n";
            $this->dwell_time = $this->dwell_time . "0";
        }
        if (preg_match("/.*:0$/", $this->travel_time_loc) == 1)
        {
            echo "TODO fixing travel_time_loc " . $this->travel_time_loc . "\n";
            $this->travel_time_loc = $this->travel_time_loc . "0";
        }
        if (preg_match("/.*:0$/", $this->travel_time_tp) == 1)
        {
            echo "TODO fixing travel_time_tp " . $this->travel_time_tp . "\n";
            $this->travel_time_tp = $this->travel_time_tp . "0";
        }
            
        $this->arrival_time_datetime = new DateTime($this->arrival_time);
        $this->departure_time_datetime = new DateTime($this->departure_time);
        $this->dwell_time_dateinterval = DateInterval::createFromDateString($this->dwell_time);
        $this->travel_time_loc_dateinterval = DateInterval::createFromDateString($this->travel_time_loc);
        $this->travel_time_tp_dateinterval = DateInterval::createFromDateString($this->travel_time_tp);
    }
}
?>
