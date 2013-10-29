<?php

set_include_path("../../../lib");
require_once("common.php");
require_once("commondb.php");
global $conn;

/**
 * @brief parse a string into a valid post code to use as a search key
 *
 * UK post codes begin with either one letter or two, followed by either one
 * numerical digit or two, then a space, then a digit followed by two letters.
 * The CodePoint data does not include the space by default, but uses spaces
 * where the segment before the space is shorter than it can be.
 * eg. "RG301BX", "RG1 1AF", "S1  1AA"
 */
function parsePostCode($q)
{
    // Check that the string is at least five characters eg. "S11AA"
    if (strlen($q) < 5) {
        return "";
    }

    // Check that the first character is alpha
    if (!ctype_alpha(substr($q, 0, 1))) {
        return "";
    }

    // Second character can be alpha or numeric
    if (ctype_alpha(substr($q, 1, 1)) == true) {
        // Had 2 letters => 3rd character must be numeric
        if (ctype_digit(substr($q, 2, 1)) == false) {
            return "";
        }

        // 4th must be digit, but could be end of first segment
        // or start of second
        if (ctype_digit(substr($q, 3, 1)) == false) {
            return "";
        }

        if (ctype_alpha(substr($q, 4, 1))) {
            // if 5th is alpha, then 4th was start of last segment
            // so last one must be alpha
            if (!ctype_alpha(substr($q, 5, 1))) {
                return "";
            }

            $cq = substr($q, 0, 3) . " " . substr($q, 3, 3);
        }
        else if (ctype_digit(substr($q, 4, 1))) {
            // if 5th is numeric, then 4th was last digit of first segment
            // so last two must be alpha
            if (ctype_alpha(substr($q, 5, 2)) == false)
            {
                return "";
            }
            $cq = substr($q, 0, 4) . substr($q, 4, 3);
        }
    }
    else if (ctype_digit(substr($q, 1, 1)) == true) {
        // 3rd must be digit, but could be end of first segment
        // or start of second
        if (ctype_digit(substr($q, 2, 1)) == false)
            return "";

        if (ctype_alpha(substr($q, 3, 1)) == true) {   
            // if 4th is alpha, then 3rd was start of last segment
            // so last one must be alpha
            if (ctype_alpha(substr($q, 4, 1)) == false)
                return "";

            $cq = substr($q, 0, 2) . "  " . substr($q, 2, 3);
        }
        else if (ctype_digit(substr($q, 3, 1)) == true) {
            // if 4th is numeric, then 3rd was last digit of first segment
            // so last two must be alpha
            if (ctype_alpha(substr($q, 4, 2)) == false)
                return "";

            $cq = substr($q, 0, 3) . " " . substr($q, 3, 3);
        }
    } else {
        return "";
    }

    return $cq;
}

function search($q)
{
    $rid = false;
    $i = 0;

    if (!$conn = db_connect()) {
        echo "Failed to connect to database\n";
        die;
    }

    // Search by route code
   $sql = "select unique route.route_id"
        . " from route, service"
        . " where service.route_id = route.route_id"
        . " and today between wef_date and wet_date"
        . " and route.route_code = \"" . $q . "\""
        . " into temp t_routes with no log";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

   $sql = "select unique route.route_code, sequence, direction, location.location_code, stop.naptan_code, stop.common_name, stop.latitude, stop.longitude, stop.bearing
        from route_pattern, route, location, t_routes, naptan_stop_point as stop
        where route.route_id = route_pattern.route_id
        and route.route_id = t_routes.route_id
        and location.location_id = route_pattern.location_id
        and (stop.atco_code = location.location_code)"
            // . " OR stop.atco_code = '1980' || location.location_code)"
        . " order by route_code, direction, sequence";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }
    echo "<results>\n";

    $prev_route = "";
    while (true) {   
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        if (trim($row["route_code"]) != $prev_route)
        {
            if ($prev_route != "")
                echo "  </route>\n";

            echo "  <route>\n";
            echo "    <route_code>" . trim($row["route_code"]) . "</route_code>\n";
        }

        $bearing = trim($row["bearing"]);
        $naptan = trim($row["naptan_code"]);
        $name = str_replace("&", "&amp;", trim($row["common_name"]));
        echo "    <call>\n";
        echo "      <direction>" . trim($row["direction"]) . "</direction>\n";
        echo "      <atco_code>" . trim($row["location_code"]) . "</atco_code>\n";
        if (strlen($naptan) <= 0)
            echo "      <naptan_code>...</naptan_code>\n";
        else
            echo "      <naptan_code>" . $naptan . "</naptan_code>\n";
        echo "      <common_name>" . $name . "</common_name>\n";
        echo "      <latitude>" . trim($row["latitude"]) . "</latitude>\n";
        echo "      <longitude>" . trim($row["longitude"]) . "</longitude>\n";
        if (strlen($bearing) > 0)
            echo "      <bearing>" . $bearing . "</bearing>\n";
        echo "    </call>\n";

        $prev_route = trim($row["route_code"]);
        $i++;
    }

    if ($i > 0)
    {
        echo "  </route>\n";
        echo "</results>\n";
        return;
    }
    
    // Search by post code
    $cq = parsePostCode(str_replace(" ", "", $q));
    $cq = strtoupper($cq);
    if ($cq != "")
    {
        $sql = "select post_code, latitude, longitude"
        . " from post_code"
        . " where post_code = \"" . $cq . "\";";

        if (!($rid = executePDOQuery($sql, $conn))) {   
            echo "Failed to executePDOQuery() for " . $sql . "\n";
            die;
        }
        while (true) {   
            if (!$row = fetchPDO($rid, "NEXT"))
                break;
            
            $sql = "select atco_code, naptan_code, common_name, latitude, longitude, bearing"
                . " from naptan_stop_point as stop, location"
                . " where " . ($row["latitude"] - 0.002) . " < latitude and latitude < " . ($row["latitude"] + 0.002)
                . " and " . ($row["longitude"] - 0.002) . " < longitude and longitude < " . ($row["longitude"] + 0.002)
                . " and location.location_code = atco_code"
                . " into temp t_stops with no log";

            if (!($rid2 = executePDOQuery($sql, $conn)))
            {   
                echo "Failed to executePDOQuery() for " . $sql . "\n";
                die;
            }

            $sql = "select * from t_stops";
            if (!($rid2 = executePDOQuery($sql, $conn)))
            {   
                echo "Failed to executePDOQuery() for " . $sql . "\n";
                die;
            }

            while (true)
            {   
                if (!$row2 = fetchPDO($rid2, "NEXT"))
                    break;

                $bearing = trim($row2["bearing"]);
                $naptan = trim($row2["naptan_code"]);
                $name = str_replace("&", "&amp;", trim($row2["common_name"]));
                echo "  <stop>\n";
                echo "    <atco_code>" . trim($row2["atco_code"]) . "</atco_code>\n";
                if (strlen($naptan) <= 0)
                    echo "      <naptan_code>...</naptan_code>\n";
                else
                    echo "    <naptan_code>" . $naptan . "</naptan_code>\n";
                echo "    <common_name>" . $name . "</common_name>\n";
                echo "    <latitude>" . trim($row2["latitude"]) . "</latitude>\n";
                echo "    <longitude>" . trim($row2["longitude"]) . "</longitude>\n";
                if (strlen($bearing) > 0)
                    echo "    <bearing>" . $bearing . "</bearing>\n";
                echo "  </stop>\n";
            }
        }
        $sql = "select unique route.route_id"
             . " from route, service, service_patt, location, t_stops"
             . " where service.route_id = route.route_id"
             . " and today between wef_date and wet_date"
             . " and service_patt.service_id = service.service_id"
             . " and location.location_id = service_patt.location_id"
             . " and location.location_code = t_stops.atco_code"
             . " into temp t_routes2 with no log";

        if (!($rid2 = executePDOQuery($sql, $conn)))
        {   
            echo "Failed to executePDOQuery() for " . $sql . "\n";
            die;
        }

       $sql = "select route.route_code, sequence, direction, location.location_code, stop.naptan_code, stop.common_name, stop.latitude, stop.longitude, stop.bearing
            from route_pattern, route, location, t_routes2, stop
            where route.route_id = route_pattern.route_id
            and route.route_id = t_routes2.route_id
            and location.location_id = route_pattern.location_id
            and stop.atco_code = location.location_code
            order by route_code, direction, sequence";

        if (!($rid2 = executePDOQuery($sql, $conn))) {   
            echo "Failed to executePDOQuery() for " . $sql . "\n";
            die;
        }

        $prev_route = "";
        while (true) {   
            if (!$row2 = fetchPDO($rid2, "NEXT"))
                break;

            if (trim($row2["route_code"]) != $prev_route)
            {
                if ($prev_route != "")
                    echo "  </route>\n";

                echo "  <route>\n";
                echo "    <route_code>" . trim($row2["route_code"]) . "</route_code>\n";
            }

            $bearing = trim($row2["bearing"]);
            $naptan = trim($row2["naptan_code"]);
            $name = str_replace("&", "&amp;", trim($row2["common_name"]));
            echo "    <call>\n";
            echo "      <direction>" . trim($row2["direction"]) . "</direction>\n";
            echo "      <atco_code>" . trim($row2["location_code"]) . "</atco_code>\n";
            if (strlen($naptan) <= 0)
                echo "      <naptan_code>...</naptan_code>\n";
            else
                echo "      <naptan_code>" . $naptan . "</naptan_code>\n";
            echo "      <common_name>" . $name . "</common_name>\n";
            echo "      <latitude>" . trim($row2["latitude"]) . "</latitude>\n";
            echo "      <longitude>" . trim($row2["longitude"]) . "</longitude>\n";
            if (strlen($bearing) > 0)
                echo "      <bearing>" . $bearing . "</bearing>\n";
            echo "    </call>\n";

            $prev_route = trim($row2["route_code"]);
            $i++;
        }
    }

    if ($i > 0)
    {
        echo "</route>\n";
        echo "</results>\n";
        return;
    }

    // Search by stop common name
    $cq = str_replace(" ", "*", strtoupper($q));

    $sql = "select location_code, description"
    . " from location"
    . " where  1= 1"
    . " and upper(description) matches \"*" . $cq . "*\""
	. " into temp t_location with no log";
    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }
    
    $sql = "select location_code, naptan_code, description, latitude, longitude, bearing"
    . " from t_location, naptan_stop_point as stop"
    . " where ( atco_code = location_code )"
    //. " OR atco_code = '1980' || location_code ) "
    . " and upper(description) matches \"*" . $cq . "*\"";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    while (true) {   
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        $bearing = trim($row["bearing"]);
        $naptan = trim($row["naptan_code"]);
        $name = str_replace("&", "&amp;", trim($row["description"]));
        echo "  <stop>\n";
        echo "    <atco_code>" . trim($row["location_code"]) . "</atco_code>\n";
        if (strlen($naptan) <= 0)
            echo "      <naptan_code>...</naptan_code>\n";
        else
            echo "    <naptan_code>" . $naptan . "</naptan_code>\n";
        echo "    <common_name>" . $name . "</common_name>\n";
        echo "    <latitude>" . trim($row["latitude"]) . "</latitude>\n";
        echo "    <longitude>" . trim($row["longitude"]) . "</longitude>\n";
        if (strlen($bearing) > 0)
            echo "    <bearing>" . $bearing . "</bearing>\n";
        echo "  </stop>\n";
        $i++;
    }

    if ($i > 0)
    {
        echo "</results>\n";
        return;
    }

    // Search by naptan code (exact match only)
    $sql = "select location_code, naptan_code, description, latitude, longitude, naptan_stop_point.bearing"
    . " from location, naptan_stop_point"
    . " where atco_code = location_code"
    . " and upper(naptan_code) = \"" . strtoupper($q) . "\"";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    while (true) {   
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        $bearing = trim($row["bearing"]);
        $naptan = trim($row["naptan_code"]);
        $name = str_replace("&", "&amp;", trim($row["description"]));
        echo "  <stop>\n";
        echo "    <atco_code>" . trim($row["location_code"]) . "</atco_code>\n";
        if (strlen($naptan) <= 0)
            echo "      <naptan_code>...</naptan_code>\n";
        else
            echo "    <naptan_code>" . $naptan . "</naptan_code>\n";
        echo "    <common_name>" . $name . "</common_name>\n";
        echo "    <latitude>" . trim($row["latitude"]) . "</latitude>\n";
        echo "    <longitude>" . trim($row["longitude"]) . "</longitude>\n";
        if (strlen($bearing) > 0)
            echo "    <bearing>" . $bearing . "</bearing>\n";
        echo "  </stop>\n";
        $i++;
    }
    
    if ($i > 0)
    {
        echo "</results>\n";
        return;
    }
    
    // Search by atco code (exact match only)
    $sql = "select location_code, naptan_code, description, latitude, longitude, naptan_stop_point.bearing"
    . " from location, naptan_stop_point"
    . " where atco_code = location_code"
    . " and upper(location_code) = \"" . strtoupper($q) . "\"";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    while (true) {   
        if (!$row = fetchPDO($rid, "NEXT"))
            break;

        $bearing = trim($row["bearing"]);
        $naptan = trim($row["naptan_code"]);
        $name = str_replace("&", "&amp;", trim($row["description"]));
        echo "  <stop>\n";
        echo "    <atco_code>" . trim($row["location_code"]) . "</atco_code>\n";
        if (strlen($naptan) <= 0)
            echo "      <naptan_code>...</naptan_code>\n";
        else
            echo "    <naptan_code>" . $naptan . "</naptan_code>\n";
        echo "    <common_name>" . $name . "</common_name>\n";
        echo "    <latitude>" . trim($row["latitude"]) . "</latitude>\n";
        echo "    <longitude>" . trim($row["longitude"]) . "</longitude>\n";
        if (strlen($bearing) > 0)
            echo "    <bearing>" . $bearing . "</bearing>\n";
        echo "  </stop>\n";
        $i++;
    }

    echo "</results>\n";
}

header('Content-Type: text/xml; charset=utf-8');

$q = $_GET["q"];

if ($q == NULL || $q == ""
|| !ctype_alnum(str_replace(array(0 => " ", 1 => "'"), "", $q)))
    echo "<div style=\"text-align:center;\"><br/>Please enter a <strong>valid</strong>route or post code in the box above and press enter or click the Search button.</div>";
else
{
    search($q);
}
?>
