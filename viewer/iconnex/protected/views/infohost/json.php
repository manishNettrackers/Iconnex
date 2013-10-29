<?php
    ini_set("memory_limit","900M");
 
                //set_include_path (get_include_path().":/home/peter/Documents/reportico/swsite/site/reportico-src");
    		set_include_path (get_include_path().":/var/www/yii/iconnex/protected/extensions/");

                include_once('reportico/reportico.php');
                $v = "x";
                $inview = "x";
				if ( array_key_exists ("view", $_REQUEST) )
                {
                    $inview = $_REQUEST["view"]; 
					$v = session_request_item ("target_menu", $_REQUEST["view"]);
                    $_REQUEST["execute_mode"] = "MENU";
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
                    $a = new reportico_query($this);
                    $a->allow_maintain = "FULL";
                    $a->embedded_report = true;
         
                    $a->forward_url_get_parameters = "r=infohost";
                    $a->execute();
                }
?>
