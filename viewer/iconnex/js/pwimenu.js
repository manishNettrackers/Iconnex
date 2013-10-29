/**
** pwimenu.js
**
** Extracts the menu options available to the user for selection from the drop list bos
** Responsible for handling selection of menu option and execution of report option to
** the side criteria panel or the main workspace panel
*/

var menuitems = []; // Array of current menu items
var menuitemsnum = []; // Array of current menu items with numeric key so we can pass throu in the order we want
var current_menu_session = false; // Current session id of a selected menu
var curMenu = 0;//Added By Prasenjit

/**
** getUserMenu
**
** userid - current logged on user name
** usermenu - the name of the menu to extract options for
** baseUrl - the url location of the web appllication
**
** Runs the Infohost usermenu.xml query to fetch user options
*/
function getUserMenu( userid , usermenu, baseUrl )
{
    set_big_loading_status(false);
	fetchurl = baseUrl + "/protected/extensions/reportico/embedded.php";
	//fetchparams = "criteria&template=pwi&target_menu=&project=infohost&target_format=json&xmlin=usermenu.xml&clear_session=1&execute_mode=EXECUTE";
	fetchparams = "criteria&template=pwi&target_menu=&project=" + yii_menu_project + "&target_format=json&xmlin=usermenu.xml&execute_mode=EXECUTE";
    fetchparams += "&user=" + userid;
    fetchparams += "&MANUAL_user=" + userid;
    //fetchparams += "&MANUAL_menu=" + usermenu;

    $.ajax({
        type: 'GET',
        url: fetchurl,
        data: fetchparams,
        dataType: 'json',
        success: function(data, status) {
            a = data;
            html = "";
            current_menu_session = false; // Current session id of a selected menu
            ct = 0;
            numitems = data["data"].length;
            while ( ct < numitems )
            { 
                nm =  data["data"][ct]["App Name"];
                menuitemsnum[ct] = [];
                menuitemsnum[ct]["name"] = nm;
                
                menuitems[nm] = [];
                menuitems[nm]["title"] = data["data"][ct]["App Name"];
                menuitems[nm]["menu"] = data["data"][ct]["Menu Name"];
                menuitems[nm]["url"] = data["data"][ct]["App Url"];
                menuitems[nm]["hasmap"] = parseInt(data["data"][ct]["Has Map"]);
                menuitems[nm]["hasline"] = parseInt(data["data"][ct]["Has Line"]);
                menuitems[nm]["hasreport"] = parseInt(data["data"][ct]["Has Report"]);
                menuitems[nm]["haschart"] = parseInt(data["data"][ct]["Has Chart"]);
                menuitems[nm]["autorefresh"] = parseInt(data["data"][ct]["Autorefresh"]);
                menuitems[nm]["autorun"] = parseInt(data["data"][ct]["Autorun"]);
                menuitems[nm]["refreshxml"] = data["data"][ct]["Refresh Xml"];
                menuitems[nm]["runlocation"] = data["data"][ct]["Run Location"];
                menuitems[nm]["originalxml"] = false;
                menuitems[nm]["counter"] = ct;
                arr = menuitems[nm]["url"].match(/xmlin=[A-Za-z_]*.xml/) ;
                if ( arr )
                {
                    menuitems[nm]["originalxml"] = arr[0].substr(6);
                }
                menuitems[nm]["menuautorun"] = parseInt(data["data"][ct]["Menu Autorun"]);
                menuitems[nm]["showaccordion"] = parseInt(data["data"][ct]["Show Accordion"]);
                menuitems[nm]["showbuttons"] = parseInt(data["data"][ct]["Show Buttons"]);
                menuitems[nm]["username"] = data["data"][ct]["User Name"];
                ct++;
            }
            optionct = 0;
            levelno = 0;
            lastmenu ="";

            menuhtml = "";
            
            itemct = 0;
            menuct = ct;
            while ( itemct < menuct )
            { 
                ct = menuitemsnum[itemct]["name"];

                if ( itemct++ == 0 )
                    $("#rgbdriver").attr("innerHTML",menuitems[ct]["username"]);

                thismenu = menuitems[ct]["menu"];

                if ( lastmenu != thismenu && lastmenu != "" )
                {
                    menuhtml += "</ul></div>";
                    levelno --;
                }
                if ( lastmenu != thismenu )
                {
                    menuhtml += "<h4 class=\"accordion-header\">" + menuitems[ct]["menu"] + "</h4><div id="+ menuitems[ct]["menu"] +" class=\"accordion-content\"><ul class=\"submenu1\">";
                    levelno++;
                }
                //menuhtml += "<li class=\"jdmenu_option\" onlick=\"handleUserMenuChange(this);\">" + menuitems[ct]["title"] + "</li>";
                menuhtml += "<li class=\"jdmenu_option\" id= "+ menuitems[ct]["title"] +"><a href='javascript:void(0)'>"+ menuitems[ct]["title"] + "</a></li>";

                //html = "<option>" + menuitems[ct]["title"] + "</option>";

                //$("#adminmenu").append(html);
                //$(".jdmenu_level1").append(menuhtml);


                $("#accordiontitle").css("display", "none");
                if ( !menuitems[nm]["showaccordion"] )
                {
                    $("#accordion").css("display", "none");
                    $("#accordiontitle").css("display", "none");
                    $("#criteriacol").css("border", "none");
                }    

                if ( menuitems[nm]["showbuttons"] )
                {
                    a = '<button label=\"' + menuitems[ct]["title"] + '\" name=\"' + menuitems[ct]["title"] + '\" class=\"menubutton\" id=\"menubutton_' + optionct + '\">' + menuitems[ct]["title"] + '</button><br>';
            	    $('#menubuttons').append('<button label=\"' + menuitems[ct]["title"] + '\" name=\"' + menuitems[ct]["title"] + '\" class=\"menubutton\" id=\"menubutton_' + optionct++ + '\">' + menuitems[ct]["title"] + '</button><br>');
            	    $('#menubuttons').css("display", "inline");
                }
                if ( menuitems[nm]["menuautorun"] )
                {
                    loadMenuQuery ( menuitems[ct]["title"], menuitems[ct]["autorefresh"], false, false, false, false, false );
                    $("#selectmenu").css("display", "none");
                }

                lastmenu = thismenu;
            }
            menuhtml += "</ul><li>";
            $(".jd_menu").append(menuhtml);
            //$('ul.jd_menu').jdMenu();
            $(document).bind('click', function() {
                $('ul.jd_menu ul:visible').jdMenuHide();
            });

            // Menu loaded - load workspace
            //loadWorkspace("DEFAULT");

            set_big_loading_status(false);
        },
        error: function(xhr, desc, err) {
            alert ( "Unable to load user menu" );
        }
    });

}

/**
** getMenuItemUrl
**
** Returns link full URL link for a a dropdown options 
** selection
**
*/
function getMenuItemUrl ( baseurl, runlocation )
{
    template = "pwi";
    if ( runlocation == "FULLSCREEN" )
        template = "fullscreen";
    url =  "protected/extensions/reportico/embedded.php?r=" 
    url +=  baseurl;
    url +=  "&execute_mode=PREPARE";
    url +=  "&user=" + iconnexUser;
    url +=  "&template=" + template;
    url +=  "&linkbaseurl=protected/extensions/reportico/run.php";
    return url;
}


/**
** handleUserMenuChange
**
** User seleced a different accordion or button panel .. load the relevant
** report criteria pane
**
*/
function handleUserMenuChange(label)//(options) //Modified By Prasenjit
{
	//var idx = options.selectedIndex;
	//var label = options[idx].text;
	//var label = options[0].innerHTML; //Modified By Prasenjit
    loadMenuQuery(label, false, false, false, false, false, false);
}

/**
** loadMenuQuery
**
** Takes the passed in menucode which identifies the drop down options
** in the database and runs a report query to fetch the menuoptions
** in json format and then populate the dropdown
**
*/
function loadMenuQuery(menuitem, forceAutoRefresh, passParameters, passCustomTitle, passSearchSettings, passDashboardLayout, passDashboardTile )
{
	//var idx = options.selectedIndex;
	//var label = options[idx].text;
	var url;


    if ( !menuitem || menuitem == "unknown" )
        return;

    //if ( !menuitems || menuitems.length == 0 )
        //return;

    runlocation = menuitems[menuitem]["runlocation"];
    runurl = menuitems[menuitem]["url"];
    url = getMenuItemUrl ( runurl, runlocation );
	
	url += "&clear_session=1";

    // Add any extra parameters passed from outside normally
    // when loading menu item automatically from workspace
    // where certain user criteria needs to be maintained
    if ( passParameters )
        url += passParameters;

    // The Query URL may include "<USER>" paramter which must be replaced with the user name
    url = url.replace(/<USER>/, iconnexUser);

	set_loading_status (true);
    wct = 0;

    panelno = 0;
	looping = 1;
	var ct =0;
	while ( looping )
	{
		var lookfor = $('#accordionframe' + ct);
		if ( lookfor.length == 0 ) 
		{
			panelno = ct;
			looping = 0;
		}
		ct++;
	}
	

    // Use store custom user title
    if ( passCustomTitle )
	    $('#accordion').append('<h3 class=\"accordionheader\" id=\"accordionheader' + panelno + '\" href=\"#\">' + '<a href=\"#\" tabindex=\"-1\">' + passCustomTitle + '<input type=\"button\" value=\"X\" class=\"accordionremove\" style=\"font-size: 10pt\" id=\"accordionremove' + panelno + '\">' + '</a></h3>');
    else
	    $('#accordion').append('<h3 class=\"accordionheader\" id=\"accordionheader' + panelno + '\" href=\"#\">' + '<a href=\"#\" tabindex=\"-1\">' + menuitem + '<input type=\"button\" value=\"X\" class=\"accordionremove\" style=\"font-size: 10pt\" id=\"accordionremove' + panelno + '\">' + '</a></h3>');
		
	$('#accordion').append('<div class=\"accordionframe\" style=\"margin-top: 4px;border: none; width: auto\" id=\"accordionframe' + panelno + '\"></div>').accordion('destroy').accordion({animated: false});
	
	$('#accordionframe' + panelno).append('<div class=\"accordiondetail\" style=\"height: 100%; width: 100%\" id=\"accordiondetail' + panelno + '\"></div>');
	
	$('#accordiondetail' + panelno).attr('innerHTML', '<input type=\"checkbox\" class=\"accordionshow\" style=\"display: none\" id=\"accordionshow' + panelno + '\"><div id=\"accordioncrit' + panelno + '\" class=\"accordioncrit\" style=\"height: 100%; width: 100%\"></div>');
	
	 while ( wct < 1 )
    {
		
	$.ajax(
	{
		type: "GET",
		url: url,
        async: false,
		success: function(result)
		{
			// Set up any pre-sending stuff like initializing progress indicators
			set_loading_status (false);

			$('#accordionframe' + panelno ).accordion('resize');
			$('#accordion' ).css('border', 'none');
			$('#accordionframe' + panelno ).css('border', 'none');
			$('#accordioncrit' + panelno ).css('border', 'none');
 			
			curMenu = panelno ; //Added By Prasenjit
			
			fill = '#accordioncrit' + panelno;
			
			$(fill).append('<div class="runlocation" id="runlocation' + panelno + '">' + runlocation + "</div>" );
			$(fill).find('.runlocation').hide();
			$(fill).append('<div class="spformcontainer" style="width:100%"></div>' );

            $(fill).find(".spformcontainer").html(result);
			
      		var session = get_golap_session('#accordiondetail' + panelno);
            if ( !passDashboardTile )
                current_menu_session = session;
			init_session_params(session, menuitem);

            if ( passCustomTitle )
                set_session_param(session, "customTitle", passCustomTitle);
            if ( passSearchSettings )
                set_session_param(session, "searchSettings", passSearchSettings);
            if ( passDashboardTile )
                set_session_param(session, "dashboardTile", passDashboardTile);

            // If menu item loaded as part of a dashboard load then automatically run ..
            if  ( passDashboardTile )
            {
			    set_session_param ( session, 'autorun' , true );
            }

			
            if ( runlocation == "FULLSCREEN" )
            {
                //help = $(fill).find('.swPrpHelp');
                //helptxt = help[0].innerHTML;
                //$(help).html("");
                //newfill = $(fill).html();
                //helptxt = "Run the report";

                // Move help information into criteria panel
                content = '<div style="margin-left:20px; margin-right:20px;">';
                content += '<input type="inline" value="' + session + '" name="session_name" style="display:none">';
                //content += helptxt;
                content += "Follow the instructions in the main report panel";
                content += '</div>';
			    $(fill).html(content);

                // Only populate the report panel with the query output if
                // no existing report accordion selections
                fullscreenct = 0;
                a = $("#criteriacol").find('.accordiondetail').each( function()  
                { 
                    tmpsession = get_golap_session("#" + $(this).attr("id"));
                    if ( get_session_param ( tmpsession, 'runlocation' ) == "FULLSCREEN" && tmpsession != session )
                        fullscreenct++;
                });
				//$('#reportcol').show(); //Changed By Prasenjit
                if ( !fullscreenct )
                    $("#reportcol").html(result);
                set_session_param(session, "runhtml", result);
                set_session_param(session, "runhtml", result);
            }

					
			$('#accordion').accordion('destroy').accordion({header: 'h3', 
                clearStyle: true,
                change: function ( event, ui )
                {
                    // On accordion change store current full screen html results/content
                    // beloging to the accordion we are leaving against the session
                    // so we can get back to it
                    olddet = ui.oldContent;

                    var old_session = get_golap_session("#reportcol");
                    if ( old_session )
                    {
                        if ( get_session_param ( old_session, 'runlocation' ) == "FULLSCREEN" )
                        {
                            set_session_param ( old_session, "runhtml", $("#reportcol").attr("innerHTML") );
                        }
                    }

                    // Populate full screen report pane with stored HTML
                    newdet = ui.newContent;
                    a = newdet.find('.accordiondetail');
                    if ( a.length > 0 ) 
                    {
                        var session = get_golap_session("#" + a[0].id);
                        if ( !passDashboardTile )
                            current_menu_session = session;
                        if ( get_session_param ( session, 'runlocation' ) == "FULLSCREEN" )
                        {
                            $("#reportcol").html(get_session_param ( session, "runhtml"));
                            $("#showreport").click();
                            setDatePickers();
                        }
                        else
                        {
                            if ( get_session_param ( session, 'current_view_type' ) == "MAPVIEW" )
                            {
                                showDashboard();
                                //initGOLAPFilters(session, get_session_param ( session, 'hasline' ));
                                showGOLAPFilters(session, false);
                                if ( dashboardIsMaximised() )
                                    maximiseDashboardWidget("map");
                                showDashboard();
                            }
                            else if ( get_session_param ( session, 'current_view_type' ) == "LINEVIEW" )
                            {
                                showDashboard();
                                initGOLAPFilters(session, get_session_param ( session, 'hasline' ));
                                showGOLAPFilters(session, false);
                            }
                            else if ( get_session_param ( session, 'current_view_type' ) == "DASHBOARD" )
                            {
                                if ( dashboardIsMaximised() )
                                    maximiseDashboardWidget(session);
                                showDashboard();
                            }
                            else if ( get_session_param ( session, 'current_view_type' ) == "REPORT" )
                            {
                                if ( dashboardIsMaximised() )
                                    maximiseDashboardWidget(session);
                                showDashboard();
                            }
                            else if ( get_session_param ( session, 'current_view_type' ) == "REPORTFULLSCREEN" )
                            {
                                showDashboard();
                            }
                            else if ( get_session_param ( session, 'current_view_type' ) == "GRID" )
                            {
                                //maximiseDashboardWidget(session);
                                showDashboard();
                            }
			                else 
                            {
                                showDashboard();
                            }
                            
                            // When 
                        }
                    }
                }
            });
			frm = $(fill).closest('.accordionframe');
			frm = frm.prev();
			frm.click();
			setDatePickers();
            initstopmessage();
            showPanelCriteriaButtons(session);
			$("#popup1").show();
							
			if ( get_session_param ( session, 'autorun' ) && get_session_param ( session, 'hasmap' ) )
            {
				$('#accordiondetail' + panelno).find('.swPlotButton').each( function(index)  { $(this).click(); });	
            }
			else if ( get_session_param ( session, 'autorun' ) && get_session_param ( session, 'hasline' ) )
            {
				$('#accordiondetail' + panelno).find('.swLineButton').each( function(index)  { $(this).click(); });	
            }
			else if ( get_session_param ( session, 'autorun' ) && get_session_param ( session, 'hasgrid' ) )
            {
				if( forceAutoRefresh )
				{
					$("#popup1").hide();
					$('#accordiondetail' + panelno).find('.swDataButton').each( function(index)  { $(this).click(); });	
				}
            }
			else if ( get_session_param ( session, 'autorun' ) && get_session_param ( session, 'haschart' ) )
            {
				$('#accordiondetail' + panelno).find('.swChartButton').each( function(index)  { $(this).click(); });	
            }
			else if ( get_session_param ( session, 'autorun' ) && get_session_param ( session, 'hasreport' ) )
            {
				$('#accordiondetail' + panelno).find('.swReportButton').each( function(index)  { $(this).click(); });	
            }
			else
			{
				if( forceAutoRefresh )
				{
					$("#popup1").hide();
					$('#accordiondetail' + panelno).find('.swDataButton').each( function(index)  { $(this).click(); });	
				}
			}
            if ( runlocation == "FULLSCREEN" )
            {
				$("#popup1").hide();
                $("#showreport").click();
            }
			$('#accordionframe' + panelno ).css('width', 'auto');
		},
		error: function(x, e)
		{
			set_loading_status (false);
			//alert(x.readyState + " "+ x.status +" "+ e.msg);   
			alert(x.readyState + " "+ x.status +" "+ "Unable to retrieve requested data - timeout");   
		}
	});
    wct++;
    }

	document.getElementById('toolbarform').reset();
};

function showPanelCriteriaButtons(session)
{
    if ( !get_session_param ( session, 'hasmap' ) )
    {
        $('#accordiondetail' + panelno).find('.swPlotButton').each( function(index)  { $(this).css('display', 'none'); });	
        $('#accordiondetail' + panelno).find('.swAutoZoom').each( function(index)  { $(this).css('display', 'none'); });	
        $('#accordiondetail' + panelno).find('.labelMapPair').each( function(index)  { $(this).css('display', 'none'); });	
        $('#accordiondetail' + panelno).find('.swAutoCentre').each( function(index)  { $(this).css('display', 'none'); });	
    }
	else
	{
		if ( get_session_param(session, "autorecentre" )  )
			$('#accordiondetail' + panelno).find('.swAutoCentre').each( function(index)  { $(this).attr('checked', true); });	
		if ( get_session_param(session, "autozoom" )  )
			$('#accordiondetail' + panelno).find('.swAutoZoom').each( function(index)  { $(this).attr('checked', true); });	
	}
	if ( !get_session_param ( session, 'hasreport' ) )
		$('#accordiondetail' + panelno).find('.swReportButton').each( function(index)  { $(this).css('display', 'none'); });	
	if ( !get_session_param ( session, 'haschart' ) )
		$('#accordiondetail' + panelno).find('.swChartButton').each( function(index)  { $(this).css('display', 'none'); });	
	if ( !get_session_param ( session, 'hasgrid' ) )
		$('#accordiondetail' + panelno).find('.swDataButton').each( function(index)  { $(this).css('display', 'none'); });	
	if ( !get_session_param ( session, 'hasline' ) )
		$('#accordiondetail' + panelno).find('.swLineButton').each( function(index)  { $(this).css('display', 'none'); });	
	if ( !get_session_param ( session, 'hasline' ) && !get_session_param ( session, 'hasmap' ) ) 
		$('#accordiondetail' + panelno).find('.showfiltermap').each( function(index)  { $(this).css('display', 'none'); });	
}
