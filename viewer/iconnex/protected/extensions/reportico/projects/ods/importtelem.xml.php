<?php

include "reading.php";
include "geohash.class.php";
include "nominatim.php";
include "odsconnector.php";


class gisconnector extends odsconnector
{
	public $fact_no = 0;

	function gps_to_decimal($type, $degmin, $heading)
	{
		if ( $type == "lat" )
		{
			$deg = substr($degmin, 0, 2 );
			$min = substr($degmin, 2 );
			$degdec = $deg + ( $min / 60 );
			if ( $heading == "S" )
				$degdec = - $degdec;
		}
		if ( $type == "long" )
		{
			$deg = substr($degmin, 0, 3 );
			$min = substr($degmin, 3 );
			$degdec = $deg + ( $min / 60 );
			if ( $heading == "W" )
				$degdec = - $degdec;
		}
		return $degdec;
	}

	function clear_telem_facts($source, $vehicle)
	{
		$sql = "DELETE FROM telem_paesa_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesb_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesc_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesd_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paese_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesf_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesg_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesi_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paest_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesp_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesr_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
		$sql = "DELETE FROM telem_paesv_fact
			WHERE vehicle_id = $vehicle
			AND sourcefile = '$source'";
		$ret = $this->executeSQL($sql);
	}

	function parseNMEA($str, $ar, $source, $operator, $vehicle, $vehicle_code, $driver, $trip)
	{
		$ret = false;

		if (!$gisid = $this->getGISByHash($ar["geohash"]))
		{
			echo "Unknown hash $gisid<BR>";
			return false;
		}

		if ($this->fact_no++ == 0)
			$this->clear_telem_facts($source, $vehicle);
		
		$dateid = $ar["fixdate"];
		$timeid = $ar["fixtime"];

		if ($str[4] == '$PAESA')
		{
			$time_since_last = trim($str[5]);
			$fuel_economy = trim($str[6]);
			$fuel_level = trim($str[7]);
			$distance_travelled = trim($str[8]);
			$odometer = trim($str[9]);
			$max_accel = trim($str[10]);
			$max_decel = trim($str[11]);
			$max_corner = trim($str[12]);
			$avg_rpm = trim($str[13]);
			$avg_speed = trim($str[14]);
			$max_speed = trim($str[15]);

			if (!array_key_exists("15", $str)
			|| trim(strlen($max_speed)) <= 0)
				$max_speed = 'null';

			$sql = "INSERT INTO telem_paesa_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					time_since_last,
					fuel_economy,
					fuel_level,
					distance_travelled,
					odometer,
					max_accel,
					max_decel,
					max_corner,
					avg_rpm,
					avg_speed,
					max_speed
				) VALUES ("
					. strToColVal($source) . ", "
					. "$gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($time_since_last) . ", "
					. strToColVal($fuel_economy) . ", "
					. strToColVal($fuel_level) . ", "
					. strToColVal($distance_travelled) . ", "
					. strToColVal($odometer) . ", "
					. strToColVal($max_accel) . ", "
					. strToColVal($max_decel) . ", "
					. strToColVal($max_corner) . ", "
					. strToColVal($avg_rpm) . ", "
					. strToColVal($avg_speed) . ", $max_speed)";
//echo "inserting paesa\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESB')
		{
			$trip_time = $str[5];
			$fuel_economy = $str[6];
			$fuel_level = $str[7];
			$distance_travelled = $str[8];
			$odometer = $str[9];
			$max_accel = $str[10];
			$max_decel = $str[11];
			$max_corner = $str[12];
			$avg_rpm = $str[13];
			$avg_speed = $str[14];
			$max_speed = $str[15];

			$sql = "INSERT INTO telem_paesb_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					trip_time,
					fuel_economy,
					fuel_level,
					distance_travelled,
					odometer,
					max_accel,
					max_decel,
					max_corner,
					avg_rpm,
					avg_speed,
					max_speed
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($trip_time) . ", "
					. strToColVal($fuel_economy) . ", "
					. strToColVal($fuel_level) . ", "
					. strToColVal($distance_travelled) . ", "
					. strToColVal($odometer) . ", "
					. strToColVal($max_accel) . ", "
					. strToColVal($max_decel) . ", "
					. strToColVal($max_corner) . ", "
					. strToColVal($avg_rpm) . ", "
					. strToColVal($avg_speed) . ", "
					. strToColVal($max_speed) . ");";
//echo "inserting paesb\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESC')
		{
			$vin = $str[5];
			$dtc_count = $str[6];
			$mil_status = $str[7];
			$service_interval = $str[8];
			$vehicle_weight = $str[9];
			$vehicle_status = $str[10];
			$fuel_method = $str[11];
			$odometer_method = $str[12];

			$sql = "INSERT INTO telem_paesc_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					vin,
					dtc_count,
					mil_status,
					service_interval,
					vehicle_weight,
					vehicle_status,
					fuel_method,
					odometer_method
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					'$vin', "
					. strToColVal($dtc_count) . ", "
					. strToColVal($mil_status) . ", "
					. strToColVal($service_interval) . ", "
					. strToColVal($vehicle_weight) . ", "
					. "'$vehicle_status',
					$fuel_method, "
					. strToColVal($odometer_method) . ");";
echo "$sql\n";
//echo "inserting paesc\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESD')
		{
			$dtc_1 = $str[5];
			$dtc_2 = $str[6];
			$dtc_3 = $str[7];
			$dtc_4 = $str[8];
			$dtc_5 = $str[9];

			$sql = "INSERT INTO telem_paesd_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					dtc_1,
					dtc_2,
					dtc_3,
					dtc_4,
					dtc_5
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					'$dtc_1',
					'$dtc_2',
					'$dtc_3',
					'$dtc_4',
					'$dtc_5'
				)";
//echo "inserting paesd\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESE')
		{
			$high_res_odo = $str[5];
			$trip_time = $str[6];
			$idle_time = $str[7];
			$harsh_accel = $str[8];
			$harsh_brake = $str[9];
			$over_speed = $str[10];
			$over_rpm = $str[11];
			$heavy_accel = $str[12];
			$coasting = $str[13];
			$cruise_ctrl = $str[14];
			$power_take_off = $str[15];

			$sql = "INSERT INTO telem_paese_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					high_res_odo,
					trip_time,
					idle_time,
					harsh_accel,
					harsh_brake,
					over_speed,
					over_rpm,
					heavy_accel,
					coasting,
					cruise_ctrl,
					power_take_off
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					$high_res_odo,
					$trip_time,
					$idle_time,
					$harsh_accel,
					$harsh_brake,
					$over_speed,
					$over_rpm,
					$heavy_accel,
					$coasting,
					$cruise_ctrl,
					$power_take_off
				)";
//echo "inserting paese\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESF')
		{
			$total_used = $str[5];
			$trip_used = $str[6];
			$trip_used_idling = $str[7];

			$sql = "INSERT INTO telem_paesf_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					total_used,
					trip_used,
					trip_used_idling
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					$total_used,
					$trip_used,
					$trip_used_idling
				)";
//echo "inserting paesf\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESG')
		{
			$ignition_source = $str[5];
			$high_res_odo = $str[6];

			$sql = "INSERT INTO telem_paesg_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					ignition_source,
					high_res_odo
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($ignition_source) . ", "
					. strToColVal($high_res_odo) . "
				)";
//echo "inserting paesg\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESI')
		{
			$avg_model = $str[5];
			$serial_no = $str[6];
			$firmware_name = $str[7];
			$firmware_version = $str[8];
			$bootloader_version = $str[9];
			$reset_type = $str[10];
			$reset_code = $str[11];
			$boot_code = $str[12];
			$vehicle_voltage = $str[13];

			$sql = "INSERT INTO telem_paesi_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					avg_model,
					serial_no,
					firmware_name,
					firmware_version,
					bootloader_version,
					reset_type,
					reset_code,
					boot_code,
					vehicle_voltage
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($avg_model) . ", "
					. strToColVal($serial_no) . ", "
					. strToColVal($firmware_name) . ", "
					. strToColVal($firmware_version) . ", "
					. strToColVal($bootloader_version) . ", "
					. strToColVal($reset_type) . ", "
					. strToColVal($reset_code) . ", "
					. strToColVal($boot_code) . ", "
					. strToColVal($vehicle_voltage) . ");";
//echo "inserting paesi\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAEST')
		{
			$event_id = $str[5];
			$duration = $str[6];
			$threshold = $str[7];

			$sql = "INSERT INTO telem_paest_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					event_id,
					duration,
					threshold
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($event_id) . ", "
					. strToColVal($duration) . ", "
					. strToColVal($threshold) . "
				);";
//echo "inserting paest\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESP')
		{
			$band_1 = $str[5];
			$band_2 = $str[6];
			$band_3 = $str[7];
			$band_4 = $str[8];
			$band_5 = $str[9];
			$band_6 = $str[10];
			$band_7 = $str[11];
			$band_8 = $str[12];
			$band_9 = $str[13];
			$band_10 = $str[14];
			$band_11 = $str[15];
			$band_12 = $str[16];
			$band_13 = $str[17];
			$band_14 = $str[18];
			$band_15 = $str[19];
			$band_16 = $str[20];
			$band_17 = $str[21];
			$band_18 = $str[22];
			$band_19 = $str[23];
			$band_20 = $str[24];

			$sql = "INSERT INTO telem_paesp_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					band_1,
					band_2,
					band_3,
					band_4,
					band_5,
					band_6,
					band_7,
					band_8,
					band_9,
					band_10,
					band_11,
					band_12,
					band_13,
					band_14,
					band_15,
					band_16,
					band_17,
					band_18,
					band_19,
					band_20
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					$band_1,
					$band_2,
					$band_3,
					$band_4,
					$band_5,
					$band_6,
					$band_7,
					$band_8,
					$band_9,
					$band_10,
					$band_11,
					$band_12,
					$band_13,
					$band_14,
					$band_15,
					$band_16,
					$band_17,
					$band_18,
					$band_19,
					$band_20
				)";
//echo "inserting paesp\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESR')
		{

			$band_1 = $str[5];
			$band_2 = $str[6];
			$band_3 = $str[7];
			$band_4 = $str[8];
			$band_5 = $str[9];
			$band_6 = $str[10];
			$band_7 = $str[11];
			$band_8 = $str[12];
			$band_9 = $str[13];
			$band_10 = $str[14];
			$band_11 = $str[15];
			$band_12 = $str[16];

			$sql = "INSERT INTO telem_paesr_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					band_1,
					band_2,
					band_3,
					band_4,
					band_5,
					band_6,
					band_7,
					band_8,
					band_9,
					band_10,
					band_11,
					band_12
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid,
					$band_1,
					$band_2,
					$band_3,
					$band_4,
					$band_5,
					$band_6,
					$band_7,
					$band_8,
					$band_9,
					$band_10,
					$band_11,
					$band_12
				)";
//echo "inserting paesr\n";
			$ret = $this->executeSQL($sql);
		}
		else if ($str[4] == '$PAESV')
		{
			$vehicle_speed_1 = $str[5];
			$fuel_rate_1 = $str[6];
			$vehicle_speed_2 = $str[7];
			$fuel_rate_2 = $str[8];
			$vehicle_speed_3 = $str[9];
			$fuel_rate_3 = $str[10];
			$no_of_samples_1 = $str[11];
			$no_of_samples_2 = $str[12];
			$no_of_samples_3 = $str[13];

			$sql = "INSERT INTO telem_paesv_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					vehicle_speed_1,
					fuel_rate_1,
					vehicle_speed_2,
					fuel_rate_2,
					vehicle_speed_3,
					fuel_rate_3,
					no_of_samples_1,
					no_of_samples_2,
					no_of_samples_3
				) VALUES (
					'$source', $gisid, $vehicle, "
					. strToColVal($driver) . ", "
					. strToColVal($trip) . ", $dateid, $timeid, "
					. strToColVal($vehicle_speed_1) . ", "
					. strToColVal($fuel_rate_1) . ", "
					. strToColVal($vehicle_speed_2) . ", "
					. strToColVal($fuel_rate_2) . ", "
					. strToColVal($vehicle_speed_3) . ", "
					. strToColVal($fuel_rate_3) . ", 
					$no_of_samples_1,
					$no_of_samples_2,
					$no_of_samples_3
				)";
//echo "inserting paesv\n";
			$ret = $this->executeSQL($sql);
		}
		else
			echo "Unknown type $str[4]\n";

		return $ret;
	}
	
	function applyGIS ($ar)
	{
		$sql = "SELECT geohash FROM gis_dimension WHERE geohash = '".$ar["geohash"]."'";
		$ret = $this->fetch1SQL($sql);
		if (!$ret)
		{
			if (!isset($ar["geodata"]["address"]["postcode"])) $ar["geodata"]["address"]["postcode"] = "";
			if (!isset($ar["geodata"]["address"]["road"])) $ar["geodata"]["address"]["road"] = "";
			if (!isset($ar["geodata"]["address"]["suburb"])) $ar["geodata"]["address"]["suburb"] = "";
			if (!isset($ar["geodata"]["address"]["city"])) $ar["geodata"]["address"]["city"] = "";
			if (!isset($ar["geodata"]["address"]["country"])) $ar["geodata"]["address"]["country"] = "";
			if (!isset($ar["geodata"]["address"]["county"])) $ar["geodata"]["address"]["county"] = "";
			$road = $ar["geodata"]["address"]["road"] ? $ar["geodata"]["address"]["road"] : "";
			$suburb = $ar["geodata"]["address"]["suburb"] ? $ar["geodata"]["address"]["suburb"] : "";
			$city = $ar["geodata"]["address"]["city"] ? $ar["geodata"]["address"]["city"] : "";
			$country = $ar["geodata"]["address"]["country"] ? $ar["geodata"]["address"]["country"] : "";
			$county = $ar["geodata"]["address"]["county"] ? $ar["geodata"]["address"]["county"] : "";
			$postcode = $ar["geodata"]["address"]["postcode"] ? $ar["geodata"]["address"]["postcode"] : "";
			$sql = "INSERT INTO gis_dimension
				( gis_id, geohash, osm_place_id, latitude, longitude, addr_road,
					addr_suburb, addr_city, addr_country, addr_county, addr_postcode )
				VALUES
				( 0,
				'".$ar["geohash"]."',
				'".$ar["geodata"]["place_id"]."',
				'".$ar["lat"]."',
				'".$ar["long"]."',
				'". addslashes($road) ."',
				'". addslashes($suburb) ."',
				'". addslashes($city) ."',
				'". addslashes($country) ."',
				'". addslashes($county) ."',
				'". addslashes($postcode) ."'
				)";
			$ret = $this->executeSQL($sql);
		}

		return $ret;
	}
	
	function applyTrip($timestamp, $operator, $vehicle_id, $vehicle_code)
	{
		global $rtpi;
		$ret = false;
		$trip_id = null;
		$driver_id = null;

		if (!($trip_id = $this->getTrip($timestamp, $operator, $vehicle_code)))
		{
			$trip = NULL;
			if (!($schedule_id = $this->getScheduleId($timestamp)))
			{
				echo "Couldn't find schedule_id for vehicle $vehicle_code at $timestamp\n";
				return false;
			}
			if (!($trip = $rtpi->getTripBySchedule($schedule_id)))
			{
				echo "Failed to find trip details for schedule $schedule_id\n";
				return false;
			}

			$sql = "INSERT INTO trip_dimension
				( 
					trip_id,
					vehicle_id,
					driver_id,
					system_code,
					route_code,
					trip_no,
					duty_no,
					running_no,
					actual_start,
					start_day,
					actual_end
				)
				VALUES
				(0,
				$vehicle_id,
				0,
				'iconnex',
				'".trim($trip["ROUTE_CODE"])."',
				'".trim($trip["TRIP_NO"])."',
				'".trim($trip["DUTY_NO"])."',
				'".trim($trip["RUNNING_NO"])."',
				'".$trip["ACTUAL_START"]."',
				".$trip["START_DAY"].",
				'".$trip["ACTUAL_END"]."')";

			$ret = $this->executeSQL($sql);
			$trip_id = $this->pdo->lastInsertId();
			$driver_id = null;
			$driver_code = trim($trip["EMPLOYEE_CODE"]);
			if (!($driver_id = $this->getDriver($driver_code, $operator)))
			{
				$sql = "INSERT INTO driver_dimension
					(driver_id, system_code, operator_code, employee_code, fullname)
					VALUES
					(0, 'iconnex', '$operator',
					'" . $driver_code . "',
					'" . trim($trip["FULLNAME"]) . "'
					)";
				$ret = $this->executeSQL($sql);
				$driver_id = $this->pdo->lastInsertId();
			}

			$sql = "update trip_dimension
				set driver_id = $driver_id
				where trip_id = $trip_id";
			$ret = $this->executeSQL($sql);
		}
		else
		{
//			echo "Found trip $trip_id in ods db, looking up driver\n";
			$driver_id = $this->getDriverByTripId($trip_id);
		}
	
		return array("trip_id" => $trip_id, "driver_id" => $driver_id);
	}
	
	function utctolocal($intime, $informat)
	{
		$start = DateTime::createFromFormat($informat, $intime, new DateTimeZone("GMT"));
		$start->setTimezone (new DateTimeZone("Europe/London"));
		return $start->format('ymdHis');
	}

	function telem_import($input, $operator, $build_code)
	{
		echo "Import $operator $build_code ".basename(dirname($input))."/".basename($input)."<br>\n";
		$sourcefile = basename($input);
		$geohash = new GeoHash();
		$ptr = fopen ($input, "r");
		$linect = 0;
		$nomct = 0;
		$ct = 0;
		global $rtpi;

		while ($val = fgets($ptr, 512))
		{
			if (strlen($val) <= 1)
				continue;

			$arr_checksum = preg_split('/\*/', $val);
			$arr = preg_split('/,/', $arr_checksum[0]);
			$type = $arr[4];
			if (preg_match('/\$PAES[ABCDEFGITPRV]/', $type) < 1)
				continue;

			$mode = $arr[3];
			if ($mode != "A")
				continue; 

			$vehicle_id = NULL;
			if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $build_code)))
			{
				echo "Unknown build_code $build_code";
				return false;
			}

			$vehicle_code = $this->getVehicleCode($vehicle_id);
			if ($ct++ == 0)
				$rtpi->initTripSearch($vehicle_code, $arr[0], $this);

			$fixdate = date('Ymd', $arr[0]);
			$fixtime = date('His', $arr[0]);
			$declat = $arr[1];
			$declong = 0 - $arr[2];
			$hash = $geohash->encode($declat, $declong);
			$hash = substr($hash, 0, 8);

			$gisarr = array(
				"lat" => $declat,
				"long" => $declong,
				"geohash" => $hash,
				"geodata" => false,
				"speed_knots" => null,
				"true_course" => null,
				"fixtime" => $fixtime,
				"fixdate" => $fixdate
				);

			if (!($gisid = $this->getGISByHash($hash)))
			{
				$nomct++;
				$nominatim = new nominatim ($declat, $declong);
				$geodata = $nominatim->reverse();
				$gisarr["geodata"] = $geodata;
				$txt = $geodata["place_id"]."_".implode(",", $geodata["address"]);
				$ret = $this->applyGIS($gisarr);
				$this->show_debug($hash." = ".$txt."<br>");
			}

echo "applying Trip\n";
			$ret = $this->applyTrip($arr[0], $operator, $vehicle_id, $vehicle_code);
			if ($ret)
			{
				$trip_id = $ret["trip_id"];
				$driver_id = $ret["driver_id"];
			}
			else
			{
				$trip_id = null;
				$driver_id = null;
			}
			
			$ret = $this->parseNMEA($arr, $gisarr, $sourcefile, $operator, $vehicle_id, $vehicle_code, $driver_id, $trip_id);
			if (!$ret)
			{
				echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
				echo "applyFACT failed for " . $arr[4] . "\n";
				echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
				break;
			}

			$linect++;
		}

		echo "$nomct / $linect nominatim calls<BR>\n";
	}
}

function identify_import_files($lookin, $container)
{
	global $imports;
	$ret = false;

	$location = $container.$lookin;

	if (!is_dir($location) && !is_file($location))
	{
		trigger_error("$location is not a valid location", E_USER_ERROR);
		return $ret;
	}
	if (is_dir($location))
	{
		if ($dh = opendir($location))
		{
			while (($file = readdir($dh)) !== false)
			{
				if ( $file == "." || $file == ".." )
					continue;

				if ( is_dir ($location."/".$file ) )
					identify_import_files($location."/".$file, "");
				else
				{
					if ( is_file ( $location."/".$file ) && preg_match ( "/telem.\d{8}$/", $file ) )
						$imports[] = $location."/".$file;
				}
			}
			closedir($dh);
		}
	}
	else
	{
		if ( $location == "." || $location == ".." )
			break;

		if ( is_file ( $location ) && preg_match ( "/telem.\d{8}$/", $location ) )
			$imports[] = $location;
	}

	$ret = true;
	return $ret;
}

function strToColVal($s)
{
	if (strlen($s) <= 0
	|| $s == null)
		return "null";
	
	return "'$s'";
}

global $imports;
global $rtpi;
$rtpi = new iconnex;

$gis = new gisconnector($_pdo);
$gis->debug = false;
$file = $_criteria["telemfile"]->get_criteria_value("VALUE", false);
if (identify_import_files($file, "/opt/centurion/live/data/import/"))
{
	$ct = 0;
	foreach ($imports as $file)
	{
		$ct++;
		$vehicle = basename(dirname($file));
		$operator = basename(dirname(dirname($file)));
		$gis->fact_no = 0;
		$gis->telem_import($file, $operator, $vehicle);
		system("gzip $file");
		$dir = dirname($file);
		//system("mkdir -p $dir/imported");
		//system("mv $file.gz $dir/imported/");
		flush();
	}
}
ob_flush();
?>

