<!doctype html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <title>Open Data Service API Version 1</title>
    <style>pre{background-color:#F9F9F9;
border:1px dashed #2F6FAB;
color:black;
line-height:1.1em;
padding:1em;
}
</style>
</head>
<body>
<h1>Open Data Service API Version 1</h1>
Responses to valid requests are XML.
<h2>Bus</h2>
Real-time arrival and departure information for buses along with supporting data.<br>Responses are XML by default, but JSON responses are available by specifying the file extension as per examples below.
<h3>locations</h3>
Provides details of all bus stop locations along with the services which call at each location.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/locations
</pre>
Details of a specific location can be obtained by using a sub-location with the desired location identifier (usually a NaPTAN atco_code).
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/locations/039027710001
</pre>
To obtain JSON, add the extension.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/locations.json
http://ods.reading-travelinfo.co.uk/api/1/bus/locations/039027710001.json
</pre>
It is also possible to request the locations which are served by a specific service by including the route code as a parameter. Again, to request JSON instead of XML, use the file extension in the URL (before the parameters).
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/locations?service=17
http://ods.reading-travelinfo.co.uk/api/1/bus/locations.json?service=17
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/locations.xsd">here</a>.
<h3>services</h3>
Provides details of bus services.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/services
</pre>
Details of a specific service can be obtained by using a sub-location with the required service identifier.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/services/17
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/services.xsd">here</a>.
<h3>servicepatterns</h3>
Provides a general order of bus stops on a service. Note that this is not the full detail of all the possible routes that a service takes, but rather an indication of how to order stops for display in a single-line linear overview.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/servicepatterns
</pre>
The pattern for a specific service can be requested by passing the service id as a parameter.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/servicepatterns?service=17
</pre>
It is also possible to request patterns for a specific set of services with a comma-delimited list.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/servicepatterns?service=16,17
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/servicepatterns.xsd">here</a>.
<h3>calls</h3>
Provides details of expected calls (arrivals/departures) at the requested locations. It is necessary to specify a location identifier.<br/>
This feed is likely to be called frequently and is only valid for 30s, hence there is currently no header information provided as part of the feed.<br/>
Support may be added to query multiple locations in which case there would be an additional <Locations> wrapper with multiple <Location> children as currently returned.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/calls/039027710001
http://ods.reading-travelinfo.co.uk/api/1/bus/calls/039027710001.json
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/calls.xsd">here</a>.
<h3>status</h3>
For bus service status. This information is based upon the compliance of actual departures in the last half an hour and the predicted departures in the next half an hour at the time of the request. It is possible to request the status of a specific service by including the service as a parameter.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/bus/status
http://ods.reading-travelinfo.co.uk/api/1/bus/status?service=17
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/status.xsd">here</a>.
<h2>Parking</h2>
Real-time parking information and supporting data.
<h3>static</h3>
For static information about car parks such as locations, opening times and capacities.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/parking/static
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/carParkStatic.xsd">here</a>.
<h3>dynamic</h3>
For dynamic information about car parks such as occupancies.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/parking/dynamic
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/carParkDynamic.xsd">here</a>.
<h2>Rail</h2>
Railway information. Spaces in a station name should be escaped with "%20".
<pre>
http://ods.reading-travelinfo.co.uk/api/1/rail/stations/Reading
http://ods.reading-travelinfo.co.uk/api/1/rail/stations/Reading%20West
</pre>
<h2>Road Links</h2>
Road link information.
<h3>static</h3>
For static road link information.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/roadlinks/static
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/roadLinkStatic.xsd">here</a>.
<h3>dynamic</h3>
For dynamic road link information.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/roadlinks/dynamic
</pre>
Schema available <a href="http://ods.reading-travelinfo.co.uk/schemas/roadLinkDynamic.xsd">here</a>.
<h2>Variable Message Signs</h2>
VMS locations and current messages being shown.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/vms
</pre>
<h2>Works</h2>
For information about Road Works.
<pre>
http://ods.reading-travelinfo.co.uk/api/1/works
</pre>
Schemas available <a href="http://ods.reading-travelinfo.co.uk/schemas/roadTrafficDisruptions.xsd">here</a> and <a href="http://ods.reading-travelinfo.co.uk/schemas/roadEvents.xsd">here</a>.
</body>
</html>
