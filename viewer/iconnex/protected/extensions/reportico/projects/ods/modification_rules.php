<?php 

function custom_project_modifications($pdo, $return_status)
{

        $dbview = get_request_item("dbview", "");

        if ( !$dbview )
        {
            $return_status["errstat"] = -1;
            $return_status["msgtext"] = "No data view name specified";
        }

        switch ( $dbview )
        {
            case "maxspeed":
                custom_mod_maxspeed();

        $return_status["errstat"] = -1;
        $return_status["msgtext"] = "No dataset matched the specified view: $dview";

}


function custom_mod_maxspeed ()
{
    $keyid = 
}
?>
