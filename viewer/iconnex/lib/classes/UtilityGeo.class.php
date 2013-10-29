<?php

class UtilityGeo extends Utility
{
    public $connector = false;

    static function bearingDegreesFrom2LatLong($lat2, $lon2, $lat1, $lon1)
    {
        $longdiff    = $lon2 - $lon1;
        $latdiff     = $lat2 - $lat1;

        if ( $latdiff == $longdiff )
            return 0;

        $paz         = (M_PI * 0.5) - atan($latdiff / $longdiff);

        if ($longdiff > 0) 
            return round ( ( $paz * 180 ) / M_PI, 0);
        else if ($longdiff < 0) 
        {
            return round ( ( ( $paz + M_PI ) * 180 / M_PI ), 0);
        }
        else if ($latdiff < 0) 
            return round ( ( M_PI * 180 ) / M_PI , 0 );

        return 0;

    }

    function distanceBetweenPoints($lat1, $long1, $lat2, $long2)
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

}

?>
