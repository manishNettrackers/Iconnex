<?php
Yii::app()->clientScript->registerScript('toolbarEvents',<<<EOD
    // User presses show/hide criteria side bar
    $('#showsidebar').live('click', function(event) {
        if ( $("#showsidebar").hasClass("hidefeature" ) )
        {  
            $("#criteriacol").css('display', 'none');
            $("#visualizer").css("width", "98%" ) ;
            $("#showsidebar").removeClass("hidefeature" ) ;
            $("#showsidebar").addClass("showfeature" ) ;
        }
        else
        {  
            $("#visualizer").css("width", "78%" ) ;
            $("#criteriacol").css('display', 'inline');
            $("#showsidebar").removeClass("showfeature" ) ;
            $("#showsidebar").addClass("hidefeature" ) ;
        }
        refreshDashboardWidgets ();
        resizeMap();
    });
    $('#showgrid').live('click', function(event) {
	    $('#reportcol').css('display', 'none');
	    $('#dashboardview').css('display', 'none');
	    //$('#mapcol').css('display', 'none');
	    //$('#mapfilter').hide();
	    $('#gridcol').css('display', 'inline');
	    //$('#linecol').css('display', 'none');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showmap').live('click', function(event) {
	    $('#reportcol').css('display', 'none');
	    $('#dashboardview').css('display', 'none');
	    $('#gridcol').css('display', 'none');
	    //$('#mapcol').css('display', 'inline');
	    //$('#linecol').css('display', 'none');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showdashboard').live('click', function(event) {
	    //$('#reportcol').css('display', 'none');
	    //$('#dashboardview').css('display', 'inline');
	    //$('#gridcol').css('display', 'none');
        showDashboard();
        unmaximiseDashboardWidgets ( )
        refreshDashboardWidgets ();
    });
    $('#showreport').live('click', function(event) {
	    $('#reportcol').css('display', 'inline');
	    $('#dashboardview').css('display', 'none');
	    $('#gridcol').css('display', 'none');
	    //$('#mapcol').css('display', 'none');
	    //$('#mapfilter').hide();
	    //$('#linecol').css('display', 'none');
	    //resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showmessages').live('click', function(event) {
        $('#messageframe').css('display', 'inline');
        $('#showmessages').removeClass('newmessages');
        $('#showmessages').addClass('nomessages');
    });
    /*$('#saveworkspace').live('click', function(event) {
        saveWorkspace("DEFAULT");
    });*/
    $('#loadworkspace').live('click', function(event) {
       // loadWorkspace("DEFAULT");
	   loadAllWorkspace();
    });
    $('.dashboardlayoutselector').live('click', function(event) {
        id = $(this).attr("id");
        layoutname = id.substr(7);
        selectDashboardLayout(layoutname);
    });

EOD
,CClientScript::POS_READY);
?>

<style>
ul.jd_menu {
    position: relative;
    margin: 0px;
    padding: 0px;
    height: 19px;
    list-style-type: none;

    background-color: #888;
    border: 1px solid #70777D;
    border-top: 1px solid #A5AFB8;
    border-left: 1px solid #A5AFB8;
}
ul.jd_menu ul {
    display: none;
}
ul.jd_menu a, 
ul.jd_menu a:active,
ul.jd_menu a:link,
ul.jd_menu a:visited
{
    text-decoration: none;
    color: #FFF;
}
ul.jd_menu li {
    float: left;
    font-family: Tahoma, sans-serif;
    font-size: 12px;
    padding: 2px 6px 4px 6px;
    cursor: pointer;
    white-space: nowrap;
    
    color: #FFF;
}
ul.jd_menu li.jd_menu_hover_toolbar {
    padding-left: 5px;
    border-left: 1px solid #ABB5BC;
    padding-right: 5px;
    border-right: 1px solid #929AA1;
    border-right: 1px solid #70777D;
    color: #FFF;
}
ul.jd_menu a.jd_menu_hover_toolbar {
    color: #FFF;
}

/* -- Sub-Menus Styling -- */
ul.jd_menu ul {
    position: absolute;
    display: none;
    list-style-type: none;
    margin: 0px;
    padding: 0px;

    background: #ABB5BC;
    border: 1px solid #70777D;
}
ul.jd_menu ul li {
    float: none;
    margin: 0px;
    padding: 3px 10px 3px 4px;
    width: 300px;
    font-size: 14px;

    background: #FFF6F6;
    border: none;
    color: #70777D;
}
ul.jd_menu ul li.jd_menu_hover {
    padding-top: 2px;
    border-top: 1px solid #ABB5BC;
    padding-bottom: 2px;
    border-bottom: 1px solid #929AA1;
    color: #FFF;
}
ul.jd_menu ul a, 
ul.jd_menu ul a:active,
ul.jd_menu ul a:link,
ul.jd_menu ul a:visited {
    text-decoration: none;
    color: #70777D;
}
ul.jd_menu ul a.jd_menu_hover {
    color: #FFF;
}

</style>
<div id="selectmenu" style="width:100%; clear: both; display: block; margin-bottom: 2px;">
		<form action="#" id="toolbarform" style="padding: 0;width:100%; display: block;">
        	<nav class="accordionblock jd_menu jd_menu_slate" id="dropmenu" >
    		</nav>
             <input style="display:none" type="button" id="showmap" title="Show Map" style="float: left"/>
             <input style="display:none" type="button" id="showreport" title="Show Report" style="float: left">
        </form>
 </div>
<?php /*
	<div id="selectmenu" style="width:100%; clear: both; display: block; margin-bottom: 2px;">
		<form action="#" id="toolbarform" style="padding: 0;width:100%; display: block;">

            <!-- Drop down menu bar -->
          <div style="height: 26px; background-color: #888888">
            <ul style="clear: none;float: left;width: 80%; z-index: 400" id="dropmenu" class="jd_menu jd_menu_slate"> </ul><!---->
           
    	    <!--input style="display:none" type="button" id="showgrid" title="Show Data Grid" value=""  style="float: left"/>
    	    <input style="display:none" type="button" id="showmap" title="Show Map" style="float: left"/>
    	    <!--input type="button" id="showlinecol" title="Show Line View" style="float: right"/-->
            
    	    <input style="display:none" type="button" id="showreport" title="Show Report" style="float: left">
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" value="Save" id="saveworkspace" title="Save Workspace" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" value="Load" id="loadworkspace" title="Load Workspace" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" value="L3" id="select_dashboardlayout3" class="dashboardlayoutselector" title="Dashboard Layout 3" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" value="L2" id="select_dashboardlayout2" class="dashboardlayoutselector" title="Dashboard Layout 2" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" value="L1" id="select_dashboardlayout1" class="dashboardlayoutselector" title="Dashboard Layout 1" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="display:inline" type="button" id="showdashboard" title="Show Dashboard" style="float: left"/></div>
    	    <div style="float: right; clear: none"><input style="clear: none;display:inline" type="button" id="showmessages" class="nomessages" title="Show Messages" style="float: right"/></div>
		    <div id="loadindicator" style="float: right"></div>
            </div>
<!--?php
$this->widget('golapProgressBar',
        array(
            'id'=>'progress',
            'value'=> 50,
            'htmlOptions'=>array( 'style'=>'margin: 2px 0 0 10px;width:200px; height:20px; float:left;'
            )
));
?-->
            <!--div id="switcher" style="float:right"></div>
            <div class="headerlinks" style="display:none">
                <a class="openaddwidgetdialog headerlink ui-widget-content" href="#">Add Widget</a>&nbsp;<span class="headerlink">|</span>&nbsp;
                <a class="editlayout headerlink ui-widget-content" href="#">Edit layout</a>
            </div-->
		    <!--div style="clear: both;"></div-->
		</form>
	</div>
*/ ?>