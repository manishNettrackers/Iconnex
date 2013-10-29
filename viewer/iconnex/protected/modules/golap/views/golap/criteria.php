<?php
    		ini_set("memory_limit","900M");

    $f = fopen ( "/tmp/req2", "w+");
    $str = "";
    foreach ( $_REQUEST as $k => $y )
    {
    $str .= "$k = $y\n";
    }
    fwrite($f, $str);
    fclose($f);

    set_include_path (get_include_path().":".YiiBase::getPathOfAlias("application")."/extensions/reportico/");
    include_once('reportico.php');
    $inview = "x";
    if ( !isset($_REQUEST["execute_mode"]) )
                	$_REQUEST["execute_mode"] = "PREPARE";
                //$_REQUEST["xmlin"] = "vehiclepos.xml";
                //$_REQUEST["project"] = "ods";
                $_REQUEST["template"] = "pwi";
                $_REQUEST["linkbaseurl"] = "protected/extensions/reportico/run.php";
				if ( array_key_exists ("view", $_REQUEST) )
                {
                    $inview = $_REQUEST["view"]; 
		    $v = session_request_item ("target_menu", $_REQUEST["view"]);
                    $_REQUEST["target_menu"] = $_REQUEST["view"];
                }
				if ( array_key_exists ("project", $_REQUEST) )
					$x = session_request_item ("project", $_REQUEST["project"]);
                if (  $inview == "mainmenu" )
                {
                    echo "<BR>";
                    echo "<BR>";
                    echo "<p style=\"text-align: center; font-size: 14pt;\">Welcome to iConnex.<br>Select an option from the menu above.</p>";
                    echo "<BR>";
                }
                else
                {
                    $a = new reportico($this);
                    $a->allow_maintain = "FULL";
                    $a->embedded_report = true;
         
                    $a->forward_url_get_parameters = "r=pwi/pwi/criteria";
                    $a->execute();
                }


?>
