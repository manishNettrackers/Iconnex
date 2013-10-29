<?php

// Work out whether to recreate tables
$val = $_criteria["recreate_tables"]->get_criteria_value("VALUE");

$stat = true;

// Perform table recreation if necessary
if ( $val == "'D'" || $val == "'R'")
{

if ( $stat || true ) { $sql = "DROP TABLE driver_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE vehicle_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE date_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE time_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE gis_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE trip_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE gps_fact"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE event_dimension"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE user_vehicle"; $stat = $_connection->Execute($sql);}
if ( $stat || true ) { $sql = "DROP TABLE cent_user"; $stat = $_connection->Execute($sql);}

}

if ( $val == "'C'" || $val == "'R'")
{
$stat = true;

if ( $stat )
{
  $sql = "
  CREATE TABLE `event_dimension` (
  event_id INTEGER AUTO_INCREMENT,
  event_description CHAR(20),
  PRIMARY KEY  (`event_id`)
)"; 
$stat = $_connection->Execute($sql);

}
if ( $stat )
{
    $sql = "CREATE INDEX ix_evt_dim_veh ON event_dimension ( event_id )"; 
    $stat = $_connection->Execute($sql);
}



if ( $stat ) 
{ 
  $sql = "
  CREATE TABLE `vehicle_dimension` (
  vehicle_id INTEGER AUTO_INCREMENT,
  system_code CHAR(20),
  operator_code CHAR(8),
  inventory_code CHAR(20),
  vehicle_code CHAR(20),
  vehicle_reg CHAR(20),
  wheelchair_flag INTEGER,
  PRIMARY KEY  (`vehicle_id`)
)"; $stat = $_connection->Execute($sql);

}
if ( $stat ) 
{ 
    $sql = "CREATE INDEX ix_veh_dim_veh ON vehicle_dimension ( vehicle_code )"; $stat = $_connection->Execute($sql);
    $sql = "CREATE INDEX ix_veh_dim_id ON vehicle_dimension ( vehicle_id )"; $stat = $_connection->Execute($sql);
}

if ( $stat ) 
{ 
  $sql = "
  CREATE TABLE `date_dimension` (
  date_id INTEGER,
  dmy CHAR(10),
  ymd CHAR(10),
  year INTEGER,
  month_no INTEGER,
  month_name CHAR(10),
  month_short CHAR(3),
  dow_no INTEGER,
  dow_name CHAR(10),
  day_no INTEGER,
  PRIMARY KEY  (`date_id`)
)"; $stat = $_connection->Execute($sql);
}
if ( $stat ) { $sql = "CREATE INDEX ix_date_dim_month_no ON date_dimension ( month_no )"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_date_dim_year ON date_dimension ( year )"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_date_dim_id ON date_dimension ( date_id )"; $stat = $_connection->Execute($sql); }

if ( $stat ) 
{ 
  $sql = "
  CREATE TABLE `time_dimension` (
  time_id INTEGER,
  hhmmss CHAR(8),
  hour_no INTEGER,
  minute_no INTEGER,
  second_no INTEGER,
  PRIMARY KEY  (`time_id`)
)"; $stat = $_connection->Execute($sql);
}


if ( $stat ) 
{ 
    $sql = "CREATE INDEX ix_time_dim_hr ON time_dimension ( hour_no )"; $stat = $_connection->Execute($sql);
    $sql = "CREATE INDEX ix_time_dim_id ON time_dimension ( time_id )"; $stat = $_connection->Execute($sql);

}
if ( $stat ) 
{ 
  $sql = "
  CREATE TABLE `gis_dimension` (
  gis_id INTEGER AUTO_INCREMENT,
  geohash CHAR(20),
  geohash2 CHAR(20),
  osm_place_id INTEGER,
  latitude DECIMAL(12,5),
  longitude DECIMAL(12,5),
  addr_road VARCHAR(30),
  addr_suburb VARCHAR(30),
  addr_city VARCHAR(30),
  addr_country VARCHAR(30),
  addr_county VARCHAR(30),
  addr_postcode VARCHAR(30),
  PRIMARY KEY  (`gis_id`)
)"; $stat = $_connection->Execute($sql);

}
  //latlong GEOMETRY NOT NULL,

if ( $stat ) 
{ 
    $sql = "CREATE UNIQUE INDEX ix_gis_dim_hash ON gis_dimension ( geohash)"; $stat = $_connection->Execute($sql);
    $sql = "CREATE UNIQUE INDEX ix_gis_dim_id ON gis_dimension ( gis_id)"; $stat = $_connection->Execute($sql);

}

if ( $stat ) 
{ 
  $sql = "
CREATE TABLE `driver_dimension` 
(   
  `driver_id` int(11) NOT NULL auto_increment,
  `system_code` char(20) default NULL,
  `operator_code` char(8) default NULL,
  `employee_code` char(8) default NULL,
  `fullname` char(30) default NULL,
  PRIMARY KEY  (`driver_id`)
) 
"; $stat = $_connection->Execute($sql);

}
if ( $stat ) 
{ 
  $sql = "
CREATE TABLE `trip_dimension` 
(   
    `trip_id` int(11) NOT NULL auto_increment,
   `vehicle_id` int(11) default NULL,
   `driver_id` int(11) default NULL,
   `system_code` char(20) default NULL,
   `route_code` char(8) default NULL,
   `trip_no` char(9) default NULL,
   `duty_no` char(10) default NULL,
   `running_no` char(9) default NULL,
   `actual_start` datetime default NULL,
   `start_day` smallint(6) default NULL,
   `actual_end` datetime default NULL,
   PRIMARY KEY  (`trip_id`),
   KEY `vehicle_id` (`vehicle_id`) 
) 
"; $stat = $_connection->Execute($sql);

}
  //latlong GEOMETRY NOT NULL,


if ( $stat ) 
{ 
    $sql = "CREATE INDEX ix_trp_dim_route ON trip_dimension ( route_code )"; $stat = $_connection->Execute($sql);
    $sql = "CREATE INDEX ix_trp_dim_running ON trip_dimension ( running_no )"; $stat = $_connection->Execute($sql);
    $sql = "CREATE INDEX ix_trp_dim_driver ON trip_dimension ( driver_id )"; $stat = $_connection->Execute($sql);

}


if ( $stat ) 
{ 
  $sql = "
  CREATE TABLE `gps_fact` (
  fact_id INTEGER AUTO_INCREMENT,
  sourcefile CHAR(20),
  event_id INTEGER,
  gis_id INTEGER,
  vehicle_id INTEGER NOT NULL,
  driver_id INTEGER NOT NULL,
  trip_id INTEGER,
  date_id INTEGER,
  time_id INTEGER,
  speed_mph DECIMAL(7,2),
  bearing DECIMAL(7,2),
  PRIMARY KEY  (`fact_id`)
)"; 
	$stat = $_connection->Execute($sql);

}

if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_gis_id ON gps_fact ( gis_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_veh_id ON gps_fact ( vehicle_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_drv_id ON gps_fact ( driver_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_trp_id ON gps_fact ( trip_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_dt_id ON gps_fact ( date_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_tm_id ON gps_fact ( time_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_fct_ev_id ON gps_fact ( event_id)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_src ON gps_fact ( sourcefile)"; $stat = $_connection->Execute($sql); }
if ( $stat ) { $sql = "CREATE INDEX ix_gps_src ON gps_fact ( sourcefile)"; $stat = $_connection->Execute($sql); }

if ( $stat ) 
{ 
  $sql = "
create table user_vehicle 
(
    userid integer not null ,
    operator_id integer,
    vehicle_id integer
)"; 
$stat = $_connection->Execute($sql);
}

if ( $stat ) { $sql = "CREATE INDEX ix_user_veh ON user_vehicle ( userid)"; $stat = $_connection->Execute($sql); }

if ( $stat ) 
{ 
  $sql = "
create table cent_user 
(
    userid integer auto_increment ,
    usernm char(15),
    narrtv char(20) not null ,
    operator_id integer,
    passwd char(10),
    passwd_md5 char(40),
    emailad char(30),
    maxsess int,
    langcd char(5),
    menucd char(6),
    primary key ( userid)
)"; 
$stat = $_connection->Execute($sql);
}

if ( $stat ) { $sql = "CREATE INDEX ix_cent_user ON cent_user ( userid)"; $stat = $_connection->Execute($sql); }

if ( $stat ) 
{ 
  $sql = "
create view vehicle_visibility (usernm,userid,vehicle_id,vehicle_code,vehicle_reg) as
select usernm ,cent_user.userid ,vehicle_dimension.vehicle_id ,vehicle_code, vehicle_reg
from user_vehicle,cent_user, vehicle_dimension
where user_vehicle.userid = cent_user.userid
and vehicle_dimension.vehicle_id = user_vehicle.vehicle_id
)"; 
$stat = $_connection->Execute($sql);
}

}


if ( !$stat )
{
    trigger_error("Query Failed<BR>".$sql."<br>" . 
         "Error ".$_connection->ErrorNo(). " - ". 
         $_connection->ErrorMsg(), E_USER_ERROR); 
}
else
{
    handle_debug("ODS database was successfully created", SW_DEBUG_NONE);

}


?>
