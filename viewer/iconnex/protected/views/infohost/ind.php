<?php
$this->breadcrumbs=array( 'Infohost',);
?>

<p style="font-size: 200%">Infohost Reporting Suite</p>
<br>
    <!--h1>iConnex Public Interface</h1-->

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/c.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datePicker.css" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/date.js" type="text/javascript"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.datePicker.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf-8">
         $(function()
         {
             $('.date-pick').datePicker({autoFocusNextInput: true});
         });
    </script>


      <div id="container">

            <h1>jquery.datePicker example: simple datePicker</h1>
            <p><a href="index.html">&lt; date picker home</a></p>
            <p>
                The following example displays simple use of the datePicker component to select a date for
                input fields.
            </p>
            <form name="chooseDateForm" id="chooseDateForm" action="#">
                <fieldset>
                    <legend>Test date picker form</legend>

                    <ol>
                        <li>
                            <label for="date1">Date 1:</label>
                            <input name="date1" id="date1" class="date-pick" />
                        </li>
                        <li>
                            <label for="date2">Date 2:</label>
                            <input name="date2" id="date2" class="date-pick" />

                        </li>
                        <li>
                            <label for="test-select">Test select:</label>
                            <select name="test-select" id="test-select" style="width: 170px">
                                <option value="1">Test SELECT </option>
                                <option value="2">Doesn't shine through</option>
                                <option value="3">Even in IE</option>

                                <option value="4">Yay!</option>
                            </select>
                        </li>
                    </ol>
                </fieldset>
            </form>
            <h2>Page sourcecode</h2>
            <pre class="sourcecode">

$(function()
{
    $('.date-pick').datePicker();
});</pre>
            <h2>Page CSS</h2>
            <pre class="sourcecode">
/* located in demo.css and creates a little calendar icon
 * instead of a text link for "Choose date"
 */
a.dp-choose-date {
    float: left;
    width: 16px;
    height: 16px;
    padding: 0;
    margin: 5px 3px 0;
    display: block;
    text-indent: -2000px;
    overflow: hidden;
    background: url(calendar.png) no-repeat; 
}
a.dp-choose-date.dp-disabled {
    background-position: 0 -20px;
    cursor: default;
}
/* makes the input field shorter once the date picker code
 * has run (to allow space for the calendar icon
 */
input.dp-applied {
    width: 140px;
    float: left;
}</pre>
        </div>


  

<?php
    ini_set("memory_limit","900M");
 
                //set_include_path (get_include_path().":/home/peter/Documents/reportico/swsite/site/reportico-src");
                set_include_path (get_include_path().":/opt/centurion/users/peterd/trunk/web/yii/iconnex/protected/extensions/");

                require_once('reportico/reportico.php');
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
                domenu($this, session_request_item ("target_menu", ""));
                if (  $inview == "mainmenu" )
                {
                    echo "<BR>";
                    echo "<BR>";
                    echo "<BR>";
                    echo "<BR>";
                    echo "<BR>";
                    echo "<p style=\"text-align: center; font-size: 14pt;\">Welcome to the Infohost Reporting Suite.<br>Select a report menu from the above options.</p>";
                    echo "<BR>";
                }
                else
                {
                    $a = new reportico_query();
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

