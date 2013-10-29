<?php

/**
** Class: TaskBoardStatusSnapshot
** ------------------------------
**
** Populates the stop_status_snapshot table with entries for each current
** stop so that it can be quickly selected from for web views etc
*/
class TaskBoardStatusSnapshot extends ScheduledTask
{

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        if ( $this->connector->get_request_item("initialise", false ) )
            $status = $this->initialiseSnapshots();

        $ct = 0;
        while ( true )
        {
            if ( $ct++ > 20 )
                die;
            $status = $this->snapshot();
            sleep(60);
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
        $stopStatus = new SnapshotBoardStatus($this->connector);
        $status = $stopStatus->dropTable();
        $status = $stopStatus->createTable();
        return $status;
    }

    /*
    ** snapshot
    **
    ** Builds stop status snapshot table
    **
    **
    */
    function snapshot()
    {
        $now = new DateTime();

        // Turn on Dirty Read
        if (!$this->connector->executeSQL("SET ISOLATION TO DIRTY READ"))
            return false;

        $this->connector->executeSQL("DROP TABLE t_ttb_status", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_actdet", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_diversions", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_allocveh", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_vehpos", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_maxtrip", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_laststop", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_maxlateness", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_latenesses", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_trips", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_results", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_now_real_time", "CONTINUE");

        $today = new DateTime();
        $tomorrow = new DateTime();
        $tomorrow->Add(new DateInterval ("P1D" ));
        $ctoday = $today->format("Ymd");
        $ctomorrow = $tomorrow->format("Ymd");
        $dmytoday = $today->format("'d-m-Y'");
        $dmytomorrow = $tomorrow->format("'d-m-Y'");

        // Get list of all trips for today and tomorrow and link with any active trips or previously run trips
        $sql = "SELECT a.operator_id, a.timetable_id, a.trip_no, a.running_no, a.route_id, a.duty_no, a.ext_timetable_id pub_ttb_id, a.actual_date_id, a.ttb_date_id, a.start_time scheduled_start, 'REAL' start_code, d.vehicle_code, d.vehicle_id, e.start_code act_start, extend(a.start_time, hour to second) operation_hms, date(a.start_time) operation_date, a.over_midnight, 
                f.vehicle_id act_veh_id, f.vehicle_code act_veh, a.start_time journey_from, a.end_time journey_to, e.actual_start, e.trip_status, '                ' active_status, next_timetable_id, next_journey_start, c.fact_id eff_sched, e.fact_id act_sched, c.fact_id arc_sched, 'DELETED' current_status
                FROM timetable_journey a, outer ( timetable_journey_fact c, vehicle d ), outer ( timetable_journey_live e, vehicle f ) 
                WHERE a.actual_date_id between $ctoday and $ctomorrow
                AND a.timetable_id = c.timetable_id
                --AND date(c.actual_start) = $dmytoday
                AND c.vehicle_id = d.vehicle_id 
                AND a.timetable_id = e.timetable_id 
                --AND date(e.actual_start) = $dmytoday
                AND e.vehicle_id = f.vehicle_id 
                --AND a.route_id = 1457
                --AND publish_tt.starT_time > '14:00:00'
                --AND publish_tt.starT_time < '16:00:00'
                --AND a.trip_no = '200'
                INTO TEMP t_ttb_status WITH NO LOG";


        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = " UPDATE t_ttb_status SET eff_sched = act_sched WHERE act_sched IS NOT NULL";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = " SELECT t_ttb_status.act_sched, journey_from, journey_to, '          ' performance, MIN(departure_time_pub) pub_start, MAX(arrival_time_pub) pub_end, MIN(arrival_time) rtpi_start, MAX(arrival_time) rtpi_end" .
            " FROM t_ttb_status, timetable_visit_live" .
            " WHERE t_ttb_status.act_sched = timetable_visit_live.journey_fact_id " .
            " AND act_sched IS NOT NULL " .
            " AND arrival_status != 'C' " .
            " AND departure_status != 'C' " .
            " GROUP BY 1, 2, 3, 4 " .
            " INTO TEMP t_actdet WITH NO LOG";
        if ( !$this->connector->executeSQL($sql) )
            return false;
        
        $sql = " UPDATE t_actdet" .
            " SET ( rtpi_start, rtpi_end ) = ( pub_start, pub_end ) " .
            " WHERE rtpi_start IS NULL ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = " UPDATE t_actdet" .
            " SET ( performance ) = ( 'LATE' ) " .
            " WHERE rtpi_end > journey_to ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( act_start, act_veh, act_veh_id, active_status ) = ( 'DONE', vehicle_code, vehicle_id, 'DONE' ) " .
            " WHERE act_start IS NULL " .
            " AND start_code IS NOT NULL";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( active_status ) = ( 'CURRENT' ) " .
            " WHERE actual_start IS NOT NULL " .
            " AND journey_to IS NOT NULL " .
            " AND CURRENT BETWEEN journey_from AND journey_to";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( active_status ) = ( 'CURRENT' ) " .
            " WHERE act_sched IN ( SELECT act_sched FROM t_actdet " .
                " WHERE CURRENT BETWEEN rtpi_start AND rtpi_end )" .
                " AND act_sched IS NOT NULL";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( act_start, active_status ) = ( 'RUNNING', 'RUNNING' ) " .
            " WHERE act_start = 'REAL'";
        if ( !$this->connector->executeSQL($sql) ) return false;


        //$sql = "UPDATE t_ttb_status SET ( act_veh, act_veh_id, act_start, active_status ) = ( NULL, NULL, 'SCH', 'SCH' ) " .
        $sql = "UPDATE t_ttb_status SET ( act_start, active_status ) = ( 'SCH', 'SCH' ) " .
            " WHERE act_veh = 'AUT' AND active_status in ( 'RUNNING', 'CURRENT') ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( active_status, act_start ) = ( 'CURLATE', 'CURLATE' ) " .
            " WHERE act_sched IN ( SELECT act_sched FROM t_actdet " .
                " WHERE CURRENT BETWEEN rtpi_start AND rtpi_end " .
                " AND rtpi_end > journey_to )" .
                " AND act_sched IS NOT NULL";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( act_start, active_status ) = ( 'NEXT', 'NEXT' ) " .
            " WHERE act_start = 'CONT'";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "UPDATE t_ttb_status SET ( trip_status ) = ( NULL ) " .
            " WHERE trip_status = 'A'";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "SELECT UNIQUE wef_date, wet_date, b.pub_ttb_id " .
           " FROM tt_mod a, tt_mod_trip b " .
           " WHERE a.mod_id = b.mod_id " .
           " AND a.location_id IS NOT NULL " .
           " INTO TEMP t_diversions WITH NO LOG";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "SELECT UNIQUE alloc_vehicle, vehicle_code alloc_vehcode, wef_date, wet_date, b.pub_ttb_id " .
          " FROM tt_mod a, tt_mod_trip b, vehicle c " .
          " WHERE a.mod_id = b.mod_id " .
          " AND mod_type = 'V' " .
          " AND a.alloc_vehicle = c.vehicle_id " .
          " INTO TEMP t_allocveh WITH NO LOG";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql ="
        SELECT vehicle_id, message_time, gpslat, -gpslong gpslong
        FROM vehicle a, unit_status b
        WHERE a.build_id = b.build_id
        AND message_time > CURRENT - 10 UNITS MINUTE
        UNION
        SELECT vehicle_id, extend(CURRENT, year to second) , 0,0
        FROM vehicle a
        WHERE vehicle_code = 'AUT'
        INTO TEMP t_vehpos WITH  NO LOG";
        if ( !$this->connector->executeSQL($sql) ) return false;

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
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql ="
        SELECT b.vehicle_id, b.fact_id, max(sequence) sequence
        FROM timetable_visit_live a, timetable_journey_live b, t_vehpos
        WHERE 1 = 1
        AND a.journey_fact_id = b.fact_id
        AND b.vehicle_id = t_vehpos.vehicle_id
        AND start_code IN ( 'REAL' )
        GROUP BY 1,2
        INTO TEMP t_laststop WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

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
        AND departure_status != 'S'
        AND start_code IN ( 'REAL', 'AUT' )
        GROUP BY 1
        INTO TEMP t_maxlateness WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql ="
        SELECT a.journey_fact_id fact_id, a.departure_time next_departure, a.departure_time_pub next_departure_time_pub,
        (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub )) || '' next_lateness,
        a.sequence next_rpat
        FROM timetable_visit_live a, t_maxlateness b, location c
        WHERE 1 = 1
        AND a.journey_fact_id = b.fact_id
        AND a.sequence = b.sequence
        AND departure_time_pub IS NOT NULL
        AND departure_time IS NOT NULL
        AND departure_status != 'C'
        AND departure_status != 'S'
        AND date(departure_time) > '1899-12-31'
        AND date(departure_time_pub) > '1899-12-31'
        AND a.location_id = c.location_id
        INTO TEMP t_latenesses WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql ="
        SELECT b.vehicle_id, b.fact_id, a.location_id, a.sequence, arrival_status, departure_status, 
        arrival_time, departure_time, departure_time_pub, 
        (( INTERVAL(0) SECOND(9) TO SECOND ) + ( departure_time - departure_time_pub ) ) || '' lateness,
        (( INTERVAL(0) MINUTE(9) TO MINUTE ) + ( departure_time - departure_time_pub ) ) || '' lateness_min,
        route_code, running_no, f.duty_no, f.trip_no, operator_code, h.operator_id, f.route_id, start_code, trip_status, driver_id,
        ( latitude_degrees + ( latitude_minutes / 60 ) ) next_latitude,
        - ( longitude_degrees + ( longitude_minutes / 60 ) ) next_longitude,
        c.location_code next_location,
        c.description next_name, r.sequence maxord
        FROM timetable_visit_live a, timetable_journey_live b, t_maxtrip d, timetable_journey f,  operator h, location c, t_laststop r
        WHERE 1 = 1
        AND a.journey_fact_id = b.fact_id
        AND d.fact_id = a.journey_fact_id
        AND b.fact_id = r.fact_id
        AND d.sequence = a.sequence
        AND b.timetable_id = f.timetable_id
        AND departure_status != 'C'
        AND f.operator_id = h.operator_id
        AND a.location_id = c.location_id
        INTO TEMP t_trips WITH NO LOG
        ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        // Set lateness to zero if bus has already finished the trip its on or if bus is early but at first stop
        $sql = "
        update t_trips set ( lateness, lateness_min ) = ( 0, 0 )
        where  sequence = maxord";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "
        update t_trips set ( lateness, lateness_min ) = ( 0, 0 )
        where  sequence = 1 and lateness < 0 ";
        if ( !$this->connector->executeSQL($sql) ) return false;

        $diversionLink = "outer";
        $cancelledLink = "outer ( tt_mod, tt_mod_trip )," ;
        $nexttripLink = "outer";
        $allocvehLink = "outer";
        $latenessLink = "outer";

        $chkShowDiverted = false;
        $chkShowCancelled = false;
        $chkShowDutyChanges = false;
        $chkShowAllocated = false;
        $chkShowLateJourneys = false;
        $chkShowCurrent = false;

        if ($chkShowDiverted)
            $diversionLink = "";
        if ($chkShowCancelled)
            $cancelledLink = " tt_mod, tt_mod_trip, ";
        if ($chkShowDutyChanges)
            $nexttripLink = "";
        if ($chkShowAllocated)
            $allocvehLink = "";
        if ($chkShowLateJourneys)
            $latenessLink = "";


        /*
        $sql = "SELECT active_status, scheduled_start , timetable_id,  t_ttb_status.pub_ttb_id, ttb_date_id day, t_ttb_status.over_midnight, t_timetable.operator_code," .
            " t_timetable.route_code, service_code, t_timetable.start_time, event_code, t_timetable.trip_no, " .
            " t_timetable.runningno, t_timetable.duty_no, next_pub.duty_no next_duty, next_pub.start_time next_duty_time, act_veh, act_veh_id, operation_date, t_ttb_status.trip_status, t_timetable.operator_id, " .
            " act_start, mod_type, mod_status, t_ttb_status.journey_from, extend(t_ttb_status.journey_to, hour to second) journey_to, act_sched, arc_sched, t_diversions.pub_ttb_id diversion, alloc_vehcode, pub_start, pub_end, rtpi_start, rtpi_end, performance, lateness, lateness_min, departure_time - departure_time_pub || ''  real_lateness" .
            " from t_ttb_status, " . $cancelledLink . $nexttripLink . " publish_tt next_pub, " . $allocvehLink . " t_allocveh, " . $diversionLink . " t_diversions, outer t_actdet, $latenessLink t_trips " . 
            " where 1 = 1".
            " and t_ttb_status.operation_date = tt_mod.wef_date" .
            " and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id" .
            " and t_ttb_status.eff_sched = t_trips.schedule_id" .
            " and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id" .
            " and tt_mod.location_id IS NULL" .
            " and tt_mod.mod_id = tt_mod_trip.mod_id" .
            " and t_ttb_status.next_pub_ttb = next_pub.pub_ttb_id" .
            " and t_timetable.duty_no <> next_pub.duty_no" .
            " and t_ttb_status.operation_date between t_diversions.wef_date and t_diversions.wef_date" .
            " and t_ttb_status.pub_ttb_id = t_diversions.pub_ttb_id" .
            " and t_ttb_status.operation_date between t_allocveh.wef_date and t_allocveh.wef_date" .
            " and t_ttb_status.pub_ttb_id = t_allocveh.pub_ttb_id" .
            " and t_ttb_status.act_sched = t_actdet.schedule_id";

        if ($chkShowDutyChanges)
            $sql .= " and next_pub.duty_no is not null ";

        if ($chkShowCancelled)
            $sql .= " and mod_type = 'C'";

        if ($chkShowDiverted)
            $sql .= " and t_diversions.pub_ttb_id > 0";

        if ($chkShowCurrent)
            $sql .= " and active_status IN ( 'CURRENT', 'CURLATE', 'CUREARLY' )";

        if ( $chkShowLateJourneys)
            $sql .= " and ( lateness < -1200 OR lateness > 300 )";
            //$sql .= " and ( active_status IN ( 'CURLATE', 'CUREARLY' ) OR lateness < -60 OR lateness > 300 )";

        $sql .= " INTO TEMP t_results WITH NO LOG";

        if ( !$this->connector->executeSQL($sql) ) return false;

        $sql = "CREATE INDEX i_t_loconrt ON t_loconrt ( location_id );";
        if ( !$this->connector->executeSQL($sql) )
            return false;
            */

        // Flag completed trips
        $sql = "update t_ttb_status 
           set ( active_status ) = ( 'DONE' )
           where arc_sched IS NOT NULL";
        $this->connector->executeSQL($sql);

        // Falg current trips
        $sql = "update t_ttb_status 
           set ( current_status ) = ( 'OK' )
           where active_status IN ( 'CURRENT', 'CURLATE', 'CUREARLY', 'SCH', 'RUNNING' )";
        $this->connector->executeSQL($sql);
        //$this->connector->dumpSQL("SELECT * FROM t_ttb_status WHERE trip_no = '226'");

        // .. followed by stop
        $boardStatus = new SnapshotBoardStatus($this->connector);
        $sql = 
        "SELECT t_ttb_status.timetable_id, active_status, scheduled_start , t_ttb_status.pub_ttb_id, t_ttb_status.operation_date, t_ttb_status.over_midnight, operator.operator_code, route.route_code, extend(operation_hms, hour to second) start_time, dow_name event_code, t_ttb_status.trip_no, t_ttb_status.running_no runningno , t_ttb_status.duty_no, next_pub.duty_no next_duty, next_pub.start_time next_duty_time, act_veh_id vehicle_id, act_veh, operation_date, t_ttb_status.trip_status, t_ttb_status.operator_id, act_start, mod_type, mod_status, t_ttb_status.journey_from, t_ttb_status.journey_to journey_to, t_ttb_status.act_sched, arc_sched, t_diversions.pub_ttb_id diversion, alloc_vehcode, pub_start, pub_end, rtpi_start, rtpi_end, performance, lateness, lateness_min, departure_time - departure_time_pub || '' real_lateness , t_ttb_status.route_id, current_status
        from t_ttb_status, date_dimension, operator, route, outer ( tt_mod, tt_mod_trip ),outer timetable_journey next_pub, outer t_allocveh, outer t_diversions, outer t_actdet, outer t_trips 
        where 1 = 1
        and t_ttb_status.operator_id = operator.operator_id
        and t_ttb_status.actual_date_id = date_dimension.date_id
        and t_ttb_status.route_id = route.route_id
        and t_ttb_status.operation_date = tt_mod.wef_date 
        and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id 
        and t_ttb_status.eff_sched = t_trips.fact_id 
        and t_ttb_status.pub_ttb_id = tt_mod_trip.pub_ttb_id 
        and tt_mod.location_id IS NULL 
        and tt_mod.mod_id = tt_mod_trip.mod_id 
        and t_ttb_status.next_timetable_id = next_pub.timetable_id 
        and t_ttb_status.duty_no <> next_pub.duty_no 
        and t_ttb_status.operation_date between t_diversions.wef_date and t_diversions.wef_date 
        and t_ttb_status.pub_ttb_id = t_diversions.pub_ttb_id 
        and t_ttb_status.operation_date between t_allocveh.wef_date 
        and t_allocveh.wef_date 
        and t_ttb_status.pub_ttb_id = t_allocveh.pub_ttb_id 
        and t_ttb_status.act_sched = t_actdet.act_sched ";
        //and active_status IN ( 'CURRENT', 'CURLATE', 'CUREARLY' )";
        //$this->connector->dumpSQL($sql);


        $boards = $boardStatus->sqlToInstanceArray($sql);
        foreach ( $boards as $board )
        {
            if ( $board->timetable_id == 15364 )
            {
                echo $board->vehicle_id."\n";
                $board->dump();
            }
            //$board->dump();
            if ( $board->active_status == 'C' )
            {
                $board->dump();
            }
            $statusboard = new SnapshotBoardStatus($this->connector);
            $statusboard->timetable_id = $board->timetable_id;
            $statusboard->vehicle_id = $board->vehicle_id;
            if ( $board && preg_match( "/0 /", $board->real_lateness ) )
            {
                $board->real_lateness = preg_replace("/0 /", "", $board->real_lateness);
            }
            if ( !$statusboard->load() )
            {
                //echo "not found ".$statusboard->timetable_id." ".$statusboard->vehicle_id."\n";
                echo "N";
                $board->row_changed = $now->format("Y-m-d H:i:s");
                $board->row_status = "OK";
                $board->add();
                $this->rows_affected ++;
            }
            else
            {
//if ( $board->trip_no == "74" && $board->scheduled_start == "2012-12-30 16:02:00" && $board->act_start == "SCH")
//{
    //echo $board->timetable_id."-".$board->active_status."/".$statusboard->active_status."\n";
    //$board->dump();
//}
                if ( $statusboard->differs($board, false, array("real_lateness", "row_changed", "row_status") ) )
                {
                    echo "differ ".$statusboard->timetable_id." ".$statusboard->vehicle_id."\n";
                    $board->row_changed = $now->format("Y-m-d H:i:s");
                    $board->row_status = "OK";
                    $board->save();
                    $this->rows_affected ++;
                }
            }
        }
        echo "\n";

        // .. Now flag all journeys that are deleted
        $journeyStatus = new SnapshotJourneyStatus($this->connector);

        // .. Now flag all Boards that are deleted
        $boardStatus = new SnapshotBoardStatus($this->connector);
        $sql = "SELECT ".$boardStatus->tableName.".*, '' row_status, CURRENT row_changed FROM ".$boardStatus->tableName. " WHERE scheduled_start < CURRENT - 1 UNITS DAY AND row_status != 'DELETED'";

        $boards = $boardStatus->sqlToInstanceArray($sql);
        $ct = 0;
        foreach ( $boards as $board )
        {   
            //echo "DELETING".$board->timetable_id."\n";
            echo "D";
            //$board->dump();
            //$board->row_changed = $now->format("Y-m-d H:i:s");
            $board->row_changed = substr($board->row_changed, 0, 19);
            $board->row_status = "DELETED";
            $board->save();
            $this->rows_affected ++;
            $ct++;
        }
        if ( $ct > 0 )
            echo "\n";

        // If a vehicle has taken over a timetable journey then the journey key will have moved from timetable_id to timetable_id + _ + vehicle_id
        // In this case we need to remove the journey for the id without a vehicle . So build a temp table of all trips with vehicle_ids
        // and flag any 
        $sql = "SELECT timetable_id, vehicle_id, active_status  FROM snapshot_board_status WHERE vehicle_id IS NOT NULL INTO TEMP t_now_real_time WITH NO LOG";
        $this->connector->executeSQL($sql);

        //$sql = "UPDATE snapshot_board_status SET ( row_status, current_status, row_changed ) = ( row_status, current_status, row_changed ) ";
        $sql = "UPDATE snapshot_board_status SET ( row_status, current_status, row_changed ) = ( 'DELETED', 'DELETED', CURRENT ) 
                    WHERE timetable_id in ( select timetable_id from t_now_real_time ) AND vehicle_id is null";
        $this->connector->executeSQL($sql);

        /* Flag any trips as not current if current time is not between start an end time of trip */
        //$sql = "UPDATE snapshot_board_status SET ( current_status, row_changed ) = ( current_status, row_changed ) 
                    //WHERE timetable_id in ( select timetable_id from t_now_real_time )";
        //$this->connector->executeSQL($sql);


        /* Flag any trips that are flagged as current  no longer current in timetable_journey_live ( that have become stale )  */
        $sql = "update snapshot_board_status 
           set ( active_status, lateness, arc_sched, act_sched, current_status, row_changed ) =
           ( 'STALE', 0, act_sched, NULL, 'DELETED', CURRENT )
           WHERE active_status != ' ' 
             AND active_status IS NOT NULL
             AND act_sched IS NOT NULL
             AND act_sched not in ( select fact_id from timetable_journey_live )";
        $this->connector->executeSQL($sql);

        /* Flag any trips that are flagged as current  no longer current in timetable_journey_live ( that have become stale )  */
        $sql = "update snapshot_board_status 
           set ( current_status, row_changed ) =
           ( 'DELETED', CURRENT )
           WHERE current_status != 'DELETED' 
             AND active_status = 'STALE'
             ";
        $this->connector->executeSQL($sql);


        // Clear out journeys
        $sql = "DELETE FROM snapshot_board_status WHERE row_status = 'DELETED' AND row_changed < CURRENT - 10 UNITS MINUTE";
        $this->connector->executeSQL($sql);

echo "done";
        return true;

    }


}


?>
