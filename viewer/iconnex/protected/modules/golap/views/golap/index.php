<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="/pii/iconnex/protected/extensions/reportico/stylesheet/cleanandsimple.css"-->

<script type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.accordion.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.resizable.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.button.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="js/dashboard.js"></script>
<script type="text/javascript" src="js/tabmod.js"></script>
<script type="text/javascript" src="js/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="js/tauren-jquery-ui-2004985/ui/jquery.ui.selectmenu.js"></script>
<link type="text/css" href="js/tauren-jquery-ui-2004985/themes/base/jquery.ui.selectmenu.css" rel="stylesheet" />

<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="js/ui/themes/iconnexgreen/jquery.ui.all.css">
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="css/iconnexgreen/jquery-ui-1.8.16.custom.css">
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="js/ui/themes/iconnexgreen/jquery.ui.tabs.css">
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="js/ui/themes/iconnexgreen/dashboardui.css">

    <script type="text/javascript" src="js/lib/jquery.dashboard.min.js"></script>
    <script type="text/javascript" src="js/lib/themeroller.js"></script>



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

<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>

<script src="js/jquery.layout.js" type="text/javascript"></script>
<script src="js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script type="text/javascript">
	$.jgrid.no_legacy_api = true;
	$.jgrid.useJSON = true;
</script>
<!--script src="js/jquery.jqGrid.min.js" type="text/javascript"></script-->
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>
<!--script src="js/ui.multiselect.js" type="text/javascript"></script>
<script src="js/jquery.tablednd.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.js" type="text/javascript"></script-->
<script type="text/javascript">
	var firstClick = true; 
	var userid = false; 
	$(".container").css("width", "100%");

function showSubwindow(link, targetwindow)
{
	var url = link.href;

	url += "&clear_session=1";

	set_loading_status (true);
	$.ajax(
	{
		type: "GET",
		url: url,
		success: function(result)
		{
			// Set up any pre-sending stuff like initializing progress indicators
			targetframe = $('#' + targetwindow + "frame");
			targetresults = $('#' + targetwindow);
			targetframe.css('display', 'inline' );
        	$(targetresults).attr('innerHTML',result);
			targettitle = $('#' + targetwindow + "title");
			$(targetframe).find('.swLinkMenu').css('display', 'none');
			$(targetframe).find('.swRepTitle').each(function(){
                                    $(targettitle).attr('innerHTML', $(this).attr('innerHTML'));
                                    $(this).css('display', 'none');
                            });


			set_loading_status (false);
		},
		error: function(x, e)
		{
			set_loading_status (false);
			alert("Error - unable to load form");   
		}
	});
};
</script>



<?php

function get_app_url()
{
    echo Yii::app()->request->baseUrl;
}

echo "<script type=\"text/javascript\">";
echo " var menuCode = 'Admin';";
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

    global $_assetsUrl;
    $_assetsUrl = false;
    function getAssetsUrl()
    {
        global $_assetsUrl;
        if ( !$_assetsUrl )
            $_assetsUrl = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias('application.views.pwi.images') );
        return $_assetsUrl;
    }

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

    $('.showfiltermap').live('click', function(event) {


	    var a = this;
        accordionContainer = $(this).closest(".accordioncrit");
        var a = accordionContainer.find('input');
        var session = "";
        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
			hideshowlayer(session, false);
			if ( get_session_param(session, "hasline" ) )
            	initGOLAPFilters(session, true);
			else
            	initGOLAPFilters(session, false);
            showGOLAPFilters(session);
        });
    

    $('.expandwindow').live('click', function(event) {
		showSubwindow(this, "subwindow");
		return false;
	});

    $('.clickinfowindow').live('click', function(event) {
		showSubwindow(this, "smallsubwindow");
		return false;
	});

    $('.mapfilterck_mutex').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;
		if ( this.checked )
       		filterGOLAP(session, "SHOWNONE", name, "", this.checked);

        //showGOLAPFilters(session);
	});

    $('.mapfilterck_showall').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;
       	filterGOLAP(session, "SHOWALL", name, "", this.checked);
        //showGOLAPFilters(session);
	});

    $('.mapfilterck_shownone').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;
       	filterGOLAP(session, "SHOWNONE", name, "", this.checked);
        //showGOLAPFilters(session);
	});

    $('.mapfilterck').live('click', function(event) {

	    var a = this;
        var value = this.name;
        var name = this.id;
        var checked = this.checked;
        html = $(this).attr('innerHTML');
        name = name.replace(/mfck_/, "");
        name = name.replace(/_/g, " ");
    
        container = $(this).closest(".mpflttab");
        var a = container.find('input');
        var session = "";

        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
        filterGOLAP(session, "NONE", name, value, checked);
    });
    $('#clearmap').live('click', function(event) {
	    clearmap();
    });
    $('#showlinecol').live('click', function(event) {
	    $('#dashboardcol').css('display', 'none');
	    $('#mapcol').css('display', 'none');
	    $('#gridcol').css('display', 'none');
	    $('#linecol').css('display', 'inline');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showgrid').live('click', function(event) {
	    $('#dashboardcol').css('display', 'none');
	    $('#mapcol').css('display', 'none');
	    $('#gridcol').css('display', 'inline');
	    //$('#linecol').css('display', 'none');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showmap').live('click', function(event) {
	    $('#dashboardcol').css('display', 'none');
	    $('#gridcol').css('display', 'none');
	    $('#mapcol').css('display', 'inline');
	    //$('#linecol').css('display', 'none');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    $('#showdashboard').live('click', function(event) {
	    $('#dashboardcol').css('display', 'inline');
	    $('#gridcol').css('display', 'none');
	    $('#mapcol').css('display', 'none');
	    //$('#linecol').css('display', 'none');
	    resizeMap();
	    $("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));
    });
    //$(function() {
	//$("#criteriacol").resizable({
			//resize: function() {
				//$("#accordion").accordion("resize");
			//}}
		//);
    //});

    $(function() {
        //$( "#accordion" ).accordion({
			//fillSpace: true,
			//header: "h3",
            //clearStyle: true,
            //event: 'mouseover',
            //change: function ( event, ui )
            //{
                //alert ("changed");
            //}
				//});

        //$( "#accordion" ).accordion( "option", "header", "div");
        //$( "#accordion" ).accordion( "option", "fillSpace", true);
    });

    //$('.accordionheader').click(function() {
        //$(this).next().toggle('slow');
        //return false;
    //}).next().hide();

    //$("#accordion .accordionheader input").click(function(evt) {
        //evt.stopPropagation();
    //}); 

    $('.swAutoCentre').live('click', function() {
   		var session = get_golap_session('.accordiondetail');
		set_session_param ( session, "autorecentre", this.checked );
	});

    $('.swAutoZoom').live('click', function() {
   		var session = get_golap_session('.accordiondetail');
		set_session_param ( session, "autozoom", this.checked );
	});

    $('.swAutoRefresh').live('click', function() {
        accordionContainer = $(this).closest(".accordiondetail");
        var a = accordionContainer.find('input');
        var session = "";
        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
		if ( !autorefreshes[session] )
		{
			this.checked = !this.checked;
			alert ("Perform query before using auto refresh" );
			return;
		}
		if ( this.checked )
		{
			autorefreshes[session].timeout = setTimeout ( autorefreshes[session].cmd, 1000 );
		}
		else
		{
			autorefreshes[session]["status"] = "IDLE";
			clearTimeout ( autorefreshes[session].timeout );
			autorefreshes[session].autorefresh = false;
		}

        //hideshowlayer(session, this.checked);
    });
    $('.accordionshow').live('click', function() {
        accordionContainer = $(this).closest(".accordiondetail");
        var a = accordionContainer.find('input');
        var session = "";
        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
        hideshowlayer(session, this.checked);
    });

    $('.accordionremove').live('click', (function() {

        accordionContainer = $(this).closest(".accordionheader").next();
        var a = accordionContainer.find('input');
        var session = "";
        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
        deleteMarkers(session);
        deleteDashboardWidget(session);

		if ( autorefreshes[session] )
		{
			//clearTimeout ( autorefreshes[session].timeout );
			autorefreshes[session]["status"] = "REMOVE";
		}
        var id = $(this).attr('id');
        var idno = id.substring(15, id.length);
        var me = this;
        var parent = $(this).closest('div');
        var head = $('#accordionframe' + idno);
        //$('#accordionframe' + idno).fadeOut('fast',function()
            //{
                $('#accordiondetail' + idno).remove();
                $('#accordionheader' + idno).remove();
                $('#accordionframe' + idno).remove();
                //$(this).remove();
            //});
    }));

    //$('.jQueryCalendar').live('click', function () {
            //$(this).datepicker().focus();
    //});
    $('#critform :submit').live('click', function(event) {
             target = this;
	     var me = this;

        set_loading_status (true);
        accordionContainer = $(this).closest(".accordioncrit");

	    var x=document.getElementsByName("submitPrepare");
        var myform = accordionContainer.find('#critform');
	    var formaction =  $(myform)[0].action;
	    var dataString = $(myform).serialize();
	    dataString = dataString + "&r=golap/golap/criteria";
	    dataString = dataString + "&" + me.name + "=1";

        var a = accordionContainer.find('input');
        var session = "";
        a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
	    if ( me.name == "submitPrepareData" )
	    {
	    	//$('#map').css('display', 'none');
	    	//$('#gridcol').css('display', 'inline');
            $("#datagrid").jqGrid('GridUnload');
			userid=$("#activeuser");
			userid=userid[0].name;
			lastGridSelection = -1;
            editurl = "protected/extensions/reportico/modify.php";
			dataString = dataString + "&target_format=jquerygrid&template=pwi&execute_mode=EXECUTE&submitPrepare=1&view=" + "user=" + userid + "&dbview=" + "tripcancel";
			$.ajax(
			{
				type: "GET",
				url: formaction,
				data: dataString,
				dataType: "json",
				success: function(result)
				{
                    $("#showgrid").click();
					colD = result.gridmodel;
					colN = result.colnames;
					colM = result.colmodel;
                    viewname = result.viewname;
					userid=$("#activeuser");
					userid=userid[0].name;
                    //editurl = formaction + "?" + "oper=unknown" + "&" + "dbview=" + viewname + "&" + dataString + "&execute_mode=MODIFY&user=" + userid + "&dbview=" + viewname;
                    editurl = "protected/extensions/reportico/modify.php" + "?" + 
                            "session_name=" + session + "&execute_mode=MODIFY&user=" + userid + "&dbview=" + viewname;
					//editurl = "http://10.0.0.9/infohostpd/server.php?viewname="+viewname;
	
                    if ( false )
                    {
                        jQuery("#datagrid").jqGrid('setGridParam',
                            {
                                url: formaction + "?" + dataString,
            		            datastr : colD,
            		            colNames:colN,
                                colModel :colM,
            		            page:1,
                                rowNum: 100,
            		            colModel :colM,
                            }).trigger("reloadGrid");
               		}
                    else
                    {
                            firstClick = false;

                           jQuery("#datagrid").jqGrid({
                            jsonReader : {
                                repeatitems: true,
                                root:"rows",
                                cell: "cell",
                                id: "id"
                            },
                            url: formaction + "?" + dataString,
                            datatype: 'jsonstring',
                            mtype: 'GET',
                            datastr : colD,
                            colNames:colN,
                            colModel :colM,
                            //shrinkToFit: true,
                            height: "400px",
                            pager: jQuery('#datagridpager'),
                            page: 1,
                            rowNum: 100,
							rowTotal: 50000,
                            sortable: true,
                            loadonce: true,
                            rowList: [5, 10, 20, 50],
                            viewrecords: true,
                            caption: 'Data Visualisation',
                            //loadComplete: function(data){alert('loaded');},
                            //editurl: "http://10.0.0.9/infohostpd/server.php?viewname="+"viewname",
                            editurl: editurl,
							edit : {
								addCaption: "Add Record",
								editCaption: "Edit Record",
								bSubmit: "Submit",
								bCancel: "Cancel",
								bClose: "Close",
								saveData: "Data has been changed! Save changes?",
								bYes : "Yes",
								bNo : "No",
								bExit : "Cancel",
							},
                            loadError: function(xhr,status,error){alert('error');},
							loadComplete: function() {
    							jQuery("#datagrid").trigger("reloadGrid"); // Call to fix client-side sorting
							},
							gridComplete: 
								function(){ 
									var ids = jQuery("#datagrid").jqGrid('getDataIDs'); 
									for(var i=0;i < ids.length;i++){ 
										var cl = ids[i]; 
										be = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-pencil' value='E' onclick=\"jQuery('#datagrid').jqGrid('editRow', '"+cl+"', true);\" />";
								 		se = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-check' value='S' onclick=\"jQuery('#datagrid').jqGrid('saveRow', '"+cl+"', true);\" />";
								 		ce = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-close' value='C' onclick=\"jQuery('#datagrid').jqGrid('restoreRow', '"+cl+"', true);\" />"; 
										jQuery("#datagrid").jqGrid('setRowData',ids[i],{options:be+se+ce});
								 } } ,
                            onSelectRow: function(id){ 
                                    if(id && id!==lastGridSelection)
                                    { 
										if ( lastGridSelection != -1 )
                                        	jQuery('#datagrid').jqGrid('restoreRow',lastGridSelection); 
                                        jQuery('#datagrid').jqGrid('editRow',id,true); lastGridSelection=id; 
                                    } 
                                } 
            		});
                    jQuery("#datagrid").jqGrid('navGrid',"#datagridpager",{edit:true,add:true,del:true});
					jQuery("#datagrid").jqGrid('navButtonAdd', '#datagridpager',{
   							caption:"MyButton", 
                            buttonicon:"none",
                            onClickButton: function()
                            {
                                alert("Petering");
                            },
   							position:"last"
						});
	    			$("#datagrid").jqGrid('setGridWidth', ($("#gridcol").width()));


                    }
                    set_loading_status (false);
				},
       			error: function(x, e)
       			{
						set_loading_status (false);
            			//alert(x.readyState + " "+ x.status +" "+ e.msg);   
            			alert("No data found matching your criteria");   
       			}
       		});
			//setTimeout(function() { $("#datagrid").jqGrid('setGridParam',{datatype:'json'}); },500);

	    }
        else if ( me.name == "submitPrepareReport" )
	    {
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=HTML&hide_output_text=0&hide_output_graph=1&execute_mode=EXECUTE&submitPrepare=1";
	    	formaction =  "protected/extensions/reportico/run.php";
		    retval = getReportOutput(this.className, session, formaction, dataString, autorefresh, false);
            set_loading_status (false);
	    }
        else if ( me.name == "submitPrepareChart" )
	    {
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=HTML&hide_output_text=1&target_show_graph=1&execute_mode=EXECUTE&submitPrepare=1";
	    	formaction =  "protected/extensions/reportico/run.php";
		    retval = getReportOutput(this.className, session, formaction, dataString, autorefresh, false);
            set_loading_status (false);
	    }
        else if ( me.name == "submitPrepare" )
	    {
	    	//$('#gridcol').css('display', 'none');
	    	//$('#map').css('display', 'inline');
            ck = $(this).closest(".accordiondetail").find(".accordionshow");
            ck[0].checked = true;
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=json&execute_mode=EXECUTE&submitPrepare=1";
	    	formaction =  "protected/extensions/reportico/run.php";
            if ( this.className == "swLineButton" )
                initializeDashboardLine();
		    retval = getGOLAPPositions(this.className, session, formaction, dataString, autorefresh, false);
	    }
	    else
	    {
		$.ajax({  
		type: 'GET',  
		url: formaction,
		data: dataString,  
		success: function(data) {  
            set_loading_status (false);
	    	if ( me.name == "submitPrepareData" )
	    	{
            	$("#gridcol").html(data);
	    	}
			else
            	accordionContainer.html(data);
		},
		error: function(xhr, desc, err) {
			set_loading_status (false);
		    alert('no');
		}
		});
	    }
	    return false;
    });
    //getUserMenu("admin", baseUrl );
EOD
,CClientScript::POS_READY);
?>

<script src="<?php get_app_url(); ?>/js/json2.js" type="text/javascript"></script>
<script src="<?php get_app_url(); ?>/js/golap.js" type="text/javascript"></script>
<script type="text/javascript"> var fill = "";</script>
<link rel="stylesheet" type="text/css" href="<?php get_app_url(); ?>/css/golap.css" />

    <div id="subwindowframe" class="curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="subwindowtitle" style="float:left">Details</p>
                <div id="swclose" style="float: right"class="button"><a title="Close" href="javascript:hideSubwindow('subwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
            </div>
			<div id="subwindow" class="ui-widget-content"></div>
        </div>
    </div>
    <div id="smallsubwindowframe" class="curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="smallsubwindowtitle" style="float:left">Details</p>
                <div id="close" style="float: right" class="button"><a title="Close" href="javascript:hideSubwindow('smallsubwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
                <div id="popout" class="button"></div>
            </div>
            <div id="smallsubwindow" class="ui-widget-header"></div>
            <div id="messages"></div>
            <div class="panefooter">
            	<div id="dstatus" class="stattxt"></div>
            	<div id="approaching" class="approaching"></div>
            </div>
        </div>
    </div>

<?php
	$appuser = "guest";
	if ( Yii::app()->user->getId() )
		$appuser = Yii::app()->user->getId();

	echo '<input type="button" id="activeuser" style="display:none" name="'.$appuser.'" label="'.$appuser.'">';

?>

<div id="debug" style="width:100%"></div>
<?php include ("golaptoolbar.php"); // full width toolbar containing dropdown list menu options, view (map, grid, loading gif) ?>

<div id="mapping" style="width: 100%; height: 1600px">
<?php include ("golapcriteria.php"); // full width toolbar containing dropdown list menu options, view (map, grid, loading gif) ?>

<!--
-->

<script src="js/pwitabs.js" type="text/javascript"></script>
<script src="js/pwimenu.js" type="text/javascript"></script>
<script src="js/pwisubwindow.js" type="text/javascript"></script>
<script src="js/pwicriteria.js" type="text/javascript"></script>

<!-- Map container -->
<div id="visualizer" style="height: 90%; padding-left: 4px; margin: 0px 0px 0px 0px; width: 77%; float: left;">
    <div id="mapfilter" class="1curved" style="display:none">
        <div class="content curved">
                <!--p style="float:left">Details</p>
                <div id="mfclose" class="button"><a title="Close" href="javascript:hideOverlay()"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div-->
                <div id="mapfiltertabs">
                <ul>
                    <li><a href="#tabs-1">Nunc tincidunt</a></li>
                </ul>
                <div id="tabs-1">
                    <p>Proin elit</p>
                </div>
                </div>
                <div id="mfcontent"></div>
        </div>
    </div>

<!-- Grid container -->
<div id="gridcol" style="margin: 4px 0px 0px; display: none; width: 100%; float: left;">
		<table id="datagrid" style="padding: 0px"></table> 
		<div id="datagridpager"></div> 
</div>

<!-- Line View container -->
<!--div id="linecol" style="margin: 4px 0px 0px 0px; display: none; width: 100%; float: left;">
		<table id="datagrid" style="padding: 0px"></table> 
</div-->

<div id="mapcol" style="height: 90%; margin: 0px 0px 0px 0px; display: inline; width: 100%; float: left;">
    <div id="mstatus" class="stattxt"></div>
    <div id="mstatus2" class="stattxt"></div>
    <div id="map">Loading map...</div>
</div>
<div id="dashboardcol" style="margin: 0px 0px 0px 0px; display: none; width: 100%; float: left;">
    <!-- this HTML covers all layouts. The 5 different layouts are handled by setting another layout classname -->
  <div id="dashboard" class="dashboard">
    <div class="layout">
      <div class="column first column-first">
      </div>
      <div class="column second column-second"></div>
      <div class="column third column-third"></div>
    </div>
   </div>
</div>

<!-- Line View container -->

</div>

</div>
<noscript><b>JavaScript must be enabled in your browser for this page to function correctly.</b></noscript>
