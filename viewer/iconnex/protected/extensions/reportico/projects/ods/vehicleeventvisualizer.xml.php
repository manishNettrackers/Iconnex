<?php

$agg = $_criteria["aggregate"]->get_criteria_value("VALUE", false);

$arr = preg_split("/,/", $agg);

foreach ( $arr as $v )
{
    if ( !$v )
        continue;
    $this->query_statement = preg_replace("/#$v/", "'All'", $this->query_statement);
    $this->query_statement = preg_replace("/#[a-zA-Z0-9_]*\.$v/", "'All'", $this->query_statement);
}

$matches = array();
$metasleft = true;
while ( $metasleft )
{
    $metasleft = false;
    preg_match ( "/#[a-zA-Z._[0-9]*/", $this->query_statement, $matches);
    if ( isset ( $matches[0] ) )
    {
        $from = $matches[0];
        $to = substr($matches[0], 1);
        $this->query_statement = preg_replace("/$from/", "$to", $this->query_statement);
        $metasleft = true;
    }
}

$golap = "'Type=Icon,".
         "HotspotX=15,".
         "HotspotY=20,".
         "Filters=Dmy;Event Type;Vehicle Code;Hour No;Route Code;Running No;Duty No;Trip No;Driver Name;Metric,".
         "Metric=Lateness Dep;Lateness Gain;Dwell Time;Travel Time,".
         "PosColor=#ff0000,".
         "Negcolor=#0000ff,".
         "ZeroColor=#00ff00,".
         "MetricRangeLower=0,".
         "MetricRangeUpper=50,".
         "PlotSize=30,".
         "RenderType=VehicleEvent,".
         "RenderElements=Route Code;Vehicle Code;Lateness Dep;Lateness Gain;Event Id;Dwell Time;Travel Time;Stop Bearing;Event Type;Etm Route;Etm Duty;Etm Runningno'";
//echo $golap;
$this->add_assignment("golap", $golap, false );

?>
