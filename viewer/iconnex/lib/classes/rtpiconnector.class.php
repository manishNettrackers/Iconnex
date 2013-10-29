<?php

/*
** Class rtpiconnector
**
** Provides functions to fetching details of vehicles, trips, timetables etc
** from the Real Time Database
*/
include_once("iconnex.class.php");

class rtpiconnector extends iconnex
{

    // Searches Trip History to find the trip for a vehicle
    // for a given time
	function initTripSearch($vehicle_code, $timestamp, $ods)
	{

		$date = date('d/m/Y', $timestamp);

		$sql = "create temp table t_arc (
			ser serial,
			schedule_id integer,
			pub_ttb_id integer,
			actual_start datetime year to second);";
echo "$sql\n";
		$stmt = $this->executeSQL($sql);
		$sql = "insert into t_arc
			select 0, schedule_id, pub_ttb_id, actual_start
			from archive_rt, vehicle
			where vehicle.vehicle_id = archive_rt.vehicle_id
			and vehicle.vehicle_code = '".$vehicle_code."'
			and date(actual_start) = '" . $date . "';";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);

		$sql = "select t_arc.schedule_id, max(rpat_orderby) rpat_orderby
			from t_arc, archive_rt_loc
			where t_arc.schedule_id = archive_rt_loc.schedule_id
			group by 1
			into temp t_arc2
			with no log;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);

		$sql = "select t_arc.schedule_id, min(t_arc_next.schedule_id) next_schedule_id
				from t_arc, t_arc t_arc_next
				where t_arc_next.actual_start > t_arc.actual_start
				and t_arc.schedule_id < t_arc_next.schedule_id
				group by t_arc.schedule_id
				into temp t_arc3
				with no log;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);

		$sql = "select t_arc.schedule_id,
					archive_rt_loc.departure_time layover_end,
					archive_rt_loc.departure_time_pub layover_end_pub
				from t_arc, archive_rt_loc, t_arc3
				where t_arc.schedule_id = t_arc3.schedule_id
				and t_arc3.next_schedule_id = archive_rt_loc.schedule_id
				and archive_rt_loc.rpat_orderby = 1
				into temp t_arc4
				with no log;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);
		
		$sql = "update t_arc4
			set layover_end = layover_end_pub
			where layover_end IS NULL;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);
		
		$sql = "select t_arc.*,
					archive_rt_loc.arrival_time actual_end,
					archive_rt_loc.arrival_time_pub publish_end,
					layover_end
                from t_arc, t_arc2, archive_rt_loc, outer t_arc4
                where t_arc.schedule_id = t_arc2.schedule_id
                and t_arc2.schedule_id = archive_rt_loc.schedule_id
                and t_arc2.rpat_orderby = archive_rt_loc.rpat_orderby
                and t_arc4.schedule_id = t_arc.schedule_id
                into temp t_arc_final
                with no log;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);

		$sql = "update t_arc_final
			set actual_end = publish_end
			where actual_end IS NULL;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);
		
		$sql = "update t_arc_final
			set layover_end = publish_end
			where layover_end IS NULL;";
//echo "$sql\n";
		$stmt = $this->executeSQL($sql);
		
		$sql = "create temporary table t_arc (
			ser integer,
			schedule_id integer,
			pub_ttb_id integer,
			actual_start datetime,
			actual_end datetime,
			layover_end datetime);";
		$ret = $ods->executeSQL($sql);

		$sql = "select * from t_arc_final";
		foreach ($this->pdo->query($sql) as $row)
		{
			$sql2 = "insert into t_arc values ("
    			. $row["ser"] . ",
				" . $row["schedule_id"] . ",
				" . $row["pub_ttb_id"] . ",
				'" . $row["actual_start"] . "',
				'" . $row["actual_end"] . "',
				'" . $row["layover_end"] . "');";
			$ret = $ods->executeSQL($sql2);
		}
	}

    // Return current trip details for a given schedule id
	function getCurrentTripBySchedule($schedule_id)
	{
		$sql = "SELECT route.route_code,
				active_rt.trip_no,
				active_rt.duty_no,
				active_rt.running_no,
				active_rt.scheduled_start,
				active_rt.actual_start,
				active_rt.start_day,
				active_rt.actual_start,
				employee.employee_code,
				employee.fullname
			FROM active_rt, route, employee
			WHERE active_rt.schedule_id = $schedule_id
			AND route.route_id = active_rt.route_id
			AND employee.employee_id = active_rt.employee_id
			AND employee.operator_id = route.operator_id;";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		return $res;
	}

    // Retuen trip details for a given schedule id
	function getTripBySchedule($schedule_id)
	{
		$sql = "SELECT route.route_code,
				archive_rt.trip_no,
				archive_rt.duty_no,
				archive_rt.running_no,
				archive_rt.scheduled_start,
				archive_rt.actual_start,
				archive_rt.start_day,
				t_arc_final.actual_end,
				employee.employee_code,
				employee.fullname
			FROM archive_rt, route, t_arc_final, employee
			WHERE archive_rt.schedule_id = $schedule_id
			AND t_arc_final.schedule_id = archive_rt.schedule_id
			AND route.route_id = archive_rt.route_id
			AND employee.employee_id = archive_rt.employee_id
			AND employee.operator_id = route.operator_id;";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		return $res;
	}

    // Finds who was driving a vehicle as at a fiven time
	function getDriverByTime($timestamp)
	{
		$datetime = date('Y-m-d H:i:s', $timestamp);

		$sql = "select schedule_id from t_arc_final
			where '" . $datetime . "' between actual_start and actual_end;";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		if ($res === false)
			return $res;

		$sql = "SELECT employee.employee_code,
				employee.fullname
			FROM archive_rt, route, t_arc_final, employee
			WHERE archive_rt.schedule_id = " . $res["schedule_id"] . "
			AND t_arc_final.schedule_id = archive_rt.schedule_id
			AND route.route_id = archive_rt.route_id
			AND employee.employee_id = archive_rt.employee_id
			AND employee.operator_id = route.operator_id;";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		return $res;
	}

    // Looks for operator that owns the unit_build if not found then
    // (DEPRECATED Extracts the 4-6th digit from a build code to find an operator code)
	function getOperatorFromBuildCode($buildcode, &$operator_id, &$operator_code, &$build_type)
	{
        $retval = false;

        $sql = "select operator.operator_id, operator_code, unit_type build_type from operator
                join unit_build on unit_build.operator_id = operator.operator_id
                and build_code = '$buildcode'";
        
		$row = $this->fetch1sql($sql);
        if ( $row )
        {
            $operator_id = trim($row["operator_id"]);
            $operator_code = trim($row["operator_code"]);
            $retval = true;
        }
        $operator_id = trim($res["operator_id"]);
        $operator_code = trim($res["operator_code"]);
        $build_type = trim($res["build_type"]);

        return $retval;

        $operator_id = false;
        $operator_code = false;

        $opid = substr($buildcode, 3, 3);

        // VERY BAD!!!! operator WEAWAY has loc_prefix (op 3 length id ) od 16  in db
        // but comes in as 122 here so change it
        if ( $opid == "122" )
            $opid = 16;

		$sql = "select operator_id, operator_code from operator
			where loc_prefix = $opid;";

        echo $sql."\n";
		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		if ($res === false)
			return false;

        $operator_id = trim($res["operator_id"]);
        $operator_code = trim($res["operator_code"]);
        $build_type = trim($res["build_type"]);
    
        return true;
	}

    // Return current trip details for a given vehicle
	function getCurrentTripScheduleByVehicle($vehicle_id)
	{
		$sql = "SELECT schedule_id
			FROM active_rt
			WHERE 1 = 1
            AND start_code = 'REAL'
            AND vehicle_id = $vehicle_id
			";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
        if ( $res )
            return $res["schedule_id"];
        else
            return false;
		return $res;
	}

}
?>
