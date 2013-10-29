<?php

include_once("gps.utility.php");

class iconnexpacket_135 extends iconnexpacket
{
    public $timetableVisits = false;

    public $route_status = false;
    public $last_route_status = false;

    public $pre_arr_in = 0;
    public $pre_arr_out = 0;
    public $aggregate_in = 0;
    public $aggregate_out = 0;
    public $at_rpat_orderby = false;
    public $last_rpat_orderby = false;
    public $occupancy = 0;
    public $prev_occupancy = 0;
    public $up = false;
    public $update_location = false;
    public $sourcefile = false;

    public $timestamp = false;
    public $latitude = false;
    public $longitude = false;
    public $gisid = false;

    function __construct($odsconnector, $rtpiconnector, $inData, $inLength)
    {
        parent::__construct($odsconnector, $rtpiconnector, $inData, $inLength);
    }

    function show()
    {
        var_dump($this->content);
    }

    // Remove any previous counts loaded form this file
    function clearExistingCounts($filename)
    {
        $this->sourcefile = $filename;
        $sql = "DELETE FROM people_count_fact
            WHERE vehicle_id = $this->vehicle_id
            AND sourcefile = '$filename'";
        $ret = $this->odsconnector->executeSQL($sql);
        if ( !$ret )
        {
            echo "Error: Failed to clear counts\n";
            die;
        }
        $sql = "DELETE FROM people_count_visit_fact
            WHERE vehicle_id = $this->vehicle_id
            AND sourcefile = '$filename'";
        $ret = $this->odsconnector->executeSQL($sql);
        if ( !$ret )
        {
            echo "Error: Failed to clear count trip summaries\n";
            die;
        }
        return true;

    }

    function setCountAggregatesFromRouteMessage()
    {
        $l_count_time = false;
        $l_count_date = false;
        $l_char = "";
        $l_hour_int = 0;
        $l_min_int = 0;

        if ( !$this->route_status_packet )
            return 0;

        $this->route_status = $this->route_status_packet->content;
        if ( !$this->route_status )
            return 0;

        $in = $this->content["in"];
        $out = $this->content["out"];
        $loc = $this->route_status_packet->currentLocation;

        if ( $this->debug )
            echo "DEBUG ".  "->". $this->route_status["action"]. " tr ". $this->route_status["tripNumber"]. " loc ". $this->route_status["locationCode"].
            " ".$this->occupancy."\n";

        $l_count_time = new DateTime();
        $l_count_time->setTimestamp($this->content["messageTime"]);

        if ( $this->route_status["action"] == 212 )
        {
            $this->prev_occupancy = $this->occupancy;
            $this->aggregate_in = 0;
            $this->aggregate_out = 0;
            $this->last_rpat_orderby = 0;
            $this->at_rpat_orderby = 0;
        }

        // Reset occupancy at start of each route to counter drift
        if ( $this->route_status["action"] == 201  // CMNO_STARTROUTE
            || $this->route_status["action"] == 204  ) // CMNO_STARTROUTE
        {
            $this->prev_occupancy = 1;
            $this->occupancy = 1;
            $this->at_rpat_orderby = 0;
        }

        if ( $this->route_status["action"] == 201 // started route
            || $this->route_status["action"] == 205 // arrived early or late
            || $this->route_status["action"] == 206 // arrived
            || $this->route_status["action"] == 232 // arrived jumped
            )
        {
            $this->pre_arr_in = $this->aggregate_in;
            $this->pre_arr_out = $this->aggregate_out;
            $this->aggregate_in = 0;
            $this->aggregate_out = 0;

//          echo "DEBUG ARRIVED $[""]. missed ", $this->pre_arr_in, " IN and ", $this->pre_arr_out, " OUT";
            $this->at_rpat_orderby = $this->route_status["locationCode"];

            if ( $this->at_rpat_orderby != $this->last_rpat_orderby && $this->last_rpat_orderby != 0 )
            {
//              echo "DEBUG Arrived at ", $this->at_rpat_orderby, " without departing ", $this->last_rpat_orderby
                $this->last_rpat_orderby = $this->at_rpat_orderby;
                $this->at_rpat_orderby = $this->update_location;
            }
        }
        //if ( $this->route_status_packet )
            //echo "Act : ".$this->route_status["action"]." ".$this->route_status["locationCode"]." occ = $this->occupancy \n";

    }

    function process()
    {
        $l_count_time = false;
        $l_count_date = false;
        $l_char = "";
        $l_hour_int = 0;
        $l_min_int = 0;

        // Store message values in variables
        $in = $this->content["in"];
        $out = $this->content["out"];

        // Extract time fields
        $l_count_time = new DateTime();
        $l_count_time->setTimestamp($this->content["messageTime"]);
        $l_hour_int = $l_count_time->format("H");
        $l_min_int = $l_count_time->format("i");
        $l_timestamp = $l_count_time->format("Y-m-d H:i:s");
        $l_date_id = $l_count_time->format("Ymd");
        $l_time_id = $l_count_time->format("His");

        // Extract GIS id
        $gisarr = array();
        $hash = false;
        $this->timestamp = false;
        latlong_packet_to_decimal( $this->content, $this->latitude, $this->longitude, $this->timestamp );

        $this->gisid = $this->odsconnector->processGeoItem($this->latitude, $this->longitude, $hash, $gisarr);

        // If not on route then store count packet in the database
        // otherwise tie it up with the trip
        if ( !$this->route_status_packet || !$this->route_status_packet->timetableVisits || $this->route_status_packet->currentLocation < 0)
        {
            $sql = " INSERT INTO people_count_fact (
                            fact_id, sourcefile, vehicle_id, driver_id,
                            journey_fact_id, visit_fact_id, gis_id, location_id, date_id,
                            time_id, timestamp, latitude, longitude, gpsAge,
                            speedKPH, bearing, in_count, out_count, total_in,
                            total_out, occupancy   ) VALUES (
                            0, '$this->sourcefile', $this->vehicle_id, NULL,
                            NULL, NULL, $this->gisid, NULL, $l_date_id,
                            $l_time_id, '$l_timestamp', $this->latitude, $this->longitude, 0, 0, 
                            0, $in, $out, $in, $out, NULL
                        ) ";
              if ( !$ret = $this->odsconnector->executeSQL($sql) )
              {  
                   echo "Error in actual journey creation $this->vehicle_code, $l_timestamp\n";
                   return -1;
               }
  
            return;
        }

        $this->route_status = $this->route_status_packet->content;
        $loc = $this->route_status_packet->currentLocation;

        // Increment at stop counts
        $this->aggregate_in = $this->aggregate_in + $this->content["in"];
        $this->aggregate_out = $this->aggregate_out + $this->content["out"];
        $this->occupancy = $this->occupancy + $this->content["in"];
        $this->occupancy = $this->occupancy - $this->content["out"];
//echo "$loc occ => $this->occupancy ".$this->route_status_packet->currentLocation."\n";
        if ( $this->occupancy < 0 )
            $this->occupancy = 0;

        if ( $this->route_status_packet->timetableVisits && $this->route_status_packet->currentLocation > -1 )
        {
                $loc = $this->route_status_packet->currentLocation;
                if ( !isset ( $this->route_status_packet->timetableVisits[$loc]["passengers"] ))
                    $this->route_status_packet->timetableVisits[$loc]["passengers"] = array();

                $this->route_status_packet->timetableVisits[$loc]["passengers"][] = array();
                $ct = count($this->route_status_packet->timetableVisits[$loc]["passengers"]) - 1;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["timestamp"] = $l_count_time;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["in"] = $in;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["out"] = $out;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["agg_in"] = $this->aggregate_in;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["agg_out"] = $this->aggregate_out;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["pre_in"] = $this->pre_arr_in;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["pre_out"] = $this->pre_arr_out;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["occupancy"] = $this->occupancy;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["prev_occupancy"] = $this->prev_occupancy;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["gisid"] = $this->gisid;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["latitude"] = $this->latitude;
                $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["longitude"] = $this->longitude;
                if ( $this->route_status["action"] == 212 )
                    $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["betweenstops"] = true;
                else
                    $this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["betweenstops"] = false;
                /*echo "$loc Count ". $l_count_time->format("Y-m-d"). " v:". $this->vehicle_id.
                        " o:". $this->update_location.
                        " pai:". $this->pre_arr_in.
                        " pao:". $this->pre_arr_out.
                        " in:". $this->aggregate_in.
                        " out:". $this->aggregate_out.
                        " occ:". $this->occupancy.
                        " pocc:". $this->prev_occupancy.
                        " between?: ".$this->route_status_packet->timetableVisits[$loc]["passengers"][$ct]["betweenstops"].
                        "\n";*/

        }

    }
    
}
?>
