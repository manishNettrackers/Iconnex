drop database infohost;
create  database infohost;
use infohost;

create table cent_user 
  (
    userid int not null ,
    usernm char(15),
    narrtv char(20) not null ,
    operator_id int,
    passwd char(10),
    passwd_md5 char(40),
    emailad char(30),
    maxsess int ,
    langcd char(5) not null ,
    menucd char(6) not null 
  );


create unique index u_cent_user_2 on cent_user 
    (usernm) ;



create table destination 
  (
    dest_id int not null ,
    operator_id int not null ,
    dest_code char(10) not null ,
    dest_long char(50),
    dest_short1 char(20),
    terminal_text char(20),
    display_text char(20)
  );


create index f_destination_2 on destination 
    (operator_id) ;
create unique index i_destination_3 on destination 
    (operator_id,dest_id) ;
create unique index i_destination_4 on destination 
    (operator_id,dest_code) ;
create unique index p_destination_1 on destination 
    (dest_id) ;
##alter table destination add constraint primary key 
#    (dest_id) constraint pk_destination  ;


##alter table destination add constraint (foreign key 
#    (operator_id) references operator  constraint 
#f_destination_a);



create table employee 
  (
    operator_id int,
    employee_id int not null ,
    employee_code char(8) not null ,
    fullname char(30) not null ,
    orun_code char(10) 
        default 'Unknown'
  );


create index f_employee_2 on employee (operator_id) 
    ;
create unique index i_employee_3 on employee 
    (operator_id,employee_code) ;
create unique index p_employee_1 on employee 
    (employee_id) ;
##alter table employee add constraint primary key (employee_id) 
##constraint pk_employee  ;



create table event 
  (
    operator_id int not null ,
    event_id int not null ,
    event_code char(8),
    event_desc char(30),
    event_tp char(1),
    spdt_start date,
    spdt_end date,
    rpdt_start char(5),
    rpdt_end char(5),
    rpdy_start smallint,
    rpdy_end smallint
  );


create index f_event_2 on event (operator_id) 
    ;
create unique index i_event_3 on event (event_id) 
    ;
create unique index p_event_1 on event (operator_id,
    event_id) ;
##alter table event add constraint primary key (operator_id,
#    event_id) constraint pk_event  ;


##alter table event add constraint (foreign key (operator_id) 
##references operator  constraint f_event_a);
    



create table event_pattern 
  (
    evprf_id int not null ,
    event_id int not null ,
    operational char(1) not null 
  );


create index f_event_pattern_2 on event_pattern 
    (evprf_id) ;
create unique index p_event_pattern_1 on 
event_pattern (evprf_id,event_id) ;
##alter table event_pattern add constraint primary key 
#    (evprf_id,event_id) constraint pk_event_pattern 
     ;


##alter table event_pattern add constraint (foreign 
#    key (evprf_id) references event_profile  constraint 
#    f_event_pattern_a);



create table event_profile 
  (
    evprf_id int not null 
  );


create unique index p_event_profile_1 on 
event_profile (evprf_id) ;
##alter table event_profile add constraint primary key 
#    (evprf_id) constraint pk_event_profile  ;



create table location 
  (
    location_id int not null ,
    location_code char(12) not null ,
    gprs_xmit_code smallint,
    point_type char(1) not null ,
    route_area_id int not null ,
    description char(40) not null ,
    public_name char(50),
    receive smallint,
    latitude_degrees smallint,
    latitude_minutes decimal(8,4),
    latitude_heading char(1),
    longitude_degrees smallint,
    longitude_minutes decimal(8,4),
    longitude_heading char(1),
    geofence_radius decimal(10,2),
    pass_angle smallint,
    gazetteer_code char(1),
    gazetteer_id char(8),
    place_id int,
    district_id int,
    arriving_addon int,
    exit_addon int,
    bay_no char(8)
    
    
  );


create index f_location_2 on location (district_id) 
    ;
create index f_location_3 on location (point_type) 
    ;
create index f_location_4 on location (route_area_id) 
    ;
create index f_location_5 on location (place_id) 
    ;
create unique index i_location_7 on location 
    (location_code) ;
create unique index p_location_1 on location 
    (location_id) ;
##alter table location add constraint primary key (location_id) 
##constraint pk_location  ;


##alter table location add constraint (foreign key (district_id) 
##references district  constraint f_location_a);
    
##alter table location add constraint (foreign key (point_type) 
##references location_type  constraint f_location_b);
    
##alter table location add constraint (foreign key (route_area_id) 
##references route_area  constraint f_location_c);
    
##alter table location add constraint (foreign key (place_id) 
##references place  constraint f_location_d);
    



create table operator 
  (
    operator_id int not null ,
    operator_code char(8) not null ,
    legal_name char(48),
    address01 char(20),
    address02 char(20),
    address03 char(20),
    address04 char(20),
    short_name char(24),
    loc_prefix char(3) not null ,
    tel_travel char(12),
    tel_enquiry char(12)
  );


create unique index i_operator_3 on operator 
    (loc_prefix) ;
create unique index p_operator_1 on operator 
    (operator_id) ;
create unique index u_operator_2 on operator 
    (operator_code) ;
##alter table operator add constraint primary key (operator_id) 
##constraint pk_operator  ;



create table publish_tt 
  (
    pub_ttb_id int not null ,
    service_id int not null ,
    trip_no char(10),
    runningno char(5),
    duty_no char(6),
    orun_code char(10),
    direction smallint,
    pub_prof_id int not null ,
    rtpi_prof_id int not null ,
    start_time char(8),
    vehicle_type_id int not null ,
    evprf_id int not null ,
    notes char(72)
  );


create index f_publish_tt_2 on publish_tt 
    (evprf_id) ;
create index f_publish_tt_3 on publish_tt 
    (service_id) ;
create index f_publish_tt_4 on publish_tt 
    (pub_prof_id) ;
create index f_publish_tt_5 on publish_tt 
    (rtpi_prof_id) ;
create unique index i_publish_tt_6 on publish_tt 
    (service_id,trip_no,start_time,evprf_id) ;
create unique index p_publish_tt_1 on publish_tt 
    (pub_ttb_id) ;
##alter table publish_tt add constraint primary key 
#    (pub_ttb_id) constraint pk_publish_tt  ;


##alter table publish_tt add constraint (foreign key 
#    (evprf_id) references event_profile  constraint 
#    f_publish_tt_a);
##alter table publish_tt add constraint (foreign key 
#    (service_id) references service  constraint 
#f_publish_tt_b);
##alter table publish_tt add constraint (foreign key 
#    (pub_prof_id) references route_profile  constraint 
#    f_publish_tt_c);
##alter table publish_tt add constraint (foreign key 
#    (rtpi_prof_id) references route_profile  constraint 
#    f_publish_tt_d);



create table route 
  (
    route_id int not null ,
    route_code char(8) not null ,
    operator_id int not null ,
    description char(30),
    outbound_desc char(40),
    inbound_desc char(40)
  );


create index f_route_2 on route (operator_id) 
    ;
create unique index i_route_3 on route (operator_id,
    route_code) ;
create unique index p_route_1 on route (route_id) 
    ;


##alter table route add constraint (foreign key (operator_id) 
##references operator  constraint f_route_a);
    



create table route_area 
  (
    route_area_id int not null ,
    route_area_code char(20) not null ,
    description char(40) not null 
  );


create unique index p_route_area_1 on route_area 
    (route_area_id) ;
##alter table route_area add constraint primary key 
#    (route_area_id) constraint pk_route_area  ;



create table route_param 
  (
    route_id int not null ,
    lookahead smallint,
    late_thresh_low smallint,
    late_thresh_high smallint,
    autolate_kickin smallint,
    autolate_freq smallint,
    autolate_addon smallint,
    radio_trip_ceiling smallint,
    radio_stop_ceiling smallint,
    default_image int,
    default_player int,
    trip_start_low smallint,
    trip_start_high smallint,
    trip_corr_low smallint,
    trip_corr_high smallint,
    trip_continuation smallint,
    route_matching char(15) not null ,
    synchro_first smallint,
    synchro_loc smallint,
    vecom_line smallint,
    combo_line char(1),
    pub_status char(1) not null ,
    rtpi_status char(1) not null 
  );


create index f_route_param_2 on route_param 
    (default_player) ;
create index f_route_param_3 on route_param 
    (default_image) ;
create index f_route_param_4 on route_param 
    (route_id) ;



create table route_patt_loc 
  (
    rpat_id int not null ,
    location_id int not null ,
    loc_from int,
    loc_to int,
    branch int
  );


create index f_route_pat_loc_2 on route_patt_loc 
    (location_id) ;
create index p_route_pat_loc_1 on route_patt_loc 
    (rpat_id) ;


##alter table route_patt_loc add constraint (foreign 
#    key (loc_from) references location  constraint 
#    f_route_patt_loc_a);
##alter table route_patt_loc add constraint (foreign 
#    key (loc_to) references location  constraint 
#f_route_patt_loc_b);
##alter table route_patt_loc add constraint (foreign 
#    key (rpat_id) references route_pattern  constraint 
#    f_route_patt_loc_c);



create table route_pattern 
  (
    rpat_id int not null ,
    route_id int not null ,
    sequence int not null ,
    location_id int not null ,
    direction int not null ,
    display_order int not null ,
    display_dir int not null ,
    grid_x int,
    grid_y int,
    node_type char(3)
  );


create index f_route_pat_1 on route_pattern 
    (route_id,direction,location_id) ;
create index f_route_pat_2 on route_pattern 
    (sequence) ;
create unique index p_route_pat_1 on route_pattern 
    (rpat_id) ;
##alter table route_pattern add constraint primary key 
#    (rpat_id) constraint pk_route_pattern  ;


##alter table route_pattern add constraint (foreign 
#    key (location_id) references location  constraint 
#    f_route_pattern_b);



create table route_profile 
  (
    profile_id int not null 
  );


create unique index p_route_profile_1 on 
route_profile (profile_id) ;
##alter table route_profile add constraint primary key 
#    (profile_id) constraint pk_route_profile  ;



create table service 
  (
    service_id int not null ,
    route_id int not null ,
    service_code char(14) not null ,
    description char(40) not null ,
    tcregnum char(8),
    wef_date date not null ,
    wet_date date
  );


create index f_service_2 on service (route_id) 
    ;
create unique index i_service_3 on service 
    (route_id,service_code,wef_date) ;
create unique index p_service_1 on service 
    (service_id) ;
##alter table service add constraint primary key (service_id) 
##constraint pk_service  ;



create table service_patt 
  (
    service_id int not null ,
    rpat_orderby int not null ,
    location_id int not null ,
    loc_type char(3) not null ,
    display_type char(3),
    direction smallint not null ,
    stage_code char(4),
    arriving_addon smallint,
    exit_addon smallint,
    diversion_code char(8),
    diversion_jump smallint,
    lookahead smallint,
    late_thresh_low smallint,
    late_thresh_high smallint,
    autolate_kickin smallint,
    autolate_freq smallint,
    autolate_addon smallint,
    dest_id int not null ,
    layover_time char(5),
    bay_no char(8),
    ability_flag char(1),
    rpat_id int
  );


create index f_service_patt_2 on service_patt 
    (location_id) ;
create index f_service_patt_3 on service_patt 
    (service_id) ;
create index f_service_patt_4 on service_patt 
    (dest_id) ;
create unique index p_service_patt_1 on service_patt 
    (service_id,rpat_orderby) ;
##alter table service_patt add constraint primary key 
#    (service_id,rpat_orderby) constraint pk_service_patt 
     ;


##alter table service_patt add constraint (foreign key 
#    (location_id) references location  constraint 
#f_service_patt_a);
##alter table service_patt add constraint (foreign key 
#    (service_id) references service  constraint 
#f_service_patt_b);
##alter table service_patt add constraint (foreign key 
#    (dest_id) references destination  constraint 
#f_service_patt_c);



create table soft_ver 
  (
    version_id int not null ,
    version char(8) not null ,
    creation_date datetime not null ,
    obu_version smallint 
        default 1 not null 
  );


create unique index p_soft_ver_1 on soft_ver 
    (version_id) ;
##alter table soft_ver add constraint primary key (version_id) 
##constraint pk_soft_ver  ;



create table unit_build 
  (
    build_id int not null ,
    operator_id int not null ,
    build_code char(10) not null ,
    unit_type char(8) not null ,
    description char(20),
    build_parent int 
        default 0 not null ,
    build_status char(1),
    version_id int,
    build_notes1 char(40),
    build_notes2 char(40),
    build_type char(1) 
        default 'C' not null ,
    allow_logs smallint,
    allow_publish smallint
  );


create index f_unit_build_2 on unit_build 
    (unit_type) ;
create index f_unit_build_3 on unit_build 
    (operator_id) ;
create index f_unit_build_4 on unit_build 
    (version_id) ;
create unique index i_unit_build_5 on unit_build 
    (operator_id,build_code) ;
create unique index p_unit_build_1 on unit_build 
    (build_id) ;
##alter table unit_build add constraint primary key 
#    (build_id) constraint pk_unit_build  ;


##alter table unit_build add constraint (foreign key 
#    (unit_type) references unit_cfg_type  constraint 
#    f_unit_build_a);
##alter table unit_build add constraint (foreign key 
#    (operator_id) references operator  constraint 
#f_unit_build_b);
##alter table unit_build add constraint (foreign key 
#    (version_id) references soft_ver  constraint 
#f_unit_build_c);



create table user_build 
  (
    userid int not null ,
    operator_id int,
    build_id int
  );


create unique index pk_user_build on user_build 
    (userid,operator_id,build_id) ;


##alter table user_build add constraint (foreign key 
#    (userid) references cent_user  constraint 
#f_user_build_a);



create table user_route 
  (
    userid int not null ,
    operator_id int,
    route_id int
  );


create unique index pk_user_route on user_route 
    (userid,operator_id,route_id) ;


##alter table user_route add constraint (foreign key 
#    (userid) references cent_user  constraint 
#f_user_route_a);



create table user_vehicle 
  (
    userid int not null ,
    operator_id int,
    vehicle_id int
  );


create unique index pk_user_vehicle on user_vehicle 
    (userid,operator_id,vehicle_id) ;


##alter table user_vehicle add constraint (foreign key 
#    (userid) references cent_user  constraint 
#f_user_vehicle_a);



create table vehicle 
  (
    vehicle_id int not null ,
    vehicle_code char(10),
    vehicle_type_id int not null ,
    operator_id int not null ,
    vehicle_reg char(10),
    orun_code char(10) 
        default 'Unknown' not null ,
    vetag_indicator char(1),
    modem_addr smallint,
    build_id int,
    wheelchair_access int not null 
  );


create index f_vehicle_2 on vehicle (vehicle_type_id) 
    ;
create index f_vehicle_3 on vehicle (build_id) 
    ;
create unique index p_vehicle_1 on vehicle 
    (vehicle_id) ;
create unique index u_vehicle_1 on vehicle 
    (operator_id,vehicle_code) ;
##alter table vehicle add constraint primary key (vehicle_id) 
##constraint pk_vehicle  ;


##alter table vehicle add constraint (foreign key (vehicle_type_id) 
##references vehicle_type  constraint f_vehicle_a);
    
##alter table vehicle add constraint (foreign key (build_id) 
##references unit_build  constraint f_vehicle_b);
    


 

