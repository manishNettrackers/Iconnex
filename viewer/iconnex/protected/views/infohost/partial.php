<?php
$this->breadcrumbs=array( 'Infohost',);
?>
    <!--h1>iConnex Public Interface</h1-->

    <!--link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/c.css" /-->

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
         
                    $a->forward_url_get_parameters = "r=infohost";
                    $a->execute();
                }
?>
</p>

<?php 

	function domenu($in, $currentview)
	{
		$in->widget('application.extensions.mbmenu.MbMenu', array( 
            'items'=>array( 
				array('label'=>'Timetables', 'active' => $currentview=='timetables', 'url'=>array('/infohost','view'=>'timetables','target_menu'=>'timetables', 'project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')),
				array('label'=>'Trip Analysis', 'active' => $currentview=='trips', 'url'=>array('/infohost','view'=>'trips','target_menu'=>'trips', 'project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')),
				array('label'=>'Messaging', 'active' => $currentview=='messages', 'url'=>array('/infohost','view'=>'messages','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')),
				array('label'=>'Stop Analysis', 'active' => $currentview=='stops', 'url'=>array('/infohost','view'=>'stops','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')),
				array('label'=>'Administration', 'active' => $currentview=='admin', 'url'=>array('/infohost','view'=>'admin','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')),
            ), 
					));
	}
?>

