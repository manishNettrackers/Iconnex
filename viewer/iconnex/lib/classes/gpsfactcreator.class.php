<?php

include_once "config.php";
include_once "odsconnector.class.php";
include_once "rtpiconnector.class.php";
include_once "geohash.class.php";
include_once "nominatim.class.php";

class gpsfactcreator extends odsconnector
{
	public $fact_no= 0;
    public $rtpiconnector = false;

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


	/*
    ** Creates a fact entry for a real time event
    */
    function applyCurrentFACT ( $ar, $content, $source, $operator, $vehicle, $trip_id, $driver_id )
	{

		if ( !$vehicle = $this->getVehicleByBuildCode($operator, $vehicle) )
		{
			echo "Unknown Vehicle $operator $vehicle\n";
			return false;
		}

		if ( !$gisid = $this->getGISByHash($ar["geohash"]) )
		{
			echo "Unknown hash $gisid<BR>";
			return false;
		}

		$driver = $driver_id;
		$trip = $trip_id;

		$eventid = $ar["event_id"];
		$dateid = $ar["fixdate"];
		$timeid = $ar["fixtime"];
        if ( !isset ($ar["speed_knots"] ) )
            $speed = 0;
        else
		    $speed = round($ar["speed_knots"] / 1.15077945, 0);
		$bearing = $ar["bearing"];

        $traveltime = "NULL";
        $traveltimesch = "NULL";
        $dwelltime = "NULL";
        $dwellgain = "NULL";
        $travelgain = "NULL";
        $latenessgain = "NULL";
        $routeid = "NULL";
        $location_id = "NULL";
        $plocation_id = "NULL";
        $prev_tp_lateness = "NULL";
        $latenessarr = "NULL";
        $latenessdep = "NULL";
        $stopbearing = "NULL";
        $etmroute = "NULL";
        $etmduty = "NULL";
        $etmtrip = "NULL";
        $etmrb = "NULL";
        $incount = "NULL";
        $outcount = "NULL";
        $totincount = "NULL";
        $totoutcount = "NULL";
        $occupancy = "NULL";

		if ( isset ( $content->dwell_time )  ) $dwelltime = $content->dwell_time;
		if ( isset ( $content->prev_tp_lateness )  ) $prev_tp_lateness = $content->prev_tp_lateness;
		if ( isset ( $content->tp_travel_time )  ) $traveltime = $content->tp_travel_time;
		if ( isset ( $content->tp_travel_time_sch )  ) $traveltimesch = $content->tp_travel_time_sch;
		if ( isset ( $content->dwell_gain )  ) $dwellgain = $content->dwell_gain;
		if ( isset ( $content->travel_gain )  ) $travelgain = $content->travel_gain;
		if ( isset ( $content->lateness_gain )  ) $latenessgain = $content->lateness_gain;
		if ( isset ( $content->location_id )  ) $location_id = $content->location_id;
		if ( isset ( $content->route_id )  ) $routeid = $content->route_id;
		if ( isset ( $content->prev_location_tp )  ) $plocation_id = $content->prev_location_tp;
		if ( isset ( $content->lateness_dep )  ) $latenessdep = $content->lateness_dep;
		if ( isset ( $content->lateness_arr )  ) $latenessarr = $content->lateness_arr;
		if ( isset ( $content->bearing )  ) $stopbearing = $content->bearing;
		if ( isset ( $content->etmroute )  ) $etmroute = "'".$content->etmroute."'";
		if ( isset ( $content->etmduty )  ) $etmduty = "'".$content->etmduty."'";
		if ( isset ( $content->etmrunningno )  ) $etmrb = "'".$content->etmrunningno."'";
		if ( isset ( $content->etmtrip )  ) $etmroute = "'".$content->etmtrip."'";
		if ( isset ( $content->in )  ) { echo "innnn "; $incount = "'".$content->in."'";}
		if ( isset ( $content->out )  ) { echo "outtt "; $outcount = "'".$content->out."'";}
		if ( isset ( $content->total_in )  ) $totincount = "'".$content->total_in."'";
		if ( isset ( $content->total_out )  ) $totoutcount = "'".$content->total_out."'";
		if ( isset ( $content->in_count )  ) $incount = "'".$content->in_count."'";
		if ( isset ( $content->out_count )  ) $outcount = "'".$content->out_count."'";
		if ( isset ( $content->occupancy )  ) $occupancy = "'".$content->occupancy."'";
			
		$ret = true;
		if ( $ret )
		{
			$sql = "INSERT INTO gps_fact_real_time
				(  gis_id, sourcefile, event_id, vehicle_id, driver_id, trip_id, date_id, time_id, route_id,
  					speed_mph, bearing, location_id, prev_location_id, dwell_time, travel_time, travel_time_sch, dwell_gain, travel_gain, lateness_gain,
                        lateness_arr, lateness_dep, stop_bearing, etm_route, etm_trip, etm_runningno, etm_duty, count_in, count_out, occupancy
                        )
				VALUES
				( $gisid,
				'$source',
				$eventid,
				$vehicle,
				$driver,
				$trip,
				$dateid,
				$timeid,
				$routeid,
				$speed,
                $bearing,
                $location_id,
                $plocation_id,
                $dwelltime,
                $traveltime,
                $traveltimesch,
                $dwellgain,
                $travelgain,
                $latenessgain,
                $latenessarr,
                $latenessdep,
                $stopbearing,
                $etmroute, $etmtrip, $etmrb, $etmduty,
                $incount,
                $outcount,
                $occupancy
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	
    /**
     * Creates a fact entry for a historical logged event
     */
	function applyFACT ( $ar, $source, $operator, $vehicle, $trip_id, $driver_id )
	{
		if ( !$vehicle = $this->getVehicleByBuildCode($operator, $vehicle) )
		{
			echo "Unknown Vehicle $vehicle";
			return false;
		}
		if ( !$gisid = $this->getGISByHash($ar["geohash"]) )
		{
			echo "Unknown hash $gisid<BR>";
			return false;
		}

		if ( $this->fact_no++ == 0 )
		{
			$sql = "DELETE FROM gps_fact
				WHERE vehicle_id = $vehicle
				AND sourcefile = '$source'";
			$ret = $this->executeSQL ( $sql );
		}
		
		$driver = $driver_id;
		$trip = $trip_id;
		$dateid1 = $ar["fixdate"];
		$dateid = "20". substr($dateid1, 4, 2).substr($dateid1, 2, 2).substr($dateid1, 0, 2);
		$timeid = $ar["fixtime"];
		$speed = round($ar["speed_knots"] / 1.15077945, 0);
			
		$ret = true;
		if ( $ret )
		{
			$sql = "INSERT INTO gps_fact
				(  gis_id, sourcefile, vehicle_id, driver_id, trip_id, date_id, time_id,
  					speed_mph )
				VALUES
				( $gisid,
				'$source',
				$vehicle,
				$driver,
				$trip,
				$dateid,
				$timeid,
				$speed
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	
    function applyUnitStatus ($msgTimestamp, $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong )
	{
		$sql = "SELECT vehicle_id FROM unit_status WHERE vehicle_id = '".$vehicle_id."'";
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret )
        {
            $sql = "INSERT INTO unit_status 
                        ( fact_time, event_id, route_id, gis_id,
                        vehicle_id, driver_id, location_id, trip_id,
                            date_id, time_id, latitude, longitude )
                        VALUES
                        (
                            '$msgTimestamp', $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, 
                            $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong 
                        )";
        }
        else
        {
            $sql = "UPDATE unit_status SET
                        ( fact_time, event_id, route_id, gis_id,
                           vehicle_id, driver_id, location_id, trip_id,
                            date_id, time_id, latitude, longitude )
                        =
                        (
                            '$msgTimestamp', $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, 
                            $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong 
                        )
                        WHERE vehicle_id = $vehicle_id";
        }
        $ret = $this->executeSQL ( $sql );
        return $ret;
	}
	
    function applyUnitStatusCount ($msgTimestamp, $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong, $content )
	{
        $in = "NULL";
        $out = "NULL";
        $totin = "NULL";
        $totout = "NULL";
        $occupancy = "NULL";
		if ( isset ( $content["in"] )  ) { $in = $content["in"];}
		if ( isset ( $content["out"] )  ) { $out = $content["out"];}
		if ( isset ( $content["total_in"] )  ) $totin = $content["total_in"];
		if ( isset ( $content["total_out"] )  ) $totout = $content["total_out"];
		if ( isset ( $content["in_count"] )  ) $in = $content["in_count"];
		if ( isset ( $content["out_count"] )  ) $out = $content["out_count"];
		if ( isset ( $content["occupancy"] )  ) $occupancy = $content["occupancy"];

		$sql = "SELECT vehicle_id FROM unit_status_counter WHERE vehicle_id = '".$vehicle_id."'";
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret )
        {
            $sql = "INSERT INTO unit_status_counter
                        ( fact_time, event_id, route_id, gis_id,
                        vehicle_id, driver_id, location_id, trip_id,
                            date_id, time_id, latitude, longitude,
                            count_in,
                            count_out,
                            total_count_in,
                            total_count_out,
                            occupancy 
                        )
                        VALUES
                        (
                            '$msgTimestamp', $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, 
                            $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong ,
                            $in, $out, $totin, $totout, $occupancy
                        )";
        }
        else
        {
            $sql = "UPDATE unit_status_counter SET
                        ( fact_time, event_id, route_id, gis_id,
                           vehicle_id, driver_id, location_id, trip_id,
                            date_id, time_id, latitude, longitude ,
                            count_in,
                            count_out,
                            total_count_in,
                            total_count_out,
                            occupancy 
                        )
                        =
                        (
                            '$msgTimestamp', $event_id, $route_id, $gis_id, $vehicle_id, $driver_id, 
                            $location_id, $trip_id,  $fixdate, $fixtime, $declat, $declong ,
                            $in, $out, $totin, $totout, $occupancy
                        )
                        WHERE vehicle_id = $vehicle_id";
        }
        $ret = $this->executeSQL ( $sql );
        return $ret;
	}
	

	function applyGIS (  $ar )
	{
		$sql = "SELECT gis_id FROM gis_dimension WHERE geohash = '".$ar["geohash"]."'";
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret )
		{
			if ( !isset ( $ar["geodata"]["address"]["postcode"] )  ) $ar["geodata"]["address"]["postcode"] = "";
			if ( !isset ( $ar["geodata"]["address"]["road"] )  ) $ar["geodata"]["address"]["road"] = "";
			if ( !isset ( $ar["geodata"]["address"]["suburb"] )  ) $ar["geodata"]["address"]["suburb"] = "";
			if ( !isset ( $ar["geodata"]["address"]["city"] )  ) $ar["geodata"]["address"]["city"] = "";
			if ( !isset ( $ar["geodata"]["address"]["country"] )  ) $ar["geodata"]["address"]["country"] = "";
			if ( !isset ( $ar["geodata"]["address"]["county"] )  ) $ar["geodata"]["address"]["county"] = "";
			$road = $ar["geodata"]["address"]["road"] ? $ar["geodata"]["address"]["road"] : "";
			$suburb = $ar["geodata"]["address"]["suburb"] ? $ar["geodata"]["address"]["suburb"] : "";
			$city = $ar["geodata"]["address"]["city"] ? $ar["geodata"]["address"]["city"] : "";
			$country = $ar["geodata"]["address"]["country"] ? $ar["geodata"]["address"]["country"] : "";
			$county = $ar["geodata"]["address"]["county"] ? $ar["geodata"]["address"]["county"] : "";
			$postcode = $ar["geodata"]["address"]["postcode"] ? $ar["geodata"]["address"]["postcode"] : "";

			if (!isset($ar["geodata"]["place_id"]))
                $ar["geodata"]["place_id"] = 0;

			$sql = "INSERT INTO gis_dimension
				(  gis_id, geohash, osm_place_id, latitude, longitude, addr_road,
  					addr_suburb, addr_city, addr_country, addr_county, addr_postcode )
				VALUES
				( ".$this->syntax_insert_serial("gis_dimension", "gis_id").",
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
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	

	function gps_clear()
	{
		$ret = $this->executeSQL ( "DELETE FROM gps_fact WHERE 1 = 1" );
		$ret = $this->executeSQL ( "DELETE FROM gis_dimension WHERE 1 = 1" );
	}

	function applyCurrentTripSchedule($operator, $schedule_id, $vehicle_id)
	{
		$ret = false;
		$trip_id = null;
		$driver_id = null;

		if (!($trip_id = $this->getTripBySchedule($schedule_id)))
		{
			$trip = NULL;
			if (!($trip = $this->rtpiconnector->getCurrentTripBySchedule($schedule_id)))
			{
				echo "Failed to find trip details for schedule $schedule_id\n";
				return false;
			}
			$sql = "INSERT INTO trip_dimension
				( 
					trip_id,
					ext_trip_id,
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
				(0, $schedule_id,
				$vehicle_id,
				0,
				'iconnex',
				'".trim($trip["route_code"])."',
				'".trim($trip["trip_no"])."',
				'".trim($trip["duty_no"])."',
				'".trim($trip["running_no"])."',
				'".$trip["actual_start"]."',
				".$trip["start_day"].",
				'".$trip["actual_start"]."')";
			$ret = $this->executeSQL($sql);
			//$trip_id = $this->pdo->lastInsertId();
			$trip_id = $schedule_id;
			$driver_id = null;
			$driver_code = trim($trip["employee_code"]);
			if (!($driver_id = $this->getDriver($driver_code, $operator)))
			{
				$sql = "INSERT INTO driver_dimension
					(driver_id, system_code, operator_code, employee_code, fullname)
					VALUES
					(0, 'iconnex', '$operator',
					'" . $driver_code . "',
					'" . trim($trip["fullname"]) . "'
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
			echo "Found trip $trip_id in ods db, looking up driver\n";
			$driver_id = $this->getDriverByTripId($trip_id);
		}
	
		return array("trip_id" => $trip_id, "driver_id" => $driver_id);
	}
	
	function applyCurrentTrip($timestamp, $operator, $vehicle_id, $vehicle_code)
	{
		$ret = false;
		$trip_id = null;
		$driver_id = null;

		if (!($trip_id = $this->getTrip($timestamp, $operator, $vehicle_code)))
		{
			$trip = NULL;
			if (!($schedule_id = $this->rtpiconnector->getCurrentTripScheduleByVehicle($vehicle_id) ) )
			{
				//echo "Couldn't find schedule_id for vehicle $vehicle_code at $timestamp<BR>\n";
				return false;
			}
			if (!($trip = $this->rtpiconnector->getCurrentTripBySchedule($schedule_id)))
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
				'".trim($trip["route_code"])."',
				'".trim($trip["trip_no"])."',
				'".trim($trip["duty_no"])."',
				'".trim($trip["running_no"])."',
				'".$trip["actual_start"]."',
				".$trip["start_day"].",
				'".$trip["actual_end"]."')";
			$ret = $this->executeSQL($sql);
			$trip_id = $this->pdo->lastInsertId();
			$driver_id = null;
			$driver_code = trim($trip["employee_code"]);
			if (!($driver_id = $this->getDriver($driver_code, $operator)))
			{
				$sql = "INSERT INTO driver_dimension
					(driver_id, system_code, operator_code, employee_code, fullname)
					VALUES
					(0, 'iconnex', '$operator',
					'" . $driver_code . "',
					'" . trim($trip["fullname"]) . "'
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
	
	function applyTrip($timestamp, $operator, $vehicle_id, $vehicle_code)
	{
		$ret = false;
		$trip_id = null;
		$driver_id = null;

		if (!($trip_id = $this->getTrip($timestamp, $operator, $vehicle_code)))
		{
			$trip = NULL;
			if (!($schedule_id = $this->getScheduleId($timestamp)))
			{
				//echo "Couldn't find schedule_id for vehicle $vehicle_code at $timestamp<BR>\n";
				return false;
			}
			if (!($trip = $this->rtpiconnector->getTripBySchedule($schedule_id)))
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
				'".trim($trip["route_code"])."',
				'".trim($trip["trip_no"])."',
				'".trim($trip["duty_no"])."',
				'".trim($trip["running_no"])."',
				'".$trip["actual_start"]."',
				".$trip["start_day"].",
				'".$trip["actual_end"]."')";
			$ret = $this->executeSQL($sql);
			$trip_id = $this->pdo->lastInsertId();
			$driver_id = null;
			$driver_code = trim($trip["employee_code"]);
			if (!($driver_id = $this->getDriver($driver_code, $operator)))
			{
				$sql = "INSERT INTO driver_dimension
					(driver_id, system_code, operator_code, employee_code, fullname)
					VALUES
					(0, 'iconnex', '$operator',
					'" . $driver_code . "',
					'" . trim($trip["fullname"]) . "'
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
	

	function utctolocaltimestamp($intime, $informat)
	{
		$start = DateTime::createFromFormat($informat, $intime, new DateTimeZone("GMT"));
		$start->setTimezone (new DateTimeZone("Europe/London"));
		return $start->getTimestamp();
	}

	function utctolocal($intime, $informat)
	{
		$start = DateTime::createFromFormat($informat, $intime, new DateTimeZone("GMT"));
		$start->setTimezone (new DateTimeZone("Europe/London"));
		return $start->format('ymdHis');
	}

	/***************************************************************
	** Takes a GPS coordinate pair and generates a GIS dimension element
	** for its associated geohash if it foes not already exist .. calls 
	** Nominatim revers geovoder for this. */
    function processGeoItem($declat, $declong, &$hash, &$gisarr)
    {
        Utility::backtrace();
        $geohash = new GeoHash();
        $hash = $geohash->encode($declat, $declong);
        $hash = substr($hash, 0, 8);
        $gisarr = array (
                "lat" => $declat,
                "long" => $declong,
                "geohash" => $hash,
                "geodata" => false
                );

        $linect = 1;
        $nomct = 0;
        if (!($gisid = $this->getGISByHash($hash)))
        {
            $nominatim = new nominatim($declat, $declong);
            $geodata = $nominatim->reverse();
            if (isset($geodata["error"]))
            {
                echo "nominatim reverse geocode failed for $declat, $declong\n";
                print_r($geodata);
                echo "\n";
                return (0);
            }
            $gisarr["geodata"] = $geodata;
            if (!isset($geodata["place_id"]) || !isset($geodata["address"]))
            {
                $geodata["place_id"] = false;
                $geodata["address"] = " ";
            }
            else
            {
                $txt = $geodata["place_id"]."_".implode(",",$geodata["address"]);
                $this->show_debug($hash." = ".$txt."<br>");
            }
            $ret = $this->applyGIS($gisarr);
            $gisid = $this->lastInsertId("gis_dimension", "gis_id");
        }

        if (!$gisid)
            print_r($geodata);
        return $gisid;
    }

	/***************************************************************
	** Takes an ODS info  message received from an RT server and generates 
	** fact tables for it
	** */
	function import_gps_route_status($input)
	{
        $operator = $input->operator_code;
        $vehicle = $input->sender;
        $content = $input->messageContent;
        $locationid = false;
        if (!isset($input->messageContent->gpslat))
        {
                echo "No gps set";
                return;
        }
        $declat = $input->messageContent->gpslat;
        $declong = -$input->messageContent->gpslong;
        $mode =  $input->messageContent->gpsstatus;
        $eventid = $input->messageContent->action;
        if ( isset ( $input->messageContent->location_id ) )
            $locationid = $input->messageContent->location_id;
        if ( isset ( $input->messageContent->prev_location_tp ) )
            $plocationid = $input->messageContent->prev_location_tp;
        else
            $plocationid = 0;
        $msgTimestamp = $content->message_time;
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");

        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
		$geohash = new GeoHash();

	   	$vehicle_id = $input->messageContent->vehicle_id;
//echo $vehicle_id;
	    //$vehicle_code = $this->getVehicleCode($vehicle_id);
        //if ( !$vehicle_code )
        //{
            //echo "Cant Find Vehicle for id $vehicle_id - aborting\n";
        //}

/*
        $hash= $geohash->encode($declat, $declong);
		$hash = substr($hash, 0, 8);
	    $gisid = $this->getGISByHash($hash );
*/

        $hash = false;
        $gisid = $this->processGeoItem($declat, $declong, $hash, $gisarr);

	    $gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geodata" => false,
					"speed_knots" =>  0,
					"true_course" =>  -1,
					"fixtime" => $fixtime,
					"fixdate" =>  $fixdate,
					"event_id" =>  $eventid,
					"bearing" =>  $bearing
					);
        //$eventid = $ar["event_id"];
        //$dateid = $ar["fixdate"];
        //$timeid = $ar["fixtime"];
        //$speed = round($ar["speed_knots"] / 1.15077945, 0);
        //$bearing = $ar["bearing"];

/*
        $linect = 1;
		$nomct = 0;
	    if ( !($gisid = $this->getGISByHash($hash )) )
	    {
	        $nomct++;
		    $nominatim = new nominatim ( $declat, $declong );
		    $geodata = $nominatim->reverse();
		    $gisarr["geodata"] = $geodata;
		    $txt = $geodata["place_id"]."_".implode(",",$geodata["address"]);
		    $ret = $this->applyGIS($gisarr);
		   	$this->show_debug ( $hash." = ".$txt."<br>" ) ;
	    }
*/
        $trip_id = "0";
        $driver_id = "0";
        if ( $this->rtpiconnector && isset ($content->schedule_id ) )
        {
            $ret = $this->applyCurrentTripSchedule($operator, $content->schedule_id, $vehicle_id);
            if ($ret)
            {
                $trip_id = $ret["trip_id"];
                $driver_id = $ret["driver_id"];
            }
        }
        
	    $ret = $this->applyCurrentFACT($gisarr, $content, $sourcefile, $operator, $vehicle, $trip_id, $driver_id);
	    if ( !$ret ) 
	    {
	        echo "Failed to create fact \n";
		    return;
		}

		//echo "O:$operator V:$vehicle Type:$content->action / $linect nominatim calls\n";
    }

	function import_gps_140($input)
	{

        $operator = $input->operator_code;
        $vehicle = $input->sender;
        $content = $input->messageContent;

        $mins = $input->messageContent["gps_lat_minutes" ];
        if ( $mins < 0 )
            $mins = - $mins;
        if ( $mins > 60 ) 
            $mins = $mins - 60;
        $declat = $input->messageContent["gps_lat_degrees"] + ( $mins / 60 )  + ( $input->messageContent["gps_lat_seconds" ] / ( 60 * 60 * 1000 ) ) ;
        if ( $input->messageContent["gps_lat_minutes" ] < 0 )
            $declat = -$declat;

        $mins = $input->messageContent["gps_long_minutes" ];
        if ( $mins < 0 )
            $mins = - $mins;
        $declong = $input->messageContent["gps_long_degrees"] + ( $mins / 60 ) + ( $input->messageContent["gps_long_seconds" ] / ( 60 * 60 * 1000 ) ) ;
        if ( $input->messageContent["gps_long_minutes" ] < 0 )
            $declong = -$declong;
        $declong = -$declong;
if ( $vehicle == "X1001808001" )
{
var_dump($content);
echo "140 $vehicle lat ".$input->messageContent["gps_lat_degrees"]. " ".$input->messageContent["gps_lat_minutes" ]. " ".$input->messageContent["gps_lat_seconds" ]. " ".$declat." ";
echo "long ".$input->messageContent["gps_long_degrees"]. " ".$input->messageContent["gps_long_minutes" ]. " ".$input->messageContent["gps_long_seconds" ]." ".$declong."\n";
}
        $eventid = $input->messageContent["messageType"];
        $msgTimestamp = $content["timeSent"];
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");
        $fixtimestamp = $timestamp->format("Y-m-d H:i:s");

        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
        $mode =  "A";

	    $vehicle_id = NULL;
	    if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $vehicle)))
	    {
		    //echo "Unknown build_code $operator/$vehicle\n";
		    return false;
	    }
	    $vehicle_code = $this->getVehicleCode($vehicle_id);

        $gisarr = array();
        $hash = false;
        $gisid = $this->processGeoItem($declat, $declong, $hash, $gisarr);

        $gisarr["speed_knots"] = 0;
        $gisarr["true_course"] = -1;
        $gisarr["fixtime"] = $fixtime;
        $gisarr["fixdate"] = $fixdate;
        $gisarr["event_id"] = $eventid;
        $gisarr["bearing"] = $bearing;

        $trip_id = "0";
        $driver_id = "0";
        if ( $this->rtpiconnector )
        {
            $ret = $this->applyCurrentTrip($timestamp->getTimestamp(), $operator, $vehicle_id, $vehicle_code);
            if ($ret)
            {
                $trip_id = $ret["trip_id"];
                $driver_id = $ret["driver_id"];
            }
        }

	    $ret = $this->applyUnitStatus($fixtimestamp, $eventid, 0, $gisid, $vehicle_id, 0, 0, 0,  $fixdate, $fixtime, $declat, $declong );
	    if ( !$ret ) 
	    {
	        echo "11111111111111111111111111break";
		    return;
		}

	    $ret = $this->applyUnitStatusCount($fixtimestamp, $eventid, 0, $gisid, $vehicle_id, 0, 0, 0,  $fixdate, $fixtime, $declat, $declong, $content );
	    if ( !$ret ) 
	    {
	        echo "11111111111111111111111111break";
		    return;
		}


	    $ret = $this->applyCurrentFACT($gisarr, $content, $sourcefile, $operator, $vehicle, $trip_id, $driver_id);
	    if ( !$ret ) 
	    {
	        echo "11111111111111111111111111break";
		    break;
		}
    }

	function import_gps_121($input)
	{

        $operator = $input->operator_code;
        $vehicle = $input->sender;
        $content = $input->messageContent;

        $mins = $input->messageContent["gps_lat_minutes" ];
        if ( $mins < 0 )
            $mins = - $mins;
        if ( $mins > 60 ) 
            $mins = $mins - 60;
        $declat = $input->messageContent["gps_lat_degrees"] + ( $mins / 60 )  + ( $input->messageContent["gps_lat_seconds" ] / ( 60 * 60 * 1000 ) ) ;
        if ( $input->messageContent["gps_lat_minutes" ] < 0 )
            $declat = -$declat;

        $mins = $input->messageContent["gps_long_minutes" ];
        if ( $mins < 0 )
            $mins = - $mins;
        $declong = $input->messageContent["gps_long_degrees"] + ( $mins / 60 ) + ( $input->messageContent["gps_long_seconds" ] / ( 60 * 60 * 1000 ) ) ;
        if ( $input->messageContent["gps_long_minutes" ] < 0 )
            $declong = -$declong;
        $declong = -$declong;
if ( $vehicle == "X1001808001" )
{
var_dump($content);
echo "121 $vehicle lat ".$input->messageContent["gps_lat_degrees"]. " ".$input->messageContent["gps_lat_minutes" ]. " ".$input->messageContent["gps_lat_seconds" ]. " ".$declat." ";
echo "long ".$input->messageContent["gps_long_degrees"]. " ".$input->messageContent["gps_long_minutes" ]. " ".$input->messageContent["gps_long_seconds" ]." ".$declong."\n";
}
        $eventid = $input->messageContent["messageType"];
        $msgTimestamp = $content["timeSent"];
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");
        $fixtimestamp = $timestamp->format("Y-m-d H:i:s");

        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
        $mode =  "A";

	    $vehicle_id = NULL;
	    if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $vehicle)))
	    {
		    //echo "Unknown build_code $operator/$vehicle\n";
		    return false;
	    }

	    $vehicle_code = $this->getVehicleCode($vehicle_id);

        $hash = false;
        $gisarr = array();
        $gisid = $this->processGeoItem($declat, $declong, $hash, $gisarr);

        $gisarr["speed_knots"] = 0;
        $gisarr["true_course"] = -1;
        $gisarr["fixtime"] = $fixtime;
        $gisarr["fixdate"] = $fixdate;
        $gisarr["event_id"] = $eventid;
        $gisarr["bearing"] = $bearing;
/*
		$geohash = new GeoHash();
        $hash= $geohash->encode($declat, $declong);
		$hash = substr($hash, 0, 8);
	    $gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geodata" => false,
					"speed_knots" =>  0,
					"true_course" =>  -1,
					"fixtime" => $fixtime,
					"fixdate" =>  $fixdate,
					"event_id" =>  $eventid,
					"bearing" =>  $bearing
					);
	    $gisid = $this->getGISByHash($hash);
*/


        $trip_id = "0";
        $driver_id = "0";
        if ( $this->rtpiconnector )
        {
            $ret = $this->applyCurrentTrip($timestamp->getTimestamp(), $operator, $vehicle_id, $vehicle_code);
            if ($ret)
            {
                $trip_id = $ret["trip_id"];
                $driver_id = $ret["driver_id"];
            }
        }

	    $ret = $this->applyUnitStatus($fixtimestamp, $eventid, 0, $gisid, $vehicle_id, 0, 0, 0,  $fixdate, $fixtime, $declat, $declong );
	    if ( !$ret ) 
	    {
		    return;
		}

	    $ret = $this->applyCurrentFACT($gisarr, $content, $sourcefile, $operator, $vehicle, $trip_id, $driver_id);
	    if ( !$ret ) 
	    {
		    return;
		}
    }

	function import_gps_240($input)
	{
        $operator = $input->operator_code;
        $vehicle = $input->sender;
        $content = $input->messageContent;
        $declat = $input->messageContent["gpslat"];
        $declong = -$input->messageContent["gpslong"];
        $eventid = $input->messageContent["action"];
        $msgTimestamp = $content["timeRouteStarted"] + $content["sendTimeAddOn"];
        $timestamp = new DateTime();
        $timestamp->setTimestamp($msgTimestamp);
        $fixdate = $timestamp->format("Ymd");
        $fixdatedmY = $timestamp->format("dmY");
        $fixtime = $timestamp->format("His");

        $sourcefile = "rtpi.".$fixdatedmY;
        $bearing = -1;
        $mode =  "A";

	    $vehicle_id = NULL;
	    if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $vehicle)))
	    {
		    //echo "Unknown build_code $operator/$vehicle\n";
		    return false;
	    }
	    $vehicle_code = $this->getVehicleCode($vehicle_id);

        $gisarr = array();
        $hash = false;
        $gisid = $this->processGeoItem($declat, $declong, $hash, $gisarr);

        $gisarr["true_course"] = -1;
        $gisarr["fixtime"] = $fixtime;
        $gisarr["fixdate"] = $fixdate;
        $gisarr["event_id"] = $eventid;
        $gisarr["bearing"] = $bearing;
/*
		$geohash = new GeoHash();
        $hash= $geohash->encode($declat, $declong);
		$hash = substr($hash, 0, 8);
	    $gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geodata" => false,
					"speed_knots" =>  0,
					"true_course" =>  -1,
					"fixtime" => $fixtime,
					"fixdate" =>  $fixdate,
					"event_id" =>  $eventid,
					"bearing" =>  $bearing
					);
	    $gisid = $this->getGISByHash($hash );
*/

        $linect = 1;
		$nomct = 0;
	    if ( !($gisid = $this->getGISByHash($hash )) )
	    {
	        $nomct++;
		    $nominatim = new nominatim ( $declat, $declong );
		    $geodata = $nominatim->reverse();
		    $gisarr["geodata"] = $geodata;
		    $txt = $geodata["place_id"]."_".implode(",",$geodata["address"]);
		    $ret = $this->applyGIS($gisarr);
		   	$this->show_debug ( $hash." = ".$txt."<br>" ) ;
	    }

        $trip_id = "0";
        $driver_id = "0";
        if ( $this->rtpiconnector )
        {
            $ret = $this->applyCurrentTrip($timestamp->getTimestamp(), $operator, $vehicle_id, $vehicle_code);
            if ($ret)
            {
                $trip_id = $ret["trip_id"];
                $driver_id = $ret["driver_id"];
            }
        }

	    $ret = $this->applyCurrentFACT($gisarr, $content, $sourcefile, $operator, $vehicle, $trip_id, $driver_id);
	    if ( !$ret ) 
	    {
	        echo "11111111111111111111111111break";
		    break;
		}
		//echo " => $nomct / $linect nominatim calls\n";
    }

	function process_gps_import($input, $operator, $vehicle)
	{
		echo "\nImport $operator $vehicle ".basename(dirname($input))."/".basename($input)." ";
		$sourcefile = basename($input);
		$geohash = new GeoHash();
		$ptr = fopen ( $input, "r" );
		$linect = 0;
		$nomct = 0;
        $ct = 0;
		while ( $val = fgets ( $ptr, 512 ) )
		{
			$gps = preg_split("/,/", $val );
			if ( $gps[0] == "\$GPRMC" )
			{
				$fixtime =  substr($gps[1], 0, 6);
				$mode =  $gps[2];
				if ( $mode != "A" ) 
                    continue; 
                if ( count ( $gps) < 10 )
                    continue;

				$lat =  $gps[3];
				$lath =  $gps[4];
				$long =  $gps[5];
				$longh =  $gps[6];
				$fixdate =  $gps[9];

				// Verify date time are exactly 6 digits
				if ( !preg_match("/^[0-9][0-9][0-9][0-9][0-9][0-9]$/", $fixdate ) )
				{
					echo "baddate $fixdate";
					continue;
				}
				if ( !preg_match("/^[0-9][0-9][0-9][0-9][0-9][0-9]$/", $fixtime ) )
				{
					echo "badtime $fixtime";
					continue;
				}
				$localtime = $this->utctolocal($fixdate.$fixtime, "ymdHis");
				$timestamp = $this->utctolocaltimestamp($fixdate.$fixtime, "dmyHis");
				$fixdate = substr($localtime, 0, 6);
				$fixtime = substr($localtime, 6, 6);

			    $vehicle_id = NULL;
			    if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $vehicle)))
			    {
				    //echo "Unknown build_code $build_code";
				    return false;
			    }

			    $vehicle_code = $this->getVehicleCode($vehicle_id);
			    if ($ct++ == 0)
				    $this->rtpiconnector->initTripSearch($vehicle_code, $timestamp, $this);


				$declat = $this->gps_to_decimal("lat", $lat, $lath);
				$declong = $this->gps_to_decimal("long", $long, $longh);

				$hash= $geohash->encode($declat, $declong);
				$hash = substr($hash, 0, 8);
				$gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geodata" => false,
					"speed_knots" =>  $gps[7],
					"true_course" =>  $gps[8],
					"fixtime" => $fixtime,
					"fixdate" =>  $fixdate
					);
				$gisid = $this->getGISByHash($hash );
				if ( !($gisid = $this->getGISByHash($hash )) )
				{
					$nomct++;
					$nominatim = new nominatim ( $declat, $declong );
					$geodata = $nominatim->reverse();
					$gisarr["geodata"] = $geodata;
					$txt = $geodata["place_id"]."_".implode(",",$geodata["address"]);
					$ret = $this->applyGIS($gisarr);
			        	$this->show_debug ( $hash." = ".$txt."<br>" ) ;
				}

                $trip_id = "0";
                $driver_id = "0";
                $ret = $this->applyTrip($timestamp, $operator, $vehicle_id, $vehicle_code);
                if ($ret)
                {
                    $trip_id = $ret["trip_id"];
                    $driver_id = $ret["driver_id"];
                }

				$ret = $this->applyFACT($gisarr, $sourcefile, $operator, $vehicle, $trip_id, $driver_id);
				if ( !$ret ) 
				{
					echo "11111111111111111111111111break";
					break;
				}
			} 
			$linect++;
		}
		//echo " => $nomct / $linect nominatim calls\n";
	}
}

?>
