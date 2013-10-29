<?php
set_include_path("../../../lib");
require_once("common.php");

global $conn;

date_default_timezone_set("Europe/London");

function departures($veh)
{
    $rid = false;

    // Open a connection to the database.
    if (!$conn = db_connect())
    {
        echo "Failed to connect to database\n";
        die;
    }

    $sql = "
        select schedule_id, route_code
        from vehicle a, active_rt b, route c
        where a.vehicle_code = \"" . $veh . "\"
        and b.vehicle_id = a.vehicle_id
        and c.route_id = b.route_id
        and b.start_code = \"REAL\"";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        echo "error die";
        die;
    }

    if (!($row = fetchPDO($rid, "NEXT")))
    {
        echo "Failed to find active route for vehicle\n";
        die;
    }
    $route = trim($row["route_code"]);

    $sql = "
        select vehicle_code, location_code, d.description, bearing,
        extend(arrival_time, HOUR TO SECOND) arrival_time,
        extend(departure_time, HOUR TO SECOND) departure_time
        from vehicle a, active_rt b, active_rt_loc c, location d, stop
        where a.vehicle_code = \"" . $veh . "\"
        and b.vehicle_id = a.vehicle_id
        and c.departure_time > current + 20 units second
        and c.schedule_id = b.schedule_id
        and d.location_id = c.location_id
        and atco_code = location_code
        order by 1, 5";

    if (!($rid = executePDOQuery($sql, $conn)))
    {
        showPDOError($conn);
        echo "error die";
        die;
    }

    echo "<Vehicle>\n";
    echo "  <VehicleCode>" . $veh . "</VehicleCode>\n";
    echo "  <Route>" . $route . "</Route>\n";
    echo "  <MonitoredCalls>\n";
    for ($i = 0; $i < 10; $i++)
    {
        if (!($row = fetchPDO($rid, "NEXT")))
            break;

        if ($row < 0)
        {
            showPDOError($conn);
            echo "error die";
            die;
        }

        if ($row["departure_time"])
        {
            echo "  <MonitoredCall>\n";
            echo "    <AtcoCode>" . trim($row["location_code"]) . "</AtcoCode>\n";
            echo "    <Location>" . trim($row["description"]) . " " . trim($row["bearing"]) . "</Location>\n";
            echo "    <ETA>" . trim($row["arrival_time"]) . "</ETA>\n";
            echo "    <ETD>" . trim($row["departure_time"]) . "</ETD>\n";
            echo "  </MonitoredCall>\n";
        }
    }
    echo "  </MonitoredCalls>\n";
    echo "</Vehicle>";
}

// Main
    $veh = $_GET["v"];

    if ($veh == NULL or $veh == "")
    {
        $veh = "1114";
    }

    header('Content-Type: text/xml; charset=utf-8');
    departures($veh);
?>

