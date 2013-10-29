<div style="padding: 10px 80px 0px 80px">
<p>
<?php
    if (Yii::app()->user->allowedAccess('task', 'Administrator'))
	{
?>
<br>
<h1>Welcome to the <i>iConnex</i> Administration Console</h1>
<p>
This panel provides access to all operational, reporting and maintenance features.
<p>
<h3>PWI</h2>
The public web interface shows a public map view on bus stop real time arrivals. Search by route, postcode, street name. As an admnistrator you can see vehicle positions on route.
<p>
<h3>Locations</h2>
View bus stop timetables, send messages to bus stops, view stop system events such as display impacts.
<p>
<h3>Operations</h2>
Access bus operator functions. View vehicle progress on route on map and schematic views. View and modify timetables and access vehicle and journey history reports.
<p>
<h3>Network</h2>
Access road network information such bus traffic light priority requests, map analysis of road network speeds and UTMC data sources.
<p>
<h3>Performance</h2>
Timetable and system reporting. Includes journey time reports, schedule adherence reporting.
<p>
<h3>Maintenance</h2>
System Administrative operations and reporting. Includes system deactivation.
<p>
<?php
	}
    else
    if (Yii::app()->user->allowedAccess('task', 'Authority'))
	{
?>
<br>
<h1>Welcome to the <i>iConnex</i> Local Authority Control Console</h1>
<p>
This panel provides access to bus network operations and reporting.
<p>
<h3>PWI</h2>
The public web interface shows a public map view on bus stop real time arrivals. Search by route, postcode, street name. As an admnistrator you can see vehicle positions on route
<p>
<h3>Locations</h2>
View bus stop timetables, send messages to bus stops, view stop system events such as display impacts.
<p>
<h3>Operations</h2>
Access bus operator functions. View vehicle progress on route on map and schematic views. View and modify timetables and access vehicle and hjourney history reports
<p>
<h3>Network</h2>
Access road network information such bus traffic light priority requests, map analysis of road network speeds and UTMC data sources.
<p>
<h3>Performance</h2>
Timetable and system reporting. Includes journey time reports, schedule adherence reporting.
<p>
</div>
<?php
	}
	else
	{
?>
<h1>Welcome to <i>iConnex</i> Web</h1>
	<p>
Login to access control functions.
<p>
Use the public web interface to access a public map view on bus stop real time arrivals. Search by route, postcode, street name. 
<?php
		}
?>
</p>

