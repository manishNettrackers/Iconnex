<?php

$golap = "'Type=Icon,".
         "HotspotX=15,".
         "HotspotY=20,".
         "Filters=Vehicle Code;Hour No;Addr Road;Addr Suburb,".
         "ClickLink=http://iconnex.info/ods/welcome.html,".
         "PosColor=#ff0000,".
         "Negcolor=#0000ff,".
         "ZeroColor=#00ff00,".
         "MetricRangeLower=0,".
         "MetricRangeUpper=50,".
         "PlotSize=30,".
         "RenderType=CountEvent,".
         "RenderElements=Vehicle Code;In Count;Out Count;Occupancy'";
echo $golap;
$this->add_assignment("golap", $golap, false );

?>
