<?php

$agg = $_criteria["aggregate"]->get_criteria_value("VALUE", false);

$arr = preg_split("/,/", $agg);

$ordcols = array(
        "dmy" => true,
        "hour_no" => true,
        "addr_suburb" => true,
        "addr_road" => true,
        "geohash" => true,
        "route_code" => true,
        "running_no" => true,
        "duty_no" => true,
        "trip_no" => true
        );

foreach ( $arr as $v )
{
    if ( !$v )
        continue;
    $this->query_statement = preg_replace("/#$v/", "'All'", $this->query_statement);
    $this->query_statement = preg_replace("/#[a-zA-Z0-9_]*\.$v/", "'All'", $this->query_statement);
    $field = 'SW_JQDEF_peoplecountaggrtpi_'.$v.'_hidden';

    $ordcols[$v] = false;
    define($field, true);
}

$orderby = "";
foreach ( $ordcols as $k => $v )
{
    if ( $v ) 
    {
        if ( !$orderby ) 
            $orderby = "ORDER BY $k";
        else
            $orderby .= ",".$k;
    }
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
        $this->query_statement .= " $orderby";

$golap = "'Type=Icon,".
         "HotspotX=15,".
         "HotspotY=20,".
         "Filters=Dmy;Vehicle Code;Hour No;Addr Road;Addr Suburb;Route Code;Running No;Duty No;Trip No,".
         "PosColor=#ff0000,".
         "Negcolor=#0000ff,".
         "ZeroColor=#00ff00,".
         "MetricRangeLower=0,".
         "MetricRangeUpper=50,".
         "PlotSize=30,".
         "RenderType=CountEvent,".
         "RenderElements=Vehicle Code;Sum In;Sum Out'";
//echo $golap;
$this->add_assignment("golap", $golap, false );

define('SW_JQDEF_peoplecountaggrtpi_latitude_hidden', true);
define('SW_JQDEF_peoplecountaggrtpi_longitude_hidden', true);
define('SW_JQDEF_peoplecountaggrtpi_average_occupancy_hidden', true);
define('SW_JQDEF_peoplecountaggrtpi_average_total_in_hidden', true);
define('SW_JQDEF_peoplecountaggrtpi_average_total_out_hidden', true);


?>
