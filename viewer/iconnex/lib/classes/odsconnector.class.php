<?php
include_once("iconnex.class.php");
include_once("geohash.class.php");
include_once("nominatim.class.php");

class odsconnector extends iconnex
{
	public $pdo;
	public $debug = false;

	function __construct($pdo = false)
	{
        if ( $pdo )
		    $this->pdo = $pdo;
	}

	function getVehicleRecordByBuildCode ( $bld )
	{
        $sql = "SELECT operator_dimension.operator_code, vehicle_code, vehicle_dimension.vehicle_id, vehicle_dimension.operator_id FROM vehicle_dimension, operator_dimension
            WHERE vehicle_dimension.system_code = 'iconnex'
            AND vehicle_dimension.operator_id = operator_dimension.operator_id
            AND inventory_code = '".$bld."'"; 
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret && $this->pdo->errorCode() != 0 )
		{
			return false;
		}
		else
		{
			return $ret;
		}
    }

	function getVehicleByBuildCode ( $op, $bld )
	{
		$sql = "SELECT vehicle_id FROM vehicle_dimension 
				WHERE system_code = 'iconnex'
				AND inventory_code = '".$bld."'";
				//"AND operator_code = '".$op."'";
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret && $this->pdo->errorCode() != 0 )
		{
			return false;
		}
		else
		{
			return $ret["vehicle_id"];
		}
	}
	
	function getVehicle ( $op, $veh )
	{
		$sql = "SELECT vehicle_id FROM vehicle_dimension 
				WHERE system_code = 'iconnex'
				AND vehicle_code = '".$veh."'
				AND operator_code = '".$op."'";
		$ret = fetch1SQL ( $sql );
		if ( $this->pdo->errorCode() != 0 )
			return false;
		else
			return $ret["VEHICLE_ID"];
	}
	
	function getGISByHash ( $hash )
	{
		$sql = "SELECT gis_id FROM gis_dimension WHERE geohash = '".$hash."'";
		$ret = $this->fetch1SQL ( $sql );
		if ( !$ret ) 
			return false;
		else
			return $ret["gis_id"];
	}

	function getTrip($timestamp, $operator, $vehicle)
	{
        $datetime = date('Y-m-d H:i:s', $timestamp);

		$sql = "SELECT trip_id
				FROM trip_dimension, vehicle_dimension
				WHERE trip_dimension.system_code = 'iconnex'
				AND trip_dimension.vehicle_id = vehicle_dimension.vehicle_id
				AND vehicle_dimension.vehicle_code = '".$vehicle."'
				AND operator_code = '".$operator."'
				AND actual_start <= '".$datetime."'
				AND actual_end >= '".$datetime."'";
		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
        {
			return $ret["trip_id"];
        }
	}

	function getTripBySchedule($schedule_id)
	{
		$sql = "SELECT trip_id
				FROM trip_dimension
				WHERE trip_dimension.ext_trip_id = $schedule_id";
		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
        {
			return $ret["trip_id"];
        }
	}

	function getDriver($driver_code, $operator)
	{
        //$datetime = date('Y-m-d H:i:s', $timestamp);

		$sql = "SELECT driver_dimension.driver_id
				FROM driver_dimension
				WHERE system_code = 'iconnex'
				AND operator_code = '".$operator."'
				AND employee_code = '".$driver_code."'";

		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
			return $ret["driver_id"];
	}

	function getDriverByTripId($trip_id)
	{
		$sql = "SELECT driver_id
				FROM trip_dimension
				WHERE trip_id = $trip_id";
		$ret = $this->fetch1SQL($sql);

		if ($this->pdo->errorCode() != 0)
			return false;
		else
			return $ret["driver_id"];
	}

	function getVehicleCode($vehicle_id)
	{
		$sql = "SELECT vehicle_code FROM vehicle_dimension WHERE vehicle_id = $vehicle_id";
		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
			return $ret["vehicle_code"];
	}

	function getEstimatedScheduleId($timestamp)
	{
        $datetime = date('Y-m-d H:i:s', $timestamp);

        $sql = "select schedule_id, actual_start, actual_end, layover_end from t_arc
            where actual_end IS NOT NULL
            and layover_end IS NOT NULL
            and '" . $datetime . "' >= addtime(actual_start, '0 0:20:0.0')
            and '" . $datetime . "' <= addtime(actual_end, '0 0:20:0.0');";

		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
		{
			//echo "for ".$datetime."got ".$ret["schedule_id"]. " ".$ret["actual_start"]."-".$ret["actual_end"]." / ".$ret["layover_end"]."\n";
			return $ret["schedule_id"];
		}
	}

	function getScheduleId($timestamp)
	{
        $datetime = date('Y-m-d H:i:s', $timestamp);

        $sql = "select schedule_id, actual_start, actual_end, layover_end from t_arc
            where actual_end IS NOT NULL
            and layover_end IS NOT NULL
            and '" . $datetime . "' >= addtime(actual_start, '0 0:20:0.0')
            and '" . $datetime . "' <= addtime(actual_end, '0 0:20:0.0');";

        $sql = "select schedule_id, actual_start, actual_end, layover_end from t_arc
            where actual_end IS NOT NULL
            and layover_end IS NOT NULL
            and '" . $datetime . "' >= addtime(actual_start, '0 0:00:0.0')
            and '" . $datetime . "' <= addtime(actual_end, '0 0:00:0.0');";


		$ret = $this->fetch1SQL($sql);
		if ($this->pdo->errorCode() != 0)
			return false;
		else
		{
			//echo "for ".$datetime."got ".$ret["schedule_id"]. " ".$ret["actual_start"]."-".$ret["actual_end"]." / ".$ret["layover_end"]."\n";
			return $ret["schedule_id"];
		}
	}

    function api_connect($url, $params = NULL, $dopost = false){
        
        //$key = self::API_KEY;
        //$secret = self::API_SECRET;
        //echo "Url ".$url."\n";
       
        //add POST fields
        $postData = false;
        $headers = false;
        if ($params != NULL){
                        
            //url encode params array before POST
            $postData = http_build_query($params, '', '&');
            // generate the extra headers
            //$headers = array(
                //'Rest-Key: '.$key,
                //'Rest-Sign: '.base64_encode(hash_hmac('sha512', $postData, base64_decode($secret), true)),
                //);

        }
    

        // our curl handle (initialize if required)
        $ch = null;
        if (is_null($ch)) {
            if ( !$dopost && $postData )
                $url .= "?".$postData;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MtGox PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }

        if ( $postData && $dopost )
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt ($ch, CURLOPT_FRESH_CONNECT, 1); 
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

        curl_setopt($ch, CURLOPT_PROXY, "http://10.0.100.1:3128");
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        //curl_setopt ($ch, CURLOPT_PROXYUSERPWD, "xxx:xxx"); 
              
        //execute CURL connection
        $returnData = curl_exec($ch);
                
        if( $returnData === false)
        {
            $info = curl_getinfo($ch);
            echo '<br />Connection error:' . curl_error($ch);
            return false;
        }

        //close CURL connection
        curl_close($ch);
                                
        return $returnData;
    }

    function processGeoItem($declat, $declong)
    {
        $geohash = new GeoHash();
        $hash= $geohash->encode($declat, $declong);
		$hash = substr($hash, 0, 8);
	    $gisarr = array (
					"lat" => $declat,
					"long" => $declong,
					"geohash" => $hash,
					"geodata" => false
					);

        $linect = 1;
		$nomct = 0;
	    if ( !($gisid = $this->getGISByHash($hash )) )
	    {
		    $nominatim = new nominatim ( $declat, $declong );
		    $geodata = $nominatim->reverse();
		    $gisarr["geodata"] = $geodata;
		    $txt = $geodata["place_id"]."_".implode(",",$geodata["address"]);
		    $ret = $this->applyGIS($gisarr);
		   	$this->show_debug ( $hash." = ".$txt."<br>" ) ;
            $gisid = $this->lastInsertId("gis_dimension");
	    }
        return $gisid;
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

            // integer too big for informix db... 
            /*
			$sql = "INSERT INTO gis_dimension
				(  gis_id, geohash, osm_place_id, latitude, longitude, addr_road,
  					addr_suburb, addr_city, addr_country, addr_county, addr_postcode )
				VALUES
				( 0,
				'".$ar["geohash"]."',
				'".$ar["geodata"]["place_id"]."',
				'".$ar["lat"]."',
				'".$ar["long"]."',
				\"". $road ."\",
				\"". $suburb ."\",
				\"". $city ."\",
				\"". $country ."\",
				\"". $county ."\",
				\"". $postcode ."\"
				)";
                */
			$sql = "INSERT INTO gis_dimension
				(  gis_id, geohash, osm_place_id, latitude, longitude, addr_road,
  					addr_suburb, addr_city, addr_country, addr_county, addr_postcode )
				VALUES
				( 0,
				'".$ar["geohash"]."',
				NULL,
				'".$ar["lat"]."',
				'".$ar["long"]."',
				\"". $road ."\",
				\"". $suburb ."\",
				\"". $city ."\",
				\"". $country ."\",
				\"". $county ."\",
				\"". $postcode ."\"
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
   
}

?>