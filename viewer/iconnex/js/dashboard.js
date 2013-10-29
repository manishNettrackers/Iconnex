var dashboard = false;

var maphtml = "";
var maximisedDashboardSource = null; // Store location portlet shoudl return to when unmaximised


/*
** Indicates whether one of the dashboard tiles is maximised
*/
function dashboardIsMaximised()
{
    if ( maximisedDashboardSource != null )
        return true;
    else
        return false;
}
    

/*
** On running a dashboard query, will return dashboard to unmaximised mode so that
** the results will appear in the dashboard. This is necessary if another view 
** is maximised and all other views are hidden. Unless of course the query relates to 
** a maximised view in which case leave that showing and refresh the results in that view
*/
function makeWayForDashboard( session )
{
            // Only maximise if not already maximised
            if ( maximisedDashboardSource == null )
            {
                showDashboard();
                return;
            }

            id = "dash" + session;
            if ( !$("#" + id).length )
            {
                showDashboard();
                $( ".portlet-maximized .portlet-header .maximiser" ).click();
                refreshDashboardWidgets();
            }
            else if ( $("#" + id).length && ! $("#" + id).is(":visible"))
            {
                showDashboard();
                $( ".portlet-maximized .portlet-header .maximiser" ).click();
                refreshDashboardWidgets();
            }
}

/*
*/

/*
** Displays the current views in the selected layout
** if the layout is blank then just select the first one
*/
function selectDashboardLayout( layout )
{
    // Set the currently selected dashboard to the first  
    if ( gDashboardLayout == "" )
    {
        gDashboardLayout = $("#dashboardview .dashboardlayout:first").attr("id");
    }

    // Used first dashboard if blank
    if ( layout == "" )
    {
        layout = $("#dashboardview .dashboardlayout:first").attr("id");
    }

    // Pass through each of the tiles in the currently selected grid and move the contents to the new layout
    $("#" + gDashboardLayout).find(".dashboardtile").each(function(){
        if ( $(this).html != "" )
        {
            tileid = $(this).attr("id");
            target = $("#" + layout).find("#" + tileid);
            $(this).children(".portlet:first").appendTo($(target));
        }
    });

    // Now hide the current layout and show the new on
    $("#" + gDashboardLayout).hide();
    $("#" + layout).show();

    // Now pass through each view and fit them to their tiles
    for ( var index in sessionParams )
    {
        sizeDashboardGridToFitParent (index);
        sizeDashboardMapToFitParent(index);
    }

    gDashboardLayout = layout;
}

/*
** Search through the dashboard to find a free tile to place a view. 
** If the workspace defines the tile then use that, if its a map then try to find
** a div defined as a map view others find the first empty one
*/
function findFreeGridTileForView( session )
{
    workspaceTile = get_session_param(session, "dashboardTile");

    // Set the global selected dashboard to the first one if not defined
    if ( gDashboardLayout == "" )
    {
        gDashboardLayout = $("#dashboardview .dashboardlayout:first").attr("id");
    }

    // Is the tile defined in the workspace .. if so use that
    useTile = null;
    if ( workspaceTile )
    {
        lookintile = $("#" + gDashboardLayout).find("#" + workspaceTile);
        if ( $(lookintile).length && $(lookintile).html() == "" )
            useTile = workspaceTile;
    }


    // Is it a map view?.. if so look for a map tile
    if ( useTile == null )
    {
        if ( session == "map" )
            $("#dashboardview #" + gDashboardLayout).find(".dashboardmap").each(function(){
                if ( useTile == null && $(this).html() == "" )
                {
                    useTile = $(this).attr("id");
                }
         });
    }


    // otherwise look for first free tile
    if ( useTile == null )
    {
        $("#dashboardview #" + gDashboardLayout).find(".dashboardsortable").each(function(){
            if ( useTile == null && $(this).html() == "" )
            {
                useTile = $(this).attr("id");
                return;
            }
            });
    }
    return useTile;
}

/*
** Creates a new dashboard for a session and populates it with 
** elements depending on the view type defined by viewtype parameter as follows :-
** "GRID" - creates a placeholder for a grid
** "LINE" - creates a placeholder for a line view
** "MAP" - creates a placeholder for a map and initilises the map ( session will be "map"
** "HTML"  tet report outout
**
*/
function addUrlToDashboard( session, id, url, title, viewtype )
{
            // If a dashboard grid/map doesnt already exist for the query then try to find an empty dash
            // to place the view in. However if the tile to use is defined in the workspace for an initial load then
            // first try to look there first to see if it will fit, otherwise carry on looking for an empty on
            if ( !$("#" + id).length )
            {
                useTile = findFreeGridTileForView( session );
                if ( useTile == null )
                {
                    alert("No space to place grid - please clear criteria before continuing");
                    return;
                }

                // Make map view a bit bigger
                if ( session == "map" )
                {
                    $("#" + gDashboardLayout).find("#" + useTile).append(
                            '<div id="' + id + '" class="portlet ui-helper-clearfix">' +
                                '<div class="portlet-header">' + '<span label="Click to change me" class="dashheadlabel">' + title + '</span><input type="text" style="display: none" value="' + title + '" class="dashheadlabelchanger"><input type="button" style="display: none" value="' + 'Save' + '" class="dashheadlabelchangerok">' + '</div>' +
                                '<div class="portlet-content"></div>' +
                            '</div>');
                }
                else
                {
                    $("#" + gDashboardLayout).find("#" + useTile).append(
                    '<div id="' + id + '" class="portlet ui-helper-clearfix">' +
                        '<div class="portlet-header">' + '<span label="Click to change me" class="dashheadlabel">' + title + '</span><input type="text" style="display: none" value="' + title + '" class="dashheadlabelchanger"><input type="button" style="display: none" value="' + 'Save' + '" class="dashheadlabelchangerok">' + '</div>' +
                        '<div class="portlet-content"></div>' +
                    '</div>');
                }

                mapwidgets = "";
                if ( viewtype == "MAP" )
                {
                    accheck = '';
                    azcheck = '';
                    if ( g_mappingAutoCentre )
                        accheck = 'checked="true"';
                    if ( g_mappingAutoZoom )
                        azcheck = 'checked="true"';
                    mapwidgets = 
                        "<span class='portlet-widget'>Autozoom <input class='mappingAutoZoom' type='checkbox' " + azcheck + ">" +
                        "Autocentre <input class='mappingAutoCentre' type='checkbox' " + accheck + ">&nbsp;</span>";
                }

                $( "#" + id )
                    .addClass( "ui-widget ui-widget-content ui-corner-all" )
                    .find( ".portlet-header" )
                    .addClass( "ui-widget-header ui-corner-all allowdrag" )
                    .prepend( mapwidgets )
                    .prepend( "<span class='dashheadchart ui-icon ui-icon-signal'>&nbsp;</span>")
                    .prepend( "<span class='dashheadgrid ui-icon ui-icon-calculator'>&nbsp;</span>")
                    .prepend( "<span class='maximiser ui-icon ui-icon-arrow-4-diag'>&nbsp;</span>")
					.prepend( "<span class='penico ui-icon' id='"+curMenu+"'>&nbsp;</span>") //Added By Prasenjit
                    //.prepend( "<span class='minimiser ui-icon ui-icon-minusthick'>&nbsp;</span>")
                    //.prepend( "<span class='widthsmaller ui-icon ui-icon-arrowstop-1-w'></span>")
                    //.prepend( "<span class='widthbigger ui-icon ui-icon-arrowthick-2-e-w'></span>")
                    //.prepend( "<span class='heightsmaller ui-icon ui-icon-arrowstop-1-n'></span>")
                    //.prepend( "<span class='heightbigger ui-icon ui-icon-arrowthick-2-n-s'></span>")
                    //.prepend( "<span class='higher ui-icon ui-icon-minusthick'></span>")
                    .end()
                    .find( ".portlet-content" );

                if ( viewtype == "MAP" )
                {
                    maphtml = '<div id="dashmap" style="padding: 0px">';
                    maphtml += '<div id="mapfilter" class="1curved" style="display:none">';
                    maphtml += '<div class="content curved">';
                    maphtml += '<div id="mapfiltertabs">';
                    maphtml += '<ul>';
                        //maphtml += '<li><a href="#tabs-1">Nunc tincidunt</a></li>';
                    maphtml += '</ul>';
                    maphtml += '<div id="tabs-1">';
                        //maphtml += '<p>Proin elit</p>';
                    maphtml += '</div>';
                    maphtml += '</div>';
                    maphtml += '<div id="mfcontent"></div>';
                    maphtml += '</div>';
                    maphtml += '</div>';
                    maphtml += '<div id="mapcol" style="height: 100%; margin: 0px 0px 0px 0px; ' +
                            'display: inline; width: 100%; float: left;">' +
                            '<div id="mstatus" class="stattxt">oo</div>' +
                            '<div id="mstatus2" class="stattxt">ipp</div>' +
                            '<div id="map">Loading map...</div>' +
                            '</div>';
                    maphtml += '</div>';
                    // Place HTML for map control into map dashboard
                    $( "#" + id + " .portlet-content" )
                        .append(maphtml);
                    initialiseFilterTabs("map", false);
                    initialiseMap();

                }

                if ( viewtype == "LINE" )
                {
                    var linehtml = '<div id="dashline' + session + '" style="padding: 0px">';
                    linehtml += '<div id="linefilter' + session + '" class="1curved" style="display:none">';
                    linehtml += '<div class="content curved">';
                    linehtml += '<div id="filtertabs' + session + '">';
                    linehtml += '<ul>';
                    //linehtml += '<li><a href="#tabs-1">Nunc tincidunt</a></li>';
                    linehtml += '</ul>';
                    linehtml += '<div id="tabs-1">';
                    linehtml += '</div>';
                    linehtml += '</div>';
                    linehtml += '<div id="mfcontent"></div>';
                    linehtml += '</div>';
                    linehtml += '</div>';
                    linehtml += '<div class="lineview" style="padding: 0px; height: 800px; overflow: scroll">';
                    linehtml += '</div>';
                    // Place HTML for map control into map dashboard
                    $( "#" + id + " .portlet-content" )
                        .append(linehtml);
                    initialiseFilterTabs("line", session);
                }


                if ( viewtype == "GRID" )
                {
                    $( "#" + id + " .portlet-content" )
                        .append('<div id="dashchartcont' + session + '" style="width:100%; display: none"><div id="dashchart' + session + '" style="width: 100%; height: 100%"></div></div>' )
                        .append('<table id="dashgrid' + session + '" style="padding: 0px"></table>' )
                        .append('<div id="dashgridpager' + session + '"></div>' );

                }
                
                dashheadlabel = "#" + id + " .portlet-header .dashheadlabel";
                dashheadlabelchanger = "#" + id + " .portlet-header .dashheadlabelchanger";
                dashheadlabelchangerok = "#" + id + " .portlet-header .dashheadlabelchangerok";
                widthbigger = "#" + id + " .portlet-header .widthbigger";
                widthsmaller = "#" + id + " .portlet-header .widthsmaller";
                heightbigger = "#" + id + " .portlet-header .heightbigger";
                heightsmaller = "#" + id + " .portlet-header .heightsmaller";
                minimiser = "#" + id + " .portlet-header .minimiser";
                maximiser = "#" + id + " .portlet-header .maximiser";
                dashheadgrid = "#" + id + " .portlet-header .dashheadgrid";
                dashheadchart = "#" + id + " .portlet-header .dashheadchart";

                // Show grid button
                $( dashheadgrid ).click(function() {
                    //if ( $( this).hasClass("ui-icon-arrow-4-diag" ) )
                    //{
                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                        if ( $("#dashline" + session).length != 0 )
                            $("#dashline" + session).css("display", "none");
                        if ( $("#dashchartcont" + session).length != 0 )
                            $("#dashchartcont" + session).css("display", "none");
                        if ( $("#dashgrid" + session).length != 0 )
                            $("#gbox_dashgrid" + session).css("display", "block");
                        if ( $("#dashgrid" + session ) )
                        {
                            sizeDashboardGridToFitParent ( session );
                        }
                    //}
                });
                $( dashheadchart ).click(function() {
                    session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                    if ( $("#dashline" + session).length != 0 )
                        $("#dashline" + session).css("display", "inline");
                    if ( $("#dashchartcont" + session).length != 0 )
                        $("#dashchartcont" + session).css("display", "inline");
                    if ( $("#dashgrid" + session).length != 0 )
                        $("#gbox_dashgrid" + session).css("display", "none");
                    if ( $("#dashgrid" + session ) )
                        sizeDashboardGridToFitParent ( session );
                });
                $( dashheadlabel ).click(function() {
                    $( this ).css( "display", "none" );
                    $( this ).siblings(".dashheadlabelchangerok").css( "display", "inline" );
                    $( this ).siblings(".dashheadlabelchanger").css( "display", "inline" );
                });
                //$( dashheadlabelchanger ).click(function() {
                    //$( this ).css( "display", "none" );
                    //$( dashheadlabel ).css( "display", "inline" );
                //});
                $( dashheadlabelchangerok ).click(function() {
                    $( this ).css( "display", "none" );
                    session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                    $( this ).siblings(".dashheadlabelchanger").css( "display", "none" );
                    $( this ).siblings(".dashheadlabel").html( $( this ).siblings(".dashheadlabelchanger").attr("value") );
                    $( this ).siblings(".dashheadlabel").css( "display", "inline" );
                    //$( dashheadlabel ).html( $(dashheadlabelchanger).attr("value") );
                    //$( dashheadlabel ).css( "display", "inline" );
                });
                $( heightsmaller ).click(function() {
                    if ( !$( this ).parents( ".portlet:first" ).hasClass( "portlet-maximized" ) )
                    {
                        if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-3" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashrow-3" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-2" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-2" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashrow-2" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-1" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-1" ) )
                        {
                        }
                        else 
                        {
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-1" );
                        }

                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);

                        sizeDashboardGridToFitParent ( session );
                        sizeDashboardMapToFitParent(session);
                        sizeDashboardLineToFitParent(session);
                    }
                });
                $( heightbigger ).click(function() {
                    if ( !$( this ).parents( ".portlet:first" ).hasClass( "portlet-maximized" ) )
                    {
                        if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-1" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashrow-1" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-2" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-2" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashrow-2" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-3" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashrow-3" ) )
                        {
                        }
                        else 
                        {
                            $( this ).parents( ".portlet:first" ).addClass( "dashrow-1" );
                        }

                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);

                        sizeDashboardMapToFitParent(session);
                        sizeDashboardGridToFitParent ( session );
                        sizeDashboardLineToFitParent ( session );
                    }

                });
                $( widthbigger ).click(function() {
                    if ( !$( this ).parents( ".portlet:first" ).hasClass( "portlet-maximized" ) )
                    {
                        if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-1" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashspan-1" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-2" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-2" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashspan-2" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-3" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-3" ) )
                        {
                        }
                        else 
                        {
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-1" );
                        }
                    }
                    session = $( this ).parents( ".portlet:first" )[0].id.substring(4);

                    sizeDashboardMapToFitParent(session);
                    sizeDashboardGridToFitParent ( session );
                    sizeDashboardLineToFitParent ( session );
                });
                $( widthsmaller ).click(function() {
                    if ( !$( this ).parents( ".portlet:first" ).hasClass( "portlet-maximized" ) )
                    {
                        if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-3" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashspan-3" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-2" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-2" ) )
                        {
                            $( this ).parents( ".portlet:first" ).removeClass( "dashspan-2" );
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-1" );
                        }
                        else if ( $( this ).parents( ".portlet:first" ).hasClass( "dashspan-1" ) )
                        {
                        }
                        else 
                        {
                            $( this ).parents( ".portlet:first" ).addClass( "dashspan-1" );
                        }
                    }
                    session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                    sizeDashboardMapToFitParent(session);
                    sizeDashboardGridToFitParent ( session );
                    sizeDashboardLineToFitParent ( session );
                });
                $( minimiser ).click(function() {
                    if ( $( this).hasClass("ui-icon-plusthick" ) )
                    {
                        // Unminimize
                        $( this ).removeClass( "ui-icon-plusthick" );
                        $( this ).addClass( "ui-icon-minusthick" );
                        $( maximiser ).removeClass( "ui-icon-newwin" );
                        $( maximiser ).addClass( "ui-icon-arrow-4-diag" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-minimized" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-maximized" );
                        $( this ).parents( ".portlet:first .portlet-content" ).show();
                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                        if ( $("#dashline" + session ) )
                            sizeDashboardLineToFitParent ( session );
                        if ( $("#dashgrid" + session ) )
                            sizeDashboardGridToFitParent ( session );
                        sizeDashboardMapToFitParent(session);
                        $('.portlet').css("display", "inline");
                        //$('.dashcolumn').css("display", "inline");
                        //$('.dashrow').css("display", "inline");
                        
                    }
                    else
                    {
                        // Minimize
                        $( this ).removeClass( "ui-icon-minusthick" );
                        $( this ).addClass( "ui-icon-plusthick" );
                        $( maximiser ).removeClass( "ui-icon-newwin" );
                        $( maximiser ).addClass( "ui-icon-arrow-4-diag" );
                        $( this ).parents( ".portlet:first" ).addClass( "portlet-minimized" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-maximized" );
                        $('.portlet').css("display", "inline");
                        //$('.dashcolumn').css("display", "inline");
                        //$('.dashrow').css("display", "inline");
                        $( this ).parents( ".portlet:first .portlet-content" ).hide();
                        if ( maximisedDashboardSource != null )
                        {
                            $('#dashboardview').show();
                            $('#dashmaximised').hide();

                            // Return the maximised content to the original dashboard portlet
                            $("#dashmaximised .portlet").appendTo($("#" + maximisedDashboardSource));
                        }
                        maximisedDashboardSource = null;
                        showColumsForNormalMaximisedState(session, false);
                    }

                    sizeDashboardMapToFitParent(session);
                    sizeDashboardGridToFitParent ( session );
                    sizeDashboardLineToFitParent ( session );
                });
 
                // Portlet Maximise Pressed
                $( maximiser ).click(function() {
                    if ( $( this).hasClass("ui-icon-arrow-4-diag" ) )
                    {
                        // Maximise - set icons
                        showColumsForNormalMaximisedState(session, false);
                        $( this ).removeClass( "ui-icon-arrow-4-diag" );
                        $( this ).addClass( "ui-icon-newwin" );
                        $( minimiser ).addClass( "ui-icon-minusthick" );
                        $( minimiser ).removeClass( "ui-icon-plusthick" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-minimized" );
                        $( this ).parents( ".portlet:first" ).addClass( "portlet-maximized" );

                        // Move dashboard portlet to maximised mode div, but store its 
                        // currentl location first so we can return to it, then show maximised div
                        maximisedDashboardSource = $( this ).parents( ".dashboardsortable:first").attr("id");
                        $( this ).parents( ".portlet:first" ).appendTo($("#dashmaximised"));
                        $("#dashmaximised").show();

                        // Fill the maximise window with portlet
                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                        showColumsForNormalMaximisedState(session, true);
                        if ( $("#dashgrid" + session ) )
                            sizeDashboardGridToFitParent ( session );
                        if ( $("#dashLine" + session ) )
                            sizeDashboardLineToFitParent ( session );
                        sizeDashboardMapToFitParent(session);

                        // Hide the dashboard, show the maximised area
                        $('#dashboardview').hide();
                        $('#dashmaximised').show();

                        // .. and fill portlet with map if this is map portlet
                        if ( session == "map" )
                            resizeMap();
                        
                    }
                    else
                    {
                        // Unmaximise - set correct icons
                        $( this ).removeClass( "ui-icon-newwin" );
                        $( this ).addClass( "ui-icon-arrow-4-diag" );
                        $( minimiser ).removeClass( "ui-icon-plusthick" );
                        $( minimiser ).addClass( "ui-icon-minusthick" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-minimized" );
                        $( this ).parents( ".portlet:first" ).removeClass( "portlet-maximized" );
                        $( maximiser ).removeClass( "ui-icon-newwin" );

                        showColumsForNormalMaximisedState(session, false);

                        // Hide dashboard, show maximise window
                        $('.portlet').css("display", "inline");
                        $('#dashboardview').show();
                        $('#dashmaximised').hide();

                        // Return the maximised content to the original dashboard portlet
                        $("#dashmaximised .portlet").appendTo($("#" + gDashboardLayout).find("#" + maximisedDashboardSource));

                        // Fill the maximise window with portlet
                        session = $( this ).parents( ".portlet:first" )[0].id.substring(4);
                        if ( $("#dashgrid" + session ) )
                            sizeDashboardGridToFitParent ( session );
                        if ( $("#dashline" + session ) )
                            sizeDashboardLineToFitParent ( session );
                        sizeDashboardMapToFitParent(session);

                        // .. and fill portlet with map if this is map portlet
                        if ( session == "map" )
                            resizeMap();
                        maximisedDashboardSource = null;
                    }
                    //sizeDashboardMapToFitParent(session);
                    //sizeDashboardGridToFitParent ( session );
                });

            }
            //sizeDashboardGridToFitParent( session );
            //sizeDashboardMapToFitParent( session );
}


/*
** initializeDashboardMap
**
** Creates a dashboard widget for a map view
*/
function initializeDashboardMap( )
{
    addUrlToDashboard( "mapview", "dashmap", "/demo/iconnex/js/dashboard/pleasewait.html", "Map View", false );
    maphtml = '<div id="mapcol" style="margin: 0px 0px 0px 0px; display: inline; width: 100%; float: left;">' + 
    '<div id="mstatus" class="stattxt"></div>' + 
    '<div id="mstatus2" class="stattxt"></div>' + 
    '<div id="map">Loading map...</div>' + 

           '</div>';
    $("#dashmap .widgetcontent").attr('innerHTML', maphtml);
    $("#dashmap .widgetcontent").css('height', "200px");
    //var contents = $("#mapcol").contents();
    //$("#mapcol2").innerHTML = contents;
    //$("#mapcol").innerHTML = "";
    //$("#mapcol").css("display", "none");
    //loadScript();


}

function initializeDashboardLine( )
{
    // Only add line view if not already there
    a = $("#linecol");
    if ( $("#linecol").length > 0 )
    {
        return;
    }

    addUrlToDashboard( "lineview", "dashline", "js/dashboard/dashline.html", "Line View", false );

    //linehtml = 'ooo<div id="linecol" style="margin: 4px 0px 0px 0px; display: inline; width: 100%; float: left;">' + 
        //'<table id="datagrid" style="padding: 0px"></table>' + 
            //'</div>';
    //$("#dashline .widgetcontent").attr('innerHTML', 'oooo');
    $("#dashline .widgetcontent").css('height', "200px");

}

/*
** deleteDashboardWidget
**
** Removes a widget form the dashboard
*/
function deleteDashboardWidget ( session )
{
    id = "#dash" + session;
    if ( get_session_param ( session, "hasmap" ) )
    {
        //id = "#dashmap";
        //$(id).html("");
        removeAllMapTabs(session)
        //map = false;
    }
    else
        $(id).remove();
}

/*
** maximiseDashboardWidget
**
** fills the dasahboard area with the selected widget and
** hides all others .. in effect maximises it
*/
function maximiseDashboardWidget ( session )
{

    oldsession = session;
    $( ".portlet-maximized .portlet-header .maximiser" ).click();
    session = oldsession;
    id = "#dash" + session;
    gridid = "#dashgrid" + session;
    refreshDashboardWidgets();
    session = oldsession;
    id = "#dash" + session;
    $( id + " .portlet-header .maximiser" ).click();


    //$('.portlet').css("display", "none");
    //$('.portlet').removeClass("portlet-maximized");
    //refreshDashboardWidgets
    //$(id).css("display", "inline");
    //$(id).addClass("portlet-maximized");
    //x = $(gridid);
    //y = $(id + " .portlet-content");
    sizeDashboardGridToFitParent ( session );
}

/*
** unmaximiseDashboardWidget
**
** restores all dashboard windows to there unmaximised size
** i.e. shows them all together
*/
function unmaximiseDashboardWidgets ( )
{
    $('.portlet').removeClass("portlet-maximized");
    $('.portlet').css("display", "inline");
    for ( session in sessionParams )
    {
        sizeDashboardGridToFitParent ( session );

        if ( session == "map" )
            sizeDashboardMapToFitParent("map");
    }
}

/*
** showDashboard
**
** Makes dashboard panel visible over other panels
*/
function showDashboard()
{  
    $('#reportcol').css('display', 'none');
    $('#dashboardview').css('display', 'inline');
    $('#gridcol').css('display', 'none');
}


/*
** refreshDashboardWidgets
**
** After leaving dashboard and redrawing it,
** all grids maps etc need to be resized to fit
*/
function refreshDashboardWidgets ( session )
{

    for ( ses in sessionParams )
    {
        sizeDashboardGridToFitParent ( ses );
    }
    sizeDashboardGridToFitParent ( "map" );
}

/*
** getGridParentHeight
**
** Returns the hight of the parent container of a grid
*/
function getGridParentHeight ( session )
{
    id = "#dash" + session;
    gridid = "#dashgrid" + session;
    chartcont = "#dashchartcont" + session;
    chart = "#dashchart" + session;

    if ( $(gridid).length )
    {
        $(gridid).jqGrid('setGridWidth', $(id + " .portlet-content").width() - 2, true);

        parpos = $(id).position().top;
        pos = $( id + " .portlet-content" ).position().top;
        curheight = $(id).parents(".dashboardsortable").height();
	return curheight;
    }
}

/*
** sizeDashboardGridToFitParent
**
** Sizes a grid view widet to fill its dash board container
** typically after a resize 
*/
function sizeDashboardGridToFitParent ( session )
{
    id = "#dash" + session;
    gridid = "#dashgrid" + session;
    chartcont = "#dashchartcont" + session;
    chart = "#dashchart" + session;

    if ( $(gridid).length )
    {
        $(gridid).jqGrid('setGridWidth', $(id + " .portlet-content").width() - 2, true);

        parpos = $(id).position().top;
        pos = $( id + " .portlet-content" ).position().top;
        curheight = $(id).parents(".dashboardtile").height();
        $(gridid).jqGrid('setGridHeight', curheight - ( pos - parpos ) - 60 - 20);
        $(chartcont).height( curheight - ( pos - parpos ) - 5);
        $(chart).height( curheight - ( pos - parpos ) - 5);

        plot_chart(session);
    }
}

/*
** sizeDashboardMapToFitParent
**
** Sizes a map widet to fill its dash board container
** typically after a resize 
*/
function sizeDashboardMapToFitParent ( session )
{
    gridid = "#dashgrid" + session;
    if ( session == "map" )
    {
        id = "#dash" + session;

        parpos = $(id).position().top;
        pos = $( id + " .portlet-content div #mapcol #map" ).position().top;
        //curheight = $(id).height();
        tileheight = $(id).parents(".dashboardtile").height();
        //$( id + " .portlet-content #dashmap" ).height( curheight - ( pos - parpos ) - 5);

        // Map height is dashboard tile height - header - map filter
        tiletop = $(id).parents(".dashboardtile").position().top;
        filtertop = $( id + " .portlet-content #dashmap #mapfilter" ).position().top;
        headerheight = filtertop - tiletop;
        filterheight = $( id + " .portlet-content #dashmap #mapfilter .content" ).height();
        mapoffset = headerheight + filterheight;
        mapheight = tileheight - mapoffset - 10;
        if ( mapheight < 5 ) 
            mapheight = 1;
        //$( id + " .portlet-content #mapcol" ).height( curheight - ( pos - parpos ) - 5);
        $( id + " .portlet-content #mapcol #map" ).height(mapheight);

        resizeMap();
    }
}

/*
** sizeDashboardLineToFitParent
**
** Sizes a map widet to fill its dash board container
** typically after a resize 
*/
function sizeDashboardLineToFitParent ( session )
{
    lineid = "#dashline" + session;
}


/*
** loadMapToDashboard
**
** Loads reports report data returned in jqueryGrid compatible json format
** into a jqgrid control and displays it within a dashboard portlet
*/
function loadMapToDashboard(session)
{
    // Get access to a dashboard to place the grid in
    set_session_param(session, "map", true);

    session = "map";
    dashid = "dash" + session;
    dashtag = "#dash" + session;
    dashgridid = "dashgrid" + session;
    dashgridtag = "#dashgrid" + session;
    dashpagertag = "dashgridpager" + session;
    
    addUrlToDashboard( session, dashid, "Please Wait ...", "Map View", "MAP" );

    // Make grid visible
    //$("#showmap").click();

}

/*
** loadLineViewToDashboard
**
** Loads json format data
** into a line view control and displays it within a dashboard portlet
*/
function loadLineViewToDashboard(session)
{
    // Get access to a dashboard to place the grid in
    set_session_param(session, "map", true);

    session = session
    dashid = "dash" + session;
    dashtag = "#dash" + session;
    dashgridid = "dashgrid" + session;
    dashgridtag = "#dashgrid" + session;
    dashpagertag = "dashgridpager" + session;
    
    addUrlToDashboard( session, dashid, "Please Wait ...", "Line View", "LINE" );

    // Make grid visible
    //$("#showmap").click();

}

/*
** showColumnsForNormalMaximisedState
**
** Will hide columns specified as minihide in query from grid when grid is unmaximised and 
** show thwm when maximised
*/
function showColumsForNormalMaximisedState(session, maximised)
{
    dashgridtag = "#dashgrid" + session;
    dashgridpagertag = "#dashgridpager" + session;
    dashgridpagerbuttonstag = "#dashgridpager" + session + "_left";

    // If minihide fields are not defined then no need to hide/show anything
    minihide = get_session_param ( session, "minihide" );
    if ( !minihide )
    {
        return;
    }

    // In unmaximised view hide edit buttons etc
    if ( maximised )
    {
        $(dashgridpagertag).show();
        $(dashgridpagerbuttonstag).show();
    }
    else
    {
        $(dashgridpagertag).show();
        $(dashgridpagerbuttonstag).hide();
    }
        //$(dashgridpagerbuttonstag).hide();
    var colmodels = $(dashgridtag).jqGrid('getGridParam', 'colModel'); 
    for ( var index in colmodels )
    {
        // stat = minihide.indexOf(colmodels[index].index );
        for (var i = 0, j = minihide.length; i < j; i++) {
            p = minihide[i];
            if (minihide[i] == colmodels[index].index) 
            { 
                if ( maximised )
                    $(dashgridtag).jqGrid('showCol', colmodels[index].index);
                else
                    $(dashgridtag).jqGrid('hideCol', colmodels[index].index);
                break;
            }
        }
    }
}

/*
    For IE7/IE8 where indexOf feature does not exist
*/
//if (!Array.prototype.indexOf) {
    //Array.prototype.indexOf = function(obj, start) {
        //for (var i = (start || 0), j = this.length; i < j; i++) {
            //if (this[i] === obj) { return i; }
            //}
            //return -1;
        //}
//}

/*
** loadReportGrid
**
** Loads reports report data returned in jqueryGrid compatible json format
** into a jqgrid control and displays it within a dashboard portlet
*/
function loadReportGrid(session, dataFormUrl, result)
{
    // Get access to a dashboard to place the grid in
    dashid = "dash" + session;
    dashtag = "#dash" + session;
    dashgridid = "dashgrid" + session;
    dashgridtag = "#dashgrid" + session;
    dashpagertag = "#dashgridpager" + session;
    dashpagernotag = "dashgridpager" + session;

    // Create list if columns that should in minimised windowm state
    minihide = false;
    if ( result.minihide )
    {
        if ( result.minihide.length > 0 ) 
            set_session_param(session, "minihide", result.minihide);
    }

    var colmodels = $(dashgridtag).jqGrid('getGridParam', 'colModel'); 

    // If grid is not showing then after weve drawn it we want to hide it
    gridshowing = false;
    gridexists = false;
    if ( $("#dashgrid" + session).length != 0 )
    {
        gridexists = true;
        if ( $("#gbox_dashgrid" + session).css("display") == "block" )
            gridshowing = true;
    }
    else
            gridshowing = true;

    // If grid already loaded then just update the relevant rows with the new data
    // unless the session is not auto refresh in which case rebuild the grid
    if ( get_session_param(session, "autorefresh") && ( gridexists && colmodels.length > 0 ) ) 
    {
        colD = result.gridmodel;
        colN = result.colnames;
        colM = result.colmodel;

        var oldScrollTop = $(dashgridtag)[0].scrollTop;
        var oldpg = $(dashgridtag).jqGrid('getGridParam', 'page'); 

        data = $(dashgridtag).jqGrid('getGridParam', 'data');
        dataindex = $(dashgridtag).jqGrid('getGridParam', '_index');

        deleteindexes = [];

        for ( var index in colD.rows )
        {
            itemIndex = dataindex[colD.rows[index].id];
            rowItem = data[itemIndex];

            newrow = {};
            deleterow = false;
            for ( var index2 in colM )
            {
                newrow[colM[index2].index] = colD.rows[index].cell[index2];
                if ( colM[index2].index == "row_status" && colD.rows[index].cell[index2] == "DELETED" )
                {
                    deleterow = true;
                    break;
                }
            }

            // Get pointers to raw data so when we uodate/delete a row we are removing from grid as well 
            // as underlying data
            if ( deleterow )
            {
                //success = $(dashgridtag).jqGrid('delRowData', colD.rows[index].id);

                // Update base grid data
                itemIndex = dataindex[colD.rows[index].id];
                //data.splice(itemIndex, 1);
                data[itemIndex] = null;
                deleteindexes.push(colD.rows[index].id);
                //delete dataindex[colD.rows[index].id];
            }
            else
                if ( data[itemIndex] != undefined )
                //if ( $(dashgridtag).jqGrid('getInd', colD.rows[index].id, false) )
                {
                    //success = $(dashgridtag).jqGrid('delRowData', colD.rows[index].id);
                    //success = $(dashgridtag).jqGrid('setRowData', colD.rows[index].id, newrow);

                    // Update base grid data
                    itemIndex = dataindex[colD.rows[index].id];
                    newrow._id_ = colD.rows[index].id;
                    data[itemIndex] = newrow;
                }
                else
                {
                    insertrelativeto = "last";
                    insertat = false;

                    var sortColumnName = $(dashgridtag).jqGrid('getGridParam','sortname');
                    var sortColumnOrder = $(dashgridtag).jqGrid('getGridParam','sortorder');
                    /*
                    if ( sortColumnName )
                    {
                        var ids = $(dashgridtag).jqGrid('getDataIDs');
                        for(i=0;i<ids.length;i++)
                        {
                            rowindex = ids[i];
                            insertat = rowindex;
                            rowdata = $(dashgridtag).jqGrid('getRowData', rowindex);
                            cellsortvalue = $(dashgridtag).jqGrid('getCell', rowindex, sortColumnName);
                            if ( sortColumnOrder == "asc" && cellsortvalue >= newrow[sortColumnName] )
                            {
                                insertrelativeto = "before";
                                break;
                            }
                            if ( sortColumnOrder != "asc" && cellsortvalue <= newrow[sortColumnName] )
                            {
                                insertrelativeto = "before";
                                break;
                            }

                        }
                    }
                    success = $(dashgridtag).jqGrid('addRowData', colD.rows[index].id, newrow, insertrelativeto, insertat );
                    */

                    // Add base grid data
                    itemIndex = dataindex[colD.rows[index].id];
                    addat = data.length;
                    newrow._id_ = colD.rows[index].id;
                    data[addat] = newrow;
                    dataindex[colD.rows[index].id] = addat;
                }
        }
   
        if ( sortColumnName )
        {
            $(dashgridtag).jqGrid('setGridParam', 'sortname', sortColumnName);
            $(dashgridtag).jqGrid('setGridParam', 'sortorder', sortColumnOrder);
        }


        // If any rows have been deleted then 
        // we need to remove all data rows that are null and 
        // then rebuild the index set. We do this becuase if we 
        // remove a data row then the array is collapsed and
        // some of the indexes are then pointing to the next row along
        if ( deleteindexes.length > 0 )
        {
            // Remove deleted null elements
            for (var i = 0; i < data.length; i++) {
                if (data[i] == null) {         
                    data.splice(i, 1);
                    i--;
                }
            }

            delobj = {};
            // Rebuild grid index from base grid data
            for ( var index in dataindex )
            {
                delobj[index] = index;
            }
            for ( var index in delobj )
            {
                delete dataindex[index];
            }

            // Remove deleted null elements
            for (var i = 0; i < data.length; i++) {
                dataindex[data[i]._id_] = i;
            }
        }


        $(dashgridtag).trigger('reloadGrid');


        //$(dashgridtag).jqGrid('setGridParam', {datatype: 'jsonstring', datastr: colD}).trigger('reloadGrid');

        //var sortColumnName = $(dashgridtag).jqGrid('setGridParam','sortname');
        return;
    }

    addUrlToDashboard( session, dashid, "Please Wait ...", get_session_param(session, "title" ), "GRID" );

    // Clear any existing grid data
    $(dashgridtag).jqGrid('GridUnload');


    // Make grid visible
    //$("#showdashboard").click();

    // Extract jqgrid columns and settings from result data
    colD = result.gridmodel;
    colN = result.colnames;
    colM = result.colmodel;
    gridparams = result.gridparams;
    multiselect = false;
    if ( gridparams && gridparams.multiselect )
        multiselect = true;
    custombuttons = result.buttons;
    custombuttonids = result.buttonids;
    viewname = result.viewname;

    // Set user data belongs to
    userid=$("#activeuser");
    userid=userid[0].name;

    // Set the URL used for data modification commital to database
    editurl = "protected/extensions/reportico/modify.php" + "?" + 
            "session_name=" + session + "&execute_mode=MODIFY&user=" + userid + "&dbview=" + viewname;


	heightTofitGridTo = getGridParentHeight ( session );

    // Create grid
    firstClick = false;
    $(dashgridtag).jqGrid({
        jsonReader : {
            repeatitems: true,
            root:"rows",
            cell: "cell",
            id: "id"
        },
        url: dataFormUrl + "?" + get_session_param(session, "url_params"),
                datatype: 'jsonstring',
                mtype: 'GET',
                datastr : colD,
                colNames:colN,
                colModel :colM,
                //shrinkToFit: true,
                height: "100px",
                pager: jQuery(dashpagertag),
                page: 1,
                rowNum: 30,
                rowTotal: 50000,
                sortable: true,
                search: true,
                loadonce: true,
                multiselect: multiselect,
                rowList: [5, 10, 20, 50],
                viewrecords: true,
                caption: false,
                //loadComplete: function(data){alert('loaded');},
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
                    bExit : "Cancel"
                },
                loadError: function(xhr,status,error){alert('error');},
                loadComplete: function() {
                    jQuery(dashgridtag).trigger("reloadGrid"); // Call to fix client-side sorting
                },
                gridComplete: 
                    function(){ 
                        var ids = jQuery(dashgridtag).jqGrid('getDataIDs'); 
                        for(var i=0;i < ids.length;i++){ 
                            var cl = ids[i]; 
                            be = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-pencil' value='E' onclick=\"jQuery(dashgridtag).jqGrid('editRow', '"+cl+"', true);\" />";
                            se = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-check' value='S' onclick=\"jQuery(dashgridtag).jqGrid('saveRow', '"+cl+"', true);\" />";
                            ce = "<input style='float: left; border: none; background-color: inherit;' type='button' class='ui-icon ui-icon-close' value='C' onclick=\"jQuery(dashgridtag).jqGrid('restoreRow', '"+cl+"', true);\" />"; 
                            jQuery(dashgridtag).jqGrid('setRowData',ids[i],{options:be+se+ce});
                     } } ,
                onSelectRow: function(id){ 
                        if(id && id!==lastGridSelection)
                        { 
                            if ( lastGridSelection != -1 )
                                jQuery(dashgridtag).jqGrid('restoreRow',lastGridSelection); 
                            jQuery(dashgridtag).jqGrid('editRow',id,true); lastGridSelection=id; 
                        } 
                    } 
        });



/*
        $(dashgridtag).jqGrid('setGridParam', 'subGrid', true);
        $(dashgridtag).jqGrid('setGridParam', 'subGridRowExpanded',  function(subgrid_id, row_id) {
    // we pass two parameters
    // subgrid_id is a id of the div tag created within a table
    // the row_id is the id of the row
    // If we want to pass additional parameters to the url we can use
    // the method getRowData(row_id) - which returns associative array in type name-value
    // here we can easy construct the following
       var subgrid_table_id;
       subgrid_table_id = subgrid_id+"_t";
       jQuery("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table>");
       jQuery("#"+subgrid_table_id).jqGrid({
          url:"subgrid.php?q=2&id="+row_id,
          datatype: "xml",
          colNames: ['No','Item','Qty','Unit','Total'],
          colModel: [
            {name:"num",index:"num",width:80,key:true},
            {name:"item",index:"item",width:130},
            {name:"qty",index:"qty",width:80,align:"right"},
            {name:"unit",index:"unit",width:80,align:"right"},           
            {name:"total",index:"total",width:100,align:"right",sortable:false}
          ],
          height: '100%',
          rowNum:20,
          sortname: 'num',
          sortorder: "asc"
       })
    });
*/

        // Add pager to grid
        jQuery(dashgridtag).jqGrid('navGrid',dashpagertag,{edit:true,add:true,del:true},
                            {},
                            {},
                            {},
                            {closeOnEscape: true, multipleSearch: true, closeAfterSearch: true},
                            {}
                            );
        jQuery(dashgridtag).jqGrid('filterToolbar',{ searchOnEnter: true, enableClear: false, defaultSearch: 'cn' });


        // Add report custom buttons to grid
        for ( var index in custombuttons )
        {  
            if ( !index )
                continue;
            butid = "custom_" + custombuttons[index];
            caption = "&nbsp;" + custombuttons[index];

            //$(dashgridtag).jqGrid('navButtonAdd', dashpagertag,{ 
                    //caption: caption,
                    //buttonicon:'none', 
                    //onClickButton: function() 
                    //{ 
                        //gridaction($(dashgridtag), index ); 
                    //}, 
                    //position:'last', 
                    //id: butid
                //});


            var x = "jQuery(dashgridtag).jqGrid('navButtonAdd','" + dashpagertag + "',{ " +
                    " caption:'&nbsp;" + custombuttons[index] + "', " +
                    " buttonicon:'none', " +
                    " onClickButton: function() " +
                    " { " +
                        " gridaction($(dashgridtag), '" + index + "'); " +
                    " }, " +
                    " position:'last', " + 
                    " id:'custom_" + custombuttons[index] + "'" +
                    " });";
             //eval(x);
        }




        $(dashgridtag).jqGrid('setGridWidth', ($(dashtag).width() - 10), true);
        sizeDashboardGridToFitParent ( session );
        showColumsForNormalMaximisedState(session, false)
        $(dashgridtag).jqGrid('setGridHeight', heightTofitGridTo - 140);

        // Grid is populated, but if grid was loaded from workspace
        // the session may have a custom dashboard title or custom
        // search/filter/sort settings .. if thids is the case then
        // apply them to the grid
        if ( get_session_param(session, "customTitle") )
            set_dashboard_title(session, get_session_param(session, "customTitle")) 
        if ( get_session_param(session, "searchSettings") )
        {
            str = get_session_param(session, "searchSettings");
            str = str.replace(/\\"/g,"\"");
            obj = eval("(" + str + ")");
            $(dashgridtag).jqGrid('setGridParam', { search: true });
            $(dashgridtag).jqGrid('setGridParam', { postData: obj } );
            if ( obj.sidx )
            {
                $(dashgridtag).jqGrid('setGridParam', { sortname: obj.sidx, sortorder: obj.sord } );
            }
            $(dashgridtag).jqGrid('setGridParam', { postData: obj }).trigger('reloadGrid');
        }

        if ( !gridshowing )
            if ( $("#dashgrid" + session).length != 0 )
                    $("#gbox_dashgrid" + session).css("display", "none");
        else                
            if ( $("#dashgrid" + session).length != 0 )
                    $("#gbox_dashgrid" + session).css("display", "block");
        
        //$(dashgridtag).jqGrid('setGridHeight', ($(dashtag).height() - 10 ));

	//$("#dashcol1 .portlet").appendTo($("#dashcol2"));
	//$("#dashcol2 .portlet").appendTo($("#dashcol3"));

}

/*
** plot_chart
**
** Reads the session query data and renders a chart filling the container
*/
function plot_chart ( session )
{

    // Clear chart before plotting
    chart = get_session_param(session, "chart");
    chartcont = "#dashchartcont" + session;
    if ( chart && $(chartcont).css("display") == "none" )
    {
        return;
    }

    if ( chart )
        chart.destroy();


    dashgridtag = "#dashgrid" + session;
    query_data = get_session_param(session, "jsondata");
    if ( !query_data || !query_data["graphopt"] )
        return;

    // Extract graph series
    var xlabels = [];
    var series = [];
    var seriesOptions = [];
    var legends = [];

    labelcol = query_data["graphopt"]["xlabelcol"];

    //found = false;
    // -------------------------------------------------------
    // Find data column for x labels
    //for ( col in query_data["colmodel"] )
    //{
        //colname = query_data["colmodel"][col]["name"];
        //if ( colname == labelcol )
        //{
            //found = true;
            //datalabelcol = col;
            //break;
        //}
    //}

    // Build x axis labels
    var ids = $(dashgridtag).jqGrid('getDataIDs');
    if ( ids.length == 0 )
        return; 

    for(i=0;i<ids.length;i++)
    {
       rowindex = ids[i];
       insertat = rowindex;
       rowdata = $(dashgridtag).jqGrid('getRowData', rowindex);
       labelvalue = $(dashgridtag).jqGrid('getCell', rowindex, labelcol);
       xlabels.push(labelvalue);
    }
    //if ( found )
    //{
        //ct = 0;
        //for ( row in query_data["gridmodel"]["rows"] )
        //{
            //xlabels.push(query_data["gridmodel"]["rows"][row]["cell"][datalabelcol]);
        //}
    //}

    // For storing the actual x axis labels that will be displayed
    ticks = [];
    // Calculate x axis tick interval by taking account of xaxis items and
    // graph width 
    chartwidth = $('#dashchartcont' + session).parents(".portlet:first" ).width();
    numxitems = xlabels.length;
    tickinterval = chartwidth;
    tickinterval = ( numxitems * 30 ) / chartwidth;
    tickinterval = Math.floor(tickinterval);
    tickinterval += 1;

    // Calculate bar width based on number of bars and width
    barwidth = chartwidth / ( numxitems  + 2 ) - 3;

    // Do stacking ?
    stacked = false;

    // -------------------------------------------------------
    // Find data columns for plot series
    plotno = 0;
    minplot = 0;
    maxplot = 0;
    for ( plot in query_data["graphopt"]["plots"] )
    {
        plotcol = query_data["graphopt"]["plots"][plot]["plotcol"];
        plottype = query_data["graphopt"]["plots"][plot]["plottype"];
        legend = query_data["graphopt"]["plots"][plot]["legend"];
        legends.push({label: legend});
        
        if ( plottype == "bar" )
        {
            seriesOptions.push({
                    disableStack: true,
                    label: legend,
                    renderer: $.jqplot.BarRenderer, 
                    rendererOptions: {fillToZero: true,
                                    barMargin: 1,
                                    barWidth: barwidth
                    }});
        }   
        else if ( plottype == "stackedbar" )
        {
            stacked = true;
            seriesOptions.push({
                    disableStack: false,
                    label: legend,
                    renderer: $.jqplot.BarRenderer, 
                    rendererOptions: {fillToZero: true,
                                    barMargin: 1,
                                    barWidth: barwidth
                    }});
        }   
        else
        {
            seriesOptions.push(
                    {
                    label: legend,
                    disableStack: false,
                    renderer: $.jqplot.LineRenderer
                    });
        }

        found = false;
        series[plotno] = [];
        for ( col in query_data["colmodel"] )
        {
            colname = query_data["colmodel"][col]["name"];
            if ( colname == plotcol )
            {
                found = true;
                plotcolno = col;
                break;
            }
        }


        // Build data series
        if ( found )
        {
            series[plotno] = [];
            ct = 0;
            for(i=0;i<ids.length;i++)
            {
                rowindex = ids[i];
                insertat = rowindex;
                rowdata = $(dashgridtag).jqGrid('getRowData', rowindex);

                if ( plotno == 0 )
                {
                    if ( ct++ % tickinterval == 0 ) 
                        ticks.push ( xlabels[i] );
                    else
                        ticks.push ( " " );
                }
                val = $(dashgridtag).jqGrid('getCell', rowindex, plotcol);
                //val = query_data["gridmodel"]["rows"][row]["cell"][plotcolno];
                val = parseInt(val);
                series[plotno].push(val);

                if ( val > maxplot ) maxplot = val;
                if ( val < minplot ) minplot = val;
                //series[plotno].push(plotarr);
            }
            plotno++;
        }
    }

    // plot graphh
    var plotchart = $.jqplot('dashchart' + session, series, {

        // Do we stack?
        stackSeries: stacked,

        // The "seriesDefaults" option is an options object that will
        // be applied to all series in the chart.
        seriesDefaults:{
            renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true, location: 'n', edgeTolerance: 1.5 },
            rendererOptions: {fillToZero: true,
                            barWidth: barwidth
                            }
        },
        // Custom labels for the series are specified with the "label"
        // option on the series option.  Here a series option object
        // is specified for each series.
        series: seriesOptions,

        axesDefaults: {
            tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
            tickOptions: {
            formatString: '%d', 
            fontSize: '8pt'
            }
        },

        // Show the legend and put it outside the grid, but inside the
        // plot container, shrinking the grid to accomodate the legend.
        // A value of "outside" would not shrink the grid and allow
        // the legend to overflow the container.
        legend: {
            show: true,
            noColumns: 3,
            marginRight: 60,
            marginTop: -5,
            placement: 'inside'
        },

        axes: {
            // Use a category axis on the x axis and use our custom ticks.
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: ticks,
                tickOptions: {
                //angle: -20,
                fontSize: '8pt'
                }
            },
            // Pad the y axis just a little so bars can get close to, but
            // not touch, the grid boundaries.  1.2 is the default padding.
            yaxis: {
                renderer: $.jqplot.LinearAxisRenderer,
                rendererOptions: {forceTickAt0: true },
                pad: 1.05,
                min: minplot,
                max: maxplot + 5
            }
        }
    });

    set_session_param(session, "chart", plotchart);

    $('#dashchart' + session).bind('resizestop', function(event, ui) {
    $('#dashchart' + session).height($('#resizable2').height()*0.96);
    $('#dashchart' + session).width($('#resizable2').width()*0.96);

    // pass in resetAxes: true option to get rid of old ticks and axis properties
    // which should be recomputed based on new plot size.
     //plotchart.replot( { resetAxes:true } );
     //plotchart.replot( { resetAxes:['yaxis'] } );
  });
};



/*
** initialiseFilterTabs
**
** Applies tab functionality to divs used for filtering that exist above 
** the map/line view
*/
function initialiseFilterTabs(type, id)
{
        if ( id )
            $( "#" + "filtertabs" + id ).tabs({
                //event: "mouseover",
                collapsible: true
            });
        else
            $( "#" + type + "filtertabs" ).tabs({
                //event: "mouseover",
                collapsible: true
            });
}

/*
** removeAllMapTabs
**
** Removes filter tabs from a map
** the map
*/
function removeAllMapTabs(session)
{
        if ( get_session_param ( session, "hasmap" ) )
        {
            if ( $('#mapfiltertabs').tabs )
            {
                var tab_count = $('#mapfiltertabs').tabs('length');
                for (i=0; i<tab_count; i++){
                    $('#mapfiltertabs').tabs( "remove" , 0 )
                }
            }
        }
        else
        {
            if ( $('#filtertabs' + session).tabs )
            {
                var tab_count = $('#filtertabs' + session).tabs('length');
                for (i=0; i<tab_count; i++){
                    $('#filtertabs' + session).tabs( "remove" , 0 )
                }
            }
        }
}

/*
** 
**
** Add a new tab to the map filter tabset with the specified content
** the map
*/
function addMapTab(session, id, name, data)
{
        if ( get_session_param ( session, "hasmap" ) )
            $('#mapfiltertabs').append(data).tabs('add', id, name);
        else
            $('#filtertabs' + session).append(data).tabs('add', id, name);
}

/*
** set_dashboard_title
**
** Sets the title of a session dashboard and any associated accordion 
*/
function set_dashboard_title(session, title) 
{
    ret = "";
    dashid = "#dash" + session;
    dashheadlabel = dashid + " .portlet-header .dashheadlabel";
    if ( $(dashid).length != 0 )
    {
        $(dashheadlabel).html(title);
    }
    return ret;
}

