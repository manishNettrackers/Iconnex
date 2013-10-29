

delete from cent_user where usernm = "rbc";
insert into cent_user values ( 0, "rbc", "Reading BC", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "rgbdesp";
delete from cent_user where usernm = "rgb";
insert into cent_user values ( 0, "rgb", "Reading Buses", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "rgbdesp";
insert into cent_user values ( 0, "mallen", "Marc Allen", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "swise";
insert into cent_user values ( 0, "swise", "swise", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "jhall";
insert into cent_user values ( 0, "jhall", "jhall", NULL, "", "", NULL, NULL, "en_gb", "MASTER");
delete from cent_user where usernm = "weaway";
insert into cent_user values ( 0, "weaway", "weaway", NULL, "", "", NULL, NULL, "en_gb", "MASTER");

drop table iconnex_menu;
drop table iconnex_param;
drop table iconnex_menuitem;
drop table iconnex_application;
drop table iconnex_menu_user;
drop table iconnex_workspace;
drop table iconnex_wsp_item ;

create table "dbmaster".iconnex_menu
(
	menu_id	SERIAL,
	menu_name	CHAR(30) NOT NULL,
    primary key (menu_id) 
);
grant select on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menu to "centrole" as "dbmaster";

create table "dbmaster".iconnex_param
(
	param_name	CHAR(30) NOT NULL,
	param_value CHAR(30) NOT NULL,
    primary key (param_name) 
);
grant select on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_param to "centrole" as "dbmaster";

create table "dbmaster".iconnex_application
(
	app_id	SERIAL,
	app_name CHAR(60) NOT NULL,
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
grant select on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_application to "centrole" as "dbmaster";

create table "dbmaster".iconnex_menuitem
(
	menu_id	INTEGER,
	menu_no	INTEGER NOT NULL,
	app_id INTEGER NOT NULL ,
    run_location CHAR(10),
    primary key (menu_id, menu_no) 
);
grant select on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";

alter table "dbmaster".iconnex_menuitem add constraint (foreign key 
    (app_id) references "dbmaster".iconnex_application);
alter table "dbmaster".iconnex_menuitem add constraint (foreign key 
    (menu_id) references "dbmaster".iconnex_menu);

create table "dbmaster".iconnex_menu_user
(
	user_id INTEGER NOT NULL,
	menu_id	INTEGER,
	app_id	INTEGER,
    autorun INTEGER,
    show_accordion INTEGER,
    show_buttons INTEGER
);
grant select on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";

alter table "dbmaster".iconnex_menu_user add constraint (foreign key 
    (user_id) references "dbmaster".cent_user);

create table "dbmaster".iconnex_workspace 
(
	wsp_id	SERIAL NOT NULL,
	user_id INTEGER,
	wsp_name	CHAR(40),
    primary key (wsp_id) 
);
grant select on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";

alter table "dbmaster".iconnex_workspace add constraint (foreign key 
    (user_id) references "dbmaster".cent_user);

create table "dbmaster".iconnex_wsp_item 
(
	wsp_id	INTEGER NOT NULL,
	wsp_item_no	INTEGER NOT NULL,
	session_params VARCHAR(255),
    primary key (wsp_id,wsp_item_no) 
);
grant select on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";

alter table "dbmaster".iconnex_wsp_item add constraint (foreign key 
    (wsp_id) references "dbmaster".iconnex_workspace);


insert into iconnex_application values ( 1, "Timetable Modification By Stop", 'criteria&target_menu=&project=rti&xmlin=tripcancelbystop.xml', 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 2, "Average Speeds", 'criteria&target_menu=&project=ods&xmlin=avgspeedfilter.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 3, "Maximum Speeds", 'criteria&target_menu=&project=ods&xmlin=maxspeed.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 4, "Services", 'criteria&target_menu=&project=rti&xmlin=services.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 5, "Vehicle Position", 'criteria&target_menu=&project=ods&xmlin=vehiclepos.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 6, "Telematics Details", 'criteria&target_menu=&project=ods&xmlin=telemdetail.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 7, "Driving Events", 'criteria&target_menu=&project=ods&xmlin=telempaest.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 8, "Lateness", 'criteria&target_menu=&project=rti&xmlin=lateness.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 9, "Stop Event Log", 'criteria&target_menu=&project=rti&xmlin=stopevent.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 10, "Stop Event Summary", 'criteria&target_menu=&project=rti&xmlin=stopeventlast.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 11, "Event Log", 'criteria&target_menu=&project=rti&xmlin=eventlog.xml' , 1,1,0,1,1,0,"",0);
insert into iconnex_application values ( 12, "Despatcher Map Veh", 'criteria&target_menu=&project=rti&xmlin=despatcher.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 13, "Despatcher Line View", 'criteria&target_menu=&project=rti&xmlin=lineviewlocs.xml' , 0,0,1,0,0,0,"despatcherline.xml",1);
insert into iconnex_application values ( 14, "Despatcher Map View", 'criteria&target_menu=&project=rti&xmlin=despatcherstops.xml' , 1,0,0,0,0,0,"despatcher.xml",1);
insert into iconnex_application values ( 15, "Line View Vehicles", 'criteria&target_menu=&project=rti&xmlin=despatcherline.xml' , 0,0,1,0,0,0,"",0);
insert into iconnex_application values ( 16, "Timetable", 'criteria&target_menu=&project=rti&xmlin=timetab1.xml' , 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 17, "Stop Timetable", 'criteria&target_menu=&project=rti&xmlin=stoparrperf.xml' , 0,1,0,0,1,0,"",0);
--insert into iconnex_application values ( 18, "Bus Stops", 'criteria&target_menu=&project=rti&xmlin=displaypoint.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 18, "Bus Stops", 'criteria&target_menu=&MANUAL_operator=&project=rti&xmlin=locations.xml', 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 19, "Inventory Items", 'criteria&target_menu=&project=rti&xmlin=unitbuilds.xml' , 0,1,0,0,0,0,"",0);
insert into iconnex_application values ( 20, "Compliance Table", 'criteria&target_menu=&project=rti&xmlin=compliancetable.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 21, "Driving Summary", 'criteria&target_menu=&project=ods&xmlin=drivingsummary.xml' , 0,1,0,0,1,1,"",0);
insert into iconnex_application values ( 22, "Driver Speeds", 'criteria&target_menu=&MANUAL_results=maxspeed&project=ods&xmlin=telemdetail.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 23, "Driver Economy", 'criteria&target_menu=&MANUAL_results=economy&project=ods&xmlin=telemdetail.xml' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 24, "Service Maps", 'criteria&target_menu=&project=rti&xmlin=services.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 25, "Driver Comparison", 'criteria&target_menu=&project=ods&xmlin=drivercomparison.xml' , 0,1,0,0,1,0,"",0);
--insert into iconnex_application values ( 26, "Driver Summary", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=driversummary.xml' , 0,1,0,0,1,0,"",0);
insert into iconnex_application values ( 26, "Driver Summary", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=driversummary.xml' , 0,1,0,0,1,0,"",0);
insert into iconnex_application values ( 27, "Driver Events", 'criteria&MANUAL_date_FROMDATE=FIRSTOFLASTMONTH&MANUAL_date_TODATE=LASTOFMONTH&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59&project=ods&xmlin=telempaest.xml' , 1,0,0,0,0,1,"",0);
insert into iconnex_application values ( 28, "My Tracking", 'criteria&target_menu=&project=ods&xmlin=vehiclepos.xml&MANUAL_date_FROMDATE=FIRSTOFLASTWEEK&MANUAL_date_TODATE=LASTOFLASTWEEK&target_menu=&MANUAL_driver=<USER>&MANUAL_timefrom=00:00:00&MANUAL_timeto=23:59:59' , 1,1,0,0,0,1,"",0);
insert into iconnex_application values ( 29, "SEEDA Hotspots", 'criteria&target_menu=&project=rti&xmlin=wifihotspots.xml' , 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 30, "RTPI Performance", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=perfsummaryroute.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 31, "Inactive Screens", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=screensdown.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 32, "Out of Date Vehicles", 'criteriaMANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=outofdate.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 33, "Current Tracking Issues", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=trackingissues.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 34, "Timetable Monitor", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=timetablemonitor.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 35, "Early/Late Vehicles", 'criteria&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=laterunners.xml&MANUAL_showLateJourneys=1' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 36, "Current Trips", 'criteria&MANUAL_showCurrent=1&MANUAL_date_FROMDATE=TODAY&MANUAL_date_TODATE=TODAY&target_menu=&project=rti&xmlin=timetablemonitor.xml' , 0,1,0,0,1,1,"",1);
insert into iconnex_application values ( 37, "Car Parks", 'criteria&target_menu=&project=ods&xmlin=parking.xml&target_menu=' , 1,1,0,0,0,1,"",0);
insert into iconnex_application values ( 38, "Traffic Lights", 'criteria&target_menu=&project=rti&xmlin=tlprequests.xml&target_menu=' , 1,1,0,0,0,0,"",0);
insert into iconnex_application values ( 39, "Media Event", 'criteria&target_menu=&MANUAL_operator=&project=rti&xmlin=mediaevent.xml', 1,0,0,0,0,0,"",0);
insert into iconnex_application values ( 40, "Route Lateness", 'criteria&target_menu=&project=rti&xmlin=routelateness.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 41, "Time Between Points", 'criteria&target_menu=&project=rti&xmlin=timebetweenpoints.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 42, "Time Between Points Banded", 'criteria&target_menu=&project=rti&xmlin=timebetweenpointsbanded.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 43, "Special Operation Days", 'criteria&target_menu=&project=rti&xmlin=specops.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 44, "Current Trip Report", 'criteria&target_menu=&project=rti&xmlin=actrte.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 45, "Historical Trip Report", 'criteria&target_menu=&project=rti&xmlin=arcrte.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 46, "Stop Messages", 'criteria&target_menu=&project=rti&xmlin=stopmessages.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 47, "Login Audit Trail", 'criteria&target_menu=&project=rti&xmlin=login_audit.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 48, "Login Audit Report", 'criteria&target_menu=&project=rti&xmlin=login_audit.xml' , 0,1,0,1,1,0,"",0);
insert into iconnex_application values ( 49, "Stop Timetable Compliance", 'criteria&target_menu=&project=rti&xmlin=complianceadv.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 50, "Interval Profile Generation", 'criteria&target_menu=&project=rti&xmlin=intcalc.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 51, "System Management", 'criteria&target_menu=&project=rti&xmlin=managesystem.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 52, "Trip Duration Report", 'criteria&target_menu=&project=rti&xmlin=triplength.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 53, "Daily Tracking Performance By Route", 'criteria&target_menu=&project=rti&xmlin=performancesummaryroute2.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 54, "Tracking Performance Detail", 'criteria&target_menu=&project=rti&xmlin=perfsummaryroute.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 55, "Operator Tracking Summary", 'criteria&target_menu=&project=rti&xmlin=performancesummary.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 56, "Route Tracking Performance By Operator", 'criteria&target_menu=&project=rti&xmlin=performancesummaryroute.xml' , 0,0,0,1,1,0,"",0);
insert into iconnex_application values ( 57, "Service Status", 'criteria&target_menu=&project=apiv1_rti&xmlin=statuscolor.xml' , 0,1,0,1,1,1,"",1);
insert into iconnex_application values ( 58, "Variable Message Signs", 'criteria&target_menu=&project=ods&xmlin=vms.xml&target_menu=' , 1,1,0,0,0,1,"",0);

alter table iconnex_application modify ( app_id SERIAL (29) );


insert into iconnex_menu values ( 1, "RTI Operations" );
insert into iconnex_menuitem select 1, 1, app_id, "SIDEPANEL" from iconnex_application where app_name = "Despatcher Line View" ;
--insert into iconnex_menuitem select 1, 2, app_id, "SIDEPANEL" from iconnex_application where app_name = "Line View Vehicles";
insert into iconnex_menuitem select 1, 3, app_id, "SIDEPANEL" from iconnex_application where app_name = "Despatcher Map View";
insert into iconnex_menuitem select 1, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable Modification By Stop";
insert into iconnex_menuitem select 1, 5, app_id, "SIDEPANEL" from iconnex_application where app_name = "Vehicle Position";
insert into iconnex_menuitem select 1, 6, app_id, "SIDEPANEL" from iconnex_application where app_name = "Lateness";
insert into iconnex_menuitem select 1, 7, app_id, "SIDEPANEL" from iconnex_application where app_name = "Event Log";
insert into iconnex_menuitem select 1, 8, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable";
insert into iconnex_menuitem select 1, 9, app_id, "SIDEPANEL" from iconnex_application where app_name = "Timetable Monitor";
insert into iconnex_menuitem select 1, 10, app_id, "SIDEPANEL" from iconnex_application where app_name = "Early/Late Vehicles";
insert into iconnex_menuitem select 1, 11, app_id, "SIDEPANEL" from iconnex_application where app_name = "Current Trips";
insert into iconnex_menuitem select 1, 12, app_id, "SIDEPANEL" from iconnex_application where app_name = "Stop Timetable";
insert into iconnex_menuitem select 1, 13, app_id, "FULLSCREEN" from iconnex_application where app_name = "Current Trip Report";
insert into iconnex_menuitem select 1, 14, app_id, "FULLSCREEN" from iconnex_application where app_name = "Historical Trip Report";
insert into iconnex_menuitem select 1, 15, app_id, "SIDEPANEL" from iconnex_application where app_name = "Special Operation Days";
insert into iconnex_menuitem select 1, 16, app_id, "SIDEPANEL" from iconnex_application where app_name = "Car Parks";
insert into iconnex_menuitem select 1, 17, app_id, "SIDEPANEL" from iconnex_application where app_name = "Variable Message Signs";

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
insert into iconnex_menuitem select 3, 4, app_id, "SIDEPANEL" from iconnex_application where app_name = "Driving Summary";
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


alter table iconnex_menu modify ( menu_id SERIAL (5) );

-- Admin Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 2, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 3, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 1, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 4, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 5, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 6, 0, 1, 0 FROM cent_user where usernm = "admin" ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 7, 0, 1, 0 FROM cent_user where usernm = "admin" ;

-- Bus Operator Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 1, 0, 1, 0 FROM cent_user where usernm in ( "first", "weaway", "rgb" );
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 6, 0, 1, 0 FROM cent_user where usernm in ( "first", "weaway", "rgb" );
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 5, 0, 1, 0 FROM cent_user where usernm in ( "first", "weaway", "rgb" );
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 8, 0, 1, 0 FROM cent_user where usernm in ( "first", "weaway", "rgb" );

-- Authority Menus
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 2, 0, 1, 0 FROM cent_user where usernm in ( "rbc", "swise", "jhall", "mallen" ) ;
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 4, 0, 1, 0 FROM cent_user where usernm in ( "rbc" , "swise", "jhall", "mallen" );
insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 9, 0, 1, 0 FROM cent_user where usernm in ( "rbc" , "swise", "jhall", "mallen" );

insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 9, 0, 1, 0 FROM cent_user where usernm = "weaway" ;

insert into iconnex_menu_user  ( user_id, menu_id, autorun, show_accordion, show_buttons )
SELECT userid, 3, 1, 0, 1 FROM cent_user where userid >= 55 and usernm not in ("rgbdesp", "weaway", "rbc", "admin");
--insert into iconnex_menu_user ( user_id, menu_id, autorun, show_accordion, show_buttons) SELECT userid, 3, 1, 0, 1 FROM cent_user where usernm = "684376" ;

