
<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="/pii/iconnex/protected/extensions/reportico/stylesheet/cleanandsimple.css"-->

<!-- Uncomment for OpenStreetMap -->
<!--script type="text/javascript" src="/osm/OpenLayers.js"></script-->
<script type="text/javascript" src="./js/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.accordion.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.resizable.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.button.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="./js/dashboard.js"></script>
<script type="text/javascript" src="./js/tabmod.js"></script>
<script type="text/javascript" src="./js/ui/jquery.ui.position.js"></script>

<script language="javascript" type="text/javascript" src="./js/jqplot/jquery.jqplot.min.js"></script>
<script language="javascript" type="text/javascript" src="./js/jquery.jdMenu.js"></script>
<link rel="stylesheet" type="text/css" href="./js/jqplot/jquery.jqplot.css" />
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.logAxisRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="./js/jqplot/plugins/jqplot.pointLabels.min.js"></script>

<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="./js/ui/themes/rbc/jquery.ui.all.css">
<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="css/iconnexgreen/jquery-ui-1.8.16.custom.css"-->
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="./js/ui/themes/iconnexgreen/jquery.ui.tabs.css">
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="./js/ui/themes/iconnexgreen/dashboardui.css">
    <script type="text/javascript" src="./js/lib/jquery.dashboard.min.js"></script>
    <!--script type="text/javascript" src="./js/lib/themeroller.js"></script-->



    <link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/ui.multiselect.css" />
    <style type="text">
        html, body {
        margin: 0;            /* Remove body margin/padding */
        padding: 0;
        overflow: hidden;    /* Remove scroll bars on browser window */
        font-size: 75%;
        }
        ui-elipsis {text-overflow:ellipsis; -moz-binding:url('../../../themes/ellipsis-xbl.xml#ellipsis') }
    </style> 

<!--script src="./js/jquery.js" type="text/javascript"></script-->
<!--script src="./js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script-->

<script src="./js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script type="text/javascript">
	$.jgrid.no_legacy_api = true;
	$.jgrid.useJSON = true;
</script>
<script src="./js/jquery.jqGrid.src.js" type="text/javascript"></script>
<!--script src="./js/jquery.jqGrid.min.js" type="text/javascript"></script>
<!--script src="./js/ui.multiselect.js" type="text/javascript"></script>
<script src="./js/jquery.layout.js" type="text/javascript"></script>
<script src="./js/jquery.tablednd.js" type="text/javascript"></script>
<script src="./js/jquery.contextmenu.js" type="text/javascript"></script-->
<script type="text/javascript">
	var firstClick = true; 
	var userid = false; 
	$(".container").css("width", "100%");
</script>



<?php

function get_app_url()
{
    echo Yii::app()->request->baseUrl;
}
echo "<script type=\"text/javascript\">";
echo " var menuCode = '".$this->menuCode."';";
echo " var baseUrl = '";
get_app_url();
echo "';";
echo " var iconnexUser = '";
echo Yii::app()->user->id;
echo "';";
echo " var urlRoot = '";
echo dirname ( CController::createUrl('index.php'));
echo "';";
echo "</script>";


Yii::app()->clientScript->registerScript('initEvents',<<<EOD

    $('.container').addClass('ui-widget-content');

    $('.menubutton').live('click', function(event) {
        arr = this.id.split("_");
        if ( arr.length > 1 )
        {
            panelno = arr[1];
            $("#accordionheader" + panelno).click();
        }
    });
EOD
,CClientScript::POS_READY);
?>

<!--script src="<?php get_app_url(); ?>/js/golap.js" type="text/javascript"></script-->
<script src="./js/session.js" type="text/javascript"></script>
<script src="./js/golap.js" type="text/javascript"></script>
<script src="./js/workspace.js" type="text/javascript"></script>
<script type="text/javascript"> var fill = "";</script>
<link rel="stylesheet" type="text/css" href="<?php get_app_url(); ?>/css/golap.css" />

    <?php $this->widget('messageWindow'); /* Message window*/ ?>
    <?php $this->widget('midscreenDetailWindow'); /* Middle window that appears when user clicks report link */ ?>
    <?php $this->widget('mapMarkerDetailWindow'); /* Small window that appears on user clicking map icon/marker */ ?>

<?php
	$appuser = "guest";
	if ( Yii::app()->user->getId() )
		$appuser = Yii::app()->user->getId();

	echo '<input type="button" id="activeuser" style="display:none" name="'.$appuser.'" label="'.$appuser.'">';

?>


<div id="debug" style="width:100%; display:none"></div>

<?php include ("golaptoolbar.php"); // full width toolbar containing dropdown list menu options, view (map, grid, loading gif) ?>

<div class="big-corners" id="bigloading"><BR>Please Wait ..</div>

<div id="mapping" style="width: 100%; height: 90%">
<?php include ("golapcriteria.php"); // full width toolbar containing dropdown list menu options, view (map, grid, loading gif) ?>
<script src="./js/pwitabs.js" type="text/javascript"></script>
<script src="./js/pwimenu.js" type="text/javascript"></script>
<!--script src="./js/pwisubwindow.js" type="text/javascript"></script-->
<script src="./js/pwicriteria.js" type="text/javascript"></script>

<div style="float: left; height: 100%"><input style="display:inline" class="hidefeature" type="button" id="showsidebar" title="Show Sidebar" style="float: left"/></div>

<!-- Map container -->
<div id="visualizer" style="height: 90%; padding-left: 4px; margin: 0px 0px 0px 0px; width: 78%; float: left;">

<!-- Grid container -->
<div id="gridcol" style="margin: 4px 0px 0px; display: none; width: 100%; float: left;">
		<table id="datagrid" style="padding: 0px"></table> 
		<div id="datagridpager"></div> 
</div>

<!-- Fulll screen report View container -->
<div id="reportcol" style="margin: 4px 0px 0px 0px; height: 100%; overflow: scroll; display: none; width: 100%; float: left;">
		<table id="datagrid" style="padding: 0px"></table> 
</div>

<!-- Line View container -->
<!--div id="linecol" style="margin: 4px 0px 0px 0px; display: none; width: 100%; float: left;">
		<table id="datagrid" style="padding: 0px"></table> 
</div-->

<?php include ("golapmapview.php"); // Map View ?>

<?php $this->widget('golapDashboard'); /* Dashboard portlet view*/ ?>

<!-- Line View container -->

</div>

</div>
</div>
<noscript><b>JavaScript must be enabled in your browser for this page to function correctly.</b></noscript>


    <style type="text/css" media="screen">
    * { 
        margin: 0; 
    }

    html, 
    body { 
        height: 100%; 
    }

    #header { 
        height: 86px; 
    }

    #footer, 
    #push {
        height: 44px;   
    }
    </style>

