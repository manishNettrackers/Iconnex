#!/usr/local/bin/php
<?php

require_once('/usr/local/lib/phpcoord/phpcoord-2.3.php');
require_once('../../lib/classes/iconnex.class.php');

function error()
{
    $xml = "<Error>404 Not Found</Error>";
    $len = strlen($xml);
    header("HTTP/1.0 404 Not Found", true);
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-Type: text/xml');
    header("Content-Length: $len");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Disposition: inline; filename=vms.xml');
    echo $xml;
    die;
}

$url = "http://64.5.1.40/RBC/businessandpartners/syndication/feed.aspx?email=rbc.ods@connexionzuk.com&feedId=30";

$c = curl_init();
curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_HEADER, 0);
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
$xml = curl_exec($c);
curl_close($c);

if ($xml === false || strlen($xml) <= 0)
    error();

try {
    $parsed_xml = new SimpleXMLElement($xml);
    foreach ($parsed_xml->RoadTrafficDisruptions->RoadEventList->RoadEvent as $roadevent)
    foreach ($roadevent->DisplayLocation as $displaylocation)
    foreach ($displaylocation->UKOSPoint as $ukospoint)
    {
        $os = new OSRef($ukospoint->Easting, $ukospoint->Northing);
        $ll = $os->toLatLng();
        $ll->OSGB36ToWGS84();
        $displaylocation->addChild('Latitude', $ll->lat);
        $displaylocation->addChild('Longitude', $ll->lng);
    }

    $xml = $parsed_xml->asXML();
    $len = strlen($xml);
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-Type: text/xml');
    header("Content-Length: $len");
    echo $xml;
}
catch (Exception $e)
{
    error();
}

?>

