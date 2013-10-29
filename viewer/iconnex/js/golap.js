var map = false;
var mapEngineInitialised = false;
var lastrefresh = "";
var stopIcon;
var stopXML;
var stopMarkers = [];

var systemMessages = [];
var lineCells = [];
var circleMarkers = [];
var circleLayers = [];
var circleFeatures = [];
var circleMetrics = [];
var circleParams = [];
var circleIcons = [];
var circleFilters = [];
var lineFilters = [];
var circleCounts = [];
var golapHttpRequests = [];
var sessionRenderElements = [];
//var sessionParams = [];
var busMarkers = [];
var fbId = null;
var siId = null;
var biId = null;
var forceZoom = false;
var selectedRoute = null;
var selectedStop = null;
var yii_base = "/";
var yii_framework_base = yii_base + "viewer/";
var osm_base = yii_base + "viewer/osm/";
var yii_framework_app_name = yii_framework_base
var yii_framework_app = yii_framework_base + "iconnex/";
var yii_menu_project = "infohost";
var lastGridSelection;
var autorefreshes = [];
var tooltiparr = "";
var mapNeedsRefresh = true;


// Indicate whether mapping system is in autozoom/autocentre mode
var g_mappingAutoZoom = false;
var g_mappingAutoCentre = false;

// When displaying markers, if the system is in the process of turning on and off markers
// and anther request occurs ( eg user check map filter ) then this flag allows existing rendering to beinterrupted
var lastRenderInstance = -1;

var mapEngine = "google";

function set_url_base(in_framework_base, in_framework_app_name)
{
	yii_framework_base = in_framework_base;
	yii_framework_app_name = in_framework_app_name;
	yii_framework_app = in_framework_base + in_framework_app_name;
}

var dodebug = false;
function debug ( msg, clear )
{
	if ( !dodebug )
		return;
   var d = new Date();
  now = d.toTimeString();
	if ( clear ) $('#debug').attr('innerHTML', "");
	$('#debug').append(" >" +now + ": " + msg);
}

/*
** Populate marker popup window with marker text field values
** and click link contents
*/
function textInfo(intxt, popupUrl)
{
    var webstop;
    var buses;
    var sTmp;
    var appHTML;

    // If a click link URL is provided then use that for the contents of
    // the popup box .. otherwise use the data attributes
    if ( popupUrl )
        mapPopup(popupUrl);
    else
    {
        d = document.getElementById('smallsubwindow')
        d.innerHTML = intxt;
    }
	$('#smallsubwindowframe').css('display', 'inline');
}

/*
** Populate marker popup window with url linked to
** from clicklink 
*/
function mapPopup(inUrl)
{
    var webstop;
    var buses;
    var sTmp;
    var appHTML;

    $('#smallsubwindowframe').addClass('critloading');
    $.ajax(
    {
        type: "GET",
        url: yii_framework_app + inUrl + "&user=" + iconnexUser,
        async: false,
        //dataType: "json",
        success: function(result)
        {
            $('#smallsubwindow').prepend(result);
            $('#smallsubwindowframe').removeClass('critloading');
        },
        error: function(x, e)
        {
            alert("Server error - unable to follow popup link");   
            $('#smallsubwindowframe').removeClass('critloading');
        }
    });
}


function set_error_status (msg )
{
        $('#pwierror').css("display", "inline");
        $('#pwierror').attr("innerHTML",  msg);
        $('#pwierror').addClass("errorstatus");
}

var loadingct = 0;
function set_loading_status (isloading )
{
    if ( isloading )
    {
		loadingct++;
		//$("#loadindicator").attr("innerHTML", "#"+loadingct);
        $('#criteriacol').addClass('critloading');
        $('#pwierror').css("display", "none");
        $('#pwierror').attr("innerHTML",  "");
        $('#pwierror').removeClass("errorstatus");
        set_big_loading_status(true);
    }
    else
    {
		loadingct--;
		if ( loadingct <= 0 )
		{
			//$("#loadindicator").attr("innerHTML", "#"+loadingct);
        	$('#criteriacol').removeClass('critloading');
            set_big_loading_status(false);
		}
		loadingct = 0;
    }
}

function set_big_loading_status (isloading )
{
    if ( isloading )
    {
        $('#mapping').addClass("bigloading");
        $('#bigloading').css("display", "inline");
    }
    else
    {
        $('#mapping').removeClass("bigloading");
        $('#bigloading').css("display", "none");
    }
}


// ----------------------------------------------------------------
// Initialize filter set and set to checked on or off depending on seton
// value. If filtername is passed then initialization is only applied
// to the matching filter name .. used in "Show All" mode where
// we only want to initialize the selected filter
function initGOLAPFilters(session, filtername, seton, cleartabs)
{
    ct = 0;
    ct1 = 0;

    if ( !circleFilters[session] )
	{
        circleFilters[session] = [];
        circleFilters[session]["on"] = true
        circleFilters[session]["mutex"] = false;
        circleFilters[session]["intersect"] = true;
        circleFilters[session]["mutexes"] = [];
        circleFilters[session]["data"] = [];
        circleFilters[session]["split"] = [];
        circleFilters[session]["metrics"] = [];
        circleFilters[session]["searchString"] = "";
        circleFilters[session]["movealerts"] = [];
	}
    
    set_loading_status ( true );
	if ( cleartabs ) 
    	removeAllMapTabs(session);
    for ( var index in circleFilters[session]["data"] )
    {
        if ( !index )
            continue;

        if ( filtername && filtername != index )
            continue;

        circleFilters[session]["mutexes"][index] = false;
        for ( var index2 in circleFilters[session]["data"][index] )
        {
            circleFilters[session]["data"][index][index2] = seton;
        }
    }
    if ( filtername && seton )
        $("#mpflttab_" + filtername.replace(/ /g,"_") + " .mapfilterck").attr("checked", "checked");
	else if ( seton )
		$(".mapfilterck").attr("checked", "checked");
	else
		$(".mapfilterck").removeAttr("checked");
    set_loading_status ( false );
	
    if ( circleMarkers[session] )
        for (var index in circleMarkers[session]) 
        {
	        circleParams[session][index]["changed"] = true;
        }
}

//--------------------------------------------------------------------
// Handles user filter search box text to only show filter checkboxes matching
// the user match string
function narrowDownFilterCheckboxes(session, fltname, matchstring, limit)
{
   // Create filter tabname tag
   tabnametag = fltname.replace(/ /g,"_");

   // If user entered only characters less than the limit in length then dont apply the filter
   // instead hide the filters
   if ( !matchstring || matchstring.length < limit )
   {  
            $('#mpflttab_' + tabnametag + " " + ".mapfilterck" ).css("display", "none");
            $('#mpflttab_' + tabnametag + " " + ".mapfiltercklabel" ).css("display", "none");
            $('#mpflttab_' + tabnametag + " " + ".mapfiltersearch" ).css("display", "inline");
            return;
    }

    // Create reg expression form user string
    matchrex = new RegExp(matchstring,"i");


    // Create data checkboxes by passing through all filter values
    for ( var index2 in circleFilters[session]["data"][fltname] )
    {
        if ( !index2 )
            continue;

        checktag = '#mfck_' + tabnametag + "_" + index2.replace(/ /g,"_").replace(/[\/()#.&@,+?']/g,"");
        checktaglabel = '#mfckl_' + tabnametag + "_" + index2.replace(/ /g,"_").replace(/[\/()#.&@,+?']/g,"");
        if ( index2.match(matchrex) )
        {
            $(checktag).css("display", "inline");
            $(checktaglabel).css("display", "inline");
        }
        else
        {  
            $(checktag).css("display", "none");
            $(checktaglabel).css("display", "none");
        }
     }

     resizeMap();
}


//--------------------------------------------------------------------
// Builds the Critera Widget and populates the widget
// with the filter tabs based on the AJAX return filters parameter
// and also the checkboxes based on the values contained within the
// returned data. If updateOnly is true then any new filter check values
// are just added and any missing ones are removed
// Otherwise the whole widget is recreated
function showGOLAPFilters(session, updateOnly)
{
    ct = 0;

    // Dont update and show filter if current menu selection 
    // does not match the query filter
    if ( current_menu_session && session != current_menu_session )
        return;

    // Initialize Filter Arrays for current session
    if ( !circleFilters[session] && !updateOnly )
		initGOLAPFilters(session, false, false, true);
    
    set_loading_status ( true );

    // Destroy the widget
    if ( !updateOnly )
        removeAllMapTabs(session);

    // Create tabs for metrics
    tabitemct =0;
    tabtag = null;
    for ( var index in circleFilters[session]["metrics"] )
    {
        if ( !index )
            continue;

        tabtag = '#mpflttab_metrics';
        tabname = "metrics";
        if ( !$(tabtag).length && tabitemct == 0 )
        {
            // Create metric tab
            content = "";
            content = "<div class='mpflttab' id='mpflttab_" + tabname + "'>";
            content += '<input style="display:inline; clear:left" type="hidden" name="session_name" value="' + session + '" />';

            content += "<div class='samplesize' style='float: left; margin-right: 10px'></div>";
        }
        tabitemct++;

        checktag = 'mfck_' + tabname + "_" + index.replace(/ /g,"_").replace(/[\/()#.&@,+?]/g,"");
        if ( !$(tabtag).length || !updateOnly )
        {
            content += "<input type='radio' name='" + index + "' id='" + checktag + "' class='mapfilterrad'";
            if ( circleFilters[session]["metrics"][index] )
                content += " checked='checked'"; 
            content += "/>" + index;
        }
        // Just add checkbos to existing tab
        else  if ( !$("#" + checktag).length )
        {
            chk = "<input type='radio' name='" + index + "' id='" + checktag + "' class='mapfilterrad'";
            if ( circleFilters[session]["metrics"][index] )
                chk += " checked='checked'"; 
            chk += "/>" + index;
            $('#mpflttab_' + tabname).append(chk);
        }
    }
    if ( circleFilters[session]["metrics"] )
    {
        if ( tabtag != null )
        if ( !$(tabtag).length || !updateOnly )
        {
            content += "</div>";
            addMapTab ( session, "#" + 'mpflttab_metrics', "Metrics", content);
        }
    }

    // Create tabs for filters
    for ( var index in circleFilters[session]["data"] )
    {
        if ( !index )
            continue;

        tabtag = '#mpflttab_' + index.replace(/ /g,"_");
        tabname = index.replace(/ /g,"_");

        if ( !updateOnly || ( !$(tabtag).length && tabitemct == 0 ) )
        {
            updateOnly = false;
        }
        else
            updateOnly = true;

        if ( !$(tabtag).length || !updateOnly )
        {
            // Create filter tab
            content = "";
            content += "<div class='mpflttab' id='mpflttab_" + tabname + "'>";
            content += '<input style="display:inline; clear:left" type="hidden" name="session_name" value="' + session + '" />';

            content += "<div class='samplesize' style='float: left; margin-right: 10px'></div>";

            // Create Intersect/Combine Keys checkbox
            content += "<input type='checkbox' name='" + index2 + "' title='oo' class='mapfilterck_intersect'";
            if ( circleFilters[session]["intersect"] )
                content += " checked='checked'"; 
            content += "/>" + "Combine Filters?";

            // Create mutex/show only checkbox
            content += "<input type='checkbox' name='" + index2 + "' class='mapfilterck_mutex'";
            if ( circleFilters[session]["mutex"] )
                content += " checked='checked'"; 
            content += "/>" + "Show Only?";
            content += "<button label='All' class='mapfilterck_showall' >All</button>";
            content += "<button label='None' class='mapfilterck_shownone'>None</button>";
        }

        optionct = 0;

        content += '&nbsp;<span class="mapfiltersearch">' + 
                '<input type="text" value="Enter Search" name="' + 'mfcks_' + tabname + '" class="mapfiltersearchbox" id="' + 'mfcks_' + tabname + '" class="oo" />'  + '</span>';

        // Create data checkboxes by passing through all filter values
        for ( var index2 in circleFilters[session]["data"][index] )
        {
            if ( !index2 )
                continue;

            optionct++;
            
            checktag = 'mfck_' + tabname + "_" + index2.replace(/ /g,"_").replace(/[\/()#.&@,+?']/g,"");
            checktaglabel = 'mfckl_' + tabname + "_" + index2.replace(/ /g,"_").replace(/[\/()#.&@,+?']/g,"");
            if ( !$(tabtag).length || !updateOnly )
            {
                content += "<input type='checkbox' name='" + index2 + "' id='" + checktag + "' class='mapfilterck'";
                if ( circleFilters[session]["data"][index][index2] )
                    content += " checked='checked'"; 
                content += "/>" + '<span id="' + checktaglabel + '" class="mapfiltercklabel">' + index2 + '</span>';
            }
            // Just add checkbos to existing tab - if value is NOFILTER then it should not be addded
            else  if ( !$("#" + checktag).length && index2 != 'NOFILTER' )
            {
                chk = "<input type='checkbox' name='" + index2 + "' id='" + checktag + "' class='mapfilterck'";
                if ( circleFilters[session]["data"][index][index2] )
                    chk += " checked='checked'"; 
                chk += "/>" + '<span id="' + checktaglabel + '" class="mapfiltercklabel">' + index2 + '</span></input>';
                $('#mpflttab_' + tabname).append(chk);
            }
        }

        if ( !$(tabtag).length || !updateOnly )
        {
            content += "</div>";
            addMapTab ( session, "#" + 'mpflttab_' + tabname, index, content);
        }

        // If more than 50 items are checkable in filter then instead show user a search
        // text box where user can narrow down the filter options, and hide the checks
        if ( !updateOnly )
        {
        if ( optionct > 60 )
        {
            $('#mpflttab_' + tabname + " " + ".mapfilterck" ).css("display", "none");
            $('#mpflttab_' + tabname + " " + ".mapfiltercklabel" ).css("display", "none");
            $('#mpflttab_' + tabname + " " + ".mapfiltersearch" ).css("display", "inline");
        }
        else
        {
            if ( optionct < 20 )
                $('#mpflttab_' + tabname + " " + ".mapfiltersearch" ).css("display", "none");
            else                    
                $('#mpflttab_' + tabname + " " + ".mapfiltersearch" ).css("display", "inline");
            $('#mpflttab_' + tabname + " " + ".mapfilterck" ).css("display", "inline");
            $('#mpflttab_' + tabname + " " + ".mapfiltercklabel" ).css("display", "inline");
        }
        }
    }

	if ( get_session_param ( session, "hasline" ) )
    {
        $("#linefilter" + session).css("display", "inline");
    }
    else
    {
        $("#mapfilter").css("display", "inline");
    }
    set_loading_status ( false );
}

function applyGOLAPFiltersLine(session, mode, mutexElement, mutexValue)
{
		for ( var index in circleFilters[session]["data"] )
		{
			for ( var index2 in circleFilters[session]["data"][index] )
			{
				if ( mode == "SHOWALL" )
					circleFilters[session]["data"][index][index2] = true;
				else if ( mode == "SHOWNONE" )
					circleFilters[session]["data"][index][index2] = false;
				else if ( circleFilters[session].mutex ) 
				{
					if ( index == mutexElement )
					{
   						tagname = ".mapfilterck";
						$(tagname).each ( function () {
							rex = new RegExp("mfck_" +  elementName.replace(/ /g,"_"), "i")
							if ( this.id.match ( rex ) )
							{
								nm = $(this).attr("name" );
								if ( $(this).attr('checked') )
									$(this).removeAttr('checked');
								if ( nm == mutexValue )
								{
									$(this).attr('checked', 'checked');
								}
							}
						} );
							
						if ( index2 == mutexValue )
						{
							circleFilters[session]["data"][index][index2] = true;
						}
						else
							circleFilters[session]["data"][index][index2] = false;
					}
					else
						circleFilters[session]["data"][index][index2] = true;
				}
			}
		}
}

// --------------------------------------------------------
// Will effect activation of filter based on users check selections
// If Show Only/Mutex mode then ensure that only items matching the checked value are matched in the CURRENT filter criteria  
// If Intersect mode then ensure that any items not matching ALL checked filters are turned off
function applyGOLAPFiltersMap(session, mode, mutexElement, mutexValue, forceRefreshAllMarkers)
{

    if ( forceRefreshAllMarkers )
        mapNeedsRefresh = true;

    // If mutexing then uncheck any nonmatching markers in the 
    // current filter crtieria

    // First ensure only one checked checkbox in selected filter criteria
    // if filter is mutexed
    if ( mode == "FILTER" )
	    for ( var index in circleFilters[session]["data"] )
	{
        if ( circleFilters[session]["mutexes"][index] &&  index == mutexElement )
	    {
            circleFilters[session]["mutexes"][index] = mutexValue;
			for ( var index2 in circleFilters[session]["data"][index] )
			{
   					tagname = ".mapfilterck";
					$(tagname).each ( function () {
						rex = new RegExp("mfck_" +  mutexElement.replace(/ /g,"_"), "i")
						if ( this.id.match ( rex ) )
						{
							nm = $(this).attr("name" );
							if ( nm != mutexValue )
                            {
							    if ( $(this).attr('checked') )
								    $(this).removeAttr('checked');
                            }
						}
					} );
			}
		}
    }

    // Now pass through each marker and decide, based on current filter
    // selections whether a marker should be displayed
    stopExistingRendering = true;
    lastRenderInstance++;
    if ( lastRenderInstance > 20 )
        lastRenderInstance = 1;
    renderMarkerVisibility(session, mode, 0, 0, 0,lastRenderInstance);

    //mapNeedsRefresh = false;

    //$("#progress").progressbar({value:0});
}

function showMarkerOnMap(n)
{
	if ( mapEngine == "google" )
	{
		n.setMap(map);
	}
	else
	{
		n.attributes.markerdisplay = "inline";
		n.layer.drawFeature(n);
	}
}

function hideMarkerOnMap(n)
{
	if ( mapEngine == "google" )
	{
		n.setMap(null);
	}
	else
	{
        n.attributes.markerdisplay = "none";
        n.layer.drawFeature(n);
	}
}

function getIconLat(n)
{
	if ( mapEngine == "google" )
		return n.getPosition().lat();
	else
    {
		return n.attributes.latitude;
    }
}

function getIconLon(n)
{
	if ( mapEngine == "google" )
		return n.getPosition().lng();
	else
    {
		return n.attributes.longitude;
    }
}

function getCircleLat(n)
{
	if ( mapEngine == "google" )
		return n.getCenter().lat();
	else
		return n.geometry.y;
}

function getCircleLon(n)
{
	if ( mapEngine == "google" )
		return n.getCenter().lng();
	else
		return n.geometry.x;
}

function mapCreateLayer(session)
{
	if ( !sessionParams[session].map_layer && mapEngine == "osm" )
	{
       sessionParams[session].map_layer = new OpenLayers.Layer.Vector("Simple " + session, {
                    styleMap: icon_stylemap,
                    renderers: renderer
                });
                //PPP sessionParams[session].map_layer.events.on({
                    //PPP 'featureselected': function(feature) {
                        //PPP $('counter').innerHTML = this.selectedFeatures.length;
                    //PPP },
                    //PPP 'featureunselected': function(feature) {
                        //PPP $('counter').innerHTML = this.selectedFeatures.length;
                    //PPP }
                //PPP });

                map.addLayer(sessionParams[session].map_layer);
                //PPP sessionParams[session].map_layer.events.on({
                    //PPP "featureselected": function(e) {
                        //PPP showOverlay();
                        //PPP clearOverlayIntervals();
                        //PPP textInfo("ooo");
                    //PPP },
                    //PPP "featureunselected": function(e) {
                    //PPP }
                //PPP });
	}
}

function mapCreateRenderer()
{
	if (!renderer && mapEngine == "osm" )
	{
        // allow testing of specific renderers via "?renderer=Canvas", etc
        renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
        renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
	}
}

function mapMarkerAttribute(n, index)
{
    markerValue = false;
	if ( mapEngine == "osm" )
	{
        if ( n.attributes[index] )
    	    markerValue = n.attributes[index];
	}
	else
	{
		markerValue = n.get(index);
	}
	return markerValue;
}
function mapCreateLayerStyles()
{
	if ( !layer_style && mapEngine == "osm" )
	{
        layer_style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
        layer_style.fillOpacity = 0.8;
        layer_style.graphicOpacity = 0.2;

        circle_style = OpenLayers.Util.extend({}, layer_style);
        circle_style.strokeColor = "${fillColor}";
        circle_style.fillColor = "${fillColor}";
        circle_style.graphicName = "circle";
        circle_style.pointRadius = "${pointRadius}";
        circle_style.strokeWidth = 0;
        circle_style.rotation = 0;
        circle_style.strokeLinecap = "butt";
        circle_style.display = "${markerdisplay}";

        circle_stylemap = new OpenLayers.StyleMap(circle_style);

        icon_style = OpenLayers.Util.extend({}, layer_style);
        icon_style.externalGraphic = "${imgurl}";
        icon_style.graphicWidth = "${graphicWidth}";
        icon_style.graphicHeight = "${graphicHeight}";
        icon_style.graphicXOffset = "${graphicXOffset}";
        icon_style.graphicYOffset = "${graphicYOffset}";
        icon_style.graphicOpacity = 1;
        icon_style.cursor = "pointer";
        icon_style.strokeColor = "${fillColor}";
        icon_style.fillColor = "${fillColor}";
        icon_style.graphicName = "icon";
        icon_style.pointRadius = "${pointRadius}";
        icon_style.strokeWidth = 0;
        icon_style.rotation = 0;
        icon_style.strokeLinecap = "butt";
        icon_style.display = "${markerdisplay}";

        icon_stylemap = new OpenLayers.StyleMap(icon_style);
	}
}

function zoomToBounds ( zoomLat, zoomLong, minLat, maxLat, minLong, maxLong, session, forceautozoomcentre )
{
	if ( mapEngine == "google" )
	{
    	ll = new google.maps.LatLng(zoomLat.toString(), zoomLong.toString());
    	var zoom = "15";
		var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(minLat - 0.0006, minLong), 
                                          new google.maps.LatLng(maxLat + 0.0006, maxLong));
        if ( minLat != 0.0 && minLong != 0.0 )
        {
            if ( g_mappingAutoZoom || forceautozoomcentre )
            {
                map.fitBounds(bounds);
            }
            else if ( g_mappingAutoCentre || forceautozoomcentre )
            {
                map.setCenter(ll);
            }
        }
	}
	else
	{
        var point1 = new OpenLayers.Geometry.Point(maxLong, minLat).transform(fromProjection,toProjection);;
        var point2 = new OpenLayers.Geometry.Point(minLong, maxLat).transform(fromProjection,toProjection);;

        var bounds = new OpenLayers.Bounds();
        bounds.extend(point1);
        bounds.extend(point2);
        bounds.toBBOX(); 


		if ( minLat != 0.0 && minLong != 0.0 )
		{
            if ( g_mappingAutoZoom || forceautozoomcentre )
			{
				map.zoomToExtent(bounds);
    		}
            else if ( g_mappingAutoCentre || forceautozoomcentre )
    		{
                map.setCenter(new OpenLayers.LonLat(zoomLong, zoomLat), 0); // 0=relative zoom level
			}
		}
	}
}

/*
** Shows all the markers on the map which correspond to users criteria to will
** bring on all markers that should be visible and hide those that shouldnt
** 
** Due to marker display potentially taking a lot of time the function only processes
** so many markers, then waits 
*/
function renderMarkerVisibility(session, mode, startfrom, visibletotal, markertotal, renderInstance)
{
    if ( startfrom == 0 )
    {
	    markertotal = 0;
	    visibletotal = 0;
        //$("#progress").progressbar({value:0});
    }
    loopct = 0;
    doct = 0;

    keyfield = false;
    if  ( sessionParams[session].keyfield )
        keyfield = sessionParams[session].keyfield;

    var gottoend = true;
   	for ( var ct in circleMarkers[session] )
   	{
        // If a new request to render markers has occured then
        // stop rendering
        if ( lastRenderInstance != renderInstance )
        {
            return;
        }

        gottoend = false;
        if ( loopct++ < startfrom ) continue;

        var ie = (function(){
            var undef,
                v = 3,
                div = document.createElement('div'),
                all = div.getElementsByTagName('i');
            while (
                div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
                all[0]
            );

            return v > 4 ? v : undef;
        }());

        delay = 300;
        if ( ie ) 
        {
            if ( ie < 8 )
                delay = 200;
            else if ( ie < 9 )
                delay = 350;
            else
                delay = 1000;
        }
        delay = 200;
        if ( doct >= delay ) 
        {
            //if ( ie ) 
                //alert ( "pausing at " + doct + " ie = " + ie );
            break;
        }
 
        startfrom++;
        loopct++;
        doct++;
        if ( circleMarkers[session].length == 0 )
            progpc = 0;
        else
            progpc = ( startfrom/ circleMarkers[session].length ) * 100;
        //$("#progress").progressbar({value:progpc});

		markertotal++;
       
       	n = circleMarkers[session][ct];
        markerVisible = true;
        displayInAnyFilter = false;
        //if ( ie && ie < 8 ) 
             //markerVisible = false;
        //else
        mapkey = circlect;
        if ( keyfield  )
			mapkey = keyfield;
        else
			mapkey = ct;

		keyValue = mapMarkerAttribute(n, mapkey);

        if ( mapNeedsRefresh || ( keyValue && circleParams[session][keyValue]["changed"] ))
        {
   		for ( var index in circleFilters[session]["data"] )
   		{
			markerValue = mapMarkerAttribute(n, index);

            displayInCurrentFilter = false;
            anychecked = false;
   			for ( var index2 in circleFilters[session]["data"][index] )
   			{
                if ( circleFilters[session]["split"][index] )
                {
                    fltarr = markerValue.split("/");
                    for ( var i2=0; i2<fltarr.length; ++i2 )
                    {
				        if ( circleFilters[session]["data"][index][fltarr[i2]] )
                            anychecked = true;

				        if ( circleFilters[session]["data"][index][fltarr[i2]] && index2 == fltarr[i2])
                        {
                            displayInCurrentFilter = true;
                            displayInAnyFilter = true;
                        }
                    }
                }
                else
                {
				    if ( circleFilters[session]["data"][index][index2] )
                        anychecked = true;

				    if ( circleFilters[session]["data"][index][index2] && index2 == markerValue)
                    {
                        displayInCurrentFilter = true;
                        displayInAnyFilter = true;
                    }
                }
			}

            // In intersect mode if any filter criteria in effect which filters out current marker
            // Then shouldnt be displayed
            if ( circleFilters[session].intersect && anychecked && !displayInCurrentFilter )
            {
                markerVisible = false;
            }
		}
        if ( !displayInAnyFilter )
        {
            markerVisible = false;
        }

       	n = circleMarkers[session][ct];
        if ( markerVisible )
        {
	        visibletotal++;
       	    if ( circleParams[session][ct]["visible"] && mode != "REFRESH" )
            {
                a = "oo";
            }
            else
            {
       	        circleParams[session][ct]["visible"] = true;
				showMarkerOnMap(n);
            }
        }
        else
        {
       	    circleParams[session][ct]["visible"] = false;
			hideMarkerOnMap(n);
        }
        }
        if ( mapkey )
        circleParams[session][keyValue]["changed"]  = false;
        gottoend = true;
   	}

    $(".samplesize").each(function(){ 
        $(this).attr('innerHTML', visibletotal + " / " + markertotal + " items"); 
    });

    var nextBatch = function () {
        renderMarkerVisibility(session, mode, startfrom,
                         visibletotal,
                         markertotal,
                         renderInstance
                         )
      };

    //len = circleMarkers[session].length ;
    //if ( startfrom < circleMarkers[session].length )
    if ( !gottoend )
        setTimeout(nextBatch, 25);
    else
        mapNeedsRefresh = false;


    
}

// -----------------------------------------------------------
// Looks at the markers that have had reportable event changes
// and logs them to the message box and raises event that
// there have been changes
function logMarkerChanges(session)
{
    ct = 0 ;
	for ( var idx in systemMessages )
    {
        ct ++;
        msg = 
            getClockTime() + ": " +
            "Marker " + systemMessages[idx]["item"] + ": " +
            systemMessages[idx]["element"] + " changed from " +
            systemMessages[idx]["from"] + " to " +
            systemMessages[idx]["to"];
        $("#messagegrid").prepend(msg + "<BR>");
    }
    if ( ct > 0 )
    {
	    $("#showmessages").addClass("newmessages");
	    $("#showmessages").removeClass("nomessages");
    }
    systemMessages.length = 0;
}

// -----------------------------------------------------------
// Takes the current set of filters and checked flags and ensures
// that only the markers thar are checked are shown
function applyFiltersToMarkers(session, forceautozoomcentre)
{
    	var minLat = parseFloat("0.00");
    	var maxLat = parseFloat("0.00");
    	var minLong = parseFloat("0.00");
    	var maxLong = parseFloat("0.00");
		
    	for ( var session in sessionParams )
        {
		circlect =  Object.size(circleMarkers);
    	for ( var ct in circleMarkers[session] )
    	{
        	n = circleMarkers[session][ct];
        
			if ( circleParams[session][ct]["visible"] )
			{
				if ( circleParams[session][ct]["plottype"] == "ICON" )
				{
                    iconlat = getIconLat(n);
                    iconlon = getIconLon(n);
                    if ( iconlat != 0 && iconlon != 0 )
                    {
                        if  ( parseFloat(getIconLat(n)) > maxLat || maxLat == 0 ) maxLat = getIconLat(n);
                        if  ( parseFloat(getIconLon(n)) > maxLong || maxLong == 0 ) maxLong = getIconLon(n);
                        if  ( parseFloat(getIconLon(n)) < minLong || minLong == 0 ) minLong = getIconLon(n);
                        if  ( parseFloat(getIconLat(n)) < minLat || minLat == 0 ) minLat = getIconLat(n);
                    }
				}
				else
				{
                    circlat = getCircleLat(n);
                    circlon = getCircleLon(n);
                    if ( circlelat != 0 && circlelon != 0 )
                    {
                        if  ( parseFloat(getCircleLat(n)) > maxLat || maxLat == 0 ) maxLat = getCircleLat(n);
                        if  ( parseFloat(getCircleLon(n)) > maxLong || maxLong == 0 ) maxLong = getCircleLon(n);
                        if  ( parseFloat(getCircleLon(n)) < minLong || minLong == 0 ) minLong = getCircleLon(n);
                        if  ( parseFloat(getCircleLat(n)) < minLat || minLat == 0 ) minLat = getCircleLat(n);
                    }
				}
				continue;
			}

    		for ( var index in circleFilters[session]["data"] )
    		{
				markerValue = mapMarkerAttribute(n, index);
        		if ( !index )
            		continue;
        		for ( var index2 in circleFilters[session]["data"][index] )
        		{
					if ( markerValue == index2 &&  circleFilters[session]["data"][index][index2])
					{
						if ( circleParams[session][ct]["plottype"] == "ICON" )
						{
                            iconlat = getIconLat(n);
                            iconlon = getIconLon(n);
                            if ( iconlat != 0 && iconlon != 0 )
                            {
                                if  ( parseFloat(getIconLat(n)) > maxLat || maxLat == 0 ) maxLat = getIconLat(n);
                                if  ( parseFloat(getIconLon(n)) > maxLong || maxLong == 0 ) maxLong = getIconLon(n);
                                if  ( parseFloat(getIconLon(n)) < minLong || minLong == 0 ) minLong = getIconLon(n);
                                if  ( parseFloat(getIconLat(n)) < minLat || minLat == 0 ) minLat = getIconLat(n);
                            }
						}
						else
						{
                            circlat = getCircleLat(n);
                            circlon = getCircleLon(n);
                            if ( circlelat != 0 && circlelon != 0 )
                            {
							    if  ( parseFloat(getCircleLat(n)) > maxLat || maxLat == 0 ) maxLat = getCircleLat(n);
							    if  ( parseFloat(getCircleLon(n)) > maxLong || maxLong == 0 ) maxLong = getCircleLon(n);
							    if  ( parseFloat(getCircleLon(n)) < minLong || minLong == 0 ) minLong = getCircleLon(n);
							    if  ( parseFloat(getCircleLat(n)) < minLat || minLat == 0 ) minLat = getCircleLat(n);
                            }
						}
					}
        		}
    		}
    	}
        }

		// Automatically zoom to the 
    	var zoomLat = minLat + ( ( maxLat - minLat ) / 2 );
    	var zoomLong = minLong + ( ( maxLong - minLong ) / 2 );

		
        // Only zoom to bounds if map view is visible
        mapshowing = $("#dashmap").css("display");
        if( mapshowing != "none" )
		    zoomToBounds ( zoomLat, zoomLong, minLat, maxLat, minLong, maxLong, session, forceautozoomcentre );


}

/*
function filterGOLAPMap(session, mode, elementName, value, checked)
{
		circlect =  Object.size(circleMarkers);
    	for ( var ct in circleMarkers[session] )
    	{
        	n = circleMarkers[session][ct];
        
        	markerValue = n.features[ct].attributes[elementName];
        	if ( markerValue == value ||  mode == "SHOWALL" || mode == "SHOWNONE" )
            	if ( checked )  {
        			circleParams[session][ct]["visible"] = true;
                	//PPP n.setMap(map);
            	} else {
        			circleParams[session][ct]["visible"] = false;
                	//PPP n.setMap(null);
            	}
			else
				if ( circleFilters[session].mutex ) 
				{
        			circleParams[session][ct]["visible"] = false;
                	//PPP n.setMap(null);
				}
					
    	}
		applyFiltersToMarkers(session, true);
}
*/

function filterGOLAPLine(session, mode, elementName, value, checked)
{
    	ct = 0;
		header1ct = 0;
		header2ct = 0;
		header2total = 0;
		header1total = 0;
		header2total = 0;
		header1total = $('#lv_container').children().length;
		$('#lv_container').children().each ( function () {
			turnonheader2s = false;
			turnoffheader2s = false;
			if ( elementName == "Operator Code" ||  mode == "SHOWALL" || mode == "SHOWNONE" )
			{
				arr = this.id.split("_");
				if ( arr.length > 1 )
				{
					if ( arr[1] == value  ||  mode == "SHOWALL" || mode == "SHOWNONE" )
					{
						if (  checked )
						{
							turnonheader2s = true;
							$(this).css("display", "inline" );
						}
						else
						{
							turnoffheader2s = true;
							$(this).css("display", "none" );
						}
					}
					if ( arr[1] != value && circleFilters[session].mutex )
					{
						$(this).css("display", "none" );
						turnoffheader2s = true;
					}
				}

			}
			if ( this.style.display != "none" )
					if ( this.id.match(/_/) )
						header1ct++;
			header2ct = 0
			$(this).children().each ( function () {
				if ( elementName == "Route Code" ||  mode == "SHOWALL" || mode == "SHOWNONE" )
				{
					arr = this.id.split("_");
					if ( arr.length > 2 )
					{
						if ( arr[2] == value  ||  mode == "SHOWALL" || mode == "SHOWNONE" )
							if (  checked )
								$(this).css("display", "inline" );
							else
								$(this).css("display", "none" );

						if ( arr[2] != value && circleFilters[session].mutex )
						{
							$(this).css("display", "none" );
						}
					}

				}
				arr = this.id.split("_");
				if ( arr.length > 2 )
				{
					if ( turnonheader2s )
						$(this).css("display", "inline" );
					if ( turnoffheader2s )
						$(this).css("display", "none" );
				}
					
				if ( this.style.display != "none" )
					if ( this.id.match(/_/) )
					{
						header2total++;
						header2ct++;
					}
			});
			if ( header2ct > 0 )
			{
				$(this).css("display", "inline" );
				newwidth = ( ( 100 / ( header2ct )) - 1.0 ) + "%";
				$(this).children().each ( function () {
					if ( this.id.match(/_/) )
					{
						$(this).css("width", newwidth);
						$(this).css("float", "left");
					}
					else
						$(this).css("width", "100%");
				});
			}
			else
				$(this).css("display", "none" );

			if ( this.style.display != "none" )
					if ( this.id.match(/_/) )
						header1ct++;
		});

		$('#lv_container').children().each ( function () {
			header2ct = 0;
			$(this).children().each ( function () {
				if ( this.id.match(/_/) )
					if ( this.style.display != "none" )
						header2ct++;
			});
			if ( this.style.display != "none" )
			{
				header1width = ( header2ct / header2total ) * 100;
				header1width += "%";
						$(this).css("width", header1width);
						$(this).css("float", "left");
			}
		} );
	
			
}

function filterGOLAP(session, mode, column, value, checked)
{
    ct = 0;
    ct1 = 0;

	if ( mode == "REFRESH" )
	{
	}
	else if ( mode == "SHOWALL" )
	{
		initGOLAPFilters(session, column, true, false);
        mapNeedsRefresh = true;
		checked = true;
	}
	else if ( mode == "SHOWNONE" )
	{
        mapNeedsRefresh = true;
		initGOLAPFilters(session, false, false, false);
		checked = false;
	}
	else if ( mode == "MUTEX" )
	{
        if ( checked )
        {
			for ( var index2 in circleFilters[session]["data"][column] )
            {
                circleFilters[session]["data"][column][index2] = false;
            }
		    checked = false;
		    circleFilters[session]["mutexes"][column] = true;
        }
        else
		    circleFilters[session]["mutexes"][column] = checked;
	}
	else if ( mode == "INTERSECT" )
	{
        a = "dummy";
	}
    else
    {
        if ( circleFilters[session]["mutexes"] )
        {
            if ( circleFilters[session]["mutexes"][column] )
            {
			    for ( var index2 in circleFilters[session]["data"][column] )
			    {
	                if ( index2 == value )
                        circleFilters[session]["data"][column][index2] = checked;
                    else
                        circleFilters[session]["data"][column][index2] = false;
                }
            }
            else
	            if ( circleFilters[session]["data"][column] )
	                circleFilters[session]["data"][column][value] = checked;
        }

        // Flag all markers as changed and need redraw that match the users check
        keyfield = get_session_param ( session, "keyfield" );
	    for (var index in circleMarkers[session]) 
        {
            n = circleMarkers[session][index];
			markerValue = mapMarkerAttribute(n, column);

            if ( markerValue == value )
		        circleParams[session][index]["changed"] = true;
        }
    }

	if ( get_session_param ( session, "hasmap" ) )
	{
   		if (circleMarkers[session]) {
			applyGOLAPFiltersMap(session, mode,column, value, true);
		    applyFiltersToMarkers(session, true);
		}
	}

	if ( get_session_param ( session, "hasline" ) )
	{
		applyGOLAPFiltersLine(session, mode,column, value);
		filterGOLAPLine ( session, mode, column, value, checked );
   	}
}


// Note that the visibility property must be a string enclosed in quotes
/*
USGSOverlay.prototype.hide = function() {
  if (this.div_) {
    this.div_.style.visibility = "hidden";
  }
}

USGSOverlay.prototype.show = function() {
  if (this.div_) {
    this.div_.style.visibility = "visible";
  }
}

USGSOverlay.prototype.toggle = function() {
  if (this.div_) {
    if (this.div_.style.visibility == "hidden") {
      this.show();
    } else {
      this.hide();
    }
  }
}

USGSOverlay.prototype.toggleDOM = function() {
  if (this.getMap()) {
    this.setMap(null);
  } else {
    this.setMap(this.map_);
  }
}
*/
function hideshowlayer(session, doshow)
{
    if (circleMarkers[session]) {
	circlect =  Object.size(circleMarkers);
    for ( var ct in circleMarkers[session] )
    {
        n = circleMarkers[session][ct];
        if ( doshow )  {
		  showMarkerOnMap(n);
        } else {
		  hideMarkerOnMap(n);
        }
    }
    }
}

function clearsession(session)
{
	deleteMarkers(session);
	if ( circleIcons[session] ) circleIcons[session].length = 0;
	if ( circleParams[session] ) circleParams[session].length = 0;
	if ( circleMarkers[session] ) circleMarkers[session].length = 0;
	if ( circleFilters[session] ) circleFilters[session].length = 0;
    removeAllMapTabs(session);
}

function deleteMarker(session, key)
{
    if (circleMarkers[session]) {
        if ( !circleMarkers[session][key] )
            return;
		if ( mapEngine == "osm" )
		{
        	//sessionParams[session].map_layer.removeAllFeatures();
        	//sessionParams[session].map_layer.destroyFeatures();
        	//map.removeLayer(sessionParams[session].map_layer);
		}
		else
		{
			n = circleMarkers[session][key];
			n.setMap(null);
		}
        delete circleMarkers[session][key];
    }
    if ( circleParams[session][key] )
		delete circleParams[session][key];

}

function deleteMarkers(session)
{
    if ( sessionParams[session] && sessionParams[session].map_control )
    {
		if ( mapEngine == "osm" )
		{
            sessionParams[session].map_control.unselectAll();
        	sessionParams[session].map_control.deactivate();
            map.removeControl(sessionParams[session].map_control);
        	sessionParams[session].map_control.destroy();
            sessionParams[session].map_control = false;

            if ( sessionParams[session].map_layer )
            {
        	    map.removeLayer(sessionParams[session].map_layer);
        	    sessionParams[session].map_layer.removeAllFeatures();
        	    sessionParams[session].map_layer.destroyFeatures();
        	    sessionParams[session].map_layer = false;
            }
		}
    }

    if (circleMarkers[session]) {
		circlect =  Object.size(circleMarkers);
		if ( mapEngine == "osm" )
		{
        	//sessionParams[session].map_layer.removeAllFeatures();
        	//sessionParams[session].map_layer.destroyFeatures();
        	//map.removeLayer(sessionParams[session].map_layer);
		}
		else
		{
			for ( var ct in circleMarkers[session] )
			{
				n = circleMarkers[session][ct];
				n.setMap(null);
			}
		}
        sessionParams[session].map_layer = null;
        circleIcons[session] = [];
        circleParams[session] = [];
        circleMarkers[session] = [];
        circleFilters[session] = [];
    }

}

function deleteLineCells(session)
{
    if (lineCells[session]) {
		cellct =  Object.size(circleMarkers);
    	for ( var ct in circleMarkers[session] )
        {
            n = lineCells[session][ct];
            n.length = 0 
        }
        lineCells[session].length = 0;
    }

}


function showStatus(pane, msg)
{
    var status = document.getElementById(pane);
    status.innerHTML = msg;
    status.style.display = "block";
}

function hideStatus(pane)
{
    document.getElementById(pane).style.display = "none";
}

function showOverlay()
{
    var o = document.getElementById("smallsubwindowframe");
    o.style.display = "none";
}

function clearOverlayIntervals()
{
    if (siId) clearInterval(siId);
    if (biId) clearInterval(biId);
}

function hideSubwindow(target)
{
    var o = document.getElementById(target + "frame");
    o.style.display = "none";
}

function hideOverlay()
{
    clearOverlayIntervals();
    var o = document.getElementById("smallsubwindowframe");
    o.style.display = "none";
}

function toggleLocs(el, mode)
{
    var next = el.nextSibling;

    while (next.nodeType != 1)
        next = next.nextSibling;
    if (next.id.substr(0, 5) == "rlocs")
    {
        if (mode == 0)
            next.style.display = "none";
        else if (mode == 1)
            next.style.display = "block";
        else
            next.style.display = ((next.style.display == "none" && mode != 0) ? "block" : "none");
    }

    next = next.nextSibling;
    while (next.nodeType != 1)
        next = next.nextSibling;
    if (next.id.substr(0, 5) == "rlocs")
    {
        if (mode == -1)
            next.style.display = ((next.style.display == "none" && mode != 0) ? "block" : "none");
        else
            next.style.display = "none";
    }
}

function getStyle(el, cssprop)
{
    if (el.currentStyle)
        return el.currentStyle[cssprop];
    else if (document.defaultView && document.defaultView.getComputedStyle)
        return document.defaultView.getComputedStyle(el, "")[cssprop];
    else
        return el.style[cssprop];
}

function nothing(e)
{
    if (!e)
        var e = window.event;
    e.cancelBubble = true;

    if (e.stopPropagation)
        e.stopPropagation();
}

function switchDirection(el, n)
{
    var clicked = document.getElementById(el);
    toggleLocs(clicked, -1);
}

function getNodeValue(obj, tag)
{
    if (obj.getElementsByTagName(tag)[0] == null)
        return "";
    else
        return obj.getElementsByTagName(tag)[0].firstChild.nodeValue;
}

hhmmss = function(dt)
{
    var hours = dt.getHours();
    var minutes = dt.getMinutes();
    var seconds = dt.getSeconds();

    if (hours < 10) hours = '0' + hours;
    if (minutes < 10) minutes = '0' + minutes;
    if (seconds < 10) seconds = '0' + seconds;

    return hours + ":" + minutes + ":" + seconds;
}       

function speedInfo(speed, vehicle, indate, intime, geohash)
{
    var webstop;
    var buses;
    var sTmp;
    var appHTML;

    if (navigator.appName == "Microsoft Internet Explorer") {
        webstop = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        webstop = new XMLHttpRequest();
    }

    sTmp = "/infohostpd/cgi-bin/wsw2.sh?" + "xx";
    webstop.open("GET", sTmp, true);
    webstop.onreadystatechange = function() {
        if (webstop.readyState == 4) {
            hideStatus("dstatus");
            var wsHTML;
            wsHTML = "Vehicle " + vehicle.toString() + "  ";
            wsHTML += "Speed " + speed.toString() + "  ";
            wsHTML += "Time " + indate.toString() + " " + intime + "  ";
            wsHTML += "Geohash " + geohash + "\n";
            d = document.getElementById("depinfo")
            d.innerHTML = wsHTML;
        }
    }
    //showStatus("dstatus", "Loading...");
    webstop.send(null);
}

function sstopInfo(atcoCode)
{
    var webstop;
    var buses;
    var sTmp;
    var appHTML;

    if (navigator.appName == "Microsoft Internet Explorer") {
        webstop = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        webstop = new XMLHttpRequest();
    }

    sTmp = "/infohostpd/cgi-bin/wsw2.sh?" + atcoCode;
    webstop.open("GET", sTmp, "true");
    webstop.onreadystatechange = function() {
        if (webstop.readyState == 4) {
            hideStatus("dstatus");
            var wsXML = webstop.responseXML;
            var wsHTML;
            var calls = wsXML.getElementsByTagName("call");
            wsHTML = "<p style=\"margin:0; text-align:center;\"><strong>" + getNodeValue(wsXML, "common_name") + " - " + getNodeValue(wsXML, "naptan_code") + "</strong></p>";
            if (calls.length == 0)
                wsHTML += "<br><p style=\"font-size:large\; text-align:center\">Please refer to timetable</p>";
            else
            {
                wsHTML += "<TABLE BORDER=0 WIDTH=\"100%\" style=\"font-size:small;\"><TR><TH align=left>Route</TH><TH align=left>Destination</TH><TH align=right>Due</TH></TR>";
                for (var i = 0; i < calls.length; i++) {
                    wsHTML += "<TR><TD align=center>";
                    wsHTML += getNodeValue(calls[i], "route");
                    wsHTML += "</TD><TD>";
                    wsHTML += getNodeValue(calls[i], "destination");
                    wsHTML += "</TD><TD ALIGN=right>"
                    wsHTML += getNodeValue(calls[i], "eta");
                    wsHTML += "</TD></TR>";
                }
                wsHTML += "</TABLE>";
            }
            d = document.getElementById("depinfo")
            d.innerHTML = wsHTML;

            var messages = wsXML.getElementsByTagName("msg");
            var mHTML = "";
            for (var i = 0; i < messages.length; i++) {
                mHTML += "<p>";
                mHTML += messages[i].firstChild.nodeValue;
                mHTML += "</p>";
            }
            var m = document.getElementById("messages")
            if (mHTML.length > 0) {
                m.innerHTML = mHTML;
                d.style.height = "120px";
                m.style.display = "block";
            }
            else {
                m.innerHTML = "";
                m.style.display = "none";
                d.style.height = null;
            }

            appHTML = '<a href="javascript:approaching(\'' + atcoCode + '\')">Show buses approaching this stop</a>';
            document.getElementById("approaching").innerHTML = appHTML;
            var url = "http://" + location.hostname + "/infohostpd/cgi-bin/wsw3.sh?" + atcoCode;
            document.getElementById("popout").innerHTML = '<a title="Show in new window" href="' + url + '" target="_blank"><img src="images/popout.png"></img></a></div>';
            var now = new Date();
            var nowTime = hhmmss(now);
            showStatus("dstatus", "Last refreshed " + nowTime);
        }
    }
    //showStatus("dstatus", "Loading...");
    webstop.send(null);
}

function busCalls(r)
{
    var depsXML = r.responseXML;
    var appHTML;
    var veh = depsXML.getElementsByTagName("Vehicle");
    var arrs = depsXML.getElementsByTagName("MonitoredCall");
    hideStatus("dstatus");
    var depinfo = "<table width=100% style=\"font-size:small;\">\n";
    depinfo += "<tr style=\"font-weight:bold\"><th colspan=2>Bus " + getNodeValue(veh[0], "VehicleCode") + " on Route " + getNodeValue(veh[0], "Route") + "</th></tr>\n";
    depinfo += "<tr style=\"font-weight:bold\"><th align=left>Next Stop</th><th align=right>Due</th></tr>\n";
    for (var i = 0; i < arrs.length; i++) {
        depinfo += "<tr>";
        depinfo += "<td>" + getNodeValue(arrs[i], "Location") + "</td>";
        depinfo += "<td align=right>" + getNodeValue(arrs[i], "ETD") + "</td>";
        depinfo += "</tr>\n";
    }
    depinfo += "</table>\n";
    document.getElementById("depinfo").innerHTML = depinfo;
    appHTML = '<div style="float:left;">&nbsp;</div>';
    appHTML += '<div style="text-align:right;">&nbsp;</a></div>';
    document.getElementById("approaching").innerHTML = appHTML;
    var now = new Date();
    var nowTime = hhmmss(now);
    showStatus("dstatus", "Last refreshed " + nowTime);
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function get_golap_session_closest ( element, selector )
{
    container = $(element).closest(selector);
    var a = container.find('input');
    var session = "";
   	a.each(function(index)  {
   		if ( this.name == "session_name" )
		{
			session = this.value;
			return;
		}
	});
	return session;

}

function get_golap_session ( selector )
{
	var a = $(selector).find('input');
	var session = "";
   	a.each(function(index)  {
   		if ( this.name == "session_name" )
		{
			session = this.value;
			return;
		}
	});
	return session;

}


function updateLineView(session, lineData, calledfromrefresh)
{
  if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
		return false;

  title = lineData.title;
  data = lineData.data;

  var needrecentre = 1;

  var d = new Date();
  now = d.getMilliseconds();


  // Data provides data names that populate line header1 (eg.operator), header2 ( eg.route ), no cols, col1, col2, col3 etc
  var header1label = "<none>";
  var header2label = "<none>";
  var nocols = 4;
  var whichcolumn = "<none>";
  var whichrow = "<none>";
  var direction = "down";
  var valuefield = "<none>";
  var keyfield = "<none>";
  var refresh = 0;
  var filtermovealerts = false;

  renderType = "";
  renderElements = "";
  header1 = "<none>";
  header2 = "<none>";
  var displaylike = lineData.displaylike;
    len = data.length;

    if ( !len && len < 1 )
        refresh = 1;

  circlect = 0;

  if( displaylike )
  {
    if ( displaylike ["FilterMoveAlerts"] ) filtermovealerts = displaylike["FilterMoveAlerts" ];
    if ( displaylike ["Filters"] ) filters = displaylike["Filters" ];
    if ( displaylike ["ValueField"] ) valuefield = displaylike["ValueField" ];
    if ( displaylike ["Header1"] ) header1 = displaylike["Header1" ];
    if ( displaylike ["Header2"] ) header2 = displaylike["Header2" ];
    if ( displaylike ["NumberColumns"] ) nocols = parseInt(displaylike["NumberColumns" ]);
    if ( displaylike ["RenderType"] ) renderType = displaylike["RenderType" ];
    if ( displaylike ["RenderElements"] ) renderElements = displaylike["RenderElements" ];
    if ( displaylike ["KeyField"] ) keyfield = displaylike["KeyField" ];

	// Put the data in the right cells
    if ( displaylike ["WhichColumn"] ) whichcolumn = displaylike["WhichColumn" ];
    if ( displaylike ["WhichRow"] ) whichrow = displaylike["WhichRow" ];
    if ( displaylike ["ReverseOrder"] ) reverseorder = parseInt(displaylike["ReverseOrder" ]);
    if ( displaylike ["Refresh"] ) refresh = parseInt(displaylike["Refresh" ]);
  }
  else
    refresh = 1;

    if ( !circleFilters[session] )
		initGOLAPFilters(session, false, false, true);
        
    if ( filters != "" )
    {
    	if ( !circleFilters[session] )
			initGOLAPFilters(session, false, false, true);
        fltarr = filters.split(";");
        for ( var i=0; i<fltarr.length; ++i ){
            circleFilters[session]["data"][fltarr[i]] = [];
        }
    }
    else
        circleFilters[session]["data"] = false;

    if ( filtermovealerts != "" )
    {
        fltarr = filtermovealerts.split(";");
        for ( var i=0; i<fltarr.length; ++i )
        {
            alertarr = fltarr[i].split(">");
            alerttrigger = alertarr[1];
            options = alerttrigger.split("/");
            circleFilters[session]["movealerts"][fltarr[i]] = [];
        }
    }
    else
        circleFilters[session]["movealerts"] = false;
        

	if (renderElements != "")
    {
        sessionRenderElements[session] = [];
        relarr = renderElements.split(";");
        for (var i = 0; i < relarr.length; ++i) {
            sessionRenderElements[session][relarr[i]] = [];
        }
    }
    else
        sessionRenderElements[session] = false;


	cells = [];
	labels = [];
	keys = [];

    for (var i = 0; i < data.length; i++)
	{
		if (i == 0)
		{
			minRow = parseInt(data[i][whichrow]);
			maxRow = parseFloat(data[i][whichrow]);
		}
		else
		{
			if  ( parseFloat(data[i][whichrow]) > maxRow ) maxRow = parseFloat(data[i][whichrow]);
			if  ( parseFloat(data[i][whichrow]) < minRow ) minRow = parseFloat(data[i][whichrow]);
		}

		rowno = data[i][whichrow];
		rowno = rowno.replace(/\'/g,"");
		columnno = parseInt(data[i][whichcolumn]);
		keyno = data[i][keyfield];


		header1val = data[i][header1];
		header2val = data[i][header2];
		if ( !cells[header1val] )
		{
			cells[header1val]  = [];
			labels[header1val]  = [];
			keys[header1val]  = [];
		}
		if ( !cells[header1val][header2val] )
		{
			cells[header1val][header2val] = [];
			labels[header1val][header2val] = [];
			keys[header1val][header2val] = [];
		}
		if ( !cells[header1val][header2val][columnno] )
		{
			cells[header1val][header2val][columnno]= [];
			labels[header1val][header2val][columnno]= [];
			keys[header1val][header2val][columnno]= [];
		}


		cct = cells[header1val][header2val][columnno].length;
		cct = rowno;
		cells[header1val][header2val][columnno][ cct ] = data[i][valuefield];

		//ll = new google.maps.LatLng(data[i]["Latitude"], data[i]["Longitude"]);

			txt = "";
			elementTypes = "";
			elementValues = "";
			ect = 0;
			for (var index in data[i]) {
				if (index == "Now" && index != "")
					refesh_time = data[i][index];
						
				if (index != "Longitude" && index != "Latitude" && index != "Line Plot" && index != "")
					if ( index != "Line Plot" )
						txt = txt + index + ": " + data[i][index] + " \
";
					//else
						//txt = txt + index + ": ";

                rexp = new RegExp(",", "g");
				for (var el in sessionRenderElements[session]) {
					if (el == index)
					{
						if ( ect > 0 ) 
						{
							elementTypes += ",";
							elementValues += ",";
						}

						ect ++;
					    elementTypes = elementTypes + el;
                        if ( el == "metrics" )
                        {
						    elementValues = elementValues + data[i][el];
                            for ( var metric in circleFilters[session]["metrics"] )
                            {
                                if ( circleFilters[session]["metrics"][metric] )
                                    elementValues = metric;
                            }
                        }
                        else
                        {
                            // Not allowed commas in filter tab values
                            param = data[i][el].replace(rexp, "");
						    elementValues = elementValues + param;
                        }
					}
				}
			
			labels[header1val][header2val][columnno][rowno] = txt;
			keys[header1val][header2val][columnno][rowno] = keyno;
			var dynamicIcon = stopIcon;
			if (renderType != "")
			{
				dynamicIcon = yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues + "&now=" + now
			}

			// Build filter Sets
			for ( var index in data[i] )
				if ( index != "Longitude" && index != "Latitude" && index != "" && index )
				{
					if ( circleFilters[session]["data"][index] )
					{
						if ( !circleFilters[session]["data"][index][data[i][index]] )
							circleFilters[session]["data"][index][data[i][index]] = 1;
					}
				}
				
    	}
    }

	str = "<div id='lv_container'>";
	lct1 = 0;

	if ( !refresh )
	{
		len1 =  Object.size(cells);
		hdr1width = Math.floor(100 / len1) + "%"
		for (var index in cells) {
			len2 =  Object.size(cells[index]);
			hdr2width = Math.floor(100 / len2) + "%";
			for (var index2 in cells[index]) {
				maxsize = "unk";
				for (var index3 in cells[index][index2]) {
					if ( maxsize == "unk" )
						maxsize =  Object.size(cells[index][index2][index3]);
					len3 =  Object.size(cells[index][index2][index3]);
					if ( len3 > maxsize )
						maxsize = len3;
				}
				for (var index3 in cells[index][index2]) {
					len3 =  Object.size(cells[index][index2][index3]);
					while ( len3 < maxsize ) {
						cells[index][index2][index3]["Empty_" + len3] = "";
						len3 ++;
					}
				}
			}
		}
		len1 =  Object.size(cells);
		hdr1width = Math.floor(100 / len1) + "%"
		hdr1ct = 0;
		hdr2ct = 0;
		for (var index in cells) {
			if( hdr1ct++ % 2 == 0 )
				hclass = "lvHeader1 lvHeader1Even ui-widget-content ui-state-active ui-widget-header ui-state-active"
			else
				hclass = "lvHeader1 lvHeader1Odd ui-widget-content ui-state-active ui-widget-header ui-state-active"
			str += "<div id='lv_" + index + "' style='width:" + hdr1width + "; float: left;'>";
			lct2 = 0;
			len2 =  Object.size(cells[index]);
			//hdr2width = Math.ceil(100 / ( len2 )) + "%";
			hdr2width = ((100 / len2 ) - 1 ) + "%";
			str += "<div  class='" + hclass + "' style='width: 100%;text-align: center; float: left;'>" + index + "</div>";

			for (var index2 in cells[index]) {
				if( hdr2ct++ % 2 == 0 )
					hclass = "lvHeader2 lvHeader2Even"
				else
					hclass = "lvHeader2 lvHeader2Odd"
				str += "<div id='lv_" + index + "_" + index2 + "' class='" + hclass + "' style='width:" + hdr2width + "; float: left;'>";
				str += "<div style='width: 100% float: left; width: 100%; text-align: center'>" + index2+ "</div>";
				//str += "<table id='lvHeader2' style='width:100%; text-align: center'>";
				len3 =  Object.size(cells[index][index2]);
				hdr3width = Math.floor(100 / len3) + "%";
				hdr3width = "25%";
				lastindex = "-1";
				for (var index3 in cells[index][index2]) {
					lct3 = 0;

					ict = parseInt(lastindex) + 1;
					dirct = 0
					while ( dirct <= 1 )
					{
						ict = parseInt ( index3);
						if ( ict == 0 ) ict = dirct
						else ict = 2 + dirct;
						if ( ict == 0 )
							hclass = "lvStopCellLeft";
						else if ( ict == 1 )
							hclass = "lvMidCell lvMidCellLeft";
						else if ( ict == 2 )
							hclass = "lvMidCell lvMidCellRight";
						else if ( ict == 3 )
							hclass = "lvStopCellRight";

						str += "<div id='lv_" + index + "_" + index2 + "_" + ict + "' class='" + hclass + "' style='width:" + hdr3width + "; float: left;'>";
						lastidx = 0;
						len3 =  Object.size(cells[index][index3]);
						for (var index4 in cells[index][index2][index3]) {
if ( index4 == "SN120562" )
	x = 4;
							lct4 = 0;
							if ( ict == parseInt(index3) )
							{
								value = cells[index][index2][index3][index4];
								label = labels[index][index2][index3][index4];
								str += "<div title=\"" + label + "\" id='lv_" + index + "_" + index2 + "_" + index3 + "_" + index4 + "' class='" + hclass + "' style='white-space: nowrap;overflow: hidden; width:" + "100%" + "; float: left;'>";
								str += value;
								str += "</div>";
							}
							else
							{
								value = cells[index][index2][index3][index4];
								label = labels[index][index2][index3][index4];
								dupcol = ict;
								str += "<div title=\"" + label + "\" id='lv_" + index + "_" + index2 + "_" + dupcol + "_" + index4 + "' class='" + hclass + "' style='white-space: nowrap;overflow: hidden; width:" + "100%" + "; float: left;'>&nbsp;";
								str += "</div>";
							}
						}
						dirct++;
						//if ( lct3++ == 0 ) continue;
						str += "</div>";
					}
					lastindex = index3;
				}
				//if ( lct2++ == 0 ) continue;
				str += "</div>";
			}
			str += "</div>";
		}
		str += "</div>";
		$("#dashline" + session + " .lineview" ).attr("innerHTML", str);
	}
	else	
	{
		len1 =  Object.size(cells);
		hdr1width = Math.floor(100 / len1) + "%"
		for (var index in cells) {
			lct2 = 0;
			len2 =  Object.size(cells[index]);
			hdr2width = Math.floor(100 / len2) + "%";
			for (var index2 in cells[index]) {
				len3 =  Object.size(cells[index][index2]);
				hdr3width = Math.floor(100 / len3) + "%";
				for (var index3 in cells[index][index2]) {
					lct3 = 0;
					lastidx = 0;
					len3 =  Object.size(cells[index][index3]);
					for (var index4 in cells[index][index2][index3]) {
						lct4 = 0;
						value = cells[index][index2][index3][index4];
						label = labels[index][index2][index3][index4];
						key = keys[index][index2][index3][index4];
						targetid = $( "#lv_" + index + "_" + index2 + "_" + index3 + "_" + index4);
						targetclass = $( ".kv_"  + key  );
						if ( targetid.length > 0  )
						{
							if ( targetclass )
							{
								targetclass.attr("innerHTML", "&nbsp;");
								targetclass.attr("title", "");
								targetclass.removeClass("kv_" + key);
							}
							targetid.attr("title", label);
							targetid.attr("innerHTML", value);
							targetid.addClass("kv_" + key);
						}
					}
					//if ( lct3++ == 0 ) continue;
				}
				//if ( lct2++ == 0 ) continue;
			}
		}
		//$("#dashline" + session).attr("innerHTML", str);
	}
}

var layer_style = false;
var circle_style = false;
var circle_stylemap = false;
var icon_style = false;
var icon_stylemap = false;

var renderer = false;

function updateGOLAP(session, mapData, calledfromrefresh)
{
  mapCreateRenderer();
  mapCreateLayerStyles();

  if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
		return false;

  if ( mapData.error )
  {
		if ( !calledfromrefresh )
			alert(mapData.error);
        set_error_status(mapData.error);
        return false;
  }

  title = mapData.title;
  data = mapData.data;

  var d = new Date();
  now = d.getMilliseconds();


  var filtermovealerts = false;
  plotType = "Icon";
  metricName = "";
  metricRangeUpper = 50;
  metricRangeLower = 0;
  filters = "";
  splitFilters = "";
  tooltips = "";
  plotSize = 30;
  plotSizeMetric = "";
  plotSizeMinimum = plotSize;
  plotSizeMaximum = plotSize;
  plotSizeMetricMinimum = -99999;
  plotSizeMetricMaximum = -99999;
  keyfield = "";
  hotspotx = 1;
  hotspoty = 1;
  clicklink = false;
  iconsizex = 20;
  iconsizey = 20;
  renderType = "";
  renderElements = "";
  serverTime = "";
  var displaylike = mapData.displaylike;

  circlect = 0;

  if( displaylike )
  {
    if ( displaylike ["FilterMoveAlerts"] ) filtermovealerts = displaylike["FilterMoveAlerts" ];
    if ( displaylike ["Timestamp"] ) sessionParams[session].lastrefresh = displaylike["Timestamp" ];
    if ( displaylike ["HotspotX"] ) hotspotx = displaylike["HotspotX" ];
    if ( displaylike ["HotspotY"] ) hotspoty = displaylike["HotspotY" ];
    if ( displaylike ["SizeX"] ) iconsizex = displaylike["SizeX" ];
    if ( displaylike ["SizeY"] ) iconsizey = displaylike["SizeY" ];
    if ( displaylike ["ClickLink"] ) clicklink = displaylike["ClickLink" ];
    if ( displaylike ["Filters"] ) filters = displaylike["Filters" ];
    if ( displaylike ["SplitFilters"] ) splitFilters = displaylike["SplitFilters" ];
    if ( displaylike ["Tooltips"] ) tooltips = displaylike["Tooltips" ];
    if ( displaylike ["Metric"] ) metricName = displaylike["Metric" ];
    if ( displaylike ["PlotSizeMetric"] ) plotSizeMetric = displaylike["PlotSizeMetric" ];
    if ( displaylike ["Type"] ) plotType = displaylike["Type" ];
    if ( displaylike ["MetricRangeUpper"] ) metricRangeUpper = parseInt(displaylike["MetricRangeUpper" ]);
    if ( displaylike ["MetricRangeLower"] ) metricRangeLower = parseInt(displaylike["MetricRangeLower" ]);
    if ( displaylike ["PlotSize"] ) plotSize = parseInt(displaylike["PlotSize" ]);
    if ( displaylike ["PlotSizeMinimum"] ) plotSizeMinimum = parseInt(displaylike["PlotSizeMinimum" ]);
    if ( displaylike ["PlotSizeMaximum"] ) plotSizeMaximum = parseInt(displaylike["PlotSizeMaximum" ]);
    if ( displaylike ["PlotSizeMetricMinimum"] ) plotSizeMetricMinimum = parseInt(displaylike["PlotSizeMetricMinimum" ]);
    if ( displaylike ["PlotSizeMetricMaximum"] ) plotSizeMetricMaximum = parseInt(displaylike["PlotSizeMetricMaximum" ]);
    if ( displaylike ["RenderType"] ) renderType = displaylike["RenderType" ];
    if ( displaylike ["RenderElements"] ) renderElements = displaylike["RenderElements" ];
    if ( displaylike ["KeyField"] ) keyfield = displaylike["KeyField" ];
    if ( displaylike ["KeyField"] ) sessionParams[session].keyfield = displaylike["KeyField" ];
  }

  if ( hotspotx == "L" )
      hotspotx = 1;
  if ( hotspotx == "R" )
      hotspotx = iconsizex;
  if ( hotspoty == "T" )
      hotspoty = 1;
  if ( hotspoty == "B" )
      hotspoty = iconsizey;

  //if ( mapEngine == "google" )
  //{
     //hotspoty = hotspoty - iconsizey;
  //} 

  // If query has brought back nothing the clear all markers from map and list
  // .. this however should probably  not be done since if a query returns
  // nothing then it probably does not mean that everything should be deleted.
  // Deletion of markers should be controlled by the row_status/row_chaged/DELETED 
  // flag mechanism.. so if there is a session last refresh this mechanism is in
  // place so just ignore it
  if ( !calledfromrefresh || ( keyfield == "" && !get_session_param(session, "lastrefresh" )) )
  {
  	    deleteMarkers(session);
  	    circleMarkers[session] = [];
  	    circleParams[session] = [];
  	    circleIcons[session] = [];
  	    circleFilters[session] = false;
  }

  mapCreateLayer(session);

  if ( !circleFilters[session] )
		initGOLAPFilters(session, false, false, true);
        
  if ( filters != "" )
  {
      fltarr = filters.split(";");
      for ( var i=0; i<fltarr.length; ++i ){
          if ( !circleFilters[session]["data"][fltarr[i]] )
          {
          	circleFilters[session]["split"][fltarr[i]] = false;
          	circleFilters[session]["data"][fltarr[i]] = [];
          }
      }
  }
  else
    if ( !calledfromrefresh )
    {
      circleFilters[session]["data"] = false;
      circleFilters[session]["split"] = false;
    }

  if ( splitFilters != "" )
  {
      fltarr = splitFilters.split(";");
      for ( var i=0; i<fltarr.length; ++i ){
          	circleFilters[session]["split"][fltarr[i]] = true;
      }
  }
  else
    if ( !calledfromrefresh )
    {
      circleFilters[session]["split"] = false;
    }

  if ( metricName != "" )
  {
        arr = metricName.split(";");
        for ( var i=0; i<arr.length; ++i ){
            if ( !circleFilters[session]["metrics"][arr[i]] )
                if ( i == 0 )
            	    circleFilters[session]["metrics"][arr[i]] = true;
                else
            	    circleFilters[session]["metrics"][arr[i]] = false;
        }
  }
  else
    if ( !calledfromrefresh )
      circleFilters[session]["metrics"] = false;

  if ( filtermovealerts != "" )
  {
      fltarr = filtermovealerts.split(";");
      for ( var i=0; i<fltarr.length; ++i )
      {
          alertarr = fltarr[i].split(">");
          alerttrigger = alertarr[1];
          options = alerttrigger.split("/");
          if ( !circleFilters[session]["movealerts"] )
              circleFilters[session]["movealerts"] = [];
  
          circleFilters[session]["movealerts"][alertarr[0]] = [];
	      for ( var index2 in options )
          {
             circleFilters[session]["movealerts"][alertarr[0]][options[index2]] = true;
           }
       }
  }
  else
    if ( !calledfromrefresh )
      circleFilters[session]["movealerts"] = false;
        
  if ( tooltips != "" )
  {
      tmparr = tooltips.split(";");
     tooltiparr = [];
     for ( var i=0; i<tmparr.length; ++i ){
        tooltiparr[tmparr[i]] = true;
        }
  }

if (renderElements != "")
    {
        sessionRenderElements[session] = [];
        relarr = renderElements.split(";");
        for (var i = 0; i < relarr.length; ++i) {
            sessionRenderElements[session][relarr[i]] = [];
        }
    }
    else
      if ( !calledfromrefresh )
        sessionRenderElements[session] = false;

    //$("#progress").progressbar({value:0});

    var minLat = parseFloat("0.00");
    var maxLat = parseFloat("0.00");
    var minLong = parseFloat("0.00");
    var maxLong = parseFloat("0.00");
    var maxMetric = parseFloat("0.00");
    var minMetric = parseFloat("0.00");
    for (var i = 0; i < data.length; i++)
	{
        progpc = ( i / data.length ) * 100;
        //$("#progress").progressbar({value:progpc});
		if (i == 0)
		{
			minLat = parseFloat(data[i]["Latitude"]);
			minLong = parseFloat(data[i]["Longitude"]);
			maxLat = parseFloat(data[i]["Latitude"]);
			maxLong = parseFloat(data[i]["Longitude"]);

			if ( title == "Vehicle Positions" )
				maxMetric = parseFloat(data[i]["Speed Mph"]);
			if ( title == "PAESA Max Speed" )
				maxMetric = parseFloat(data[i]["Max Speed"]);
			if ( title == "PAESE Heavy Accel" )
				maxMetric = parseFloat(data[i]["Heavy Accel"]);
		}
		else
		{
			if  ( parseFloat(data[i]["Latitude"]) > maxLat ) maxLat = parseFloat(data[i]["Latitude"]);
			if  ( parseFloat(data[i]["Latitude"]) < minLat && parseFloat(data[i]["Latitude"]) != 0 ) minLat = parseFloat(data[i]["Latitude"]);
			if  ( parseFloat(data[i]["Longitude"]) > maxLong ) maxLong = parseFloat(data[i]["Longitude"]);
			if  ( parseFloat(data[i]["Longitude"]) < minLong && parseFloat(data[i]["Longitude"]) != 0 ) minLong = parseFloat(data[i]["Longitude"]);
			if  ( metricName != "" )
			{
				if  ( parseFloat(data[i][metricName]) > maxMetric ) maxMetric = parseFloat(data[i][metricName]);
				if  ( parseFloat(data[i][metricName]) < minMetric ) minMetric = parseFloat(data[i][metricName]);
			}
		}

        // If this data item has a key field then set mapkey with the 
        // key value so we can look to find an existing marker to update
        // or delete
        mapkey = circlect;
        if ( keyfield != "" )
			mapkey = data[i][keyfield];

        // See whether marker needs to be delete ( i.e. if there is rowstatus data value and it contains DELETED )
        // and if so remove from map and marker list
        if ( data[i]["Row Status"] && data[i]["Row Status"] == "DELETED" )
        {
            deleteMarker(session, mapkey);
  		    circlect++;
            continue;
        }

        // Create an Circle style marker
		if (plotType == "Circle")
		{
			sessionParams[session].plottype = "CIRCLE";
			
		    mk_metric = parseFloat(data[i][metricName]);
		    mk_metric = Math.round( mk_metric );

			tmpSpeed = mk_metric;
			if ( tmpSpeed > metricRangeUpper ) tmpSpeed = metricRangeUpper;
			if ( tmpSpeed < metricRangeLower ) tmpSpeed = metricRangeLower;

			range = metricRangeUpper - metricRangeLower;

			redcolor = 255 * ( tmpSpeed - metricRangeLower ) / range;
			redcolor = Math.round ( redcolor );
			greencolor = 255 * ( tmpSpeed - metricRangeLower ) / range;
			greencolor = 255 - Math.round ( greencolor );
			hex = "#" + redcolor.toString(16) + greencolor.toString(16) + "00";

			// Derive PlotSize
			if ( plotSizeMetric != "" )
			{
				plotSizeSource = parseInt(data[i][plotSizeMetric]);
				if ( plotSizeMetricMinimum != -99999 && plotSizeSource < plotSizeMetricMinimum )
					plotSizeSource = plotSizeMetricMinimum;
				if ( plotSizeMetricMaximum != -99999 && plotSizeSource > plotSizeMetricMaximum )
					plotSizeSource = plotSizeMetricMaximum;
				range = plotSizeMaximum - plotSizeMinimum;
				metricrange = plotSizeMetricMaximum - plotSizeMetricMinimum;
				plotSizeFraction = ( plotSizeSource - plotSizeMetricMinimum ) / metricrange;
				toTarget = plotSizeMinimum + ( range * plotSizeFraction );
				toTarget = Math.round ( toTarget );
				plotSize = toTarget;
			}

			if ( mapEngine == "google" )
			{
                ll = new google.maps.LatLng(data[i]["Latitude"], data[i]["Longitude"]);
				var populationOptions = {
			    	strokeColor: hex,
			    	strokeOpacity: 0.4,
			    	strokeWeight: 1,
			    	fillColor: hex,
			    	fillOpacity: 0.6,
			    	map: map,
			    	center: ll,
			    	radius: plotSize
			    	};
				var marker = new google.maps.Circle(populationOptions);
            	marker = setMarkerDetailsGoogle(session, keyfield, marker, data, i, false, circlect, "CIRCLE", clicklink, calledfromrefresh);
			}
			else
			{
            	marker = setMarkerDetailsOSM(session, keyfield, marker, data, i, false, circlect, "CIRCLE", clicklink, calledfromrefresh);
            	marker.features[0].attributes.fillColor = hex;
            	marker.features[0].attributes.strokeColor = "#202020";
            	marker.features[0].attributes.pointRadius = plotSize / 2;
			}


            // Does marker for key field already exist ?
			if ( keyfield != "" )
			{
				mapkey = data[i][keyfield];
				if ( circleMarkers[session][mapkey] )
				{
				 	n = circleMarkers[session][mapkey];
					if ( circleParams[session][mapkey]["visible"] )
                    {
						hideMarkerOnMap(n);
                    }
				}
				else 
                {
					circleParams[session][mapkey] = [];
                }
			}
			else 
				circleParams[session][mapkey] = [];

			circleParams[session][mapkey]["visible"] = false;
			circleParams[session][mapkey]["plottype"] = "CIRCLE";
			circleParams[session][mapkey]["changed"] = true;
			circleMarkers[session][mapkey] = marker;
		}

        if ( !circleParams[session][mapkey] )
        {
			circleParams[session][mapkey] = [];
        }

        // Create an Icon style marker
		if (plotType == "Icon")
		{
           	// Generate Marker Icons - create maps image from url built from render elements
           	dynamicIcon = createMapIcon(session, data, i, mapkey);

			if ( mapEngine == "osm" )
			{
            	// Create the marker
				var marker = false;
            	marker = setMarkerDetailsOSM(session, keyfield, marker, data, i, dynamicIcon, circlect, "ICON", clicklink, calledfromrefresh);
			}
			else
			{
            	// Create the marker
				var marker = false;
            	marker = setMarkerDetailsGoogle(session, keyfield, marker, data, i, dynamicIcon, circlect, "ICON", clicklink, calledfromrefresh);
			}
			

    	}
  		circlect++;
    }

    
    var eventLayers = [];
    ptr = 0;
	for (var index in circleMarkers) 
    {
        if ( sessionParams[index].plottype == "CIRCLE" || true )
        {
            eventLayers[ptr++] =  circleMarkers[session];
        }
    }
	if (mapEngine == "osm")
    {
        //if ( sessionParams[index].map_control )
        //{
            //map.removeControl(sessionParams[index].map_control);
            //sessionParams[index].map_control.deactivate();
            //sessionParams[index].map_control.destroy();
            //sessionParams[index].map_control = null;
        //}

        circleLayers.length = 0;
        ctr = 0;
        for ( var index in sessionParams )
        {
            if ( sessionParams[index].map_layer )
                circleLayers[ctr++] = sessionParams[index].map_layer;
        }
        if ( !sessionParams[index].map_control )
        {
            sessionParams[index].map_control = new OpenLayers.Control.SelectFeature(circleLayers, {onSelect: onFeatureSelect, onUnselect: onFeatureUnselect, multiple: false, hover: false, clickout: true});
            //sessionParams[index].map_control = new OpenLayers.Control.SelectFeature(circleLayers, {onSelect: onFeatureSelect, onUnselect: onFeatureUnselect});
            map.addControl(sessionParams[index].map_control);
            sessionParams[index].map_control.activate();
        }
        else
        {
            //sessionParams[index].map_control.setLayer(circleLayers);
            sessionParams[index].map_control.setLayer(circleMarkers[session]);
        }
    }

	if ( calledfromrefresh )
    {
		applyFiltersToMarkers(session, false)
		logMarkerChanges(session)
    }

	return true;
}

function onPopupClose(evt) {
    //selectControl.unselect(selectedFeature);
}
function onFeatureSelect(feature) {
    selectedFeature = feature;
    //textInfo(feature.data.popupContentHTML);
    popup = new OpenLayers.Popup.FramedCloud("chicken", 
                             feature.geometry.getBounds().getCenterLonLat(),
                             null,
                             feature.data.popupContentHTML,
                            //"<div style='font-size:.8em'>ooo<br>eee</div>",
                             null, true, onPopupClose);
    feature.popup = popup;
    map.addPopup(popup);
    
    $('#popupButton').button();
    $('.expandwindow').button();
    $('.expandwindow').click(function(){ showSubwindow(this, "subwindow"); return false; });
}

function onFeatureUnselect(feature) {
    map.removePopup(feature.popup);
    feature.popup.destroy();
    feature.popup = null;
}   

// -----------------------------------------------------------------------
// Creates a map marker on a Google Map
// Populates the marker with attributes basedon the current data set
function setMarkerDetailsGoogle (session, keyfield, marker, data, i, dynamicIcon, circlect, plottype, clicklink, calledfromrefresh)
{
			txt = "";
			tooltiptxt = "";

            oldlat = 0;
            oldlon = 0;


		    ll = new google.maps.LatLng(data[i]["Latitude"], data[i]["Longitude"]);

            // If marker already exists matching the new plot data then
            // update the plot
			if ( keyfield != "" )
			{
				mapkey = data[i][keyfield];
				if ( circleMarkers[session][mapkey] )
				{
				 	marker = circleMarkers[session][mapkey];
                    if ( marker )
                    {
                            oldlat = getIconLat(marker);
                            oldlat = oldlat * 1000000;
                            oldlat = Math.round(oldlat);
                            oldlat = oldlat / 1000000;
                            oldlon = getIconLon(marker);
                            oldlon = oldlon * 1000000;
                            oldlon = Math.round(oldlon);
                            oldlon = oldlon / 1000000;
                            //if ( calledfromrefresh )
				 	            //marker.setZindex(501);
				 	            //marker.zIndex = 501;
                            //else
				 	            //marker.setZindex(500);
				 	            //marker.zIndex = 500;
                    }
					ll1 = marker.getPosition();
				 	marker.setPosition(ll);
				 	marker.setIcon(dynamicIcon);
				 	marker.setTitle(tooltiptxt);
				}
			}

            // Derive text for marker tooltip and expand window when shown when marker clicked
			for (var index in data[i]) {
				if (index != "Longitude" && index != "Latitude" && index != "")
				{
					txt = txt + index + ": " + data[i][index] + "<BR>";
					if ( tooltips == "" )
						tooltiptxt = tooltiptxt + index + ": " + data[i][index] + "   ";
					else
						if ( tooltiparr[index] )
							tooltiptxt = tooltiptxt + index + ": " + data[i][index] + "   ";
				}
            }

            // If marker is ploted in the first query then the zindex will be lower than for markers from future queries
            // as the first query may be setting a background. This causes despatcher vehicles to overlay despatcher stops
			zidx = 1000;
            if ( calledfromrefresh )
			    zidx = 1001;

            // Create marker if non-exsitent
			if ( !marker ) 
				marker = new google.maps.Marker({position: ll, title:tooltiptxt, icon:dynamicIcon, zIndex:zidx});

            // Set marker attributes from data array
            changed = false;
			for ( var index in data[i] )
			    if ( index != "Longitude" && index != "Latitude" && index != "" )
                {
                    // Deal with a null column
                    if ( data[i][index] == null  )
                        data[i][index] = "Blank";
                    
                    // Check if marker value has changed
			        if ( keyfield != "" )
			        {
				        mapkey = data[i][keyfield];
				        if ( circleMarkers[session][mapkey] && circleFilters[session]["data"][index] )
                        {
					        if ( marker.get(index) != undefined && marker.get(index) != data[i][index] )
                            {
                                changed = true;
                                if ( circleFilters[session]["movealerts"] )
                                    if ( circleFilters[session]["movealerts"][index] )
                                        if ( circleFilters[session]["movealerts"][index][data[i][index]] )
                                        {
                                            idx = systemMessages.length;
                                            systemMessages[idx] = [];
                                            systemMessages[idx]["item"] = data[i][keyfield];
                                            systemMessages[idx]["element"] = index;
                                            systemMessages[idx]["from"] = marker.get(index);
                                            systemMessages[idx]["to"] = data[i][index];
                                            systemMessages[idx]["actioned"] = false
                                        }
                            }
                        }
			        }

				    marker.set(index, data[i][index]);

                    if ( circleFilters[session]["data"][index] )
                    {
                        if ( circleFilters[session]["split"][index] )
                        {
                            fltarr = data[i][index].split("/");
                            for ( var i2=0; i2<fltarr.length; ++i2 )
                            {
                                if ( !circleFilters[session]["data"][index][fltarr[i2]] )
                                    circleFilters[session]["data"][index][fltarr[i2]] = false;
                            }
                        }
                        else if ( !circleFilters[session]["data"][index][data[i][index]] )
                            circleFilters[session]["data"][index][data[i][index]] = false;
                    }
                }
                else
                {
                    val = data[i][index];
                    val = val * 1000000;
                    val = Math.round(val);
                    val = val / 1000000;
    		        if ( index == "Latitude" && val != oldlat )
                        changed = true;
    		        if ( index == "Longitude" && val != oldlon )
                        changed = true;
                }

            // Set the content of the window to be shown when the marker is clicked
			marker.set("description", txt);
			marker.set("clickLink", clicklink);

            urllink = clicklink;
            if ( urllink ) 
            {
                var matches = urllink.match(/<<[a-zA-Z_ 0-9]*>>/g);
			    for (var index in matches) 
                {
                    from = 2;
                    to = matches[index].length - 2;
                    if ( !matches[index] || index == "index" || index == "lastIndex" )
                        continue;
                    field = matches[index].substring(from, to );

                    rexp = new RegExp(matches[index], "g");
                    urllink = urllink.replace(rexp, data[i][field]);
                }
            }

			marker.set("clickLink", urllink);

            // Set marker parameters which will hold whether the marker is
            // visible or not and the type of marker - icon or circle
			mapkey = circlect;
			if ( keyfield != "" )
			{
				mapkey = data[i][keyfield];
				if ( !circleMarkers[session][mapkey] )
				    if ( !circleParams[session][mapkey] )
                    {
					    circleParams[session][mapkey] = [];
                        changed = true;
                    }
			}
			else 
				if ( !circleParams[session][mapkey] )
                {
				    circleParams[session][mapkey] = [];
                    changed = true;
                }

            // Tell the marker to open a window when its clicked but remove any previous handler first
            if ( !circleParams[session][mapkey]["clicker"] )
            {
			    var eventhandle = google.maps.event.addListener(marker, "click", function() {
				    document.getElementById("smallsubwindow").innerHTML = "";
				    showOverlay();
				    clearOverlayIntervals();
				    textInfo(this.get("description"), this.get("clickLink"));
			    });
                circleParams[session][mapkey]["clicker"]  = eventhandle;
            }

			circleMarkers[session][mapkey] = marker;
			circleParams[session][mapkey]["plottype"] = plottype;
			circleParams[session][mapkey]["changed"] = changed;
            return marker;
}

// -----------------------------------------------------------------------
// Creates a map marker on an OSM map
// Populates the marker with attributes basedon the current data set
function setMarkerDetailsOSM (session, keyfield, marker, data, i, dynamicIcon, circlect, plottype, clicklink, calledfromrefresh)
{
			txt = "";
			tooltiptxt = "";

            marker = null;

            oldlat = 0;
            oldlon = 0;
            if ( marker )
            {
                    oldlat = getIconLat(marker);
                    oldlat = oldlat * 1000000;
                    oldlat = Math.round(oldlat);
                    oldlat = oldlat / 1000000;
                    oldlon = getIconLon(marker);
                    oldlon = oldlon * 1000000;
                    oldlon = Math.round(oldlon);
                    oldlon = oldlon / 1000000;
            }

            // If marker already exists matching the new plot data then
            // update the plot
			if ( keyfield != "" )
			{
				mapkey = data[i][keyfield];
				if ( circleMarkers[session][mapkey] )
				{
				 	marker = circleMarkers[session][mapkey];
                    point = new OpenLayers.Geometry.Point(data[i]["Longitude"], data[i]["Latitude"] ).transform(fromProjection,toProjection);
                    //marker.features[0].move(point);
                    //marker.features[0].geometry.move(point);
                    redraw = false;
                    if ( marker.geometry.x != point.x || marker.geometry.y != point.y )
                    {
                        marker.geometry.x = point.x;
                        marker.geometry.y = point.y;
                        marker.geometry.clearBounds();
                        redraw = true;
                    }
                    if ( dynamicIcon && dynamicIcon != marker.attributes.imgurl ) 
                    {
                        marker.attributes.imgurl = dynamicIcon;
                        redraw = true;
                    }
                    if ( redraw )
                        marker.layer.drawFeature(marker);
					//ll1 = marker.getPosition();
//PPP to do
				 	//marker.setPosition(ll);
				 	//marker.setIcon(dynamicIcon);
				 	//marker.setTitle(tooltiptxt);
				}
			}
			zidx = 1000 + autorefreshes[session]["autorefresh"];

            // Derive text for marker tooltip and expand window when shown when marker clicked
			for (var index in data[i]) {
				if (index != "Longitude" && index != "Latitude" && index != "")
				{
					txt = txt + index + ": " + data[i][index] + "<BR>";
					if ( tooltips == "" )
						tooltiptxt = tooltiptxt + index + ": " + data[i][index] + "   ";
					else
						if ( tooltiparr[index] )
							tooltiptxt = tooltiptxt + index + ": " + data[i][index] + "  " ;
                }
            }


            // Create marker if non-exsitent
			if ( !marker && !dynamicIcon ) 
            {
                point = new OpenLayers.Geometry.Point(data[i]["Longitude"], data[i]["Latitude"] ).transform(fromProjection,toProjection);
                marker = new OpenLayers.Feature.Vector(point);
                sessionParams[session].map_layer.addFeatures([marker]);
                marker.data.popupContentHTML = " <script type=\"text/javascript\"> alert('hello'); $('.expandwindow').live('click', function(event) { showSubwindow(this, \"subwindow\"); return false; }); </script>";
                marker.data.popupContentHTML += txt;
                marker.attributes.markerdisplay = "inline";
                marker.attributes.latitude = data[i]["Latitude"];
                marker.attributes.longitude = data[i]["Longitude"];
            }
            else
			if ( !marker && dynamicIcon ) 
            {
                //marker = new OpenLayers.Layer.Vector("Vector Layer");
                point = new OpenLayers.Geometry.Point(data[i]["Longitude"], data[i]["Latitude"] ).transform(fromProjection,toProjection);
                marker = new OpenLayers.Feature.Vector(point);
                marker.data.popupContentHTML = txt;
                marker.attributes.imgurl = dynamicIcon;
                marker.attributes.graphicHeight = iconsizey ;
                marker.attributes.graphicWidth = iconsizex;
                marker.attributes.graphicXOffset = -hotspotx;
                marker.attributes.graphicYOffset = -hotspoty;
                marker.data.popupContentHTML = " <script type=\"text/javascript\"> alert('hello'); $('.expandwindow').live('click', function(event) { showSubwindow(this, \"subwindow\"); return false; }); </script>";
                marker.data.popupContentHTML += txt;
                marker.popupClass = " <script type=\"text/javascript\"> alert('hello'); $('.expandwindow').live('click', function(event) { showSubwindow(this, \"subwindow\"); return false; }); </script>";
                marker.attributes.markerdisplay = "none";
                marker.attributes.latitude = data[i]["Latitude"];
                marker.attributes.longitude = data[i]["Longitude"];
                sessionParams[session].map_layer.addFeatures([marker]);
            }

            // Set marker attributes from data array
            changed = false;
			for ( var index in data[i] )
			    if ( index != "Longitude" && index != "Latitude" && index != "" )
                {
                    // Deal with a null column
                    if ( data[i][index] == null  )
                        data[i][index] = "Blank";
                    
                    // Check if marker value has changed
			        if ( keyfield != "" )
			        {
				        mapkey = data[i][keyfield];
				        if ( circleMarkers[session][mapkey] && circleFilters[session]["data"][index] )
                        {
					        if ( marker.attributes[index] != undefined && marker.attributes[index] != data[i][index] )
                            {
                                changed = true;
                                if ( circleFilters[session]["movealerts"] )
                                    if ( circleFilters[session]["movealerts"][index] )
                                        if ( circleFilters[session]["movealerts"][index][data[i][index]] )
                                        {
                                            idx = systemMessages.length;
                                            systemMessages[idx] = [];
                                            systemMessages[idx]["item"] = data[i][keyfield];
                                            systemMessages[idx]["element"] = index;
                                            systemMessages[idx]["from"] = marker.attributes[index];
                                            systemMessages[idx]["to"] = data[i][index];
                                            systemMessages[idx]["actioned"] = false
                                        }
                            }
                        }
			        }

				    //PPPmarker.set(index, data[i][index]);
                    //markerFeatureAttr[index] = data[i][index];
                    marker.attributes[index] = data[i][index];
                    if ( circleFilters[session]["data"][index] )
                    {
                        if ( circleFilters[session]["split"][index] )
                        {
                            fltarr = data[i][index].split("/");
                            for ( var i2=0; i2<fltarr.length; ++i2 )
                            {
                                if ( !circleFilters[session]["data"][index][fltarr[i2]] )
                                    circleFilters[session]["data"][index][fltarr[i2]] = false;
                            }
                        }
                        else if ( !circleFilters[session]["data"][index][data[i][index]] )
                            circleFilters[session]["data"][index][data[i][index]] = false;
                    }
                }
                else
                {
                    val = data[i][index];
                    val = val * 1000000;
                    val = Math.round(val);
                    val = val / 1000000;
    		        if ( index == "Latitude" && val != oldlat )
                        changed = true;
    		        if ( index == "Longitude" && val != oldlon )
                        changed = true;
                }

            // Set the content of the window to be shown when the marker is clicked
			//PPP marker.set("description", txt);

            // Tell the marker to open a window when its clicked
			//PPP google.maps.event.addListener(marker, "click", function() {
				//PPP document.getElementById("smallsubwindow").innerHTML = "";
				//PPP showOverlay();
				//PPP clearOverlayIntervals();
				//PPP textInfo(this.get("description"));
			//PPP });

            // Set marker parameters which will hold whether the marker is
            // visible or not and the type of marker - icon or circle
			mapkey = circlect;
			if ( keyfield != "" )
			{
				mapkey = data[i][keyfield];
				if ( !circleMarkers[session][mapkey] )
                {
					circleParams[session][mapkey] = [];
                    changed = true;
                }
			}
			else 
            {
				circleParams[session][mapkey] = [];
                changed = true;
            }

			circleMarkers[session][mapkey] = marker;
			circleParams[session][mapkey]["plottype"] = plottype;
			circleParams[session][mapkey]["changed"] = changed;
            return marker;
}

// -------------------------------------------------------------------
// Resets map marker icon url after user interaction, for example if the
// Metric value has changed
function changeMapIconsMetric (session, metric)
{ 
    for ( var ct in circleMarkers[session] )
    {  
        marker = circleMarkers[session][ct];
	    iconkey = circleParams[session][ct]["iconkey"];
	  	iconkeymod = iconkey.replace(/_[ A-Za-z0-9]*/,"_" + metric);
        icon = circleIcons[session][iconkey];
        url = icon.url;
        anchor = icon.anchor;
	  	urlmod = url.replace(/elementValues=[ A-Za-z0-9]*/,"elementValues=" + metric);

        if ( !circleIcons[iconkeymod] )
        {
		    if ( mapEngine == "osm" )
		    {
           	    var size = new OpenLayers.Size(21, 25);
          	    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
			    if ( !circleIcons[session][renderType + "_" + elementValues] )
				    circleIcons[session][renderType + "_" + elementValues] = 
                icon = new OpenLayers.Icon(yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues, size, offset); 

			    dynamicIcon = circleIcons[session][renderType + "_" + elementValues];
           	    dynamicIconString = yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues;
    		    return dynamicIconString;
		    }
		    else
		    {
                circleIcons[iconkeymod] = new google.maps.MarkerImage(urlmod,
					    null, null, anchor,
					    null);
                    
		    }
	    }
	    if ( mapEngine == "osm" )
	    {
            circleMarkers[session][ct].setIcon(urlmod);
        }
        else
	    {
            circleMarkers[session][ct].setIcon(urlmod);
        }
                
    }
}

// -------------------------------------------------------------------
// Generates a maps image from a url built up from the filter elements
function createMapIcon (session, data, i, circlect)
{ 
	ect = 0;
	elementTypes = "";
	elementValues = "";

    for ( var metric in circleFilters[session]["metrics"] )
    {
        if ( circleFilters[session]["metrics"][metric] )
        {
		    elementTypes += "Metric";
            elementValues += metric;
            ect++;
        }
    }
            
    rexp = new RegExp(",", "g");
	for (var index in data[i]) {
		for (var el in sessionRenderElements[session]) {
			if (el == index)
			{
				if ( ect > 0 ) 
				{
					elementTypes += ",";
					elementValues += ",";
				}
				ect ++;
				elementTypes = elementTypes + el;

                // Not allowed commas in filter tab values
                if ( data[i][el] )
                {
                    param = data[i][el].replace(rexp, "");
				    elementValues = elementValues + param;
                }
                else
                    elementValues = elementValues + data[i][el];
			}
		}
	}

    // Store marker key 
	circleParams[session][circlect]["iconkey"] = renderType + "_" + elementValues;

	var dynamicIcon = stopIcon;
	var dynamicIconString = "";
	if (renderType != "")
	{
		if ( mapEngine == "osm" )
		{
           	var size = new OpenLayers.Size(21, 25);
          	var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
			if ( !circleIcons[session][renderType + "_" + elementValues] )
				circleIcons[session][renderType + "_" + elementValues] = 
                icon = new OpenLayers.Icon(yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues, size, offset); 

			dynamicIcon = circleIcons[session][renderType + "_" + elementValues];
           	dynamicIconString = yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues;
    		return dynamicIconString;
		}
		else
		{
			if ( !circleIcons[session][renderType + "_" + elementValues] )
				circleIcons[session][renderType + "_" + elementValues] = new google.maps.MarkerImage(yii_framework_app + "icons/render.php?type=" + renderType + "&elementTypes=" + elementTypes + "&elementValues=" + elementValues,
					null, null, new google.maps.Point(hotspotx, hotspoty),
					null);
			dynamicIcon = circleIcons[session][renderType + "_" + elementValues];
    		return dynamicIcon;
		}
	}
    return dynamicIconString;
}
function vehiclePositions(el)
{
	alert("vp");
	document.forms["meform"].submit();
	//x.submit();
}

function getReportFullScreen(type, session, formaction, formparams, autorefresh, inbackground, inOutputType )
{
	if ( !inbackground )
    	hideOverlay();

    // Force autorefresh if menu option specifies
    if ( get_session_param(session, "autorefresh" ) )
        autorefresh = true;

	if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
	{
			autorefreshes[session].length = 0;
			//clearsession(session);
			return;
	}

    				
	if ( !autorefreshes[session] )
	{
       		autorefreshes[session] = [];
			autorefreshes[session]["type"] = type;
			autorefreshes[session]["session"] = session;
			autorefreshes[session]["formaction"] = formaction;
			autorefreshes[session]["formparams"] = formparams;
			autorefreshes[session]["autorefresh"] = autorefresh;
			autorefreshes[session]["refreshes"] = 0;
			autorefreshes[session]["status"] = "IDLE";
			autorefreshes[session]["request"] = "IDLE";
	}

    if (navigator.appName == "Microsoft Internet Explorer")
		autorefreshes[session]["request"] = new ActiveXObject("Microsoft.XMLHTTP");
    else
		autorefreshes[session]["request"] = new XMLHttpRequest();

	if ( !inbackground )
		set_loading_status ( true );
	else
		$("#loadindicator").addClass("loading2");


	fetchurl = formaction;
	fetchparams = formparams;

   	if ( get_session_param(session, "refreshxml" ) && autorefresh)
	{
          //newxml = get_session_param(session, "refreshxml" );
		  if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
		  	fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,newxml,fetchparams);
		  else
		  	fetchparams = fetchparams + "&xmlin=" + get_session_param(session, "refreshxml" );
	}

    if ( inOutputType == "PDF" || inOutputType == "CSV" )
    {
        var win =  window.open(
                    fetchurl + "?" + fetchparams,
                    'Batch Print',
                    'width=600,height=600,location=_newtab'
             );
        $(win).ready(function()
        {
		    set_loading_status ( false );
        });

        return;
    }
    


    autorefreshes[session]["request"].open("GET", fetchurl + "?" + fetchparams, true);
    autorefreshes[session]["request"].onreadystatechange = function() {
        if (autorefreshes[session]["request"].readyState != 3) 
        if (autorefreshes[session]["request"].readyState == 4) {
			if ( autorefreshes[session]["request"].status != 200 )
			{
				alert ( "Query action failed - status code " + autorefreshes[session]["request"].status );
				if ( !inbackground )		
            		set_loading_status (false );
				if ( !inbackground )		
            		set_loading_status (false );
				$("#loadindicator").removeClass("loading2");
				return;
			}
            //document.getElementById("accordion").className = "";
       		$("#showreport").click();
            $("#reportcol").attr('innerHTML', autorefreshes[session]["request"].responseText);
            //$("#reportcol").css('height', '700px');
            $("#reportcol").css('overflow', 'scroll');

			setDatePickers();
			initstopmessage();
			if ( autorefreshes[session]["status"] == "REMOVE" )
			{
				autorefreshes[session].length = 0;
				$("#loadindicator").removeClass("loading2");
				if ( !inbackground )		
            		set_loading_status (false );
				//clearsession(session);
				return;
			}
				
	
			if ( autorefreshes[session].timeout )
				clearTimeout ( autorefreshes[session].timeout );
			cmd = "getReportFullScreen( " +
							"autorefreshes['" + session + "']['type'], " +
							"autorefreshes['" + session + "']['session'],  " +
							"autorefreshes['" + session + "']['formaction'],  " +
							"autorefreshes['" + session + "']['formparams'], " +
							 "true, true, 'HTML' )";
			autorefreshes[session]["cmd"] = cmd;
			if ( autorefresh ) 
				autorefreshes[session].timeout = setTimeout ( cmd,  10000);

			// Automatically load GOLAP filter pane
			if ( !autorefresh ) 
			{
            	hideshowlayer(session, false);
            	//if ( get_session_param(session, "hasline" ) )
                	//initGOLAPFilters(session, false, true, true);
            	//else
                	//initGOLAPFilters(session, false, false, true);
			}
				
			if ( !inbackground )		
           		set_loading_status (false );
			else
				$("#loadindicator").removeClass("loading2");
        }
    }
    autorefreshes[session]["request"].send(null);
}

/*
** getLineOutput
**
** Runs a report query that generates json line view data and creates a line view from it
*/
function getLineOutput(type, session, formaction, formparams, autorefresh, inbackground )
{
	if ( !inbackground )
    	hideOverlay();

    // Set up session refreshing
    if ( preQueryRefreshHandling(type, session, formaction, formparams, autorefresh, inbackground ) == "REMOVE" )
        return;

    formparams = get_session_param(session, "fetchParameters");

    $.ajax(
    {
        type: "GET",
        url: formaction,
        data: formparams,
        async: true,
        dataType: "json",
        success: function(result)
        {
            // Extract timestamp of request so we can only retrieve
            // ata since this time next time we do it
            if ( result.timestamp )
                sessionParams[session].lastrefresh = result.timestamp;

            loadLineViewToDashboard(session);
           	val = updateLineView(session, result, inbackground);

			if ( !autorefresh )
            	hideshowlayer(session, false);
            showGOLAPFilters(session, inbackground);
			applyGOLAPFiltersLine(session, "NONE", "", "", false);
		    if ( !inbackground )
            		showDashboard();
            set_loading_status (false);

            if (  postQueryRefreshHandling(session, "getLineOutput", autorefresh, inbackground ) == "REMOVE" )
            //if (  postQueryRefreshHandling(session, "getLineOutput", false, inbackground ) == "REMOVE" )
                return;

			// Automatically load GOLAP filter pane
			if ( !autorefresh ) 
			{
            	hideshowlayer(session, false);
			}

            // Resize Map is Filter has reduced space
            sizeDashboardLineToFitParent(session);
        },
        error: function(x, e)
        {
            set_session_param(session, "jsondata", false);
            set_session_param(session, "urlparams", false);
            set_loading_status (false);
            set_loading_status (false);
            alert("No data found matching your criteria or internal server error");   

            if (  postQueryRefreshHandling(session, "getLineOutput", autorefresh, inbackground ) == "REMOVE" )
                return;

        }
    });

    //autorefreshes[session]["request"].send(null);
}


/*
** getGridOutput
**
** Runs a report query that generates jqgrid compatible output and creates a grid view on the results
*/
function getGridOutput(type, session, formaction, formparams, autorefresh, inbackground )
{
	if ( !inbackground )
    	hideOverlay();

    // Set up session refreshing
    if ( preQueryRefreshHandling(type, session, formaction, formparams, autorefresh, inbackground ) == "REMOVE" )
        return;

    formparams = get_session_param(session, "fetchParameters");

    $.ajax(
    {
        type: "GET",
        url: formaction,
        data: formparams,
        async: true,
        dataType: "json",
        success: function(result)
        {
            // Store the column model details for use by the grpahing tool
            if ( !get_session_param ( session, "jsondata" ) && result.colmodel.length > 0 )
            {
                graph_properties = {
                    colmodel: result.colmodel,
                    colnames: result.colnames,
                    graphopt: result.graphopt
                }
                set_session_param(session, "jsondata", graph_properties);
            }

            // Extract timestamp of request so we can only retrieve
            // ata since this time next time we do it
            if ( result.timestamp )
                sessionParams[session].lastrefresh = result.timestamp;

            loadReportGrid(session, formaction, result);
            plot_chart(session);
            set_loading_status (false);

            // Default to grid on first load
            if ( !inbackground )
            {
                dashtag = "#dash" + session + " .portlet-header .dashheadgrid";
                $(dashtag).click();
            }

            if (  postQueryRefreshHandling(session, "getGridOutput", autorefresh, inbackground ) == "REMOVE" )
                return;

			// Automatically load GOLAP filter pane
			if ( !autorefresh ) 
			{
            	hideshowlayer(session, false);
			}
        },
        error: function(x, e)
        {
            set_session_param(session, "jsondata", false);
            set_session_param(session, "urlparams", false);
            set_loading_status (false);
            set_loading_status (false);
            //alert("No data found matching your criteria");   //Modified By Prasenjit
        }
    });

    //autorefreshes[session]["request"].send(null);
}

function getReportOutput(type, session, formaction, formparams, autorefresh, inbackground )
{
	if ( !inbackground )
    	hideOverlay();

    // Force autorefresh if menu option specifies
    if ( get_session_param(session, "autorefresh" ) )
        autorefresh = true;

	if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
	{
			autorefreshes[session].length = 0;
			//clearsession(session);
			return;
	}

    				
	if ( !autorefreshes[session] )
	{
       		autorefreshes[session] = [];
			autorefreshes[session]["type"] = type;
			autorefreshes[session]["session"] = session;
			autorefreshes[session]["formaction"] = formaction;
			autorefreshes[session]["formparams"] = formparams;
			autorefreshes[session]["autorefresh"] = autorefresh;
			autorefreshes[session]["refreshes"] = 0;
			autorefreshes[session]["status"] = "IDLE";
			autorefreshes[session]["request"] = "IDLE";
	}

    if (navigator.appName == "Microsoft Internet Explorer")
		autorefreshes[session]["request"] = new ActiveXObject("Microsoft.XMLHTTP");
    else
		autorefreshes[session]["request"] = new XMLHttpRequest();

	if ( !inbackground )
		set_loading_status ( true );
	else
		$("#loadindicator").addClass("loading2");


	fetchurl = formaction;
	fetchparams = formparams;
   	if ( get_session_param(session, "refreshxml" ) && autorefresh)
	{
          //newxml = get_session_param(session, "refreshxml" );
		  if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
		  	fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,newxml,fetchparams);
		  else
		  	fetchparams = fetchparams + "&xmlin=" + get_session_param(session, "refreshxml" );
	}


    autorefreshes[session]["request"].open("GET", fetchurl + "?" + fetchparams, true);
    autorefreshes[session]["request"].onreadystatechange = function() {
        if (autorefreshes[session]["request"].readyState != 3) 
        if (autorefreshes[session]["request"].readyState == 4) {
			if ( autorefreshes[session]["request"].status != 200 )
			{
				alert ( "Query action failed - status code " + autorefreshes[session]["request"].status );
				if ( !inbackground )		
            		set_loading_status (false );
				if ( !inbackground )		
            		set_loading_status (false );
				$("#loadindicator").removeClass("loading2");
				return;
			}
            document.getElementById("accordion").className = "";
            addUrlToDashboard( session, "dash" + session, "js/dashboard/pleasewait.html", get_session_param(session, "title" ), "REPORT" );
			if ( type == "swLineButton" )
			{
                // Create dashboard widget ofr line view if not there
            	updateLineView(session, autorefreshes[session]["request"], autorefresh);
				if ( !inbackground )
            		showDashboard();
			}
			else
			{
           		showDashboard();
                a = $("#dash" + session + " .widgetcontent");
                b = autorefreshes[session]["request"].responseText;
                $("#dash" + session + " .portlet-content").attr('innerHTML', autorefreshes[session]["request"].responseText);
                $("#" + "dash" + session + " .widgetcontent").attr('innerHTML', autorefreshes[session]["request"].responseText);
                $("#dash" + session + " .widgetcontent").find('.swRepBackBox').css('display', 'none');
                $("#dash" + session + " .widgetcontent").find('.swRepTitle').each(function(){
                                    $(this).css('display', 'none');
                            });

                graphwidth =  ( $("#dash" + session + " .widgetcontent").innerWidth() * 0.95 ) + "px";
                pagewidth =  ( $("#dash" + session + " .widgetcontent").innerWidth()) + "px";
                height =  ( $("#dash" + session + " .widgetcontent").innerWidth());
                if ( height > 500 ) 
                    $("#dash" + session + " .widgetcontent").attr("height", "500px");
                a = $("#dash" + session + " .widgetcontent");
                resizeDashboard();
                //$("#dash" + session + " .widgetcontent").find('.swRepGraph').each(function(){ 
                        //$(this).css('max-width', "100%"); 
                        //$(this).css('max-height', "100%"); 
                        //$(this).css('width', graphwidth); 
                        //$(this).css('width', ""); 
                        //});
                $("#dash" + session + " .widgetcontent").find('.swMntForm').each(function(){ $(this).css('background-color', "transparent"); });
                $("#dash" + session + " .widgetcontent").find('.swRepPage').each(function(){ $(this).css('background-color', "transparent"); });



                //$("#dash" + session + " .widgetcontent").each( function () {
                            //$(this).attr('innerHTML', autorefreshes[session]["request"].responseText);
                    //});
			}

			if ( autorefreshes[session]["status"] == "REMOVE" )
			{
				autorefreshes[session].length = 0;
				$("#loadindicator").removeClass("loading2");
				if ( !inbackground )		
            		set_loading_status (false );
				//clearsession(session);
				return;
			}
				
	
			if ( autorefreshes[session].timeout )
				clearTimeout ( autorefreshes[session].timeout );
			cmd = "getReportOutput( " +
							"autorefreshes['" + session + "']['type'], " +
							"autorefreshes['" + session + "']['session'],  " +
							"autorefreshes['" + session + "']['formaction'],  " +
							"autorefreshes['" + session + "']['formparams'], " +
							 "true, true )";
			autorefreshes[session]["cmd"] = cmd;
			if ( autorefresh ) 
				autorefreshes[session].timeout = setTimeout ( cmd,  10000);

			// Automatically load GOLAP filter pane
			if ( !autorefresh ) 
			{
            	hideshowlayer(session, false);
            	//if ( get_session_param(session, "hasline" ) )
                	//initGOLAPFilters(session, false, true, true);
            	//else
                	//initGOLAPFilters(session, false, false, true);
			}
				
			if ( !inbackground )		
           		set_loading_status (false );
			else
				$("#loadindicator").removeClass("loading2");
        }
    }
    autorefreshes[session]["request"].send(null);
}

/*
** getMapOutput
**
** Runs a report query that generates jsondata suitable for a map view
*/
function getMapOutput(type, session, formaction, formparams, autorefresh, inbackground )
{
	if ( !inbackground )
    {
        //showDashboard();
    	hideOverlay();
    }

    // Set up session refreshing
    if ( preQueryRefreshHandling(type, session, formaction, formparams, autorefresh, inbackground ) == "REMOVE" )
        return;

    autorefresh = get_session_param(session, "autorefresh");

    formparams = get_session_param(session, "fetchParameters");
    
    $.ajax(
    {
        type: "GET",
        url: formaction,
        data: formparams,
        async: true,
        dataType: "json",
        success: function(result)
        {
            // Store the column model details for use by the grpahing tool, but only if
            // there are enough rows to produce a column model
            //if ( !get_session_param ( session, "jsondata" ) && result.colmodel.length > 0 )
            //{
                //graph_properties = {
                    //colmodel: result.colmodel,
                    //colnames: result.colnames,
                    //graphopt: result.graphopt
                //}
                //set_session_param(session, "jsondata", graph_properties);
            //}
            loadMapToDashboard(session);
           	val = updateGOLAP(session, result, inbackground);

			if ( !autorefresh )
            	hideshowlayer(session, false);
            showGOLAPFilters(session, autorefresh);
			applyGOLAPFiltersMap(session, "NONE", "", "", false);
		    if ( !inbackground )
            		showDashboard();
            set_loading_status (false);

            if (  postQueryRefreshHandling(session, "getMapOutput", autorefresh, inbackground ) == "REMOVE" )
                return;

			// Automatically load GOLAP filter pane
			if ( !autorefresh ) 
			{
            	hideshowlayer(session, false);
			}

            // Resize Map is Filter has reduced space
            sizeDashboardMapToFitParent("map");
        },
        error: function(x, e)
        {
            set_session_param(session, "jsondata", false);
            set_session_param(session, "urlparams", false);
            set_loading_status (false);
            set_loading_status (false);
            alert("No data found matching your criteria or internal server error");   

            if (  postQueryRefreshHandling(session, "getMapOutput", autorefresh, inbackground ) == "REMOVE" )
                return;

        }
    });

    //autorefreshes[session]["request"].send(null);
}

function getGOLAPPositions(type, session, formaction, formparams, autorefresh, inbackground )
{
	if ( !inbackground )
    	hideOverlay();

	if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
	{
			autorefreshes[session].length = 0;
			//clearsession(session);
			return;
	}
				
	if ( !autorefreshes[session] )
	{
       		autorefreshes[session] = [];
			autorefreshes[session]["type"] = type;
			autorefreshes[session]["session"] = session;
			autorefreshes[session]["formaction"] = formaction;
			autorefreshes[session]["formparams"] = formparams;
			autorefreshes[session]["autorefresh"] = autorefresh;
			autorefreshes[session]["refreshes"] = 0;
			autorefreshes[session]["status"] = "IDLE";
			autorefreshes[session]["request"] = "IDLE";
	}

    if ( !inbackground )
		if ( autorefreshes[session].timeout )
        {
		    if ( autorefreshes[session].timeout )
            {
			    clearTimeout ( autorefreshes[session].timeout );
		        autorefreshes[session].timeout = false;
            }
        }


    if (navigator.appName == "Microsoft Internet Explorer")
		autorefreshes[session]["request"] = new ActiveXObject("Microsoft.XMLHTTP");
    else
		autorefreshes[session]["request"] = new XMLHttpRequest();

	if ( !inbackground )
		set_loading_status ( true );
	else
		$("#loadindicator").addClass("loading2");


	fetchurl = formaction;
	fetchparams = formparams;
   	if ( get_session_param(session, "refreshxml" ) && autorefresh && inbackground)
	{
          //newxml = get_session_param(session, "refreshxml" );
		  if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
		  	fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,newxml,fetchparams);
		  else
		  	fetchparams = fetchparams + "&xmlin=" + get_session_param(session, "refreshxml" );
	}

   	if ( get_session_param(session, "originalxml" ) && !inbackground)
	{
          //newxml = get_session_param(session, "refreshxml" );
		  if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
		  	fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,newxml,fetchparams);
		  else
		  	fetchparams = fetchparams + "&xmlin=" + get_session_param(session, "originalxml" );
	}

    // Force autorefresh if menu option specifies
    if ( get_session_param(session, "autorefresh" ) )
        autorefresh = true;


    autorefreshes[session]["request"].open("GET", fetchurl + "?" + fetchparams, true);
    autorefreshes[session]["request"].onreadystatechange = function() {
        if (autorefreshes[session]["request"].readyState != 3) 
        if (autorefreshes[session]["request"].readyState == 4) {
			if ( autorefreshes[session]["request"].status != 200 )
			{
				alert ( "Query action failed - status code " + autorefreshes[session]["request"].status );
				if ( !inbackground )		
            		set_loading_status (false );
				if ( !inbackground )		
            		set_loading_status (false );
				$("#loadindicator").removeClass("loading2");
				return;
			}
            //document.getElementById("accordion").className = "";
			if ( type == "swLineButton" )
			{
            	updateLineView(session, autorefreshes[session]["request"], inbackground);
                resizeDashboard();
				if ( !inbackground )
            		showDashboard();
			}
			else
			{
            	val = updateGOLAP(session, autorefreshes[session]["request"], inbackground);
				//if ( val && ( !autorefresh ) )
					applyGOLAPFiltersMap(session, "NONE", "", "", false);
				if ( !inbackground )
            		$("#showmap").click();
			}


			if ( autorefreshes[session]["status"] == "REMOVE" )
			{
				autorefreshes[session].length = 0;
				$("#loadindicator").removeClass("loading2");
				if ( !inbackground )		
            		set_loading_status (false );
				//clearsession(session);
				return;
			}
				
	
			if ( autorefreshes[session].timeout )
				clearTimeout ( autorefreshes[session].timeout );
			cmd = "getGOLAPPositions( " +
							"autorefreshes['" + session + "']['type'], " +
							"autorefreshes['" + session + "']['session'],  " +
							"autorefreshes['" + session + "']['formaction'],  " +
							"autorefreshes['" + session + "']['formparams'], " +
							 "true, true )";
			autorefreshes[session]["cmd"] = cmd;
			if ( autorefresh ) 
				autorefreshes[session].timeout = setTimeout ( cmd,  10000);

			// Automatically load GOLAP filter pane
			//if ( !autorefresh || autorefreshes[session]["refreshes"]++ == 0 )
			if ( !autorefresh )
			{
            	hideshowlayer(session, false);
            	//if ( get_session_param(session, "hasline" ) )
                	//initGOLAPFilters(session, false, true, true);
            	//else
                	//initGOLAPFilters(session, false, false, true);
			}
            showGOLAPFilters(session, autorefresh);
				
			if ( !inbackground )		
           		set_loading_status (false );
			else
				$("#loadindicator").removeClass("loading2");
        }
    }
    autorefreshes[session]["request"].send(null);
}

// Resize the map to fill the container after a change of container size
function resizeMap()
{
    if ( mapEngine == "google" )
	    google.maps.event.trigger(map, "resize");
    else
	    map.updateSize();

}

function getClockTime()
{
    currentTime = new Date()
    var hours = currentTime.getHours()
    var minutes = currentTime.getMinutes()
    var seconds = currentTime.getSeconds()
    var month = currentTime.getMonth() + 1
    var day = currentTime.getDate()
    var year = currentTime.getFullYear()

    if (minutes < 10)
        minutes = "0" + minutes
    if (seconds < 10)
        seconds = "0" + seconds
    return year + "/" + month + "/" + day + " " + hours + ":" + minutes + ":" + seconds
}

// OSM Projections - initialised later
var fromProjection = false;
var toProjection = false;
var extent = false;

function initialise()
{
	
    getUserMenu(iconnexUser, menuCode, baseUrl );
    //loadWorkspace("DEFAULT");
}
function initialiseMap()
{
    if ( !map )
	    if ( mapEngine == "osm" )
		    mapInitialiseOSM()
	    else
		    mapInitialiseGoogle()
}
function mapInitialiseOSM()
{
        fromProjection = new OpenLayers.Projection("EPSG:4326"); // transform from WGS 1984
        toProjection = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
        extent = new OpenLayers.Bounds(-1.2,51.3,0.4,55.6).transform(fromProjection,toProjection);
        var size, icon;
        
        var options = {
          restrictedExtent : extent,
          controls: [
            new OpenLayers.Control.Navigation(),
            new OpenLayers.Control.PanZoomBar(),
            new OpenLayers.Control.Attribution()
          ]
        };

        map = new OpenLayers.Map('map');
        layer = new OpenLayers.Layer.WMS( "OpenLayers WMS", 
                "http://vmap0.tiles.osgeo.org/wms/vmap0", {layers: 'basic'} );

        var mapnik = new OpenLayers.Layer.OSM(
            "Reading", 
            osm_base + "tiles/${z}/${x}/${y}.png", 
            {isBaseLayer:true,displayInLayerSwitcher:true,zoomOffset:11,
                attribution: '',
                resolutions: [76.4370282714844,38.2185141357422,19.1092570678711,9.55462853393555,4.77731426696777,2.38865713348389,1.19432856674194]}
            );
            map.addLayer(mapnik);

      	var extent = new OpenLayers.Bounds(-1.2,51.3,0.4,55.6).transform(fromProjection,toProjection);
		map.setOptions({restrictedExtent: extent});
        map.setCenter(new OpenLayers.LonLat(-0.90,51.45).transform(fromProjection,toProjection), 0); // 0=relative zoom level
   		$("#showreport").click();
}

/*
** Before querying tell session whether it needs/allows autorefreshing
** and if not set create sessio parameters which indicate what url and params
** are required to run autorefresh
*/
function preQueryRefreshHandling(type, session, formaction, formparams, autorefresh, inbackground )
{
    // Force autorefresh if menu option specifies
    if ( get_session_param(session, "autorefresh" ) )
        autorefresh = true;

	if ( autorefreshes[session] && autorefreshes[session]["status"] == "REMOVE" )
	{
			autorefreshes[session].length = 0;
		    return "REMOVE";
	}

    				
    // If first time session run or
    // new query started on a session then set query parametees for future
    // refreshes on this session
	if ( !inbackground || !autorefreshes[session] )
	{
       		autorefreshes[session] = [];
			autorefreshes[session]["type"] = type;
			autorefreshes[session]["session"] = session;
			autorefreshes[session]["formaction"] = formaction;
			autorefreshes[session]["formparams"] = formparams;
			autorefreshes[session]["autorefresh"] = autorefresh;
			autorefreshes[session]["refreshes"] = 0;
			autorefreshes[session]["status"] = "IDLE";
			autorefreshes[session]["request"] = "IDLE";
	}

    if (navigator.appName == "Microsoft Internet Explorer")
		autorefreshes[session]["request"] = new ActiveXObject("Microsoft.XMLHTTP");
    else
		autorefreshes[session]["request"] = new XMLHttpRequest();

    // Dont tell user to wait in refreshing in background
	if ( !inbackground )
		set_loading_status ( true );
	else
        if ( get_session_param(session, "current_view_type") == "DASHBOARD" )
        {
		    $("#dash" + session + " .portlet-header").addClass("loading2");
        }
        else if ( get_session_param(session, "current_view_type") == "MAPVIEW" )
        {
		    $("#dash" + "map" + " .portlet-header").addClass("loading2");
        }
        else
		    $("#loadindicator").addClass("loading2");

	fetchurl = formaction;
	fetchparams = formparams;
    if ( inbackground ) 
    {
   	    if ( get_session_param(session, "refreshxml" ) && autorefresh)
	    {
		    if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
		  	    fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,newxml,fetchparams);
		    else
		  	    fetchparams = fetchparams + "&xmlin=" + get_session_param(session, "refreshxml" );

		    if ( autorefreshes[session]["refreshes"] > 0 )
            if  ( get_session_param(session, "lastrefresh" ) )
            {
                fetchparams = fetchparams + "&MANUAL_since=" + get_session_param(session, "lastrefresh" );
            }
	    }
        else if ( get_session_param(session, "lastrefresh" ) )
        {
            fetchparams = fetchparams + "&MANUAL_since=" + get_session_param(session, "lastrefresh" );
        }
		autorefreshes[session]["refreshes"]++;
    }
    else
    {
		autorefreshes[session]["refreshes"] = 0;
        original = get_session_param(session, "originalxml" );
        if ( original )
	        if ( fetchparams.match(/xmlin=[A-Za-z_].xml/) )
	  	        fetchparams = fetchparams.replace(/xmlin=[A-Za-z_].xml/,"xmlin=" + original,fetchparams);
	        else
	  	        fetchparams = fetchparams + "&xmlin=" + original;
    }
    set_session_param(session, "fetchParameters", fetchparams);

    return autorefresh;
}

/*
** After a query is run, if refresh mode is set then set a timer to 
** cause refresh in x seconds
*/
function postQueryRefreshHandling(session, refreshFunction, autorefresh, fromBackground )
{
    
    if ( autorefreshes[session]["status"] == "REMOVE" )
    {
        autorefreshes[session].length = 0; 
        $("#loadindicator").removeClass("loading2");
        if ( !fromBackground )		
            set_loading_status (false );
		return "REMOVE";
	}
				
    if ( autorefreshes[session].timeout )
        clearTimeout ( autorefreshes[session].timeout );
    cmd = refreshFunction + "( " +
							"autorefreshes['" + session + "']['type'], " +
							"autorefreshes['" + session + "']['session'],  " +
							"autorefreshes['" + session + "']['formaction'],  " +
							"autorefreshes['" + session + "']['formparams'], " +
							 "true, true )";
    autorefreshes[session]["cmd"] = cmd;
    if ( autorefresh ) 
               if ( fromBackground )
				    autorefreshes[session].timeout = setTimeout ( cmd,  10000);
               else
				    autorefreshes[session].timeout = setTimeout ( cmd,  1000);

    if ( get_session_param(session, "current_view_type") == "DASHBOARD" )
    {
	    $("#dash" + session + " .portlet-header").removeClass("loading2");
    }
    else if ( get_session_param(session, "current_view_type") == "MAPVIEW" )
    {
	    $("#dash" + "map" + " .portlet-header").removeClass("loading2");
    }
    else
        $("#loadindicator").removeClass("loading2");
    return "OK";
}

function mapInitialiseGoogle()
{
    // Reading
    //var latlng = new google.maps.LatLng(51.455041, -0.969088);
	//Southampton
	//var latlng = new google.maps.LatLng(50.904966, -1.40323);
    // Reading 2
	if ( !mapEngineInitialised )
	{
        mapEngineInitialised = true;
    	var script = document.createElement("script");
    	script.type = "text/javascript";
    	script.src = "http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyABtlQfVe0qpbhufQH96bmtp71M7Hm0aOU&async=2&callback=initialise";
    	document.body.appendChild(script);
	}
    var latlng = new google.maps.LatLng(52.040622, -0.759417);
    var myOptions = {       
        zoom: 12,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map"), myOptions);
    stopIcon = new google.maps.MarkerImage("images/stop.png",
        null,
        null,
        new google.maps.Point(7, 20),
        null);
}

function loadScript()
{
    if ( iconnexUser == "admin" || iconnexUser == "rgb" )
        mapEngine = "osm";

	if ( !mapEngineInitialised && mapEngine == "google" )
	{
        mapEngineInitialised = true;
    	var script = document.createElement("script");
    	script.type = "text/javascript";
    	script.src = "http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyABtlQfVe0qpbhufQH96bmtp71M7Hm0aOU&async=2&callback=initialise";
    	document.body.appendChild(script);
	}
    else
    {
        mapEngineInitialised = true;
    	var script = document.createElement("script");
    	script.type = "text/javascript";
    	script.src = osm_base + "OpenLayers.js";
    	document.body.appendChild(script);
        initialise();
    }
    return;
}

function loadScriptGoogle()
{
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = "http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyABtlQfVe0qpbhufQH96bmtp71M7Hm0aOU&async=2&callback=initialise";
    //script.src = "http://maps.google.com/maps/api/js?sensor=false&key=ABQIAAAAgtVWRSLOp93zH1hilYsImxQcbZF30VMahtSXGkebzgiUq894vhSaqezNzCMc5s9n-FlxzUlpsUgXJQ&async=2&callback=initialise";
    document.body.appendChild(script);
}

window.onload = loadScript;
