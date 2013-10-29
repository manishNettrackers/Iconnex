/**
** workspace.js
**
** Functions for saving and loading user workspaces for automatic load on startup
*/


// Global for storing current selected layout
var gDashboardLayout = "";

/**
** saveWorkspace
**
** workspaceName - the name to give to the workspace
**
** Runs the Infohost workspace.xml query to save user workspace
*/
function saveWorkspace(workspaceName)
{
	var workspace_name = $('#workspaceName').val();
	$('#popup3').fadeOut();
	if(workspace_name == '')
	 return false;
	
    set_big_loading_status(true);

    userid = iconnexUser;

    // Prepare URL for workspace storage
	url = baseUrl + "/index.php?r=golap/golap/dashboardSave";
	params = "user=" + userid;
    params += "&MANUAL_user=" + userid;
    params += "&MANUAL_workspace=" + workspace_name;
    //params += "&MANUAL_menu=" + usermenu;

    // Create URL parameters for session variables
    sessct = 1;
    for ( var index in sessionParams )
    {
        if ( !sessionParams[index] || index == "false" || sessionParams[index].title == "unknown")
            continue;

        // Map session is not a proper workspace session 
        //if ( index == "map" )
            //continue;

        //if ( !sessionParams[index]["url_params"] )
            //continue;

        if ( !sessionParams[index]["title"] )
            continue;

        //sessextra = encodeURIComponent(sessionParams[index]["url_params"]);
        sessextra = encodeURIComponent("session_name=" + index);
        params += "&workspace_title_" + sessct + "=" + encodeURIComponent(sessionParams[index]["title"]);
        params += "&workspace_session_" + sessct + "=" + sessextra;

        params += get_workspace_dashboard_title(sessct, index);
        params += get_workspace_dashboard_search_settings(sessct, index);
        params += get_workspace_dashboard_tile(sessct, index);


        sessct++;
    }
    params += get_workspace_dashboard_layout();
	// Get current layout
	params += "&layout = "+$("#curDashboard").val();
    $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function() {
            set_big_loading_status(false);
        },
        error: function(xhr, desc, err) {
           // alert ( "Unable to save workspace" );
            set_big_loading_status(false);
        }
    });

}

function loadAllWorkspace()
{
	   userid = iconnexUser;
	   set_big_loading_status(true);
	   url = baseUrl + "/index.php?r=golap/golap/loadAll";
	   params = "user=" + userid;
	   $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function(data) {
            set_big_loading_status(false);
			$('#popup4').fadeIn();
			var html = '';
			for ( var index in data )
            {
				html += '<input type="button" value="'+data[index]+'" onclick="loadWorkspace('+index+')"  class="exclusive" />&nbsp;';
			}
			$('#loadDash').html(html);
        },
		error: function(xhr, desc, err) {
           // alert ( "Unable to save workspace" );
            set_big_loading_status(false);
        }
	   });
}

/**
** loadWorkspace
**
** workspaceName - the name to load
**
** Runs the Infohost workspace.xml query to fetch workspace definitions
*/
function loadWorkspace(workspaceName)
{
    set_big_loading_status(true);
    userid = iconnexUser;

    // Remove existing accordions to prepare for workspace load
    for ( var index in sessionParams )
    {
        if ( !sessionParams[index] )
            continue;

        // Map session is not a proper workspace session 
        if ( index == "map" )
            continue;


    }
    $(".accordionremove").click();

    // Prepare URL for workspace storage
	//url = baseUrl + "/protected/extensions/reportico/embedded.php";
	url = baseUrl + "/index.php?r=golap/golap/dashboardLoad";
    params = "&user=" + userid;
    params += "&MANUAL_workspace=" + "DEFAULT";
    params += "&workspace_id=" + workspaceName;

    $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function(data) {
			$('#dashcol1').parent().hide();
			$('#dashcol2').parent().hide();
			$('#dashcol3').parent().hide();
			$('#dashcol4').parent().hide();
			for ( var index in data )
            {
				if ( index == 'layout')
				{
				 	for (var ind in data['layout'])
					{
						$('#'+ind).parent().show().css("width",data['layout'][ind])
					}
				}
				//loadMenuQuery(data[index], true, false, false, false, false, false);
			}
			for ( var index in data )
            {
				loadMenuQuery(data[index], true, false, false, false, false, false);
			}
			/*alert("Hi");return false;
            for ( var index in data["data"] )
            {
                menuitem = data["data"][index]["Workspace Menu Item"];
                loadMenuQuery ( menuitem, true, data["data"][index]["Params"], data["data"][index]["Dashboard Title"], data["data"][index]["Dashboard Settings"],
                        data["data"][index]["Dashboard Layout"], data["data"][index]["Dashboard Tile"]);
            }*/
		  $('#popup4').fadeOut();       
   			set_big_loading_status(false);
        },
        error: function(xhr, desc, err) {
           // alert ( "Unable to load workspace" );
		    //$('#popup4').fadeOut();   
			location.href = baseUrl + "/index.php?r=golap/golap";    
            set_big_loading_status(false);
        }
    });

}


/*
** get_workspace_dashboard_layout
**
** Get the layout id of the current dashboard view .. do this by
* finding the first dashboard layout which is visible
*/
function get_workspace_dashboard_layout()
{
    ret = "";
    if ( gDashboardLayout != "" )
    {
        ret += "&workspace_dashboard_layout" + "=" + gDashboardLayout;
    }
    return ret;
}
/*
** get_workspace_dashboard_tile
**
** Get the current dashboard tile id for the grid session
*/
function get_workspace_dashboard_tile(sessct, session)
{
    ret = "";
    dashid = "#dash" + session;
    if ( get_session_param(session, "current_view_type") == "MAPVIEW" )
        dashid = "#dash" + "map";
    dashheadlabel = dashid + " .portlet-header .dashheadlabel";
    if ( $(dashid).length != 0 )
    {
        tile = $(dashid).parents(".dashboardtile");
        ret += "&workspace_dashboard_tile_" + sessct + "=" + $(tile).attr("id");
    }
    return ret;
}

/*
** get_workspace_dashboard_title
**
** Gets custom user dashboard title for storing in workspace
*/
function get_workspace_dashboard_title(sessct, session)
{
    ret = "";
    dashid = "#dash" + session;
    if ( get_session_param(session, "current_view_type") == "MAPVIEW" )
        dashid = "#dash" + "map";
    dashheadlabel = dashid + " .portlet-header .dashheadlabel";
    if ( $(dashid).length != 0 )
    {
        if ( $(dashid).length != 0 )
        {
            ret += "&workspace_custom_title_" + sessct + "=" + $(dashheadlabel).html();
        }
    }
    return ret;
}

/*
** get_workspace_dashboard_search_settings
**
** Gets dashboard grid filters, paging and sorting order for storing in workspace
*/
function get_workspace_dashboard_search_settings(sessct, session)
{
    ret = "";
    dashgridtag = "#dashgrid" + session;
    if ( get_session_param(session, "current_view_type") == "MAPVIEW" )
    {
        ret += "&workspace_search_settings_" + sessct + "=";
        return ret;
    }
    dashheadlabel = dashid + " .portlet-header .dashheadlabel";
    if ( $(dashgridtag).length != 0 )
    {
        x = $(dashgridtag).jqGrid('jqGridExport', "jsonstring");
        postData = $(dashgridtag).jqGrid('getGridParam', "postData");
        if ( postData )
        {
            ret += "&workspace_search_settings_" + sessct + "=" + JSON.stringify(postData);
        }
    }
    return ret;
}
