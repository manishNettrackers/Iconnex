/*
var map;
function initialise()
{
    // Reading
    //var latlng = new google.maps.LatLng(51.455041, -0.969088);
	//Southampton
	var latlng = new google.maps.LatLng(50.904966, -1.40323);
    var myOptions = {
        zoom: 12,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    }
    map = new google.maps.Map(document.getElementById("map"), myOptions);
    stopIcon = new google.maps.MarkerImage("images/stop.png",
        null,
        null,
        new google.maps.Point(7, 20),
        null);
    busIcon = new google.maps.MarkerImage("images/bus.png",
        null,
        null,
        new google.maps.Point(10, 10),
        null);
}

function loadScript()
{
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = "http://maps.google.com/maps/api/js?sensor=false&key=ABQIAAAAgtVWRSLOp93zH1hilYsImxQcbZF30VMahtSXGkebzgiUq894vhSaqezNzCMc5s9n-FlxzUlpsUgXJQ&callback=initialise";
    document.body.appendChild(script);
}
*/
window.onload = loadScript;

