<?php
/**
* TimetableVisitLive
*
* Datamodel for table timetable_visit_live
*/

require_once("VisitVehicleRelationship.class.php");
require_once("UtilityDateTime.class.php");

define("METRES_IN_A_NAUTICAL_MILE", 1851.85);

class TimetableVisitLive extends DataModel
{
    private $log;

    public $geofence = 40;

    public $gps_position = NULL;
    public $prev_visit = NULL;
    public $next_visit = NULL;
    public $timetable_visit = NULL;
    public $timetable_journey_live = NULL;

    public $vehicle_distance = NULL;
    public $vehicle_on_way = false;
    public $vehicle_here = false;

    // the angle subtended to the vehicle by a line between this visit and the
    // prev_visit. If there is no prev_visit, then this should be set to false.
    public $vehicle_angle = false;

    // A kind of index of how much the vehicle is between this visit and the
    // prev_vist.
    public $vehicle_betweenness = false;

    public $arrival_time_datetime = NULL;
    public $departure_time_datetime = NULL;
    public $dwell_time_dateinterval = NULL;
    public $travel_time_loc_dateinterval = NULL;
    public $travel_time_tp_dateinterval = NULL;
    public $arrival_lateness_dateinterval = NULL;
    public $departure_lateness_dateinterval = NULL;

    public $display_points = false; // Prediction Delivery

    public $visit_vehicle_relationship = NULL;

    function __construct($connector = false)
    {
//echo " dc0 ".Utility::memory_increase(); 
        $this->log = Logger::getLogger(__CLASS__);

        $this->columns = array (
            "fact_id" => new DataModelColumn($this->connector, "fact_id", "serial"),
            "journey_fact_id" => new DataModelColumn($this->connector, "journey_fact_id", "integer"),
            "timetable_id" => new DataModelColumn($this->connector, "timetable_id", "integer"),
            "timetable_visit_id" => new DataModelColumn($this->connector, "timetable_visit_id", "integer"),
            "sequence" => new DataModelColumn($this->connector, "sequence", "integer"),
            "location_id" => new DataModelColumn($this->connector, "location_id", "integer"),
            "prev_id" => new DataModelColumn($this->connector, "prev_id", "integer"),
            "prev_tp_id" => new DataModelColumn($this->connector, "prev_tp_id", "integer"),
            "timing_point" => new DataModelColumn($this->connector, "timing_point", "integer"),
            "arrival_date_id" => new DataModelColumn($this->connector, "arrival_date_id", "integer"),
            "departure_date_id" => new DataModelColumn($this->connector, "departure_date_id", "integer"),
            "arrival_time_id" => new DataModelColumn($this->connector, "arrival_time_id", "integer"),
            "departure_time_id" => new DataModelColumn($this->connector, "departure_time_id", "integer"),
            "arrival_time_pub" => new DataModelColumn($this->connector, "arrival_time_pub", "datetime"),
            "departure_time_pub" => new DataModelColumn($this->connector, "departure_time_pub", "datetime"),
            "arrival_time" => new DataModelColumn($this->connector, "arrival_time", "datetime"),
            "departure_time" => new DataModelColumn($this->connector, "departure_time", "datetime"),
            "dwell_time" => new DataModelColumn($this->connector, "dwell_time", "interval"),
            "travel_time_loc" => new DataModelColumn($this->connector, "travel_time_loc", "interval"),
            "travel_time_tp" => new DataModelColumn($this->connector, "travel_time_tp", "interval"),
            "arrival_status" => new DataModelColumn($this->connector, "arrival_status", "char"),
            "departure_status" => new DataModelColumn($this->connector, "departure_status", "char"),
            "arrival_lateness" => new DataModelColumn($this->connector, "arrival_lateness", "interval"),
            "departure_lateness" => new DataModelColumn($this->connector, "departure_lateness", "interval")
            );

        $this->tableName = "timetable_visit_live";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("fact_id");
        parent::__construct($connector);

        $this->visit_vehicle_relationship = new VisitVehicleUnknown();
    }

    function initialise($timetable_journey, $timetable_journey_live, $sequence)
    {
        $this->timetable_journey_live = $timetable_journey_live;

        $tv = $timetable_journey->visits[$sequence];
        if (!$tv)
        {
            $this->log->error("initialise() failed to find a timetable_visit for fact_id " . $timetable_journey_live->fact_id . ", sequence $sequence - EXIT!");
            exit;
            return false;
        }

        $this->timetable_visit = $tv;

        // Set the rest of the fields from the timetable_visit
        $this->fact_id = 0; // To be saved by object parent
        $this->journey_fact_id = $this->timetable_journey_live->fact_id;
        $this->timetable_id = $tv->timetable_id;
        $this->timetable_visit_id = $tv->timetable_visit_id;
        $this->sequence = $tv->sequence;
        $this->location_id = $tv->location_id;
        $this->prev_id = $tv->prev_id;
        $this->prev_tp_id = $tv->prev_tp_id;
        $this->timing_point = $tv->timing_point;
        $this->arrival_date_id = $tv->arrival_date_id;
        $this->departure_date_id = $tv->departure_date_id;
        $this->arrival_time_id = $tv->arrival_time_id;
        $this->departure_time_id = $tv->departure_time_id;
        if ($tv->timing_point) $this->arrival_time_pub = $tv->arrival_time;
        if ($tv->timing_point) $this->departure_time_pub = $tv->departure_time;
        $this->arrival_time = $tv->arrival_time;
        $this->arrival_status = "S";
        $this->departure_time = $tv->departure_time;
        $this->departure_status = "S";

        $this->dwell_time = $tv->dwell_time;
        $this->travel_time_loc = $tv->travel_time_loc;
        $this->travel_time_tp = $tv->travel_time_tp;
//        $this->arrival_lateness = 
//        $this->departure_lateness = 

        $this->arrival_time_datetime = new DateTime($this->arrival_time);
        $this->departure_time_datetime = new DateTime($this->departure_time);
        $udt = new UtilityDateTime();
        $this->dwell_time_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->dwell_time));
        $this->travel_time_loc_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->travel_time_loc));
        $this->travel_time_tp_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->travel_time_tp));
        $this->arrival_lateness_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->arrival_lateness));
        $this->departure_lateness_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->departure_lateness));

        if (!$this->loadGPSPosition())
        {
            $this->log->error("initialise() Failed to loadGPSPosition for visit $sequence - EXIT!");
            exit;
            return false;
        }

        return true;
    }

    function initialiseAfterLoad($tv, $timetable_journey_live)
    {
        $this->timetable_visit = $tv;
        $this->timetable_journey_live = $timetable_journey_live;
        $this->journey_fact_id = $timetable_journey_live->fact_id;

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
        if (preg_match("/.*:0$/", $this->arrival_lateness) == 1)
        {
            echo "TODO fixing arrival_lateness " . $this->arrival_lateness . "\n";
            $this->arrival_lateness = $this->arrival_lateness . "0";
        }
        if (preg_match("/.*:0$/", $this->departure_lateness) == 1)
        {
            echo "TODO fixing departure_lateness " . $this->departure_lateness . "\n";
            $this->departure_lateness = $this->departure_lateness . "0";
        }
            
        $this->arrival_time_datetime = new DateTime($this->arrival_time);
        $this->departure_time_datetime = new DateTime($this->departure_time);
        $udt = new UtilityDateTime();
        $this->dwell_time_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->dwell_time));
        $this->travel_time_loc_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->travel_time_loc));
        $this->travel_time_tp_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->travel_time_tp));
        $this->arrival_lateness_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->arrival_lateness));
        $this->departure_lateness_dateinterval = new DateInterval($udt->HHMMSSToDateInterval($this->departure_lateness));

        if (!$this->loadGPSPosition())
        {
            $this->log->debug("initialiseAfterLoad() Failed to loadGPSPosition for visit " . $this->sequence . " - EXIT!");
            exit;
            return false;
        }
    }

    /**
     * @brief Lookup the GPS details for this visit in the location table.
     *        Sets the location and gps_position fields for this visit.
     */
    function loadGPSPosition()
    {
        global $rtpiconnector;

        $this->location = new Location($rtpiconnector);
        $this->location->location_id = $this->location_id;
        if (!$this->location->load())
        {
            $this->log->error("loadGPSPosition() failed to load location_id " . $this->location_id);
            return false;
        }

        $latitude = $this->location->latitude_degrees + ($this->location->latitude_minutes / 60);
        $longitude = $this->location->longitude_degrees + ($this->location->longitude_minutes / 60);
        if ($latitude == 0 && $longitude == 0)
            $this->log->warn("loadGPSPosition() location_id " . $this->location_id . " has zero lat and long");

        $this->gps_position = new GPSPosition();
        if (!$this->gps_position->initialiseWithLatLong($latitude, $longitude))
        {
            $this->log->error("loadGPSPosition() gps_position->initialiseWithLatLong failed for $latitude, $longitude");
            exit; // TODO handle this properly
            return false;
        }

        return true;
    }

    function track($event)
    {
        $this->log->debug("track()");
        return $this->visit_vehicle_relationship->track($this, $event);
    }

    function on_way_here($event)
    {
//        $this->log->debug("on_way_here() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " between visits " . ($this->sequence - 1) . " and " . $this->sequence);
        $this->timetable_journey_live->active_vehicle->on_way_to_visit = $this;
        $this->timetable_journey_live->active_vehicle->visit = $this;
        if (get_class($this->visit_vehicle_relationship) == "VisitVehicleOnWay")
            $this->visit_vehicle_relationship->just_detected = false;
        else
            $this->visit_vehicle_relationship = new VisitVehicleOnWay();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();
        if ($this->prev_visit)
            $this->prev_visit->visit_vehicle_relationship->backtrack($this->prev_visit, $event);
        if ($this->next_visit)
            $this->next_visit->future($event);
    }

    function here($event)
    {
//        $this->log->debug("here() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at visit " . $this->sequence);
        $this->timetable_journey_live->active_vehicle->last_visit = $this;
        $this->timetable_journey_live->active_vehicle->at_visit = $this;
        $this->timetable_journey_live->active_vehicle->visit = $this;
        if (get_class($this->visit_vehicle_relationship) == "VisitVehicleHere")
            $this->visit_vehicle_relationship->just_arrived = false;
        else
            $this->visit_vehicle_relationship = new VisitVehicleHere();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();
        if ($this->next_visit)
            $this->next_visit->future($event);
        else
            $this->log->debug("here() with no next_visit");
    }

    function future($event)
    {
//        $this->log->debug("future() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " visit " . $this->sequence);
        $this->visit_vehicle_relationship = new VisitVehicleFuture();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();
        if ($this->next_visit)
            $this->next_visit->future($event);
    }

    function lost($event)
    {
//        $this->log->debug("lost() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " visit " . $this->sequence);
        $this->visit_vehicle_relationship = new VisitVehicleLost();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();
        if ($this->next_visit)
            $this->next_visit->lost($event);
    }

    function missed($event)
    {
//        $this->log->debug("missed() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " visit " . $this->sequence);
        $this->visit_vehicle_relationship = new VisitVehicleMissed();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();
    }

    function visited($event)
    {
//        $this->log->debug("visited() " . $this->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " visit " . $this->sequence);
        $this->timetable_journey_live->active_vehicle->visit = $this->next_visit;
        if (get_class($this->visit_vehicle_relationship) == "VisitVehicleVisited")
            $this->visit_vehicle_relationship->just_departed = false;
        else
            $this->visit_vehicle_relationship = new VisitVehicleVisited();
        $this->visit_vehicle_relationship->update($this, $event);
        $this->save();

        if ($this->next_visit)
            $this->next_visit->visit_vehicle_relationship->track($this->next_visit, $event);
        else
            $this->log->debug("visited() with no next_visit");
    }
}
?>
