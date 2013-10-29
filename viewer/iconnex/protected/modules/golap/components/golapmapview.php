<?php
    // Mapping and Map Filter Events
    Yii::app()->clientScript->registerScript('golapmapEvents',<<<EOD

    var maphtml = "oo"; 
    // 'Show Only' option pressed
    $('.mapfilterck_mutex').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;

        container = $(this).closest(".mpflttab");

        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");
       	filterGOLAP(session, "MUTEX", fltname, "", this.checked);
	});

    // 'Show All' option pressed
    $('.mapfilterck_showall').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;
        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");
       	filterGOLAP(session, "SHOWALL", fltname, "", this.checked);
	});

    // 'Show None' option pressed
    $('.mapfilterck_shownone').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].mutex = this.checked;
        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");
       	filterGOLAP(session, "SHOWNONE", fltname, "", this.checked);
	});

    // 'Show None' option pressed
    $('.mapfilterck_intersect').live('click', function(event) {
   		var session = get_golap_session_closest(this, ".mpflttab");
		circleFilters[session].intersect = this.checked;
        if ( this.checked )
            $('.mapfilterck_intersect').each(function()  {
                $(this).attr("checked", "checked");
            } );
        else
            $('.mapfilterck_intersect').each(function()  {
                $(this).attr("checked", "");
            } );
        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");
       	filterGOLAP(session, "INTERSECT", fltname, "", this.checked);
	});

    // Filter check box clicked
    $('.mapfilterck').live('click', function(event) {

        var a = this;
        var value = this.name;
        var checked = this.checked;
        html = $(this).attr('innerHTML');
        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");

        var session = get_golap_session_closest(this, ".mpflttab");
        filterGOLAP(session, "FILTER", fltname, value, checked);
    });

    // Metric Radio button clicked
    $('.mapfilterrad').live('click', function(event) {

	    var a = this;
        var value = this.name;
        var checked = this.checked;
        html = $(this).attr('innerHTML');
        fltname = $(this).parent().attr("id");
        fltname = fltname.replace(/mpflttab_/, "");
        fltname = fltname.replace(/_/g, " ");

   		var session = get_golap_session_closest(this, ".mpflttab");
        $("#mapflttab_metrics .mapfilterrad").attr("checked", "");
        $(".mapfilterrad").attr("checked", "");
        $(this).attr("checked", "checked");
        for ( var index in circleFilters[session]["metrics"] )
        {   
            if ( index == value )
            {
                circleFilters[session]["metrics"][index] = true;
            }
            else
            {
                circleFilters[session]["metrics"][index] = false;
            }
        }

        changeMapIconsMetric (session, value);

    
        filterGOLAP(session, "REFRESH", fltname, value, checked);
    });

    // 'Clear Map' pressed
    $('#clearmap').live('click', function(event) {
	    clearmap();
    });

    // User types text into search box, if text box is cleared or text is less than
    // 3 characters dont automatically apply filter and hide all the boxes
    $('.mapfiltersearchbox').live('keyup', function(event) {

        var a = this;
        fltname = $(this).attr("id");
        matchstring = $(this).attr("value");
        fltname = fltname.replace(/mfcks_/, "");
        fltname = fltname.replace(/_/g, " ");

        limit = 2
        // If retrun pressed search regardless of string length
        if ( event.keyCode == 13 )
            limit = 0;

        var session = get_golap_session_closest(this, ".mpflttab");

        narrowDownFilterCheckboxes(session, fltname, matchstring, limit);
        return false;
    });

/*
    maphtml = '<div id="dashmap" style="padding: 0px">';
    maphtml += '<div id="mapfilter" class="1curved" style="display:none">';
    maphtml += '<div class="content curved">';
    maphtml += '<div id="mapfiltertabs">';
    maphtml += '<ul>';
    maphtml += '<li><a href="#tabs-1">Nunc tincidunt</a></li>';
    maphtml += '</ul>';
    maphtml += '<div id="tabs-1">';
    maphtml += '<p>Proin elit</p>';
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
*/


EOD
,CClientScript::POS_READY);


?>

    <!--div id="mapfilter" class="1curved" style="display:none">
        <div class="content curved">
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
<div id="mapcol" style="height: 90%; margin: 0px 0px 0px 0px; display: inline; width: 100%; float: left;">
    <div id="mstatus" class="stattxt"></div>
    <div id="mstatus2" class="stattxt"></div>
    <div id="map"></div>
</div-->
