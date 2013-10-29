<?php

include "iconnex.php";
include "geohash.class.php";
include "nominatim.php";
include "odsconnector.php";

class gisconnector extends odsconnector
{
	public $fact_no= 0;

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


	function applyFACT ( $ar, $source, $operator, $vehicle, $trip_id, $driver_id, $event_type )
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
		$dateid = "20".$ar["fixdate"];
		$timeid = $ar["fixtime"];
		$speed = round($ar["speed_knots"] / 1.15077945, 0);
		$bearing = round($ar["true_course"] , 0);
			
		$ret = true;
		if ( $ret )
		{
			$sql = "INSERT INTO gps_fact
				(  gis_id, sourcefile, event_id, vehicle_id, driver_id, trip_id, date_id, time_id,
  					speed_mph, bearing )
				VALUES
				( $gisid,
				'$source',
                $event_type,
				$vehicle,
				$driver,
				$trip,
				$dateid,
				$timeid,
				$speed,
				$bearing
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	
	function applyGIS (  $ar )
	{
		$sql = "SELECT geohash FROM gis_dimension WHERE geohash = '".$ar["geohash"]."'";
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
			$sql = "INSERT INTO gis_dimension
				(  gis_id, geohash, geohash2, osm_place_id, latitude, longitude, addr_road,
  					addr_suburb, addr_city, addr_country, addr_county, addr_postcode )
				VALUES
				( 0,
				'".$ar["geohash"]."',
				'".$ar["geohash2"]."',
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
				//echo "Couldn't find schedule_id for vehicle $vehicle_code at $timestamp<BR>\n";
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
	

	function utctolocaltimestamp($intime, $informat)
	{
		$start = DateTime::createFromFormat($informat, $intime, new DateTimeZone("Europe/London"));
		$start->setTimezone (new DateTimeZone("Europe/London"));
		return $start->getTimestamp();
	}

	function utctolocal($intime, $informat)
	{
		$start = DateTime::createFromFormat($informat, $intime, new DateTimeZone("Europe/London"));
		$start->setTimezone (new DateTimeZone("Europe/London"));
		return $start->format('ymdHis');
	}

	function gps_import($input, $operator, $vehicle)
	{
        global $rtpi;
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
			if ( true )
			{
				$mode = "A";
				if ( $mode != "A" ) 
                    continue; 
                if ( count ( $gps) < 7 )
                    continue;

				$fixtime =  $gps[1];
				$lat =  $gps[5];
				$long =  $gps[6];
				//$vehicle =  $gps[2];
				$event_type =  $gps[3];
				$event_text =  $gps[4];

				$localtime = $this->utctolocal($fixtime, "Y-m-d H:i:s");
				$timestamp = $this->utctolocaltimestamp($fixtime, "Y-m-d H:i:s");
echo "\n";
echo $localtime;
echo "\n";
				$fixdate = substr($localtime, 0, 6);
				$fixtime = substr($localtime, 6, 6);

			    $vehicle_id = NULL;
			    if (!($vehicle_id = $this->getVehicleByBuildCode($operator, $vehicle)))
			    {
				    echo "Unknown build_code $vehicle";
				    return false;
			    }

			    $vehicle_code = $this->getVehicleCode($vehicle_id);
			    if ($ct++ == 0)
				    $rtpi->initTripSearch($vehicle_code, $timestamp, $this);

				$declat = $lat;
				$declong = -$long;

				$hash= $geohash->encode($declat, $declong);
				$hash = substr($hash, 0, 8);
				$hash2 = substr($hash, 0, 7);
				$gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geohash2" => $hash2,
					"geodata" => false,
					"geodata2" => false,
					"speed_knots" =>  -1,
					"true_course" =>  -1,
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


				$ret = $this->applyFACT($gisarr, $sourcefile, $operator, $vehicle, $trip_id, $driver_id, $event_type);
				if ( !$ret ) 
				{
					break;
				}
			} 
			$linect++;
		}
		echo "$nomct / $linect nominatim calls";
	}
}

function identify_import_files($lookin, $container)
{
	global $imports;
	//$this->show_debug ( "Process $container,$lookin <BR>" );
	$ret = false;

	$location = $container.$lookin;

	// Generate Menu from XML files
       	if (!is_dir($location) && !is_file($location))
	{
		trigger_error("$location is not a valid location", E_USER_ERROR);
		return $ret;
	}
	if ( is_dir (  $location )  )
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
					if ( is_file ( $location."/".$file ) && preg_match ( "/routecsv.\d{8}$/", $file ) )
						$imports[] =  $location."/".$file;
				}
			}
			closedir($dh);
		}
	}
	else
	{
		if ( $location == "." || $location == ".." )
			break;

		if ( is_file ( $location ) && preg_match ( "/routecsv.\d{8}$/", $location ) )
			$imports[] =  $location;
	}

	$ret = true;
	return $ret;
}

global $imports;
global $rtpi;
$import = false;
$rtpi = new iconnex;
$gis = new gisconnector($_pdo);
$gis->debug = false;
$file = $_criteria["gpsfile"]->get_criteria_value("VALUE", false);
$file = $file;
//$gis->gps_clear();
if ( identify_import_files($file, "/opt/centurion/live/data/import/") )
{
	$ct = 0;
	foreach ( $imports as $file )
	{
		flush();
		$ct++;
		$vehicle = basename(dirname($file));
		$operator = basename(dirname(dirname($file)));
		$gis->fact_no = 0;
		$gis->gps_import($file, $operator, $vehicle);
		system("gzip $file");
	}
}
ob_flush();
die;
?>
