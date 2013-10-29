var map;
var stopIcon;
var stopXML;
var stopMarkers = [];
var stopsReady = false;
var fbId = null;
var siId = null;
var biId = null;
var itemClicked = false;
var selStopName;
var forceZoom = false;
var selectedRoute = null;
var selectedStop = null;
var yii_base = "/";
var yii_framework_base = yii_base + "viewer/";
var yii_framework_app_name = yii_framework_base
var yii_framework_app = yii_framework_base + "iconnex/";

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
    var o = document.getElementById("overlay");
    o.style.visibility = "visible";
}

function clearOverlayIntervals()
{
    if (siId) clearInterval(siId);
    if (biId) clearInterval(biId);
}

function hideOverlay()
{
    clearOverlayIntervals();
    var o = document.getElementById("overlay");
    o.style.visibility = "hidden";
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

function nothing(e)
{
    if (!e)
        var e = window.event;
    e.cancelBubble = true;

    if (e.stopPropagation)
        e.stopPropagation();
}

function toggleStops(el, n)
{
    var clicked = document.getElementById(el);
    var c = clicked.childNodes;
    var j = 0;
    for (var i = 0; i < c.length; i++) {
        if (c.item(i).nodeName == "DIV") {
            if (j++ == 0)
                continue;
            var ss = c.item(i);
            var s;
            var next = ss.nextSibling;
            while (next.nodeType != 1)
                next = next.nextSibling;

            if (ss.currentStyle)
            {
                if (ss.currentStyle["backgroundPosition"])
                    s = ss.currentStyle["backgroundPosition"];  // Opera
                else
                    s = ss.currentStyle["backgroundPositionX"] + " " + ss.currentStyle["backgroundPositionY"]; // IE
            }
            else if (document.defaultView && document.defaultView.getComputedStyle)
                s = document.defaultView.getComputedStyle(ss, "")["backgroundPosition"];
            else
                s = ss.style["backgroundPosition"];

            if (s == "0px 2px" || s == "0% 2px")
            {
                ss.innerHTML = "<a onclick='nothing(event)' href=\"javascript:toggleStops('rres" + n + "', " + n + ")\";>Hide stops</a>";
                next.style.display = "block";
                toggleLocs(clicked, 1);
            }
            else
            {
                ss.innerHTML = "<a href=\"javascript:toggleStops('rres" + n + "', " + n + ")\";>Show stops</a>";
                next.style.display = "none";
                toggleLocs(clicked, 0);
            }
            ss.style.backgroundPosition = ((s == "0px 2px" || s == "0% 2px") ? "0px -44px" : "0px 2px");
            break;
        }
    }
}

function switchDirection(el, n)
{
    var clicked = document.getElementById(el);
    toggleLocs(clicked, -1);
}

function deleteStops(except)
{
    var keepMarker = null;
    if (stopMarkers) {
        for (i in stopMarkers)
        {
            if (!(except === null))
            {
                if (stopMarkers[i].get("atco") != except)
                    stopMarkers[i].setMap(null);
                else
                    keepMarker = stopMarkers[i];
            }
            else
                stopMarkers[i].setMap(null);
        }
        
        if (keepMarker)
        {
            stopMarkers[0] = keepMarker;
            stopMarkers.length = 1;
        }
        else
            stopMarkers.length = 0;
    }
}

function getNodeValue(obj, tag)
{
    if (obj.getElementsByTagName(tag)[0] == null)
        return "";

    if (obj.getElementsByTagName(tag)[0].firstChild == null)
        return "";
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

function stopInfo(atcoCode)
{
    var webstop;
    var sTmp;
    var appHTML;

    if (navigator.appName == "Microsoft Internet Explorer") {
        webstop = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        webstop = new XMLHttpRequest();
    }

    sTmp = yii_framework_app + "index.php?r=webstop&locations=" + atcoCode;
    webstop.open("GET", sTmp, "true");
    webstop.onreadystatechange = function() {
        if (webstop.readyState == 4) {
            hideStatus("dstatus");
            var wsXML = webstop.responseXML;
            var wsHTML;
            var calls = wsXML.getElementsByTagName("call");
            wsHTML = "<p style=\"margin:0; text-align:center;\"><strong>" + getNodeValue(wsXML, "common_name") + " - " + getNodeValue(wsXML, "naptan_code") + "</strong></p>";
            if (calls.length == 0)
                wsHTML += "<br><p style=\"font-size:1.4em\; text-align:center\">Please refer to timetable</p>";
            else
            {
                wsHTML += "<TABLE BORDER=0 WIDTH=\"100%\" style=\"font-size:0.75em;\"><TR><TH align=left>Route</TH><TH align=left>Destination</TH><TH align=right>Due</TH></TR>";
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

            //var url = "http://" + location.hostname + "/cgi-bin/wsw3.sh?" + atcoCode;
            var url = "http://" + location.hostname + yii_framework_app + "index.php?r=webstop/popout&locations=" + atcoCode;
            document.getElementById("popout").innerHTML = '<a title="Show in new window" href="' + url + '" target="_blank"><img src="images/popout.png"></img></a></div>';
            var now = new Date();
            var nowTime = hhmmss(now);
            showStatus("dstatus", "Last refreshed " + nowTime);
        }
    }
    showStatus("dstatus", "Loading...");
    webstop.send(null);
}

function updateStops(r)
{
    var stopXML = r.responseXML;
    var ll;
    var common_name;
    var atco_code;
    var common_name;
    deleteStops(null);
    var stops = stopXML.getElementsByTagName("stop");
    for (var i = 0; i < stops.length; i++) {
        ll = new google.maps.LatLng(getNodeValue(stops[i], "latitude"), getNodeValue(stops[i], "longitude"));
        atco_code = getNodeValue(stops[i], "atco_code");
        naptan_code = getNodeValue(stops[i], "naptan_code");
        common_name = getNodeValue(stops[i], "common_name");
        var marker = new google.maps.Marker({position: ll, title:common_name + " - " + naptan_code, icon:stopIcon, zIndex:1000});
        marker.set("atco", atco_code);
        marker.set("naptan", naptan_code);
        marker.set("name", common_name);
        google.maps.event.addListener(marker, "click", function() {
            document.getElementById("depinfo").innerHTML = "";
            hideStatus("mstatus2");
            showOverlay();
            itemClicked = true;
            selStopName = this.get("name");
            clearOverlayIntervals();
            map.setCenter(this.getPosition());
            map.setZoom(16);
            var sTmp = this.get("atco");
            stopInfo(sTmp);
            var stopInfoC = function() { stopInfo(sTmp) }; // Workaround for IE
            siId = setInterval(stopInfoC, 30000);
        });
        marker.setMap(map);
        stopMarkers.push(marker);
    }
    var viewParams = stopXML.getElementsByTagName("parameters");
    ll = new google.maps.LatLng(getNodeValue(viewParams[0], "latitude"), getNodeValue(viewParams[0], "longitude"));
    var zoom = getNodeValue(viewParams[0], "zoom");
    map.setCenter(ll);
    map.setZoom(parseInt(zoom));
}

function switchRoute(el, route)
{
    var stops;
    var sTmp = "";
    hideStatus("mstatus2");
    hideOverlay();
    deleteStops(null);
    stopsReady = false;
    itemClicked = false;
    showStatus("mstatus", "Loading...");

    if (!(selectedRoute === null))
        selectedRoute.className = selectedRoute.className.replace(" selected", "");
    el.className = el.className + " selected";
    selectedRoute = el;

    if (navigator.appName == "Microsoft Internet Explorer")
        stops = new ActiveXObject("Microsoft.XMLHTTP");
    else
        stops = new XMLHttpRequest();

    sTmp = "index.php?r=pwi/pwi/stops&rt=" + route;
    stops.open("GET", sTmp, "true");
    stops.onreadystatechange = function() {
        if (stops.readyState == 4) {
            showStatus("mstatus2", "Showing stops on route " + route);
            updateStops(stops);
            stopsReady = true;
            hideStatus("mstatus");
        }
    }
    stops.send(null);
}

function showStopFromMenu(el, atco_code, naptan_code, common_name, latitude, longitude)
{
    if (!(selectedStop === null))
    {
        selectedStop.className = selectedStop.className.replace(" selected", "");
        selectedStop.style.backgroundColor = '';
    }
    if (!(selectedRoute === null))
    {
        selectedRoute.className = selectedRoute.className.replace(" selected", "");
        selectedRoute.style.backgroundColor = '';
    }

    el.className = el.className + ' selected';
    el.style.backgroundColor = '#cde';
    selectedStop = el;
    showStop(atco_code, naptan_code, common_name, latitude, longitude);
}

function showStop(atco_code, naptan_code, common_name, latitude, longitude)
{
    hideStatus("mstatus2");
    clearOverlayIntervals();
    deleteStops(null);

    selStopName = common_name;
    ll = new google.maps.LatLng(latitude, longitude);
    var marker = new google.maps.Marker({position: ll, title:common_name + " - " + naptan_code, icon:stopIcon, zIndex:1000}); 
    marker.set("atco", atco_code);
    marker.set("naptan", naptan_code);
    marker.set("name", common_name);
    google.maps.event.addListener(marker, "click", function() {
        showStop(this.get("atco"), this.get("naptan"), this.get("name"), this.getPosition().lat(), this.getPosition().lng());
    });
    marker.setMap(map);
    stopMarkers.push(marker);

    document.getElementById("depinfo").innerHTML = "";
    showOverlay();
    itemClicked = true;
    map.setCenter(ll);
    map.setZoom(16);
    stopInfo(atco_code);
    var stopInfoC = function() { stopInfo(atco_code) }; // Workaround for IE
    siId = setInterval(stopInfoC, 30000);
}

function results(r)
{
    var resXML = r.responseXML;
    var resHTML = "";
    var name;
    var f;
    var bearing = "";
    var nodes = resXML.getElementsByTagName("stop");
    if (nodes.length > 0) {
        resHTML += '<div class="menuheader"><p>Stops</p></div>';
        for (var i = 0; i < nodes.length; i++) {
            bearing = getNodeValue(nodes[i], "bearing");
            name = getNodeValue(nodes[i], "common_name");
            if (bearing != "")
                name += " " + bearing + "-bound";
            var str = name;
            str = str.replace("'", "%27");
            f = "showStopFromMenu(this, \""
                + getNodeValue(nodes[i], "atco_code") + "\", \""
                + getNodeValue(nodes[i], "naptan_code") + "\", \""
                + str + "\", "
                + getNodeValue(nodes[i], "latitude") + ", "
                + getNodeValue(nodes[i], "longitude") + ");";
            resHTML += "<div class='rresult' onclick='" + f + "'>"
                + "<div class=\"stopicon\"><img src=\"images/stopicon.png\"></img></div>"
                + "<div class=\"stopname\">"
                + "<p class=\"linky\">"
                + name
                + "</p></div></div>\n";
        }
    }

    var nodes = resXML.getElementsByTagName("route");
    if (nodes.length > 0) {
        resHTML += '<div class="menuheader"><p>Routes</p></div>';
        for (var i = 0; i < nodes.length; i++) {
            resHTML += "<div id='rres" + i + "' class='rresult' onclick=\"switchRoute(this, '" + getNodeValue(nodes[i], "route_code") + "')\";>"
                    + "<div class='routeicon'>"
                        + "<a class=\"route\" style=\"color:#111111\" href=\"javascript:switchRoute(this, '" + getNodeValue(nodes[i], "route_code") + "')\";>" + getNodeValue(nodes[i], "route_code") + "</a>"
                    + "</div>"
                    + "<div class='showstops'><a href=\"javascript:toggleStops('rres" + i + "', " + i + ")\";>Show stops</a></div>"
                    + "<div onclick='nothing(event)' class='dirswitch'><a title=\"Switch direction\" href=\"javascript:switchDirection('rres" + i + "', " + i + ")\";><img src=\"images/dir.png\"></img></a></div>"
                + "</div>\n";

            var rlocs = nodes[i].getElementsByTagName("call");
            if (rlocs.length > 0) {
                resHTML += "<div id=\"rlocs" + i + "_O\" class=\"rlocs\" style=\"display:none\">";
                var dir = "";
                for (var j = 0; j < rlocs.length; j++) {
                    dir = getNodeValue(rlocs[j], "direction");
                    if (dir == "1" && prevDir == "0")
                        resHTML += "</div><div id=\"rlocs" + i + "_I\" class=\"rlocs\" style=\"display:none\">";
                    name = getNodeValue(rlocs[j], "common_name") + " " + getNodeValue(rlocs[j], "bearing");
                    resHTML += "<div><p><a href=\"javascript:showStop('"
                            + getNodeValue(rlocs[j], "atco_code") + "', '"
                            + getNodeValue(rlocs[j], "naptan_code") + "', '"
                            + name + "', "
                            + getNodeValue(rlocs[j], "latitude") + ", "
                            + getNodeValue(rlocs[j], "longitude") + ");\">"
                        + name +  "</a></p></div>\n";
                    prevDir = dir;
                }
                resHTML += "</div";
            }
        }
    }

    if (resHTML == "")
        resHTML = "<div style=\"text-align:center;\"><br>No results.<br><br>Enter a route or post code in the box above, then press enter or click the Search button.<br><br>Note that this search is limited to post codes and bus routes in the vicinity of Milton Keynes</div>";

    document.getElementById("results").innerHTML = resHTML;
}

function search(s)
{
    document.getElementById("results").innerHTML = "Searching...";
    if (navigator.appName == "Microsoft Internet Explorer")
        sReq = new ActiveXObject("Microsoft.XMLHTTP");
    else
        sReq = new XMLHttpRequest();

    sTmp = "index.php?r=pwi/pwi/search&q=" + s;
    sReq.open("GET", sTmp, "true");
    sReq.onreadystatechange = function() {
        if (sReq.readyState == 4) {
            results(sReq);
        }
    }
    sReq.send(null);
}

function initialise()
{
    // Reading
    //var latlng = new google.maps.LatLng(51.455041, -0.969088);
    // Milton Keynes
    var latlng = new google.maps.LatLng(52.040626, -0.759414);

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
    var script = document.createElement("script");
    script.type = "text/javascript";
    //script.src = "http://maps.google.com/maps/api/js?sensor=false&key=ABQIAAAAgtVWRSLOp93zH1hilYsImxQcbZF30VMahtSXGkebzgiUq894vhSaqezNzCMc5s9n-FlxzUlpsUgXJQ&callback=initialise";
    script.src = "http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyABtlQfVe0qpbhufQH96bmtp71M7Hm0aOU&callback=initialise";
    document.body.appendChild(script);
}

window.onload = loadScript;
