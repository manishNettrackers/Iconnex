<?php

require_once("gps.utility.php");

class VisitVehicleRelationship
{
    protected $log;

    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    /**
     * @brief Figure out the vehicle's relationship to the passed visit and
     * the visit before.
     */
    function track($visit, $event)
    {
        $this->log->debug("track()");
        if (!$this->assess_relationship($visit))
        {
            $this->log->error("track() Failed to assess_relationship for visit " . $visit->sequence);
            return false;
        }

        if ($visit->vehicle_here)
        {
            $visit->here($event);
            return true;
        }
        else
        {
            if (get_class($visit->visit_vehicle_relationship) == "VisitVehicleHere")
            {
                $visit->visited($event);
                return true;
            }
        }

        if ($visit->vehicle_on_way)
        {
            $visit->on_way_here($event);
            return true;
        }

        if ($visit->timetable_journey->active_vehicle->last_visit->sequence < $visit->sequence)
            $visit->lost($event);
    }

    /**
     * @brief Step backwards through the linked list of visits and update the
     * predictions if they were missed.
     */
    function backtrack($visit, $event)
    {
        $this->log->debug("backtrack()");
        if ($visit->arrival_status == "E" || $visit->arrival_status == "S"
        || $visit->departure_status == "E" || $visit->departure_status == "S")
        {   
            $visit->missed($event);
        }

        if ($visit->prev_visit)
            $visit->prev_visit->visit_vehicle_relationship->backtrack($visit->prev_visit, $event);
    }

    /**
     * @brief Assess the current relationship between the current vehicle
     * position and the positions of the passed visit and prev_visit.
     */
    function assess_relationship($visit)
    {
        $this->log->debug("assess_relationship()");
        if (!isset($visit))
            $this->log->error("assess_relationship() visit is not set");
        if (!isset($visit->timetable_journey_live))
            $this->log->error("assess_relationship() tjl is not set");
        if (!isset($visit->timetable_journey_live->active_vehicle))
            $this->log->error("assess_relationship() vehicle is not set");
        if (!isset($visit->timetable_journey_live->active_vehicle->latest_gps_position))
            $this->log->error("assess_relationship() gps_position is not set");
        if (!$visit->timetable_visit)
        {
            $this->log->error("assess_relationship() timetable_visit is not set");
            return false;
        }
        if (!$visit->gps_position)
        {
            $this->log->error("assess_relationship() gps_position is not set");
            return false;
        }

        $this->where($visit);

        return true;
    }

    /**
     * @brief Decide whether to position bus between currently compared stops
     *
     * Decision based on betweenness index && relative angle and that time is 
     * close enough to the predicted time to stop (we dont want to predict
     * position at end of trip that is a long way away).
     * if ($proximity index < 0 && $relative angle > 45 degrees) accept it
     *
     * @return boolean whether or not vehicle is on way here
     */
    function where($visit)
    {
        $this->log->debug("where()");

        $position = $visit->timetable_journey_live->active_vehicle->latest_gps_position;
        $lat = $position->latitude;
        $lng = $position->longitude;
        $visit_lat = $visit->gps_position->latitude;
        $visit_lng = $visit->gps_position->longitude;
        $visit->vehicle_here = false;
        $visit->vehicle_on_way = false;
        $visit->vehicle_angle = false;
        $visit->vehicle_betweenness = false;
        $visit->vehicle_distance = metres_between_coords($lat, $lng, $visit_lat, $visit_lng);

        // If this is the first stop, just use the geofence
        if (!$visit->prev_visit)
        {
            if ($visit->vehicle_distance < $visit->geofence)
            {
                $this->log->debug("where() Inside geofence of first stop");
                $visit->vehicle_here = true;
            }
            else
                $this->log->debug("where() Outside geofence of first stop");

            return;
        }

        $prev_visit_lat = $visit->prev_visit->gps_position->latitude;
        $prev_visit_lng = $visit->prev_visit->gps_position->longitude;
        $visit->prev_visit->vehicle_distance = metres_between_coords($lat, $lng, $prev_visit_lat, $prev_visit_lng);
        $visit->vehicle_angle = subtendedAngle($prev_visit_lat, $prev_visit_lng, $lat, $lng, $visit_lat, $visit_lng);
        $visit->vehicle_betweenness = relativeposition($prev_visit_lat, $prev_visit_lng, $lat, $lng, $visit_lat, $visit_lng);

        // Get unix times for easy comparisons...
        $unit_time_unix = $position->gps_time->getTimestamp();

        $compare_time = $visit->departure_time_datetime;
        if (!$compare_time)
            $compare_time = $position->gps_time;
        $compare_time_unix = $compare_time->getTimestamp();

        $compare_time_pub = $visit->timetable_visit->departure_time_datetime;
        if (!$compare_time_pub)
        {
            $this->log->error("where() cannot check without a published time - returning");
            return false;
        }
        $compare_time_pub_unix = $compare_time_pub->getTimestamp();

        // Calculate the estimated time to stop
        if ($compare_time_unix > $unit_time_unix)
            $est_time_to_stop = $compare_time_unix - $unit_time_unix;
        else
            $est_time_to_stop = $unit_time_unix - $compare_time_pub_unix;

        if ($compare_time_pub_unix > $unit_time_unix)
            $est_time_to_stop_pub = $compare_time_pub_unix - $unit_time_unix;
        else
            $est_time_to_stop_pub = $unit_time_unix - $compare_time_pub_unix;

        if ($visit->vehicle_angle > 30
        && $visit->vehicle_betweenness > -1
        && $visit->vehicle_betweenness < 1.2)
            $visit->vehicle_on_way = true;
        else
        {
            $this->log->debug("where() angle/betweenness not good enough");
            if ($visit->prev_visit->vehicle_distance > 0
            && $visit->prev_visit->vehicle_distance < 120)
            {
                if (!$visit->prev_visit->vehicle_here)
                {
                    $this->log->debug("where() vehicle is just leaving prev stop, on way here");
                    $visit->vehicle_on_way = true;
                }
                else
                    $this->log->debug("where() vehicle is not just leaving prev stop");
            }

            if ($visit->vehicle_distance < 120)
            {
                $this->log->debug("where() vehicle within 120m, on way here");
                $visit->vehicle_on_way = true;
            }
        }

        // Don't say the vehicle is on the way if we can't expect to get to the
        // stop within 15 minutes.
        $predict_thresh = 15 * 60;
        if ($visit->vehicle_on_way 
        && $est_time_to_stop > $predict_thresh
        && $est_time_to_stop_pub > $predict_thresh)
        {
            $this->log->debug("where() vehicle would take too long to get here, can't be on way here");
            $this->log->debug("where() est_time_to_stop $est_time_to_stop, est_time_to_stop_pub $est_time_to_stop_pub");
// TODO this is too simple as there may be cases where the time between stops is greater than 15 minutes.
// $visit->vehicle_on_way = false;
        }

        $in_start_code = "REAL"; // TODO handle CONT trips
        if ($visit->vehicle_on_way && $in_start_code == "CONT")
            $this->log->debug("where() on way to visit " . $visit->sequence . " on CONT trip");

        // Finally check geofence
        if ($visit->prev_visit->vehicle_distance < $visit->prev_visit->geofence
        && $visit->prev_visit->vehicle_distance > 0)
        {
            $this->log->debug("where() thinks bus is at prev_visit"); // TODO handle this
        }

        if ($visit->vehicle_distance < $visit->geofence)
        {
            $this->log->debug("where() vehicle is within geofence");
            $visit->vehicle_on_way = false;
            $visit->vehicle_here = true;
        }
    }

    function update()
    {
        $this->log->debug("update() nothing to do");
    }
}

class VisitVehicleUnknown extends VisitVehicleRelationship
{
    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }
}

class VisitVehicleMissed extends VisitVehicleRelationship
{
    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    function update($visit, $event)
    {
        if ($visit->arrival_status == "E" || $visit->arrival_status == "S")
        {
            $this->log->debug("update() arrival_status " . $visit->arrival_status . " -> M for sequence " . $visit->sequence);
            $visit->arrival_status = "M";
        }
        if ($visit->departure_status == "E" || $visit->departure_status == "S")
        {   
            $this->log->debug("update() departure_status " . $visit->departure_status . " -> M for sequence " . $visit->sequence);
            $visit->departure_status = "M";
        }
    }
}

class VisitVehicleLost extends VisitVehicleRelationship
{
    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    function update($visit, $event)
    {
        $this->log->debug("update()");
        if ($visit->timetable_journey_live->active_vehicle->last_visit)
        {
            if ($visit->timetable_journey_live->active_vehicle->last_visit->sequence < $visit->sequence)
            {
                $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " " . $visit->sequence . " LOST with angle " . $visit->vehicle_angle . " betweenness " . $visit->vehicle_betweenness . " (L)");
                $visit->arrival_status = "L";
                $visit->departure_status = "L";
            }
        }
        else
            $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " no last_visit " . $visit->sequence . " (lost, but leaving status values alone)");
    }
}

class VisitVehicleHere extends VisitVehicleRelationship
{
    public $just_arrived = true;

    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    function update($visit, $event)
    {
        // Arrival prediction only updated if vehicle hadn't already
        // arrived at the stop with a previous position update.
        if ($visit->arrival_status != "H" && $visit->arrival_status != "A" && $visit->arrival_status != "P")
        {
            // Unless we have an "A" arrival_status, then we don't know
            // exactly when we arrived at this stop, but it
            // must be within the time since the last position update. We
            // could estimate arrival to be half the time between the
            // previous position update and the latest one (or something
            // even more fancy), but for now just use the message timestamp.
            $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at timing point " . $visit->sequence . " (arrival H)");
            $visit->arrival_time_datetime = $event->msg_timestamp;
            $visit->arrival_time = $visit->arrival_time_datetime->format("Y-m-d H:i:s");
            $visit->arrival_status = "H";

            // Go backwards and update predictions at previous visits
            // which we missed.
            if ($visit->prev_visit)
                $visit->prev_visit->visit_vehicle_relationship->backtrack($visit->prev_visit, $event);
        }

        // Departure prediction depends on time and timing_point status
        if ($event->receipt_time > $visit->timetable_visit->departure_time)
        {
            // Assume the driver will depart imminently (maybe add 10 seconds?)
            $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at timing point " . $visit->sequence . " (departure E)");
            $visit->departure_time_datetime = $event->msg_timestamp;
            $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
            $visit->departure_status = "E";
        }
        else
        {
            if ($visit->timing_point)
            {
                // Assume the driver will wait to depart on time.
                $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at timing point " . $visit->sequence . " (departure E)");
                $visit->departure_time_datetime = $visit->timetable_visit->departure_time_datetime;
                $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
                $visit->departure_status = "E";
            }
            else
            {
                if ($visit->arrival_status == "A")
                {
                    // With an accurately-known arrival time, we can
                    // use an historic dwell-time to estimate departure
                    // time more accurately. TODO
                    $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at non-timing point " . $visit->sequence . " (departure E)");
                    $visit->departure_time_datetime = $event->msg_timestamp;
                    $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
                    $visit->departure_status = "E";
                }
                else
                {
                    // Not a timing point and we don't have an accurate arrival
                    // time, so assume the driver will depart imminently
                    // (maybe add 10 seconds?)
                    $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " at non-timing point " . $visit->sequence . " (departure E)");
                    $visit->departure_time_datetime = $event->msg_timestamp;
                    $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
                }
            }
        }
    }
}

class VisitVehicleVisited extends VisitVehicleRelationship
{
    public $just_detected = true;

    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    /**
     * @brief Vehicle was previously at this visit but isn't any longer,
     * so update the departure_status as it must have departed.
     */
    function update($visit, $event)
    {
        if ($this->just_detected)
        {
            if ($visit->arrival_status == "H" && $visit->departure_status == "E")
            {
                $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " departed " . $visit->sequence . " (departure P)");
                echo "TODO improve estimation of departure time\n";
                $visit->departure_time_datetime = $event->msg_timestamp;
                $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
                $visit->departure_status = "P";
            }
            else
                $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " just detected departed " . $visit->sequence . "(not changing status)");
        }
        else
            $this->log->debug("update() no change to old visit");
    }
}

class VisitVehicleOnWay extends VisitVehicleRelationship
{
    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    function update($visit, $event)
    {
        $this->log->debug("update() " . $visit->timetable_journey_live->active_vehicle->vehicle->vehicle_code . " on way to visit " . $visit->sequence);

        $udt = new UtilityDateTime();
        $interval_in_seconds = $udt->getDateIntervalInSeconds($visit->travel_time_loc_dateinterval);
        $dist_prorateto = $visit->vehicle_distance;
        if ($dist_prorateto > $visit->prev_visit->vehicle_distance * 2)
            $dist_prorateto = $dist_prorateto * 2;
        $timetonextstop = round(($dist_prorateto / ($visit->prev_visit->vehicle_distance + $dist_prorateto)) * $interval_in_seconds, 0);
        $estimated_arrival_time = $event->msg_timestamp->add(new DateInterval("PT" . $timetonextstop . "S"));
        $this->log->debug("update() estimated_arrival_time is " . $estimated_arrival_time->format("Y-m-d H:i:s"));
        $dist_prorateto = $visit->prev_visit->vehicle_distance;
        if ($dist_prorateto > $visit->vehicle_distance * 2)
            $dist_prorateto = $dist_prorateto * 2;
        $timefromprevstop = round(($dist_prorateto / ($visit->vehicle_distance + $dist_prorateto)) * $interval_in_seconds, 0);
        $estimated_departure_time = $event->msg_timestamp->sub(new DateInterval("PT" . $timefromprevstop . "S"));
        $this->log->debug("update() estimated_departure_time is " . $estimated_departure_time->format("Y-m-d H:i:s"));

        // We have calculated an estimated arrival time at the stop we think a
        // bus is on its way to but we now deduct the estimated travel time
        // because it will get re-added on in the next section
        $estimated_arrival_time = $estimated_arrival_time->sub($visit->travel_time_loc_dateinterval);
        $this->log->debug("update() estimated_arrival_time is " . $estimated_arrival_time->format("Y-m-d H:i:s") . " after subtracting travel_time");

        // If previous stop is layover point or start of trip and predictied
        // time is before the estimated departure then still predict departure
        if (isset($visit->prev_visit->departure_time_pub))
        {
            if ($estimated_departure_time < $visit->prev_visit->departure_time_pub
            && ($visit->prev_visit->sequence == 1 || $visit->prev_visit->timetable_visit->layover == 1))
             // and not m_nolayover ) OR ( wr_dcd_param.pred_layover = "T" AND not m_nolayover ) ) then
            {
                $this->log->debug("update() LAYOVER RULES APPLIED TO PREDICTION");
                $estimated_departure_time = $visit->departure_time_pub;
                $estimated_arrival_time = $estimated_departure_time;
                $this->log->debug("update() estimated_arrival_time is " . $estimated_arrival_time->format("Y-m-d H:i:s"));
            }
        }

        if ($visit->prev_visit->departure_status == "E"
        || $visit->prev_visit->departure_status == "L")
        {
            $visit->prev_visit->departure_status = "P";
            $visit->prev_visit->actual_est = "P";
            $visit->prev_visit->departure_time = $estimated_departure_time;
        }

        if ($visit->prev_visit->arrival_status == "E"
        || $visit->prev_visit->arrival_status == "L")
        {
            $visit->prev_visit->arrival_status = "P";
            $visit->arrival_time = $estimated_departure_time->sub(new DateInterval("PT1S"));
        }
    }
}

class VisitVehicleFuture extends VisitVehicleRelationship
{
    function __construct()
    {
        $this->log = Logger::getLogger(__CLASS__);
    }

    function update($visit, $event)
    {
        $this->log->debug("update()");
        if ($this->last_visit->sequence < $visit->sequence)
        {
            $visit->arrival_status = "E";
            $visit->departure_status = "E";

            if ($visit->prev_visit)
            {
                $visit->arrival_time_datetime = $visit->prev_visit->departure_time_datetime->add($visit->travel_time_loc_dateinterval);
                $visit->arrival_time = $visit->arrival_time_datetime->format("Y-m-d H:i:s");

                $visit->departure_time_datetime = $visit->prev_visit->departure_time_datetime->add($visit->dwell_time_dateinterval);
                $visit->departure_time = $visit->departure_time_datetime->format("Y-m-d H:i:s");
            }
            else
                $this->log->warn("update() visit $sequence has no prev_visit, leaving predictions alone");
        }
    }
}

?>
