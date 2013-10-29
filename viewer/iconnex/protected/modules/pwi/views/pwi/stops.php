<?php

set_include_path("../../../lib");
require_once("common.php");

global $conn;

date_default_timezone_set("Europe/London");

function markers($route)
{
    $rid = false;

    // Open a connection to the database.
    if (!$conn = db_connect())
    {
        echo "Failed to connect to database\n";
        die;
    }

    $sql = "select unique operator_code, route_code, location_code, naptan_code, location.description, latitude, longitude, bearing"
        . " from location, service_patt, service, route, operator, stop"
        . " where service_patt.service_id = service.service_id"
        . " and service.route_id = route.route_id"
        . " and operator.operator_id = route.operator_id"
        . " and location.location_id = service_patt.location_id"
        . " and (stop.atco_code = location.location_code)"
//		. "     or stop.atco_code = '1980' || location.location_code)"
        . " and today between wef_date and wet_date"
        . " and route.route_code = \"" . $route . "\"";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    $minLat = NULL;
    $maxLat = NULL;
    $minLong = NULL;
    $maxLong = NULL;

    echo "<view>\n";
    echo "<stops>\n";
    while (true)
    {
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        if ($minLat == NULL) $minLat = $row["latitude"];
        if ($maxLat == NULL) $maxLat = $row["latitude"];
        if ($minLong == NULL) $minLong = $row["longitude"];
        if ($maxLong == NULL) $maxLong = $row["longitude"];
        $minLat = min($row["latitude"], $minLat);
        $maxLat = max($row["latitude"], $maxLat);
        $minLong = min($row["longitude"], $minLong);
        $maxLong = max($row["longitude"], $maxLong);

        $name = trim($row["description"]) . " " . trim($row["bearing"]);
        $atco = trim($row["location_code"]);
        $naptan = trim($row["naptan_code"]);
        echo "<stop>
  <common_name>$name</common_name>
  <atco_code>$atco</atco_code>
  <naptan_code>$naptan</naptan_code>
  <latitude>" . $row["latitude"] . "</latitude>
  <longitude>" . $row["longitude"] . "</longitude>
  <bearing>" . $row["bearing"] . "</bearing>
</stop>\n";
    }
    echo "</stops>\n";

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
//        $centreLat = 51.455041;
//        $centreLong = -0.969088;
		// Southampton
        $centreLat = 50.904966;
        $centreLong = -1.40323;
        $zoom = 12;
    }
    echo "<parameters>
  <latitude>$centreLat</latitude>
  <longitude>$centreLong</longitude>
  <zoom>$zoom</zoom>
</parameters>
</view>\n";
}

// Main
    $route = $_GET["rt"];

    if ($route == NULL or $route == "")
    {
//        echo "<html>Usage: webint.php?r=[route_code]</html>";
//        exit;
        $route = "17";
    }

    header('Content-Type: text/xml; charset=utf-8');
    markers($route);
?>

