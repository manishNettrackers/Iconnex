<?php

class GPSPosition
{
    const MILLIARCSECSPERDEGREE = 3600000;

    public $latitude_milliarcsecs;
    public $longitude_milliarcsecs;
    public $latitude; // latitude in decimal degrees
    public $longitude; // longitude in decimal degrees
    public $latdeg; // whole degrees
    public $latmin; // decimal minutes
    public $lngdeg; // whole degrees
    public $lngmin; // decimal minutes
    public $northsouth;
    public $eastwest;
    public $plottableString;
    public $gps_time;
    public $gpslat_str;
    public $gpslong_str;

    function initialiseWithMilliarcsecs($latmas, $longmas)
    {
        if ($latmas <= 0)
        {
            $this->latitude = 0 - ($latmas / 3600000);
            $this->latdeg = 0 - ceil($latmas / 3600000);
            $this->latmin = 0 - ((($latmas / 3600000) + $this->latdeg) * 60);
            $this->northsouth = 'S';
        }
        else
        {
            $this->latitude = $latmas / 3600000;
            $this->latdeg = floor($latmas / 3600000);
            $this->latmin = (($latmas / 3600000) - $this->latdeg) * 60;
            $this->northsouth = 'N';
        }

        if ($longmas <= 0)
        {
            $this->longitude = 0 - ($longmas / 3600000);
            $this->lngdeg = 0 - ceil($longmas / 3600000);
            $this->lngmin = 0 - ((($longmas / 3600000) + $this->lngdeg) * 60);
            $this->eastwest = 'W';
        }
        else
        {
            $this->longitude = $longmas / 3600000;
            $this->lngdeg = floor($longmas / 3600000);
            $this->lngmin = (($longmas / 3600000) - $this->lngdeg) * 60;
            $this->eastwest = 'E';
        }

        $this->latitude_milliarcsecs = $latmas;
        $this->longitude_milliarcsecs = $longmas;
        $this->gpslat_str = "".$this->latdeg." ".$this->latmin." ".$this->northsouth;
        $this->gpslong_str = "".$this->lngdeg." ".$this->lngmin." ".$this->eastwest;
        $this->plottableString = "".$this->latdeg." ".$this->latmin." ".$this->northsouth.", ".$this->lngdeg." ".$this->lngmin." ".$this->eastwest;

        return true;
    }

    function initialiseWithLatLong($latitude, $longitude)
    {
        return $this->initialiseWithMilliarcsecs($latitude * GPSPosition::MILLIARCSECSPERDEGREE, $longitude * GPSPosition::MILLIARCSECSPERDEGREE);
    }

    function getMidPoint($secondpos)
    {
        $midlat = $this->latitude_milliarcsecs / 2 + $secondpos->latitude_milliarcsecs / 2;
        $midlng = $this->longitude_milliarcsecs / 2 + $secondpos->longitude_milliarcsecs / 2;
        $midpos = new GPSPosition();
        $midpos->initialiseWithMilliarcsecs($midlat, $midlng);
        return $midpos;
    }

    function withinGeofence($test_position, $geofence)
    {
        if (!$test_position)
        {
            echo "GPSPosition->withinGeofence() test_position is not set\n";
            return false;
        }

        $dist = metres_between_coords(
            $test_position->latitude, $test_position->longitude,
            $this->gps_position->latitude, $this->gps_position->longitude);

        if ($dist < $geofence)
        {
//            echo "GPSPosition->withinGeofence() test_position is $dist metres from this position\n";
            return true;
        }

        return false;
    }
}
?>
