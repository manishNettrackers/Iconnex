<?php
$this->breadcrumbs=array(
	'Webstop',
);?>


<?php

set_include_path("../../../lib");

require_once("commondb.php");
include "webstop.php";

global $conn;

date_default_timezone_set("Europe/London");


if (webstop())
{
    webstop_ajax();
}

function webstop_ajax()
{
    global $wr_countdowns;

    echo '

            <div id="depinfo">
';

    echo "<p style='margin:0; text-align:center;'><strong>".$wr_countdowns["location"]["description"]." - ".$wr_countdowns["location"]["naptan"]."</strong></p>";

    echo "<table border=0 width=\"100%\" style=\"font-size:small;\">";
    if (count($wr_countdowns["arrivals"]) > 0)
        echo "<TR><TH align=left>Route</TH><TH align=left>Destination</TH><TH align=right>Due</TH><TH align=right>Vehicle</TH></TR>";

    $ct = 0;
    foreach ( $wr_countdowns["arrivals"] as $k => $v )
    {
        $route = $v["service_description"];
        $duein = $v["eta"];
        $pub = $v["pub"];
        $vehcd = $v["vehicle_code"];

        if ($duein != "D")
        {
            if ($duein == "P" || $vehcd == "AUT")
            {
                $duein = substr($pub, 0, 2).":".substr($pub, 2, 2);
                $mins = 1;
            }
            else
                $mins = preg_replace("/m.*/", "", $duein);

            if ($mins > 59)
                continue;

            echo "<TR><TD align=left>$route</TD><TD>" . $v["destination1"] . "</TD><TD ALIGN=right>$duein</TD><TD ALIGN=right>$vehcd</TD></TR>";
            $ct++;
        }

    }

    if ($ct == 0)
    {
        echo "<TR><TD COLSPAN=3 ALIGN=center style=\"font-size:large\"><br>Please refer to timetable</TD></TR>";
    }

    echo "</TABLE>";
    echo '<hr style="color:#CECECE;background-color:#CECECE;height:1px;border:none;">';
    //messages("html");
    echo '          </div>';
    echo '          <div class="panefooter">';
    $now = date("H:i:s");
    echo '              <div id="dstatus" class="stattxt" style="display:block">Last refreshed '.$now.'</div>';
    echo '          </div>
';
}

?>

