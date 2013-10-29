<?php

require_once("ActiveItem.class.php");
require_once("gps.utility.php");

class ActiveVehicle extends ActiveItem
{
    private $log;
    private $vehicle_course;
    private $geofence = 20;

    public $vehicle = NULL;
    public $latest_gps_position = NULL;
    public $current_journey_live = NULL; // link to the current journey in the tjl_list
//    public $last_visit = NULL; // link to the last detected visit in the current_journey_live's visits array
    public $visit; // link to the "current" visit, or the first one if we haven't detected any yet

    // TODO average bearing
    // TODO last etm entry with validity assessment

    function __construct($build_code, $tj_list, $tjl_list)
    {
        global $rtpiconnector;

        $this->log = Logger::getLogger(__CLASS__);

        parent::__construct($build_code);

        /* If the vehicle code is AUT, thenb this means the active vehicle
        to be constructed is a "scheduled" vehicle representing a non-tracking vehicle
        .. in this case the the biuld will be irrelevant and set to NULL */
        if ( $build_code == "AUT" )
        {
            // ActiveVehicle relates to tracking vehicle so retrieve the data
            $this->vehicle = new Vehicle($rtpiconnector);
            $this->vehicle->vehicle_code = $build_code;
            if (!$this->vehicle->load(array("vehicle_code")))
            {
                echo "ActiveVehicle->__construct() Failed to load vehicle for scheduled only vehicle $build_code\n";
                exit;
                return false;
            }

            // Scheduled only vehicles dont have a latest GPS position
            $this->latest_gps_position = false;
        }
        else
        {
            // ActiveVehicle relates to tracking vehicle so retrieve the data
            $this->vehicle = new Vehicle($rtpiconnector);
            $this->vehicle->build_id = $this->unit_build->build_id;
            if (!$this->vehicle->load(array("build_id")))
            {
                echo "ActiveVehicle->__construct() Failed to load vehicle for build $build_code\n";
                exit;
                return false;
            }
        
            // Find Unit Status record, if not found create one
            $unit_status = new UnitStatus($rtpiconnector);
            $unit_status->build_id = $this->unit_build->build_id;
            if ($unit_status->load())
            {
                $gps_position = new GPSPosition($rtpiconnector);
                $gps_position->initialiseWithLatLong($unit_status->gpslat, $unit_status->gpslong);
                $gps_position->gps_time = new DateTime($unit_status->gps_time);
                $this->latest_gps_position = $gps_position;
            }
            else
            {
                // Add new unit status - time/conection status will be set late in the event
                $unit_status->build_id = $this->unit_build->build_id;
                $unit_status->add();
            }
        }

        $this->vehicle_course = new VehicleCourse();
    }

    function changeJourney($unit_build, $event_journey_details, $tj_list, $tjl_list)
    {
        global $rtpiconnector;

        // Look for a matching journey in the TimetableJourneyList
        if (!$timetable_journey = $tj_list->getMatchingJourney($unit_build, $event_journey_details))
        {
            echo "ActiveVehicle->changeJourney() tj_list->getMatchingJourney failed for journey_details\n";
            return false;
        }

        // Look for a matching journey in the TimetableJourneyLiveList
        if (!$timetable_journey_live = $tjl_list->getMatchingJourney($event_journey_details, $timetable_journey, $this->vehicle))
        {
            echo "ActiveVehicle->changeJourney() tjl_list->getMatchingJourney failed for journey_details\n";
            return false;
        }

        $this->switchJourney($timetable_journey_live);

        return true;
    }

    function switchJourney($timetable_journey_live)
    {
        echo " => V ".$this->vehicle->vehicle_code." => ";
        if (!$this->current_journey_live)
        {
            echo " new journey " . $timetable_journey_live->timetable_id . " ";
            $this->current_journey_live = $timetable_journey_live;
            $this->current_journey_live->active_vehicle = $this;

            // TODO resume journey from last visit
            $this->visit = $this->current_journey_live->visits[1];
        }
        else
        {
            if ($this->current_journey_live->timetable_id != $timetable_journey_live->timetable_id)
            {
                echo " "
                    . $this->current_journey_live->timetable_id . " to "
                    . $timetable_journey_live->timetable_id . " ";
                $this->current_journey_live->active_vehicle = NULL;
                $this->current_journey_live = $timetable_journey_live;
                $this->current_journey_live->active_vehicle = $this;

                // TODO resume journey from last visit
                $this->visit = $this->current_journey_live->visits[1];
            }
            else
                echo " Already running " . $timetable_journey_live->timetable_id . " ";
        }
    }

    /**
     * @brief Update the current position of this ActiveVehicle
     */
    function updatePosition($gps_position)
    {
        echo "ActiveVehicle->updatePosition() " . $this->vehicle->vehicle_code . " " . $gps_position->plottableString . "\n";
        $this->vehicle_course->update($gps_position);
        $this->latest_gps_position = $gps_position;
    }

    /**
     * @brief update predictions based on the location of the vehicle.
     *
     * Figure out where this ActiveVehicle is on the route then update the
     * predicted arrival and departure times of this vehicle at the visits on
     * the current journey and the next.
     *
     * This should be called after every updatePosition
     *
     * arrival_status values:
     *      "A" => Actual
     *      "E" => Estimated based on timetable/history (maybe should only use this when we have accurate actual arrivals/departures)
     *      "P" => Predicted based on rough idea of arrivals/departures
     *      "H" => Here
     *      "L" => Lost
     *      "?" => ???
     */
    function update($event)
    {
        $on_way_to_sequence = -1;
        $at_a_visit = false;

        if (!$this->current_journey_live)
        {
            $this->log->warn("update() current_journey_live not set");
            return;
        }
        if (!$this->latest_gps_position)
        {
            $this->log->warn("update() latest_gps_position not set");
            return;
        }

        $this->at_visit = false;
        $this->on_way_to_visit = false;
        // Recurse forward from "current" visit
        $this->visit->track($event);

        if (!$at_a_visit && $on_way_to_sequence <= 0)
        {
//            echo "ActiveVehicle->update() looked through trip without finding position on route - what TODO?\n";
        }
    }

    function show()
    {
        if (!isset($this->vehicle))
            echo "ActiveVehicle->show() vehicle is not set\n";
        if (!isset($this->message_time))
            echo "ActiveVehicle->show() message_time is not set\n";
        if (!isset($this->latest_gps_position))
            echo "ActiveVehicle->show() latest_gps_position is not set\n";

        echo $this->vehicle->vehicle_code . " " . $this->message_time->format("Y-m-d H:i:s") . " " . $this->latest_gps_position->plottableString;
        if ($this->current_journey_live)
        {
            echo "\n";
            $this->current_journey_live->show();
        }
        else
            echo " - no live journey\n";
    }
}

?>
