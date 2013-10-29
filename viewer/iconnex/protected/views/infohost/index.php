<?php
$this->breadcrumbs=array( 'Infohost',);
require_once("topmenustrip.php");
?>
    <!--h1>iConnex Public Interface</h1-->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/c.css" />

<?php
    ini_set("memory_limit","900M");
 
    set_include_path (get_include_path().":protected/extensions");
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


    topmenustrip($this, session_request_item ("target_menu", ""));
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
			$_REQUEST["forward_url_get_parameters"] = "r=infohost";
            $a->forward_url_get_parameters = "r=infohost";
            $a->execute();
        }
?>
</p>

