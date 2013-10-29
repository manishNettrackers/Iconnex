<?php

class iconnexpacket_telematics extends iconnexpacket
{
    public $timetableVisits = false;

    public $debug = false;
    public $ignoreRoute = false;
    public $ignoreTrip = false;
    public $currentLocation = -1;
    public $driver_id = false;
    public $sourcefile = false;
    public $currentTripId = false;
    public $currentTripStart = false;
    public $currentTripEnd = false;
    public $currentDriverId = false;
    public $currentDriverStart = false;
    public $currentDriverEnd = false;

    public $paes_stats = array();

    function __construct($odsconnector, $rtpiconnector, $inData, $inLength)
    {
        parent::__construct($odsconnector, $rtpiconnector, $inData, $inLength);
    }

    function show()
    {
        var_dump($this->content);
    }

    function post_process()
    {
        $this->show_stats();
    }

    function process()
    {

        //echo $this->content;

		$arr_checksum = preg_split('/\*/', $this->content);
        $arr = preg_split('/,/', $arr_checksum[0]);

        if ( count($arr) < 2 )
            return;

        if ( !isset ( $arr[4] ))
        {
            echo "error!!!\n";
            return;
        }

        $paes_offset = false;
        $etm_val = false;

        if (preg_match('/ETM:/', $arr[4]))
            $etm_val = $arr[4];

        if (preg_match('/\$PAES[ABCDEFGITPRV]/', $arr[4]))
            $paes_offset = 4;
        if (preg_match('/\$PAES[ABCDEFGITPRV]/', $arr[5]))
            $paes_offset = 5;

        if ( !$paes_offset )
            return;

        $timestamp = $arr[0];
        $mode = $arr[3];
        if ($mode != "A")
            return;

        $dttimestamp = new DateTime();
        $dttimestamp->setTimestamp($timestamp);
        $trip_id = $this->fetchJourneyFactForVehicleAndTime($dttimestamp);
        $driver_id = $this->fetchDriverForVehicleAndTime($dttimestamp);

        $fixdate = $dttimestamp->format("Ymd");
        $fixtime = $dttimestamp->format("His");
        $declat = $arr[1];
        $declong = 0 - $arr[2];
        $hash = false;

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

        $gis_id = $this->odsconnector->processGeoItem($declat, $declong, $hash, $gisarr);


        $ret = $this->applyTelematicsFacts($arr, $paes_offset, $gisarr, $gis_id, $driver_id, $trip_id);
        if (!$ret)
        {
            echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
            echo "applyFACT failed for " . $arr[4] . "\n";
            echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
            return false;
        }
    }

    function applyTelematicsFacts($str, $paes_offset, $ar, $gis_id, $driver_id, $trip_id)
	{
		$ret = false;

		$dateid = $ar["fixdate"];
		$timeid = $ar["fixtime"];
        $source = $this->sourcefile;
        $vehicle_id = $this->vehicle_id;


        $paes_type = $str[$paes_offset];


		if ($paes_type == '$PAESA')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$time_since_last = trim($str[$paes_offset + 1]);
			$fuel_economy = trim($str[$paes_offset + 2]);
			$fuel_level = trim($str[$paes_offset + 3]);
			$distance_travelled = trim($str[$paes_offset + 4]);
			$odometer = trim($str[$paes_offset + 5]);
			$max_accel = trim($str[$paes_offset + 6]);
			$max_decel = trim($str[$paes_offset + 7]);
			$max_corner = trim($str[$paes_offset + 8]);
			$avg_rpm = trim($str[$paes_offset + 9]);
			$avg_speed = trim($str[$paes_offset + 10]);
			$max_speed = trim($str[$paes_offset + 11]);

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
					. $this->odsconnector->stringToDbValue($source) . ", "
					. "$gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($time_since_last) . ", "
					. $this->odsconnector->stringToDbValue($fuel_economy) . ", "
					. $this->odsconnector->stringToDbValue($fuel_level) . ", "
					. $this->odsconnector->stringToDbValue($distance_travelled) . ", "
					. $this->odsconnector->stringToDbValue($odometer) . ", "
					. $this->odsconnector->stringToDbValue($max_accel) . ", "
					. $this->odsconnector->stringToDbValue($max_decel) . ", "
					. $this->odsconnector->stringToDbValue($max_corner) . ", "
					. $this->odsconnector->stringToDbValue($avg_rpm) . ", "
					. $this->odsconnector->stringToDbValue($avg_speed) . ", $max_speed)";
//echo "inserting paesa\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESB')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$trip_time = $str[$paes_offset + 1];
			$fuel_economy = $str[$paes_offset + 2];
			$fuel_level = $str[$paes_offset + 3];
			$distance_travelled = $str[$paes_offset + 4];
			$odometer = $str[$paes_offset + 5];
			$max_accel = $str[$paes_offset + 6];
			$max_decel = $str[$paes_offset + 7];
			$max_corner = $str[$paes_offset + 8];
			$avg_rpm = $str[$paes_offset + 9];
			$avg_speed = $str[$paes_offset + 10];
			$max_speed = $str[$paes_offset + 11];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($trip_time) . ", "
					. $this->odsconnector->stringToDbValue($fuel_economy) . ", "
					. $this->odsconnector->stringToDbValue($fuel_level) . ", "
					. $this->odsconnector->stringToDbValue($distance_travelled) . ", "
					. $this->odsconnector->stringToDbValue($odometer) . ", "
					. $this->odsconnector->stringToDbValue($max_accel) . ", "
					. $this->odsconnector->stringToDbValue($max_decel) . ", "
					. $this->odsconnector->stringToDbValue($max_corner) . ", "
					. $this->odsconnector->stringToDbValue($avg_rpm) . ", "
					. $this->odsconnector->stringToDbValue($avg_speed) . ", "
					. $this->odsconnector->stringToDbValue($max_speed) . ");";
//echo "inserting paesb\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESC')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$vin = trim($str[$paes_offset + 1]);
			$dtc_count = $str[$paes_offset + 2];
			$mil_status = $str[$paes_offset + 3];
			$service_interval = $str[$paes_offset + 4];
			$vehicle_weight = $str[$paes_offset + 5];
			$vehicle_status = $str[$paes_offset + 6];
			$fuel_method = $str[$paes_offset + 7];
			$odometer_method = $str[$paes_offset + 8];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
					'$vin', "
					. $this->odsconnector->stringToDbValue($dtc_count) . ", "
					. $this->odsconnector->stringToDbValue($mil_status) . ", "
					. $this->odsconnector->stringToDbValue($service_interval) . ", "
					. $this->odsconnector->stringToDbValue($vehicle_weight) . ", "
					. "'$vehicle_status',
					$fuel_method, "
					. $this->odsconnector->stringToDbValue($odometer_method) . ");";
//echo "inserting paesc\n";

			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESD')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$dtc_1 = $str[$paes_offset + 1];
			$dtc_2 = $str[$paes_offset + 2];
			$dtc_3 = $str[$paes_offset + 3];
			$dtc_4 = $str[$paes_offset + 4];
			$dtc_5 = $str[$paes_offset + 5];

			$sql = "INSERT INTO telem_paesd_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					dtc_1,
					dtc_2,
					dtc_3,
					dtc_4,
					dtc_5
				) VALUES (
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
					'$dtc_1',
					'$dtc_2',
					'$dtc_3',
					'$dtc_4',
					'$dtc_5'
				)";
//echo "inserting paesd\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESE')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$high_res_odo = $str[$paes_offset + 1];
			$trip_time = $str[$paes_offset + 2];
			$idle_time = $str[$paes_offset + 3];
			$harsh_accel = $str[$paes_offset + 4];
			$harsh_brake = $str[$paes_offset + 5];
			$over_speed = $str[$paes_offset + 6];
			$over_rpm = $str[$paes_offset + 7];
			$heavy_accel = $str[$paes_offset + 8];
			$coasting = $str[$paes_offset + 9];
			$cruise_ctrl = $str[$paes_offset + 10];
			$power_take_off = $str[$paes_offset + 11];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
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
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESF')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$total_used = $str[$paes_offset + 1];
			$trip_used = $str[$paes_offset + 2];
			$trip_used_idling = $str[$paes_offset + 3];

			$sql = "INSERT INTO telem_paesf_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					total_used,
					trip_used,
					trip_used_idling
				) VALUES (
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
					$total_used,
					$trip_used,
					$trip_used_idling
				)";
//echo "inserting paesf\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESG')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$ignition_source = $str[$paes_offset + 1];
			$high_res_odo = $str[$paes_offset + 2];

			$sql = "INSERT INTO telem_paesg_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					ignition_source,
					high_res_odo
				) VALUES (
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($ignition_source) . ", "
					. $this->odsconnector->stringToDbValue($high_res_odo) . "
				)";
//echo "inserting paesg\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESI')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$avg_model = $str[$paes_offset + 1];
			$serial_no = $str[$paes_offset + 2];
			$firmware_name = $str[$paes_offset + 3];
			$firmware_version = $str[$paes_offset + 4];
			$bootloader_version = $str[$paes_offset + 5];
			$reset_type = $str[$paes_offset + 6];
			$reset_code = $str[$paes_offset + 7];
			$boot_code = $str[$paes_offset + 8];
			$vehicle_voltage = $str[$paes_offset + 9];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($avg_model) . ", "
					. $this->odsconnector->stringToDbValue($serial_no) . ", "
					. $this->odsconnector->stringToDbValue($firmware_name) . ", "
					. $this->odsconnector->stringToDbValue($firmware_version) . ", "
					. $this->odsconnector->stringToDbValue($bootloader_version) . ", "
					. $this->odsconnector->stringToDbValue($reset_type) . ", "
					. $this->odsconnector->stringToDbValue($reset_code) . ", "
					. $this->odsconnector->stringToDbValue($boot_code) . ", "
					. $this->odsconnector->stringToDbValue($vehicle_voltage) . ");";
//echo "inserting paesi\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAEST')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$event_id = $str[$paes_offset + 1];
			$duration = $str[$paes_offset + 2];
			$threshold = $str[$paes_offset + 3];

			$sql = "INSERT INTO telem_paest_fact (
					sourcefile, gis_id, vehicle_id, driver_id, trip_id, date_id, time_id,
					event_id,
					duration,
					threshold
				) VALUES (
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($event_id) . ", "
					. $this->odsconnector->stringToDbValue($duration) . ", "
					. $this->odsconnector->stringToDbValue($threshold) . "
				);";
//echo "inserting paest\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESP')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$band_1 = $str[$paes_offset + 1];
			$band_2 = $str[$paes_offset + 2];
			$band_3 = $str[$paes_offset + 3];
			$band_4 = $str[$paes_offset + 4];
			$band_5 = $str[$paes_offset + 5];
			$band_6 = $str[$paes_offset + 6];
			$band_7 = $str[$paes_offset + 7];
			$band_8 = $str[$paes_offset + 8];
			$band_9 = $str[$paes_offset + 9];
			$band_10 = $str[$paes_offset + 10];
			$band_11 = $str[$paes_offset + 11];
			$band_12 = $str[$paes_offset + 12];
			$band_13 = $str[$paes_offset + 13];
			$band_14 = $str[$paes_offset + 14];
			$band_15 = $str[$paes_offset + 15];
			$band_16 = $str[$paes_offset + 16];
			$band_17 = $str[$paes_offset + 17];
			$band_18 = $str[$paes_offset + 18];
			$band_19 = $str[$paes_offset + 19];
			$band_20 = $str[$paes_offset + 20];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
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
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESR')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);

			$band_1 = $str[$paes_offset + 1];
			$band_2 = $str[$paes_offset + 2];
			$band_3 = $str[$paes_offset + 3];
			$band_4 = $str[$paes_offset + 4];
			$band_5 = $str[$paes_offset + 5];
			$band_6 = $str[$paes_offset + 6];
			$band_7 = $str[$paes_offset + 7];
			$band_8 = $str[$paes_offset + 8];
			$band_9 = $str[$paes_offset + 9];
			$band_10 = $str[$paes_offset + 10];
			$band_11 = $str[$paes_offset + 11];
			$band_12 = $str[$paes_offset + 12];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid,
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
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else if ($paes_type == '$PAESV')
		{
            $this->set_stats($paes_type, $driver_id, $trip_id);
			$vehicle_speed_1 = $str[$paes_offset + 1];
			$fuel_rate_1 = $str[$paes_offset + 2];
			$vehicle_speed_2 = $str[$paes_offset + 3];
			$fuel_rate_2 = $str[$paes_offset + 4];
			$vehicle_speed_3 = $str[$paes_offset + 5];
			$fuel_rate_3 = $str[$paes_offset + 6];
			$no_of_samples_1 = $str[$paes_offset + 7];
			$no_of_samples_2 = $str[$paes_offset + 8];
			$no_of_samples_3 = $str[$paes_offset + 9];

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
					'$source', $gis_id, $vehicle_id, "
					. $this->odsconnector->stringToDbValue($driver_id) . ", "
					. $this->odsconnector->stringToDbValue($trip_id) . ", $dateid, $timeid, "
					. $this->odsconnector->stringToDbValue($vehicle_speed_1) . ", "
					. $this->odsconnector->stringToDbValue($fuel_rate_1) . ", "
					. $this->odsconnector->stringToDbValue($vehicle_speed_2) . ", "
					. $this->odsconnector->stringToDbValue($fuel_rate_2) . ", "
					. $this->odsconnector->stringToDbValue($vehicle_speed_3) . ", "
					. $this->odsconnector->stringToDbValue($fuel_rate_3) . ", 
					$no_of_samples_1,
					$no_of_samples_2,
					$no_of_samples_3
				)";
//echo "inserting paesv\n";
			$ret = $this->odsconnector->executeSQL($sql);
		}
		else
			echo "Unknown type $paes_type\n";

		return $ret;
	}
	
        /*
        $action = $this->content["action"];
        $tripNumber = $this->content["tripNumber"];
        $route = sprintf("%c", $this->content["routeCode1"]);
        if ( $this->content["routeCode2"] ) $route = $route.sprintf("%c", $this->content["routeCode2"]);
        if ( $this->content["routeCode3"] ) $route = $route.sprintf("%c", $this->content["routeCode3"]);
        if ( $this->content["routeCode4"] ) $route = $route.sprintf("%c", $this->content["routeCode4"]);
        //var_dump($this->content);
        $l_action = $this->lastcontent["action"];
        $l_tripNumber = $this->lastcontent["tripNumber"];

        // Route Code seemd to be terminated with a weird character - strip it out
        //$fp = fopen("/tmp/fred", "w+" );
        //fputs($fp, $route);
        //fclose($fp);

        // Extract GPS
        $declat = $this->content["gpslat"];
        $declong = -$this->content["gpslong"];
        $eventid = $this->content["action"];
        $driverCode = $this->content["driverNumber"];

        // Extract Message Time
        $msgTimestamp = $this->content["timeRouteStarted"] + $this->content["sendTimeAddOn"];
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixtimestamp = $timestamp->format("Y-m-d H:i:s");
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");

        // Set other stuff
        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
        $mode =  "A";

        $route = preg_replace("/\s/", "", $route);

        // Find GIS Identifier for record
        $gisarr = array();
        $hash = false;
        $gisid = $this->odsconnector->processGeoItem($declat, $declong, $hash, $gisarr);

        $gisarr["true_course"] = -1;
        $gisarr["fixtime"] = $fixtime;
        $gisarr["fixdate"] = $fixdate;
        $gisarr["event_id"] = $eventid;
        $gisarr["bearing"] = $bearing;

        // New trip started
        if ( $action == 201 || 
            ($action == 206 && $tripNumber != $this->lastcontent["tripNumber"]) ||
            ($action == 212 && $tripNumber != $this->lastcontent["tripNumber"]) ||
            ($action == 232 && $tripNumber != $this->lastcontent["tripNumber"]) 
            )
        {
            // Clear last status content for a new trip
            $this->lastcontent = $this->content;

            // New trip started, process arrivals/departures for any prior one
            if ( $this->timetableVisits )
            {
                $this->commitActualJourney();
                $this->timetableVisits = false;
            }

            if ( $this->ignoreRoute == $route && $this->ignoreTrip == $tripNumber )
                return false;

            $this->ignoreRoute = false;
            $this->ignoreTrip = false;

            $seq = $this->content["locationCode"];

            // Fetch driver id 
            $this->driver_id = $this->odsconnector->getDriver ( $driverCode, $this->operator_code );

            // Fetch trip timetable data so we can tie it up with actuals
            $this->timetableVisits = $this->fetchTimetableJourneyForRouteTrip ( $this->operator_code, $fixdate, $fixtimestamp, $route, $tripNumber);
            if ( !$this->timetableVisits )
            {
                if ( $this->debug )
                    echo "Not found Op:$this->operator_code, Rt:$route, Tr:$tripNumber Sq:$seq Tm:$fixtimestamp\n";
                $this->ignoreRoute = $route;
                $this->ignoreTrip = $tripNumber;
                $this->processed_not_found_trips++;
                return false;
            }
            if ( $this->debug )
                echo "Found     Op:$this->operator_code, Rt:$route, Tr:$tripNumber Sq:$seq Tm:$fixtimestamp, ".$this->timetableVisits[0]["departure_time"]." ";
            $this->processed_found_trips++;

            // Set the journey first message time in the visits array .. this will be used to set journey actual start
            $this->timetableVisits[0]["timestamp"] = $timestamp;
        }

        if ( !$this->timetableVisits )
        {
            //echo "No current timetable id\n";
            return;
        }

        if ( $action == 201 || $action == 206 || $action == 212 || $action == 232 )
        {
            $locno = $this->content["locationCode"] - 1;
            $this->currentLocation = $locno;
            if ( !isset($this->timetableVisits[$locno] ) )
            {
                echo count( $this->timetableVisits); die;
                foreach ( $this->timetableVisits as $k => $v )
                    echo $k." ";
                echo "Cant match message with location order $locno\n";
                return;
            }
            $ttbvisit = $this->timetableVisits[$locno];

            if ( $action == 206 || $action == 232 )
            {
                $this->timetableVisits[$locno]["actual_arrival_time"] = $fixtimestamp;
                $this->timetableVisits[$locno]["actual_departure_time"] = false;
            }
            if ( $action == 212 )
            {
                $this->timetableVisits[$locno]["actual_departure_time"] = $fixtimestamp;
            }
            $this->lastcontent = $this->content;
        }

        if ( $action == 244 // driver entry
            || $action == 209 // driver update
            || $action == 238 // etm valid
            || $action == 235 // etm unknown trip info
            || $action == 236 // etm unknown trip for time of day
            || $action == 237 // etm route corruption
            )
        {
            //echo "\n ".$action." ";
            //echo $this->content["driverNumber"]."";
            ///var_dump ($this->content);
            $this->applyDriverEntryToDriverRunFact ($timestamp);
            
        }

        $this->lastLogMessageTime = $timestamp;


    }

        */
    /*
    ** Finds the driver run for a particular vehicle and time
    */
    function fetchJourneyFactForVehicleAndTime ( $timestamp )
    {

        if ( $this->currentTripStart  &&
            $this->currentTripStart->getTimestamp() <= $timestamp->getTimestamp() &&
            $this->currentTripEnd->getTimestamp() >= $timestamp->getTimestamp() )
        {
            $trip_id = $this->currentTripId;
            return $trip_id;
        }

        $timeymdhms = $timestamp->format("Y-m-d H:i:s");

        $sql = "SELECT timetable_journey_fact.fact_id,
                actual_start,
                actual_end
                FROM timetable_journey_fact 
                WHERE 1 = 1
                AND vehicle_id = $this->vehicle_id
                AND '$timeymdhms' between actual_start and actual_end";
        $ret = $this->odsconnector->fetch1SQL($sql);
        if ( $ret )
        {
            $this->currentTripId = $ret["fact_id"];
            $this->currentTripStart = DateTime::createFromFormat("Y-m-d H:i:s", $ret["actual_start"]);
            $this->currentTripEnd = DateTime::createFromFormat("Y-m-d H:i:s", $ret["actual_end"]);
            return $this->currentTripId;
        }
        else
        {
            $this->currentTripId = false;
            $this->currentTripStart = false;
            $this->currentTripEnd = false;
        }
        return $ret;

    }

    /*
    ** Finds the stored trip for the vehicle time
    */
    function fetchDriverForVehicleAndTime ( $timestamp )
    {

        if ( $this->currentDriverStart  &&
            $this->currentDriverStart->getTimestamp() <= $timestamp->getTimestamp() &&
            $this->currentDriverEnd->getTimestamp() >= $timestamp->getTimestamp() )
        {
            $trip_id = $this->currentDriverId;
            return $trip_id;
        }

        $timeymdhms = $timestamp->format("Y-m-d H:i:s");

        $sql = "SELECT timetable_duty_run_fact.fact_id, driver_id,
                actual_start,
                actual_end
                FROM timetable_duty_run_fact 
                WHERE 1 = 1
                AND vehicle_id = $this->vehicle_id
                AND '$timeymdhms' between actual_start and actual_end";
        $ret = $this->odsconnector->fetch1SQL($sql);
        if ( $ret )
        {
            $this->currentDriverId = $ret["driver_id"];
            $this->currentDriverStart = DateTime::createFromFormat("Y-m-d H:i:s", $ret["actual_start"]);
            $this->currentDriverEnd = DateTime::createFromFormat("Y-m-d H:i:s", $ret["actual_end"]);
            return $this->currentDriverId;
        }
        else
        {
            $this->currentDriverId = false;
            $this->currentDriverStart = false;
            $this->currentDriverEnd = false;
        }
        return $ret;

    }

    /*
    ** REmove previous data loaded for file
    */
    function clearExistingData($filename)
    {
        $tables = array ( 
                    "paesa",
                    "paesb",
                    "paesc",
                    "paesd",
                    "paese",
                    "paesf",
                    "paesg",
                    "paesi",
                    "paesp",
                    "paesr",
                    "paest",
                    "paesf",
                    );
        foreach ( $tables as $v )
        {
            $sql = "DELETE FROM telem_${v}_fact
                WHERE vehicle_id = $this->vehicle_id
                AND sourcefile = '$filename'";
            $ret = $this->odsconnector->executeSQL($sql);
            if ( !$ret )
            {
                echo "Error: Failed to clear duty runs\n";
                die;
            }
        }

    }


    /*
    ** count telematics data types
    */
    function set_stats($paes_type, $driver_id, $trip_id)
    {
        $stripped = substr($paes_type, 1);

        if ( !isset($this->paes_stats[$stripped] ) )
        {
            $this->paes_stats[$stripped] = array ();
            $this->paes_stats[$stripped]["all"] = 0;
            $this->paes_stats[$stripped]["driver"] = 0;
            $this->paes_stats[$stripped]["trip"] = 0;
        }

        $this->paes_stats[$stripped]["all"]++;
        if ( $driver_id )
            $this->paes_stats[$stripped]["driver"]++;
        if ( $trip_id )
            $this->paes_stats[$stripped]["trip"]++;

    }

    /*
    ** show counts of telematics data types
    */
    function show_stats()
    {
        echo "Tematics: $this->sourcefile ";
        foreach ( $this->paes_stats as $k => $v )
        {
            echo " ".$k;
            echo " ".$this->paes_stats[$k]["all"]."/";
            echo " ".$this->paes_stats[$k]["driver"]."/";
            echo " ".$this->paes_stats[$k]["trip"];
            echo "\n";
        }
    }

    /*
    ** Converts a DateInterval value to pure seconds
    */
    function interval_to_secs($int)
    {
        //var_dump($int);
        $secs = 0;
        $extsec = $int->s;
        $extmin = $int->i;
        $exthr = $int->h;
        $invert = $int->invert;
        $x = $int->format("'%r%H:%I:%S'");
        $secs = ( $exthr * 3600 ) + ( $extmin * 60 ) + $extsec;
        if ( $invert )
            $secs = - $secs;
        return $secs;
    }
        
}

?>
