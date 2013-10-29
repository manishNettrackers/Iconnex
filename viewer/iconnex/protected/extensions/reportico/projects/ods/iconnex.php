<?php 

class iconnex
{
	public $pdo = false;
	public $stmt;

	function connect()
	{
try {
		if ($this->pdo = new PDO("informix:host=10.9.1.254; service=5130; database=centurion; server=centlive_tcp; protocol=onsoctcp;", "dbmaster", "read109!!"))
		{
			return true;
		}
		else
		{
			return false;
		}
}
catch ( PDOException $ex )
{
    var_dump($ex);
}
	}
	function executeSQL($in_sql)
	{
		if (!$this->pdo)
			$this->connect();

		$this->stmt = $this->pdo->query($in_sql);

		if (!$this->stmt)
		{
			$this->showPDOError();
			return ($this->stmt);
		}

		return $this->stmt;
	}

	function fetch()
	{
			$result = $this->stmt->fetch();
			return $result;
	}

	function close()
	{
			$this->stmt = null;
	}

	function showPDOError( )
	{
			$info = $this->pdo->errorInfo();
			$msg =  "Error ".$info[1]."<BR>".
					$info[2];
			trigger_error("$msg");
	}

	function rpt_setDirtyRead()
	{
		$sql = "SET ISOLATION TO DIRTY READ";
		return $this->pdo->Execute($sql);
	}

	function initTripSearch($vehicle_code, $timestamp, $ods)
	{

		$date = date('d/m/Y', $timestamp);

		$sql = "create temp table t_arc (
			ser serial,
			schedule_id integer,
			pub_ttb_id integer,
			actual_start datetime year to second);";
//echo "$sql\n";
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
    			. $row["SER"] . ",
				" . $row["SCHEDULE_ID"] . ",
				" . $row["PUB_TTB_ID"] . ",
				'" . $row["ACTUAL_START"] . "',
				'" . $row["ACTUAL_END"] . "',
				'" . $row["LAYOVER_END"] . "');";
			$ret = $ods->executeSQL($sql2);
		}
	}

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
			WHERE archive_rt.schedule_id = " . $res["SCHEDULE_ID"] . "
			AND t_arc_final.schedule_id = archive_rt.schedule_id
			AND route.route_id = archive_rt.route_id
			AND employee.employee_id = archive_rt.employee_id
			AND employee.operator_id = route.operator_id;";

		$stmt = $this->executeSQL($sql);
		$res = $stmt->fetch();
		return $res;
	}
}
?>

