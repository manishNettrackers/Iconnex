var map;
var markerIcon = [];
var theMarker = [];

function plot(idx, lat, lng) {
        ll = new google.maps.LatLng(lat, lng);
        theMarker[idx] = new google.maps.Marker({position: ll, title:"marker title", icon:markerIcon[idx], zIndex:1000});
        theMarker[idx].setMap(map);
}

function initialise() {
    var o = {
        zoom: 12,
        center: new google.maps.LatLng(50.904964, -1.403233),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById('map_canvas'), o);

    markerIcon[0] = new google.maps.MarkerImage("http://10.0.0.9/infohostpd/z/icons/render.php?type=bus&elementTypes=route,lateness,fleetno,bearing&elementValues=17,600,804,90",
        null,
        null,
        new google.maps.Point(1, 1),
        null);

    markerIcon[1] = new google.maps.MarkerImage("http://10.0.0.9/infohostpd/z/icons/render.php?type=bus&elementTypes=route,lateness,fleetno,bearing&elementValues=18,-30,806,90",
        null,
        null,
        new google.maps.Point(1, 1),
        null);

    plot(0, 50.904964, -1.403233);
    plot(1, 50.924964, -1.423233);
}

google.maps.event.addDomListener(window, 'load', initialise);

