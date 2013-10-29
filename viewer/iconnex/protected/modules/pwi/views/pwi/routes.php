<?php
set_include_path("../../../lib");
require_once("common.php");
global $conn;

function routes($r)
{
    $rid = false;

    if (!$conn = db_connect()) {
        echo "Failed to connect to database\n";
        die;
    }

   $sql = "select unique route.route_code"
        . " from route, service"
        . " where service.route_id = route.route_id"
        . " and today between wef_date and wet_date";

    if ($r != "")
        $sql .= " and route.route_code matches \"" . $r . "\";";

    if (!($rid = executePDOQuery($sql, $conn))) {   
        echo "Failed to executePDOQuery() for " . $sql . "\n";
        die;
    }

    while (true) {   
        if (!$row = fetchPDO($rid, "NEXT"))
            break;
        echo "<div class='rresult'>";
        echo "<a href=\"javascript:switchRoute('" . trim($row["route_code"]) . "')\";>" . trim($row["route_code"]) .  "</a><br>\n";
        echo "</div>";
    }
}

$r = $_GET["r"];

if ($r == NULL or $r == "") {
    $r = "";
}

header('Content-Type: text/xml; charset=utf-8');
routes($r);
?>
