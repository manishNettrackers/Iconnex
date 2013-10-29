<?php

//include "iconnex.php";
include "odsconnector.php";

$engine = new odsconnector($_pdo);

//$dr = $_criteria["driver"]->get_criteria_value("VALUE");

$carparks = get_car_parks ($engine);

function get_car_parks ($engine)
{
    // Prepare temporary table
    $sql = "CREATE TEMPORARY TABLE t_carparks (
        id CHAR(40),
        name CHAR(40),
        latitude DECIMAL(12,5),
        longitude DECIMAL(12,5),
        capacity INTEGER,
        occupancy INTEGER,
        spaces INTEGER,
        timestamp CHAR(25)
        );";
    $engine->executeSQL($sql);

    // ------------------------------------------------------------------ults)
    // Fetch list of car parks from Metro TV
    $results = $engine->api_connect("http://www.metrotv.co.uk/mapfeed.ashx", 
                    array ("cfg" => "reading_web_metrotv", "type" => "11")
                    );
    
    $carparks = json_decode(json_encode((array) simplexml_load_string($results)),1);

    if ( $carparks )
    {
        foreach ( $carparks["entity"] as $k => $v )
        {
            $id = $v["id"];
            $occupancy = $engine->api_connect("http://www.metrotv.co.uk/mapdetail.ashx", 
                    array ("config" => "reading_web_metrotv", "id" => $id)
                    );

            $carparks["entity"][$k]["title"] = preg_replace("/\(.*/", "", $carparks["entity"][$k]["title"]);
            $matches = array();
            $matched = preg_match ( "/^.*Spaces Free:[^0-9]*([0-9]*).*Capacity:[^0-9]*([0-9]*).*Occupancy:[^0-9]*([0-9]*).*Last Updated:[^0-9]*(.*)/", $occupancy, $matches);
            if ( $matched )
            {
                if ( $matched && $matches )
                {
                    $carparks["entity"][$k]["spaces"] = $matches[1];
                    $carparks["entity"][$k]["capacity"] = $matches[2];
                    $carparks["entity"][$k]["occupancy"] = $matches[3];
                    $carparks["entity"][$k]["timestamp"] = $matches[4];
                }
            }
            else
            {
                $matched = preg_match ( "/^.*Capacity:[^0-9]*([0-9]*).*Last Updated:[^0-9]*(.*)/", $occupancy, $matches);
                if ( $matched && $matches )
                {
                    $carparks["entity"][$k]["capacity"] = $matches[1];
                    $carparks["entity"][$k]["timestamp"] = $matches[2];
                    $carparks["entity"][$k]["spaces"] = "0";
                    $carparks["entity"][$k]["occupancy"] = "0";
                }
                else
                {
                    $carparks["entity"][$k]["spaces"] = "0";
                    $carparks["entity"][$k]["capacity"] = "0";
                    $carparks["entity"][$k]["occupancy"] = "0";
                    $carparks["entity"][$k]["timestamp"] = "0";
                }
            }

            // Prepare temporary table
            $sql = "INSERT INTO t_carparks VALUES (".
                "'".$carparks["entity"][$k]["id"]."',".
                "'".$carparks["entity"][$k]["title"]."',".
                "".$carparks["entity"][$k]["lat"].",".
                "".$carparks["entity"][$k]["lng"].",".
                "".$carparks["entity"][$k]["capacity"].",".
                "".$carparks["entity"][$k]["occupancy"].",".
                "".$carparks["entity"][$k]["spaces"].",".
                "'".$carparks["entity"][$k]["timestamp"]."')";
            $engine->executeSQL($sql);
        }
    }
}


?>
