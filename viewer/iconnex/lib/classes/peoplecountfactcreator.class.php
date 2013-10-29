<?php

include_once "config.php";
include_once "odsconnector.class.php";
include_once "rtpiconnector.class.php";
include_once "gpsfactcreator.class.php";
include_once "geohash.class.php";
include_once "nominatim.class.php";

class peoplecountfactcreator extends gpsfactcreator
{
    /**
     * Creates a fact entry for a historical logged event
     */
	function applyPeopleCountFact($sourcefile,
        $operator,
        $build_code,
        $unix_timestamp,
        $timestamp,
        $latitude,
        $longitude,
        $gpsage,
        $in,
        $out,
        $totalIn,
        $totalOut,
        $occupancy)
	{
        $gisarr = array();
        $hash = false;
        $gisid = $this->processGeoItem($latitude, $longitude, &$hash, &$gisarr);

        // Get the vehicle details
		if (!$vehicle_id = $this->getVehicleByBuildCode($operator, $build_code))
		{
			echo "Failed to find vehicle for build_code $build_code";
			return false;
		}
        $vehicle_code = $this->getVehicleCode($vehicle_id);

        //if ($this->fact_no == 0)
            //$this->rtpiconnector->initTripSearch($vehicle_code, $unix_timestamp, $this);

        // Get the trip details
        $trip_id = "0";
        $driver_id = "0";
        $ret = $this->applyTrip($unix_timestamp, $operator, $vehicle_id, $vehicle_code);
        if ($ret)
        {
            $trip_id = $ret["trip_id"];
            $driver_id = $ret["driver_id"];
        }

		$dateid = substr($timestamp, 0, 4) . substr($timestamp, 5, 2) . substr($timestamp, 8, 2);
		$timeid = substr($timestamp, 11, 2) . substr($timestamp, 14, 2) . substr($timestamp, 17, 2);

        if ($this->fact_no++ == 0)
        {
            $sql = "DELETE FROM people_count_fact
                WHERE vehicle_id = $vehicle_id
                AND sourcefile = '$sourcefile'";
            $ret = $this->executeSQL($sql);
        }

        $sql = "insert into people_count_fact (
            sourcefile,
            vehicle_id,
            driver_id,
            trip_id,
            gis_id,
            location_id,
            date_id,
            time_id,
            timestamp,
            latitude,
            longitude,
            gpsAge,
            speedKPH,
            bearing,
            in_count,
            out_count,
            total_in,
            total_out,
            occupancy
            )
            values ("
            . "'$sourcefile', "
            . $vehicle_id . ", "
            . $driver_id . ", "
            . $trip_id . ", "
            . $gisid . ", "
            . "NULL, "
            . "'$dateid', "
            . "'$timeid', "
            . "'$timestamp', " 
            . $latitude . ", " 
            . $longitude . ", " 
            . $gpsage . ", NULL, NULL, " 
            . $in . ", " 
            . $out . ", " 
            . $totalIn . ", " 
            . $totalOut . ", "
            . $occupancy . ")";

        $ret = $this->executeSQL($sql);
    }
}
?>
