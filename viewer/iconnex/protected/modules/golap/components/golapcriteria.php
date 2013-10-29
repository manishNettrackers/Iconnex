<script>
$(document).ready(function(){
	$('.swDataButton').live("click",function(){
		$(".search_open").hide();
	});
});
</script>
<?php
    Yii::app()->clientScript->registerScript('golapcriteriaEvents',<<<EOD

	$('#critform').live('submit', function(event){ 
		return false;
	});
	$('.swPrpCritExpandButton,.swPrpCritReturnToExpandButton').live('click', function() {
            submitform = $(this).closest("#critform");
            outputcontainer = $(submitform).find("#swPrpExpandCell");
                outputcontainer = outputcontainer[0];
       		//$(outputcontainer).addClass("loading");
       		//$("#mapping").addClass("loading");
            set_big_loading_status(true);
            action = $(submitform).attr('action').replace("run.php","partial.php");
       		$.ajax({
           			type: 'GET',
            		url: action,
            		data: $(submitform).serialize() + '&partial_template=fullexpand&execute_mode=PREPARE&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
                        set_big_loading_status(false);
       					//$("#mapping").removeClass("loading");
       					//$(outputcontainer).removeClass("loading");
                 		$(outputcontainer).attr('innerHTML',data);
           			},
           			error: function(xhr, desc, err) {
                        set_big_loading_status(false);
       					//$("#mapping").removeClass("loading");
       					//$(outputcontainer).removeClass("loading");
       					$(outputcontainer).attr('innerHTML','Error in lookup option');
          			}
       		});
		return false;
	});
	$('.swPrpCritExpandButtonReturn').live('click', function() {
            submitform = $(this).closest("#critform");
            outputcontainer = $(this).closest("#reportcol");
       		$(outputcontainer).addClass("loading");
       		$("#mapping").addClass("loading");
            action = $(submitform).attr('action').replace("run.php","partial.php");
       		$.ajax({
           			type: 'GET',
            		url: action,
            		data: $(submitform).serialize() + '&partial_template=fullscreen&execute_mode=PREPARE&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
       					$("#mapping").removeClass("loading");
       					$(outputcontainer).removeClass("loading");
                 		$(outputcontainer).attr('innerHTML',data);
           			},
           			error: function(xhr, desc, err) {
       					$("#mapping").removeClass("loading");
       					$(outputcontainer).removeClass("loading");
       					$(outputcontainer).attr('innerHTML','Error in lookup option');
          			}
       		});
		return false;
	});
	$('.swPrpSidePanelCritExpandButton').live('click', function() {
       		//$("#swPrpExpandCell").addClass("loading");
            formcontainer = $(this).closest(".spformcontainer");
            submitform = $(this).closest("#critform");
            set_big_loading_status(true);
       		$.ajax({
           			type: 'GET',
            		url: $(submitform).attr('action'),
            		data: $(submitform).serialize() + '&partial_template=pwi&execute_mode=PREPARE&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
                        set_big_loading_status(false);
       					//$("#swPrpExpandCell").removeClass("loading");
                 		//$(formcontainer).attr('innerHTML',data); //Changes By Prasenjit
						$(formcontainer).html(data);
           			},
           			error: function(xhr, desc, err) {
       					//$("#swPrpExpandCell").removeClass("loading");
                        set_big_loading_status(false);
       					//$("#swPrpExpandCell").attr('innerHTML','Error in lookup option');//Changes By Prasenjit
						$("#swPrpExpandCell").html('Error in lookup option');
          			}
       		});
		return false;
	});
	$('.swPrpReturnFromExpand').live('click', function() {
       		//$("#swPrpExpandCell").addClass("loading");
            formcontainer = $(this).closest(".spformcontainer");
            submitform = $(this).closest("#critform");
            accordion = $(this).closest(".accordiondetail");
            session = get_golap_session("#" +$(accordion).attr("id"));
            set_big_loading_status(true);
       		$.ajax({
           			type: 'GET',
            		url: $(submitform).attr('action'),
            		data: $(submitform).serialize() + '&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
                        set_big_loading_status(false);
       					//$("#swPrpExpandCell").removeClass("loading");
                 		//$(formcontainer).attr('innerHTML',data); //Modified By Prasenjit
						$(formcontainer).html(data);
                        showPanelCriteriaButtons(session);
                        setDatePickers();
           			},
           			error: function(xhr, desc, err) {
       					//$("#swPrpExpandCell").removeClass("loading");
                        set_big_loading_status(false);
       					//$("#swPrpExpandCell").attr('innerHTML','Error in lookup option'); //Modified By Prasenjit
						$("#swPrpExpandCell").html('Error in lookup option');
          			}
       		});
		return false;
	});

    // ------------------------------------------------------------------
    // Function to handle custom button actions from grid
    function gridaction ( ingrid, intype )
    {
        // Build custom action to affect rows
        // by passing to standard editurl the ids, the action type
        url = $(ingrid).jqGrid('getGridParam','editurl');
        ids = $(ingrid).jqGrid('getGridParam','selarrrow');

        if ( !ids )
        {
            alert ("Please select one or more rows to continue");
            return;
        }

        idkey = "pub_ttb_id";

        url += "&ids[]=";
        ctr =1;
        for ( var id in ids )
        {
            if ( ctr > 1 )
                url += ",";
            row = $(ingrid).jqGrid('getRowData',ids[id]);
            url += row[idkey];
            ctr ++;
        }

        url += "&oper=" + intype;

        if ( intype == "Cancel" )
            url += "&Cancel=Yes";
        if ( intype == "Uncancel" )
            url += "&Cancel=No";

        url += "&u=8";
        

        $.ajax(
        {
            type: "GET",
            url: url,
            //data: dataString,
            dataType: "json",
            success: function(result)
            {
    			for ( var id in ids )
    			{
    				if ( intype == "Cancel" )
        				row = $(ingrid).jqGrid('setRowData',ids[id],{ active_status: "<img src=\"images/canctrip.bmp\">"});
    				if ( intype == "Uncancel" )
        				row = $(ingrid).jqGrid('setRowData',ids[id],{ active_status: "<img src=\"images/bus.bmp\">"});
    			}
           		alert("Operation Successful");   
            },
            error: function(x, e)
            {
				set_loading_status (false);
           		alert("Operation Failed : " + x.readyState + " "+ x.status +" "+ e.msg);   
       		}
        });
    }

    // ------------------------------------------------------------------
    // User presses side panel button to trigger accordion header click
    $('.menubutton').live('click', function(event) {
        arr = this.id.split("_");
        if ( arr.length > 1 )
        {   
            panelno = arr[1];
            $("#accordionheader" + panelno).click();
        }
    });

    // ------------------------------------------------------------------
    // User presses filter button in criteria panel
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
    

    // ------------------------------------------------------------------
    // User presses a link within a report, grid, map item summary to bring up further info
    // in a subwindow
    $('.expandwindow').live('click', function(event) {
		showSubwindow(this, "subwindow");
		return false;
	});

    // ------------------------------------------------------------------
    // User presses a link within a report, grid, map item summary to bring up further info
    // in a subwindow
    $('.webstopwindow').live('click', function(event) {
		showSubwindow(this, "webstopwindow");
		return false;
	});

    // ------------------------------------------------------------------
    // User presses a link within a report, grid, map item summary to bring up further info
    // in a subwindow
    $('.clickinfowindow').live('click', function(event) {
		showSubwindow(this, "smallsubwindow");
		return false;
	});

    // ------------------------------------------------------------------
    // User presses Auto centre checkbox
    $('.mappingAutoCentre').live('click', function() {
        g_mappingAutoCentre = this.checked;
	});

    // ------------------------------------------------------------------
    // User presses Auto zoom checkbox
    $('.mappingAutoZoom').live('click', function() {
        g_mappingAutoZoom = this.checked;
	});

    // ------------------------------------------------------------------
    // User presses Auto centre checkbox
    $('.swAutoCentre').live('click', function() {
   		var session = get_golap_session('.accordiondetail');
		set_session_param ( session, "autorecentre", this.checked );
	});

    // ------------------------------------------------------------------
    // User presses Auto zoom checkbox
    $('.swAutoZoom').live('click', function() {
   		var session = get_golap_session('.accordiondetail');
		set_session_param ( session, "autozoom", this.checked );
	});

    // ------------------------------------------------------------------
    // User presses Auto refresh checkbox
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

    // ------------------------------------------------------------------
    // User clicks to activate an accordion item
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

    // ------------------------------------------------------------------
    // User clicks to delete an accordion
    // Remove the session and try to
    // up the web view to reflect this
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

        // If its a full screen report we are clearing, clear the report pane
        // but first save the contents of the existing pane
        var old_session = get_golap_session("#reportcol");
        if ( old_session )
        {
            if ( get_session_param ( old_session, 'runlocation' ) == "FULLSCREEN" )
            {
                set_session_param ( old_session, "runhtml", $("#reportcol").attr("innerHTML") );
            }
        }
        runtype = get_session_param(session, "runlocation" );
        if ( runtype == "FULLSCREEN" )
            $("#reportcol").attr("innerHTML", "" );

        deleteMarkers(session);
        deleteDashboardWidget(session);
        sessionParams[session] = false;
        //sessionParams.splice(sessionParams.indexOf(session), 1);

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
        //$('#accordionframe' + idno).
                //add($('#accordionheader' + idno)).
                //fadeOut('slow',function(){
                //$(this).remove();
                //});
        $('#accordiondetail' + idno).remove();
        $('#accordionheader' + idno).remove();
        $('#accordionframe' + idno).remove();
    }));

    // ------------------------------------------------------------------
    // User runs a query in an accordion item which here generates AJAX call
    // to populate the grid/map/dashboard
    $('#criteriaform :submit, #critform :submit, #fullScreenReturn, #fullScreenGo').live('click', function(event) {
             target = this;
        var me = this;
        set_loading_status (true);
		userid=$("#activeuser");
		userid=userid[0].name;

        var myform = $(this).closest("#critform");
	    var formaction =  $(myform)[0].action;
	    var dataString = $(myform).serialize();
	    dataString = dataString + "&r=golap/golap/criteria";
	    dataString = dataString + "&" + me.name + "=1";

        if ( me.name == "fullScreenGo" || me.name == "fullScreenReturn" ||  me.name == "fullScreenCSV" ||  me.name == "fullScreenPDF" )
   			var session = get_golap_session('#reportcol');
		else
		{
        	var a = $(myform).find('input');
        	var session = "";
        	a.each(function(index)  {
                if ( this.name == "session_name" )
                {
                    session = this.value;
                    return;
                }
                var i = this;
            });
		}


        autorefresh = false;
        if ( sessionParams[session].autorefresh )
        {
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
            ck[0].checked = true;
            autorefresh = true;
        }


        // Grid View Report Pressed
	    if ( me.name == "submitPrepareLine" )
	    {
            // If weve just run a grid report then show the unmaximised dashboard view
            $("#dashmaximised").hide();
            $("#dashboardview").show();
            $( this ).parents( ".portlet:first" ).removeClass( "portlet-minimized" );
            $( this ).parents( ".portlet:first" ).removeClass( "portlet-maximized" );
            $( ".portlet minimiser" ).removeClass( "portlet-maximized" );
            $( ".portlet maximiser" ).removeClass( "ui-icon-newwin" );

            makeWayForDashboard(session);

            set_session_param(session, "current_view_type", "GRID");
			userid=$("#activeuser");
			userid=userid[0].name;
			dataString = dataString + "&target_format=json&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
            set_session_param(session, "url_params", dataString);
            set_loading_status (false);
		    retval = getLineOutput(this.className, session, formaction, dataString, autorefresh, false, "JSON", false );
            set_session_param(session, "current_view_type", "DASHBOARD");
	    }
	    else if ( me.name == "submitPrepareData" )
	    {
            // If weve just run a grid report then show the unmaximised dashboard view
            $("#dashmaximised").hide();
            $("#dashboardview").show();
            $( this ).parents( ".portlet:first" ).removeClass( "portlet-minimized" );
            $( this ).parents( ".portlet:first" ).removeClass( "portlet-maximized" );
            $( ".portlet minimiser" ).removeClass( "portlet-maximized" );
            $( ".portlet maximiser" ).removeClass( "ui-icon-newwin" );

            makeWayForDashboard(session);

            set_session_param(session, "current_view_type", "GRID");
			userid=$("#activeuser");
			userid=userid[0].name;
			dataString = dataString + "&target_format=jquerygrid&template=pwi&execute_mode=EXECUTE&submitPrepare=1&user=" + userid + "&dbview=" + "tripcancel&view=";
            set_session_param(session, "url_params", dataString);
            set_loading_status (false);
		    retval = getGridOutput(this.className, session, formaction, dataString, autorefresh, false, "JQUERYGRID", false );
            if ( session )
                plot_chart(session);
            set_session_param(session, "current_view_type", "DASHBOARD");
	    }
        else if ( me.name == "fullScreenGo" || me.name == "fullScreenReturn" ||  me.name == "fullScreenCSV" ||  me.name == "fullScreenPDF" )
	    {
            // Full Screen Report
			outputType = "HTML";
			autorefresh = false;
			if ( me.name == "fullScreenCSV" )
			{
				outputType = "CSV";
		    	dataString = dataString + "&target_format=CSV&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    		formaction =  "protected/extensions/reportico/embedded.php";
			}
			else
			if ( me.name == "fullScreenPDF" )
			{
				outputType = "PDF";
		    	dataString = dataString + "&target_format=PDF&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    		formaction =  "protected/extensions/reportico/embedded.php";
			}
			else
			if ( me.name == "fullScreenGo" )
			{
		    	dataString = dataString + "&target_format=HTML&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    		formaction =  "protected/extensions/reportico/embedded.php";
			}
			else
			{
		    	dataString = dataString + "&target_format=HTML&execute_mode=PREPARE&user=" + userid;
	    		formaction =  "protected/extensions/reportico/embedded.php";
			}
		    retval = getReportFullScreen(this.className, session, formaction, dataString, false, false, outputType);
            set_loading_status (false);
            set_session_param(session, "url_params", dataString);
            set_session_param(session, "current_view_type", "REPORTFULLSCREEN");
	    }
        else if ( me.name == "submitPrepareReport" )
	    {
            // Dashboard Text Report
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=HTML&hide_output_text=0&hide_output_graph=1&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    	formaction =  "protected/extensions/reportico/run.php";
		    retval = getReportOutput(this.className, session, formaction, dataString, autorefresh, false);
            set_loading_status (false);
            set_session_param(session, "current_view_type", "DASHBOARD");
            set_session_param(session, "url_params", dataString);
	    }
        else if ( me.name == "submitPrepareChart" )
	    {
            // Chart Graph View Pressed
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=HTML&hide_output_text=1&target_show_graph=1&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    	formaction =  "protected/extensions/reportico/run.php";
		    retval = getReportOutput(this.className, session, formaction, dataString, autorefresh, false);
            set_loading_status (false);
            set_session_param(session, "current_view_type", "DASHBOARD");
            set_session_param(session, "url_params", dataString);
	    }
        else if ( me.name == "submitPrepare" )
	    {
            if ( this.className != "swLineButton" )
            {
                makeWayForDashboard("map");
            }

            // Line View
	    	//$('#gridcol').css('display', 'none');
	    	//$('#map').css('display', 'inline');
            ck = $(this).closest(".accordiondetail").find(".accordionshow");
            ck[0].checked = true;
            ck = $(this).closest(".accordiondetail").find(".swAutoRefresh");
			autorefresh = false;
			if ( ck )
				if ( ck[0].checked )
					autorefresh = true;
		    dataString = dataString + "&target_format=json&execute_mode=EXECUTE&submitPrepare=1&user=" + userid;
	    	formaction =  "protected/extensions/reportico/run.php";
            if ( this.className == "swLineButton" )
                initializeDashboardLine();
		    retval = getMapOutput(this.className, session, formaction, dataString, false, false, "MAP", false);
            if ( this.className == "swLineButton" )
                set_session_param(session, "current_view_type", "LINEVIEW");
            else
                set_session_param(session, "current_view_type", "MAPVIEW");
            set_session_param(session, "url_params", dataString);
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
EOD
,CClientScript::POS_READY);

?>

<div id="criteriacol" class="ui-widget-content" style="float: left">
	
  <div>
    <?php include ("golaptoolbar.php"); // full width toolbar containing dropdown list menu options, view (map, grid, loading gif) ?>
    
  </div>
  <!--div id="results" class="list">
                    <div style="font-size:medium">
                        <br/>Select a mapping visualization from the above options
                    </div>
                </div-->
  <div id="accordiontitle" class="ui-widget-header ui-state-active"> Query Control Panel </div>
  <div id="menubuttons" class="ui-state-normal" style="display: none"> </div>
</div>
