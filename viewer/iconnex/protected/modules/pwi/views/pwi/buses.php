<?php
set_include_path("../../../lib");
require_once("common.php");
global $conn;

date_default_timezone_set("Europe/London");

function onroute($route)
{
    global $conn;
    $rid = false;

    $sql = "select a.*"
        . " from active_rt a, route b, vehicle c"
        . " where a.route_id = b.route_id"
        . " and a.vehicle_id = c.vehicle_id"
        . " and route_code = \"" . $route . "\""
        . " and start_code in ('REAL', 'AUT') into temp t_log1 with no log;";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $sql = "
        select vehicle_id, gpslat_str, gpslong_str, message_time
        from unit_status a, vehicle b
        where a.build_id = b.build_id
        and vehicle_id in (select vehicle_id from t_log1)
        and message_time > current - 8 units minute
        into temp t_vehpos;";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $sql = "
        select a.schedule_id, vehicle_id, min(rpat_orderby)  minord
        from t_log1 a, active_rt_loc b
        where a.schedule_id = b.schedule_id
        and departure_time > current + 20 units second
        group by 1, 2
        into temp t_vehnext;
        ";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $sql = "
        select a.schedule_id, b.rpat_orderby, b.location_id,
        location_code, c.description,
        latitude_degrees, latitude_minutes, latitude_heading,
        longitude_degrees, longitude_minutes, longitude_heading, b.arrival_time
        from t_vehnext a, active_rt_loc b, location c
        where a.schedule_id = b.schedule_id
        and a.minord = b.rpat_orderby
        and b.location_id = c.location_id
        into temp t_locnext;";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $sql = "
        select b.schedule_id, b.trip_no, b.duty_no, b.running_no,
        vehicle_code,
        t_vehpos.gpslat_str, t_vehpos.gpslong_str, t_vehpos.message_time, c.rpat_orderby,
        t_locnext.location_code, t_locnext.description,
        extend(t_locnext.arrival_time, HOUR TO MINUTE) arrival_time,
        b.start_code,
        '-' || longitude_degrees + (longitude_minutes / 60) || ',' ||
        latitude_degrees + ( latitude_minutes / 60) lastlocpos
        from t_log1 a, active_rt b, active_rt_loc c, outer t_vehpos, t_locnext, vehicle
        where a.schedule_id = b.schedule_id
        and b.schedule_id = c.schedule_id
        and b.vehicle_id = vehicle.vehicle_id
        and b.vehicle_id = t_vehpos.vehicle_id
        and c.schedule_id = t_locnext.schedule_id
        and c.rpat_orderby = t_locnext.rpat_orderby
        into temp t_vehstatus;";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $sql = "select * from t_vehstatus order by vehicle_code, start_code DESC;";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        die;
    }

    $minLat = NULL; 
    $maxLat = NULL;
    $minLong = NULL;
    $maxLong = NULL;
    
    echo "<view>\n";
    echo "  <Vehicles>\n";
    while (true)
    {
        if (!($row = fetchPDO($rid, "NEXT")))
            break;

        if ($row < 0) {
            showPDOError($conn);
            die;
        }

        if ($row["gpslat_str"])
        {
            $latresults = array();
            preg_match( "/(.*) (.*) (.*)/", trim($row["gpslat_str"]), $latresults);
            $lat = $latresults[1] + ( $latresults[2] / 60 );

            if ($latresults[3] == "S")
                $lat = "-".$lat;

            $lngresults = array();
            preg_match( "/(.*) (.*) (.*)/", trim($row["gpslong_str"]), $lngresults);
            $lng = ( $lngresults[2] / 60 );
            $lng = $lngresults[1] + ($lngresults[2] / 60);

            if ($lngresults[3] == "W")
                $lng = "-".$lng;

            $lng1 = (double)$lng + 0.0001;

            echo "    <Vehicle>\n";
            echo "      <VehicleCode>" . trim($row["vehicle_code"]) . "</VehicleCode>\n";
            echo "      <Latitude>" . $lat . "</Latitude>\n";
            echo "      <Longitude>" . $lng . "</Longitude>\n";
            echo "      <Board>" . trim($row["running_no"]) . "</Board>\n";
            echo "      <Duty>" . trim($row["duty_no"]) . "</Duty>\n";
            echo "      <Route>" . $route . "</Route>\n";
            echo "      <Trip>" . trim($row["trip_no"]) . "</Trip>\n";
            echo "      <NextAtcoCode>" . trim($row["location_code"]) . "</NextAtcoCode>\n";
            echo "      <NextLocation>" . trim($row["description"]) . "</NextLocation>\n";
            echo "      <NextETA>" . trim($row["arrival_time"]) . "</NextETA>\n";
            echo "    </Vehicle>\n";

            if ($minLat == NULL) $minLat = $lat;
            if ($maxLat == NULL) $maxLat = $lat;
            if ($minLong == NULL) $minLong = $lng;
            if ($maxLong == NULL) $maxLong = $lng;
            $minLat = min($lat, $minLat);
            $maxLat = max($lat, $maxLat);
            $minLong = min($lng, $minLong);
            $maxLong = max($lng, $maxLong);
        }
    }
    echo "  </Vehicles>\n";

    $centreLat = $minLat + ($maxLat - $minLat) / 2;
    $centreLong = $minLong + ($maxLong - $minLong) / 2;

    $miles = (3958.75 * acos(sin($minLat / 57.2958) * sin($maxLat / 57.2958) + cos($minLat / 57.2958) * cos($maxLat / 57.2958) * cos($maxLong / 57.2958 - $minLong / 57.2958)));

    if ($miles < 0.2)
        $zoom = 16;
    else if ($miles < 0.5)
        $zoom = 16;
    else if ($miles < 1)
        $zoom = 15;
    else if ($miles < 2)
        $zoom = 14;
    else if ($miles < 3)
        $zoom = 13;
    else if ($miles < 7)
        $zoom = 12;
    else if ($miles < 15)
        $zoom = 11;
    else
        $zoom = 10;

    if ($centreLat == 0 && $centreLong == 0)
    {   
		// Reading
        //$centreLat = 51.455041;
        //$centreLong = -0.969088;
		//Southampton
        $centreLat = 50.904966;
        $centreLong = -1.40323;
        $zoom = 12;
    }
    echo "  <parameters>
    <latitude>$centreLat</latitude>
    <longitude>$centreLong</longitude>
    <zoom>$zoom</zoom>
  </parameters>
</view>\n";
}

function approaching($location)
{
    global $conn;
    $rid = false;

    $sql = "
select UNIQUE a.*, route_code
from active_rt a, active_rt_loc d, location e, route b, vehicle c
where a.route_id = b.route_id
and a.schedule_id = d.schedule_id
and d.location_id = e.location_id
and a.vehicle_id = c.vehicle_id
and start_code in ('REAL', 'CONT')
into temp t_log1 with no log";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $sql = "
select  vehicle_id, gpslat_str, gpslong_str, message_time
from unit_status a, vehicle b
where a.build_id = b.build_id
and vehicle_id in ( select vehicle_id from t_log1 )
and message_time > current - 2 units minute
into temp t_vehpos;
";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $sql = "
select a.schedule_id, vehicle_id, min(rpat_orderby)  minord
from t_log1 a, active_rt_loc b, location c
where a.schedule_id = b.schedule_id
and departure_time > current - 2 units minute
and c.location_id = b.location_id
and location_code = '".$location."'
group by 1, 2
into temp t_vehnext;
";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $sql = "
select a.schedule_id, b.rpat_orderby, b.location_id,
location_code, description,
latitude_degrees, latitude_minutes, latitude_heading,
longitude_degrees, longitude_minutes, longitude_heading
from t_vehnext a, active_rt_loc b, location c
where a.schedule_id = b.schedule_id
and a.minord = b.rpat_orderby
and b.location_id = c.location_id
into temp t_locnext;
";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $sql = "
select b.schedule_id, b.running_no, b.duty_no, b.trip_no,
vehicle_code,
t_vehpos.gpslat_str, t_vehpos.gpslong_str, message_time, interval(0) minute(9) to minute +  (current - t_vehpos.message_time) || '' time_since, c.rpat_orderby,
t_locnext.location_code, t_locnext.description, b.start_code, 
'-' || longitude_degrees + (longitude_minutes / 60) || ',' ||
latitude_degrees + ( latitude_minutes / 60) lastlocpos, arrival_status, departure_status, extend(arrival_time, HOUR TO SECOND) arrival_time, departure_time, arrival_time - CURRENT || '' eta, a.route_code
from t_log1 a, active_rt b, active_rt_loc c, outer t_vehpos, t_locnext, vehicle
where a.schedule_id = b.schedule_id
and b.schedule_id = c.schedule_id
and b.vehicle_id = vehicle.vehicle_id
and b.vehicle_id = t_vehpos.vehicle_id
and c.schedule_id = t_locnext.schedule_id
and c.rpat_orderby = t_locnext.rpat_orderby
into temp t_vehstatus
";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $sql = "select * from t_vehstatus order by departure_time ASC";

    if (!($rid = executePDOQuery($sql, $conn))) {
        showPDOError($conn);
        die;
    }

    $minLat = NULL; 
    $maxLat = NULL;
    $minLong = NULL;
    $maxLong = NULL;

    $ct = 0;
    echo "<view>\n";
    echo "  <Vehicles>\n";
    while (true)
    {
        if (!($row = fetchPDO($rid, "NEXT")))
            break;

        if ($row < 0) {
            showPDOError($conn);
            die;
        }

        if ($row["gpslat_str"])
        {
            $latresults = array();
            preg_match( "/(.*) (.*) (.*)/", trim($row["gpslat_str"]), $latresults);
            $lat = $latresults[1] + ($latresults[2] / 60);
            if ($latresults[3] == "S") $lat = "-".$lat;
            $lngresults = array();
            preg_match("/(.*) (.*) (.*)/", trim($row["gpslong_str"]), $lngresults);
            $lng = ($lngresults[2] / 60);
            $lng = $lngresults[1] + ($lngresults[2] / 60);
            if ($lngresults[3] == "W") $lng = "-".$lng;
            $lng1 = (double)$lng + 0.0001;
        }

        $eta = trim(preg_replace("/0 /", "", $row["eta"]));

//        if (substr($eta, 0, 1) == "-") EARLY

        $ct++;
        echo "    <Vehicle>\n";
        echo "      <VehicleCode>" . trim($row["vehicle_code"]) . "</VehicleCode>\n";
        echo "      <Latitude>" . $lat . "</Latitude>\n";
        echo "      <Longitude>" . $lng . "</Longitude>\n";
        echo "      <Board>" . trim($row["running_no"]) . "</Board>\n";
        echo "      <Duty>" . trim($row["duty_no"]) . "</Duty>\n";
        echo "      <Route>" . trim($row["route_code"]) . "</Route>\n";
        echo "      <Trip>" . trim($row["trip_no"]) . "</Trip>\n";
        echo "      <NextAtcoCode>" . trim($row["location_code"]) . "</NextAtcoCode>\n";
        echo "      <NextLocation>" . trim($row["description"]) . "</NextLocation>\n";
        echo "      <NextETA>" . trim($row["arrival_time"]) . "</NextETA>\n";
        echo "      <Order>" . $ct . "</Order>\n";
        echo "    </Vehicle>\n";

        if ($minLat == NULL) $minLat = $lat;
        if ($maxLat == NULL) $maxLat = $lat;
        if ($minLong == NULL) $minLong = $lng;
        if ($maxLong == NULL) $maxLong = $lng;
        $minLat = min($lat, $minLat);
        $maxLat = max($lat, $maxLat);
        $minLong = min($lng, $minLong);
        $maxLong = max($lng, $maxLong);
    }
    echo "  </Vehicles>\n";

    $centreLat = $minLat + ($maxLat - $minLat) / 2;
    $centreLong = $minLong + ($maxLong - $minLong) / 2;

    $miles = (3958.75 * acos(sin($minLat / 57.2958) * sin($maxLat / 57.2958) + cos($minLat / 57.2958) * cos($maxLat / 57.2958) * cos($maxLong / 57.2958 - $minLong / 57.2958)));

    if ($miles < 0.2)
        $zoom = 16;
    else if ($miles < 0.5)
        $zoom = 16;
    else if ($miles < 1)
        $zoom = 15;
    else if ($miles < 2)
        $zoom = 14;
    else if ($miles < 3)
        $zoom = 13;
    else if ($miles < 7)
        $zoom = 12;
    else if ($miles < 15)
        $zoom = 11;
    else
        $zoom = 10;

    if ($centreLat == 0 && $centreLong == 0)
    {
        $centreLat = 51.455041;
        $centreLong = -0.969088;
        $zoom = 12;
    }
    echo "  <parameters>
    <latitude>$centreLat</latitude>
    <longitude>$centreLong</longitude>
    <zoom>$zoom</zoom>
  </parameters>
</view>\n";

}

header('Content-Type: text/xml; charset=utf-8');

if (!$conn = db_connect()) {
    echo "Failed to connect to database\n";
    die;
}

$route = NULL;
$stop = NULL;
if (isset($_GET['rt']) && $_GET['rt'] != "")
    $route = $_GET["rt"];
if (isset($_GET['s']) && $_GET['s'] != "")
    $stop = $_GET["s"];
if ($route)
    onroute($route);
else if ($stop)
    approaching($stop);
else
    echo "Usage: supply either rt=[route_code] or s=[atco_code]";
?>
