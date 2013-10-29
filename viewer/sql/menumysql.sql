

delete from cent_user where usernm = "rbc";
insert into cent_user values ( 0, "rbc", "Reading BC", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "rgbdesp";
insert into cent_user values ( 0, "rgbdesp", "Reading BC", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "rgbdesp";
insert into cent_user values ( 0, "mallen", "Marc Allen", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "swise";
insert into cent_user values ( 0, "swise", "swise", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "jhall";
insert into cent_user values ( 0, "jhall", "jhall", NULL, "", "", NULL, NULL, "en_gb", "MASTER");

drop table iconnex_menu;
drop table iconnex_param;
drop table iconnex_menuitem;
drop table iconnex_application;
drop table iconnex_menu_user;
drop table iconnex_workspace;
drop table iconnex_workspace_item ;
drop table iconnex_workspace_parameter ;

create table iconnex_menu
(
	menu_id	SERIAL,
	menu_name	CHAR(30) NOT NULL,
    primary key (menu_id) 
);

create table iconnex_param
(
	param_name	CHAR(30) NOT NULL,
	param_value CHAR(30) NOT NULL,
    primary key (param_name) 
);

create table iconnex_application
(
	app_id	INTEGER ,
	app_name CHAR(30) NOT NULL,
	app_url CHAR(255) NOT NULL,
	has_map INTEGER NOT NULL,
	has_grid INTEGER NOT NULL,
	has_line INTEGER NOT NULL,
	has_chart INTEGER NOT NULL,
	has_report INTEGER NOT NULL,
	autorun INTEGER NOT NULL,
	refresh_xml CHAR(20),
	autorefresh INTEGER NOT NULL,
	
    primary key (app_id) 
);

create table iconnex_menuitem
(
	menu_id	INTEGER,
	menu_no	INTEGER NOT NULL,
	app_id INTEGER NOT NULL ,
    run_location CHAR(10),
    primary key (menu_id, menu_no) 
);

#alter table iconnex_menuitem add constraint (foreign key 
    #(app_id) references iconnex_application);
#alter table iconnex_menuitem add constraint (foreign key 
    #(menu_id) references iconnex_menu);

create table iconnex_menu_user
(
	user_id INTEGER NOT NULL,
	menu_id	INTEGER,
	app_id	INTEGER,
    autorun INTEGER,
    show_accordion INTEGER,
    show_buttons INTEGER
);

#alter table iconnex_menu_user add constraint (foreign key 
    #(user_id) references cent_user);



insert into iconnex_application values ( 1, "Timetable Modification By Stop", 'criteria&target_menu=&project=rtimysql&xmlin=tripcancelbystop.xml', 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 2, "Average Speeds", 'criteria&target_menu=&project=ods&xmlin=avgspeedfilter.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 3, "Maximum Speeds", 'criteria&target_menu=&project=ods&xmlin=maxspeed.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 4, "Services", 'criteria&target_menu=&project=rtimysql&xmlin=services.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 5, "Vehicle Position", 'criteria&target_menu=&project=ods&xmlin=vehiclepos.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 6, "Telematics Details", 'criteria&target_menu=&project=ods&xmlin=telemdetail.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 7, "Driving Events", 'criteria&target_menu=&project=ods&xmlin=telempaest.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 8, "Lateness", 'criteria&target_menu=&project=rtimysql&xmlin=lateness.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 9, "Stop Event Log", 'criteria&target_menu=&project=rtimysql&xmlin=stopevent.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 10, "Stop Event Summary", 'criteria&target_menu=&project=rtimysql&xmlin=stopeventlast.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 11, "Event Log", 'criteria&target_menu=&project=rtimysql&xmlin=eventlog.xml' , 1,1,0,1,1,0,"",0);
insert into iconnex_application values ( 12, "Despatcher Map Veh", 'criteria&target_menu=&project=rtimysql&xmlin=despatcher.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 13, "Despatcher Line View", 'criteria&target_menu=&project=rtimysql&xmlin=lineviewlocs.xml' , 0,0,1,0,0,0,"despatcherline.xml",1);
insert into iconnex_application values ( 14, "Despatcher Map View", 'criteria&target_menu=&project=rtimysql&xmlin=despatcherstops.xml' , 1,0,0,0,0,0,"despatcher.xml",1);
insert into iconnex_application values ( 15, "Line View Vehicles", 'criteria&target_menu=&project=rtimysql&xmlin=despatcherline.xml' , 0,0,1,0,0,0,"",0);
insert into iconnex_application values ( 16, "Timetable", 'criteria&target_menu=&project=rtimysql&xmlin=timetab1.xml' , 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 17, "Stop Timetable", 'criteria&target_menu=&project=rtimysql&xmlin=stoparrperf.xml' , 0,1,0,0,1,0,"",0);
#insert into iconnex_application values ( 18, "Bus Stops", 'criteria&target_menu=&project=rtimysql&xmlin=displaypoint.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 18, "Bus Stops", 'criteria&target_menu=&MANUAL_operator=&project=rtimysql&xmlin=locations.xml', 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 19, "Inventory Items", 'criteria&target_menu=&project=rtimysql&xmlin=unitbuilds.xml' , 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 20, "Compliance Table", 'criteria&target_menu=&project=rtimysql&xmlin=compliancetable.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 21, "Driving Summary", 'criteria&target_menu=&project=ods&xmlin=drivingsummary.xml' , 0,1,0,0,1,1,"",0);
insert into iconnex_application values ( 22, "Driver Speeds", 'criteria&target_menu=&MANUAL_results=maxspeed&project=ods&xmlin=telemdetail.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 23, "Driver Economy", 'criteria&target_menu=&MANUAL_results=economy&project=ods&xmlin=telemdetail.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 24, "Service Maps", 'criteria&target_menu=&project=rtimysql&xmlin=services.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 25, "Driver Comparison", 'criteria&target_menu=&project=ods&xmlin=drivercomparison.xml' , 0,1,0,0,1,0,"",0);
#insert into iconnex_application values ( 26, "Driver Summary", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=driversummary.xml' , 0,1,0,0,1,0,"",0);
insert into iconnex_application values ( 26, "Driver Summary", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=driversummary.xml' , 0,1,0,0,1,0,"",0);
insert into iconnex_application values ( 27, "Driver Events", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=telempaest.xml' , 1,0,0,0,0,1,"",0);
insert into iconnex_application values ( 28, "My Tracking", 'criteria&target_menu=&project=ods&xmlin=vehiclepos.xml&MANUAL_date_FROMDATE=FIRSTOFLASTWEEK&MANUAL_date_TODATE=LASTOFLASTWEEK&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59' , 1,1,0,0,0,1,"",0);
insert into iconnex_application values ( 29, "SEEDA Hotspots", 'criteria&target_menu=&project=rtimysql&xmlin=wifihotspots.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 30, "RTPI Performance", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=perfsummaryroute.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 31, "Inactive Screens", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=screensdown.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 32, "Out of Date Vehicles", 'criteriaMANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=outofdate.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 33, "Current Tracking Issues", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=trackingissues.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 34, "Timetable Monitor", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=timetablemonitor.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 35, "Early/Late Vehicles", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=laterunners.xml&MANUAL_showLateJourneys=1' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 36, "Current Trips", 'criteria&MANUAL_showCurrent=1&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rtimysql&xmlin=timetablemonitor.xml' , 0,1,0,0,1,1,"",1);
insert into iconnex_application values ( 37, "Car Parks", 'criteria&target_menu=&project=ods&xmlin=carparks.xml&target_menu=' , 1,1,0,0,0,1,"",0);
insert into iconnex_application values ( 38, "Traffic Lights", 'criteria&target_menu=&project=rtimysql&xmlin=tlprequests.xml&target_menu=' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 39, "Media Event", 'criteria&target_menu=&MANUAL_operator=&project=rtimysql&xmlin=mediaevent.xml', 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 40, "Route Lateness", 'criteria&target_menu=&project=rtimysql&xmlin=routelateness.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 41, "Time Between Points", 'criteria&target_menu=&project=rtimysql&xmlin=timebetweenpoints.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 42, "Time Between Points Banded", 'criteria&target_menu=&project=rtimysql&xmlin=timebetweenpointsbanded.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 43, "Special Operation Days", 'criteria&target_menu=&project=rtimysql&xmlin=specops.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 44, "Current Trip Report", 'criteria&target_menu=&project=rtimysql&xmlin=actrte.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 45, "Historical Trip Report", 'criteria&target_menu=&project=rtimysql&xmlin=arcrte.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 46, "Stop Messages", 'criteria&target_menu=&project=rtimysql&xmlin=stopmessages.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 47, "Login Audit Trail", 'criteria&target_menu=&project=rtimysql&xmlin=login_audit.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 48, "Login Audit Report", 'criteria&target_menu=&project=rtimysql&xmlin=login_audit.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 49, "Stop Timetable Compliance", 'criteria&target_menu=&project=rtimysql&xmlin=complianceadv.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 50, "Interval Profile Generation", 'criteria&target_menu=&project=rtimysql&xmlin=intcalc.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 51, "System Management", 'criteria&target_menu=&project=rtimysql&xmlin=managesystem.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 52, "Trip Duration Report", 'criteria&target_menu=&project=rtimysql&xmlin=triplength.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 53, "Daily Tracking Performance By Route", 'criteria&target_menu=&project=rtimysql&xmlin=performancesummaryroute2.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 54, "Tracking Performance Detail", 'criteria&target_menu=&project=rtimysql&xmlin=perfsummaryroute.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 55, "Operator Tracking Summary", 'criteria&target_menu=&project=rtimysql&xmlin=performancesummary.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 56, "Route Tracking Performance By Operator", 'criteria&target_menu=&project=rtimysql&xmlin=performancesummaryroute.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 57, "Service Status", 'criteria&target_menu=&project=apiv1_rti&xmlin=statuscolor.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 58, "Service Pattern", 'criteria&target_menu=&project=rtimysql&xmlin=servpatt.xml' , 1,1,1,1,1,0,"",0);
insert into iconnex_application values ( 59, "Random", 'criteria&target_menu=&project=ods&xmlin=randtime.xml' , 1,1,1,1,1,0,"",0);
insert into iconnex_application values ( 60, "Location List", 'criteria&target_menu=&project=rtimysql&xmlin=loclist.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 61, "Random Report", 'criteria&target_menu=&project=ods&xmlin=randtime.xml' , 0,1,0,1,1,0,"",0);

#alter table iconnex_application modify ( app_id SERIAL (29) );



insert into iconnex_menu values ( 1, "RTI Operations" );
insert into iconnex_menuitem select 1, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Despatcher Line View" ;
insert into iconnex_menuitem select 1, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Random";
insert into iconnex_menuitem select 1, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Location List";
insert into iconnex_menuitem select 1, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Service Pattern";
insert into iconnex_menuitem select 1, 5, app_id, "SIDEPANEL" from iconnex_application where app_name = "Despatcher Map View";
insert into iconnex_menuitem select 1, 6, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable Modification By Stop";
insert into iconnex_menuitem select 1, 7, app_id, "SIDEPANEL" from iconnex_application where app_name = "Vehicle Position";
insert into iconnex_menuitem select 1, 8, app_id, "SIDEPANEL" from iconnex_application where app_name = "Lateness";
insert into iconnex_menuitem select 1, 9, app_id, "SIDEPANEL" from iconnex_application where app_name = "Event Log";
insert into iconnex_menuitem select 1, 10, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable";
insert into iconnex_menuitem select 1, 11, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable Monitor";
insert into iconnex_menuitem select 1, 12, app_id, "SIDEPANEL" from iconnex_application where app_name = "Early/Late Vehicles";
insert into iconnex_menuitem select 1, 13, app_id, "SIDEPANEL" from iconnex_application where app_name = "Current Trips";
insert into iconnex_menuitem select 1, 15, app_id, "FULLSCREEN" from iconnex_application where app_name = "Current Trip Report";
insert into iconnex_menuitem select 1, 16, app_id, "FULLSCREEN" from iconnex_application where app_name = "Historical Trip Report";
insert into iconnex_menuitem select 1, 17, app_id, "SIDEPANEL" from iconnex_application where app_name = "Special Operation Days";
insert into iconnex_menuitem select 1, 18, app_id, "FULLSCREEN" from iconnex_application where app_name = "Random Report";

insert into iconnex_menu values ( 2, "Locations" );
insert into iconnex_menuitem select 2, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Stop Event Log";
insert into iconnex_menuitem select 2, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Stop Event Summary";
insert into iconnex_menuitem select 2, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Stop Timetable";
insert into iconnex_menuitem select 2, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Bus Stops";
insert into iconnex_menuitem select 2, 5, app_id, "SIDEPANEL" from iconnex_application where app_name = "Media Event";
insert into iconnex_menuitem select 2, 6, app_id, "FULLSCREEN" from iconnex_application where app_name = "Stop Messages";

insert into iconnex_menu values ( 3, "Driver" );
insert into iconnex_menuitem select 3, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driving Summary";
insert into iconnex_menuitem select 3, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driver Speeds";
insert into iconnex_menuitem select 3, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driver Economy";
insert into iconnex_menuitem select 3, 5, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driver Events";
insert into iconnex_menuitem select 3, 6, app_id, "SIDEPANEL" from iconnex_application where app_name = "My Tracking";

insert into iconnex_menu values ( 4, "Network Management" );
insert into iconnex_menuitem select 4, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Average Speeds";
insert into iconnex_menuitem select 4, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Maximum Speeds";
insert into iconnex_menuitem select 4, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Car Parks";
insert into iconnex_menuitem select 4, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Traffic Lights";
insert into iconnex_menuitem select 4, 5, app_id, "SIDEPANEL" from iconnex_application where app_name = "SEEDA Hotspots";

insert into iconnex_menu values ( 5, "Telematics" );
insert into iconnex_menuitem select 5, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Telematics Details";
insert into iconnex_menuitem select 5, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driving Events";
insert into iconnex_menuitem select 5, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driver Summary";
insert into iconnex_menuitem select 5, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driver Comparison";

insert into iconnex_menu values ( 6, "System Performance" );
insert into iconnex_menuitem select 6, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Compliance Table";
insert into iconnex_menuitem select 6, 2, app_id, "FULLSCREEN" from iconnex_application where app_name = "Route Lateness";
insert into iconnex_menuitem select 6, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "RTPI Performance";
insert into iconnex_menuitem select 6, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Inactive Screens";
insert into iconnex_menuitem select 6, 5, app_id, "FULLSCREEN" from iconnex_application where app_name = "Out of Date Vehicles";
insert into iconnex_menuitem select 6, 6, app_id, "FULLSCREEN" from iconnex_application where app_name = "Current Tracking Issues";
insert into iconnex_menuitem select 6, 7, app_id, "FULLSCREEN" from iconnex_application where app_name = "Time Between Points";
insert into iconnex_menuitem select 6, 8, app_id, "FULLSCREEN" from iconnex_application where app_name = "Time Between Points Banded";
insert into iconnex_menuitem select 6, 9, app_id, "FULLSCREEN" from iconnex_application where app_name = "Stop Timetable Compliance";
insert into iconnex_menuitem select 6, 10, app_id, "FULLSCREEN" from iconnex_application where app_name = "Interval Profile Generation";
insert into iconnex_menuitem select 6, 11, app_id, "FULLSCREEN" from iconnex_application where app_name = "Trip Duration Report";
insert into iconnex_menuitem select 6, 12, app_id, "FULLSCREEN" from iconnex_application where app_name = "Daily Tracking Performance By Route";
insert into iconnex_menuitem select 6, 13, app_id, "FULLSCREEN" from iconnex_application where app_name = "Tracking Performance Detail";
insert into iconnex_menuitem select 6, 14, app_id, "FULLSCREEN" from iconnex_application where app_name = "Operator Tracking Summary";
insert into iconnex_menuitem select 6, 15, app_id, "FULLSCREEN" from iconnex_application where app_name = "Route Tracking Performance By Operator";

insert into iconnex_menu values ( 7, "System Maintenance" );
insert into iconnex_menuitem select 7, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Inventory Items";
insert into iconnex_menuitem select 7, 2, app_id, "FULLSCREEN" from iconnex_application where app_name = "Login Audit Report";
insert into iconnex_menuitem select 7, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Login Audit Trail";
insert into iconnex_menuitem select 7, 4, app_id, "FULLSCREEN" from iconnex_application where app_name = "System Management";

insert into iconnex_menu values ( 8, "Performance Dashboard" );
insert into iconnex_menuitem select 8, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "RTPI Performance";
insert into iconnex_menuitem select 8, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Inactive Screens";
insert into iconnex_menuitem select 8, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Out of Date Vehicles";
insert into iconnex_menuitem select 8, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Compliance Table";

insert into iconnex_menu values ( 9, "RTI Operations" );
insert into iconnex_menuitem select 9, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Despatcher Map View";
insert into iconnex_menuitem select 9, 2, app_id, "FULLSCREEN" from iconnex_application where app_name = "Current Trip Report";
insert into iconnex_menuitem select 9, 3, app_id, "FULLSCREEN" from iconnex_application where app_name = "Historical Trip Report";


# Admin Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 2, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 3, 0, 1, 0 FROM cent_user where usernm = "1admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 1, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 4, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 5, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 6, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 7, 0, 1, 0 FROM cent_user where usernm = "admin" ;

# Bus Operator Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 1, 0, 1, 0 FROM cent_user where usernm = "rgbdesp" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 6, 0, 1, 0 FROM cent_user where usernm = "rgbdesp" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 5, 0, 1, 0 FROM cent_user where usernm = "rgbdesp" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 8, 0, 1, 0 FROM cent_user where usernm = "rgbdesp" ;

# Authority Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 2, 0, 1, 0 FROM cent_user where usernm in ( "rbc", "swise", "jhall", "mallen" ) ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 4, 0, 1, 0 FROM cent_user where usernm in ( "rbc" , "swise", "jhall", "mallen" );
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 9, 0, 1, 0 FROM cent_user where usernm in ( "rbc" , "swise", "jhall", "mallen" );

insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 9, 0, 1, 0 FROM cent_user where usernm = "weaway" ;

insert into iconnex_menu_user  ( user_id, menu_id, autorun, show_accordion, show_buttons )
SELECT userid, 3, 1, 0, 1 FROM cent_user where userid >= 55 and usernm not in ("rgbdesp", "weaway", "rbc", "admin");
#insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 3, 1, 0, 1 FROM cent_user where usernm = "684376" ;







-- Identifies a collection of dashboards, reports etc that can be bulk loaded
create table iconnex_workspace 
(
	workspace_id	SERIAL NOT NULL,
	user_id INTEGER,
	workspace_name	CHAR(40),
    primary key (workspace_id) 
);

#alter table iconnex_workspace add constraint (foreign key 
    #(user_id) references cent_user);

# A report/dashboard that is part of a users workspace
create table iconnex_workspace_item 
(
	workspace_item_id	SERIAL NOT NULL,
	workspace_id	INTEGER NOT NULL,
	workspace_item_no	INTEGER NOT NULL,
	workspace_menu_item	CHAR(50),
	refreshing  INTEGER,
	height  INTEGER,
	width  INTEGER,
	x  INTEGER,
	y  INTEGER,
    primary key (workspace_item_id) 
);

# A report/dashboard that is part of a users workspace
#alter table iconnex_workspace_item add constraint (foreign key 
    #(workspace_id) references iconnex_workspace);

# A workspace parameter
create table iconnex_workspace_parameter 
(
	workspace_id	    INTEGER NOT NULL,
	workspace_item_id	INTEGER NOT NULL,
	session_parameter	VARCHAR(255),
	session_param_value	VARCHAR(255),
    primary key (workspace_item_id,session_parameter) 
);

#alter table iconnex_workspace_parameter add constraint (foreign key 
    #(workspace_item_id) references iconnex_workspace_item);
