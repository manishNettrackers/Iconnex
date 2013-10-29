<?php

/**
** Class: TaskStopStatusSnapshot
** ------------------------------
**
** Populates the stop_status_snapshot table with entries for each current
** stop so that it can be quickly selected from for web views etc
*/
class TaskStopStatusSnapshot extends ScheduledTask
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
        $stopStatus = new SnapshotStopStatus($this->connector);
        $this->connector->debug = 1;
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

        $this->connector->executeSQL("DROP TABLE t_routeloc", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_events", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locs", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_loconrt", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_routeloc", "CONTINUE");

        $sql = 
        "SELECT UNIQUE location_id, 0 route_id, route_code, operator.operator_id, operator_code
        FROM service_patt, service, route, operator
        WHERE service_patt.service_id = service.service_id
        AND service.route_id  = route.route_id
        AND route.operator_id  = operator.operator_id
        AND TODAY BETWEEN wef_date AND wet_date
        INTO TEMP t_routeloc WITH NO LOG";


        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "CREATE INDEX i_t_routeloc ON t_routeloc ( location_id );";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $this->get_stop_params("make");
        $this->get_stop_params("maxTextWidth");

        // -------------------------------------------------------
        // Extract List of shocks, bootups etc
        // -------------------------------------------------------
        $sql = "SELECT unit_build.build_id, unit_alert.message_type message_type, max(alert_time) last_alert,
        count(*) alert_count 
        FROM unit_build, display_point, unit_alert, outer message_type 
        WHERE 1 = 1 
        AND display_point.build_id = unit_build.build_id 
        AND unit_alert.build_id = unit_build.build_id 
        AND message_type.msg_type = unit_alert.message_type 
        AND date(alert_time) BETWEEN TODAY - 7 AND TODAY
        AND unit_alert.message_type IN ('476', '481', '494', '493' ) 
        GROUP BY 1,2
        INTO TEMP t_events WITH NO LOG";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        // -------------------------------------------------------
        // Extract report locations
        // ------------------------------------------------------
        $sql = "SELECT l.location_id, location_code location_code, l.bay_no bay_no, l.description description, 
                naptan_stop_point.common_name,
                naptan_stop_point.indicator,
                ra.route_area_code route_area_code, latitude_degrees latitude_degrees,
                latitude_minutes latitude_minutes, latitude_heading latitude_heading, longitude_degrees longitude_degrees, longitude_minutes longitude_minutes, 
                longitude_heading longitude_heading, u.build_code build_code, us.message_time message_time, us.ip_address ip_address , t_stops_make.param_value make,
            t_events1.last_alert last_impact, t_events1.alert_count impact_count, 
            t_events2.last_alert last_bootup, t_events2.alert_count bootup_count ,
            (INTERVAL(0) HOUR(4) TO HOUR + ( CURRENT - us.message_time )) || ''  last_active_hour,
            (INTERVAL(0) DAY(4) TO DAY + ( CURRENT - us.message_time )) || ''  last_active_day,
            naptan_stop_point.bearing bearing
        FROM location l,
            route_area ra,
            outer (display_point dp, unit_build u, t_stops_make, outer unit_status us, outer t_events  t_events1, outer t_events t_events2) ,
        OUTER naptan_stop_point 
        WHERE 1 = 1  AND l.route_area_id = ra.route_area_id
        and l.location_id in ( select location_id from t_routeloc)
        and l.point_type = 'S'
        and l.location_id = dp.location_id
        and dp.display_type = 'B'
        and dp.build_id = u.build_id
        and t_stops_make.build_id = u.build_id
        and dp.build_id = us.build_id
        and dp.build_id = t_events1.build_id
        and t_events1.message_type = 476
        and atco_code = location_code
        and dp.build_id = t_events2.build_id
            AND us.message_time > CURRENT - 200 UNITS DAY
        and t_events2.message_type = 113";

        $sql .= " INTO TEMP t_locs WITH NO LOG";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "UPDATE t_locs set description = common_name || ' ' || indicator
            where common_name is not null ";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        // -------------------------------------------------------
        // Filter equipped/non-equipped stops
        // --------------------------------------------------------
        $showeq = 1;
        $shownoneq = 1;

        if ( !$showeq )
        {
                $sql = "DELETE FROM t_locs WHERE build_code IS NOT NULL";
                if ( !$this->connector->executeSQL($sql) )
                    return false;
        }

        if ( !$shownoneq )
        {
            $sql = "DELETE FROM t_locs WHERE build_code IS NULL";
            if ( !$this->connector->executeSQL($sql) )
                return false;
        }

        // -------------------------------------------------------
        // Fetch the routes each location resides on
        // --------------------------------------------------------
        $sql = "CREATE TEMP TABLE t_loconrt ( location_id INTEGER, routes CHAR(40) ) WITH NO LOG;";
        if ( !$this->connector->executeSQL($sql) )
            return false;
        
        $sql =
        "
        SELECT UNIQUE location_id, route_code
        FROM service_patt, service, route
        WHERE service.route_id = route.route_id
	AND service_patt.service_id = service.service_id
  	AND location_id IN ( SELECT location_id FROM t_locs )
	AND today between wef_date and wet_date
        ORDER BY location_id";
        
        if ( !($recordSet = $this->connector->executeSQL($sql)) )
            return false;

        $lastid="";
        $rtes="";
        while ($line = $recordSet->fetch())
        {
            $locid = $line["location_id"];
            $rte = trim($line["route_code"]);

            if ( $lastid && $lastid != $locid )
            {
                $sql = "INSERT INTO t_loconrt VALUES ( $lastid, '$rtes');";
                if ( !$this->connector->executeSQL($sql) )
                    return false;
            }
            
            if ( !$lastid || $lastid != $locid )
                $rtes = "";

            if ( !$rtes )
                $rtes .= $rte;
            else
                $rtes .= "/".$rte;

            $lastid = $locid;
        }

        if ( $lastid )
        {
                $sql = "INSERT INTO t_loconrt VALUES ( $lastid, '$rtes');";
                if ( !$this->connector->executeSQL($sql) )
                    return false;
        }


        $sql = "CREATE INDEX i_t_loconrt ON t_loconrt ( location_id );";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        // .. followed by stop
        $stopStatus = new SnapshotStopStatus($this->connector);

        $sql = " SELECT t_routeloc.location_id, location_code location_code, bay_no bay_no, description, route_area_code route_area_code,
                ( latitude_degrees + ( latitude_minutes / 60 ) ) latitude,
                - ( longitude_degrees + ( longitude_minutes / 60 ) ) longitude,
                build_code build_code, message_time message_time, ip_address ip_address,
                t_routeloc.route_id, route_code route_code, make make, last_impact last_impact, impact_count impact_count,
                last_bootup last_bootup, bootup_count bootup_count, last_active_hour last_active_hour,
                last_active_day last_active_day, operator_code operator_code, routes routes, t_locs.bearing,
                '' row_status, CURRENT row_changed
                FROM t_routeloc,  t_locs left join t_loconrt on t_locs.location_id = t_loconrt.location_id 
                WHERE 1 = 1                         
                AND t_locs.location_id = t_routeloc.location_id  
                ORDER BY  location_code ";

        $stops = $stopStatus->sqlToInstanceArray($sql);
        foreach ( $stops as $stop )
        {
            // Round latitude longitude
            $stop->longitude = round($stop->longitude, 6);
            $stop->latitude = round($stop->latitude, 6);
            
            $statusstop = new SnapshotStopStatus($this->connector);
            $statusstop->location_id = $stop->location_id;
            $statusstop->route_id = $stop->route_id;
            if ( !$statusstop->load() )
            {
                $stop->row_changed = $now->format("Y-m-d H:i:s");
                $stop->row_status = "OK";
                $stop->add();
                $this->rows_affected ++;
            }
            else
            {
                if ( $statusstop->differs($stop, false, array("row_changed", "row_status") ) )
                {
                    $stop->row_changed = $now->format("Y-m-d H:i:s");
                    $stop->row_status = "OK";
                    $stop->save();
                    $this->rows_affected ++;
                }
            }
        }

        return true;

    }

    function get_stop_params( $tp )
    {
        $this->connector->executeSQL("DROP TABLE t_stops_$tp", "CONTINUE");

        $sql = "
        select a.build_id,
        a.build_code,
        a.build_code parent,
        param_desc,
        param_value,
        a.unit_type
        from unit_build a, unit_param b, component c, parameter d
        where a.build_id = b.build_id
        and b.component_id = c.component_id
        and b.param_id = d.param_id
        and component_code = 'STOPDISPLAYDEVICE'
        and ( param_desc = '$tp' )
        and unit_type = 'BUSSTOP'
        and param_value is not null
        and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
        and param_value != ''
        and a.build_id in  ( select build_id from display_point )
        INTO TEMP t_stops_$tp";

        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "
            insert into t_stops_$tp
            select unique
            a.build_id,
            a.build_code,
            pa.build_code parent,
            param_desc,
            param_value,
            a.unit_type
            from unit_build a, unit_param b, component c, parameter d,
            unit_build pa
            where 1 = 1
            and a.build_parent = pa.build_id
            and pa.build_id = b.build_id
            and b.component_id = c.component_id
            and b.param_id = d.param_id
            and component_code = 'STOPDISPLAYDEVICE'
            and ( param_desc = '$tp' )
            and param_value is not null
            and param_value != ''
            and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
            and a.unit_type = 'BUSSTOP'
            and a.build_id in  ( select build_id from display_point )
            and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp )
            ";
        if ( !$this->connector->executeSQL($sql) )
            return false;

        $sql = "
            insert into t_stops_$tp
            select unique
            a.build_id,
            a.build_code,
            pa.build_code parent,
            param_desc,
            param_value,
            a.unit_type
            from unit_build a, unit_param b, component c, parameter d,
            unit_build pa, unit_build ppa
            where 1 = 1
            and a.build_parent = pa.build_id
            and pa.build_parent = ppa.build_id
            and ppa.build_id = b.build_id
            and b.component_id = c.component_id
            and b.param_id = d.param_id
            and component_code = 'STOPDISPLAYDEVICE'
            and ( param_desc = '$tp' )
            and param_value is not null
            and param_value != ''
            and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' )
            and a.unit_type = 'BUSSTOP'
            and a.build_id in  ( select build_id from display_point )
            and a.build_id NOT IN  ( SELECT build_id FROM t_stops_$tp );
            ";
        if ( !$this->connector->executeSQL($sql) )
            return false;
    }

}


?>
