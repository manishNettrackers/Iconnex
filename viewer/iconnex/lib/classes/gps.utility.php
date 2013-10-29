<?php

/*
 * Functions for manipulating GPS data etc
 * TODO These should probably be moved into GPSPosition.class.php !!!
 */

/*
** Calculates distance between two decimal gps values
*/
function metres_between_coords($lat1, $long1, $lat2, $long2)
{
    $r = 6378; // radius of earth in km

    $a1 = $lat1 * (M_PI / 180);
    $b1 = $long1 * (M_PI / 180);
    $a2 = $lat2 * (M_PI / 180);
    $b2 = $long2 * (M_PI / 180);

    if ($a1 == $a2 && $b1 == $b2 )
        return 0;

    $dist = acos(cos($a1)*cos($b1)*cos($a2)*cos($b2) + cos($a1)*sin($b1)*cos($a2)*sin($b2) + sin($a1)*sin($a2)) * $r;
    return ($dist * 1000); // *1000 to convert from km to m
}

/*
** Converts GPS specified in separate degrees, minutes, seconds value to 2 decimal coordinates
** this provided format is what is found in iconnex gps heartbeat messages
*/
function latlong_packet_to_decimal( $msgArray, &$latitude, &$longitude, &$timestamp )
{
                // -ve gps_lat_degrees indicates "V" invalid GPS reading
                if ($msgArray["gps_lat_degrees"] <= 0)
                    $gpsage = "-1";
                else 
                    $gpsage = "NULL";

                // -ve gps_lat_minutes indicates southern latitude
                if ($msgArray["gps_lat_degrees"] < 0)
                    $latitude = -$msgArray["gps_lat_degrees"];
                else
                    $latitude = $msgArray["gps_lat_degrees"];

                if ($msgArray["gps_lat_minutes"] < 0)
                    $latitude += (((-$msgArray["gps_lat_minutes"]) + ($msgArray["gps_lat_seconds"] / 1000 / 60)) / 60);
                else
                    $latitude += (($msgArray["gps_lat_minutes"] + ($msgArray["gps_lat_seconds"] / 1000 / 60)) / 60);

                // -ve gps_long_minutes indicates eastern longitude
                if ($msgArray["gps_long_minutes"] < 0)
                    $longitude = $msgArray["gps_long_degrees"]
                        + (((-$msgArray["gps_long_minutes"]) + ($msgArray["gps_long_seconds"] / 1000 / 60)) / 60);
                else
                    $longitude = -($msgArray["gps_long_degrees"]
                        + (($msgArray["gps_long_minutes"] + ($msgArray["gps_long_seconds"] / 1000 / 60)) / 60));

                if ($latitude == 0 && $longitude == 0)
                {
                    $gpsage = "-1";
                }

                $timestamp = date("Y-m-d H:i:s", $msgArray["messageTime"]);
}

function subtendedAngle ($LatInDegPoint1, $LonInDegPoint1, $LatInDegPoint2, $LonInDegPoint2, $LatInDegPoint3, $LonInDegPoint3)
{   
    $correctedLatitude = 0;
    $correctedLongitude = 0;
    $correctLatitudeAdd = false;
    $correctLongitudeAdd = false;
    $correctLength = false;
    $prev2Lat = false;
    $prev2Long = false;
    $prev1Lat = false;
    $prev1Long = false;
    $thisLat = false;
    $thisLong = false;
    $first2mid = false;
    $mid2last = false;
    $first2last = false;
    $predActualAngle = false;
    $cosAngle = false;
    $angleRads = false;
    $a = false;
    $b = false;
    $c = false;
    $d = false;

    $first2mid  = metres_between_coords($LatInDegPoint1, $LonInDegPoint1, $LatInDegPoint2, $LonInDegPoint2);
    $mid2last = metres_between_coords($LatInDegPoint2, $LonInDegPoint2, $LatInDegPoint3, $LonInDegPoint3);
    $first2last = metres_between_coords($LatInDegPoint1, $LonInDegPoint1, $LatInDegPoint3, $LonInDegPoint3);

    // Calculate angle using cos rule
    if ($first2last >= $mid2last + $first2mid - 0.1)
        return 180.0;
    else
        $cosAngle = ((($first2mid * $first2mid) + ($mid2last * $mid2last) - ($first2last * $first2last))
                    / (2 * $first2mid * $mid2last));

    $angleRad = acos($cosAngle) * 57.2957;

    return $angleRad;
}

function relativeposition($LatInDegPoint1, $LonInDegPoint1, $LatInDegPoint2, $LonInDegPoint2, $LatInDegPoint3, $LonInDegPoint3)
{
    $prev2Lat = false;
    $prev2Long = false;
    $prev1Lat = false;
    $prev1Long = false;
    $thisLat = false;
    $thisLong = false;
    $first2mid = false;
    $mid2last = false;
    $first2last = false;
    $predActualAngle = false;
    $cosAngle = false;
    $angleRads = false;
    $a = false;
    $b = false;
    $c = false;
    $d = false;
    $comparisondist = false;
    $mincompdistance = 0.000899928005;

    $s1s2latmid = $LatInDegPoint1 + (($LatInDegPoint3 - $LatInDegPoint1) / 2);
    $s1s2lngmid = $LonInDegPoint1 + (($LonInDegPoint3 - $LonInDegPoint1) / 2);

    // If the lats barely differ or long barely differ then separate them artifically
    // by 100 metres (0.000899928005 degrees) and arbitrary amount so that a fairly close
    // vehicle is not ruled out relative to the significant closeness of the data
    // too stringent on how close the
    $comparisondist = abs($LatInDegPoint3 - $LatInDegPoint1);
    if ($comparisondist < $mincompdistance)
        $comparisondist = $mincompdistance;

    $latrelpos = abs(($LatInDegPoint2 - $s1s2latmid) / ($comparisondist));

    $comparisondist = abs($LonInDegPoint3 - $LonInDegPoint1);
    if ($comparisondist < $mincompdistance)
        $comparisondist = $mincompdistance;

    $longrelpos = abs(($LonInDegPoint2 - $s1s2lngmid) / ($comparisondist));
    $relpos = ($latrelpos + $longrelpos) / 2;

    return $relpos;
}

?>
