<?php
set_include_path("/opt/ods/lib/classes");
require_once("iconnex.class.php");

function service_patterns($route_code)
{
$iconnex = new iconnex();

if (!$iconnex->connect("informix:host=10.9.1.254;server=centlive_tcp;protocol=onsoctcp;database=centurion;", "dbmaster", "read109!!"))
{
    return false;
}

$sql = "SELECT route_code, location.location_code, display_order display_order, direction direction
FROM route, route_pattern, location, operator
WHERE 1 = 1
AND route.route_id = route_pattern.route_id
AND route_pattern.location_id = location.location_id
AND route.operator_id = operator.operator_id
AND route.route_id in (SELECT UNIQUE route_id FROM service WHERE 1 = 1 AND wef_date < CURRENT and wet_date > CURRENT)
";
if ($route_code)
{
    if (strpos($route_code , ","))
    {
        $routes = explode(",", $route_code);
        $sql .= "AND route.route_code in (";
        $ct = 0;
        foreach ($routes as $route)
        {
            if ($ct++ > 0)
                $sql .= ",";

            $sql .= "\"$route\"";
        }
        $sql .= ")";
    }
    else
        $sql .= "AND route.route_code = \"$route_code\"";
}
$sql .= "ORDER BY route_code, direction, display_order";

header('Content-Type: text/xml');

$stat = $iconnex->executeSQL($sql);
if (!$stat)
{
    echo "<Error>404 Not Found</Error>";
    die;
}

$t = microtime(true);
$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
$datetime = new DateTime(date('Y-m-d H:i:s.'.$micro, $t));
$canonical = substr($datetime->format("Y-m-d H:i:s.u"), 0, -3);
$timestamp = substr($datetime->format("D d M Y H:i:s.u"), 0, -3);

$xml = "<Root>";
$xml .= "<Header>";
$xml .= "<Identifier>servicepatterns</Identifier>";
$xml .= "<DisplayTitle>Service Patterns</DisplayTitle>";
$xml .= "<PublishDateTime canonical=\"$canonical\">$timestamp</PublishDateTime>";
$xml .= "<Author>api@connexionzuk.com</Author>";
$xml .= "<Owner>Reading Borough Council</Owner>";
$xml .= "<RefreshRate>604800</RefreshRate>";
$xml .= "<Max_Latency>86400</Max_Latency>";
$xml .= "<TimeToError>1209600</TimeToError>";
$xml .= "<Schedule>Once a week</Schedule>";
$xml .= "<OverrideMessage>Due to unforeseen circumstances, this feed is currently not publishable.</OverrideMessage>";
$xml .= "<ErrorMessage>Due to unforeseen circumstances, this feed is currently not available.</ErrorMessage>";
$xml .= "<FeedInfo/>";
$xml .= "<Attribution>";
$xml .= "<Url>http://www.reading-travelinfo.co.uk/</Url>";
$xml .= "<Text>(c) RBC</Text>";
$xml .= "<Logo>http://www.reading-travelinfo.co.uk/images/CompanyLogos/rbclogo.gif</Logo>";
$xml .= "</Attribution>";
$xml .= "<Language>EN</Language>";
$xml .= "</Header>";

$xml .= "<ServicePatterns>";

$ct = 0;
while ($row = $iconnex->fetch())
{
    if (!isset($prev_route_code))
    {
        $prev_route_code = $row["ROUTE_CODE"];
        $xml .= "<ServicePattern>";
        $xml .= "<ServiceId>" . trim($row["ROUTE_CODE"]) . "</ServiceId>";
        $xml .= "<Locations>";
        $xml .= "<Location>";
    }
    else
    {
        if ($row["ROUTE_CODE"] != $prev_route_code)
        {
            $xml .= "</Locations>";
            $xml .= "</ServicePattern>";
            $prev_route_code = $row["ROUTE_CODE"];
            $xml .= "<ServicePattern>";
            $xml .= "<ServiceId>" . trim($row["ROUTE_CODE"]) . "</ServiceId>";
            $xml .= "<Locations>";
            $xml .= "<Location>";
        }
        else
            $xml .= "<Location>";
    }

    $xml .= "<Id>" . trim($row["LOCATION_CODE"]) . "</Id>";
    $xml .= "<Direction>" . trim($row["DIRECTION"]) . "</Direction>";
    $xml .= "<DisplayOrder>" . trim($row["DISPLAY_ORDER"]) . "</DisplayOrder>";
    $xml .= "</Location>";

    $ct++;
}

if ($ct > 0)
{
    $xml .= "</Locations>";
    $xml .= "</ServicePattern>";
}
$xml .= "</ServicePatterns>";
$xml .= "</Root>";

echo $xml;
}

?>

