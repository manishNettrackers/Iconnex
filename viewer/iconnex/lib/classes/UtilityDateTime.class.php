<?php

class UtilityDateTime extends Utility
{
    public $connector = false;

    static function HHMMSSToDateInterval($intime)
    {
//        echo "UtilityDateTime->HHMMSSToDateInterval() for $intime\n";
        $arr = explode(":", $intime);
        switch (count($arr))
        {
            case 3:
                $interval = "PT".trim($arr[0])."H".$arr[1]."M".$arr[2]."S"; 
                break;
            case 2:
                $interval = "PT".$arr[0]."M".$arr[1]."S"; 
                break;
            case 1:
                if (strlen($arr[0]) < 1)
                    $interval = "PT0S";
                else
                    $interval = "PT".$arr[0]."S"; 
                break;
            default:
                $interval = "PT0S";
                break;
        }

//        echo "UtilityDateTime->HHMMSSToDateInterval() result $interval\n";
        return $interval;
    }

    static function sumIntervals($interval1, $interval2)
    {
        $d1 = new DateTime('00:00');
        $d2 = clone $d1;
        $d2->add($interval1);
        $d2->add($interval2);
        return $d2->diff($d1);
    }

    static function getDateIntervalInSeconds($interval)
    {
//        echo "UtilityDateTime->getDateIntervalInSeconds() for " . print_r($interval, true) . "\n";
        $seconds = $interval->h * 3600
            + $interval->i * 60
            + $interval->s;
//        if (isset($interval->days)) $seconds += $interval->days * 86400;
//        echo "UtilityDateTime->getDateIntervalInSeconds() result $seconds\n";
        return $seconds;
    }

    static function currentTime()
    {
        $now = new DateTime();
        return $now->format("Y-m-d H:i:s");
    }

    static function currentTimestamp()
    {
        $now = new DateTime();
        return $now->getTimestamp();
    }

    static function dateExtract($datestring, $extract)
    {
        $retval = $datestring;

        switch ( $extract )
        {
            case "hour to second":
                $retval = substr($datestring, 11);

            default:
                $retval = $datestring;
        }

        return $retval;
    }

    static function SecondsToIntervalString($secs)
    {
        $str = "PT".$secs."S"; 
        $int = new DateInterval($str);
        $int->format("H:i:s");
    }

    static function IntervalStringToSeconds($interval)
    {
        return strtotime("0000-00-00 $time")-strtotime("0000-00-00 00:00:00");
    }

    /*
    * Converts a timestamp as string, datetime object to a string
    */
    static function TimestampObjectToString($timestamp)
    {
        if ( is_string($timestamp) )
            return $timestamp;

        if ( gettype($timestamp) == "object" )
        {
            return $timestamp->format("Y-m-d H:i:s");
        }
    }
}

?>
