<?php

/**
** Class: TaskJourneyStatusSnapshot
** ------------------------------
**
** Populates the journey_status_snapshot table with entries for each current
** journey so that it can be quickly selected from for web views etc
*/
class TaskJourneyStatusSnapshot extends ScheduledTask
{

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        $status = $this->initialiseSnapshots();
        if ( $status )
        	$ct = 0;
        while ( true )
        {
            if ( $ct++ > 20 )
                die;
            $status = $this->snapshot();
            sleep (5);
        }
        return $status;
    }

    /*
    ** initialiseSnapshots
    **
    ** Creates the snapshot tables
    **
    **
    */
    function initialiseSnapshots()
    {
        $vehicleStatus = new SnapshotVehicleStatus($this->connector);
        $status = $vehicleStatus->dropTable();
        $status = $vehicleStatus->createTable();
        if ( $status )
        {
            $journeyStatus = new SnapshotJourneyStatus($this->connector);
            $status = $journeyStatus->dropTable();
            $status = $journeyStatus->createTable();
        }
        return $status;

    }

    /*
    ** snapshot
    **
    ** Builds journey status snapshot table
    **
    **
    */
    function snapshot()
    {
        $now = new DateTime();

        // Turn on Dirty Read
        if (!$this->connector->executeSQL("SET ISOLATION TO DIRTY READ"))
            return false;

        $this->connector->executeSQL("DROP TABLE t_vehpos", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_maxtrip", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_maxlateness", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_latenesses", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_trips", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_notin", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_next_aut_loc", "CONTINUE");

        // ------------------------------------------------------------------------
        // Build list of all vehicles with associated tracking Status
        // ------------------------------------------------------------------------
        $sql ="
            SELECT vehicle_id, message_time, gpslat, -gpslong gpslong, 'On Route            ' vehicle_status, message_type, route_status
            FROM vehicle a, unit_status b, outer unit_status_rt c
            WHERE a.build_id = b.build_id
            AND a.build_id = c.build_id
            AND message_time > CURRENT - 1 UNITS DAY";

        $sql .= " UNION
            SELECT vehicle_id, extend(CURRENT, year to second) , 0,0, 'Scheduled' vehicle_status, 0 message_type, 'U' route_status
            FROM vehicle a
            WHERE vehicle_code = 'AUT'";

        $sql .= " INTO TEMP t_vehpos WITH  NO LOG";

        if (!$this->connector->executeSQL($sql))
           return;


        $sql = "UPDATE t_vehpos SET vehicle_status = 'Not Tracking' WHERE vehicle_status != 'Scheduled' and route_status NOT IN ( 'R' )";
        if (!$this->connector->executeSQL($sql))
           return;

        $sql = "UPDATE t_vehpos SET vehicle_status = 'Waiting for Start' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'P' )";
        if (!$this->connector->executeSQL($sql))
           return;

        $sql = "UPDATE t_vehpos SET vehicle_status = 'Stuck' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'S' )";
        if (!$this->connector->executeSQL($sql))
           return;

        $sql = "UPDATE t_vehpos SET vehicle_status = 'Idle' WHERE vehicle_status != 'Scheduled' and route_status IN ( 'W' )";
        if (!$this->connector->executeSQL($sql))
           return;

        $sql = "UPDATE t_vehpos SET vehicle_status = 'Off Line' WHERE vehicle_status != 'Scheduled' and message_time < CURRENT - 10 UNITS MINUTE";
        if (!$this->connector->executeSQL($sql))
           return;

        // ------------------------------------------------------------------------------
        // Now build a list of current trips which need to be reflected in the despatcher
        // -----------------------------------------------------------------------------
        // For each current trip ( Real TIme or Scheduled ) identify the last actual 
        // identified, so we can calculate last stop lateness
        $sql = "
        SELECT b.vehicle_id, b.fact_id, max(sequence) sequence
        FROM timetable_visit_live a, timetable_journey_live b, t_vehpos
        WHERE 1 = 1
        AND a.journey_fact_id = b.fact_id
        AND b.vehicle_id = t_vehpos.vehicle_id
        AND departure_time < CURRENT + 10 UNITS MINUTE
        AND ( 
            departure_status IN ( 'A', 'P' ) OR  
            ( sequence = 1 AND departure_status != 'C' ) OR
            ( start_code = 'AUT' AND departure_time <= CURRENT )
        )
        AND start_code IN ( 'REAL', 'AUT' )
        GROUP BY 1,2
        INTO TEMP t_maxtrip WITH NO LOG
        ";
        if (!$this->connector->executeSQL($sql))
          return;

$this->connector->debug = 0;
        // For each current trip ( Real TIme or Scheduled ) identify the last scheduled
        // departure time
        $sql = "SELECT b.fact_id, max(sequence) sequence
            FROM timetable_visit_live a, timetable_journey_live b, t_vehpos
            WHERE 1 = 1
            AND b.vehicle_id = t_vehpos.vehicle_id
            AND a.journey_fact_id = b.fact_id
            AND departure_time < CURRENT
            AND departure_time_pub IS NOT NULL
            AND departure_time IS NOT NULL
            AND date(departure_time) > '1899-12-31'
            AND departure_status != 'C'
            AND start_code IN ( 'REAL', 'AUT' )
            GROUP BY 1
            INTO TEMP t_maxlateness WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        // For each current trip, calculate lateness value as the difference between actual departure time at last
        // timing point and scheduled departure time at last timing point
        $sql ="
            SELECT a.journey_fact_id fact_id, a.departure_time next_departure, a.departure_time_pub next_departure_time_pub,
            (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub )) || '' next_lateness,
            a.sequence next_rpat, a.arrival_status, a.departure_status, 'RUNNING' start_status
            FROM timetable_visit_live a, t_maxlateness b, location c
            WHERE 1 = 1
            AND a.journey_fact_id = b.fact_id
            AND a.sequence = b.sequence
            AND departure_time_pub IS NOT NULL
            AND departure_time IS NOT NULL
            AND departure_status != 'C'
            AND date(departure_time) > '1899-12-31'
            AND date(departure_time_pub) > '1899-12-31'
            AND a.location_id = c.location_id
            INTO TEMP t_latenesses WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        // Where vehicles have not departed flag their status as NOT LEFT
        $sql ="
            UPDATE t_latenesses SET start_status = 'NOTLEFT'
            WHERE next_rpat = 1 
            AND departure_status = 'E'
            ";

        // Also cursors may be used for example :-
        if (!$this->connector->executeSQL($sql))
           return;

        // Where vehicles have not departed and are late set status to Late Departing
        $sql ="
            UPDATE t_latenesses SET start_status = 'LATEDEP'
            WHERE next_rpat = 1 
            AND departure_status = 'E'
            AND next_departure > next_departure_time_pub + 2 UNITS MINUTE
            ";
        if (!$this->connector->executeSQL($sql))
           return;

        // ---------------------------------------------------------------------------
        // Create journey status tables. The status is a combination of all tracked trips
        // and their vehicles along with all non-tracking vehicles
        // ---------------------------------------------------------------------------

        // Populate table with all current journeys where vehicles are tracking
        $sql ="
            SELECT b.vehicle_id, b.fact_id, a.location_id, a.sequence, arrival_status, departure_status, 
            arrival_time, departure_time, departure_time_pub, 
            (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
            (( INTERVAL(0) MINUTE(9) TO MINUTE ) + ( departure_time - departure_time_pub ) ) || '' lateness_min,
            route_code, running_no, f.duty_no, f.trip_no, operator_code, h.operator_id, f.route_id, start_code, trip_status, driver_id,
            ( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
            - ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
            c.location_code next_location,
            c.description next_name, 
            employee_code, fullname employee_name, f.end_time,
            s.description service_code
            FROM timetable_visit_live a, timetable_journey_live b, t_maxtrip d, timetable_journey f,  service s, operator h, location c, outer employee w
            WHERE 1 = 1
            AND a.journey_fact_id = b.fact_id
            AND f.service_id = s.service_id
            AND d.fact_id = a.journey_fact_id
            AND d.sequence = a.sequence
            AND b.timetable_id = f.timetable_id
            AND departure_status != 'C'
            AND f.operator_id = h.operator_id
            AND a.location_id = c.location_id
            AND w.employee_id = b.driver_id
            INTO TEMP t_trips WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;


        // Calculate a list of all vehicles not in the tracked vehicle list
        $sql ="
            SELECT t_vehpos.vehicle_id, vehicle_status
            FROM t_vehpos, vehicle vehicle
            WHERE t_vehpos.vehicle_id NOT IN ( SELECT vehicle_id FROM t_trips )
            AND t_vehpos.vehicle_id = vehicle.vehicle_id
            AND vehicle_code != 'AUT'
            INTO TEMP t_notin WITH NO LOG
            ";
        if (!$this->connector->executeSQL($sql))
           return;

        // Add the non-tracking vehicles into the journey status view
        $sql ="
            INSERT INTO t_trips ( vehicle_id, route_code, lateness, operator_code, operator_id )
            SELECT t_notin.vehicle_id, 'N/A', 0, operator.operator_code, operator.operator_id
            FROM t_notin,  vehicle vehicle, operator
            where t_notin.vehicle_id = vehicle.vehicle_id
            and vehicle.operator_id = operator.operator_id
            ";

        if (!$this->connector->executeSQL($sql))
           return;
        $this->connector->debug = false;

        // Set table of next location gps coords for scheduled journeys
        $sql = "SELECT journey_fact_id, ( latitude_degrees + ( latitude_minutes / 60 ) ) prev_latitude,
                            ( longitude_degrees + ( longitude_minutes / 60 ) ) prev_longitude,
                            latitude_heading, longitude_heading
                            FROM t_trips
                            JOIN timetable_visit_live tvl on t_trips.fact_id = tvl.journey_fact_id and t_trips.sequence - 1 = tvl.sequence
                            join location on tvl.location_id = location.location_id
                            WHERE start_code = 'AUT'
                            INTO TEMP t_next_aut_loc WITH NO LOG";
        if (!$this->connector->executeSQL($sql))
           return;

        $sql = "UPDATE t_next_aut_loc SET prev_longitude = - prev_longitude WHERE longitude_heading = 'W'";
        if (!$this->connector->executeSQL($sql))
           return;

        // Now we have a journey status and a vehicle status commit them to the journey snapshot tables

        $now = new DateTime();

        $this->rows_affected = 0;
        $vehicles_affected = 0;
        $journeys_affected = 0;


        // .. starting with vehicle
        $vehicleStatus = new SnapshotVehicleStatus($this->connector);
        $sql = "SELECT vehicle_id, message_time, gpslat, gpslong, vehicle_status, message_type, route_status, '' row_status, CURRENT row_changed FROM t_vehpos";
        $vehicles = $vehicleStatus->sqlToInstanceArray($sql);
        foreach ( $vehicles as $vehicle )
        {
            $statusveh = new SnapshotVehicleStatus($this->connector);
            $statusveh->vehicle_id = $vehicle->vehicle_id;
            if ( !$statusveh->load() )
            {
                $vehicle->row_changed = $now->format("Y-m-d H:i:s");
                $vehicle->row_status = "OK";
                $vehicles_affected++;
                $vehicle->add();
            }
            else
            {
                if ( $statusveh->differs($vehicle, false, array("row_changed", "row_status")) )
                {
                    $vehicle->row_changed = $now->format("Y-m-d H:i:s");
                    $vehicle->row_status = "OK";
                    $vehicle->save();
                    $this->rows_affected ++;
                    $vehicles_affected++;
                }
            }
        }
        echo "Vehicles Affected = $vehicles_affected \n";

        // .. followed by journey
        $journeyStatus = new SnapshotJourneyStatus($this->connector);

        $sql = "SELECT t_trips.*,
                t_latenesses.next_departure, t_latenesses.next_departure_time_pub, t_latenesses.next_lateness, t_latenesses.next_rpat,
                '' row_status, CURRENT row_changed, gpslat curr_latitude, gpslong curr_longitude, t_next_aut_loc.prev_latitude, t_next_aut_loc.prev_longitude
                FROM t_trips left join t_latenesses ON t_trips.fact_id = t_latenesses.fact_id
                LEFT JOIN t_vehpos ON t_vehpos.vehicle_id = t_trips.vehicle_id
                LEFT JOIN t_next_aut_loc ON t_next_aut_loc.journey_fact_id = t_trips.fact_id ";
        $journeys = $journeyStatus->sqlToInstanceArray($sql);
        foreach ( $journeys as $journey )
        {
            // Round latitude longitude
            $journey->curr_longitude = round($journey->curr_longitude, 6);
            $journey->curr_latitude = round($journey->curr_latitude, 6);
            $journey->next_longitude = round($journey->next_longitude, 6);
            $journey->next_latitude = round($journey->next_latitude, 6);
            $journey->prev_longitude = round($journey->prev_longitude, 6);
            $journey->prev_latitude = round($journey->prev_latitude, 6);

            if ( $journey->next_longitude && $journey->next_longitude != 0 && 
                $journey->next_latitude && $journey->next_latitude != 0 &&
                $journey->curr_latitude && $journey->curr_latitude != 0 &&
                $journey->curr_longitude && $journey->curr_longitude != 0 
                )
            {
                //echo "$journey->next_latitude, $journey->next_longitude, $journey->curr_latitude, $journey->curr_longitude \n";
                $journey->next_stop_bearing = UtilityGeo::bearingDegreesFrom2LatLong($journey->curr_latitude, $journey->curr_longitude, $journey->next_latitude, $journey->next_longitude);
                //echo "got ".$journey->next_stop_bearing."\n";
            }
            else if ( $journey->prev_longitude && $journey->prev_longitude != 0 && 
                $journey->prev_latitude && $journey->prev_latitude != 0 &&
                $journey->next_latitude && $journey->next_latitude != 0 &&
                $journey->next_longitude && $journey->next_longitude != 0 
                )
            {
                //echo "$journey->prev_latitude, $journey->prev_longitude, $journey->next_latitude, $journey->next_longitude \n";
                $journey->next_stop_bearing = UtilityGeo::bearingDegreesFrom2LatLong($journey->next_latitude, $journey->next_longitude, $journey->prev_latitude, $journey->prev_longitude);
                //echo "got ".$journey->next_stop_bearing."\n";
            }
            else
                $journey->next_stop_bearing = false;

            $lookupby = false;
            if ( $journey->start_code != "AUT" )
                 $lookupby = array("vehicle_id");
            $statusjourney = new SnapshotJourneyStatus($this->connector);
            $statusjourney->vehicle_id = $journey->vehicle_id;
            $statusjourney->fact_id = $journey->fact_id;
            if ( !$statusjourney->load($lookupby) )
            {
                $journey->row_changed = $now->format("Y-m-d H:i:s");
                $journey->row_status = "OK";
                $journey->add();
                $this->rows_affected ++;
                $journeys_affected++;
            }
            else
            {
                if ( $statusjourney->differs($journey, false, array("row_changed", "row_status") ) )
                {
                    if ( $journey->vehicle_id == 143 )
                    {
                    echo $journey->vehicle_id." /".$journey->fact_id."/ ".$journey->start_code."\n";
                    var_dump($lookupby);
                    }
                    $journey->row_changed = $now->format("Y-m-d H:i:s");
                    $journey->row_status = "OK";
                    $journey->save($lookupby);
                    $this->rows_affected ++;
                    $journeys_affected++;
                }
            }
        }
        echo "Journeys Affected = $journeys_affected \n";
        $journeys = false;

        // .. Now flag all journeys that are deleted
        $journeyStatus = new SnapshotJourneyStatus($this->connector);
        $sql = "SELECT ".$journeyStatus->tableName.".*, '' row_status, CURRENT row_changed FROM ".$journeyStatus->tableName. " WHERE start_code = 'AUT' AND fact_id NOT IN ( SELECT fact_id FROM t_trips ) AND row_status != 'DELETED'";

        $journeys = $journeyStatus->sqlToInstanceArray($sql);
        foreach ( $journeys as $journey )
        {
            echo "DELETING".$journey->trip_no."\n";
            //$journey->dump();
            //$journey->row_changed = $now->format("Y-m-d H:i:s");
            $journey->row_changed = substr($journey->row_changed, 0, 19);
            $journey->row_status = "DELETED";
            $journey->save();
            $this->rows_affected ++;
        }

        // Remove AUT trips that ended a couple of minutes ago
        $sql = "SELECT ".$journeyStatus->tableName.".*, '' row_status, CURRENT row_changed FROM ".$journeyStatus->tableName. " WHERE start_code = 'AUT' AND fact_id IN ( SELECT fact_id FROM t_trips  where end_time < current - 2 units minute ) AND row_status != 'DELETED'";

        $journeys = $journeyStatus->sqlToInstanceArray($sql);
        foreach ( $journeys as $journey )
        {
            echo "DELETING".$journey->trip_no."\n";
            //$journey->dump();
            //$journey->row_changed = $now->format("Y-m-d H:i:s");
            $journey->row_changed = substr($journey->row_changed, 0, 19);
            $journey->row_status = "DELETED";
            $journey->save();
            $this->rows_affected ++;
        }


        // Clear out journeys
        $sql = "DELETE FROM snapshot_journey_status WHERE row_status = 'DELETED' AND row_changed < CURRENT - 10 UNITS MINUTE";
        $this->connector->executeSQL($sql);


    }
}
?>
