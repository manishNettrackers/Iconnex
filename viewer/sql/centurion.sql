
DBSCHEMA Schema Utility       INFORMIX-SQL Version 11.50.FC9GE
grant dba to "dbmaster";
grant connect to "wwwrun";
grant connect to "appadmin";
grant connect to "appuser";
grant connect to "infsupp";
grant connect to "rgbuser";
grant connect to "gordon";
grant dba to "rgbrep";
grant dba to "thmrep";
grant dba to "firrep";
grant dba to "root";
grant connect to "rgbimp";
grant connect to "sblrep";
grant connect to "hoerep";
grant connect to "wdrep";
grant connect to "weaway";
grant dba to "tranrep";
grant connect to "rgbgen";
grant connect to "voda";
grant dba to "nwbrep";
grant dba to "wearep";
grant dba to "vodrep";
grant dba to "wb_newb";
grant dba to "wb_wway";
grant dba to "wbsrep";


create role "centrole" ;

grant "centrole" to "wwwrun" ;
grant "centrole" to "appadmin" ;
grant "centrole" to "appuser" ;
grant "centrole" to "infsupp" ;
grant "centrole" to "rgbuser" ;
grant "centrole" to "gordon" ;
grant "centrole" to "rgbrep" ;
grant "centrole" to "thmrep" ;
grant "centrole" to "firrep" ;
grant "centrole" to "rgbimp" ;
grant "centrole" to "rgbgen" ;
grant "centrole" to "rgbtest" ;
grant "centrole" to "thmstest" ;
grant "centrole" to "frsttest" ;
grant "centrole" to "voda" ;
grant "centrole" to "weaway" ;
grant "centrole" to "nwbrep" ;
grant "centrole" to "vodrep" ;
grant "centrole" to "wearep" ;
grant "centrole" to "wb_newb" ;
grant "centrole" to "wb_wway" ;
grant "centrole" to "wbsrep" ;

grant default role "centrole" to "appadmin" ;
grant default role "centrole" to "rgbgen" ;
grant default role "centrole" to "rgbtest" ;
grant default role "centrole" to "thmstest" ;
grant default role "centrole" to "frsttest" ;








{ TABLE "dbmaster".archive_rt row size = 138 number of columns = 28 index size = 
              49 }
create table "dbmaster".archive_rt 
  (
    schedule_id integer not null ,
    inbound_id integer,
    route_started smallint,
    route_synch smallint,
    route_id integer not null ,
    pub_ttb_id integer,
    profile_id integer,
    employee_id integer not null ,
    vehicle_id integer,
    trip_no char(9),
    duty_no char(10),
    running_no char(9),
    next_duty_loc smallint,
    employee2_id integer,
    next_schedule_id integer,
    start_code char(8),
    employee2_loc smallint,
    scheduled_start datetime year to second,
    timetable_start datetime hour to second,
    actual_start datetime year to second,
    start_day smallint,
    next_pub_ttb integer,
    next_pub_time datetime year to second,
    time_at_last_stop datetime year to second,
    start_lateness interval hour to second,
    route_lateness interval hour to second,
    stop_lateness interval hour to second,
    curr_lateness interval hour to second
  );

revoke all on "dbmaster".archive_rt from "public" as "dbmaster";

{ TABLE "dbmaster".media row size = 180 number of columns = 8 index size = 89 }
create table "dbmaster".media 
  (
    media_id serial not null ,
    media_desc char(50),
    media_md5sum char(32),
    media_sign char(15),
    media_type_code char(5) not null ,
    media_file byte in blobdbs,
    media_frm_code char(10) not null ,
    load_date datetime year to second
  );

revoke all on "dbmaster".media from "public" as "dbmaster";

{ TABLE "dbmaster".archive_rt_loc row size = 51 number of columns = 11 index size 
              = 26 }
create table "dbmaster".archive_rt_loc 
  (
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    location_id integer not null ,
    actual_est char(1),
    arrival_time_pub datetime year to second,
    arrival_time datetime year to second,
    arrival_status char(1),
    departure_time_pub datetime year to second,
    departure_time datetime year to second,
    departure_status char(1),
    lateness interval hour to second,
    primary key (schedule_id,rpat_orderby,location_id)  constraint "dbmaster".pk_archive_rt_loc
  );

revoke all on "dbmaster".archive_rt_loc from "public" as "dbmaster";

{ TABLE "dbmaster".active_rt row size = 144 number of columns = 31 index size = 49 
              }
create table "dbmaster".active_rt 
  (
    schedule_id serial not null ,
    inbound_id integer,
    route_started smallint,
    route_synch smallint,
    route_id integer not null ,
    pub_ttb_id integer,
    profile_id integer,
    employee_id integer not null ,
    vehicle_id integer not null ,
    trip_no char(9),
    duty_no char(10),
    running_no char(9),
    next_duty_loc smallint,
    employee2_id integer,
    next_schedule_id integer,
    start_code char(8),
    employee2_loc smallint,
    scheduled_start datetime year to second,
    timetable_start datetime hour to second,
    actual_start datetime year to second,
    start_day smallint,
    next_pub_ttb integer,
    next_pub_time datetime year to second,
    time_at_last_stop datetime year to second,
    start_lateness interval hour to second,
    route_lateness interval hour to second,
    publish_lateness interval hour to second,
    curr_lateness interval hour to second,
    trip_status char(1) 
        default 'A' not null ,
    lost_count integer,
    lost_status char(1)
  );

revoke all on "dbmaster".active_rt from "public" as "dbmaster";

{ TABLE "dbmaster".active_rt_loc row size = 65 number of columns = 15 index size 
              = 26 }
create table "dbmaster".active_rt_loc 
  (
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    location_id integer not null ,
    actual_est char(1),
    arrival_time_pub datetime year to second,
    arrival_time datetime year to second,
    arrival_status char(1),
    departure_time_pub datetime year to second,
    departure_time datetime year to second,
    departure_status char(1),
    lateness interval hour to second,
    est_travel_time interval hour to second,
    est_wait_time interval hour to second,
    pub_time datetime hour to second,
    layover_flag smallint
  );

revoke all on "dbmaster".active_rt_loc from "public" as "dbmaster";

{ TABLE "dbmaster".autoroute_time row size = 46 number of columns = 8 index size 
              = 66 }
create table "dbmaster".autoroute_time 
  (
    auto_prof_id integer not null ,
    service_id integer not null ,
    duty_no char(10) not null ,
    trip_no char(9) not null ,
    running_no char(9) not null ,
    scheduled_start datetime hour to second,
    direction smallint not null ,
    auto_start_time datetime hour to second
  );

revoke all on "dbmaster".autoroute_time from "public" as "dbmaster";

{ TABLE "dbmaster".autort_config row size = 14 number of columns = 4 index size = 
              22 }
create table "dbmaster".autort_config 
  (
    service_id integer not null ,
    dayno integer not null ,
    auto_prof_id integer not null ,
    enabled smallint
  );

revoke all on "dbmaster".autort_config from "public" as "dbmaster";

{ TABLE "dbmaster".autort_profile row size = 12 number of columns = 2 index size 
              = 9 }
create table "dbmaster".autort_profile 
  (
    auto_prof_id serial not null ,
    auto_prof_desc char(8)
  );

revoke all on "dbmaster".autort_profile from "public" as "dbmaster";

{ TABLE "dbmaster".cent_user row size = 136 number of columns = 10 index size = 29 
              }
create table "dbmaster".cent_user 
  (
    userid serial not null ,
    usernm char(15),
    narrtv char(20) not null ,
    operator_id integer,
    passwd char(10),
    passwd_md5 char(40),
    emailad char(30),
    maxsess smallint 
        default 99,
    langcd char(5) 
        default 'en_gb' not null ,
    menucd char(6) 
        default 'MASTER' not null 
  );

revoke all on "dbmaster".cent_user from "public" as "dbmaster";

{ TABLE "dbmaster".component row size = 55 number of columns = 4 index size = 34 
              }
create table "dbmaster".component 
  (
    component_id serial not null ,
    component_type char(1) not null ,
    component_code char(20) not null ,
    component_desc char(30) not null 
  );

revoke all on "dbmaster".component from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_message row size = 569 number of columns = 4 index size = 
              0 }
create table "dbmaster".dcd_message 
  (
    message_id serial not null ,
    feed char(50),
    message_text char(500),
    message_group char(15)
  );

revoke all on "dbmaster".dcd_message from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_message_loc row size = 75 number of columns = 12 index size 
              = 13 }
create table "dbmaster".dcd_message_loc 
  (
    message_id integer not null ,
    build_id integer not null ,
    creation_time datetime year to second,
    display_time datetime year to second,
    expiry_time datetime year to second,
    hold_time integer,
    interleave_mode char(10),
    display_style char(10),
    activity_mode char(1) not null ,
    message_sent datetime year to second,
    display_flag smallint,
    received datetime year to second
  );

revoke all on "dbmaster".dcd_message_loc from "public" as "dbmaster";

{ TABLE "dbmaster".destination row size = 128 number of columns = 7 index size = 
              50 }
create table "dbmaster".destination 
  (
    dest_id serial not null ,
    operator_id integer not null ,
    dest_code char(10) not null ,
    dest_long char(50),
    dest_short1 char(20),
    terminal_text char(20),
    display_text char(20)
  );

revoke all on "dbmaster".destination from "public" as "dbmaster";

{ TABLE "dbmaster".display_point row size = 31 number of columns = 7 index size = 
              18 }
create table "dbmaster".display_point 
  (
    location_id integer,
    build_id integer,
    display_type char(1),
    filename char(15),
    display_mode char(1),
    delivery_mode char(5),
    disabled char(1)
  );

revoke all on "dbmaster".display_point from "public" as "dbmaster";

{ TABLE "dbmaster".district row size = 34 number of columns = 3 index size = 49 }
create table "dbmaster".district 
  (
    district_id serial not null ,
    district_code char(6),
    district_name char(24) not null 
  );

revoke all on "dbmaster".district from "public" as "dbmaster";

{ TABLE "dbmaster".driver_message row size = 92 number of columns = 4 index size 
              = 22 }
create table "dbmaster".driver_message 
  (
    operator_id integer not null ,
    message_id integer not null ,
    message_text char(80) not null ,
    priority integer not null 
  );

revoke all on "dbmaster".driver_message from "public" as "dbmaster";

{ TABLE "dbmaster".employee row size = 56 number of columns = 5 index size = 35 }
create table "dbmaster".employee 
  (
    operator_id integer,
    employee_id serial not null ,
    employee_code char(8) not null ,
    fullname char(30) not null ,
    orun_code char(10) 
        default 'Unknown'
  );

revoke all on "dbmaster".employee from "public" as "dbmaster";

{ TABLE "dbmaster".event_pattern row size = 9 number of columns = 3 index size = 
              22 }
create table "dbmaster".event_pattern 
  (
    evprf_id integer not null ,
    event_id integer not null ,
    operational char(1) not null 
  );

revoke all on "dbmaster".event_pattern from "public" as "dbmaster";

{ TABLE "dbmaster".event_profile row size = 4 number of columns = 1 index size = 
              9 }
create table "dbmaster".event_profile 
  (
    evprf_id serial not null 
  );

revoke all on "dbmaster".event_profile from "public" as "dbmaster";

{ TABLE "dbmaster".fare_stage row size = 68 number of columns = 4 index size = 18 
              }
create table "dbmaster".fare_stage 
  (
    fare_stage_id serial not null ,
    service_id integer not null ,
    fare_stage_code char(10),
    description char(50)
  );

revoke all on "dbmaster".fare_stage from "public" as "dbmaster";

{ TABLE "dbmaster".feed_format row size = 40 number of columns = 2 index size = 15 
              }
create table "dbmaster".feed_format 
  (
    format_code char(10) not null ,
    description char(30) not null 
  );

revoke all on "dbmaster".feed_format from "public" as "dbmaster";

{ TABLE "dbmaster".feed_history row size = 76 number of columns = 7 index size = 
              18 }
create table "dbmaster".feed_history 
  (
    feed_id serial not null ,
    feed_time datetime year to second not null ,
    feed_file char(20) not null ,
    feed_type_id integer not null ,
    feed_origin char(16) not null ,
    feed_product char(16) not null ,
    feed_prod_time datetime year to second
  );

revoke all on "dbmaster".feed_history from "public" as "dbmaster";

{ TABLE "dbmaster".feed_type row size = 24 number of columns = 4 index size = 58 
              }
create table "dbmaster".feed_type 
  (
    feed_type_id serial not null ,
    operator_id integer not null ,
    format_code char(10) not null ,
    version char(6) not null 
  );

revoke all on "dbmaster".feed_type from "public" as "dbmaster";

{ TABLE "dbmaster".gprs_mapping row size = 32 number of columns = 3 index size = 
              9 }
create table "dbmaster".gprs_mapping 
  (
    build_id integer not null ,
    ip_address char(20),
    connect_date datetime year to second
  );

revoke all on "dbmaster".gprs_mapping from "public" as "dbmaster";

{ TABLE "dbmaster".html_tag row size = 180 number of columns = 4 index size = 35 
              }
create table "dbmaster".html_tag 
  (
    report_code char(10) not null ,
    tag_type char(5) not null ,
    tag_code char(15) not null ,
    tag_text char(150)
  );

revoke all on "dbmaster".html_tag from "public" as "dbmaster";

{ TABLE "dbmaster".junction row size = 72 number of columns = 5 index size = 29 }
create table "dbmaster".junction 
  (
    junction_code integer not null ,
    junction_desc char(50) not null ,
    location_id integer not null ,
    sigprot_code char(6) not null ,
    signal_number char(8) not null 
  );

revoke all on "dbmaster".junction from "public" as "dbmaster";

{ TABLE "dbmaster".junction_aprch row size = 18 number of columns = 3 index size 
              = 52 }
create table "dbmaster".junction_aprch 
  (
    junction_code integer not null ,
    road_code char(10) not null ,
    location_id integer
  );

revoke all on "dbmaster".junction_aprch from "public" as "dbmaster";

{ TABLE "dbmaster".junction_reg row size = 32 number of columns = 5 index size = 
              62 }
create table "dbmaster".junction_reg 
  (
    junction_code integer not null ,
    traversal_id integer,
    regis_code char(10) not null ,
    distance integer not null ,
    trigger_type_code char(10) not null 
  );

revoke all on "dbmaster".junction_reg from "public" as "dbmaster";

{ TABLE "dbmaster".junction_xtrav row size = 41 number of columns = 8 index size 
              = 47 }
create table "dbmaster".junction_xtrav 
  (
    traversal_id serial not null ,
    junction_code integer not null ,
    approach_roadcd char(10) not null ,
    exit_roadcd char(10) not null ,
    siggrp_movnum integer not null ,
    activation_type char(1) not null ,
    activation_efdt date not null ,
    activation_endt date
  );

revoke all on "dbmaster".junction_xtrav from "public" as "dbmaster";

{ TABLE "dbmaster".layover row size = 16 number of columns = 2 index size = 6 }
create table "dbmaster".layover 
  (
    layover_type char(1) not null ,
    layover_desc char(15) not null 
  );

revoke all on "dbmaster".layover from "public" as "dbmaster";

{ TABLE "dbmaster".location row size = 172 number of columns = 23 index size = 59 
              }
create table "dbmaster".location 
  (
    location_id serial not null ,
    location_code char(12) not null ,
    gprs_xmit_code smallint,
    point_type char(1) not null ,
    route_area_id integer not null ,
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
    place_id integer,
    district_id integer,
    arriving_addon integer,
    exit_addon integer,
    bay_no char(8),
    
    check (latitude_heading IN ('N' ,'S' ,'' )) constraint "dbmaster".c_location_a,
    
    check (longitude_heading IN ('W' ,'E' ,'' )) constraint "dbmaster".c_location_b
  );

revoke all on "dbmaster".location from "public" as "dbmaster";

{ TABLE "dbmaster".location_type row size = 34 number of columns = 3 index size = 
              6 }
create table "dbmaster".location_type 
  (
    point_type char(1) not null ,
    point_desc char(30) not null ,
    point_catg char(3)
  );

revoke all on "dbmaster".location_type from "public" as "dbmaster";

{ TABLE "dbmaster".media_format row size = 40 number of columns = 2 index size = 
              15 }
create table "dbmaster".media_format 
  (
    format_code char(10) not null ,
    format_desc char(30) not null 
  );

revoke all on "dbmaster".media_format from "public" as "dbmaster";

{ TABLE "dbmaster".media_type row size = 35 number of columns = 2 index size = 10 
              }
create table "dbmaster".media_type 
  (
    media_type_code char(5) not null ,
    media_type_desc char(30) not null 
  );

revoke all on "dbmaster".media_type from "public" as "dbmaster";

{ TABLE "dbmaster".msg_to_veh row size = 99 number of columns = 10 index size = 27 
              }
create table "dbmaster".msg_to_veh 
  (
    message_id serial not null ,
    build_id integer not null ,
    message_text char(50) not null ,
    message_sent char(1) not null ,
    time_sent datetime year to second not null ,
    user_id integer not null ,
    priority integer,
    veh_ack datetime year to second,
    drv_ack datetime year to second,
    time_create datetime year to second
  );

revoke all on "dbmaster".msg_to_veh from "public" as "dbmaster";

{ TABLE "dbmaster".opconarea row size = 44 number of columns = 3 index size = 28 
              }
create table "dbmaster".opconarea 
  (
    operator_id integer not null ,
    opconarea_code char(10) not null ,
    opconarea_desc char(30) not null 
  );

revoke all on "dbmaster".opconarea from "public" as "dbmaster";

{ TABLE "dbmaster".operator row size = 191 number of columns = 11 index size = 30 
              }
create table "dbmaster".operator 
  (
    operator_id serial not null ,
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

revoke all on "dbmaster".operator from "public" as "dbmaster";

{ TABLE "dbmaster".operator_media row size = 88 number of columns = 4 index size 
              = 22 }
create table "dbmaster".operator_media 
  (
    operator_id integer not null ,
    publish_path char(40) not null ,
    target_path char(40) not null ,
    media_id integer not null 
  );

revoke all on "dbmaster".operator_media from "public" as "dbmaster";

{ TABLE "dbmaster".orgunit row size = 64 number of columns = 5 index size = 38 }
create table "dbmaster".orgunit 
  (
    operator_id integer not null ,
    orun_code char(10) not null ,
    orun_desc char(30) not null ,
    opconarea_code char(10) not null ,
    parent_code char(10) not null 
  );

revoke all on "dbmaster".orgunit from "public" as "dbmaster";

{ TABLE "dbmaster".parameter row size = 39 number of columns = 4 index size = 61 
              }
create table "dbmaster".parameter 
  (
    component_id integer not null ,
    param_id serial not null ,
    param_desc char(30) not null ,
    inheritable char(1) not null 
  );

revoke all on "dbmaster".parameter from "public" as "dbmaster";

{ TABLE "dbmaster".period_group row size = 36 number of columns = 4 index size = 
              24 }
create table "dbmaster".period_group 
  (
    operator_id integer not null ,
    pegp_code char(6) not null ,
    pegp_desc char(20) not null ,
    parent_code char(6)
  );

revoke all on "dbmaster".period_group from "public" as "dbmaster";

{ TABLE "dbmaster".place row size = 55 number of columns = 5 index size = 37 }
create table "dbmaster".place 
  (
    place_id serial not null ,
    place_code char(10) not null ,
    place_name char(30) not null ,
    place_short char(7),
    town_id integer not null 
  );

revoke all on "dbmaster".place from "public" as "dbmaster";

{ TABLE "dbmaster".pt_duty row size = 22 number of columns = 5 index size = 47 }
create table "dbmaster".pt_duty 
  (
    pub_ttb_id integer not null ,
    profile_id integer not null ,
    service_id integer not null ,
    rpat_orderby integer not null ,
    duty_no char(6) not null 
  );

revoke all on "dbmaster".pt_duty from "public" as "dbmaster";

{ TABLE "dbmaster".ptactn row size = 41 number of columns = 4 index size = 65 }
create table "dbmaster".ptactn 
  (
    dmnscd char(5) not null ,
    langcd char(5) not null ,
    optkey char(1) not null ,
    optwrd char(30) not null 
  );

revoke all on "dbmaster".ptactn from "public" as "dbmaster";

{ TABLE "dbmaster".ptdict row size = 296 number of columns = 4 index size = 74 }
create table "dbmaster".ptdict 
  (
    langcd char(5),
    tabnam char(18) not null ,
    colnam char(18) not null ,
    coldes char(255)
  );

revoke all on "dbmaster".ptdict from "public" as "dbmaster";

{ TABLE "dbmaster".ptdmac row size = 8 number of columns = 3 index size = 21 }
create table "dbmaster".ptdmac 
  (
    dmnscd char(5) not null ,
    optkey char(1) not null ,
    seqnum smallint not null 
  );

revoke all on "dbmaster".ptdmac from "public" as "dbmaster";

{ TABLE "dbmaster".ptdmns row size = 25 number of columns = 2 index size = 10 }
create table "dbmaster".ptdmns 
  (
    dmnscd char(5) not null ,
    dmnsds char(20) not null 
  );

revoke all on "dbmaster".ptdmns from "public" as "dbmaster";

{ TABLE "dbmaster".ptengl row size = 85 number of columns = 3 index size = 9 }
create table "dbmaster".ptengl 
  (
    serlno serial not null ,
    engwrd char(80) not null ,
    engkey char(1)
  );

revoke all on "dbmaster".ptengl from "public" as "dbmaster";

{ TABLE "dbmaster".ptfgms row size = 135 number of columns = 3 index size = 29 }
create table "dbmaster".ptfgms 
  (
    mesgcd smallint not null ,
    langcd char(5) not null ,
    mesgds char(128) not null 
  );

revoke all on "dbmaster".ptfgms from "public" as "dbmaster";

{ TABLE "dbmaster".ptfgop row size = 304 number of columns = 4 index size = 45 }
create table "dbmaster".ptfgop 
  (
    optcod char(10) not null ,
    langcd char(5) not null ,
    opttxt char(34) not null ,
    optdes char(255)
  );

revoke all on "dbmaster".ptfgop from "public" as "dbmaster";

{ TABLE "dbmaster".ptfgtx row size = 90 number of columns = 4 index size = 33 }
create table "dbmaster".ptfgtx 
  (
    serlno integer not null ,
    langcd char(5) not null ,
    fgnwrd char(80) not null ,
    fgnkey char(1)
  );

revoke all on "dbmaster".ptfgtx from "public" as "dbmaster";

{ TABLE "dbmaster".ptgprm row size = 14 number of columns = 2 index size = 28 }
create table "dbmaster".ptgprm 
  (
    grupid integer not null ,
    optcod char(10) not null 
  );

revoke all on "dbmaster".ptgprm from "public" as "dbmaster";

{ TABLE "dbmaster".ptgrup row size = 44 number of columns = 3 index size = 24 }
create table "dbmaster".ptgrup 
  (
    grupid serial not null ,
    grupcd char(10) not null ,
    grupnm char(30) not null 
  );

revoke all on "dbmaster".ptgrup from "public" as "dbmaster";

{ TABLE "dbmaster".ptgrus row size = 8 number of columns = 2 index size = 31 }
create table "dbmaster".ptgrus 
  (
    grupid integer not null ,
    userid integer not null 
  );

revoke all on "dbmaster".ptgrus from "public" as "dbmaster";

{ TABLE "dbmaster".ptlang row size = 35 number of columns = 2 index size = 10 }
create table "dbmaster".ptlang 
  (
    langcd char(5) not null ,
    langnm char(30) not null 
  );

revoke all on "dbmaster".ptlang from "public" as "dbmaster";

{ TABLE "dbmaster".ptmndt row size = 19 number of columns = 4 index size = 24 }
create table "dbmaster".ptmndt 
  (
    menucd char(6) not null ,
    seqnum smallint not null ,
    opttyp char(1) not null ,
    mnopcd char(10) not null 
  );

revoke all on "dbmaster".ptmndt from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnhd row size = 6 number of columns = 1 index size = 11 }
create table "dbmaster".ptmnhd 
  (
    menucd char(6) not null 
  );

revoke all on "dbmaster".ptmnhd from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnlg row size = 28 number of columns = 6 index size = 26 }
create table "dbmaster".ptmnlg 
  (
    userid integer not null ,
    sttdat date not null ,
    stttim datetime hour to second not null ,
    enddat date not null ,
    endtim datetime hour to second not null ,
    optcod char(8) not null 
  );

revoke all on "dbmaster".ptmnlg from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnms row size = 51 number of columns = 3 index size = 37 }
create table "dbmaster".ptmnms 
  (
    menucd char(6) not null ,
    langcd char(5) not null ,
    mennam char(40) not null 
  );

revoke all on "dbmaster".ptmnms from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnop row size = 111 number of columns = 5 index size = 15 }
create table "dbmaster".ptmnop 
  (
    optcod char(10) not null ,
    opttyp char(1) not null ,
    optapp char(20),
    optmod char(20),
    optcmd char(60)
  );

revoke all on "dbmaster".ptmnop from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnpc row size = 634 number of columns = 14 index size = 15 }
create table "dbmaster".ptmnpc 
  (
    ptrtyp char(10) not null ,
    inistr char(48),
    cp10df char(48),
    cp12df char(48),
    cp15df char(48),
    cp17df char(48),
    cp20df char(48),
    cp10nl char(48),
    cp12nl char(48),
    cp15nl char(48),
    cp17nl char(48),
    cp20nl char(48),
    lp0006 char(48),
    lp0008 char(48)
  );

revoke all on "dbmaster".ptmnpc from "public" as "dbmaster";

{ TABLE "dbmaster".ptmnpt row size = 156 number of columns = 8 index size = 30 }
create table "dbmaster".ptmnpt 
  (
    ptrnam char(10) not null ,
    ptrflg char(1) not null ,
    ptrtyp char(10) not null ,
    narrtv char(30) not null ,
    pageln smallint,
    pagewd smallint,
    slctbl char(1) not null ,
    pipcmd char(100) not null 
  );

revoke all on "dbmaster".ptmnpt from "public" as "dbmaster";

{ TABLE "dbmaster".ptmsgs row size = 8 number of columns = 3 index size = 17 }
create table "dbmaster".ptmsgs 
  (
    mesgcd smallint not null ,
    dmnscd char(5) not null ,
    mesgtp char(1)
  );

revoke all on "dbmaster".ptmsgs from "public" as "dbmaster";

{ TABLE "dbmaster".pttabs row size = 83 number of columns = 3 index size = 38 }
create table "dbmaster".pttabs 
  (
    langcd char(5) 
        default 'en_gb' not null ,
    tabnam char(18) not null ,
    tabdes char(60)
  );

revoke all on "dbmaster".pttabs from "public" as "dbmaster";

{ TABLE "dbmaster".ptuprm row size = 14 number of columns = 2 index size = 43 }
create table "dbmaster".ptuprm 
  (
    userid integer not null ,
    optcod char(10) not null 
  );

revoke all on "dbmaster".ptuprm from "public" as "dbmaster";

{ TABLE "dbmaster".ptuspr row size = 24 number of columns = 3 index size = 58 }
create table "dbmaster".ptuspr 
  (
    userid integer not null ,
    optcod char(10) not null ,
    prntcd char(10) not null 
  );

revoke all on "dbmaster".ptuspr from "public" as "dbmaster";

{ TABLE "dbmaster".publication row size = 48 number of columns = 10 index size = 
              18 }
create table "dbmaster".publication 
  (
    pub_id serial not null ,
    operator_id integer not null ,
    pub_start_time datetime year to second,
    pub_end_time datetime year to second,
    pub_due datetime year to second,
    pub_months smallint,
    stripped_duty smallint,
    runningboard_type char(1),
    output_type char(10),
    pubtype char(1)
  );

revoke all on "dbmaster".publication from "public" as "dbmaster";

{ TABLE "dbmaster".publish_time row size = 20 number of columns = 5 index size = 
              22 }
create table "dbmaster".publish_time 
  (
    pub_ttb_id integer not null ,
    rpat_orderby integer,
    location_id integer not null ,
    arrival_time datetime hour to second,
    pub_time datetime hour to second
  );

revoke all on "dbmaster".publish_time from "public" as "dbmaster";

{ TABLE "dbmaster".publish_tt row size = 133 number of columns = 13 index size = 
              105 }
create table "dbmaster".publish_tt 
  (
    pub_ttb_id serial not null ,
    service_id integer not null ,
    trip_no char(10),
    runningno char(5),
    duty_no char(6),
    orun_code char(10),
    direction smallint,
    pub_prof_id integer not null ,
    rtpi_prof_id integer not null ,
    start_time datetime hour to second,
    vehicle_type_id integer not null ,
    evprf_id integer not null ,
    notes char(72)
  );

revoke all on "dbmaster".publish_tt from "public" as "dbmaster";

{ TABLE "dbmaster".registration row size = 41 number of columns = 3 index size = 
              15 }
create table "dbmaster".registration 
  (
    regis_code char(10) not null ,
    regis_desc char(30) not null ,
    regis_evnt char(1) not null 
  );

revoke all on "dbmaster".registration from "public" as "dbmaster";

{ TABLE "dbmaster".revision_hist row size = 35 number of columns = 4 index size = 
              17 }
create table "dbmaster".revision_hist 
  (
    revision_id serial not null ,
    revision_time datetime year to second not null ,
    rev_type_code char(3) not null ,
    table_key char(20) not null 
  );

revoke all on "dbmaster".revision_hist from "public" as "dbmaster";

{ TABLE "dbmaster".revision_type row size = 33 number of columns = 2 index size = 
              8 }
create table "dbmaster".revision_type 
  (
    rev_type_code char(3) not null ,
    rev_type_desc char(30) not null 
  );

revoke all on "dbmaster".revision_type from "public" as "dbmaster";

{ TABLE "dbmaster".road row size = 40 number of columns = 2 index size = 15 }
create table "dbmaster".road 
  (
    road_code char(10) not null ,
    road_desc char(30) not null 
  );

revoke all on "dbmaster".road from "public" as "dbmaster";

{ TABLE "dbmaster".route row size = 126 number of columns = 6 index size = 35 }
create table "dbmaster".route 
  (
    route_id serial not null ,
    route_code char(8) not null ,
    operator_id integer not null ,
    description char(30),
    outbound_desc char(40),
    inbound_desc char(40)
  );

revoke all on "dbmaster".route from "public" as "dbmaster";

{ TABLE "dbmaster".route_area row size = 64 number of columns = 3 index size = 9 
              }
create table "dbmaster".route_area 
  (
    route_area_id serial not null ,
    route_area_code char(20) not null ,
    description char(40) not null 
  );

revoke all on "dbmaster".route_area from "public" as "dbmaster";

{ TABLE "dbmaster".route_loc_avg row size = 81 number of columns = 17 index size 
              = 49 }
create table "dbmaster".route_loc_avg 
  (
    route_int_id serial not null ,
    service_id integer not null ,
    profile_id integer not null ,
    location_id integer not null ,
    rloc_orderby smallint,
    stage_code char(4),
    lookahead smallint,
    late_thresh_low smallint,
    late_thresh_high smallint,
    autolate_kickin smallint,
    autolate_freq smallint,
    autolate_addon smallint,
    map_image char(25),
    distance decimal(16),
    travel_time interval hour to second,
    wait_time interval hour to second,
    unload_time interval hour to second
  );

revoke all on "dbmaster".route_loc_avg from "public" as "dbmaster";

{ TABLE "dbmaster".route_message row size = 167 number of columns = 26 index size 
              = 9 }
create table "dbmaster".route_message 
  (
    message_id serial not null ,
    messagetype integer,
    messageid integer,
    addressspec char(10),
    structuretype integer,
    action smallint,
    routestarted smallint,
    routesynchronizing smallint,
    routecode char(11),
    drivercode char(11),
    vehiclecode char(11),
    trip_no char(10),
    dutynumber char(9),
    stagenumber char(9),
    runningnumber char(9),
    direction smallint,
    starttime char(9),
    locationcode char(11),
    prevlocationcode char(11),
    timeroutestarted datetime year to second,
    timeatlaststop datetime year to second,
    timebetweenstops smallint,
    schedtimeforstops smallint,
    timetablelateness smallint,
    currentlateness smallint,
    statustime datetime year to second
  );

revoke all on "dbmaster".route_message from "public" as "dbmaster";

{ TABLE "dbmaster".route_profile row size = 4 number of columns = 1 index size = 
              9 }
create table "dbmaster".route_profile 
  (
    profile_id serial not null 
  );

revoke all on "dbmaster".route_profile from "public" as "dbmaster";

{ TABLE "dbmaster".serv_pat_media row size = 12 number of columns = 3 index size 
              = 30 }
create table "dbmaster".serv_pat_media 
  (
    service_id integer not null ,
    rpat_orderby integer not null ,
    media_id integer not null 
  );

revoke all on "dbmaster".serv_pat_media from "public" as "dbmaster";

{ TABLE "dbmaster".service row size = 78 number of columns = 7 index size = 45 }
create table "dbmaster".service 
  (
    service_id serial not null ,
    route_id integer not null ,
    service_code char(14) not null ,
    description char(40) not null ,
    tcregnum char(8),
    wef_date date not null ,
    wet_date date
  );

revoke all on "dbmaster".service from "public" as "dbmaster";

{ TABLE "dbmaster".service_link row size = 20 number of columns = 5 index size = 
              39 }
create table "dbmaster".service_link 
  (
    service_link_id serial not null ,
    route_id integer,
    service_id integer not null ,
    str_loc_id integer not null ,
    end_loc_id integer not null 
  );

revoke all on "dbmaster".service_link from "public" as "dbmaster";

{ TABLE "dbmaster".service_patt row size = 66 number of columns = 21 index size = 
              40 }
create table "dbmaster".service_patt 
  (
    service_id integer not null ,
    rpat_orderby integer not null ,
    location_id integer not null ,
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
    dest_id integer not null ,
    layover_time datetime minute to second,
    bay_no char(8),
    ability_flag char(1)
  );

revoke all on "dbmaster".service_patt from "public" as "dbmaster";

{ TABLE "dbmaster".servlink_xtrav row size = 14 number of columns = 4 index size 
              = 20 }
create table "dbmaster".servlink_xtrav 
  (
    service_link_id integer not null ,
    seqnum smallint not null ,
    traversal_id integer,
    location_id integer
  );

revoke all on "dbmaster".servlink_xtrav from "public" as "dbmaster";

{ TABLE "dbmaster".sign_info row size = 34 number of columns = 2 index size = 9 }
create table "dbmaster".sign_info 
  (
    sign_number integer not null ,
    sign_description char(30) not null 
  );

revoke all on "dbmaster".sign_info from "public" as "dbmaster";

{ TABLE "dbmaster".signal_prot row size = 36 number of columns = 2 index size = 11 
              }
create table "dbmaster".signal_prot 
  (
    sigprot_code char(6) not null ,
    sigprot_desc char(30) not null 
  );

revoke all on "dbmaster".signal_prot from "public" as "dbmaster";

{ TABLE "dbmaster".soft_ver row size = 22 number of columns = 4 index size = 9 }
create table "dbmaster".soft_ver 
  (
    version_id serial not null ,
    version char(8) not null ,
    creation_date datetime year to second not null ,
    obu_version smallint 
        default 1 not null 
  );

revoke all on "dbmaster".soft_ver from "public" as "dbmaster";

{ TABLE "dbmaster".special_op row size = 20 number of columns = 5 index size = 56 
              }
create table "dbmaster".special_op 
  (
    operator_id integer not null ,
    route_id integer,
    service_id integer,
    op_event integer not null ,
    map_event integer not null 
  );

revoke all on "dbmaster".special_op from "public" as "dbmaster";

{ TABLE "dbmaster".system_key row size = 70 number of columns = 2 index size = 15 
              }
create table "dbmaster".system_key 
  (
    key_code char(10) not null ,
    key_value char(60)
  );

revoke all on "dbmaster".system_key from "public" as "dbmaster";

{ TABLE "dbmaster".tlp_adjust row size = 25 number of columns = 6 index size = 37 
              }
create table "dbmaster".tlp_adjust 
  (
    operator_id integer not null ,
    junction_code integer not null ,
    road_code char(10) not null ,
    day_number smallint not null ,
    start_time datetime hour to minute not null ,
    adj_meters smallint not null 
  );

revoke all on "dbmaster".tlp_adjust from "public" as "dbmaster";

{ TABLE "dbmaster".tlp_sched_adh row size = 16 number of columns = 4 index size = 
              26 }
create table "dbmaster".tlp_sched_adh 
  (
    sigprot_code char(6) not null ,
    min_deviation integer,
    max_deviation integer not null ,
    deviation_code smallint not null 
  );

revoke all on "dbmaster".tlp_sched_adh from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_place row size = 169 number of columns = 10 index size = 15 
              }
create table "dbmaster".tmi_place 
  (
    record_type char(10),
    imporexp char(1),
    version_number smallint,
    batch_id integer,
    data_owner_code char(10),
    user_place_code char(10),
    place_valid_from char(8),
    name char(50),
    short_name char(24),
    remark char(50)
  );

revoke all on "dbmaster".tmi_place from "public" as "dbmaster";

{ TABLE "dbmaster".town row size = 34 number of columns = 3 index size = 49 }
create table "dbmaster".town 
  (
    town_id serial not null ,
    town_code char(6) not null ,
    town_name char(24) not null 
  );

revoke all on "dbmaster".town from "public" as "dbmaster";

{ TABLE "dbmaster".trigger_type row size = 40 number of columns = 2 index size = 
              15 }
create table "dbmaster".trigger_type 
  (
    trigger_type_code char(10) not null ,
    trigger_type_desc char(30) not null 
  );

revoke all on "dbmaster".trigger_type from "public" as "dbmaster";

{ TABLE "dbmaster".unit_build row size = 140 number of columns = 13 index size = 
              59 }
create table "dbmaster".unit_build 
  (
    build_id serial not null ,
    operator_id integer not null ,
    build_code char(10) not null ,
    unit_type char(8) not null ,
    description char(20),
    build_parent integer 
        default 0 not null ,
    build_status char(1),
    version_id integer,
    build_notes1 char(40),
    build_notes2 char(40),
    build_type char(1) 
        default 'C' not null ,
    allow_logs smallint,
    allow_publish smallint
  );

revoke all on "dbmaster".unit_build from "public" as "dbmaster";

{ TABLE "dbmaster".unit_cfg_type row size = 39 number of columns = 3 index size = 
              13 }
create table "dbmaster".unit_cfg_type 
  (
    unit_type char(8) not null ,
    description char(30) not null ,
    publish char(1) not null 
  );

revoke all on "dbmaster".unit_cfg_type from "public" as "dbmaster";

{ TABLE "dbmaster".unit_history row size = 63 number of columns = 4 index size = 
              26 }
create table "dbmaster".unit_history 
  (
    build_id integer not null ,
    note_date datetime year to second not null ,
    note_type char(1),
    note_text char(50)
  );

revoke all on "dbmaster".unit_history from "public" as "dbmaster";

{ TABLE "dbmaster".unit_param row size = 72 number of columns = 4 index size = 39 
              }
create table "dbmaster".unit_param 
  (
    build_id integer not null ,
    component_id integer not null ,
    param_id integer not null ,
    param_value char(60)
  );

revoke all on "dbmaster".unit_param from "public" as "dbmaster";

{ TABLE "dbmaster".unit_publish row size = 42 number of columns = 8 index size = 
              31 }
create table "dbmaster".unit_publish 
  (
    pub_id integer not null ,
    build_id integer not null ,
    upload_start datetime year to second,
    upload_finish datetime year to second,
    upload_status char(1),
    tidyup_start datetime year to second,
    tidyup_finish datetime year to second,
    tidyup_status char(1)
  );

revoke all on "dbmaster".unit_publish from "public" as "dbmaster";

{ TABLE "dbmaster".unit_reply row size = 117 number of columns = 6 index size = 26 
              }
create table "dbmaster".unit_reply 
  (
    mesg_type smallint,
    message_id integer not null ,
    message_time datetime year to second not null ,
    ping_task char(15),
    ping_answer char(80),
    reply_time datetime year to second
  );

revoke all on "dbmaster".unit_reply from "public" as "dbmaster";

{ TABLE "dbmaster".vehicle row size = 53 number of columns = 10 index size = 65 }
create table "dbmaster".vehicle 
  (
    vehicle_id serial not null ,
    vehicle_code char(10),
    vehicle_type_id integer not null ,
    operator_id integer not null ,
    vehicle_reg char(10),
    orun_code char(10) 
        default 'Unknown' not null ,
    vetag_indicator char(1),
    modem_addr smallint,
    build_id integer,
    wheelchair_access integer not null 
  );

revoke all on "dbmaster".vehicle from "public" as "dbmaster";

{ TABLE "dbmaster".vehicle_type row size = 52 number of columns = 7 index size = 
              24 }
create table "dbmaster".vehicle_type 
  (
    vehicle_type_id serial not null ,
    vehicle_type_code char(10) not null ,
    vehicle_type_desc char(30) not null ,
    vehicle_length smallint,
    seating_cap smallint,
    standing_cap smallint,
    special_cap smallint
  );

revoke all on "dbmaster".vehicle_type from "public" as "dbmaster";

{ TABLE "dbmaster".event row size = 65 number of columns = 11 index size = 31 }
create table "dbmaster".event 
  (
    operator_id integer not null ,
    event_id serial not null ,
    event_code char(8),
    event_desc char(30),
    event_tp char(1),
    spdt_start date,
    spdt_end date,
    rpdt_start datetime month to day,
    rpdt_end datetime month to day,
    rpdy_start smallint,
    rpdy_end smallint
  );

revoke all on "dbmaster".event from "public" as "dbmaster";

{ TABLE "dbmaster".pergrval row size = 32 number of columns = 5 index size = 71 }
create table "dbmaster".pergrval 
  (
    operator_id integer not null ,
    orun_code char(10) not null ,
    pegr_code char(10) not null ,
    valid_from date not null ,
    valid_thru date not null 
  );

revoke all on "dbmaster".pergrval from "public" as "dbmaster";

{ TABLE "dbmaster".unit_log_hist row size = 24 number of columns = 4 index size = 
              22 }
create table "dbmaster".unit_log_hist 
  (
    build_id integer not null ,
    logfile_date date not null ,
    load_db_time datetime year to second,
    load_wlan_time datetime year to second
  );

revoke all on "dbmaster".unit_log_hist from "public" as "dbmaster";

{ TABLE "dbmaster".route_alias row size = 12 number of columns = 2 index size = 26 
              }
create table "dbmaster".route_alias 
  (
    route_id integer not null ,
    route_alias_code char(8) not null 
  );

revoke all on "dbmaster".route_alias from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imprtal row size = 26 number of columns = 5 index size = 
              42 }
create table "dbmaster".feed_imprtal 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    route_code char(8) not null ,
    route_alias char(8) not null ,
    csv_row smallint
  );

revoke all on "dbmaster".feed_imprtal from "public" as "dbmaster";

{ TABLE "dbmaster".pthelp row size = 507 number of columns = 3 index size = 29 }
create table "dbmaster".pthelp 
  (
    langcd char(5) not null ,
    mesgcd smallint not null ,
    narrative char(500)
  );

revoke all on "dbmaster".pthelp from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imphead row size = 710 number of columns = 10 index size 
              = 37 }
create table "dbmaster".feed_imphead 
  (
    txc_pub_id serial not null ,
    operator_id integer not null ,
    txc_pub_time datetime year to second not null ,
    txc_pub_type char(10) not null ,
    txc_csv_name char(32),
    txc_comment char(60),
    txc_file_dir char(80),
    txc_file_list char(500),
    txc_last_load_date datetime year to second,
    txc_version integer not null 
  );

revoke all on "dbmaster".feed_imphead from "public" as "dbmaster";

{ TABLE "dbmaster".feed_impdest row size = 56 number of columns = 5 index size = 
              32 }
create table "dbmaster".feed_impdest 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    destination_code char(6) not null ,
    destination_disp char(40) not null ,
    csv_row smallint
  );

revoke all on "dbmaster".feed_impdest from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imprept row size = 218 number of columns = 6 index size = 
              39 }
create table "dbmaster".feed_imprept 
  (
    txc_pub_id integer,
    operator_id integer,
    txc_rep_seqnum serial not null ,
    txc_rep_line char(200),
    csv_row smallint,
    mesgcd integer
  );

revoke all on "dbmaster".feed_imprept from "public" as "dbmaster";

{ TABLE "dbmaster".feed_impmedi row size = 80 number of columns = 5 index size = 
              36 }
create table "dbmaster".feed_impmedi 
  (
    txc_pub_id integer,
    operator_id integer,
    media_code char(10),
    media_text char(60),
    csv_row smallint
  );

revoke all on "dbmaster".feed_impmedi from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imprtar row size = 75 number of columns = 5 index size = 
              46 }
create table "dbmaster".feed_imprtar 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    area_code char(20) not null ,
    area_desc char(45) not null ,
    csv_row smallint
  );

revoke all on "dbmaster".feed_imprtar from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imploca row size = 140 number of columns = 12 index size 
              = 38 }
create table "dbmaster".feed_imploca 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    loc_desc char(50),
    cifref char(12),
    bay_no char(3),
    location char(12),
    latitude char(15),
    longitude char(15),
    area_code char(15),
    arriving_addon integer,
    exit_addon integer,
    csv_row smallint
  );

revoke all on "dbmaster".feed_imploca from "public" as "dbmaster";

{ TABLE "dbmaster".unit_gps_log row size = 56 number of columns = 7 index size = 
              9 }
create table "dbmaster".unit_gps_log 
  (
    build_id integer,
    msg_type integer,
    time datetime year to second,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".unit_gps_log from "public" as "dbmaster";

{ TABLE "dbmaster".unit_wlan_log row size = 64 number of columns = 7 index size = 
              59 }
create table "dbmaster".unit_wlan_log 
  (
    pub_id integer not null ,
    build_id integer not null ,
    action_time datetime year to second not null ,
    action_value1 char(20) not null ,
    action_value2 char(20),
    action_duration interval hour to second,
    action_size integer
  );

revoke all on "dbmaster".unit_wlan_log from "public" as "dbmaster";

{ TABLE "dbmaster".websrv_sess row size = 34 number of columns = 3 index size = 15 
              }
create table "dbmaster".websrv_sess 
  (
    websrv_code char(10) not null ,
    websrv_desc char(20),
    websrv_nextid integer not null 
  );

revoke all on "dbmaster".websrv_sess from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_bloc row size = 107 number of columns = 15 index size = 149 
              }
create table "dbmaster".tmi_bloc 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    org_unit_code char(10),
    timetable_ver_code char(10),
    veh_schedule_code char(10),
    block_code char(10),
    day_type_code char(7),
    vehicle_type_code char(10),
    vehicle_service_no integer,
    indicator_vetag char(1),
    veh_service_number smallint,
    add_day_code char(10),
    exc_day_code char(10)
  );

revoke all on "dbmaster".tmi_bloc from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_cresc row size = 116 number of columns = 12 index size = 149 
              }
create table "dbmaster".tmi_cresc 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    timetable_ver_code char(10),
    crew_schedule_code char(10),
    day_type_code char(7),
    pegr_code char(10),
    name_description char(30),
    valid_from char(8),
    valid_thru char(8),
    org_unit_code char(10)
  );

revoke all on "dbmaster".tmi_cresc from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_daow row size = 73 number of columns = 5 index size = 15 }
create table "dbmaster".tmi_daow 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    descr char(50)
  );

revoke all on "dbmaster".tmi_daow from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_daty row size = 50 number of columns = 5 index size = 12 }
create table "dbmaster".tmi_daty 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    day_type_code char(7),
    day_type_name char(30)
  );

revoke all on "dbmaster".tmi_daty from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_dest row size = 117 number of columns = 9 index size = 40 
              }
create table "dbmaster".tmi_dest 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10) not null ,
    dest_code char(10),
    dest_code_end char(10),
    name_dest40 char(40),
    name_dest24 char(24),
    name_dest10 char(10)
  );

revoke all on "dbmaster".tmi_dest from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_driv row size = 69 number of columns = 7 index size = 71 }
create table "dbmaster".tmi_driv 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    orun_code char(10),
    driver_code char(6),
    name char(30)
  );

revoke all on "dbmaster".tmi_driv from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_duac row size = 152 number of columns = 19 index size = 56 
              }
create table "dbmaster".tmi_duac 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    timetable_ver_code char(10),
    crew_schedule_code char(10),
    duty_code char(10),
    sequence_in_duty integer,
    duty_activity_type char(4),
    activity_type_code char(10),
    start_time integer,
    end_time integer,
    duty_activity_code char(10),
    place_code_start char(10),
    place_code_end char(10),
    block_code integer,
    description char(30),
    fortify_jo_seq_nr char(2),
    csc_sched_type char(7)
  );

revoke all on "dbmaster".tmi_duac from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_duty row size = 100 number of columns = 12 index size = 52 
              }
create table "dbmaster".tmi_duty 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    timetable_ver_code char(10),
    crew_schedule_code char(10),
    duty_code char(10),
    day_type_code char(7),
    duty_type_code char(10),
    duty_group_code char(10),
    add_day_code char(10),
    exc_day_code char(10)
  );

revoke all on "dbmaster".tmi_duty from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_excday row size = 59 number of columns = 8 index size = 15 
              }
create table "dbmaster".tmi_excday 
  (
    record_type char(10),
    imporexp char(1),
    version_number smallint,
    batch_id integer,
    data_owner_code char(10),
    excday_code char(1),
    name char(1),
    description char(30)
  );

revoke all on "dbmaster".tmi_excday from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_exopday row size = 103 number of columns = 11 index size = 
              73 }
create table "dbmaster".tmi_exopday 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    org_unit_code char(10),
    valid_date char(8),
    operate_as_day_num smallint,
    add_day_code char(10),
    exc_day_code char(10),
    description char(30),
    period_group_code char(10)
  );

revoke all on "dbmaster".tmi_exopday from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_line row size = 198 number of columns = 13 index size = 35 
              }
create table "dbmaster".tmi_line 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    line_num_planning char(5),
    line_num_internal integer,
    line_num_public char(4),
    line_num_vetag integer,
    line_num_vecom integer,
    line_num_combo integer,
    name char(50),
    description_dir_a char(50),
    description_dir_b char(50)
  );

revoke all on "dbmaster".tmi_line from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_lirorunt row size = 85 number of columns = 15 index size = 
              44 }
create table "dbmaster".tmi_lirorunt 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    time_dem_type_code char(10),
    sequence_in_route integer,
    user_stopcode_from char(10),
    user_stopcode_to char(10),
    total_runtime integer,
    drive_time integer,
    expected_delay integer,
    layover_time integer,
    stop_time integer,
    minimum_stop_time integer,
    summarized_runtime integer
  );

revoke all on "dbmaster".tmi_lirorunt from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_opconarea row size = 83 number of columns = 6 index size = 
              40 }
create table "dbmaster".tmi_opconarea 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    opconarea_code char(10),
    name char(50)
  );

revoke all on "dbmaster".tmi_opconarea from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_orun row size = 83 number of columns = 8 index size = 40 }
create table "dbmaster".tmi_orun 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    orun_code char(10),
    name char(30),
    parent_org_unit char(10),
    opcon_area char(10)
  );

revoke all on "dbmaster".tmi_orun from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_pegr row size = 193 number of columns = 8 index size = 40 
              }
create table "dbmaster".tmi_pegr 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    pegr_code char(10),
    name char(30),
    par_perd_group char(10),
    description char(120)
  );

revoke all on "dbmaster".tmi_pegr from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_pergrval row size = 76 number of columns = 10 index size = 
              120 }
create table "dbmaster".tmi_pergrval 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    org_unit_code char(10),
    ttb_year_code char(10),
    valid_from char(8),
    valid_thru char(8),
    day_type_code char(7),
    pegr_code char(10)
  );

revoke all on "dbmaster".tmi_pergrval from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_poininro row size = 80 number of columns = 12 index size = 
              61 }
create table "dbmaster".tmi_poininro 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    line_number char(5),
    direction smallint,
    route_variant_code char(10),
    route_valid_from char(8),
    route_valid_thru char(8),
    user_stop_code char(10),
    sequence_in_route integer,
    con_provider_code char(10)
  );

revoke all on "dbmaster".tmi_poininro from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_rout row size = 160 number of columns = 16 index size = 92 
              }
create table "dbmaster".tmi_rout 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    line_num_planning char(5),
    direction smallint,
    route_type char(4),
    int_route_code char(10),
    route_valid_from char(8),
    place_code_start char(10),
    place_code_end char(10),
    dest_code_end char(10),
    con_prov_code char(10),
    user_stop_code_via char(10),
    name_of_route char(50),
    route_valid_thru char(8)
  );

revoke all on "dbmaster".tmi_rout from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_stoppoint row size = 419 number of columns = 30 index size 
              = 48 }
create table "dbmaster".tmi_stoppoint 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    user_stop_code char(10),
    stop_valid_from char(8),
    town char(30),
    name char(50),
    is_stop_point char(1),
    is_embarking char(1),
    is_disembarking char(1),
    user_stoparea_code char(10),
    stop_site_code char(2),
    stop_description char(50),
    stop_length char(3),
    stop_valid_thru char(8),
    stop_zone_number integer,
    stop_seqnum_inzone integer,
    place_code char(10),
    point_usage_code char(10),
    latitude char(15),
    longitude char(15),
    rds_x_coordinate integer,
    rds_y_coordinate integer,
    rds_z_coordinate integer,
    geographic_note char(40),
    pos_win_radius integer,
    coord_precision char(4),
    pass_inf_unit integer,
    remarks char(100)
  );

revoke all on "dbmaster".tmi_stoppoint from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_timedty row size = 129 number of columns = 18 index size = 
              155 }
create table "dbmaster".tmi_timedty 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    time_dem_type_code char(10),
    pegr_code char(10),
    day_type_code char(7),
    add_day_type_code char(6),
    line_num_planning char(5),
    direction smallint,
    int_route_code char(10),
    route_valid_from char(8),
    place_code_start char(10),
    place_code_end char(10),
    start_time char(6),
    end_time char(6),
    time_dem_val_fr char(8),
    time_dem_val_to char(8)
  );

revoke all on "dbmaster".tmi_timedty from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_tive row size = 89 number of columns = 9 index size = 60 }
create table "dbmaster".tmi_tive 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    org_unit_code char(10),
    timetable_ver_code char(10),
    name_desc char(30),
    valid_from char(8),
    valid_thru char(8)
  );

revoke all on "dbmaster".tmi_tive from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_veh row size = 90 number of columns = 11 index size = 74 }
create table "dbmaster".tmi_veh 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    orun_code char(10),
    registration_num integer,
    vehicle_type char(10),
    indicator_vetag char(1),
    licence_nr char(8),
    kar_modem_adress integer,
    description char(30)
  );

revoke all on "dbmaster".tmi_veh from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_vejo row size = 201 number of columns = 29 index size = 128 
              }
create table "dbmaster".tmi_vejo 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    org_unit_code char(10),
    timetable_ver_code char(10),
    veh_schedule_code char(10),
    block_code char(10),
    sequence_in_block integer,
    line_num_planning char(5),
    int_route_code char(10),
    route_valid_from char(8),
    place_code_start char(10),
    place_code_end char(10),
    trip_number integer,
    veh_journey_type char(3),
    fort_seq_number integer,
    pubjrny_valid_from char(8),
    pubjrny_thru char(8),
    timetabled_deptime integer,
    arrival_time integer,
    add_day_code char(10),
    exc_day_code char(10),
    pointnum_begjrny integer,
    pointnum_endjrny integer,
    dest_code_end char(10),
    distance integer,
    vehjrny_lyvr_time integer,
    time_dem_code char(10)
  );

revoke all on "dbmaster".tmi_vejo from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_vesc row size = 126 number of columns = 13 index size = 87 
              }
create table "dbmaster".tmi_vesc 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    orun_code char(10),
    timetable_ver_code char(10),
    veh_schedule_code char(10),
    day_type_code char(7),
    pegr_code char(10),
    add_day_code char(10),
    name_description char(30),
    valid_from char(8),
    valid_thru char(8)
  );

revoke all on "dbmaster".tmi_vesc from "public" as "dbmaster";

{ TABLE "dbmaster".tmi_vety row size = 101 number of columns = 11 index size = 15 
              }
create table "dbmaster".tmi_vety 
  (
    record_type char(10),
    version_number smallint,
    imporexp char(1),
    data_owner_code char(10),
    vety_code char(10),
    urvt_code char(10),
    descr char(50),
    length smallint,
    seating_capacity smallint,
    special_place_cap smallint,
    standing_capacity smallint
  );

revoke all on "dbmaster".tmi_vety from "public" as "dbmaster";

{ TABLE "dbmaster".unit_status row size = 125 number of columns = 13 index size = 
              9 }
create table "dbmaster".unit_status 
  (
    build_id integer not null ,
    ip_address char(20),
    conn_status char(1) 
        default 'A',
    message_time datetime year to second,
    message_type integer,
    gps_time datetime year to second,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15),
    gps_dup_ct integer,
    soft_ver char(16),
    sim_no char(20)
  );

revoke all on "dbmaster".unit_status from "public" as "dbmaster";

{ TABLE "dbmaster".unit_status_rt row size = 77 number of columns = 15 index size 
              = 18 }
create table "dbmaster".unit_status_rt 
  (
    build_id integer,
    unit_time datetime year to second,
    etm_time datetime year to second,
    etm_route char(8),
    etm_running_no char(6),
    etm_duty_no char(6),
    etm_trip_no char(6),
    etm_direction char(4),
    etm_status char(1),
    fault_status char(1),
    fault_time datetime year to second,
    route_action integer,
    route_time datetime year to second,
    route_status char(1),
    employee_id integer
  );

revoke all on "dbmaster".unit_status_rt from "public" as "dbmaster";

{ TABLE "dbmaster".message_type row size = 68 number of columns = 6 index size = 
              9 }
create table "dbmaster".message_type 
  (
    msg_type integer,
    description char(30),
    ack_reqd char(1) 
        default '0',
    raise_alert smallint,
    alert_status char(1),
    email_address char(30)
  );

revoke all on "dbmaster".message_type from "public" as "dbmaster";

{ TABLE "dbmaster".route_pattern row size = 39 number of columns = 10 index size 
              = 53 }
create table "dbmaster".route_pattern 
  (
    rpat_id serial not null ,
    route_id integer not null ,
    sequence integer not null ,
    location_id integer not null ,
    direction integer not null ,
    display_order integer not null ,
    display_dir integer not null ,
    grid_x integer,
    grid_y integer,
    node_type char(3)
  );

revoke all on "dbmaster".route_pattern from "public" as "dbmaster";

{ TABLE "dbmaster".route_patt_loc row size = 20 number of columns = 5 index size 
              = 36 }
create table "dbmaster".route_patt_loc 
  (
    rpat_id integer not null ,
    location_id integer not null ,
    loc_from integer,
    loc_to integer,
    branch integer
  );

revoke all on "dbmaster".route_patt_loc from "public" as "dbmaster";

{ TABLE "dbmaster".user_route row size = 12 number of columns = 3 index size = 26 
              }
create table "dbmaster".user_route 
  (
    userid integer not null ,
    operator_id integer,
    route_id integer
  );

revoke all on "dbmaster".user_route from "public" as "dbmaster";

{ TABLE "dbmaster".feed_impstag row size = 34 number of columns = 6 index size = 
              46 }
create table "dbmaster".feed_impstag 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    location_code char(12) not null ,
    route_code char(8),
    stage_id char(4) not null ,
    csv_row smallint
  );

revoke all on "dbmaster".feed_impstag from "public" as "dbmaster";

{ TABLE "dbmaster".database_patch row size = 147 number of columns = 6 index size 
              = 9 }
create table "dbmaster".database_patch 
  (
    patch_no integer,
    bugzilla_no integer,
    patch_desc varchar(120),
    patch_creator char(10),
    patch_created date,
    patch_applied date
  );

revoke all on "dbmaster".database_patch from "public" as "dbmaster";

{ TABLE "dbmaster".unit_bld_media row size = 98 number of columns = 5 index size 
              = 31 }
create table "dbmaster".unit_bld_media 
  (
    build_id integer not null ,
    media_id integer not null ,
    pub_name char(30) not null ,
    target_path char(30),
    pub_dir char(30)
  );

revoke all on "dbmaster".unit_bld_media from "public" as "dbmaster";

{ TABLE "dbmaster".soft_ver_media row size = 68 number of columns = 4 index size 
              = 31 }
create table "dbmaster".soft_ver_media 
  (
    version_id integer not null ,
    media_id integer not null ,
    pub_name char(30) not null ,
    pub_dir char(30)
  );

revoke all on "dbmaster".soft_ver_media from "public" as "dbmaster";

{ TABLE "dbmaster".time_band row size = 24 number of columns = 6 index size = 36 
              }
create table "dbmaster".time_band 
  (
    band_id serial not null ,
    operator_id integer,
    route_id integer,
    evprf_id integer,
    time_low datetime hour to second,
    time_high datetime hour to second
  );

revoke all on "dbmaster".time_band from "public" as "dbmaster";

{ TABLE "dbmaster".loc_interval row size = 20 number of columns = 5 index size = 
              57 }
create table "dbmaster".loc_interval 
  (
    loc_from integer,
    loc_to integer,
    band_id integer,
    travel_time interval hour to second,
    wait_time interval hour to second
  );

revoke all on "dbmaster".loc_interval from "public" as "dbmaster";

{ TABLE "dbmaster".pub_exprept row size = 212 number of columns = 4 index size = 
              31 }
create table "dbmaster".pub_exprept 
  (
    pub_id integer not null ,
    rep_seqnum serial not null ,
    rep_line char(200) not null ,
    mesgcd integer not null ,
    primary key (pub_id,rep_seqnum)  constraint "dbmaster".pk_pub_exprept
  );

revoke all on "dbmaster".pub_exprept from "public" as "dbmaster";

{ TABLE "dbmaster".geogate row size = 72 number of columns = 26 index size = 31 }
create table "dbmaster".geogate 
  (
    route_id integer not null ,
    location_id integer not null ,
    l1_lat_degrees smallint not null ,
    l1_lat_minutes decimal(8,4) not null ,
    l1_lat_heading char(1) not null ,
    l1_long_degrees smallint not null ,
    l1_long_minutes decimal(8,4) not null ,
    l1_long_heading char(1) not null ,
    l2_lat_degrees smallint not null ,
    l2_lat_minutes decimal(8,4) not null ,
    l2_lat_heading char(1) not null ,
    l2_long_degrees smallint not null ,
    l2_long_minutes decimal(8,4) not null ,
    l2_long_heading char(1) not null ,
    r1_lat_degrees smallint,
    r1_lat_minutes decimal(8,4),
    r1_lat_heading char(1),
    r1_long_degrees smallint,
    r1_long_minutes decimal(8,4),
    r1_long_heading char(1),
    r2_lat_degrees smallint,
    r2_lat_minutes decimal(8,4),
    r2_lat_heading char(1),
    r2_long_degrees smallint,
    r2_long_minutes decimal(8,4),
    r2_long_heading char(1)
  );

revoke all on "dbmaster".geogate from "public" as "dbmaster";

{ TABLE "dbmaster".route_location row size = 39 number of columns = 13 index size 
              = 56 }
create table "dbmaster".route_location 
  (
    service_id integer not null ,
    rpat_orderby integer not null ,
    location_id integer not null ,
    profile_id integer not null ,
    travel_time interval hour to second,
    wait_time interval hour to second,
    unload_time interval hour to second,
    bay_number char(3),
    timing_point char(1),
    is_fare_stage char(1),
    activity_flag char(1),
    direction smallint,
    layover_time datetime minute to second
  );

revoke all on "dbmaster".route_location from "public" as "dbmaster";

{ TABLE "dbmaster".despatcher row size = 80 number of columns = 12 index size = 15 
              }
create table "dbmaster".despatcher 
  (
    desp_id serial not null ,
    operator_id integer,
    desp_code char(10) not null ,
    lic_gis smallint,
    lic_veh integer,
    last_ip char(15),
    last_access datetime year to second,
    login_id char(20),
    login_time datetime year to second,
    login_status char(1),
    udp_enabled smallint,
    udp_port smallint
  );

revoke all on "dbmaster".despatcher from "public" as "dbmaster";

{ TABLE "dbmaster".unit_alert row size = 114 number of columns = 14 index size = 
              31 }
create table "dbmaster".unit_alert 
  (
    alert_id serial not null ,
    build_id integer not null ,
    driver_id integer not null ,
    alert_time datetime year to second not null ,
    message_type integer not null ,
    message_id integer not null ,
    message_text char(30),
    priority integer,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15),
    ack_unit integer,
    ack_time datetime year to second
  );

revoke all on "dbmaster".unit_alert from "public" as "dbmaster";

{ TABLE "dbmaster".unit_message row size = 84 number of columns = 10 index size = 
              39 }
create table "dbmaster".unit_message 
  (
    build_id integer not null ,
    network_id integer,
    msg_time datetime year to second not null ,
    msg_no integer not null ,
    ip_address char(20) not null ,
    message_type integer not null ,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".unit_message from "public" as "dbmaster";

{ TABLE "dbmaster".driver_alert row size = 118 number of columns = 15 index size 
              = 31 }
create table "dbmaster".driver_alert 
  (
    alert_id integer,
    build_id integer not null ,
    driver_id integer not null ,
    alert_time datetime year to second not null ,
    message_type integer not null ,
    message_id integer not null ,
    serial_no integer not null ,
    message_text char(30),
    priority integer,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15),
    ack_unit integer,
    ack_time datetime year to second
  );

revoke all on "dbmaster".driver_alert from "public" as "dbmaster";

{ TABLE "dbmaster".unit_alert_arc row size = 114 number of columns = 14 index size 
              = 22 }
create table "dbmaster".unit_alert_arc 
  (
    alert_id serial not null ,
    build_id integer not null ,
    driver_id integer not null ,
    alert_time datetime year to second not null ,
    message_type integer not null ,
    message_id integer not null ,
    message_text char(30),
    priority integer,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15),
    ack_unit integer,
    ack_time datetime year to second
  );

revoke all on "dbmaster".unit_alert_arc from "public" as "dbmaster";

{ TABLE "dbmaster".unit_mess_arc row size = 80 number of columns = 9 index size = 
              39 }
create table "dbmaster".unit_mess_arc 
  (
    build_id integer not null ,
    msg_time datetime year to second not null ,
    msg_no integer not null ,
    ip_address char(20) not null ,
    message_type integer not null ,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".unit_mess_arc from "public" as "dbmaster";

{ TABLE "dbmaster".desp_message row size = 106 number of columns = 8 index size = 
              15 }
create table "dbmaster".desp_message 
  (
    msg_id serial not null ,
    operator_id integer,
    desp_id integer,
    user_id integer,
    msg_lang char(5) not null ,
    msg_type char(1) not null ,
    msg_no integer,
    msg_text char(80)
  );

revoke all on "dbmaster".desp_message from "public" as "dbmaster";

{ TABLE "dbmaster".tt_mod row size = 85 number of columns = 20 index size = 9 }
create table "dbmaster".tt_mod 
  (
    mod_id serial not null ,
    user_id integer not null ,
    desp_id integer,
    mod_time datetime year to second not null ,
    operator_id integer not null ,
    route_id integer,
    evprf_id integer,
    running_no char(5),
    duty_no char(6),
    trip_no char(10),
    pub_ttb_id integer,
    location_id integer,
    wef_date date,
    wet_date date,
    wef_time datetime hour to second,
    wet_time datetime hour to second,
    mod_type char(1) not null ,
    mod_status char(1) not null ,
    alloc_vehicle integer,
    mod_trip_gen smallint,
    primary key (mod_id)  constraint "dbmaster".pk_tt_mod
  );

revoke all on "dbmaster".tt_mod from "public" as "dbmaster";

{ TABLE "dbmaster".tt_mod_trip row size = 8 number of columns = 2 index size = 18 
              }
create table "dbmaster".tt_mod_trip 
  (
    mod_id integer,
    pub_ttb_id integer not null 
  );

revoke all on "dbmaster".tt_mod_trip from "public" as "dbmaster";

{ TABLE "dbmaster".gprs_history row size = 56 number of columns = 8 index size = 
              19 }
create table "dbmaster".gprs_history 
  (
    msg_date date,
    msg_time datetime year to second,
    msg_type integer,
    msg_id integer,
    ip_address char(20),
    build_code char(10),
    project char(3),
    customer char(3)
  );

revoke all on "dbmaster".gprs_history from "public" as "dbmaster";

{ TABLE "dbmaster".t_gps row size = 40 number of columns = 2 index size = 0 }
create table "dbmaster".t_gps 
  (
    bc char(10),
    fl char(30)
  );

revoke all on "dbmaster".t_gps from "public" as "dbmaster";

{ TABLE "dbmaster".vehicle_route row size = 8 number of columns = 2 index size = 
              31 }
create table "dbmaster".vehicle_route 
  (
    vehicle_id integer not null ,
    route_id integer not null ,
    primary key (vehicle_id,route_id)  constraint "dbmaster".pk_veh_route
  );

revoke all on "dbmaster".vehicle_route from "public" as "dbmaster";

{ TABLE "dbmaster".pt_location row size = 17 number of columns = 4 index size = 13 
              }
create table "dbmaster".pt_location 
  (
    pub_ttb_id integer,
    rpat_orderby integer,
    bay_no char(8),
    ability_flag char(1),
    primary key (pub_ttb_id,rpat_orderby) 
  );

revoke all on "dbmaster".pt_location from "public" as "dbmaster";

{ TABLE "dbmaster".omnistop_data row size = 416 number of columns = 44 index size 
              = 32 }
create table "dbmaster".omnistop_data 
  (
    omnistop_code char(10) not null ,
    build_id integer not null ,
    page_no integer not null ,
    line_no integer not null ,
    creation_date datetime year to second not null ,
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    rtpi_type char(4) not null ,
    route_code char(10),
    service_code char(10),
    vehicle_code char(10),
    trip_no char(10),
    arr_time datetime year to second,
    dep_time datetime year to second,
    arr_time_pub datetime year to second,
    dep_time_pub datetime year to second,
    arr_hhmm char(5),
    dep_hhmm char(5),
    arr_hhmm_pub char(5),
    dep_hhmm_pub char(5),
    arr_cdn_min char(20),
    dep_cdn_min char(20),
    arr_cdn_min_pub char(10),
    dep_cdn_min_pub char(10),
    arr_countdown char(30),
    dep_countdown char(30),
    dest1 char(40),
    dest2 char(40),
    bay_no char(5),
    ability_flag char(1),
    ms_schedule_id integer,
    ms_orderby integer,
    ms_arr_time datetime year to second,
    ms_dep_time datetime year to second,
    ms_arr_time_pub datetime year to second,
    ms_dep_time_pub datetime year to second,
    ms_arr_hhmm char(5),
    ms_dep_hhmm char(5),
    ms_arr_hhmm_pub char(5),
    ms_dep_hhmm_pub char(5),
    ms_arr_cdn_min integer,
    ms_dep_cdn_min integer,
    ms_arr_cdn_min_pub integer,
    ms_dep_cdn_min_pub integer,
    primary key (omnistop_code,page_no,line_no) 
  );

revoke all on "dbmaster".omnistop_data from "public" as "dbmaster";

{ TABLE "dbmaster".omnistop_msg row size = 266 number of columns = 2 index size = 
              0 }
create table "dbmaster".omnistop_msg 
  (
    build_code char(10),
    message_text varchar(255)
  );

revoke all on "dbmaster".omnistop_msg from "public" as "dbmaster";

{ TABLE "dbmaster".dest_loc row size = 8 number of columns = 2 index size = 31 }
create table "dbmaster".dest_loc 
  (
    dest_id integer,
    location_id integer,
    primary key (dest_id,location_id) 
  );

revoke all on "dbmaster".dest_loc from "public" as "dbmaster";

{ TABLE "dbmaster".serv_pat_ann row size = 37 number of columns = 6 index size = 
              36 }
create table "dbmaster".serv_pat_ann 
  (
    service_id integer not null ,
    rpat_orderby integer not null ,
    announce_type char(5),
    trigger_type char(5),
    announce_cmd char(15),
    media_id integer,
    primary key (service_id,rpat_orderby,announce_type,trigger_type)  constraint 
              "dbmaster".pk_serv_pat_ann
  );

revoke all on "dbmaster".serv_pat_ann from "public" as "dbmaster";

{ TABLE "dbmaster".license_status row size = 55 number of columns = 5 index size 
              = 0 }
create table "dbmaster".license_status 
  (
    licstatus_id serial not null ,
    module varchar(20),
    unit_id varchar(20),
    lic_status char(1),
    timestamp datetime year to second
  );

revoke all on "dbmaster".license_status from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_param_time row size = 20 number of columns = 5 index size 
              = 30 }
create table "dbmaster".dcd_param_time 
  (
    operator_id integer not null ,
    route_id integer,
    time_low datetime hour to second not null ,
    time_high datetime hour to second not null ,
    time_window integer not null 
  );

revoke all on "dbmaster".dcd_param_time from "public" as "dbmaster";

{ TABLE "dbmaster".desp_audit_trail row size = 74 number of columns = 7 index size 
              = 18 }
create table "dbmaster".desp_audit_trail 
  (
    audit_id serial not null ,
    desp_id integer,
    userid integer,
    action char(20),
    description char(30),
    action_time datetime year to second 
        default current year to second,
    action_result integer,
    primary key (audit_id) 
  );

revoke all on "dbmaster".desp_audit_trail from "public" as "dbmaster";

{ TABLE "dbmaster".station_app row size = 18 number of columns = 3 index size = 31 
              }
create table "dbmaster".station_app 
  (
    station_id integer not null ,
    approach_id integer not null ,
    waypoint_code char(10)
  );

revoke all on "dbmaster".station_app from "public" as "dbmaster";

{ TABLE "dbmaster".station_loc row size = 8 number of columns = 2 index size = 31 
              }
create table "dbmaster".station_loc 
  (
    station_id integer not null ,
    stand_id integer not null 
  );

revoke all on "dbmaster".station_loc from "public" as "dbmaster";

{ TABLE "dbmaster".tmp_obu3 row size = 27 number of columns = 5 index size = 35 }
create table "dbmaster".tmp_obu3 
  (
    dt date not null ,
    vehicle_code char(6) not null ,
    tot_in_archive integer not null ,
    tot_actuals integer not null ,
    actest_perc decimal(16,2) not null 
  );

revoke all on "dbmaster".tmp_obu3 from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_omnistop row size = 58 number of columns = 11 index size = 
              26 }
create table "dbmaster".dcd_omnistop 
  (
    schedule_id serial not null ,
    rpat_orderby integer,
    ms_orderby integer,
    dest_id integer,
    arrival_time datetime year to second,
    departure_time datetime year to second,
    arrival_time_pub datetime year to second,
    departure_time_pub datetime year to second,
    bay_no char(8),
    ability_flag char(1),
    dep_arr_flag char(1),
    primary key (schedule_id,rpat_orderby,dest_id)  constraint "dbmaster".pk_dcd_omnistop
  );

revoke all on "dbmaster".dcd_omnistop from "public" as "dbmaster";

{ TABLE "dbmaster".unit_log row size = 90 number of columns = 9 index size = 26 }
create table "dbmaster".unit_log 
  (
    log_id serial not null ,
    build_id integer,
    log_time datetime year to second not null ,
    message_type integer not null ,
    message_text char(30),
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".unit_log from "public" as "dbmaster";

{ TABLE "dbmaster".x_schedules row size = 4 number of columns = 1 index size = 9 
              }
create table "dbmaster".x_schedules 
  (
    schedule_id integer
  );

revoke all on "dbmaster".x_schedules from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_countdown row size = 76 number of columns = 13 index size 
              = 13 }
create table "dbmaster".dcd_countdown 
  (
    schedule_id integer,
    rpat_orderby integer,
    rtpi_eta_sent datetime year to second,
    rtpi_etd_sent datetime year to second,
    pub_eta_sent datetime year to second,
    pub_etd_sent datetime year to second,
    time_last_sent datetime year to second,
    arr_dep_last_sent char(1),
    sch_rtpi_last_sent char(1),
    eta_last_sent datetime year to second,
    etd_last_sent datetime year to second,
    counted_down smallint,
    time_generated datetime year to second,
    primary key (schedule_id,rpat_orderby)  constraint "dbmaster".pk_dcd_countdown
  );

revoke all on "dbmaster".dcd_countdown from "public" as "dbmaster";

{ TABLE "dbmaster".feed_improut row size = 100 number of columns = 25 index size 
              = 34 }
create table "dbmaster".feed_improut 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    route_code char(8) not null ,
    route_desc char(30) not null ,
    lookahead smallint,
    late_thresh_low smallint,
    late_thresh_high smallint,
    autolate_kickin smallint,
    autolate_freq smallint,
    autolate_addon smallint,
    radio_trip_ceiling smallint,
    radio_stop_ceiling smallint,
    default_image integer,
    default_player integer,
    trip_start_low smallint,
    trip_start_high smallint,
    trip_corr_low smallint,
    trip_corr_high smallint,
    trip_continuation smallint,
    route_matching char(12),
    synchro_first smallint,
    synchro_loc smallint,
    csv_row smallint,
    pub_status char(1),
    rtpi_status char(1),
    primary key (txc_pub_id,operator_id,route_code)  constraint "dbmaster".pk_feed_improut
  );

revoke all on "dbmaster".feed_improut from "public" as "dbmaster";

{ TABLE "dbmaster".feed_impspat row size = 219 number of columns = 18 index size 
              = 68 }
create table "dbmaster".feed_impspat 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    route_code char(8) not null ,
    service_code char(14) not null ,
    start_date date not null ,
    end_date date not null ,
    public_id char(8),
    seqnum integer not null ,
    location char(12) not null ,
    drctn_text char(3),
    stage_id char(4) not null ,
    destination char(10),
    nextstopimage char(10),
    thisstopimage char(10),
    announcements char(100),
    area_code char(15),
    layover_time datetime minute to second,
    csv_row smallint,
    primary key (txc_pub_id,operator_id,route_code,service_code,start_date,end_date,seqnum) 
              
  );

revoke all on "dbmaster".feed_impspat from "public" as "dbmaster";

{ TABLE "dbmaster".feed_imptrip row size = 81 number of columns = 15 index size = 
              88 }
create table "dbmaster".feed_imptrip 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    route_code char(8) not null ,
    service_code char(14) not null ,
    start_date date not null ,
    end_date date not null ,
    vehijourn char(8) not null ,
    change_loc char(12) not null ,
    dayno_start smallint,
    dayno_end smallint,
    duty_number char(6),
    runningno char(5),
    vehicle_type char(5),
    direction char(1),
    csv_row smallint,
    primary key (txc_pub_id,operator_id,route_code,service_code,start_date,end_date,vehijourn,change_loc,dayno_start,dayno_end) 
              
  );

revoke all on "dbmaster".feed_imptrip from "public" as "dbmaster";

{ TABLE "dbmaster".feed_impttb row size = 77 number of columns = 15 index size = 
              93 }
create table "dbmaster".feed_impttb 
  (
    txc_pub_id integer not null ,
    operator_id integer not null ,
    route_code char(8) not null ,
    service_code char(14) not null ,
    start_date date not null ,
    end_date date not null ,
    trip_no char(9) not null ,
    departure_time interval hour to second not null ,
    dayno_start smallint not null ,
    dayno_end smallint not null ,
    location_code char(12) not null ,
    drctn_text char(3),
    wait_time interval hour to second,
    public char(1),
    csv_row smallint,
    primary key (txc_pub_id,operator_id,route_code,service_code,start_date,end_date,trip_no,location_code,departure_time,dayno_start,dayno_end) 
              
  );

revoke all on "dbmaster".feed_impttb from "public" as "dbmaster";

{ TABLE "dbmaster".unit_comments row size = 297 number of columns = 9 index size 
              = 36 }
create table "dbmaster".unit_comments 
  (
    unit_comments_id serial not null ,
    vehicle_id integer,
    location_id integer,
    user_id integer,
    create_time datetime year to second 
        default current year to second,
    active_time datetime year to second 
        default current year to second,
    deactive_time datetime year to second,
    comments varchar(255,10),
    comment_status char(1) 
        default 'A',
    primary key (unit_comments_id) 
  );

revoke all on "dbmaster".unit_comments from "public" as "dbmaster";

{ TABLE "dbmaster".desp_auth row size = 54 number of columns = 2 index size = 9 }
create table "dbmaster".desp_auth 
  (
    user_id integer,
    capability char(50)
  );

revoke all on "dbmaster".desp_auth from "public" as "dbmaster";

{ TABLE "dbmaster".tidyup_priority row size = 24 number of columns = 2 index size 
              = 0 }
create table "dbmaster".tidyup_priority 
  (
    filename char(20) not null ,
    priority integer not null 
  );

revoke all on "dbmaster".tidyup_priority from "public" as "dbmaster";

{ TABLE "dbmaster".active_rt_duty row size = 22 number of columns = 4 index size 
              = 13 }
create table "dbmaster".active_rt_duty 
  (
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    employee_id integer not null ,
    duty_no char(10)
  );

revoke all on "dbmaster".active_rt_duty from "public" as "dbmaster";

{ TABLE "dbmaster".archive_rt_duty row size = 22 number of columns = 4 index size 
              = 13 }
create table "dbmaster".archive_rt_duty 
  (
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    employee_id integer not null ,
    duty_no char(10)
  );

revoke all on "dbmaster".archive_rt_duty from "public" as "dbmaster";

{ TABLE "dbmaster".csv_imprept row size = 218 number of columns = 6 index size = 
              39 }
create table "dbmaster".csv_imprept 
  (
    txc_pub_id integer,
    operator_id integer,
    txc_rep_seqnum serial not null ,
    txc_rep_line char(200),
    csv_row smallint,
    mesgcd integer
  );

revoke all on "dbmaster".csv_imprept from "public" as "dbmaster";

{ TABLE "dbmaster".archive_rt_vp row size = 45 number of columns = 9 index size = 
              32 }
create table "dbmaster".archive_rt_vp 
  (
    schedule_id integer,
    rpat_orderby integer,
    point_type char(1),
    point_code integer,
    movement_code integer,
    priority integer,
    prereg_time datetime year to second,
    arrival_time datetime year to second,
    departure_time datetime year to second
  );

revoke all on "dbmaster".archive_rt_vp from "public" as "dbmaster";

{ TABLE "dbmaster".route_param row size = 62 number of columns = 23 index size = 
              27 }
create table "dbmaster".route_param 
  (
    route_id integer not null ,
    lookahead smallint,
    late_thresh_low smallint,
    late_thresh_high smallint,
    autolate_kickin smallint,
    autolate_freq smallint,
    autolate_addon smallint,
    radio_trip_ceiling smallint,
    radio_stop_ceiling smallint,
    default_image integer,
    default_player integer,
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

revoke all on "dbmaster".route_param from "public" as "dbmaster";

{ TABLE "dbmaster".network row size = 62 number of columns = 4 index size = 9 }
create table "dbmaster".network 
  (
    network_id serial not null ,
    network_code char(8),
    network_desc char(30),
    network_mask char(20),
    primary key (network_id)  constraint "dbmaster".pk_network
  );

revoke all on "dbmaster".network from "public" as "dbmaster";

{ TABLE "dbmaster".unit_status_net row size = 89 number of columns = 11 index size 
              = 31 }
create table "dbmaster".unit_status_net 
  (
    build_id integer not null ,
    network_id integer not null ,
    message_time datetime year to second,
    message_type integer,
    ip_address char(20),
    conn_status char(1) 
        default 'A',
    gps_time datetime year to second,
    gpslat decimal(8,6),
    gpslong decimal(9,6),
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".unit_status_net from "public" as "dbmaster";

{ TABLE "dbmaster".unit_build_log_msg row size = 4 number of columns = 1 index size 
              = 9 }
create table "dbmaster".unit_build_log_msg 
  (
    build_id integer not null 
  );

revoke all on "dbmaster".unit_build_log_msg from "public" as "dbmaster";

{ TABLE "dbmaster".unit_index row size = 187 number of columns = 7 index size = 30 
              }
create table "dbmaster".unit_index 
  (
    pub_id integer not null ,
    build_id integer not null ,
    doc_no serial not null ,
    source_path char(80) not null ,
    dest_dir char(60) not null ,
    dest_name char(20) not null ,
    check_sum char(15) not null 
  );

revoke all on "dbmaster".unit_index from "public" as "dbmaster";

{ TABLE "dbmaster".import_log row size = 41 number of columns = 6 index size = 9 
              }
create table "dbmaster".import_log 
  (
    import_id serial not null ,
    import_time datetime year to second,
    route_code char(20),
    service_wef_date date,
    service_wet_date date,
    incorporate_mode char(1),
    primary key (import_id)  constraint "dbmaster".pk_import_log
  );

revoke all on "dbmaster".import_log from "public" as "dbmaster";

{ TABLE "dbmaster".import_log_report row size = 86 number of columns = 3 index size 
              = 20 }
create table "dbmaster".import_log_report 
  (
    import_id integer not null ,
    line_no smallint,
    line_data char(80)
  );

revoke all on "dbmaster".import_log_report from "public" as "dbmaster";

{ TABLE "dbmaster".autort_sched row size = 74 number of columns = 12 index size = 
              68 }
create table "dbmaster".autort_sched 
  (
    route_id integer not null ,
    duty_no char(10) not null ,
    trip_no char(9) not null ,
    running_no char(9) not null ,
    orun_code char(10),
    scheduled_start datetime year to second,
    direction smallint not null ,
    auto_start_time datetime year to second,
    start_status smallint,
    profile_id integer,
    pub_ttb_id integer,
    operation_date date,
    primary key (pub_ttb_id,operation_date)  constraint "dbmaster".pk_autort_sched
  );

revoke all on "dbmaster".autort_sched from "public" as "dbmaster";

{ TABLE "dbmaster".active_rt_lost row size = 92 number of columns = 9 index size 
              = 13 }
create table "dbmaster".active_rt_lost 
  (
    schedule_id integer not null ,
    rpat_orderby integer not null ,
    predict_orderby integer,
    between_angle decimal(16),
    betweenness decimal(16),
    dist_to1 decimal(16),
    dist_to2 decimal(16),
    dist_avg decimal(16),
    vehicle_gps char(30)
  );

revoke all on "dbmaster".active_rt_lost from "public" as "dbmaster";

{ TABLE "dbmaster".subscriber row size = 96 number of columns = 5 index size = 18 
              }
create table "dbmaster".subscriber 
  (
    subscriber_id serial not null ,
    subscriber_code char(64),
    user_id integer,
    ip_address char(20),
    gateway_id integer,
    primary key (subscriber_id)  constraint "dbmaster".pk_subscriber
  );

revoke all on "dbmaster".subscriber from "public" as "dbmaster";

{ TABLE "dbmaster".subscription row size = 131 number of columns = 13 index size 
              = 18 }
create table "dbmaster".subscription 
  (
    subscription_id serial not null ,
    subscriber_id integer not null ,
    subscription_type char(10) not null ,
    creation_time datetime year to second not null ,
    start_time datetime year to second,
    end_time datetime year to second,
    subscribed_time datetime year to second,
    update_interval integer not null ,
    max_departures integer,
    display_thresh integer,
    request_id integer,
    disabled char(1),
    subscription_ref char(64),
    primary key (subscription_id)  constraint "dbmaster".pk_subscription
  );

revoke all on "dbmaster".subscription from "public" as "dbmaster";

{ TABLE "dbmaster".subscr_loc row size = 8 number of columns = 2 index size = 31 
              }
create table "dbmaster".subscr_loc 
  (
    subscription_id integer,
    location_id integer,
    primary key (subscription_id,location_id)  constraint "dbmaster".pk_subscr_loc
  );

revoke all on "dbmaster".subscr_loc from "public" as "dbmaster";

{ TABLE "dbmaster".user_loc row size = 8 number of columns = 2 index size = 31 }
create table "dbmaster".user_loc 
  (
    user_id integer,
    location_id integer,
    primary key (user_id,location_id)  constraint "dbmaster".pk_user_loc
  );

revoke all on "dbmaster".user_loc from "public" as "dbmaster";

{ TABLE "dbmaster".aux_countdown row size = 72 number of columns = 11 index size 
              = 18 }
create table "dbmaster".aux_countdown 
  (
    countdown_id serial not null ,
    subscription_id integer,
    location_id integer,
    timestamp datetime year to second,
    service_name char(8),
    trip_no char(8),
    dest_id integer,
    arrival_time datetime year to second,
    departure_time datetime year to second,
    arrival_time_pub datetime year to second,
    departure_time_pub datetime year to second
  );

revoke all on "dbmaster".aux_countdown from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_prediction row size = 58 number of columns = 10 index size 
              = 26 }
create table "dbmaster".dcd_prediction 
  (
    schedule_id integer,
    rpat_orderby integer,
    send_time datetime year to second,
    pred_type char(1),
    display_mode char(1),
    rtpi_eta_sent datetime year to second,
    rtpi_etd_sent datetime year to second,
    pub_eta_sent datetime year to second,
    pub_etd_sent datetime year to second,
    prediction datetime year to second
  );

revoke all on "dbmaster".dcd_prediction from "public" as "dbmaster";

{ TABLE "dbmaster".t_scoot_veh_list row size = 14 number of columns = 2 index size 
              = 15 }
create table "dbmaster".t_scoot_veh_list 
  (
    vehicle_id serial not null ,
    build_code char(10)
  );

revoke all on "dbmaster".t_scoot_veh_list from "public" as "dbmaster";

{ TABLE "dbmaster".rbc_sim_phone_mapping row size = 65 number of columns = 3 index 
              size = 45 }
create table "dbmaster".rbc_sim_phone_mapping 
  (
    phone_no char(15) not null ,
    sim_no char(20) not null ,
    info char(30),
    unique (phone_no)  constraint "dbmaster".u_phone,
    unique (sim_no)  constraint "dbmaster".u_sim
  );

revoke all on "dbmaster".rbc_sim_phone_mapping from "public" as "dbmaster";

{ TABLE "dbmaster".temp_rbc_sims row size = 100 number of columns = 6 index size 
              = 0 }
create table "dbmaster".temp_rbc_sims 
  (
    unit_type char(10) not null ,
    location char(40) not null ,
    build_code char(10),
    last_message datetime year to second,
    sim_no char(20),
    phone_no char(12)
  );

revoke all on "dbmaster".temp_rbc_sims from "public" as "dbmaster";

{ TABLE "dbmaster".gps_pred_loc row size = 14 number of columns = 2 index size = 
              9 }
create table "dbmaster".gps_pred_loc 
  (
    location_id integer,
    pred_type char(10)
  );

revoke all on "dbmaster".gps_pred_loc from "public" as "dbmaster";

{ TABLE "dbmaster".user_vehicle row size = 12 number of columns = 3 index size = 
              26 }
create table "dbmaster".user_vehicle 
  (
    userid integer not null ,
    operator_id integer,
    vehicle_id integer
  );

revoke all on "dbmaster".user_vehicle from "public" as "dbmaster";

{ TABLE "dbmaster".user_build row size = 12 number of columns = 3 index size = 26 
              }
create table "dbmaster".user_build 
  (
    userid integer not null ,
    operator_id integer,
    build_id integer
  );

revoke all on "dbmaster".user_build from "public" as "dbmaster";

{ TABLE "dbmaster".raw_count row size = 24 number of columns = 5 index size = 9 }
create table "dbmaster".raw_count 
  (
    count_id serial not null ,
    counter_id integer not null ,
    count_time datetime year to second not null ,
    in integer not null ,
    out integer not null 
  );

revoke all on "dbmaster".raw_count from "public" as "dbmaster";

{ TABLE "dbmaster".gprscov_sent row size = 44 number of columns = 5 index size = 
              19 }
create table "dbmaster".gprscov_sent 
  (
    send_time datetime hour to second,
    build_code char(10),
    msg_id integer,
    gpslat char(13),
    gpslng char(13)
  );

revoke all on "dbmaster".gprscov_sent from "public" as "dbmaster";

{ TABLE "dbmaster".gprscov_recd row size = 18 number of columns = 3 index size = 
              19 }
create table "dbmaster".gprscov_recd 
  (
    recd_time datetime hour to second,
    msg_id integer,
    build_code char(10)
  );

revoke all on "dbmaster".gprscov_recd from "public" as "dbmaster";

{ TABLE "dbmaster".gprscov_comp row size = 58 number of columns = 7 index size = 
              0 }
create table "dbmaster".gprscov_comp 
  (
    vehicle_code char(10),
    build_code char(10),
    msg_id integer,
    sent_time datetime hour to second,
    recd_time datetime hour to second,
    gpslat char(13),
    gpslng char(13)
  );

revoke all on "dbmaster".gprscov_comp from "public" as "dbmaster";

{ TABLE "dbmaster".gprscov_bunch row size = 108 number of columns = 8 index size 
              = 0 }
create table "dbmaster".gprscov_bunch 
  (
    vehicle_code char(10),
    build_code char(10),
    dup_ct integer,
    recd datetime hour to second,
    minlat char(20),
    minlong char(20),
    maxlat char(20),
    maxlong char(20)
  );

revoke all on "dbmaster".gprscov_bunch from "public" as "dbmaster";

{ TABLE "dbmaster".passenger_count row size = 52 number of columns = 13 index size 
              = 22 }
create table "dbmaster".passenger_count 
  (
    count_id serial not null ,
    count_date date not null ,
    count_hour integer not null ,
    count_min integer not null ,
    vehicle_id integer,
    schedule_id integer,
    rpat_orderby integer,
    pre_arr_in integer,
    pre_arr_out integer,
    in integer not null ,
    out integer not null ,
    occupancy integer not null ,
    prev_occupancy integer not null ,
    primary key (count_id)  constraint "dbmaster".pk_passenger_count
  );

revoke all on "dbmaster".passenger_count from "public" as "dbmaster";

{ TABLE "dbmaster".stop_cleardowns_tmp row size = 16 number of columns = 3 index 
              size = 0 }
create table "dbmaster".stop_cleardowns_tmp 
  (
    build_id integer,
    vehicle_id integer,
    clear_time datetime year to second
  );

revoke all on "dbmaster".stop_cleardowns_tmp from "public" as "dbmaster";

{ TABLE "dbmaster".ih_performance row size = 156 number of columns = 33 index size 
              = 21 }
create table "dbmaster".ih_performance 
  (
    operator_id integer,
    dayno date,
    running_no char(8),
    minveh integer,
    maxveh integer,
    firstroute integer,
    map char(24),
    scheduled integer,
    sched_trackedrb integer,
    sched_untrackedrb integer,
    tracked integer,
    untracked integer,
    tracked_well integer,
    tracked_badly integer,
    start_gap integer,
    mid_gap integer,
    end_gap integer,
    nr_late_start integer,
    nr_skipped integer,
    nr_droppped_off integer,
    nr_tripentry integer,
    nr_etm integer,
    nr_gprs integer,
    nr_calibration integer,
    nr_gps integer,
    nr_offroute integer,
    nr_other integer,
    msg_received integer,
    msg_offroute integer,
    msg_etmok integer,
    msg_etmfail integer,
    msg_heartbeat integer,
    msg_other integer
  );

revoke all on "dbmaster".ih_performance from "public" as "dbmaster";

{ TABLE "dbmaster".unit_message_hr row size = 38 number of columns = 10 index size 
              = 15 }
create table "dbmaster".unit_message_hr 
  (
    build_id integer,
    dayno date,
    day_hour datetime hour to hour,
    msg_rec integer,
    msg_offroute integer,
    msg_onroute integer,
    msg_etm integer,
    msg_etmfail integer,
    msg_heartbeat integer,
    msg_other integer
  );

revoke all on "dbmaster".unit_message_hr from "public" as "dbmaster";

{ TABLE "dbmaster".dcd_param row size = 80 number of columns = 22 index size = 0 
              }
create table "dbmaster".dcd_param 
  (
    dcd_id serial not null ,
    level integer not null ,
    operator_id integer,
    route_id integer,
    location_id integer,
    build_id integer,
    day_of_week integer,
    wef_time datetime hour to second,
    wet_time datetime hour to second,
    max_arrivals integer,
    max_dest_arrivals integer,
    autort_preempt datetime hour to second,
    pred_layover char(1),
    pred_pub_after integer,
    disp_pub_after integer,
    display_window integer,
    countdown_dep_arr char(1),
    delivery_mode char(5) 
        default 'RCA',
    update_thresh_low integer,
    update_thresh_high integer,
    loop_sleep integer,
    disabled char(1)
  );

revoke all on "dbmaster".dcd_param from "public" as "dbmaster";

{ TABLE "dbmaster".tlp_request row size = 69 number of columns = 11 index size = 
              17 }
create table "dbmaster".tlp_request 
  (
    vehicle_id integer,
    trigger_time datetime year to second,
    trigger_type integer,
    locationcode integer,
    protocol integer,
    announcement integer,
    movementcode integer,
    priority integer,
    lateness integer,
    gpslat_str char(14),
    gpslong_str char(15)
  );

revoke all on "dbmaster".tlp_request from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_route row size = 14 number of columns = 4 index size 
              = 0 }
create table "dbmaster".playlist_route 
  (
    program_id integer,
    global_default smallint,
    operator_id integer,
    route_id integer
  );

revoke all on "dbmaster".playlist_route from "public" as "dbmaster";

{ TABLE "dbmaster".unit_status_sign row size = 23 number of columns = 5 index size 
              = 9 }
create table "dbmaster".unit_status_sign 
  (
    build_id integer not null ,
    update_status char(1) not null ,
    channel_number integer,
    power_status integer,
    firmware_version char(10)
  );

revoke all on "dbmaster".unit_status_sign from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_media_type row size = 30 number of columns = 2 index 
              size = 20 }
create table "dbmaster".playlist_media_type 
  (
    media_type_code char(15),
    media_type_desc char(15)
  );

revoke all on "dbmaster".playlist_media_type from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_program row size = 28 number of columns = 3 index size 
              = 9 }
create table "dbmaster".playlist_program 
  (
    program_id serial not null ,
    program_name char(20) not null ,
    media_id integer
  );

revoke all on "dbmaster".playlist_program from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_block row size = 58 number of columns = 4 index size 
              = 18 }
create table "dbmaster".playlist_block 
  (
    block_id serial not null ,
    program_id integer,
    block_name char(20) not null ,
    block_desc char(30)
  );

revoke all on "dbmaster".playlist_block from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_slot row size = 12 number of columns = 3 index size = 
              22 }
create table "dbmaster".playlist_slot 
  (
    block_id integer,
    sequence integer not null ,
    duration integer
  );

revoke all on "dbmaster".playlist_slot from "public" as "dbmaster";

{ TABLE "dbmaster".playlist row size = 68 number of columns = 10 index size = 18 
              }
create table "dbmaster".playlist 
  (
    playlist_id serial not null ,
    include_playlist integer,
    block_id integer,
    layer integer not null ,
    screen_metric char(8),
    screen_from_x decimal(16),
    screen_from_y decimal(16),
    screen_to_x decimal(16),
    screen_to_y decimal(16),
    condition_block integer
  );

revoke all on "dbmaster".playlist from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_media row size = 300 number of columns = 7 index size 
              = 22 }
create table "dbmaster".playlist_media 
  (
    playlist_id integer,
    sequence integer not null ,
    media_type char(8) not null ,
    feed_name char(20),
    feed_content varchar(255,30),
    feed_media integer,
    attribute_block integer
  );

revoke all on "dbmaster".playlist_media from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_condition row size = 168 number of columns = 13 index 
              size = 22 }
create table "dbmaster".playlist_condition 
  (
    playlist_id integer,
    sequence integer not null ,
    condition_type char(20) not null ,
    condition_char char(80),
    condition_int integer,
    condition_dec1 decimal(16),
    condition_dec2 decimal(16),
    condition_dec3 decimal(16),
    condition_dec4 decimal(16),
    from_date date,
    to_date date,
    from_time datetime hour to second,
    to_time datetime hour to second
  );

revoke all on "dbmaster".playlist_condition from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_attrib_type row size = 40 number of columns = 2 index 
              size = 25 }
create table "dbmaster".playlist_attrib_type 
  (
    attrib_type char(20) not null ,
    attrib_desc char(20)
  );

revoke all on "dbmaster".playlist_attrib_type from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_attrib_value row size = 100 number of columns = 2 index 
              size = 105 }
create table "dbmaster".playlist_attrib_value 
  (
    attrib_type char(20) not null ,
    attrib_value char(80)
  );

revoke all on "dbmaster".playlist_attrib_value from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_attrib row size = 108 number of columns = 4 index size 
              = 80 }
create table "dbmaster".playlist_attrib 
  (
    playlist_id integer,
    sequence integer not null ,
    attrib_type char(20) not null ,
    attrib_value char(80)
  );

revoke all on "dbmaster".playlist_attrib from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_cond_type row size = 40 number of columns = 2 index size 
              = 25 }
create table "dbmaster".playlist_cond_type 
  (
    cond_type char(20) not null ,
    cond_desc char(20)
  );

revoke all on "dbmaster".playlist_cond_type from "public" as "dbmaster";

{ TABLE "dbmaster".playlist_cond_value row size = 40 number of columns = 2 index 
              size = 45 }
create table "dbmaster".playlist_cond_value 
  (
    cond_type char(20) not null ,
    cond_value char(20)
  );

revoke all on "dbmaster".playlist_cond_value from "public" as "dbmaster";

{ TABLE "dbmaster".post_code row size = 43 number of columns = 6 index size = 47 
              }
create table "dbmaster".post_code 
  (
    post_code_id serial not null ,
    post_code char(7) not null ,
    easting float,
    northing float,
    latitude float,
    longitude float,
    unique (post_code) ,
    primary key (post_code_id) 
  );

revoke all on "dbmaster".post_code from "public" as "dbmaster";

{ TABLE "dbmaster".ttt row size = 20 number of columns = 1 index size = 0 }
create table "dbmaster".ttt 
  (
    col1 char(20)
  );

revoke all on "dbmaster".ttt from "public" as "dbmaster";

{ TABLE "dbmaster".location_media row size = 16 number of columns = 4 index size 
              = 13 }
create table "dbmaster".location_media 
  (
    location_id integer not null ,
    route_id integer,
    next_stop_audio integer,
    this_stop_audio integer
  );

revoke all on "dbmaster".location_media from "public" as "dbmaster";

{ TABLE "dbmaster".login_audit row size = 166 number of columns = 6 index size = 
              13 }
create table "dbmaster".login_audit 
  (
    login_time datetime year to second not null ,
    in_out char(1) not null ,
    login_name char(20),
    success char(1) not null ,
    source_ip char(16),
    source_url char(120)
  );

revoke all on "dbmaster".login_audit from "public" as "dbmaster";

{ TABLE "dbmaster".items row size = 264 number of columns = 5 index size = 70 }
create table "dbmaster".items 
  (
    name varchar(64) not null ,
    type integer not null ,
    description varchar(64),
    bizrule varchar(64),
    data varchar(64),
    primary key (name) 
  );

revoke all on "dbmaster".items from "public" as "dbmaster";

{ TABLE "dbmaster".assignments row size = 260 number of columns = 4 index size = 
              135 }
create table "dbmaster".assignments 
  (
    itemname varchar(64) not null ,
    userid varchar(64) not null ,
    bizrule varchar(64),
    data varchar(64),
    primary key (itemname,userid) 
  );

revoke all on "dbmaster".assignments from "public" as "dbmaster";

{ TABLE "dbmaster".itemchildren row size = 130 number of columns = 2 index size = 
              275 }
create table "dbmaster".itemchildren 
  (
    parent varchar(64) not null ,
    child varchar(64) not null ,
    primary key (parent,child) 
  );

revoke all on "dbmaster".itemchildren from "public" as "dbmaster";

{ TABLE "dbmaster".ih_performance_route row size = 184 number of columns = 40 index 
              size = 25 }
create table "dbmaster".ih_performance_route 
  (
    operator_id integer,
    dayno date,
    running_no char(8),
    minveh integer,
    maxveh integer,
    route_id integer,
    map char(24),
    scheduled integer,
    sched_trackedrb integer,
    sched_untrackedrb integer,
    tracked integer,
    untracked integer,
    tracked_little integer,
    tracked_well integer,
    tracked_badly integer,
    start_gap integer,
    mid_gap integer,
    end_gap integer,
    nr_late_start integer,
    nr_skipped integer,
    nr_droppped_off integer,
    nr_tripentry integer,
    nr_etm integer,
    nr_gprs integer,
    nr_calibration integer,
    nr_gps integer,
    nr_offroute integer,
    nr_other integer,
    msg_received integer,
    msg_offroute integer,
    msg_etmok integer,
    msg_etmfail integer,
    msg_heartbeat integer,
    msg_other integer,
    veh1_ood_by integer,
    veh1_no_wlan_for integer,
    veh1_no_gprs_since integer,
    veh2_ood_by integer,
    veh2_no_wlan_for integer,
    veh2_no_gprs_since integer
  );

revoke all on "dbmaster".ih_performance_route from "public" as "dbmaster";

{ TABLE "dbmaster".stop row size = 9018 number of columns = 44 index size = 818 }
create table "dbmaster".stop 
  (
    stop_id serial not null ,
    atco_code varchar(255) not null ,
    naptan_code varchar(255),
    plate_code varchar(255),
    cleardown_code varchar(255),
    common_name varchar(255),
    common_name_lang varchar(255),
    short_common_name varchar(255),
    short_common_name_lang varchar(255),
    landmark varchar(255),
    landmark_lang varchar(255),
    street varchar(255),
    street_lang varchar(255),
    crossing varchar(255),
    crossing_lang varchar(255),
    indicator varchar(255),
    indicator_lang varchar(255),
    bearing varchar(255),
    nptg_locality_code varchar(255),
    locality_name varchar(255),
    parent_locality_name varchar(255),
    grand_parent_locality_name varchar(255),
    town varchar(255),
    town_lang varchar(255),
    suburb varchar(255),
    suburb_lang varchar(255),
    locality_centre "informix".boolean,
    grid_type varchar(255),
    easting float,
    northing float,
    longitude float,
    latitude float,
    stop_type varchar(255),
    bus_stop_type varchar(255),
    timing_status varchar(255),
    default_wait_time varchar(255),
    notes varchar(255),
    notes_lang varchar(255),
    administrative_area_code varchar(255),
    creation_datetime datetime year to second,
    modification_datetime datetime year to second,
    revision_number integer,
    modification varchar(255),
    status varchar(255),
    unique (atco_code) ,
    primary key (stop_id) 
  );

revoke all on "dbmaster".stop from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_menu row size = 34 number of columns = 2 index size = 
              0 }
create table "dbmaster".iconnex_menu 
  (
    menu_id serial not null ,
    menu_name char(30) not null 
  );

revoke all on "dbmaster".iconnex_menu from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_param row size = 60 number of columns = 2 index size = 
              35 }
create table "dbmaster".iconnex_param 
  (
    param_name char(30) not null ,
    param_value char(30) not null ,
    primary key (param_name) 
  );

revoke all on "dbmaster".iconnex_param from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_application row size = 337 number of columns = 11 index 
              size = 0 }
create table "dbmaster".iconnex_application 
  (
    app_id serial not null ,
    app_name char(30) not null ,
    app_url char(255) not null ,
    has_map integer not null ,
    has_grid integer not null ,
    has_line integer not null ,
    has_chart integer not null ,
    has_report integer not null ,
    autorun integer not null ,
    refresh_xml char(20),
    autorefresh integer not null 
  );

revoke all on "dbmaster".iconnex_application from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_menuitem row size = 12 number of columns = 3 index size 
              = 13 }
create table "dbmaster".iconnex_menuitem 
  (
    menu_id integer,
    menu_no integer not null ,
    app_id integer not null ,
    primary key (menu_id,menu_no) 
  );

revoke all on "dbmaster".iconnex_menuitem from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_menu_user row size = 24 number of columns = 6 index size 
              = 9 }
create table "dbmaster".iconnex_menu_user 
  (
    user_id integer not null ,
    menu_id integer,
    app_id integer,
    autorun integer,
    show_accordion integer,
    show_buttons integer
  );

revoke all on "dbmaster".iconnex_menu_user from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_workspace row size = 48 number of columns = 3 index size 
              = 18 }
create table "dbmaster".iconnex_workspace 
  (
    wsp_id serial not null ,
    user_id integer,
    wsp_name char(40),
    primary key (wsp_id) 
  );

revoke all on "dbmaster".iconnex_workspace from "public" as "dbmaster";

{ TABLE "dbmaster".iconnex_wsp_item row size = 264 number of columns = 3 index size 
              = 22 }
create table "dbmaster".iconnex_wsp_item 
  (
    wsp_id integer not null ,
    wsp_item_no integer not null ,
    session_params varchar(255),
    primary key (wsp_id,wsp_item_no) 
  );

revoke all on "dbmaster".iconnex_wsp_item from "public" as "dbmaster";


grant select on "dbmaster".archive_rt to "centrole" as "dbmaster";
grant update on "dbmaster".archive_rt to "centrole" as "dbmaster";
grant insert on "dbmaster".archive_rt to "centrole" as "dbmaster";
grant delete on "dbmaster".archive_rt to "centrole" as "dbmaster";
grant select on "dbmaster".media to "centrole" as "dbmaster";
grant update on "dbmaster".media to "centrole" as "dbmaster";
grant insert on "dbmaster".media to "centrole" as "dbmaster";
grant delete on "dbmaster".media to "centrole" as "dbmaster";
grant select on "dbmaster".archive_rt_loc to "centrole" as "dbmaster";
grant update on "dbmaster".archive_rt_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".archive_rt_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".archive_rt_loc to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt to "centrole" as "dbmaster";
grant update on "dbmaster".active_rt to "centrole" as "dbmaster";
grant insert on "dbmaster".active_rt to "centrole" as "dbmaster";
grant delete on "dbmaster".active_rt to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt_loc to "centrole" as "dbmaster";
grant update on "dbmaster".active_rt_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".active_rt_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".active_rt_loc to "centrole" as "dbmaster";
grant select on "dbmaster".autoroute_time to "centrole" as "dbmaster";
grant update on "dbmaster".autoroute_time to "centrole" as "dbmaster";
grant insert on "dbmaster".autoroute_time to "centrole" as "dbmaster";
grant delete on "dbmaster".autoroute_time to "centrole" as "dbmaster";
grant select on "dbmaster".autort_config to "centrole" as "dbmaster";
grant update on "dbmaster".autort_config to "centrole" as "dbmaster";
grant insert on "dbmaster".autort_config to "centrole" as "dbmaster";
grant delete on "dbmaster".autort_config to "centrole" as "dbmaster";
grant select on "dbmaster".autort_profile to "centrole" as "dbmaster";
grant update on "dbmaster".autort_profile to "centrole" as "dbmaster";
grant insert on "dbmaster".autort_profile to "centrole" as "dbmaster";
grant delete on "dbmaster".autort_profile to "centrole" as "dbmaster";
grant select on "dbmaster".cent_user to "centrole" as "dbmaster";
grant update on "dbmaster".cent_user to "centrole" as "dbmaster";
grant insert on "dbmaster".cent_user to "centrole" as "dbmaster";
grant delete on "dbmaster".cent_user to "centrole" as "dbmaster";
grant select on "dbmaster".component to "centrole" as "dbmaster";
grant update on "dbmaster".component to "centrole" as "dbmaster";
grant insert on "dbmaster".component to "centrole" as "dbmaster";
grant delete on "dbmaster".component to "centrole" as "dbmaster";
grant select on "dbmaster".dcd_message to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_message to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_message to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_message to "centrole" as "dbmaster";
grant select on "dbmaster".dcd_message_loc to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_message_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_message_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_message_loc to "centrole" as "dbmaster";
grant select on "dbmaster".destination to "centrole" as "dbmaster";
grant update on "dbmaster".destination to "centrole" as "dbmaster";
grant insert on "dbmaster".destination to "centrole" as "dbmaster";
grant delete on "dbmaster".destination to "centrole" as "dbmaster";
grant select on "dbmaster".display_point to "centrole" as "dbmaster";
grant update on "dbmaster".display_point to "centrole" as "dbmaster";
grant insert on "dbmaster".display_point to "centrole" as "dbmaster";
grant delete on "dbmaster".display_point to "centrole" as "dbmaster";
grant select on "dbmaster".district to "centrole" as "dbmaster";
grant update on "dbmaster".district to "centrole" as "dbmaster";
grant insert on "dbmaster".district to "centrole" as "dbmaster";
grant delete on "dbmaster".district to "centrole" as "dbmaster";
grant select on "dbmaster".driver_message to "centrole" as "dbmaster";
grant update on "dbmaster".driver_message to "centrole" as "dbmaster";
grant insert on "dbmaster".driver_message to "centrole" as "dbmaster";
grant delete on "dbmaster".driver_message to "centrole" as "dbmaster";
grant select on "dbmaster".employee to "centrole" as "dbmaster";
grant update on "dbmaster".employee to "centrole" as "dbmaster";
grant insert on "dbmaster".employee to "centrole" as "dbmaster";
grant delete on "dbmaster".employee to "centrole" as "dbmaster";
grant select on "dbmaster".event_pattern to "centrole" as "dbmaster";
grant update on "dbmaster".event_pattern to "centrole" as "dbmaster";
grant insert on "dbmaster".event_pattern to "centrole" as "dbmaster";
grant delete on "dbmaster".event_pattern to "centrole" as "dbmaster";
grant select on "dbmaster".event_profile to "centrole" as "dbmaster";
grant update on "dbmaster".event_profile to "centrole" as "dbmaster";
grant insert on "dbmaster".event_profile to "centrole" as "dbmaster";
grant delete on "dbmaster".event_profile to "centrole" as "dbmaster";
grant select on "dbmaster".fare_stage to "centrole" as "dbmaster";
grant update on "dbmaster".fare_stage to "centrole" as "dbmaster";
grant insert on "dbmaster".fare_stage to "centrole" as "dbmaster";
grant delete on "dbmaster".fare_stage to "centrole" as "dbmaster";
grant select on "dbmaster".feed_format to "centrole" as "dbmaster";
grant update on "dbmaster".feed_format to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_format to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_format to "centrole" as "dbmaster";
grant select on "dbmaster".feed_history to "centrole" as "dbmaster";
grant update on "dbmaster".feed_history to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_history to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_history to "centrole" as "dbmaster";
grant select on "dbmaster".feed_type to "centrole" as "dbmaster";
grant update on "dbmaster".feed_type to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_type to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_type to "centrole" as "dbmaster";
grant select on "dbmaster".gprs_mapping to "centrole" as "dbmaster";
grant update on "dbmaster".gprs_mapping to "centrole" as "dbmaster";
grant insert on "dbmaster".gprs_mapping to "centrole" as "dbmaster";
grant delete on "dbmaster".gprs_mapping to "centrole" as "dbmaster";
grant select on "dbmaster".html_tag to "centrole" as "dbmaster";
grant update on "dbmaster".html_tag to "centrole" as "dbmaster";
grant insert on "dbmaster".html_tag to "centrole" as "dbmaster";
grant delete on "dbmaster".html_tag to "centrole" as "dbmaster";
grant select on "dbmaster".junction to "centrole" as "dbmaster";
grant update on "dbmaster".junction to "centrole" as "dbmaster";
grant insert on "dbmaster".junction to "centrole" as "dbmaster";
grant delete on "dbmaster".junction to "centrole" as "dbmaster";
grant select on "dbmaster".junction_aprch to "centrole" as "dbmaster";
grant update on "dbmaster".junction_aprch to "centrole" as "dbmaster";
grant insert on "dbmaster".junction_aprch to "centrole" as "dbmaster";
grant delete on "dbmaster".junction_aprch to "centrole" as "dbmaster";
grant select on "dbmaster".junction_reg to "centrole" as "dbmaster";
grant update on "dbmaster".junction_reg to "centrole" as "dbmaster";
grant insert on "dbmaster".junction_reg to "centrole" as "dbmaster";
grant delete on "dbmaster".junction_reg to "centrole" as "dbmaster";
grant select on "dbmaster".junction_xtrav to "centrole" as "dbmaster";
grant update on "dbmaster".junction_xtrav to "centrole" as "dbmaster";
grant insert on "dbmaster".junction_xtrav to "centrole" as "dbmaster";
grant delete on "dbmaster".junction_xtrav to "centrole" as "dbmaster";
grant select on "dbmaster".layover to "centrole" as "dbmaster";
grant update on "dbmaster".layover to "centrole" as "dbmaster";
grant insert on "dbmaster".layover to "centrole" as "dbmaster";
grant delete on "dbmaster".layover to "centrole" as "dbmaster";
grant select on "dbmaster".location to "centrole" as "dbmaster";
grant update on "dbmaster".location to "centrole" as "dbmaster";
grant insert on "dbmaster".location to "centrole" as "dbmaster";
grant delete on "dbmaster".location to "centrole" as "dbmaster";
grant select on "dbmaster".location_type to "centrole" as "dbmaster";
grant update on "dbmaster".location_type to "centrole" as "dbmaster";
grant insert on "dbmaster".location_type to "centrole" as "dbmaster";
grant delete on "dbmaster".location_type to "centrole" as "dbmaster";
grant select on "dbmaster".media_format to "centrole" as "dbmaster";
grant update on "dbmaster".media_format to "centrole" as "dbmaster";
grant insert on "dbmaster".media_format to "centrole" as "dbmaster";
grant delete on "dbmaster".media_format to "centrole" as "dbmaster";
grant select on "dbmaster".media_type to "centrole" as "dbmaster";
grant update on "dbmaster".media_type to "centrole" as "dbmaster";
grant insert on "dbmaster".media_type to "centrole" as "dbmaster";
grant delete on "dbmaster".media_type to "centrole" as "dbmaster";
grant select on "dbmaster".msg_to_veh to "centrole" as "dbmaster";
grant update on "dbmaster".msg_to_veh to "centrole" as "dbmaster";
grant insert on "dbmaster".msg_to_veh to "centrole" as "dbmaster";
grant delete on "dbmaster".msg_to_veh to "centrole" as "dbmaster";
grant select on "dbmaster".opconarea to "centrole" as "dbmaster";
grant update on "dbmaster".opconarea to "centrole" as "dbmaster";
grant insert on "dbmaster".opconarea to "centrole" as "dbmaster";
grant delete on "dbmaster".opconarea to "centrole" as "dbmaster";
grant select on "dbmaster".operator to "centrole" as "dbmaster";
grant update on "dbmaster".operator to "centrole" as "dbmaster";
grant insert on "dbmaster".operator to "centrole" as "dbmaster";
grant delete on "dbmaster".operator to "centrole" as "dbmaster";
grant select on "dbmaster".operator_media to "centrole" as "dbmaster";
grant update on "dbmaster".operator_media to "centrole" as "dbmaster";
grant insert on "dbmaster".operator_media to "centrole" as "dbmaster";
grant delete on "dbmaster".operator_media to "centrole" as "dbmaster";
grant select on "dbmaster".orgunit to "centrole" as "dbmaster";
grant update on "dbmaster".orgunit to "centrole" as "dbmaster";
grant insert on "dbmaster".orgunit to "centrole" as "dbmaster";
grant delete on "dbmaster".orgunit to "centrole" as "dbmaster";
grant select on "dbmaster".parameter to "centrole" as "dbmaster";
grant update on "dbmaster".parameter to "centrole" as "dbmaster";
grant insert on "dbmaster".parameter to "centrole" as "dbmaster";
grant delete on "dbmaster".parameter to "centrole" as "dbmaster";
grant select on "dbmaster".period_group to "centrole" as "dbmaster";
grant update on "dbmaster".period_group to "centrole" as "dbmaster";
grant insert on "dbmaster".period_group to "centrole" as "dbmaster";
grant delete on "dbmaster".period_group to "centrole" as "dbmaster";
grant select on "dbmaster".place to "centrole" as "dbmaster";
grant update on "dbmaster".place to "centrole" as "dbmaster";
grant insert on "dbmaster".place to "centrole" as "dbmaster";
grant delete on "dbmaster".place to "centrole" as "dbmaster";
grant select on "dbmaster".pt_duty to "centrole" as "dbmaster";
grant update on "dbmaster".pt_duty to "centrole" as "dbmaster";
grant insert on "dbmaster".pt_duty to "centrole" as "dbmaster";
grant delete on "dbmaster".pt_duty to "centrole" as "dbmaster";
grant select on "dbmaster".ptactn to "centrole" as "dbmaster";
grant update on "dbmaster".ptactn to "centrole" as "dbmaster";
grant insert on "dbmaster".ptactn to "centrole" as "dbmaster";
grant delete on "dbmaster".ptactn to "centrole" as "dbmaster";
grant select on "dbmaster".ptdict to "centrole" as "dbmaster";
grant update on "dbmaster".ptdict to "centrole" as "dbmaster";
grant insert on "dbmaster".ptdict to "centrole" as "dbmaster";
grant delete on "dbmaster".ptdict to "centrole" as "dbmaster";
grant select on "dbmaster".ptdmac to "centrole" as "dbmaster";
grant update on "dbmaster".ptdmac to "centrole" as "dbmaster";
grant insert on "dbmaster".ptdmac to "centrole" as "dbmaster";
grant delete on "dbmaster".ptdmac to "centrole" as "dbmaster";
grant select on "dbmaster".ptdmns to "centrole" as "dbmaster";
grant update on "dbmaster".ptdmns to "centrole" as "dbmaster";
grant insert on "dbmaster".ptdmns to "centrole" as "dbmaster";
grant delete on "dbmaster".ptdmns to "centrole" as "dbmaster";
grant select on "dbmaster".ptengl to "centrole" as "dbmaster";
grant update on "dbmaster".ptengl to "centrole" as "dbmaster";
grant insert on "dbmaster".ptengl to "centrole" as "dbmaster";
grant delete on "dbmaster".ptengl to "centrole" as "dbmaster";
grant select on "dbmaster".ptfgms to "centrole" as "dbmaster";
grant update on "dbmaster".ptfgms to "centrole" as "dbmaster";
grant insert on "dbmaster".ptfgms to "centrole" as "dbmaster";
grant delete on "dbmaster".ptfgms to "centrole" as "dbmaster";
grant select on "dbmaster".ptfgop to "centrole" as "dbmaster";
grant update on "dbmaster".ptfgop to "centrole" as "dbmaster";
grant insert on "dbmaster".ptfgop to "centrole" as "dbmaster";
grant delete on "dbmaster".ptfgop to "centrole" as "dbmaster";
grant select on "dbmaster".ptfgtx to "centrole" as "dbmaster";
grant update on "dbmaster".ptfgtx to "centrole" as "dbmaster";
grant insert on "dbmaster".ptfgtx to "centrole" as "dbmaster";
grant delete on "dbmaster".ptfgtx to "centrole" as "dbmaster";
grant select on "dbmaster".ptgprm to "centrole" as "dbmaster";
grant update on "dbmaster".ptgprm to "centrole" as "dbmaster";
grant insert on "dbmaster".ptgprm to "centrole" as "dbmaster";
grant delete on "dbmaster".ptgprm to "centrole" as "dbmaster";
grant select on "dbmaster".ptgrup to "centrole" as "dbmaster";
grant update on "dbmaster".ptgrup to "centrole" as "dbmaster";
grant insert on "dbmaster".ptgrup to "centrole" as "dbmaster";
grant delete on "dbmaster".ptgrup to "centrole" as "dbmaster";
grant select on "dbmaster".ptgrus to "centrole" as "dbmaster";
grant update on "dbmaster".ptgrus to "centrole" as "dbmaster";
grant insert on "dbmaster".ptgrus to "centrole" as "dbmaster";
grant delete on "dbmaster".ptgrus to "centrole" as "dbmaster";
grant select on "dbmaster".ptlang to "centrole" as "dbmaster";
grant update on "dbmaster".ptlang to "centrole" as "dbmaster";
grant insert on "dbmaster".ptlang to "centrole" as "dbmaster";
grant delete on "dbmaster".ptlang to "centrole" as "dbmaster";
grant select on "dbmaster".ptmndt to "centrole" as "dbmaster";
grant update on "dbmaster".ptmndt to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmndt to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmndt to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnhd to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnhd to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnhd to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnhd to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnlg to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnlg to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnlg to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnlg to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnms to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnms to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnms to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnms to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnop to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnop to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnop to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnop to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnpc to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnpc to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnpc to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnpc to "centrole" as "dbmaster";
grant select on "dbmaster".ptmnpt to "centrole" as "dbmaster";
grant update on "dbmaster".ptmnpt to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmnpt to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmnpt to "centrole" as "dbmaster";
grant select on "dbmaster".ptmsgs to "centrole" as "dbmaster";
grant update on "dbmaster".ptmsgs to "centrole" as "dbmaster";
grant insert on "dbmaster".ptmsgs to "centrole" as "dbmaster";
grant delete on "dbmaster".ptmsgs to "centrole" as "dbmaster";
grant select on "dbmaster".pttabs to "centrole" as "dbmaster";
grant update on "dbmaster".pttabs to "centrole" as "dbmaster";
grant insert on "dbmaster".pttabs to "centrole" as "dbmaster";
grant delete on "dbmaster".pttabs to "centrole" as "dbmaster";
grant select on "dbmaster".ptuprm to "centrole" as "dbmaster";
grant update on "dbmaster".ptuprm to "centrole" as "dbmaster";
grant insert on "dbmaster".ptuprm to "centrole" as "dbmaster";
grant delete on "dbmaster".ptuprm to "centrole" as "dbmaster";
grant select on "dbmaster".ptuspr to "centrole" as "dbmaster";
grant update on "dbmaster".ptuspr to "centrole" as "dbmaster";
grant insert on "dbmaster".ptuspr to "centrole" as "dbmaster";
grant delete on "dbmaster".ptuspr to "centrole" as "dbmaster";
grant select on "dbmaster".publication to "centrole" as "dbmaster";
grant update on "dbmaster".publication to "centrole" as "dbmaster";
grant insert on "dbmaster".publication to "centrole" as "dbmaster";
grant delete on "dbmaster".publication to "centrole" as "dbmaster";
grant select on "dbmaster".publish_time to "centrole" as "dbmaster";
grant update on "dbmaster".publish_time to "centrole" as "dbmaster";
grant insert on "dbmaster".publish_time to "centrole" as "dbmaster";
grant delete on "dbmaster".publish_time to "centrole" as "dbmaster";
grant select on "dbmaster".publish_tt to "centrole" as "dbmaster";
grant update on "dbmaster".publish_tt to "centrole" as "dbmaster";
grant insert on "dbmaster".publish_tt to "centrole" as "dbmaster";
grant delete on "dbmaster".publish_tt to "centrole" as "dbmaster";
grant select on "dbmaster".registration to "centrole" as "dbmaster";
grant update on "dbmaster".registration to "centrole" as "dbmaster";
grant insert on "dbmaster".registration to "centrole" as "dbmaster";
grant delete on "dbmaster".registration to "centrole" as "dbmaster";
grant select on "dbmaster".revision_hist to "centrole" as "dbmaster";
grant update on "dbmaster".revision_hist to "centrole" as "dbmaster";
grant insert on "dbmaster".revision_hist to "centrole" as "dbmaster";
grant delete on "dbmaster".revision_hist to "centrole" as "dbmaster";
grant select on "dbmaster".revision_type to "centrole" as "dbmaster";
grant update on "dbmaster".revision_type to "centrole" as "dbmaster";
grant insert on "dbmaster".revision_type to "centrole" as "dbmaster";
grant delete on "dbmaster".revision_type to "centrole" as "dbmaster";
grant select on "dbmaster".road to "centrole" as "dbmaster";
grant update on "dbmaster".road to "centrole" as "dbmaster";
grant insert on "dbmaster".road to "centrole" as "dbmaster";
grant delete on "dbmaster".road to "centrole" as "dbmaster";
grant select on "dbmaster".route to "centrole" as "dbmaster";
grant update on "dbmaster".route to "centrole" as "dbmaster";
grant insert on "dbmaster".route to "centrole" as "dbmaster";
grant delete on "dbmaster".route to "centrole" as "dbmaster";
grant select on "dbmaster".route_area to "centrole" as "dbmaster";
grant update on "dbmaster".route_area to "centrole" as "dbmaster";
grant insert on "dbmaster".route_area to "centrole" as "dbmaster";
grant delete on "dbmaster".route_area to "centrole" as "dbmaster";
grant select on "dbmaster".route_loc_avg to "centrole" as "dbmaster";
grant update on "dbmaster".route_loc_avg to "centrole" as "dbmaster";
grant insert on "dbmaster".route_loc_avg to "centrole" as "dbmaster";
grant delete on "dbmaster".route_loc_avg to "centrole" as "dbmaster";
grant select on "dbmaster".route_message to "centrole" as "dbmaster";
grant update on "dbmaster".route_message to "centrole" as "dbmaster";
grant insert on "dbmaster".route_message to "centrole" as "dbmaster";
grant delete on "dbmaster".route_message to "centrole" as "dbmaster";
grant select on "dbmaster".route_profile to "centrole" as "dbmaster";
grant update on "dbmaster".route_profile to "centrole" as "dbmaster";
grant insert on "dbmaster".route_profile to "centrole" as "dbmaster";
grant delete on "dbmaster".route_profile to "centrole" as "dbmaster";
grant select on "dbmaster".serv_pat_media to "centrole" as "dbmaster";
grant update on "dbmaster".serv_pat_media to "centrole" as "dbmaster";
grant insert on "dbmaster".serv_pat_media to "centrole" as "dbmaster";
grant delete on "dbmaster".serv_pat_media to "centrole" as "dbmaster";
grant select on "dbmaster".service to "centrole" as "dbmaster";
grant update on "dbmaster".service to "centrole" as "dbmaster";
grant insert on "dbmaster".service to "centrole" as "dbmaster";
grant delete on "dbmaster".service to "centrole" as "dbmaster";
grant select on "dbmaster".service_link to "centrole" as "dbmaster";
grant update on "dbmaster".service_link to "centrole" as "dbmaster";
grant insert on "dbmaster".service_link to "centrole" as "dbmaster";
grant delete on "dbmaster".service_link to "centrole" as "dbmaster";
grant select on "dbmaster".service_patt to "centrole" as "dbmaster";
grant update on "dbmaster".service_patt to "centrole" as "dbmaster";
grant insert on "dbmaster".service_patt to "centrole" as "dbmaster";
grant delete on "dbmaster".service_patt to "centrole" as "dbmaster";
grant select on "dbmaster".servlink_xtrav to "centrole" as "dbmaster";
grant update on "dbmaster".servlink_xtrav to "centrole" as "dbmaster";
grant insert on "dbmaster".servlink_xtrav to "centrole" as "dbmaster";
grant delete on "dbmaster".servlink_xtrav to "centrole" as "dbmaster";
grant select on "dbmaster".sign_info to "centrole" as "dbmaster";
grant update on "dbmaster".sign_info to "centrole" as "dbmaster";
grant insert on "dbmaster".sign_info to "centrole" as "dbmaster";
grant delete on "dbmaster".sign_info to "centrole" as "dbmaster";
grant select on "dbmaster".signal_prot to "centrole" as "dbmaster";
grant update on "dbmaster".signal_prot to "centrole" as "dbmaster";
grant insert on "dbmaster".signal_prot to "centrole" as "dbmaster";
grant delete on "dbmaster".signal_prot to "centrole" as "dbmaster";
grant select on "dbmaster".soft_ver to "centrole" as "dbmaster";
grant update on "dbmaster".soft_ver to "centrole" as "dbmaster";
grant insert on "dbmaster".soft_ver to "centrole" as "dbmaster";
grant delete on "dbmaster".soft_ver to "centrole" as "dbmaster";
grant select on "dbmaster".special_op to "centrole" as "dbmaster";
grant update on "dbmaster".special_op to "centrole" as "dbmaster";
grant insert on "dbmaster".special_op to "centrole" as "dbmaster";
grant delete on "dbmaster".special_op to "centrole" as "dbmaster";
grant select on "dbmaster".system_key to "centrole" as "dbmaster";
grant update on "dbmaster".system_key to "centrole" as "dbmaster";
grant insert on "dbmaster".system_key to "centrole" as "dbmaster";
grant delete on "dbmaster".system_key to "centrole" as "dbmaster";
grant select on "dbmaster".tlp_adjust to "centrole" as "dbmaster";
grant update on "dbmaster".tlp_adjust to "centrole" as "dbmaster";
grant insert on "dbmaster".tlp_adjust to "centrole" as "dbmaster";
grant delete on "dbmaster".tlp_adjust to "centrole" as "dbmaster";
grant select on "dbmaster".tlp_sched_adh to "centrole" as "dbmaster";
grant update on "dbmaster".tlp_sched_adh to "centrole" as "dbmaster";
grant insert on "dbmaster".tlp_sched_adh to "centrole" as "dbmaster";
grant delete on "dbmaster".tlp_sched_adh to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_place to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_place to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_place to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_place to "centrole" as "dbmaster";
grant select on "dbmaster".town to "centrole" as "dbmaster";
grant update on "dbmaster".town to "centrole" as "dbmaster";
grant insert on "dbmaster".town to "centrole" as "dbmaster";
grant delete on "dbmaster".town to "centrole" as "dbmaster";
grant select on "dbmaster".trigger_type to "centrole" as "dbmaster";
grant update on "dbmaster".trigger_type to "centrole" as "dbmaster";
grant insert on "dbmaster".trigger_type to "centrole" as "dbmaster";
grant delete on "dbmaster".trigger_type to "centrole" as "dbmaster";
grant select on "dbmaster".unit_build to "centrole" as "dbmaster";
grant update on "dbmaster".unit_build to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_build to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_build to "centrole" as "dbmaster";
grant select on "dbmaster".unit_cfg_type to "centrole" as "dbmaster";
grant update on "dbmaster".unit_cfg_type to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_cfg_type to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_cfg_type to "centrole" as "dbmaster";
grant select on "dbmaster".unit_history to "centrole" as "dbmaster";
grant update on "dbmaster".unit_history to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_history to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_history to "centrole" as "dbmaster";
grant select on "dbmaster".unit_param to "centrole" as "dbmaster";
grant update on "dbmaster".unit_param to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_param to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_param to "centrole" as "dbmaster";
grant select on "dbmaster".unit_publish to "centrole" as "dbmaster";
grant update on "dbmaster".unit_publish to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_publish to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_publish to "centrole" as "dbmaster";
grant select on "dbmaster".unit_reply to "centrole" as "dbmaster";
grant update on "dbmaster".unit_reply to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_reply to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_reply to "centrole" as "dbmaster";
grant select on "dbmaster".vehicle to "centrole" as "dbmaster";
grant update on "dbmaster".vehicle to "centrole" as "dbmaster";
grant insert on "dbmaster".vehicle to "centrole" as "dbmaster";
grant delete on "dbmaster".vehicle to "centrole" as "dbmaster";
grant select on "dbmaster".vehicle_type to "centrole" as "dbmaster";
grant update on "dbmaster".vehicle_type to "centrole" as "dbmaster";
grant insert on "dbmaster".vehicle_type to "centrole" as "dbmaster";
grant delete on "dbmaster".vehicle_type to "centrole" as "dbmaster";
grant select on "dbmaster".event to "centrole" as "dbmaster";
grant update on "dbmaster".event to "centrole" as "dbmaster";
grant insert on "dbmaster".event to "centrole" as "dbmaster";
grant delete on "dbmaster".event to "centrole" as "dbmaster";
grant select on "dbmaster".pergrval to "centrole" as "dbmaster";
grant update on "dbmaster".pergrval to "centrole" as "dbmaster";
grant insert on "dbmaster".pergrval to "centrole" as "dbmaster";
grant delete on "dbmaster".pergrval to "centrole" as "dbmaster";
grant select on "dbmaster".unit_log_hist to "centrole" as "dbmaster";
grant update on "dbmaster".unit_log_hist to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_log_hist to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_log_hist to "centrole" as "dbmaster";
grant select on "dbmaster".route_alias to "centrole" as "dbmaster";
grant update on "dbmaster".route_alias to "centrole" as "dbmaster";
grant insert on "dbmaster".route_alias to "centrole" as "dbmaster";
grant delete on "dbmaster".route_alias to "centrole" as "dbmaster";
grant select on "dbmaster".route_alias to "public" as "dbmaster";
grant update on "dbmaster".route_alias to "public" as "dbmaster";
grant insert on "dbmaster".route_alias to "public" as "dbmaster";
grant delete on "dbmaster".route_alias to "public" as "dbmaster";
grant index on "dbmaster".route_alias to "public" as "dbmaster";
grant select on "dbmaster".feed_imprtal to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imprtal to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imprtal to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imprtal to "centrole" as "dbmaster";
grant select on "dbmaster".pthelp to "centrole" as "dbmaster";
grant update on "dbmaster".pthelp to "centrole" as "dbmaster";
grant insert on "dbmaster".pthelp to "centrole" as "dbmaster";
grant delete on "dbmaster".pthelp to "centrole" as "dbmaster";
grant select on "dbmaster".feed_imphead to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imphead to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imphead to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imphead to "centrole" as "dbmaster";
grant select on "dbmaster".feed_impdest to "centrole" as "dbmaster";
grant update on "dbmaster".feed_impdest to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_impdest to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_impdest to "centrole" as "dbmaster";
grant select on "dbmaster".feed_imprept to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imprept to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imprept to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imprept to "centrole" as "dbmaster";
grant select on "dbmaster".feed_impmedi to "centrole" as "dbmaster";
grant update on "dbmaster".feed_impmedi to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_impmedi to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_impmedi to "centrole" as "dbmaster";
grant select on "dbmaster".feed_imprtar to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imprtar to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imprtar to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imprtar to "centrole" as "dbmaster";
grant select on "dbmaster".feed_imploca to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imploca to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imploca to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imploca to "centrole" as "dbmaster";
grant select on "dbmaster".unit_wlan_log to "centrole" as "dbmaster";
grant update on "dbmaster".unit_wlan_log to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_wlan_log to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_wlan_log to "centrole" as "dbmaster";
grant select on "dbmaster".websrv_sess to "centrole" as "dbmaster";
grant update on "dbmaster".websrv_sess to "centrole" as "dbmaster";
grant insert on "dbmaster".websrv_sess to "centrole" as "dbmaster";
grant delete on "dbmaster".websrv_sess to "centrole" as "dbmaster";
grant select on "dbmaster".websrv_sess to "public" as "dbmaster";
grant update on "dbmaster".websrv_sess to "public" as "dbmaster";
grant insert on "dbmaster".websrv_sess to "public" as "dbmaster";
grant delete on "dbmaster".websrv_sess to "public" as "dbmaster";
grant index on "dbmaster".websrv_sess to "public" as "dbmaster";
grant select on "dbmaster".tmi_bloc to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_bloc to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_bloc to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_bloc to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_cresc to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_cresc to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_cresc to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_cresc to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_daow to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_daow to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_daow to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_daow to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_daty to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_daty to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_daty to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_daty to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_dest to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_dest to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_dest to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_dest to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_driv to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_driv to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_driv to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_driv to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_duac to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_duac to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_duac to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_duac to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_duty to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_duty to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_duty to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_duty to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_exopday to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_exopday to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_exopday to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_exopday to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_line to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_line to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_line to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_line to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_lirorunt to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_lirorunt to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_lirorunt to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_lirorunt to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_opconarea to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_opconarea to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_opconarea to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_opconarea to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_orun to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_orun to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_orun to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_orun to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_pegr to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_pegr to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_pegr to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_pegr to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_pergrval to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_pergrval to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_pergrval to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_pergrval to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_poininro to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_poininro to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_poininro to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_poininro to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_rout to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_rout to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_rout to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_rout to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_stoppoint to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_stoppoint to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_stoppoint to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_stoppoint to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_timedty to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_timedty to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_timedty to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_timedty to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_tive to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_tive to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_tive to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_tive to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_veh to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_veh to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_veh to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_veh to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_vejo to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_vejo to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_vejo to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_vejo to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_vesc to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_vesc to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_vesc to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_vesc to "centrole" as "dbmaster";
grant select on "dbmaster".tmi_vety to "centrole" as "dbmaster";
grant update on "dbmaster".tmi_vety to "centrole" as "dbmaster";
grant insert on "dbmaster".tmi_vety to "centrole" as "dbmaster";
grant delete on "dbmaster".tmi_vety to "centrole" as "dbmaster";
grant select on "dbmaster".unit_status to "centrole" as "dbmaster";
grant update on "dbmaster".unit_status to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_status to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_status to "centrole" as "dbmaster";
grant select on "dbmaster".unit_status_rt to "public" as "dbmaster";
grant update on "dbmaster".unit_status_rt to "public" as "dbmaster";
grant insert on "dbmaster".unit_status_rt to "public" as "dbmaster";
grant delete on "dbmaster".unit_status_rt to "public" as "dbmaster";
grant index on "dbmaster".unit_status_rt to "public" as "dbmaster";
grant select on "dbmaster".message_type to "public" as "dbmaster";
grant update on "dbmaster".message_type to "public" as "dbmaster";
grant insert on "dbmaster".message_type to "public" as "dbmaster";
grant delete on "dbmaster".message_type to "public" as "dbmaster";
grant index on "dbmaster".message_type to "public" as "dbmaster";
grant select on "dbmaster".route_pattern to "centrole" as "dbmaster";
grant update on "dbmaster".route_pattern to "centrole" as "dbmaster";
grant insert on "dbmaster".route_pattern to "centrole" as "dbmaster";
grant delete on "dbmaster".route_pattern to "centrole" as "dbmaster";
grant select on "dbmaster".route_pattern to "public" as "dbmaster";
grant update on "dbmaster".route_pattern to "public" as "dbmaster";
grant insert on "dbmaster".route_pattern to "public" as "dbmaster";
grant delete on "dbmaster".route_pattern to "public" as "dbmaster";
grant index on "dbmaster".route_pattern to "public" as "dbmaster";
grant select on "dbmaster".route_patt_loc to "centrole" as "dbmaster";
grant update on "dbmaster".route_patt_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".route_patt_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".route_patt_loc to "centrole" as "dbmaster";
grant select on "dbmaster".route_patt_loc to "public" as "dbmaster";
grant update on "dbmaster".route_patt_loc to "public" as "dbmaster";
grant insert on "dbmaster".route_patt_loc to "public" as "dbmaster";
grant delete on "dbmaster".route_patt_loc to "public" as "dbmaster";
grant index on "dbmaster".route_patt_loc to "public" as "dbmaster";
grant select on "dbmaster".user_route to "centrole" as "dbmaster";
grant update on "dbmaster".user_route to "centrole" as "dbmaster";
grant insert on "dbmaster".user_route to "centrole" as "dbmaster";
grant delete on "dbmaster".user_route to "centrole" as "dbmaster";
grant select on "dbmaster".feed_impstag to "centrole" as "dbmaster";
grant update on "dbmaster".feed_impstag to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_impstag to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_impstag to "centrole" as "dbmaster";
grant select on "dbmaster".database_patch to "centrole" as "dbmaster";
grant update on "dbmaster".database_patch to "centrole" as "dbmaster";
grant insert on "dbmaster".database_patch to "centrole" as "dbmaster";
grant delete on "dbmaster".database_patch to "centrole" as "dbmaster";
grant select on "dbmaster".database_patch to "public" as "dbmaster";
grant update on "dbmaster".database_patch to "public" as "dbmaster";
grant insert on "dbmaster".database_patch to "public" as "dbmaster";
grant delete on "dbmaster".database_patch to "public" as "dbmaster";
grant index on "dbmaster".database_patch to "public" as "dbmaster";
grant select on "dbmaster".unit_bld_media to "centrole" as "dbmaster";
grant update on "dbmaster".unit_bld_media to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_bld_media to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_bld_media to "centrole" as "dbmaster";
grant select on "dbmaster".soft_ver_media to "centrole" as "dbmaster";
grant update on "dbmaster".soft_ver_media to "centrole" as "dbmaster";
grant insert on "dbmaster".soft_ver_media to "centrole" as "dbmaster";
grant delete on "dbmaster".soft_ver_media to "centrole" as "dbmaster";
grant select on "dbmaster".time_band to "public" as "dbmaster";
grant update on "dbmaster".time_band to "public" as "dbmaster";
grant insert on "dbmaster".time_band to "public" as "dbmaster";
grant delete on "dbmaster".time_band to "public" as "dbmaster";
grant index on "dbmaster".time_band to "public" as "dbmaster";
grant select on "dbmaster".loc_interval to "public" as "dbmaster";
grant update on "dbmaster".loc_interval to "public" as "dbmaster";
grant insert on "dbmaster".loc_interval to "public" as "dbmaster";
grant delete on "dbmaster".loc_interval to "public" as "dbmaster";
grant index on "dbmaster".loc_interval to "public" as "dbmaster";
grant select on "dbmaster".pub_exprept to "centrole" as "dbmaster";
grant update on "dbmaster".pub_exprept to "centrole" as "dbmaster";
grant insert on "dbmaster".pub_exprept to "centrole" as "dbmaster";
grant delete on "dbmaster".pub_exprept to "centrole" as "dbmaster";
grant select on "dbmaster".geogate to "centrole" as "dbmaster";
grant update on "dbmaster".geogate to "centrole" as "dbmaster";
grant insert on "dbmaster".geogate to "centrole" as "dbmaster";
grant delete on "dbmaster".geogate to "centrole" as "dbmaster";
grant select on "dbmaster".route_location to "centrole" as "dbmaster";
grant update on "dbmaster".route_location to "centrole" as "dbmaster";
grant insert on "dbmaster".route_location to "centrole" as "dbmaster";
grant delete on "dbmaster".route_location to "centrole" as "dbmaster";
grant select on "dbmaster".despatcher to "centrole" as "dbmaster";
grant update on "dbmaster".despatcher to "centrole" as "dbmaster";
grant insert on "dbmaster".despatcher to "centrole" as "dbmaster";
grant delete on "dbmaster".despatcher to "centrole" as "dbmaster";
grant select on "dbmaster".unit_alert to "public" as "dbmaster";
grant update on "dbmaster".unit_alert to "public" as "dbmaster";
grant insert on "dbmaster".unit_alert to "public" as "dbmaster";
grant delete on "dbmaster".unit_alert to "public" as "dbmaster";
grant index on "dbmaster".unit_alert to "public" as "dbmaster";
grant select on "dbmaster".unit_message to "public" as "dbmaster";
grant update on "dbmaster".unit_message to "public" as "dbmaster";
grant insert on "dbmaster".unit_message to "public" as "dbmaster";
grant delete on "dbmaster".unit_message to "public" as "dbmaster";
grant index on "dbmaster".unit_message to "public" as "dbmaster";
grant select on "dbmaster".unit_alert_arc to "centrole" as "dbmaster";
grant update on "dbmaster".unit_alert_arc to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_alert_arc to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_alert_arc to "centrole" as "dbmaster";
grant select on "dbmaster".unit_mess_arc to "centrole" as "dbmaster";
grant update on "dbmaster".unit_mess_arc to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_mess_arc to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_mess_arc to "centrole" as "dbmaster";
grant select on "dbmaster".desp_message to "centrole" as "dbmaster";
grant update on "dbmaster".desp_message to "centrole" as "dbmaster";
grant insert on "dbmaster".desp_message to "centrole" as "dbmaster";
grant delete on "dbmaster".desp_message to "centrole" as "dbmaster";
grant select on "dbmaster".tt_mod to "centrole" as "dbmaster";
grant update on "dbmaster".tt_mod to "centrole" as "dbmaster";
grant insert on "dbmaster".tt_mod to "centrole" as "dbmaster";
grant delete on "dbmaster".tt_mod to "centrole" as "dbmaster";
grant select on "dbmaster".tt_mod_trip to "centrole" as "dbmaster";
grant update on "dbmaster".tt_mod_trip to "centrole" as "dbmaster";
grant insert on "dbmaster".tt_mod_trip to "centrole" as "dbmaster";
grant delete on "dbmaster".tt_mod_trip to "centrole" as "dbmaster";
grant select on "dbmaster".tt_mod_trip to "public" as "dbmaster";
grant update on "dbmaster".tt_mod_trip to "public" as "dbmaster";
grant insert on "dbmaster".tt_mod_trip to "public" as "dbmaster";
grant delete on "dbmaster".tt_mod_trip to "public" as "dbmaster";
grant index on "dbmaster".tt_mod_trip to "public" as "dbmaster";
grant select on "dbmaster".gprs_history to "public" as "dbmaster";
grant update on "dbmaster".gprs_history to "public" as "dbmaster";
grant insert on "dbmaster".gprs_history to "public" as "dbmaster";
grant delete on "dbmaster".gprs_history to "public" as "dbmaster";
grant index on "dbmaster".gprs_history to "public" as "dbmaster";
grant select on "dbmaster".t_gps to "public" as "dbmaster";
grant update on "dbmaster".t_gps to "public" as "dbmaster";
grant insert on "dbmaster".t_gps to "public" as "dbmaster";
grant delete on "dbmaster".t_gps to "public" as "dbmaster";
grant index on "dbmaster".t_gps to "public" as "dbmaster";
grant select on "dbmaster".vehicle_route to "centrole" as "dbmaster";
grant update on "dbmaster".vehicle_route to "centrole" as "dbmaster";
grant insert on "dbmaster".vehicle_route to "centrole" as "dbmaster";
grant delete on "dbmaster".vehicle_route to "centrole" as "dbmaster";
grant select on "dbmaster".pt_location to "centrole" as "dbmaster";
grant update on "dbmaster".pt_location to "centrole" as "dbmaster";
grant insert on "dbmaster".pt_location to "centrole" as "dbmaster";
grant delete on "dbmaster".pt_location to "centrole" as "dbmaster";
grant select on "dbmaster".omnistop_data to "centrole" as "dbmaster";
grant update on "dbmaster".omnistop_data to "centrole" as "dbmaster";
grant insert on "dbmaster".omnistop_data to "centrole" as "dbmaster";
grant delete on "dbmaster".omnistop_data to "centrole" as "dbmaster";
grant select on "dbmaster".omnistop_msg to "centrole" as "dbmaster";
grant update on "dbmaster".omnistop_msg to "centrole" as "dbmaster";
grant insert on "dbmaster".omnistop_msg to "centrole" as "dbmaster";
grant delete on "dbmaster".omnistop_msg to "centrole" as "dbmaster";
grant select on "dbmaster".dest_loc to "centrole" as "dbmaster";
grant update on "dbmaster".dest_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".dest_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".dest_loc to "centrole" as "dbmaster";
grant select on "dbmaster".serv_pat_ann to "centrole" as "dbmaster";
grant update on "dbmaster".serv_pat_ann to "centrole" as "dbmaster";
grant insert on "dbmaster".serv_pat_ann to "centrole" as "dbmaster";
grant delete on "dbmaster".serv_pat_ann to "centrole" as "dbmaster";
grant select on "dbmaster".license_status to "centrole" as "dbmaster";
grant update on "dbmaster".license_status to "centrole" as "dbmaster";
grant insert on "dbmaster".license_status to "centrole" as "dbmaster";
grant delete on "dbmaster".license_status to "centrole" as "dbmaster";
grant select on "dbmaster".dcd_param_time to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_param_time to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_param_time to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_param_time to "centrole" as "dbmaster";
grant select on "dbmaster".desp_audit_trail to "centrole" as "dbmaster";
grant update on "dbmaster".desp_audit_trail to "centrole" as "dbmaster";
grant insert on "dbmaster".desp_audit_trail to "centrole" as "dbmaster";
grant delete on "dbmaster".desp_audit_trail to "centrole" as "dbmaster";
grant select on "dbmaster".station_app to "centrole" as "dbmaster";
grant update on "dbmaster".station_app to "centrole" as "dbmaster";
grant insert on "dbmaster".station_app to "centrole" as "dbmaster";
grant delete on "dbmaster".station_app to "centrole" as "dbmaster";
grant select on "dbmaster".station_loc to "centrole" as "dbmaster";
grant update on "dbmaster".station_loc to "centrole" as "dbmaster";
grant insert on "dbmaster".station_loc to "centrole" as "dbmaster";
grant delete on "dbmaster".station_loc to "centrole" as "dbmaster";
grant select on "dbmaster".tmp_obu3 to "public" as "dbmaster";
grant update on "dbmaster".tmp_obu3 to "public" as "dbmaster";
grant insert on "dbmaster".tmp_obu3 to "public" as "dbmaster";
grant delete on "dbmaster".tmp_obu3 to "public" as "dbmaster";
grant index on "dbmaster".tmp_obu3 to "public" as "dbmaster";
grant select on "dbmaster".dcd_omnistop to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_omnistop to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_omnistop to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_omnistop to "centrole" as "dbmaster";
grant select on "dbmaster".unit_log to "public" as "dbmaster";
grant update on "dbmaster".unit_log to "public" as "dbmaster";
grant insert on "dbmaster".unit_log to "public" as "dbmaster";
grant delete on "dbmaster".unit_log to "public" as "dbmaster";
grant index on "dbmaster".unit_log to "public" as "dbmaster";
grant select on "dbmaster".x_schedules to "public" as "dbmaster";
grant update on "dbmaster".x_schedules to "public" as "dbmaster";
grant insert on "dbmaster".x_schedules to "public" as "dbmaster";
grant delete on "dbmaster".x_schedules to "public" as "dbmaster";
grant index on "dbmaster".x_schedules to "public" as "dbmaster";
grant select on "dbmaster".feed_improut to "centrole" as "dbmaster";
grant update on "dbmaster".feed_improut to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_improut to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_improut to "centrole" as "dbmaster";
grant select on "dbmaster".feed_impspat to "centrole" as "dbmaster";
grant update on "dbmaster".feed_impspat to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_impspat to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_impspat to "centrole" as "dbmaster";
grant select on "dbmaster".feed_imptrip to "centrole" as "dbmaster";
grant update on "dbmaster".feed_imptrip to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_imptrip to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_imptrip to "centrole" as "dbmaster";
grant select on "dbmaster".feed_impttb to "centrole" as "dbmaster";
grant update on "dbmaster".feed_impttb to "centrole" as "dbmaster";
grant insert on "dbmaster".feed_impttb to "centrole" as "dbmaster";
grant delete on "dbmaster".feed_impttb to "centrole" as "dbmaster";
grant select on "dbmaster".unit_comments to "centrole" as "dbmaster";
grant update on "dbmaster".unit_comments to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_comments to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_comments to "centrole" as "dbmaster";
grant select on "dbmaster".desp_auth to "centrole" as "dbmaster";
grant update on "dbmaster".desp_auth to "centrole" as "dbmaster";
grant insert on "dbmaster".desp_auth to "centrole" as "dbmaster";
grant delete on "dbmaster".desp_auth to "centrole" as "dbmaster";
grant select on "dbmaster".tidyup_priority to "centrole" as "dbmaster";
grant update on "dbmaster".tidyup_priority to "centrole" as "dbmaster";
grant insert on "dbmaster".tidyup_priority to "centrole" as "dbmaster";
grant delete on "dbmaster".tidyup_priority to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt_duty to "centrole" as "dbmaster";
grant update on "dbmaster".active_rt_duty to "centrole" as "dbmaster";
grant insert on "dbmaster".active_rt_duty to "centrole" as "dbmaster";
grant delete on "dbmaster".active_rt_duty to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt_duty to "public" as "dbmaster";
grant update on "dbmaster".active_rt_duty to "public" as "dbmaster";
grant insert on "dbmaster".active_rt_duty to "public" as "dbmaster";
grant delete on "dbmaster".active_rt_duty to "public" as "dbmaster";
grant index on "dbmaster".active_rt_duty to "public" as "dbmaster";
grant select on "dbmaster".archive_rt_duty to "centrole" as "dbmaster";
grant update on "dbmaster".archive_rt_duty to "centrole" as "dbmaster";
grant insert on "dbmaster".archive_rt_duty to "centrole" as "dbmaster";
grant delete on "dbmaster".archive_rt_duty to "centrole" as "dbmaster";
grant select on "dbmaster".archive_rt_duty to "public" as "dbmaster";
grant update on "dbmaster".archive_rt_duty to "public" as "dbmaster";
grant insert on "dbmaster".archive_rt_duty to "public" as "dbmaster";
grant delete on "dbmaster".archive_rt_duty to "public" as "dbmaster";
grant index on "dbmaster".archive_rt_duty to "public" as "dbmaster";
grant select on "dbmaster".csv_imprept to "centrole" as "dbmaster";
grant update on "dbmaster".csv_imprept to "centrole" as "dbmaster";
grant insert on "dbmaster".csv_imprept to "centrole" as "dbmaster";
grant delete on "dbmaster".csv_imprept to "centrole" as "dbmaster";
grant select on "dbmaster".route_param to "centrole" as "dbmaster";
grant update on "dbmaster".route_param to "centrole" as "dbmaster";
grant insert on "dbmaster".route_param to "centrole" as "dbmaster";
grant delete on "dbmaster".route_param to "centrole" as "dbmaster";
grant select on "dbmaster".network to "centrole" as "dbmaster";
grant update on "dbmaster".network to "centrole" as "dbmaster";
grant insert on "dbmaster".network to "centrole" as "dbmaster";
grant delete on "dbmaster".network to "centrole" as "dbmaster";
grant select on "dbmaster".unit_status_net to "centrole" as "dbmaster";
grant update on "dbmaster".unit_status_net to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_status_net to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_status_net to "centrole" as "dbmaster";
grant select on "dbmaster".unit_build_log_msg to "centrole" as "dbmaster";
grant update on "dbmaster".unit_build_log_msg to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_build_log_msg to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_build_log_msg to "centrole" as "dbmaster";
grant select on "dbmaster".unit_index to "centrole" as "dbmaster";
grant update on "dbmaster".unit_index to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_index to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_index to "centrole" as "dbmaster";
grant select on "dbmaster".import_log to "centrole" as "dbmaster";
grant update on "dbmaster".import_log to "centrole" as "dbmaster";
grant insert on "dbmaster".import_log to "centrole" as "dbmaster";
grant delete on "dbmaster".import_log to "centrole" as "dbmaster";
grant select on "dbmaster".import_log to "public" as "dbmaster";
grant update on "dbmaster".import_log to "public" as "dbmaster";
grant insert on "dbmaster".import_log to "public" as "dbmaster";
grant delete on "dbmaster".import_log to "public" as "dbmaster";
grant index on "dbmaster".import_log to "public" as "dbmaster";
grant select on "dbmaster".import_log_report to "centrole" as "dbmaster";
grant update on "dbmaster".import_log_report to "centrole" as "dbmaster";
grant insert on "dbmaster".import_log_report to "centrole" as "dbmaster";
grant delete on "dbmaster".import_log_report to "centrole" as "dbmaster";
grant select on "dbmaster".import_log_report to "public" as "dbmaster";
grant update on "dbmaster".import_log_report to "public" as "dbmaster";
grant insert on "dbmaster".import_log_report to "public" as "dbmaster";
grant delete on "dbmaster".import_log_report to "public" as "dbmaster";
grant index on "dbmaster".import_log_report to "public" as "dbmaster";
grant select on "dbmaster".autort_sched to "centrole" as "dbmaster";
grant update on "dbmaster".autort_sched to "centrole" as "dbmaster";
grant insert on "dbmaster".autort_sched to "centrole" as "dbmaster";
grant delete on "dbmaster".autort_sched to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt_lost to "centrole" as "dbmaster";
grant update on "dbmaster".active_rt_lost to "centrole" as "dbmaster";
grant insert on "dbmaster".active_rt_lost to "centrole" as "dbmaster";
grant delete on "dbmaster".active_rt_lost to "centrole" as "dbmaster";
grant select on "dbmaster".active_rt_lost to "public" as "dbmaster";
grant update on "dbmaster".active_rt_lost to "public" as "dbmaster";
grant insert on "dbmaster".active_rt_lost to "public" as "dbmaster";
grant delete on "dbmaster".active_rt_lost to "public" as "dbmaster";
grant index on "dbmaster".active_rt_lost to "public" as "dbmaster";
grant select on "dbmaster".subscriber to "public" as "dbmaster";
grant update on "dbmaster".subscriber to "public" as "dbmaster";
grant insert on "dbmaster".subscriber to "public" as "dbmaster";
grant delete on "dbmaster".subscriber to "public" as "dbmaster";
grant index on "dbmaster".subscriber to "public" as "dbmaster";
grant select on "dbmaster".subscription to "public" as "dbmaster";
grant update on "dbmaster".subscription to "public" as "dbmaster";
grant insert on "dbmaster".subscription to "public" as "dbmaster";
grant delete on "dbmaster".subscription to "public" as "dbmaster";
grant index on "dbmaster".subscription to "public" as "dbmaster";
grant select on "dbmaster".subscr_loc to "public" as "dbmaster";
grant update on "dbmaster".subscr_loc to "public" as "dbmaster";
grant insert on "dbmaster".subscr_loc to "public" as "dbmaster";
grant delete on "dbmaster".subscr_loc to "public" as "dbmaster";
grant index on "dbmaster".subscr_loc to "public" as "dbmaster";
grant select on "dbmaster".user_loc to "public" as "dbmaster";
grant update on "dbmaster".user_loc to "public" as "dbmaster";
grant insert on "dbmaster".user_loc to "public" as "dbmaster";
grant delete on "dbmaster".user_loc to "public" as "dbmaster";
grant index on "dbmaster".user_loc to "public" as "dbmaster";
grant select on "dbmaster".dcd_prediction to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_prediction to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_prediction to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_prediction to "centrole" as "dbmaster";
grant select on "dbmaster".t_scoot_veh_list to "public" as "dbmaster";
grant update on "dbmaster".t_scoot_veh_list to "public" as "dbmaster";
grant insert on "dbmaster".t_scoot_veh_list to "public" as "dbmaster";
grant delete on "dbmaster".t_scoot_veh_list to "public" as "dbmaster";
grant index on "dbmaster".t_scoot_veh_list to "public" as "dbmaster";
grant select on "dbmaster".rbc_sim_phone_mapping to "public" as "dbmaster";
grant update on "dbmaster".rbc_sim_phone_mapping to "public" as "dbmaster";
grant insert on "dbmaster".rbc_sim_phone_mapping to "public" as "dbmaster";
grant delete on "dbmaster".rbc_sim_phone_mapping to "public" as "dbmaster";
grant index on "dbmaster".rbc_sim_phone_mapping to "public" as "dbmaster";
grant select on "dbmaster".temp_rbc_sims to "public" as "dbmaster";
grant update on "dbmaster".temp_rbc_sims to "public" as "dbmaster";
grant insert on "dbmaster".temp_rbc_sims to "public" as "dbmaster";
grant delete on "dbmaster".temp_rbc_sims to "public" as "dbmaster";
grant index on "dbmaster".temp_rbc_sims to "public" as "dbmaster";
grant select on "dbmaster".gps_pred_loc to "public" as "dbmaster";
grant update on "dbmaster".gps_pred_loc to "public" as "dbmaster";
grant insert on "dbmaster".gps_pred_loc to "public" as "dbmaster";
grant delete on "dbmaster".gps_pred_loc to "public" as "dbmaster";
grant index on "dbmaster".gps_pred_loc to "public" as "dbmaster";
grant select on "dbmaster".user_vehicle to "centrole" as "dbmaster";
grant update on "dbmaster".user_vehicle to "centrole" as "dbmaster";
grant insert on "dbmaster".user_vehicle to "centrole" as "dbmaster";
grant delete on "dbmaster".user_vehicle to "centrole" as "dbmaster";
grant select on "dbmaster".user_vehicle to "public" as "dbmaster";
grant update on "dbmaster".user_vehicle to "public" as "dbmaster";
grant insert on "dbmaster".user_vehicle to "public" as "dbmaster";
grant delete on "dbmaster".user_vehicle to "public" as "dbmaster";
grant index on "dbmaster".user_vehicle to "public" as "dbmaster";
grant select on "dbmaster".user_build to "centrole" as "dbmaster";
grant update on "dbmaster".user_build to "centrole" as "dbmaster";
grant insert on "dbmaster".user_build to "centrole" as "dbmaster";
grant delete on "dbmaster".user_build to "centrole" as "dbmaster";
grant select on "dbmaster".user_build to "public" as "dbmaster";
grant update on "dbmaster".user_build to "public" as "dbmaster";
grant insert on "dbmaster".user_build to "public" as "dbmaster";
grant delete on "dbmaster".user_build to "public" as "dbmaster";
grant index on "dbmaster".user_build to "public" as "dbmaster";
grant select on "dbmaster".raw_count to "public" as "dbmaster";
grant update on "dbmaster".raw_count to "public" as "dbmaster";
grant insert on "dbmaster".raw_count to "public" as "dbmaster";
grant delete on "dbmaster".raw_count to "public" as "dbmaster";
grant index on "dbmaster".raw_count to "public" as "dbmaster";
grant select on "dbmaster".gprscov_sent to "public" as "dbmaster";
grant update on "dbmaster".gprscov_sent to "public" as "dbmaster";
grant insert on "dbmaster".gprscov_sent to "public" as "dbmaster";
grant delete on "dbmaster".gprscov_sent to "public" as "dbmaster";
grant index on "dbmaster".gprscov_sent to "public" as "dbmaster";
grant select on "dbmaster".gprscov_recd to "public" as "dbmaster";
grant update on "dbmaster".gprscov_recd to "public" as "dbmaster";
grant insert on "dbmaster".gprscov_recd to "public" as "dbmaster";
grant delete on "dbmaster".gprscov_recd to "public" as "dbmaster";
grant index on "dbmaster".gprscov_recd to "public" as "dbmaster";
grant select on "dbmaster".gprscov_comp to "public" as "dbmaster";
grant update on "dbmaster".gprscov_comp to "public" as "dbmaster";
grant insert on "dbmaster".gprscov_comp to "public" as "dbmaster";
grant delete on "dbmaster".gprscov_comp to "public" as "dbmaster";
grant index on "dbmaster".gprscov_comp to "public" as "dbmaster";
grant select on "dbmaster".gprscov_bunch to "public" as "dbmaster";
grant update on "dbmaster".gprscov_bunch to "public" as "dbmaster";
grant insert on "dbmaster".gprscov_bunch to "public" as "dbmaster";
grant delete on "dbmaster".gprscov_bunch to "public" as "dbmaster";
grant index on "dbmaster".gprscov_bunch to "public" as "dbmaster";
grant select on "dbmaster".passenger_count to "public" as "dbmaster";
grant update on "dbmaster".passenger_count to "public" as "dbmaster";
grant insert on "dbmaster".passenger_count to "public" as "dbmaster";
grant delete on "dbmaster".passenger_count to "public" as "dbmaster";
grant index on "dbmaster".passenger_count to "public" as "dbmaster";
grant select on "dbmaster".stop_cleardowns_tmp to "public" as "dbmaster";
grant update on "dbmaster".stop_cleardowns_tmp to "public" as "dbmaster";
grant insert on "dbmaster".stop_cleardowns_tmp to "public" as "dbmaster";
grant delete on "dbmaster".stop_cleardowns_tmp to "public" as "dbmaster";
grant index on "dbmaster".stop_cleardowns_tmp to "public" as "dbmaster";
grant select on "dbmaster".ih_performance to "centrole" as "dbmaster";
grant update on "dbmaster".ih_performance to "centrole" as "dbmaster";
grant insert on "dbmaster".ih_performance to "centrole" as "dbmaster";
grant delete on "dbmaster".ih_performance to "centrole" as "dbmaster";
grant select on "dbmaster".unit_message_hr to "centrole" as "dbmaster";
grant update on "dbmaster".unit_message_hr to "centrole" as "dbmaster";
grant insert on "dbmaster".unit_message_hr to "centrole" as "dbmaster";
grant delete on "dbmaster".unit_message_hr to "centrole" as "dbmaster";
grant select on "dbmaster".dcd_param to "centrole" as "dbmaster";
grant update on "dbmaster".dcd_param to "centrole" as "dbmaster";
grant insert on "dbmaster".dcd_param to "centrole" as "dbmaster";
grant delete on "dbmaster".dcd_param to "centrole" as "dbmaster";
grant select on "dbmaster".tlp_request to "centrole" as "dbmaster";
grant update on "dbmaster".tlp_request to "centrole" as "dbmaster";
grant insert on "dbmaster".tlp_request to "centrole" as "dbmaster";
grant delete on "dbmaster".tlp_request to "centrole" as "dbmaster";
grant select on "dbmaster".tlp_request to "public" as "dbmaster";
grant update on "dbmaster".tlp_request to "public" as "dbmaster";
grant insert on "dbmaster".tlp_request to "public" as "dbmaster";
grant delete on "dbmaster".tlp_request to "public" as "dbmaster";
grant index on "dbmaster".tlp_request to "public" as "dbmaster";
grant select on "dbmaster".playlist_route to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_route to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_route to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_route to "centrole" as "dbmaster";
grant select on "dbmaster".unit_status_sign to "public" as "dbmaster";
grant update on "dbmaster".unit_status_sign to "public" as "dbmaster";
grant insert on "dbmaster".unit_status_sign to "public" as "dbmaster";
grant delete on "dbmaster".unit_status_sign to "public" as "dbmaster";
grant index on "dbmaster".unit_status_sign to "public" as "dbmaster";
grant select on "dbmaster".playlist_media_type to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_media_type to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_media_type to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_media_type to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_program to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_program to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_program to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_program to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_block to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_block to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_block to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_block to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_slot to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_slot to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_slot to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_slot to "centrole" as "dbmaster";
grant select on "dbmaster".playlist to "centrole" as "dbmaster";
grant update on "dbmaster".playlist to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_media to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_media to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_media to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_media to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_condition to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_condition to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_condition to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_condition to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_attrib_type to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_attrib_type to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_attrib_type to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_attrib_type to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_attrib_value to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_attrib_value to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_attrib_value to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_attrib_value to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_attrib to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_attrib to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_attrib to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_attrib to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_cond_type to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_cond_type to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_cond_type to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_cond_type to "centrole" as "dbmaster";
grant select on "dbmaster".playlist_cond_value to "centrole" as "dbmaster";
grant update on "dbmaster".playlist_cond_value to "centrole" as "dbmaster";
grant insert on "dbmaster".playlist_cond_value to "centrole" as "dbmaster";
grant delete on "dbmaster".playlist_cond_value to "centrole" as "dbmaster";
grant select on "dbmaster".post_code to "centrole" as "dbmaster";
grant select on "dbmaster".ttt to "public" as "dbmaster";
grant update on "dbmaster".ttt to "public" as "dbmaster";
grant insert on "dbmaster".ttt to "public" as "dbmaster";
grant delete on "dbmaster".ttt to "public" as "dbmaster";
grant index on "dbmaster".ttt to "public" as "dbmaster";
grant select on "dbmaster".location_media to "public" as "dbmaster";
grant update on "dbmaster".location_media to "public" as "dbmaster";
grant insert on "dbmaster".location_media to "public" as "dbmaster";
grant delete on "dbmaster".location_media to "public" as "dbmaster";
grant index on "dbmaster".location_media to "public" as "dbmaster";
grant select on "dbmaster".login_audit to "centrole" as "dbmaster";
grant update on "dbmaster".login_audit to "centrole" as "dbmaster";
grant insert on "dbmaster".login_audit to "centrole" as "dbmaster";
grant delete on "dbmaster".login_audit to "centrole" as "dbmaster";
grant select on "dbmaster".login_audit to "public" as "dbmaster";
grant update on "dbmaster".login_audit to "public" as "dbmaster";
grant insert on "dbmaster".login_audit to "public" as "dbmaster";
grant delete on "dbmaster".login_audit to "public" as "dbmaster";
grant index on "dbmaster".login_audit to "public" as "dbmaster";
grant select on "dbmaster".items to "public" as "dbmaster";
grant update on "dbmaster".items to "public" as "dbmaster";
grant insert on "dbmaster".items to "public" as "dbmaster";
grant delete on "dbmaster".items to "public" as "dbmaster";
grant index on "dbmaster".items to "public" as "dbmaster";
grant select on "dbmaster".assignments to "public" as "dbmaster";
grant update on "dbmaster".assignments to "public" as "dbmaster";
grant insert on "dbmaster".assignments to "public" as "dbmaster";
grant delete on "dbmaster".assignments to "public" as "dbmaster";
grant index on "dbmaster".assignments to "public" as "dbmaster";
grant select on "dbmaster".ih_performance_route to "centrole" as "dbmaster";
grant update on "dbmaster".ih_performance_route to "centrole" as "dbmaster";
grant insert on "dbmaster".ih_performance_route to "centrole" as "dbmaster";
grant delete on "dbmaster".ih_performance_route to "centrole" as "dbmaster";
grant select on "dbmaster".stop to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menu to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_menu to "public" as "dbmaster";
grant update on "dbmaster".iconnex_menu to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_menu to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_menu to "public" as "dbmaster";
grant index on "dbmaster".iconnex_menu to "public" as "dbmaster";
grant select on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_param to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_param to "public" as "dbmaster";
grant update on "dbmaster".iconnex_param to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_param to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_param to "public" as "dbmaster";
grant index on "dbmaster".iconnex_param to "public" as "dbmaster";
grant select on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_application to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_application to "public" as "dbmaster";
grant update on "dbmaster".iconnex_application to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_application to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_application to "public" as "dbmaster";
grant index on "dbmaster".iconnex_application to "public" as "dbmaster";
grant select on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menuitem to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_menuitem to "public" as "dbmaster";
grant update on "dbmaster".iconnex_menuitem to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_menuitem to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_menuitem to "public" as "dbmaster";
grant index on "dbmaster".iconnex_menuitem to "public" as "dbmaster";
grant select on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_menu_user to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_menu_user to "public" as "dbmaster";
grant update on "dbmaster".iconnex_menu_user to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_menu_user to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_menu_user to "public" as "dbmaster";
grant index on "dbmaster".iconnex_menu_user to "public" as "dbmaster";
grant select on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_workspace to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_workspace to "public" as "dbmaster";
grant update on "dbmaster".iconnex_workspace to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_workspace to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_workspace to "public" as "dbmaster";
grant index on "dbmaster".iconnex_workspace to "public" as "dbmaster";
grant select on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant update on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant insert on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant delete on "dbmaster".iconnex_wsp_item to "centrole" as "dbmaster";
grant select on "dbmaster".iconnex_wsp_item to "public" as "dbmaster";
grant update on "dbmaster".iconnex_wsp_item to "public" as "dbmaster";
grant insert on "dbmaster".iconnex_wsp_item to "public" as "dbmaster";
grant delete on "dbmaster".iconnex_wsp_item to "public" as "dbmaster";
grant index on "dbmaster".iconnex_wsp_item to "public" as "dbmaster";


revoke usage on language SPL from public ;

grant usage on language SPL to public ;


create view "dbmaster".v_loc_comp (actual_start,start_day,trip_no,vehicle_code,rpat_orderby,arrival_time,departure_time,route_code,service_code,location_code,description,pub_time,route_area_code,actual_est,operator_code,arrival_status,departure_status) as 
  select x0.actual_start ,x0.start_day ,x1.trip_no ,x2.vehicle_code 
    ,x3.rpat_orderby ,x3.arrival_time ,x3.departure_time ,x5.route_code 
    ,x7.service_code ,x4.location_code ,x4.description ,x3.departure_time_pub 
    ,x6.route_area_code ,x3.actual_est ,x8.operator_code ,x3.arrival_status 
    ,x3.departure_status from "dbmaster".archive_rt x0 ,"dbmaster"
    .publish_tt x1 ,"dbmaster".vehicle x2 ,"dbmaster".archive_rt_loc 
    x3 ,"dbmaster".location x4 ,"dbmaster".route x5 ,"dbmaster".route_area 
    x6 ,"dbmaster".service x7 ,"dbmaster".operator x8 where (((((((((x0.pub_ttb_id 
    = x1.pub_ttb_id ) AND (x0.trip_no = x1.trip_no ) ) AND (x0.vehicle_id 
    = x2.vehicle_id ) ) AND (x0.schedule_id = x3.schedule_id 
    ) ) AND (x0.route_id = x5.route_id ) ) AND (x3.location_id 
    = x4.location_id ) ) AND (x6.route_area_id = x4.route_area_id 
    ) ) AND (x1.service_id = x7.service_id ) ) AND (x5.operator_id 
    = x8.operator_id ) ) ;      
create view "dbmaster".route_for_user (route_id,route_code,operator_id,description,outbound_desc,inbound_desc) as 
  select x0.route_id ,x0.route_code ,x0.operator_id ,x0.description 
    ,x0.outbound_desc ,x0.inbound_desc from "dbmaster".route x0 
    where (x0.route_id = ANY (select x8.route_id from "dbmaster"
    .user_route x6 ,"dbmaster".cent_user x7 ,"dbmaster".route x8 
    where (((x6.userid = x7.userid ) AND (x7.usernm = USER ) 
    ) AND (x8.operator_id = x6.operator_id ) ) ) )  union select 
    x3.route_id ,x3.route_code ,x3.operator_id ,x3.description 
    ,x3.outbound_desc ,x3.inbound_desc from "dbmaster".user_route 
    x1 ,"dbmaster".cent_user x2 ,"dbmaster".route x3 where (((x1.userid 
    = x2.userid ) AND (x2.usernm = USER ) ) AND (x3.route_id 
    = x1.route_id ) )  union select x4.route_id ,x4.route_code 
    ,x4.operator_id ,x4.description ,x4.outbound_desc ,x4.inbound_desc 
    from "dbmaster".route x4 ,"dbmaster".cent_user x5 where ((((x4.operator_id 
    = x5.operator_id ) OR (x5.operator_id IS NULL ) ) AND (x5.usernm 
    = USER ) ) AND (x5.userid != ALL (select x9.userid from "dbmaster"
    .user_route x9 ) ) ) ;         
create view "dbmaster".route_visibility (usernm,userid,route_id,route_code,operator_id,description,outbound_desc,inbound_desc) as 
  select x1.usernm ,x1.userid ,x2.route_id ,x2.route_code ,x2.operator_id 
    ,x2.description ,x2.outbound_desc ,x2.inbound_desc from "dbmaster"
    .user_route x0 ,"dbmaster".cent_user x1 ,"dbmaster".route x2 
    where ((x0.userid = x1.userid ) AND (x2.operator_id = x0.operator_id 
    ) )  union select x4.usernm ,x4.userid ,x5.route_id ,x5.route_code 
    ,x5.operator_id ,x5.description ,x5.outbound_desc ,x5.inbound_desc 
    from "dbmaster".user_route x3 ,"dbmaster".cent_user x4 ,"dbmaster"
    .route x5 where ((x3.userid = x4.userid ) AND (x5.route_id 
    = x3.route_id ) )  union select x7.usernm ,x7.userid ,x6.route_id 
    ,x6.route_code ,x6.operator_id ,x6.description ,x6.outbound_desc 
    ,x6.inbound_desc from "dbmaster".route x6 ,"dbmaster".cent_user 
    x7 where (((x6.operator_id = x7.operator_id ) OR (x7.operator_id 
    IS NULL ) ) AND (x7.userid != ALL (select x8.userid from 
    "dbmaster".user_route x8 ) ) ) ;                  
create view "dbmaster".vehicle_visibility (usernm,userid,vehicle_id,vehicle_code,vehicle_type_id,operator_id,vehicle_reg,orun_code,vetag_indicator,modem_addr,build_id,wheelchair_access) as 
  select x1.usernm ,x1.userid ,x2.vehicle_id ,x2.vehicle_code 
    ,x2.vehicle_type_id ,x2.operator_id ,x2.vehicle_reg ,x2.orun_code 
    ,x2.vetag_indicator ,x2.modem_addr ,x2.build_id ,x2.wheelchair_access 
    from "dbmaster".user_vehicle x0 ,"dbmaster".cent_user x1 ,"dbmaster"
    .vehicle x2 where ((x0.userid = x1.userid ) AND (x2.operator_id 
    = x0.operator_id ) )  union select x4.usernm ,x4.userid ,
    x5.vehicle_id ,x5.vehicle_code ,x5.vehicle_type_id ,x5.operator_id 
    ,x5.vehicle_reg ,x5.orun_code ,x5.vetag_indicator ,x5.modem_addr 
    ,x5.build_id ,x5.wheelchair_access from "dbmaster".user_vehicle 
    x3 ,"dbmaster".cent_user x4 ,"dbmaster".vehicle x5 where ((x3.userid 
    = x4.userid ) AND (x5.vehicle_id = x3.vehicle_id ) )  union 
    select x7.usernm ,x7.userid ,x6.vehicle_id ,x6.vehicle_code 
    ,x6.vehicle_type_id ,x6.operator_id ,x6.vehicle_reg ,x6.orun_code 
    ,x6.vetag_indicator ,x6.modem_addr ,x6.build_id ,x6.wheelchair_access 
    from "dbmaster".vehicle x6 ,"dbmaster".cent_user x7 where (((x6.operator_id 
    = x7.operator_id ) OR (x7.operator_id IS NULL ) ) AND (x7.userid 
    != ALL (select x8.userid from "dbmaster".user_vehicle x8 ) 
    ) ) ;                                            
create view "dbmaster".build_visibility (usernm,userid,build_id,operator_id,build_code,unit_type,description,build_parent,build_status,version_id,build_notes1,build_notes2,build_type,allow_logs,allow_publish) as 
  select x1.usernm ,x1.userid ,x2.build_id ,x2.operator_id ,x2.build_code 
    ,x2.unit_type ,x2.description ,x2.build_parent ,x2.build_status 
    ,x2.version_id ,x2.build_notes1 ,x2.build_notes2 ,x2.build_type 
    ,x2.allow_logs ,x2.allow_publish from "dbmaster".user_build 
    x0 ,"dbmaster".cent_user x1 ,"dbmaster".unit_build x2 where 
    ((x0.userid = x1.userid ) AND (x2.operator_id = x0.operator_id 
    ) )  union select x4.usernm ,x4.userid ,x5.build_id ,x5.operator_id 
    ,x5.build_code ,x5.unit_type ,x5.description ,x5.build_parent 
    ,x5.build_status ,x5.version_id ,x5.build_notes1 ,x5.build_notes2 
    ,x5.build_type ,x5.allow_logs ,x5.allow_publish from "dbmaster"
    .user_build x3 ,"dbmaster".cent_user x4 ,"dbmaster".unit_build 
    x5 where ((x3.userid = x4.userid ) AND (x5.build_id = x3.build_id 
    ) )  union select x7.usernm ,x7.userid ,x6.build_id ,x6.operator_id 
    ,x6.build_code ,x6.unit_type ,x6.description ,x6.build_parent 
    ,x6.build_status ,x6.version_id ,x6.build_notes1 ,x6.build_notes2 
    ,x6.build_type ,x6.allow_logs ,x6.allow_publish from "dbmaster"
    .unit_build x6 ,"dbmaster".cent_user x7 where (((x6.operator_id 
    = x7.operator_id ) OR (x7.operator_id IS NULL ) ) AND (x7.userid 
    != ALL (select x8.userid from "dbmaster".user_build x8 ) ) 
    ) ;                                       

create index "dbmaster".f_archive_rt_2 on "dbmaster".archive_rt 
    (route_id) using btree ;
create index "dbmaster".f_archive_rt_3 on "dbmaster".archive_rt 
    (pub_ttb_id) using btree ;
create index "dbmaster".f_archive_rt_4 on "dbmaster".archive_rt 
    (employee_id) using btree ;
create index "dbmaster".i_archive_rt_5 on "dbmaster".archive_rt 
    (vehicle_id,employee_id) using btree ;
create unique index "dbmaster".p_archive_rt_1 on "dbmaster".archive_rt 
    (schedule_id) using btree ;
alter table "dbmaster".archive_rt add constraint primary key 
    (schedule_id) constraint "dbmaster".pk_archive_rt  ;
create index "dbmaster".f_media_2 on "dbmaster".media (media_type_code) 
    using btree ;
create index "dbmaster".f_media_3 on "dbmaster".media (media_frm_code) 
    using btree ;
create index "dbmaster".i_media_4 on "dbmaster".media (media_desc) 
    using btree ;
create unique index "dbmaster".p_media_1 on "dbmaster".media (media_id) 
    using btree ;
alter table "dbmaster".media add constraint primary key (media_id) 
    constraint "dbmaster".pk_media  ;
create index "dbmaster".f_active_rt_2 on "dbmaster".active_rt 
    (route_id) using btree ;
create index "dbmaster".f_active_rt_3 on "dbmaster".active_rt 
    (pub_ttb_id) using btree ;
create index "dbmaster".f_active_rt_4 on "dbmaster".active_rt 
    (employee_id) using btree ;
create unique index "dbmaster".f_active_rt_5 on "dbmaster".active_rt 
    (vehicle_id,pub_ttb_id) using btree ;
create unique index "dbmaster".p_active_rt_1 on "dbmaster".active_rt 
    (schedule_id) using btree ;
alter table "dbmaster".active_rt add constraint primary key (schedule_id) 
    constraint "dbmaster".pk_active_rt  ;
create index "dbmaster".f_active_rt_loc_2 on "dbmaster".active_rt_loc 
    (schedule_id) using btree ;
create unique index "dbmaster".p_active_rt_loc_1 on "dbmaster"
    .active_rt_loc (schedule_id,rpat_orderby,location_id) using 
    btree ;
alter table "dbmaster".active_rt_loc add constraint primary key 
    (schedule_id,rpat_orderby,location_id) constraint "dbmaster"
    .pk_active_rt_loc  ;
create index "dbmaster".f_autoroute_time_2 on "dbmaster".autoroute_time 
    (service_id) using btree ;
create index "dbmaster".f_autoroute_time_3 on "dbmaster".autoroute_time 
    (auto_prof_id) using btree ;
create unique index "dbmaster".i_autoroute_time_4 on "dbmaster"
    .autoroute_time (direction) using btree ;
create unique index "dbmaster".p_autoroute_time_1 on "dbmaster"
    .autoroute_time (auto_prof_id,service_id,duty_no,trip_no,
    running_no) using btree ;
alter table "dbmaster".autoroute_time add constraint primary 
    key (auto_prof_id,service_id,duty_no,trip_no,running_no) 
    constraint "dbmaster".pk_autoroute_time  ;
create index "dbmaster".f_autort_config_2 on "dbmaster".autort_config 
    (service_id) using btree ;
create unique index "dbmaster".p_autort_config_1 on "dbmaster"
    .autort_config (service_id,dayno) using btree ;
alter table "dbmaster".autort_config add constraint primary key 
    (service_id,dayno) constraint "dbmaster".pk_autort_config 
     ;
create unique index "dbmaster".p_autort_profile_1 on "dbmaster"
    .autort_profile (auto_prof_id) using btree ;
alter table "dbmaster".autort_profile add constraint primary 
    key (auto_prof_id) constraint "dbmaster".pk_autort_profile 
     ;
create unique index "dbmaster".p_cent_user_1 on "dbmaster".cent_user 
    (userid) using btree ;
create unique index "dbmaster".u_cent_user_2 on "dbmaster".cent_user 
    (usernm) using btree ;
alter table "dbmaster".cent_user add constraint primary key (userid) 
    constraint "dbmaster".pk_cent_user  ;
create unique index "dbmaster".i_component_2 on "dbmaster".component 
    (component_code) using btree ;
create unique index "dbmaster".p_component_1 on "dbmaster".component 
    (component_id) using btree ;
alter table "dbmaster".component add constraint primary key (component_id) 
    constraint "dbmaster".pk_component  ;
create unique index "dbmaster".p_dcd_msg_loc_1 on "dbmaster".dcd_message_loc 
    (message_id,build_id) using btree ;
alter table "dbmaster".dcd_message_loc add constraint primary 
    key (message_id,build_id) constraint "dbmaster".pk_dcd_message_loc 
     ;
create index "dbmaster".f_destination_2 on "dbmaster".destination 
    (operator_id) using btree ;
create unique index "dbmaster".i_destination_3 on "dbmaster".destination 
    (operator_id,dest_id) using btree ;
create unique index "dbmaster".i_destination_4 on "dbmaster".destination 
    (operator_id,dest_code) using btree ;
create unique index "dbmaster".p_destination_1 on "dbmaster".destination 
    (dest_id) using btree ;
alter table "dbmaster".destination add constraint primary key 
    (dest_id) constraint "dbmaster".pk_destination  ;
create index "dbmaster".i_display_point_1 on "dbmaster".display_point 
    (location_id) using btree ;
create index "dbmaster".i_display_point_2 on "dbmaster".display_point 
    (build_id) using btree ;
create unique index "dbmaster".i_district_2 on "dbmaster".district 
    (district_code) using btree ;
create unique index "dbmaster".i_district_3 on "dbmaster".district 
    (district_name) using btree ;
create unique index "dbmaster".p_district_1 on "dbmaster".district 
    (district_id) using btree ;
alter table "dbmaster".district add constraint primary key (district_id) 
    constraint "dbmaster".pk_district  ;
create index "dbmaster".f_driver_message_2 on "dbmaster".driver_message 
    (operator_id) using btree ;
create unique index "dbmaster".p_driver_message_1 on "dbmaster"
    .driver_message (operator_id,message_id) using btree ;
alter table "dbmaster".driver_message add constraint primary 
    key (operator_id,message_id) constraint "dbmaster".pk_driver_message 
     ;
create index "dbmaster".f_employee_2 on "dbmaster".employee (operator_id) 
    using btree ;
create unique index "dbmaster".i_employee_3 on "dbmaster".employee 
    (operator_id,employee_code) using btree ;
create unique index "dbmaster".p_employee_1 on "dbmaster".employee 
    (employee_id) using btree ;
alter table "dbmaster".employee add constraint primary key (employee_id) 
    constraint "dbmaster".pk_employee  ;
create index "dbmaster".f_event_pattern_2 on "dbmaster".event_pattern 
    (evprf_id) using btree ;
create unique index "dbmaster".p_event_pattern_1 on "dbmaster"
    .event_pattern (evprf_id,event_id) using btree ;
alter table "dbmaster".event_pattern add constraint primary key 
    (evprf_id,event_id) constraint "dbmaster".pk_event_pattern 
     ;
create unique index "dbmaster".p_event_profile_1 on "dbmaster"
    .event_profile (evprf_id) using btree ;
alter table "dbmaster".event_profile add constraint primary key 
    (evprf_id) constraint "dbmaster".pk_event_profile  ;
create index "dbmaster".f_fare_stage_2 on "dbmaster".fare_stage 
    (service_id) using btree ;
create unique index "dbmaster".p_fare_stage_1 on "dbmaster".fare_stage 
    (fare_stage_id) using btree ;
alter table "dbmaster".fare_stage add constraint primary key 
    (fare_stage_id) constraint "dbmaster".pk_fare_stage  ;
create unique index "dbmaster".p_feed_format_1 on "dbmaster".feed_format 
    (format_code) using btree ;
alter table "dbmaster".feed_format add constraint primary key 
    (format_code) constraint "dbmaster".pk_feed_format  ;
create index "dbmaster".f_feed_history_2 on "dbmaster".feed_history 
    (feed_type_id) using btree ;
create unique index "dbmaster".p_feed_history_1 on "dbmaster".feed_history 
    (feed_id) using btree ;
alter table "dbmaster".feed_history add constraint primary key 
    (feed_id) constraint "dbmaster".pk_feed_history  ;
create index "dbmaster".f_feed_type_2 on "dbmaster".feed_type 
    (operator_id) using btree ;
create index "dbmaster".f_feed_type_3 on "dbmaster".feed_type 
    (format_code) using btree ;
create unique index "dbmaster".i_feed_type_4 on "dbmaster".feed_type 
    (operator_id,format_code,version) using btree ;
create unique index "dbmaster".p_feed_type_1 on "dbmaster".feed_type 
    (feed_type_id) using btree ;
alter table "dbmaster".feed_type add constraint primary key (feed_type_id) 
    constraint "dbmaster".pk_feed_type  ;
create unique index "dbmaster".p_gprs_mapping_1 on "dbmaster".gprs_mapping 
    (build_id) using btree ;
alter table "dbmaster".gprs_mapping add constraint primary key 
    (build_id) constraint "dbmaster".pk_gprs_mapping  ;
create unique index "dbmaster".p_html_tag_1 on "dbmaster".html_tag 
    (report_code,tag_type,tag_code) using btree ;
alter table "dbmaster".html_tag add constraint primary key (report_code,
    tag_type,tag_code) constraint "dbmaster".pk_html_tag  ;
create index "dbmaster".f_junction_2 on "dbmaster".junction (sigprot_code) 
    using btree ;
create index "dbmaster".f_junction_3 on "dbmaster".junction (location_id) 
    using btree ;
create unique index "dbmaster".p_junction_1 on "dbmaster".junction 
    (junction_code) using btree ;
alter table "dbmaster".junction add constraint primary key (junction_code) 
    constraint "dbmaster".pk_junction  ;
create index "dbmaster".f_junction_aprch_2 on "dbmaster".junction_aprch 
    (road_code) using btree ;
create index "dbmaster".f_junction_aprch_3 on "dbmaster".junction_aprch 
    (junction_code) using btree ;
create index "dbmaster".f_junction_aprch_4 on "dbmaster".junction_aprch 
    (location_id) using btree ;
create unique index "dbmaster".p_junction_aprch_1 on "dbmaster"
    .junction_aprch (junction_code,road_code) using btree ;
alter table "dbmaster".junction_aprch add constraint primary 
    key (junction_code,road_code) constraint "dbmaster".pk_junction_aprch 
     ;
create index "dbmaster".f_junction_reg_2 on "dbmaster".junction_reg 
    (regis_code) using btree ;
create index "dbmaster".f_junction_reg_3 on "dbmaster".junction_reg 
    (trigger_type_code) using btree ;
create index "dbmaster".f_junction_reg_4 on "dbmaster".junction_reg 
    (junction_code) using btree ;
create unique index "dbmaster".p_junction_reg_1 on "dbmaster".junction_reg 
    (junction_code,traversal_id,regis_code) using btree ;
create index "dbmaster".f_junction_xtrav_2 on "dbmaster".junction_xtrav 
    (junction_code) using btree ;
create unique index "dbmaster".i_junction_xtrav_3 on "dbmaster"
    .junction_xtrav (traversal_id) using btree ;
create unique index "dbmaster".p_junction_xtrav_1 on "dbmaster"
    .junction_xtrav (junction_code,approach_roadcd,exit_roadcd) 
    using btree ;
alter table "dbmaster".junction_xtrav add constraint primary 
    key (junction_code,approach_roadcd,exit_roadcd) constraint 
    "dbmaster".pk_junction_xtrav  ;
create unique index "dbmaster".p_layover_1 on "dbmaster".layover 
    (layover_type) using btree ;
alter table "dbmaster".layover add constraint primary key (layover_type) 
    constraint "dbmaster".pk_layover  ;
create index "dbmaster".f_location_2 on "dbmaster".location (district_id) 
    using btree ;
create index "dbmaster".f_location_3 on "dbmaster".location (point_type) 
    using btree ;
create index "dbmaster".f_location_4 on "dbmaster".location (route_area_id) 
    using btree ;
create index "dbmaster".f_location_5 on "dbmaster".location (place_id) 
    using btree ;
create unique index "dbmaster".i_location_7 on "dbmaster".location 
    (location_code) using btree ;
create unique index "dbmaster".p_location_1 on "dbmaster".location 
    (location_id) using btree ;
alter table "dbmaster".location add constraint primary key (location_id) 
    constraint "dbmaster".pk_location  ;
create unique index "dbmaster".p_location_type_1 on "dbmaster"
    .location_type (point_type) using btree ;
alter table "dbmaster".location_type add constraint primary key 
    (point_type) constraint "dbmaster".pk_location_type  ;
create unique index "dbmaster".p_media_format_1 on "dbmaster".media_format 
    (format_code) using btree ;
alter table "dbmaster".media_format add constraint primary key 
    (format_code) constraint "dbmaster".pk_media_format  ;
create unique index "dbmaster".p_media_type_1 on "dbmaster".media_type 
    (media_type_code) using btree ;
alter table "dbmaster".media_type add constraint primary key 
    (media_type_code) constraint "dbmaster".pk_media_type  ;
create unique index "dbmaster".p_msg_to_veh_1 on "dbmaster".msg_to_veh 
    (message_id) using btree ;
alter table "dbmaster".msg_to_veh add constraint primary key 
    (message_id) constraint "dbmaster".pk_msg_to_veh  ;
create index "dbmaster".f_opconarea_2 on "dbmaster".opconarea 
    (operator_id) using btree ;
create unique index "dbmaster".p_opconarea_1 on "dbmaster".opconarea 
    (operator_id,opconarea_code) using btree ;
alter table "dbmaster".opconarea add constraint primary key (operator_id,
    opconarea_code) constraint "dbmaster".pk_opconarea  ;
create unique index "dbmaster".i_operator_3 on "dbmaster".operator 
    (loc_prefix) using btree ;
create unique index "dbmaster".p_operator_1 on "dbmaster".operator 
    (operator_id) using btree ;
create unique index "dbmaster".u_operator_2 on "dbmaster".operator 
    (operator_code) using btree ;
alter table "dbmaster".operator add constraint primary key (operator_id) 
    constraint "dbmaster".pk_operator  ;
create index "dbmaster".f_operator_media_2 on "dbmaster".operator_media 
    (operator_id) using btree ;
create unique index "dbmaster".p_operator_media_1 on "dbmaster"
    .operator_media (operator_id,media_id) using btree ;
alter table "dbmaster".operator_media add constraint primary 
    key (operator_id,media_id) constraint "dbmaster".pk_operator_media 
     ;
create index "dbmaster".f_orgunit_2 on "dbmaster".orgunit (operator_id,
    opconarea_code) using btree ;
create unique index "dbmaster".p_orgunit_1 on "dbmaster".orgunit 
    (operator_id,orun_code) using btree ;
alter table "dbmaster".orgunit add constraint primary key (operator_id,
    orun_code) constraint "dbmaster".pk_orgunit  ;
create index "dbmaster".f_parameter_2 on "dbmaster".parameter 
    (component_id) using btree ;
create unique index "dbmaster".p_parameter_1 on "dbmaster".parameter 
    (component_id,param_id) using btree ;
create unique index "dbmaster".p_parameter_3 on "dbmaster".parameter 
    (component_id,param_desc) using btree ;
alter table "dbmaster".parameter add constraint primary key (component_id,
    param_id) constraint "dbmaster".pk_parameter  ;
create index "dbmaster".f_period_group_2 on "dbmaster".period_group 
    (operator_id) using btree ;
create unique index "dbmaster".p_period_group_1 on "dbmaster".period_group 
    (operator_id,pegp_code) using btree ;
alter table "dbmaster".period_group add constraint primary key 
    (operator_id,pegp_code) constraint "dbmaster".pk_period_group 
     ;
create index "dbmaster".f_place_2 on "dbmaster".place (town_id) 
    using btree ;
create unique index "dbmaster".i_place_3 on "dbmaster".place (town_id,
    place_code) using btree ;
create unique index "dbmaster".p_place_1 on "dbmaster".place (place_id) 
    using btree ;
alter table "dbmaster".place add constraint primary key (place_id) 
    constraint "dbmaster".pk_place  ;
create index "dbmaster".f_pt_duty_2 on "dbmaster".pt_duty (pub_ttb_id) 
    using btree ;
create index "dbmaster".f_pt_duty_3 on "dbmaster".pt_duty (profile_id,
    service_id,rpat_orderby) using btree ;
create unique index "dbmaster".p_pt_duty_1 on "dbmaster".pt_duty 
    (pub_ttb_id,profile_id,service_id,rpat_orderby) using btree 
    ;
alter table "dbmaster".pt_duty add constraint primary key (pub_ttb_id,
    profile_id,service_id,rpat_orderby) constraint "dbmaster".pk_pt_duty 
     ;
create index "dbmaster".f_ptactn_2 on "dbmaster".ptactn (dmnscd) 
    using btree ;
create index "dbmaster".f_ptactn_3 on "dbmaster".ptactn (langcd) 
    using btree ;
create index "dbmaster".p_ptactn_1 on "dbmaster".ptactn (dmnscd,
    langcd,optwrd) using btree ;
create index "dbmaster".f_ptdict_2 on "dbmaster".ptdict (langcd,
    tabnam) using btree ;
create unique index "dbmaster".p_ptdict_1 on "dbmaster".ptdict 
    (langcd,tabnam,colnam) using btree ;
alter table "dbmaster".ptdict add constraint primary key (langcd,
    tabnam,colnam) constraint "dbmaster".pk_ptdict  ;
create index "dbmaster".f_ptdmac_2 on "dbmaster".ptdmac (dmnscd) 
    using btree ;
create unique index "dbmaster".p_ptdmac_1 on "dbmaster".ptdmac 
    (dmnscd,optkey) using btree ;
alter table "dbmaster".ptdmac add constraint primary key (dmnscd,
    optkey) constraint "dbmaster".pk_ptdmac  ;
create unique index "dbmaster".p_ptdmns_1 on "dbmaster".ptdmns 
    (dmnscd) using btree ;
alter table "dbmaster".ptdmns add constraint primary key (dmnscd) 
    constraint "dbmaster".pk_ptdmns  ;
create unique index "dbmaster".p_ptengl_1 on "dbmaster".ptengl 
    (serlno) using btree ;
alter table "dbmaster".ptengl add constraint primary key (serlno) 
    constraint "dbmaster".pk_ptengl  ;
create index "dbmaster".f_ptfgms_2 on "dbmaster".ptfgms (mesgcd) 
    using btree ;
create index "dbmaster".f_ptfgms_3 on "dbmaster".ptfgms (langcd) 
    using btree ;
create unique index "dbmaster".p_ptfgms_1 on "dbmaster".ptfgms 
    (mesgcd,langcd) using btree ;
alter table "dbmaster".ptfgms add constraint primary key (mesgcd,
    langcd) constraint "dbmaster".pk_ptfgms  ;
create index "dbmaster".f_ptfgop_2 on "dbmaster".ptfgop (optcod) 
    using btree ;
create index "dbmaster".f_ptfgop_3 on "dbmaster".ptfgop (langcd) 
    using btree ;
create unique index "dbmaster".p_ptfgop_1 on "dbmaster".ptfgop 
    (optcod,langcd) using btree ;
alter table "dbmaster".ptfgop add constraint primary key (optcod,
    langcd) constraint "dbmaster".pk_ptfgop  ;
create index "dbmaster".f_ptfgtx_2 on "dbmaster".ptfgtx (langcd) 
    using btree ;
create index "dbmaster".f_ptfgtx_3 on "dbmaster".ptfgtx (serlno) 
    using btree ;
create unique index "dbmaster".p_ptfgtx_1 on "dbmaster".ptfgtx 
    (serlno,langcd) using btree ;
alter table "dbmaster".ptfgtx add constraint primary key (serlno,
    langcd) constraint "dbmaster".pk_ptfgtx  ;
create index "dbmaster".f_ptgprm_2 on "dbmaster".ptgprm (grupid) 
    using btree ;
create unique index "dbmaster".p_ptgprm_1 on "dbmaster".ptgprm 
    (grupid,optcod) using btree ;
alter table "dbmaster".ptgprm add constraint primary key (grupid,
    optcod) constraint "dbmaster".pk_ptgprm  ;
create unique index "dbmaster".p_ptgrup_1 on "dbmaster".ptgrup 
    (grupid) using btree ;
create unique index "dbmaster".u_ptgrup_2 on "dbmaster".ptgrup 
    (grupcd) using btree ;
alter table "dbmaster".ptgrup add constraint unique (grupcd) 
    constraint "dbmaster".u_ptgrup_a  ;
alter table "dbmaster".ptgrup add constraint primary key (grupid) 
    constraint "dbmaster".pk_ptgrup  ;
create index "dbmaster".f_ptgrus_2 on "dbmaster".ptgrus (grupid) 
    using btree ;
create index "dbmaster".f_ptgrus_3 on "dbmaster".ptgrus (userid) 
    using btree ;
create unique index "dbmaster".p_ptgrus_1 on "dbmaster".ptgrus 
    (grupid,userid) using btree ;
alter table "dbmaster".ptgrus add constraint primary key (grupid,
    userid) constraint "dbmaster".pk_ptgrus  ;
create unique index "dbmaster".p_ptlang_1 on "dbmaster".ptlang 
    (langcd) using btree ;
alter table "dbmaster".ptlang add constraint primary key (langcd) 
    constraint "dbmaster".pk_ptlang  ;
create index "dbmaster".f_ptmndt_2 on "dbmaster".ptmndt (menucd) 
    using btree ;
create unique index "dbmaster".p_ptmndt_1 on "dbmaster".ptmndt 
    (menucd,seqnum) using btree ;
alter table "dbmaster".ptmndt add constraint primary key (menucd,
    seqnum) constraint "dbmaster".pk_ptmndt  ;
create unique index "dbmaster".p_ptmnhd_1 on "dbmaster".ptmnhd 
    (menucd) using btree ;
alter table "dbmaster".ptmnhd add constraint primary key (menucd) 
    constraint "dbmaster".pk_ptmnhd  ;
create index "dbmaster".f_ptmnlg_2 on "dbmaster".ptmnlg (userid) 
    using btree ;
create unique index "dbmaster".p_ptmnlg_1 on "dbmaster".ptmnlg 
    (userid,sttdat,stttim) using btree ;
alter table "dbmaster".ptmnlg add constraint primary key (userid,
    sttdat,stttim) constraint "dbmaster".pk_ptmnlg  ;
create index "dbmaster".f_ptmnms_2 on "dbmaster".ptmnms (langcd) 
    using btree ;
create index "dbmaster".f_ptmnms_3 on "dbmaster".ptmnms (menucd) 
    using btree ;
create unique index "dbmaster".p_ptmnms_1 on "dbmaster".ptmnms 
    (menucd,langcd) using btree ;
alter table "dbmaster".ptmnms add constraint primary key (menucd,
    langcd) constraint "dbmaster".pk_ptmnms  ;
create unique index "dbmaster".p_ptmnop_1 on "dbmaster".ptmnop 
    (optcod) using btree ;
alter table "dbmaster".ptmnop add constraint primary key (optcod) 
    constraint "dbmaster".pk_ptmnop  ;
create unique index "dbmaster".p_ptmnpc_1 on "dbmaster".ptmnpc 
    (ptrtyp) using btree ;
alter table "dbmaster".ptmnpc add constraint primary key (ptrtyp) 
    constraint "dbmaster".pk_ptmnpc  ;
create index "dbmaster".f_ptmnpt_2 on "dbmaster".ptmnpt (ptrtyp) 
    using btree ;
create unique index "dbmaster".p_ptmnpt_1 on "dbmaster".ptmnpt 
    (ptrnam) using btree ;
alter table "dbmaster".ptmnpt add constraint primary key (ptrnam) 
    constraint "dbmaster".pk_ptmnpt  ;
create index "dbmaster".f_ptmsgs_2 on "dbmaster".ptmsgs (dmnscd) 
    using btree ;
create unique index "dbmaster".p_ptmsgs_1 on "dbmaster".ptmsgs 
    (mesgcd) using btree ;
alter table "dbmaster".ptmsgs add constraint primary key (mesgcd) 
    constraint "dbmaster".pk_ptmsgs  ;
create index "dbmaster".f_pttabs_2 on "dbmaster".pttabs (langcd) 
    using btree ;
create unique index "dbmaster".p_pttabs_1 on "dbmaster".pttabs 
    (langcd,tabnam) using btree ;
alter table "dbmaster".pttabs add constraint primary key (langcd,
    tabnam) constraint "dbmaster".pk_pttabs  ;
create index "dbmaster".f_ptuprm_2 on "dbmaster".ptuprm (userid) 
    using btree ;
create index "dbmaster".f_ptuprm_3 on "dbmaster".ptuprm (optcod) 
    using btree ;
create unique index "dbmaster".p_ptuprm_1 on "dbmaster".ptuprm 
    (userid,optcod) using btree ;
alter table "dbmaster".ptuprm add constraint primary key (userid,
    optcod) constraint "dbmaster".pk_ptuprm  ;
create index "dbmaster".f_ptuspr_2 on "dbmaster".ptuspr (optcod) 
    using btree ;
create index "dbmaster".f_ptuspr_3 on "dbmaster".ptuspr (userid) 
    using btree ;
create index "dbmaster".f_ptuspr_4 on "dbmaster".ptuspr (prntcd) 
    using btree ;
create unique index "dbmaster".p_ptuspr_1 on "dbmaster".ptuspr 
    (userid,optcod) using btree ;
alter table "dbmaster".ptuspr add constraint primary key (userid,
    optcod) constraint "dbmaster".pk_ptuspr  ;
create index "dbmaster".f_publication_2 on "dbmaster".publication 
    (operator_id) using btree ;
create unique index "dbmaster".p_publication_1 on "dbmaster".publication 
    (pub_id) using btree ;
alter table "dbmaster".publication add constraint primary key 
    (pub_id) constraint "dbmaster".pk_publication  ;
create index "dbmaster".f_publish_time_2 on "dbmaster".publish_time 
    (pub_ttb_id) using btree ;
create unique index "dbmaster".p_publish_time_1 on "dbmaster".publish_time 
    (pub_ttb_id,rpat_orderby) using btree ;
create index "dbmaster".f_publish_tt_2 on "dbmaster".publish_tt 
    (evprf_id) using btree ;
create index "dbmaster".f_publish_tt_3 on "dbmaster".publish_tt 
    (service_id) using btree ;
create index "dbmaster".f_publish_tt_4 on "dbmaster".publish_tt 
    (pub_prof_id) using btree ;
create index "dbmaster".f_publish_tt_5 on "dbmaster".publish_tt 
    (rtpi_prof_id) using btree ;
create unique index "dbmaster".i_publish_tt_6 on "dbmaster".publish_tt 
    (service_id,trip_no,start_time,evprf_id) using btree ;
create index "dbmaster".i_publish_tt_7 on "dbmaster".publish_tt 
    (service_id,trip_no,orun_code,evprf_id) using btree ;
create unique index "dbmaster".p_publish_tt_1 on "dbmaster".publish_tt 
    (pub_ttb_id) using btree ;
alter table "dbmaster".publish_tt add constraint primary key 
    (pub_ttb_id) constraint "dbmaster".pk_publish_tt  ;
create unique index "dbmaster".p_registration_1 on "dbmaster".registration 
    (regis_code) using btree ;
alter table "dbmaster".registration add constraint primary key 
    (regis_code) constraint "dbmaster".pk_registration  ;
create index "dbmaster".f_revision_hist_2 on "dbmaster".revision_hist 
    (rev_type_code) using btree ;
create unique index "dbmaster".p_revision_hist_1 on "dbmaster"
    .revision_hist (revision_id) using btree ;
alter table "dbmaster".revision_hist add constraint primary key 
    (revision_id) constraint "dbmaster".pk_revision_hist  ;
create unique index "dbmaster".p_revision_type_1 on "dbmaster"
    .revision_type (rev_type_code) using btree ;
alter table "dbmaster".revision_type add constraint primary key 
    (rev_type_code) constraint "dbmaster".pk_revision_type  ;
create unique index "dbmaster".p_road_1 on "dbmaster".road (road_code) 
    using btree ;
alter table "dbmaster".road add constraint primary key (road_code) 
    constraint "dbmaster".pk_road  ;
create index "dbmaster".f_route_2 on "dbmaster".route (operator_id) 
    using btree ;
create unique index "dbmaster".i_route_3 on "dbmaster".route (operator_id,
    route_code) using btree ;
create unique index "dbmaster".p_route_1 on "dbmaster".route (route_id) 
    using btree ;
alter table "dbmaster".route add constraint primary key (route_id) 
    constraint "dbmaster".pk_route  ;
create unique index "dbmaster".p_route_area_1 on "dbmaster".route_area 
    (route_area_id) using btree ;
alter table "dbmaster".route_area add constraint primary key 
    (route_area_id) constraint "dbmaster".pk_route_area  ;
create index "dbmaster".f_route_loc_avg_2 on "dbmaster".route_loc_avg 
    (service_id) using btree ;
create index "dbmaster".f_route_loc_avg_3 on "dbmaster".route_loc_avg 
    (profile_id) using btree ;
create index "dbmaster".f_route_loc_avg_4 on "dbmaster".route_loc_avg 
    (location_id) using btree ;
create unique index "dbmaster".i_route_loc_avg_5 on "dbmaster"
    .route_loc_avg (route_int_id) using btree ;
create unique index "dbmaster".p_route_loc_avg_1 on "dbmaster"
    .route_loc_avg (profile_id,location_id) using btree ;
alter table "dbmaster".route_loc_avg add constraint primary key 
    (profile_id,location_id) constraint "dbmaster".pk_route_loc_avg 
     ;
create unique index "dbmaster".p_route_message_1 on "dbmaster"
    .route_message (message_id) using btree ;
alter table "dbmaster".route_message add constraint primary key 
    (message_id) constraint "dbmaster".pk_route_message  ;
create unique index "dbmaster".p_route_profile_1 on "dbmaster"
    .route_profile (profile_id) using btree ;
alter table "dbmaster".route_profile add constraint primary key 
    (profile_id) constraint "dbmaster".pk_route_profile  ;
create index "dbmaster".f_serv_pat_media_2 on "dbmaster".serv_pat_media 
    (service_id,rpat_orderby) using btree ;
create unique index "dbmaster".p_serv_pat_media_1 on "dbmaster"
    .serv_pat_media (service_id,rpat_orderby,media_id) using 
    btree ;
alter table "dbmaster".serv_pat_media add constraint primary 
    key (service_id,rpat_orderby,media_id) constraint "dbmaster"
    .pk_serv_pat_media  ;
create index "dbmaster".f_service_2 on "dbmaster".service (route_id) 
    using btree ;
create unique index "dbmaster".i_service_3 on "dbmaster".service 
    (route_id,service_code,wef_date) using btree ;
create unique index "dbmaster".p_service_1 on "dbmaster".service 
    (service_id) using btree ;
alter table "dbmaster".service add constraint primary key (service_id) 
    constraint "dbmaster".pk_service  ;
create index "dbmaster".f_service_link_2 on "dbmaster".service_link 
    (service_id) using btree ;
create unique index "dbmaster".f_service_link_3 on "dbmaster".service_link 
    (route_id,service_id,str_loc_id,end_loc_id) using btree ;
    
create unique index "dbmaster".p_service_link_1 on "dbmaster".service_link 
    (service_link_id) using btree ;
alter table "dbmaster".service_link add constraint primary key 
    (service_link_id) constraint "dbmaster".pk_service_link  ;
    
create index "dbmaster".f_service_patt_2 on "dbmaster".service_patt 
    (location_id) using btree ;
create index "dbmaster".f_service_patt_3 on "dbmaster".service_patt 
    (service_id) using btree ;
create index "dbmaster".f_service_patt_4 on "dbmaster".service_patt 
    (dest_id) using btree ;
create unique index "dbmaster".p_service_patt_1 on "dbmaster".service_patt 
    (service_id,rpat_orderby) using btree ;
alter table "dbmaster".service_patt add constraint primary key 
    (service_id,rpat_orderby) constraint "dbmaster".pk_service_patt 
     ;
create index "dbmaster".f_servlink_xtrav_2 on "dbmaster".servlink_xtrav 
    (service_link_id) using btree ;
create unique index "dbmaster".p_servlink_xtrav_1 on "dbmaster"
    .servlink_xtrav (service_link_id,seqnum) using btree ;
alter table "dbmaster".servlink_xtrav add constraint primary 
    key (service_link_id,seqnum) constraint "dbmaster".pk_servlink_xtrav 
     ;
create unique index "dbmaster".p_sign_info_1 on "dbmaster".sign_info 
    (sign_number) using btree ;
alter table "dbmaster".sign_info add constraint primary key (sign_number) 
    constraint "dbmaster".pk_sign_info  ;
create unique index "dbmaster".p_signal_prot_1 on "dbmaster".signal_prot 
    (sigprot_code) using btree ;
alter table "dbmaster".signal_prot add constraint primary key 
    (sigprot_code) constraint "dbmaster".pk_signal_prot  ;
create unique index "dbmaster".p_soft_ver_1 on "dbmaster".soft_ver 
    (version_id) using btree ;
alter table "dbmaster".soft_ver add constraint primary key (version_id) 
    constraint "dbmaster".pk_soft_ver  ;
create index "dbmaster".f_special_op_2 on "dbmaster".special_op 
    (operator_id) using btree ;
create index "dbmaster".f_special_op_3 on "dbmaster".special_op 
    (operator_id,op_event) using btree ;
create index "dbmaster".f_special_op_4 on "dbmaster".special_op 
    (operator_id,map_event) using btree ;
create unique index "dbmaster".p_special_op_1 on "dbmaster".special_op 
    (operator_id,route_id,service_id,op_event) using btree ;
alter table "dbmaster".special_op add constraint primary key 
    (operator_id,route_id,service_id,op_event) constraint "dbmaster"
    .pk_special_op  ;
create unique index "dbmaster".p_system_key_1 on "dbmaster".system_key 
    (key_code) using btree ;
alter table "dbmaster".system_key add constraint primary key 
    (key_code) constraint "dbmaster".pk_system_key  ;
create index "dbmaster".f_tlp_adjust_2 on "dbmaster".tlp_adjust 
    (operator_id) using btree ;
create unique index "dbmaster".p_tlp_adjust_1 on "dbmaster".tlp_adjust 
    (operator_id,junction_code,road_code,day_number,start_time) 
    using btree ;
alter table "dbmaster".tlp_adjust add constraint primary key 
    (operator_id,junction_code,road_code,day_number,start_time) 
    constraint "dbmaster".pk_tlp_adjust  ;
create index "dbmaster".f_tlp_sched_adh_2 on "dbmaster".tlp_sched_adh 
    (sigprot_code) using btree ;
create unique index "dbmaster".p_tlp_sched_adh_1 on "dbmaster"
    .tlp_sched_adh (sigprot_code,max_deviation) using btree ;
    
alter table "dbmaster".tlp_sched_adh add constraint primary key 
    (sigprot_code,max_deviation) constraint "dbmaster".pk_tlp_sched_adh 
     ;
create index "dbmaster".f_tmi_place_2 on "dbmaster".tmi_place 
    (data_owner_code) using btree ;
create unique index "dbmaster".i_town_2 on "dbmaster".town (town_name) 
    using btree ;
create unique index "dbmaster".i_town_3 on "dbmaster".town (town_code) 
    using btree ;
create unique index "dbmaster".p_town_1 on "dbmaster".town (town_id) 
    using btree ;
alter table "dbmaster".town add constraint primary key (town_id) 
    constraint "dbmaster".pk_town  ;
create unique index "dbmaster".p_trigger_type_1 on "dbmaster".trigger_type 
    (trigger_type_code) using btree ;
alter table "dbmaster".trigger_type add constraint primary key 
    (trigger_type_code) constraint "dbmaster".pk_trigger_type 
     ;
create index "dbmaster".f_unit_build_2 on "dbmaster".unit_build 
    (unit_type) using btree ;
create index "dbmaster".f_unit_build_3 on "dbmaster".unit_build 
    (operator_id) using btree ;
create index "dbmaster".f_unit_build_4 on "dbmaster".unit_build 
    (version_id) using btree ;
create unique index "dbmaster".i_unit_build_5 on "dbmaster".unit_build 
    (operator_id,build_code) using btree ;
create unique index "dbmaster".p_unit_build_1 on "dbmaster".unit_build 
    (build_id) using btree ;
alter table "dbmaster".unit_build add constraint primary key 
    (build_id) constraint "dbmaster".pk_unit_build  ;
create unique index "dbmaster".p_unit_cfg_type_1 on "dbmaster"
    .unit_cfg_type (unit_type) using btree ;
alter table "dbmaster".unit_cfg_type add constraint primary key 
    (unit_type) constraint "dbmaster".pk_unit_cfg_type  ;
create index "dbmaster".f_unit_history_2 on "dbmaster".unit_history 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_history_1 on "dbmaster".unit_history 
    (build_id,note_date) using btree ;
alter table "dbmaster".unit_history add constraint primary key 
    (build_id,note_date) constraint "dbmaster".pk_unit_history 
     ;
create index "dbmaster".f_unit_param_2 on "dbmaster".unit_param 
    (component_id,param_id) using btree ;
create index "dbmaster".f_unit_param_3 on "dbmaster".unit_param 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_param_1 on "dbmaster".unit_param 
    (build_id,component_id,param_id) using btree ;
alter table "dbmaster".unit_param add constraint primary key 
    (build_id,component_id,param_id) constraint "dbmaster".pk_unit_param 
     ;
create index "dbmaster".f_unit_publish_2 on "dbmaster".unit_publish 
    (pub_id) using btree ;
create index "dbmaster".f_unit_publish_3 on "dbmaster".unit_publish 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_publish_1 on "dbmaster".unit_publish 
    (pub_id,build_id) using btree ;
alter table "dbmaster".unit_publish add constraint primary key 
    (pub_id,build_id) constraint "dbmaster".pk_unit_publish  ;
    
create index "dbmaster".i_unit_reply_2 on "dbmaster".unit_reply 
    (message_id) using btree ;
create unique index "dbmaster".p_unit_reply_1 on "dbmaster".unit_reply 
    (message_id,message_time) using btree ;
alter table "dbmaster".unit_reply add constraint primary key 
    (message_id,message_time) constraint "dbmaster".pk_unit_reply 
     ;
create index "dbmaster".f_vehicle_2 on "dbmaster".vehicle (vehicle_type_id) 
    using btree ;
create index "dbmaster".f_vehicle_3 on "dbmaster".vehicle (build_id) 
    using btree ;
create unique index "dbmaster".p_vehicle_1 on "dbmaster".vehicle 
    (vehicle_id) using btree ;
create unique index "dbmaster".u_vehicle_1 on "dbmaster".vehicle 
    (operator_id,vehicle_code) using btree ;
alter table "dbmaster".vehicle add constraint primary key (vehicle_id) 
    constraint "dbmaster".pk_vehicle  ;
create unique index "dbmaster".i_vehicle_type_2 on "dbmaster".vehicle_type 
    (vehicle_type_code) using btree ;
create unique index "dbmaster".p_vehicle_type_1 on "dbmaster".vehicle_type 
    (vehicle_type_id) using btree ;
alter table "dbmaster".vehicle_type add constraint primary key 
    (vehicle_type_id) constraint "dbmaster".pk_vehicle_type  ;
    
create index "dbmaster".f_event_2 on "dbmaster".event (operator_id) 
    using btree ;
create unique index "dbmaster".i_event_3 on "dbmaster".event (event_id) 
    using btree ;
create unique index "dbmaster".p_event_1 on "dbmaster".event (operator_id,
    event_id) using btree ;
alter table "dbmaster".event add constraint primary key (operator_id,
    event_id) constraint "dbmaster".pk_event  ;
create index "dbmaster".f_pergrval_2 on "dbmaster".pergrval (operator_id,
    orun_code) using btree ;
create index "dbmaster".f_pergrval_3 on "dbmaster".pergrval (operator_id,
    pegr_code) using btree ;
create unique index "dbmaster".p_pergrval_1 on "dbmaster".pergrval 
    (operator_id,orun_code,pegr_code,valid_from) using btree 
    ;
alter table "dbmaster".pergrval add constraint primary key (operator_id,
    orun_code,pegr_code,valid_from) constraint "dbmaster".pk_pergrval 
     ;
create index "dbmaster".f_unit_log_hist_2 on "dbmaster".unit_log_hist 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_log_hist_1 on "dbmaster"
    .unit_log_hist (build_id,logfile_date) using btree ;
alter table "dbmaster".unit_log_hist add constraint primary key 
    (build_id,logfile_date) constraint "dbmaster".pk_unit_log_hist 
     ;
create index "dbmaster".f_route_alias_2 on "dbmaster".route_alias 
    (route_id) using btree ;
create unique index "dbmaster".p_route_alias_1 on "dbmaster".route_alias 
    (route_id,route_alias_code) using btree ;
alter table "dbmaster".route_alias add constraint primary key 
    (route_id,route_alias_code) constraint "dbmaster".pk_route_alias 
     ;
create index "dbmaster".f_feed_imprtal_2 on "dbmaster".feed_imprtal 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_imprtal_1 on "dbmaster".feed_imprtal 
    (txc_pub_id,operator_id,route_code,route_alias) using btree 
    ;
alter table "dbmaster".feed_imprtal add constraint primary key 
    (txc_pub_id,operator_id,route_code,route_alias) constraint 
    "dbmaster".pk_feed_imprtal  ;
create index "dbmaster".f_pthelp_2 on "dbmaster".pthelp (langcd) 
    using btree ;
create index "dbmaster".f_pthelp_3 on "dbmaster".pthelp (mesgcd) 
    using btree ;
create unique index "dbmaster".p_pthelp_1 on "dbmaster".pthelp 
    (langcd,mesgcd) using btree ;
alter table "dbmaster".pthelp add constraint primary key (langcd,
    mesgcd) constraint "dbmaster".pk_pthelp  ;
create index "dbmaster".f_feed_imphead_2 on "dbmaster".feed_imphead 
    (txc_pub_type) using btree ;
create index "dbmaster".f_feed_imphead_3 on "dbmaster".feed_imphead 
    (operator_id) using btree ;
create unique index "dbmaster".p_feed_imphead_1 on "dbmaster".feed_imphead 
    (txc_pub_id,operator_id) using btree ;
alter table "dbmaster".feed_imphead add constraint primary key 
    (txc_pub_id,operator_id) constraint "dbmaster".pk_feed_imphead 
     ;
create index "dbmaster".f_feed_impdest_2 on "dbmaster".feed_impdest 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_impdest_1 on "dbmaster".feed_impdest 
    (txc_pub_id,operator_id,destination_code) using btree ;
alter table "dbmaster".feed_impdest add constraint primary key 
    (txc_pub_id,operator_id,destination_code) constraint "dbmaster"
    .pk_feed_impdest  ;
create index "dbmaster".f_feed_imprept_2 on "dbmaster".feed_imprept 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".i_feed_imprept_3 on "dbmaster".feed_imprept 
    (txc_rep_seqnum) using btree ;
create unique index "dbmaster".p_feed_imprept_1 on "dbmaster".feed_imprept 
    (txc_pub_id,operator_id,txc_rep_seqnum) using btree ;
alter table "dbmaster".feed_imprept add constraint primary key 
    (txc_pub_id,operator_id,txc_rep_seqnum) constraint "dbmaster"
    .pk_feed_imprept  ;
create index "dbmaster".f_feed_impmedi_2 on "dbmaster".feed_impmedi 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_impmedi_1 on "dbmaster".feed_impmedi 
    (txc_pub_id,operator_id,media_code) using btree ;
alter table "dbmaster".feed_impmedi add constraint primary key 
    (txc_pub_id,operator_id,media_code) constraint "dbmaster".pk_feed_impmedi 
     ;
create index "dbmaster".f_feed_imprtar_2 on "dbmaster".feed_imprtar 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_imprtar_1 on "dbmaster".feed_imprtar 
    (txc_pub_id,operator_id,area_code) using btree ;
alter table "dbmaster".feed_imprtar add constraint primary key 
    (txc_pub_id,operator_id,area_code) constraint "dbmaster".pk_feed_imprtar 
     ;
create index "dbmaster".f_feed_imploca_2 on "dbmaster".feed_imploca 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_imploca_1 on "dbmaster".feed_imploca 
    (txc_pub_id,operator_id,location) using btree ;
alter table "dbmaster".feed_imploca add constraint primary key 
    (txc_pub_id,operator_id,location) constraint "dbmaster".pk_feed_imploca 
     ;
create index "dbmaster".i_unit_gps_log_2 on "dbmaster".unit_gps_log 
    (build_id) using btree ;
create index "dbmaster".i_unit_wlan_log_1 on "dbmaster".unit_wlan_log 
    (pub_id) using btree ;
create index "dbmaster".i_unit_wlan_log_2 on "dbmaster".unit_wlan_log 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_wlan_log_1 on "dbmaster"
    .unit_wlan_log (pub_id,build_id,action_time,action_value1) 
    using btree ;
alter table "dbmaster".unit_wlan_log add constraint primary key 
    (pub_id,build_id,action_time,action_value1) constraint "dbmaster"
    .pk_unit_wlan_log  ;
create unique index "dbmaster".websrv_idx1 on "dbmaster".websrv_sess 
    (websrv_code) using btree ;
alter table "dbmaster".websrv_sess add constraint primary key 
    (websrv_code) constraint "dbmaster".pk_websrv_sess  ;
create index "dbmaster".f_tmi_bloc_2 on "dbmaster".tmi_bloc (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_bloc_1 on "dbmaster".tmi_bloc 
    (data_owner_code,org_unit_code,timetable_ver_code,veh_schedule_code,
    block_code,day_type_code) using btree ;
alter table "dbmaster".tmi_bloc add constraint primary key (data_owner_code,
    org_unit_code,timetable_ver_code,veh_schedule_code,block_code,
    day_type_code) constraint "dbmaster".pk_tmi_bloc  ;
create unique index "dbmaster".p_tmi_cresc_1 on "dbmaster".tmi_cresc 
    (data_owner_code,timetable_ver_code,crew_schedule_code,org_unit_code,
    pegr_code,day_type_code) using btree ;
alter table "dbmaster".tmi_cresc add constraint primary key (data_owner_code,
    timetable_ver_code,crew_schedule_code,org_unit_code,pegr_code,
    day_type_code) constraint "dbmaster".pk_tmi_cresc  ;
create unique index "dbmaster".p_tmi_daow_1 on "dbmaster".tmi_daow 
    (data_owner_code) using btree ;
alter table "dbmaster".tmi_daow add constraint primary key (data_owner_code) 
    constraint "dbmaster".pk_tmi_daow  ;
create unique index "dbmaster".p_tmi_daty_1 on "dbmaster".tmi_daty 
    (day_type_code) using btree ;
alter table "dbmaster".tmi_daty add constraint primary key (day_type_code) 
    constraint "dbmaster".pk_tmi_daty  ;
create index "dbmaster".f_tmi_dest_2 on "dbmaster".tmi_dest (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_dest_1 on "dbmaster".tmi_dest 
    (data_owner_code,dest_code) using btree ;
alter table "dbmaster".tmi_dest add constraint primary key (data_owner_code,
    dest_code) constraint "dbmaster".pk_tmi_dest  ;
create index "dbmaster".f_tmi_driv_2 on "dbmaster".tmi_driv (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_driv_1 on "dbmaster".tmi_driv 
    (data_owner_code,orun_code,driver_code) using btree ;
alter table "dbmaster".tmi_driv add constraint primary key (data_owner_code,
    orun_code,driver_code) constraint "dbmaster".pk_tmi_driv  
    ;
create unique index "dbmaster".p_tmi_duac_1 on "dbmaster".tmi_duac 
    (data_owner_code,timetable_ver_code,crew_schedule_code,duty_code,
    sequence_in_duty,csc_sched_type) using btree ;
alter table "dbmaster".tmi_duac add constraint primary key (data_owner_code,
    timetable_ver_code,crew_schedule_code,duty_code,sequence_in_duty,
    csc_sched_type) constraint "dbmaster".pk_tmi_duac  ;
create unique index "dbmaster".p_tmi_duty_1 on "dbmaster".tmi_duty 
    (data_owner_code,timetable_ver_code,crew_schedule_code,duty_code,
    day_type_code) using btree ;
alter table "dbmaster".tmi_duty add constraint primary key (data_owner_code,
    timetable_ver_code,crew_schedule_code,duty_code,day_type_code) 
    constraint "dbmaster".pk_tmi_duty  ;
create index "dbmaster".f_tmi_excday_2 on "dbmaster".tmi_excday 
    (data_owner_code) using btree ;
create index "dbmaster".f_tmi_exopday_2 on "dbmaster".tmi_exopday 
    (data_owner_code) using btree ;
create unique index "dbmaster".p_tmi_exopday_1 on "dbmaster".tmi_exopday 
    (data_owner_code,org_unit_code,valid_date) using btree ;
alter table "dbmaster".tmi_exopday add constraint primary key 
    (data_owner_code,org_unit_code,valid_date) constraint "dbmaster"
    .pk_tmi_exopday  ;
create index "dbmaster".f_tmi_line_2 on "dbmaster".tmi_line (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_line_1 on "dbmaster".tmi_line 
    (data_owner_code,line_num_planning) using btree ;
alter table "dbmaster".tmi_line add constraint primary key (data_owner_code,
    line_num_planning) constraint "dbmaster".pk_tmi_line  ;
create index "dbmaster".f_tmi_lirorunt_2 on "dbmaster".tmi_lirorunt 
    (data_owner_code) using btree ;
create unique index "dbmaster".p_tmi_lirorunt_1 on "dbmaster".tmi_lirorunt 
    (data_owner_code,time_dem_type_code,sequence_in_route) using 
    btree ;
alter table "dbmaster".tmi_lirorunt add constraint primary key 
    (data_owner_code,time_dem_type_code,sequence_in_route) constraint 
    "dbmaster".pk_tmi_lirorunt  ;
create index "dbmaster".f_tmi_opconarea_2 on "dbmaster".tmi_opconarea 
    (data_owner_code) using btree ;
create unique index "dbmaster".p_tmi_opconarea_1 on "dbmaster"
    .tmi_opconarea (data_owner_code,opconarea_code) using btree 
    ;
alter table "dbmaster".tmi_opconarea add constraint primary key 
    (data_owner_code,opconarea_code) constraint "dbmaster".pk_tmi_opconarea 
     ;
create index "dbmaster".f_tmi_orun_2 on "dbmaster".tmi_orun (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_orun_1 on "dbmaster".tmi_orun 
    (data_owner_code,orun_code) using btree ;
alter table "dbmaster".tmi_orun add constraint primary key (data_owner_code,
    orun_code) constraint "dbmaster".pk_tmi_orun  ;
create index "dbmaster".f_tmi_pegr_2 on "dbmaster".tmi_pegr (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_pegr_1 on "dbmaster".tmi_pegr 
    (data_owner_code,pegr_code) using btree ;
alter table "dbmaster".tmi_pegr add constraint primary key (data_owner_code,
    pegr_code) constraint "dbmaster".pk_tmi_pegr  ;
create unique index "dbmaster".p_tmi_pergrval_1 on "dbmaster".tmi_pergrval 
    (data_owner_code,org_unit_code,pegr_code,valid_from) using 
    btree ;
alter table "dbmaster".tmi_pergrval add constraint primary key 
    (data_owner_code,org_unit_code,pegr_code,valid_from) constraint 
    "dbmaster".pk_tmi_pergrval  ;
create index "dbmaster".f_tmi_poininro_2 on "dbmaster".tmi_poininro 
    (data_owner_code) using btree ;
create unique index "dbmaster".p_tmi_poininro_1 on "dbmaster".tmi_poininro 
    (data_owner_code,line_number,direction,route_variant_code,
    user_stop_code,sequence_in_route) using btree ;
alter table "dbmaster".tmi_poininro add constraint primary key 
    (data_owner_code,line_number,direction,route_variant_code,
    user_stop_code,sequence_in_route) constraint "dbmaster".pk_tmi_poininro 
     ;
create index "dbmaster".f_tmi_rout_2 on "dbmaster".tmi_rout (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_rout_1 on "dbmaster".tmi_rout 
    (data_owner_code,line_num_planning,direction,int_route_code) 
    using btree ;
alter table "dbmaster".tmi_rout add constraint primary key (data_owner_code,
    line_num_planning,direction,int_route_code) constraint "dbmaster"
    .pk_tmi_rout  ;
create index "dbmaster".f_tmi_stoppoint_2 on "dbmaster".tmi_stoppoint 
    (data_owner_code) using btree ;
create unique index "dbmaster".p_tmi_stoppoint_1 on "dbmaster"
    .tmi_stoppoint (data_owner_code,user_stop_code,stop_valid_from) 
    using btree ;
alter table "dbmaster".tmi_stoppoint add constraint primary key 
    (data_owner_code,user_stop_code,stop_valid_from) constraint 
    "dbmaster".pk_tmi_stoppoint  ;
create index "dbmaster".f_tmi_timedty_2 on "dbmaster".tmi_timedty 
    (data_owner_code) using btree ;
create index "dbmaster".i_tmi_timedty_2 on "dbmaster".tmi_timedty 
    (data_owner_code,time_dem_type_code) using btree ;
create index "dbmaster".i_tmi_timedty_3 on "dbmaster".tmi_timedty 
    (data_owner_code,line_num_planning,int_route_code) using 
    btree ;
create unique index "dbmaster".p_tmi_timedty_1 on "dbmaster".tmi_timedty 
    (data_owner_code,time_dem_type_code,pegr_code,day_type_code,
    line_num_planning,direction,int_route_code,start_time,time_dem_val_fr) 
    using btree ;
alter table "dbmaster".tmi_timedty add constraint primary key 
    (data_owner_code,time_dem_type_code,pegr_code,day_type_code,
    line_num_planning,direction,int_route_code,start_time,time_dem_val_fr) 
    constraint "dbmaster".pk_tmi_timedty  ;
create unique index "dbmaster".p_tmi_tive_1 on "dbmaster".tmi_tive 
    (data_owner_code,org_unit_code,timetable_ver_code) using 
    btree ;
alter table "dbmaster".tmi_tive add constraint primary key (data_owner_code,
    org_unit_code,timetable_ver_code) constraint "dbmaster".pk_tmi_tive 
     ;
create index "dbmaster".f_tmi_veh_2 on "dbmaster".tmi_veh (data_owner_code) 
    using btree ;
create index "dbmaster".f_tmi_veh_3 on "dbmaster".tmi_veh (vehicle_type) 
    using btree ;
create unique index "dbmaster".p_tmi_veh_1 on "dbmaster".tmi_veh 
    (data_owner_code,registration_num) using btree ;
alter table "dbmaster".tmi_veh add constraint primary key (data_owner_code,
    registration_num) constraint "dbmaster".pk_tmi_veh  ;
create index "dbmaster".f_tmi_vejo_2 on "dbmaster".tmi_vejo (data_owner_code) 
    using btree ;
create unique index "dbmaster".p_tmi_vejo_1 on "dbmaster".tmi_vejo 
    (data_owner_code,org_unit_code,timetable_ver_code,veh_schedule_code,
    block_code,sequence_in_block,line_num_planning,int_route_code,
    trip_number,time_dem_code) using btree ;
alter table "dbmaster".tmi_vejo add constraint primary key (data_owner_code,
    org_unit_code,timetable_ver_code,veh_schedule_code,block_code,
    sequence_in_block,line_num_planning,int_route_code,trip_number,
    time_dem_code) constraint "dbmaster".pk_tmi_vejo  ;
create unique index "dbmaster".p_tmi_vesc_1 on "dbmaster".tmi_vesc 
    (data_owner_code,orun_code,timetable_ver_code,veh_schedule_code,
    day_type_code,pegr_code) using btree ;
alter table "dbmaster".tmi_vesc add constraint primary key (data_owner_code,
    orun_code,timetable_ver_code,veh_schedule_code,day_type_code,
    pegr_code) constraint "dbmaster".pk_tmi_vesc  ;
create unique index "dbmaster".p_tmi_vety_1 on "dbmaster".tmi_vety 
    (vety_code) using btree ;
alter table "dbmaster".tmi_vety add constraint primary key (vety_code) 
    constraint "dbmaster".pk_tmi_vety  ;
create unique index "dbmaster".ix_unitstat on "dbmaster".unit_status 
    (build_id) using btree ;
alter table "dbmaster".unit_status add constraint primary key 
    (build_id) constraint "informix".pk_unit_status  ;
create unique index "dbmaster".p_unit_statrt_1 on "dbmaster".unit_status_rt 
    (build_id) using btree ;
alter table "dbmaster".unit_status_rt add constraint primary 
    key (build_id) constraint "informix".pk_unit_status_rt  ;
create unique index "dbmaster".p_message_type_1 on "dbmaster".message_type 
    (msg_type) using btree ;
create index "dbmaster".f_route_pat_1 on "dbmaster".route_pattern 
    (route_id,direction,location_id) using btree ;
create index "dbmaster".f_route_pat_2 on "dbmaster".route_pattern 
    (sequence) using btree ;
create unique index "dbmaster".p_route_pat_1 on "dbmaster".route_pattern 
    (rpat_id) using btree ;
alter table "dbmaster".route_pattern add constraint primary key 
    (rpat_id) constraint "dbmaster".pk_route_pattern  ;
create index "dbmaster".f_route_pat_loc_2 on "dbmaster".route_patt_loc 
    (location_id) using btree ;
create index "dbmaster".p_route_pat_loc_1 on "dbmaster".route_patt_loc 
    (rpat_id) using btree ;
create unique index "dbmaster".pk_user_route on "dbmaster".user_route 
    (userid,operator_id,route_id) using btree ;
create index "dbmaster".f_feed_impstag_2 on "dbmaster".feed_impstag 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".p_feed_impstag_1 on "dbmaster".feed_impstag 
    (txc_pub_id,operator_id,location_code,route_code) using btree 
    ;
create unique index "dbmaster".i_dbpatch on "dbmaster".database_patch 
    (patch_no) using btree ;
create index "dbmaster".f_unit_bld_media_2 on "dbmaster".unit_bld_media 
    (build_id) using btree ;
create index "dbmaster".f_unit_bld_media_3 on "dbmaster".unit_bld_media 
    (media_id) using btree ;
create unique index "dbmaster".p_unit_bld_media_1 on "dbmaster"
    .unit_bld_media (build_id,media_id) using btree ;
alter table "dbmaster".unit_bld_media add constraint primary 
    key (build_id,media_id) constraint "dbmaster".pk_unit_bld_media 
     ;
create index "dbmaster".f_soft_ver_media_2 on "dbmaster".soft_ver_media 
    (version_id) using btree ;
create index "dbmaster".f_soft_ver_media_3 on "dbmaster".soft_ver_media 
    (media_id) using btree ;
create unique index "dbmaster".p_soft_ver_media_1 on "dbmaster"
    .soft_ver_media (version_id,media_id) using btree ;
alter table "dbmaster".soft_ver_media add constraint primary 
    key (version_id,media_id) constraint "dbmaster".pk_soft_ver_media 
     ;
create unique index "dbmaster".p_time_band_1 on "dbmaster".time_band 
    (band_id) using btree ;
create index "dbmaster".p_time_band_2 on "dbmaster".time_band 
    (evprf_id) using btree ;
create index "dbmaster".p_time_band_3 on "dbmaster".time_band 
    (route_id) using btree ;
alter table "dbmaster".time_band add constraint primary key (band_id) 
    constraint "dbmaster".pk_time_band  ;
create unique index "dbmaster".p_loc_int_1 on "dbmaster".loc_interval 
    (loc_from,loc_to,band_id) using btree ;
create index "dbmaster".p_loc_int_2 on "dbmaster".loc_interval 
    (loc_from,loc_to) using btree ;
alter table "dbmaster".loc_interval add constraint primary key 
    (loc_from,loc_to,band_id) constraint "dbmaster".pk_loc_interval 
     ;
create index "dbmaster".i_pub_exprept_1 on "dbmaster".pub_exprept 
    (pub_id) using btree ;
create unique index "dbmaster".i_pub_exprept_2 on "dbmaster".pub_exprept 
    (rep_seqnum) using btree ;
create unique index "dbmaster".p_geogate_1 on "dbmaster".geogate 
    (route_id,location_id) using btree ;
alter table "dbmaster".geogate add constraint primary key (route_id,
    location_id) constraint "dbmaster".pk_geogate  ;
create index "dbmaster".f_route_location_2 on "dbmaster".route_location 
    (service_id,rpat_orderby) using btree ;
create index "dbmaster".f_route_location_3 on "dbmaster".route_location 
    (profile_id) using btree ;
create index "dbmaster".f_route_location_4 on "dbmaster".route_location 
    (profile_id,rpat_orderby,location_id) using btree ;
create unique index "dbmaster".p_route_location_1 on "dbmaster"
    .route_location (profile_id,service_id,rpat_orderby) using 
    btree ;
alter table "dbmaster".route_location add constraint primary 
    key (profile_id,service_id,rpat_orderby) constraint "dbmaster"
    .pk_route_location  ;
create unique index "dbmaster".f_despatcher_1 on "dbmaster".despatcher 
    (desp_code) using btree ;
create index "dbmaster".f_unit_alert_2 on "dbmaster".unit_alert 
    (build_id) using btree ;
create index "dbmaster".i_unit_alert_1 on "dbmaster".unit_alert 
    (alert_time) using btree ;
create unique index "dbmaster".i_unit_alert_3 on "dbmaster".unit_alert 
    (alert_id) using btree ;
create index "dbmaster".i_unit_message_2 on "dbmaster".unit_message 
    (build_id) using btree ;
create index "dbmaster".i_unit_message_3 on "dbmaster".unit_message 
    (msg_time) using btree ;
create index "dbmaster".i_unit_message_4 on "dbmaster".unit_message 
    (build_id,msg_time) using btree ;
create index "dbmaster".i_driver_alert_1 on "dbmaster".driver_alert 
    (build_id) using btree ;
create index "dbmaster".i_driver_alert_2 on "dbmaster".driver_alert 
    (alert_time) using btree ;
create unique index "dbmaster".p_driver_alert on "dbmaster".driver_alert 
    (alert_id) using btree ;
create index "dbmaster".f_unit_alarc_2 on "dbmaster".unit_alert_arc 
    (build_id) using btree ;
create index "dbmaster".i_unit_alarc_1 on "dbmaster".unit_alert_arc 
    (alert_time) using btree ;
create index "dbmaster".i_unit_mess_arc_2 on "dbmaster".unit_mess_arc 
    (build_id) using btree ;
create index "dbmaster".i_unit_mess_arc_3 on "dbmaster".unit_mess_arc 
    (msg_time) using btree ;
create index "dbmaster".i_unit_mess_arc_4 on "dbmaster".unit_mess_arc 
    (build_id,msg_time) using btree ;
create unique index "dbmaster".desp_message on "dbmaster".desp_message 
    (msg_id) using btree ;
create index "dbmaster".i_desp_mess1 on "dbmaster".desp_message 
    (msg_type) using btree ;
create index "dbmaster".i_mod_trip1 on "dbmaster".tt_mod_trip 
    (mod_id) using btree ;
create index "dbmaster".i_mod_trip2 on "dbmaster".tt_mod_trip 
    (pub_ttb_id) using btree ;
create index "dbmaster".ix_gprshist2 on "dbmaster".gprs_history 
    (msg_date,build_code) using btree ;
create index "dbmaster".p_serv_pat_ann_1 on "dbmaster".serv_pat_ann 
    (service_id,rpat_orderby) using btree ;
create unique index "dbmaster".i_paramtime on "dbmaster".dcd_param_time 
    (operator_id,route_id,time_low,time_high) using btree ;
create unique index "dbmaster".p_statapp_1 on "dbmaster".station_app 
    (station_id,approach_id) using btree ;
create unique index "dbmaster".p_statloc_1 on "dbmaster".station_loc 
    (station_id,stand_id) using btree ;
create unique index "dbmaster".i_tmp_obu3 on "dbmaster".tmp_obu3 
    (dt,vehicle_code) using btree ;
create index "dbmaster".ix353_1 on "dbmaster".tmp_obu3 (dt) using 
    btree ;
create index "dbmaster".ix353_2 on "dbmaster".tmp_obu3 (vehicle_code) 
    using btree ;
create unique index "dbmaster".p_unit_log_1 on "dbmaster".unit_log 
    (log_id) using btree ;
create index "dbmaster".unit_log_2 on "dbmaster".unit_log (message_type,
    log_time) using btree ;
alter table "dbmaster".unit_log add constraint primary key (log_id) 
    constraint "dbmaster".pk_unit_log  ;
create index "dbmaster".i_t_xched on "dbmaster".x_schedules (schedule_id) 
    using btree ;
create unique index "dbmaster".i_desp_auth1 on "dbmaster".desp_auth 
    (user_id) using btree ;
create unique index "dbmaster".p_act_rt_duty_1 on "dbmaster".active_rt_duty 
    (schedule_id,rpat_orderby) using btree ;
create unique index "dbmaster".p_arch_rt_duty_1 on "dbmaster".archive_rt_duty 
    (schedule_id,rpat_orderby) using btree ;
create index "dbmaster".f_csv_imprept_2 on "dbmaster".csv_imprept 
    (txc_pub_id,operator_id) using btree ;
create unique index "dbmaster".i_csv_imprept_3 on "dbmaster".csv_imprept 
    (txc_rep_seqnum) using btree ;
create unique index "dbmaster".p_csv_imprept_1 on "dbmaster".csv_imprept 
    (txc_pub_id,operator_id,txc_rep_seqnum) using btree ;
alter table "dbmaster".csv_imprept add constraint primary key 
    (txc_pub_id,operator_id,txc_rep_seqnum) constraint "dbmaster"
    .pk_csv_imprept  ;
create unique index "dbmaster".p_archive_rt_vp_1 on "dbmaster"
    .archive_rt_vp (schedule_id,point_type,point_code) using 
    btree ;
create index "dbmaster".p_archive_rt_vp_2 on "dbmaster".archive_rt_vp 
    (point_code) using btree ;
alter table "dbmaster".archive_rt_vp add constraint primary key 
    (schedule_id,point_type,point_code) constraint "dbmaster".pk_archive_rt_vp 
     ;
create index "dbmaster".f_route_param_2 on "dbmaster".route_param 
    (default_player) using btree ;
create index "dbmaster".f_route_param_3 on "dbmaster".route_param 
    (default_image) using btree ;
create index "dbmaster".f_route_param_4 on "dbmaster".route_param 
    (route_id) using btree ;
create index "dbmaster".f_unit_stat_net2 on "dbmaster".unit_status_net 
    (build_id) using btree ;
create unique index "dbmaster".p_unit_stat_net_1 on "dbmaster"
    .unit_status_net (build_id,network_id) using btree ;
alter table "dbmaster".unit_status_net add constraint primary 
    key (build_id,network_id) constraint "dbmaster".pk_unit_stat_net 
     ;
create unique index "dbmaster".p_unit_bld_lm_1 on "dbmaster".unit_build_log_msg 
    (build_id) using btree ;
create index "dbmaster".f_unit_index_2 on "dbmaster".unit_index 
    (pub_id,build_id) using btree ;
create unique index "dbmaster".p_unit_index_1 on "dbmaster".unit_index 
    (pub_id,build_id,doc_no) using btree ;
alter table "dbmaster".unit_index add constraint primary key 
    (pub_id,build_id,doc_no) constraint "dbmaster".pk_unit_index 
     ;
create unique index "dbmaster".p_implogrep_1 on "dbmaster".import_log_report 
    (import_id,line_no) using btree ;
create index "dbmaster".f_autort_sched_2 on "dbmaster".autort_sched 
    (route_id) using btree ;
create index "dbmaster".f_autort_sched_3 on "dbmaster".autort_sched 
    (profile_id) using btree ;
create index "dbmaster".p_autort_sched_1 on "dbmaster".autort_sched 
    (route_id,duty_no,trip_no,running_no) using btree ;
create unique index "dbmaster".p_act_rt_lost_1 on "dbmaster".active_rt_lost 
    (schedule_id,rpat_orderby) using btree ;
create index "dbmaster".i_dcd_prediction on "dbmaster".dcd_prediction 
    (schedule_id,rpat_orderby) using btree ;
create index "dbmaster".i_dcd_prediction_2 on "dbmaster".dcd_prediction 
    (send_time) using btree ;
create unique index "dbmaster".ix_t_scoot_veh on "dbmaster".t_scoot_veh_list 
    (build_code) using btree ;
create unique index "dbmaster".p_gps_pred_loc on "dbmaster".gps_pred_loc 
    (location_id) using btree ;
create unique index "peterd".pk_user_vehicle on "dbmaster".user_vehicle 
    (userid,operator_id,vehicle_id) using btree ;
create unique index "peterd".pk_user_build on "dbmaster".user_build 
    (userid,operator_id,build_id) using btree ;
create index "dbmaster".i_icount_1 on "dbmaster".raw_count (count_id) 
    using btree ;
create index "dbmaster".i_gprscov_sent on "dbmaster".gprscov_sent 
    (build_code,msg_id) using btree ;
create index "dbmaster".i_gprscov_recd on "dbmaster".gprscov_recd 
    (build_code,msg_id) using btree ;
create index "dbmaster".i_passenger_count_1 on "dbmaster".passenger_count 
    (schedule_id,rpat_orderby) using btree ;
create index "dbmaster".ih_performance on "dbmaster".ih_performance 
    (operator_id,dayno,running_no) using btree ;
create index "dbmaster".unit_message_hr on "dbmaster".unit_message_hr 
    (build_id,dayno,day_hour) using btree ;
create index "dbmaster".pk_tlp_request on "dbmaster".tlp_request 
    (vehicle_id,trigger_time) using btree ;
create index "dbmaster".ix_unitstatsign on "dbmaster".unit_status_sign 
    (build_id) using btree ;
create unique index "dbmaster".p_pl_medtype on "dbmaster".playlist_media_type 
    (media_type_code) using btree ;
alter table "dbmaster".playlist_media_type add constraint primary 
    key (media_type_code)  ;
create unique index "dbmaster".p_pl_program on "dbmaster".playlist_program 
    (program_id) using btree ;
alter table "dbmaster".playlist_program add constraint primary 
    key (program_id)  ;
create unique index "dbmaster".p_pl_block on "dbmaster".playlist_block 
    (block_id) using btree ;
alter table "dbmaster".playlist_block add constraint primary 
    key (block_id)  ;
create unique index "dbmaster".p_pl_slot on "dbmaster".playlist_slot 
    (block_id,sequence) using btree ;
alter table "dbmaster".playlist_slot add constraint primary key 
    (block_id,sequence)  ;
create unique index "dbmaster".p_playlist on "dbmaster".playlist 
    (playlist_id) using btree ;
alter table "dbmaster".playlist add constraint primary key (playlist_id) 
     ;
create unique index "dbmaster".p_pl_media on "dbmaster".playlist_media 
    (playlist_id,sequence) using btree ;
alter table "dbmaster".playlist_media add constraint primary 
    key (playlist_id,sequence)  ;
create index "dbmaster".p_pl_condition on "dbmaster".playlist_condition 
    (playlist_id,sequence) using btree ;
create unique index "dbmaster".p_pl_attribt on "dbmaster".playlist_attrib_type 
    (attrib_type) using btree ;
alter table "dbmaster".playlist_attrib_type add constraint primary 
    key (attrib_type)  ;
create unique index "dbmaster".p_pl_attribv on "dbmaster".playlist_attrib_value 
    (attrib_type,attrib_value) using btree ;
alter table "dbmaster".playlist_attrib_value add constraint primary 
    key (attrib_type,attrib_value)  ;
create unique index "dbmaster".p_pl_attrib on "dbmaster".playlist_attrib 
    (playlist_id,sequence,attrib_type) using btree ;
alter table "dbmaster".playlist_attrib add constraint primary 
    key (playlist_id,sequence,attrib_type)  ;
create unique index "dbmaster".p_pl_condt on "dbmaster".playlist_cond_type 
    (cond_type) using btree ;
alter table "dbmaster".playlist_cond_type add constraint primary 
    key (cond_type)  ;
create unique index "dbmaster".p_pl_condv on "dbmaster".playlist_cond_value 
    (cond_type,cond_value) using btree ;
alter table "dbmaster".playlist_cond_value add constraint primary 
    key (cond_type,cond_value)  ;
create index "dbmaster".i_post_code_1 on "dbmaster".post_code 
    (latitude) using btree ;
create index "dbmaster".i_post_code_2 on "dbmaster".post_code 
    (longitude) using btree ;
create unique index "dbmaster".p_loc_media on "dbmaster".location_media 
    (location_id,route_id) using btree ;
create index "dbmaster".i_login_audit1 on "dbmaster".login_audit 
    (login_time) using btree ;
create index "dbmaster".ih_performance_route on "dbmaster".ih_performance_route 
    (operator_id,dayno,route_id,running_no) using btree ;
create index "dbmaster".i_stops_1 on "dbmaster".stop (latitude) 
    using btree ;
create index "dbmaster".i_stops_2 on "dbmaster".stop (longitude) 
    using btree ;
create index "dbmaster".i_stops_3 on "dbmaster".stop (naptan_code) 
    using btree ;
create index "dbmaster".i_stops_4 on "dbmaster".stop (common_name) 
    using btree ;


alter table "dbmaster".archive_rt add constraint (foreign key 
    (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_archive_rt_a);
alter table "dbmaster".publish_tt add constraint (foreign key 
    (evprf_id) references "dbmaster".event_profile  constraint 
    "dbmaster".f_publish_tt_a);
alter table "dbmaster".archive_rt add constraint (foreign key 
    (employee_id) references "dbmaster".employee  constraint "dbmaster"
    .f_archive_rt_c);
alter table "dbmaster".media add constraint (foreign key (media_type_code) 
    references "dbmaster".media_type  constraint "dbmaster".f_media_a);
    
alter table "dbmaster".media add constraint (foreign key (media_frm_code) 
    references "dbmaster".media_format  constraint "dbmaster".f_media_b);
    
alter table "dbmaster".archive_rt_loc add constraint (foreign 
    key (schedule_id) references "dbmaster".archive_rt  constraint 
    "dbmaster".f_archive_rt_loc_a);
alter table "dbmaster".active_rt add constraint (foreign key 
    (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_active_rt_a);
alter table "dbmaster".publish_tt add constraint (foreign key 
    (service_id) references "dbmaster".service  constraint "dbmaster"
    .f_publish_tt_b);
alter table "dbmaster".active_rt add constraint (foreign key 
    (employee_id) references "dbmaster".employee  constraint "dbmaster"
    .f_active_rt_c);
alter table "dbmaster".active_rt_loc add constraint (foreign 
    key (schedule_id) references "dbmaster".active_rt  constraint 
    "dbmaster".f_active_rt_loc_a);
alter table "dbmaster".autoroute_time add constraint (foreign 
    key (service_id) references "dbmaster".service  constraint 
    "dbmaster".f_autoroute_time_a);
alter table "dbmaster".autoroute_time add constraint (foreign 
    key (auto_prof_id) references "dbmaster".autort_profile  constraint 
    "dbmaster".f_autoroute_time_b);
alter table "dbmaster".autort_config add constraint (foreign 
    key (service_id) references "dbmaster".service  constraint 
    "dbmaster".f_autort_config_a);
alter table "dbmaster".destination add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_destination_a);
alter table "dbmaster".event_pattern add constraint (foreign 
    key (evprf_id) references "dbmaster".event_profile  constraint 
    "dbmaster".f_event_pattern_a);
alter table "dbmaster".fare_stage add constraint (foreign key 
    (service_id) references "dbmaster".service  constraint "dbmaster"
    .f_fare_stage_a);
alter table "dbmaster".feed_history add constraint (foreign key 
    (feed_type_id) references "dbmaster".feed_type  constraint 
    "dbmaster".f_feed_history_a);
alter table "dbmaster".feed_type add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_feed_type_a);
alter table "dbmaster".feed_type add constraint (foreign key 
    (format_code) references "dbmaster".feed_format  constraint 
    "dbmaster".f_feed_type_b);
alter table "dbmaster".gprs_mapping add constraint (foreign key 
    (build_id) references "dbmaster".unit_build  constraint "dbmaster"
    .f_gprs_mapping_a);
alter table "dbmaster".junction add constraint (foreign key (sigprot_code) 
    references "dbmaster".signal_prot  constraint "dbmaster".f_junction_a);
    
alter table "dbmaster".junction add constraint (foreign key (location_id) 
    references "dbmaster".location  constraint "dbmaster".f_junction_b);
    
alter table "dbmaster".junction_aprch add constraint (foreign 
    key (road_code) references "dbmaster".road  constraint "dbmaster"
    .f_junction_aprch_a);
alter table "dbmaster".junction_aprch add constraint (foreign 
    key (junction_code) references "dbmaster".junction  constraint 
    "dbmaster".f_junction_aprch_b);
alter table "dbmaster".junction_aprch add constraint (foreign 
    key (location_id) references "dbmaster".location  constraint 
    "dbmaster".f_junction_aprch_c);
alter table "dbmaster".junction_reg add constraint (foreign key 
    (regis_code) references "dbmaster".registration  constraint 
    "dbmaster".f_junction_reg_a);
alter table "dbmaster".junction_reg add constraint (foreign key 
    (trigger_type_code) references "dbmaster".trigger_type  constraint 
    "dbmaster".f_junction_reg_b);
alter table "dbmaster".junction_reg add constraint (foreign key 
    (junction_code) references "dbmaster".junction  constraint 
    "dbmaster".f_junction_reg_c);
alter table "dbmaster".junction_xtrav add constraint (foreign 
    key (junction_code) references "dbmaster".junction  constraint 
    "dbmaster".f_junction_xtrav_a);
alter table "dbmaster".location add constraint (foreign key (district_id) 
    references "dbmaster".district  constraint "dbmaster".f_location_a);
    
alter table "dbmaster".location add constraint (foreign key (point_type) 
    references "dbmaster".location_type  constraint "dbmaster".f_location_b);
    
alter table "dbmaster".location add constraint (foreign key (route_area_id) 
    references "dbmaster".route_area  constraint "dbmaster".f_location_c);
    
alter table "dbmaster".location add constraint (foreign key (place_id) 
    references "dbmaster".place  constraint "dbmaster".f_location_d);
    
alter table "dbmaster".msg_to_veh add constraint (foreign key 
    (build_id) references "dbmaster".unit_build  constraint "dbmaster"
    .f_msg_to_veh_a);
alter table "dbmaster".msg_to_veh add constraint (foreign key 
    (user_id) references "dbmaster".cent_user  constraint "dbmaster"
    .f_msg_to_veh_b);
alter table "dbmaster".opconarea add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_opconarea_a);
alter table "dbmaster".operator_media add constraint (foreign 
    key (operator_id) references "dbmaster".operator  constraint 
    "dbmaster".f_operator_media_a);
alter table "dbmaster".orgunit add constraint (foreign key (operator_id,
    opconarea_code) references "dbmaster".opconarea  constraint 
    "dbmaster".f_orgunit_a);
alter table "dbmaster".parameter add constraint (foreign key 
    (component_id) references "dbmaster".component  constraint 
    "dbmaster".f_parameter_a);
alter table "dbmaster".period_group add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_period_group_a);
alter table "dbmaster".place add constraint (foreign key (town_id) 
    references "dbmaster".town  constraint "dbmaster".f_place_a);
    
alter table "dbmaster".publish_tt add constraint (foreign key 
    (pub_prof_id) references "dbmaster".route_profile  constraint 
    "dbmaster".f_publish_tt_c);
alter table "dbmaster".ptactn add constraint (foreign key (dmnscd) 
    references "dbmaster".ptdmns  constraint "dbmaster".f_ptactn_a);
    
alter table "dbmaster".ptactn add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_ptactn_b);
    
alter table "dbmaster".ptdict add constraint (foreign key (langcd,
    tabnam) references "dbmaster".pttabs  constraint "dbmaster"
    .f_ptdict_a);
alter table "dbmaster".ptdmac add constraint (foreign key (dmnscd) 
    references "dbmaster".ptdmns  constraint "dbmaster".f_ptdmac_a);
    
alter table "dbmaster".ptfgms add constraint (foreign key (mesgcd) 
    references "dbmaster".ptmsgs  constraint "dbmaster".f_ptfgms_a);
    
alter table "dbmaster".ptfgms add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_ptfgms_b);
    
alter table "dbmaster".ptfgop add constraint (foreign key (optcod) 
    references "dbmaster".ptmnop  constraint "dbmaster".f_ptfgop_a);
    
alter table "dbmaster".ptfgop add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_ptfgop_b);
    
alter table "dbmaster".ptfgtx add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_ptfgtx_a);
    
alter table "dbmaster".ptfgtx add constraint (foreign key (serlno) 
    references "dbmaster".ptengl  constraint "dbmaster".f_ptfgtx_b);
    
alter table "dbmaster".ptgprm add constraint (foreign key (grupid) 
    references "dbmaster".ptgrup  constraint "dbmaster".f_ptgprm_a);
    
alter table "dbmaster".ptgrus add constraint (foreign key (grupid) 
    references "dbmaster".ptgrup  constraint "dbmaster".f_ptgrus_a);
    
alter table "dbmaster".ptgrus add constraint (foreign key (userid) 
    references "dbmaster".cent_user  constraint "dbmaster".f_ptgrus_b);
    
alter table "dbmaster".ptmndt add constraint (foreign key (menucd) 
    references "dbmaster".ptmnhd  constraint "dbmaster".f_ptmndt_a);
    
alter table "dbmaster".ptmnlg add constraint (foreign key (userid) 
    references "dbmaster".cent_user  constraint "dbmaster".f_ptmnlg_a);
    
alter table "dbmaster".ptmnms add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_ptmnms_a);
    
alter table "dbmaster".ptmnms add constraint (foreign key (menucd) 
    references "dbmaster".ptmnhd  constraint "dbmaster".f_ptmnms_b);
    
alter table "dbmaster".ptmnpt add constraint (foreign key (ptrtyp) 
    references "dbmaster".ptmnpc  constraint "dbmaster".f_ptmnpt_a);
    
alter table "dbmaster".ptmsgs add constraint (foreign key (dmnscd) 
    references "dbmaster".ptdmns  constraint "dbmaster".f_ptmsgs_a);
    
alter table "dbmaster".ptuprm add constraint (foreign key (userid) 
    references "dbmaster".cent_user  constraint "dbmaster".f_ptuprm_a);
    
alter table "dbmaster".ptuprm add constraint (foreign key (optcod) 
    references "dbmaster".ptmnop  constraint "dbmaster".f_ptuprm_b);
    
alter table "dbmaster".ptuspr add constraint (foreign key (optcod) 
    references "dbmaster".ptmnop  constraint "dbmaster".f_ptuspr_a);
    
alter table "dbmaster".ptuspr add constraint (foreign key (userid) 
    references "dbmaster".cent_user  constraint "dbmaster".f_ptuspr_b);
    
alter table "dbmaster".ptuspr add constraint (foreign key (prntcd) 
    references "dbmaster".ptmnpt  constraint "dbmaster".f_ptuspr_c);
    
alter table "dbmaster".publication add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_publication_a);
alter table "dbmaster".publish_tt add constraint (foreign key 
    (rtpi_prof_id) references "dbmaster".route_profile  constraint 
    "dbmaster".f_publish_tt_d);
alter table "dbmaster".feed_improut add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_improut_a);
alter table "dbmaster".feed_impspat add constraint (foreign key 
    (txc_pub_id,operator_id,route_code) references "dbmaster".feed_improut 
     constraint "dbmaster".f_feed_impspat_a);
alter table "dbmaster".feed_imptrip add constraint (foreign key 
    (txc_pub_id,operator_id,route_code) references "dbmaster".feed_improut 
     constraint "dbmaster".f_feed_imptrip_a);
alter table "dbmaster".revision_hist add constraint (foreign 
    key (rev_type_code) references "dbmaster".revision_type  constraint 
    "dbmaster".f_revision_hist_a);
alter table "dbmaster".route add constraint (foreign key (operator_id) 
    references "dbmaster".operator  constraint "dbmaster".f_route_a);
    
alter table "dbmaster".route add constraint (foreign key (route_id) 
    references "dbmaster".route  constraint "dbmaster".f_route_b);
    
alter table "dbmaster".route_loc_avg add constraint (foreign 
    key (service_id) references "dbmaster".service  constraint 
    "dbmaster".f_route_loc_avg_a);
alter table "dbmaster".route_loc_avg add constraint (foreign 
    key (profile_id) references "dbmaster".route_profile  constraint 
    "dbmaster".f_route_loc_avg_b);
alter table "dbmaster".route_loc_avg add constraint (foreign 
    key (location_id) references "dbmaster".location  constraint 
    "dbmaster".f_route_loc_avg_c);
alter table "dbmaster".route_param add constraint (foreign key 
    (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_route_param_c);
alter table "dbmaster".serv_pat_media add constraint (foreign 
    key (service_id,rpat_orderby) references "dbmaster".service_patt 
     constraint "dbmaster".f_serv_pat_media_a);
alter table "dbmaster".service add constraint (foreign key (route_id) 
    references "dbmaster".route  constraint "dbmaster".f_service_a);
    
alter table "dbmaster".service_patt add constraint (foreign key 
    (location_id) references "dbmaster".location  constraint "dbmaster"
    .f_service_patt_a);
alter table "dbmaster".service_patt add constraint (foreign key 
    (service_id) references "dbmaster".service  constraint "dbmaster"
    .f_service_patt_b);
alter table "dbmaster".service_patt add constraint (foreign key 
    (dest_id) references "dbmaster".destination  constraint "dbmaster"
    .f_service_patt_c);
alter table "dbmaster".servlink_xtrav add constraint (foreign 
    key (service_link_id) references "dbmaster".service_link  
    constraint "dbmaster".f_servlink_xtrav_a);
alter table "dbmaster".special_op add constraint (foreign key 
    (operator_id,op_event) references "dbmaster".event  constraint 
    "dbmaster".f_special_op_b);
alter table "dbmaster".special_op add constraint (foreign key 
    (operator_id,map_event) references "dbmaster".event  constraint 
    "dbmaster".f_special_op_c);
alter table "dbmaster".special_op add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_special_op_a);
alter table "dbmaster".tlp_adjust add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_tlp_adjust_a);
alter table "dbmaster".tlp_sched_adh add constraint (foreign 
    key (sigprot_code) references "dbmaster".signal_prot  constraint 
    "dbmaster".f_tlp_sched_adh_a);
alter table "dbmaster".unit_build add constraint (foreign key 
    (unit_type) references "dbmaster".unit_cfg_type  constraint 
    "dbmaster".f_unit_build_a);
alter table "dbmaster".unit_build add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_unit_build_b);
alter table "dbmaster".unit_build add constraint (foreign key 
    (version_id) references "dbmaster".soft_ver  constraint "dbmaster"
    .f_unit_build_c);
alter table "dbmaster".unit_history add constraint (foreign key 
    (build_id) references "dbmaster".unit_build  constraint "dbmaster"
    .f_unit_history_a);
alter table "dbmaster".unit_param add constraint (foreign key 
    (component_id,param_id) references "dbmaster".parameter  constraint 
    "dbmaster".f_unit_param_a);
alter table "dbmaster".unit_param add constraint (foreign key 
    (build_id) references "dbmaster".unit_build  constraint "dbmaster"
    .f_unit_param_b);
alter table "dbmaster".unit_publish add constraint (foreign key 
    (pub_id) references "dbmaster".publication  constraint "dbmaster"
    .f_unit_publish_a);
alter table "dbmaster".unit_publish add constraint (foreign key 
    (build_id) references "dbmaster".unit_build  constraint "dbmaster"
    .f_unit_publish_b);
alter table "dbmaster".vehicle add constraint (foreign key (operator_id,
    orun_code) references "dbmaster".orgunit  constraint "dbmaster"
    .f_vehicle_c);
alter table "dbmaster".vehicle add constraint (foreign key (vehicle_type_id) 
    references "dbmaster".vehicle_type  constraint "dbmaster".f_vehicle_a);
    
alter table "dbmaster".vehicle add constraint (foreign key (build_id) 
    references "dbmaster".unit_build  constraint "dbmaster".f_vehicle_b);
    
alter table "dbmaster".event add constraint (foreign key (operator_id) 
    references "dbmaster".operator  constraint "dbmaster".f_event_a);
    
alter table "dbmaster".pergrval add constraint (foreign key (operator_id,
    orun_code) references "dbmaster".orgunit  constraint "dbmaster"
    .f_pergrval_a);
alter table "dbmaster".subscriber add constraint (foreign key 
    (user_id) references "dbmaster".cent_user  constraint "dbmaster"
    .f_subscription_u);
alter table "dbmaster".unit_log_hist add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  on delete 
    cascade constraint "dbmaster".f_unit_log_hist_a);
alter table "dbmaster".route_alias add constraint (foreign key 
    (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_route_alias_a);
alter table "dbmaster".pthelp add constraint (foreign key (langcd) 
    references "dbmaster".ptlang  constraint "dbmaster".f_pthelp_a);
    
alter table "dbmaster".pthelp add constraint (foreign key (mesgcd) 
    references "dbmaster".ptmsgs  constraint "dbmaster".f_pthelp_b);
    
alter table "dbmaster".feed_imphead add constraint (foreign key 
    (txc_pub_type) references "dbmaster".feed_format  constraint 
    "dbmaster".f_feed_imphead_a);
alter table "dbmaster".feed_imphead add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_feed_imphead_b);
alter table "dbmaster".feed_impdest add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_impdest_a);
alter table "dbmaster".autort_sched add constraint (foreign key 
    (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_autort_sched_a);
alter table "dbmaster".feed_imprept add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_imprept_a);
alter table "dbmaster".feed_impmedi add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_impmedi_a);
alter table "dbmaster".autort_sched add constraint (foreign key 
    (profile_id) references "dbmaster".route_profile  constraint 
    "dbmaster".f_autort_sched_b);
alter table "dbmaster".subscription add constraint (foreign key 
    (subscriber_id) references "dbmaster".subscriber  constraint 
    "dbmaster".f_subscription_s);
alter table "dbmaster".feed_imprtar add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_imprtar_a);
alter table "dbmaster".feed_imploca add constraint (foreign key 
    (txc_pub_id,operator_id) references "dbmaster".feed_imphead 
     constraint "dbmaster".f_feed_imploca_a);
alter table "dbmaster".subscr_loc add constraint (foreign key 
    (subscription_id) references "dbmaster".subscription  constraint 
    "dbmaster".f_subscr_loc_s);
alter table "dbmaster".tmi_bloc add constraint (foreign key (data_owner_code,
    org_unit_code) references "dbmaster".tmi_orun  constraint 
    "dbmaster".f_tmi_bloc_b);
alter table "dbmaster".tmi_bloc add constraint (foreign key (day_type_code) 
    references "dbmaster".tmi_daty  constraint "dbmaster".f_tmi_bloc_c);
    
alter table "dbmaster".tmi_bloc add constraint (foreign key (data_owner_code,
    org_unit_code,timetable_ver_code) references "dbmaster".tmi_tive 
     constraint "dbmaster".f_tmi_bloc_d);
alter table "dbmaster".tmi_cresc add constraint (foreign key 
    (data_owner_code) references "dbmaster".tmi_daow  constraint 
    "dbmaster".f_tmi_cresc_a);
alter table "dbmaster".tmi_cresc add constraint (foreign key 
    (data_owner_code,org_unit_code) references "dbmaster".tmi_orun 
     constraint "dbmaster".f_tmi_cresc_b);
alter table "dbmaster".tmi_cresc add constraint (foreign key 
    (day_type_code) references "dbmaster".tmi_daty  constraint 
    "dbmaster".f_tmi_cresc_c);
alter table "dbmaster".tmi_cresc add constraint (foreign key 
    (data_owner_code,org_unit_code,timetable_ver_code) references 
    "dbmaster".tmi_tive  constraint "dbmaster".f_tmi_cresc_d);
alter table "dbmaster".tmi_driv add constraint (foreign key (data_owner_code,
    orun_code) references "dbmaster".tmi_orun  constraint "dbmaster"
    .f_tmi_driv_b);
alter table "dbmaster".tmi_exopday add constraint (foreign key 
    (data_owner_code,org_unit_code) references "dbmaster".tmi_orun 
     constraint "dbmaster".f_tmi_exopday_b);
alter table "dbmaster".tmi_pergrval add constraint (foreign key 
    (data_owner_code) references "dbmaster".tmi_daow  constraint 
    "dbmaster".f_tmi_pergrval_a);
alter table "dbmaster".tmi_pergrval add constraint (foreign key 
    (data_owner_code,pegr_code) references "dbmaster".tmi_pegr 
     constraint "dbmaster".f_tmi_pergrval_b);
alter table "dbmaster".tmi_pergrval add constraint (foreign key 
    (data_owner_code,org_unit_code) references "dbmaster".tmi_orun 
     constraint "dbmaster".f_tmi_pergrval_c);
alter table "dbmaster".tmi_pergrval add constraint (foreign key 
    (day_type_code) references "dbmaster".tmi_daty  constraint 
    "dbmaster".f_tmi_pergrval_d);
alter table "dbmaster".tmi_poininro add constraint (foreign key 
    (data_owner_code) references "dbmaster".tmi_daow  constraint 
    "dbmaster".f_tmi_poininro_a);
alter table "dbmaster".tmi_rout add constraint (foreign key (data_owner_code) 
    references "dbmaster".tmi_daow  constraint "dbmaster".f_tmi_rout_a);
    
alter table "dbmaster".tmi_rout add constraint (foreign key (data_owner_code,
    line_num_planning) references "dbmaster".tmi_line  constraint 
    "dbmaster".f_tmi_rout_b);
alter table "dbmaster".tmi_rout add constraint (foreign key (data_owner_code,
    dest_code_end) references "dbmaster".tmi_dest  constraint 
    "dbmaster".f_tmi_rout_c);
alter table "dbmaster".tmi_timedty add constraint (foreign key 
    (data_owner_code) references "dbmaster".tmi_daow  constraint 
    "dbmaster".f_tmi_timedty_a);
alter table "dbmaster".tmi_timedty add constraint (foreign key 
    (day_type_code) references "dbmaster".tmi_daty  constraint 
    "dbmaster".f_tmi_timedty_b);
alter table "dbmaster".tmi_tive add constraint (foreign key (data_owner_code,
    org_unit_code) references "dbmaster".tmi_orun  constraint 
    "dbmaster".f_tmi_tive_b);
alter table "dbmaster".tmi_veh add constraint (foreign key (vehicle_type) 
    references "dbmaster".tmi_vety  constraint "dbmaster".f_tmi_veh_b);
    
alter table "dbmaster".tmi_veh add constraint (foreign key (data_owner_code,
    orun_code) references "dbmaster".tmi_orun  constraint "dbmaster"
    .f_tmi_veh_c);
alter table "dbmaster".tmi_vejo add constraint (foreign key (data_owner_code,
    org_unit_code) references "dbmaster".tmi_orun  constraint 
    "dbmaster".f_tmi_vejo_b);
alter table "dbmaster".tmi_vesc add constraint (foreign key (data_owner_code,
    orun_code) references "dbmaster".tmi_orun  constraint "dbmaster"
    .f_tmi_vesc_b);
alter table "dbmaster".unit_status_rt add constraint (foreign 
    key (employee_id) references "dbmaster".employee );
alter table "dbmaster".route_pattern add constraint (foreign 
    key (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_route_pattern_a);
alter table "dbmaster".route_pattern add constraint (foreign 
    key (location_id) references "dbmaster".location  constraint 
    "dbmaster".f_route_pattern_b);
alter table "dbmaster".route_patt_loc add constraint (foreign 
    key (loc_from) references "dbmaster".location  constraint 
    "dbmaster".f_route_patt_loc_a);
alter table "dbmaster".route_patt_loc add constraint (foreign 
    key (loc_to) references "dbmaster".location  constraint "dbmaster"
    .f_route_patt_loc_b);
alter table "dbmaster".route_patt_loc add constraint (foreign 
    key (rpat_id) references "dbmaster".route_pattern  constraint 
    "dbmaster".f_route_patt_loc_c);
alter table "dbmaster".user_route add constraint (foreign key 
    (userid) references "dbmaster".cent_user  constraint "dbmaster"
    .f_user_route_a);
alter table "dbmaster".user_vehicle add constraint (foreign key 
    (userid) references "dbmaster".cent_user  constraint "dbmaster"
    .f_user_vehicle_a);
alter table "dbmaster".unit_bld_media add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  constraint 
    "dbmaster".f_unit_bld_media_a);
alter table "dbmaster".unit_bld_media add constraint (foreign 
    key (media_id) references "dbmaster".media  constraint "dbmaster"
    .f_unit_bld_media_b);
alter table "dbmaster".soft_ver_media add constraint (foreign 
    key (version_id) references "dbmaster".soft_ver  constraint 
    "dbmaster".f_soft_ver_media_a);
alter table "dbmaster".soft_ver_media add constraint (foreign 
    key (media_id) references "dbmaster".media  constraint "dbmaster"
    .f_soft_ver_media_b);
alter table "dbmaster".time_band add constraint (foreign key 
    (operator_id) references "dbmaster".operator  constraint "dbmaster"
    .f_time_band_a);
alter table "dbmaster".loc_interval add constraint (foreign key 
    (loc_from) references "dbmaster".location  constraint "dbmaster"
    .f_loc_int_a);
alter table "dbmaster".loc_interval add constraint (foreign key 
    (loc_to) references "dbmaster".location  constraint "dbmaster"
    .f_loc_int_b);
alter table "dbmaster".loc_interval add constraint (foreign key 
    (band_id) references "dbmaster".time_band  constraint "dbmaster"
    .f_loc_int_c);
alter table "dbmaster".geogate add constraint (foreign key (location_id) 
    references "dbmaster".location  constraint "dbmaster".f_geogate_a);
    
alter table "dbmaster".geogate add constraint (foreign key (route_id) 
    references "dbmaster".route  constraint "dbmaster".f_geogate_b);
    
alter table "dbmaster".route_location add constraint (foreign 
    key (service_id,rpat_orderby) references "dbmaster".service_patt 
     constraint "dbmaster".f_route_location_a);
alter table "dbmaster".route_location add constraint (foreign 
    key (profile_id) references "dbmaster".route_profile  constraint 
    "dbmaster".f_route_location_b);
alter table "dbmaster".tt_mod_trip add constraint (foreign key 
    (mod_id) references "dbmaster".tt_mod  constraint "dbmaster"
    .f_tt_mod_trip_a);
alter table "dbmaster".subscr_loc add constraint (foreign key 
    (location_id) references "dbmaster".location  constraint "dbmaster"
    .f_subscr_loc_l);
alter table "dbmaster".vehicle_route add constraint (foreign 
    key (vehicle_id) references "dbmaster".vehicle  constraint 
    "dbmaster".f_vehicle_route_a);
alter table "dbmaster".vehicle_route add constraint (foreign 
    key (route_id) references "dbmaster".route  constraint "dbmaster"
    .f_vehicle_route_b);
alter table "dbmaster".user_loc add constraint (foreign key (user_id) 
    references "dbmaster".cent_user  constraint "dbmaster".f_user_loc_u);
    
alter table "dbmaster".omnistop_data add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  constraint 
    "dbmaster".f_omnidata_a);
alter table "dbmaster".dest_loc add constraint (foreign key (dest_id) 
    references "dbmaster".destination  constraint "dbmaster".f_dest_loc_a);
    
alter table "dbmaster".dest_loc add constraint (foreign key (location_id) 
    references "dbmaster".location  constraint "dbmaster".f_dest_loc_b);
    
alter table "dbmaster".serv_pat_ann add constraint (foreign key 
    (service_id,rpat_orderby) references "dbmaster".service_patt 
     constraint "dbmaster".f_serv_pat_ann_a);
alter table "dbmaster".unit_comments add constraint (foreign 
    key (location_id) references "dbmaster".location );
alter table "dbmaster".unit_comments add constraint (foreign 
    key (vehicle_id) references "dbmaster".vehicle );
alter table "dbmaster".unit_comments add constraint (foreign 
    key (user_id) references "dbmaster".cent_user );
alter table "dbmaster".dcd_param_time add constraint (foreign 
    key (operator_id) references "dbmaster".operator  constraint 
    "dbmaster".f_dptime_a);
alter table "dbmaster".desp_audit_trail add constraint (foreign 
    key (userid) references "dbmaster".cent_user );
alter table "dbmaster".station_app add constraint (foreign key 
    (station_id) references "dbmaster".location  constraint "dbmaster"
    .f_statapp_a);
alter table "dbmaster".station_app add constraint (foreign key 
    (approach_id) references "dbmaster".location  constraint "dbmaster"
    .f_statapp_b);
alter table "dbmaster".station_loc add constraint (foreign key 
    (station_id) references "dbmaster".location  constraint "dbmaster"
    .f_statloc_a);
alter table "dbmaster".station_loc add constraint (foreign key 
    (stand_id) references "dbmaster".location  constraint "dbmaster"
    .f_statloc_b);
alter table "dbmaster".dcd_omnistop add constraint (foreign key 
    (dest_id) references "dbmaster".destination  constraint "dbmaster"
    .f_dcd_ms_b);
alter table "dbmaster".user_loc add constraint (foreign key (location_id) 
    references "dbmaster".location  constraint "dbmaster".f_user_loc_l);
    
alter table "dbmaster".aux_countdown add constraint (foreign 
    key (subscription_id) references "dbmaster".subscription  
    constraint "dbmaster".f_aux_countdown_s);
alter table "dbmaster".aux_countdown add constraint (foreign 
    key (location_id) references "dbmaster".location  constraint 
    "dbmaster".f_aux_countdown_l);
alter table "dbmaster".feed_impttb add constraint (foreign key 
    (txc_pub_id,operator_id,route_code) references "dbmaster".feed_improut 
     constraint "dbmaster".f_feed_impttb_a);
alter table "dbmaster".desp_auth add constraint (foreign key 
    (user_id) references "dbmaster".cent_user  constraint "dbmaster"
    .f_desp_auth_a);
alter table "dbmaster".archive_rt_vp add constraint (foreign 
    key (schedule_id) references "dbmaster".active_rt  constraint 
    "dbmaster".f_archive_rt_vp_a);
alter table "dbmaster".unit_status_net add constraint (foreign 
    key (network_id) references "dbmaster".network  constraint 
    "dbmaster".f_unit_net_stat_a);
alter table "dbmaster".unit_status_net add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  constraint 
    "dbmaster".f_unit_net_stat_b);
alter table "dbmaster".unit_build_log_msg add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  constraint 
    "dbmaster".f_unit_bld_lm_a);
alter table "dbmaster".unit_index add constraint (foreign key 
    (pub_id,build_id) references "dbmaster".unit_publish  constraint 
    "dbmaster".f_unit_index_a);
alter table "dbmaster".user_build add constraint (foreign key 
    (userid) references "dbmaster".cent_user  constraint "dbmaster"
    .f_user_build_a);
alter table "dbmaster".playlist_block add constraint (foreign 
    key (program_id) references "dbmaster".playlist_program  constraint 
    "dbmaster".f_pl_program_a);
alter table "dbmaster".import_log_report add constraint (foreign 
    key (import_id) references "dbmaster".import_log  constraint 
    "dbmaster".f_implogrep_u);
alter table "dbmaster".playlist_slot add constraint (foreign 
    key (block_id) references "dbmaster".playlist_block  constraint 
    "dbmaster".f_pl_slot_a);
alter table "dbmaster".playlist add constraint (foreign key (block_id) 
    references "dbmaster".playlist_block  constraint "dbmaster"
    .f_playlist);
alter table "dbmaster".playlist_media add constraint (foreign 
    key (playlist_id) references "dbmaster".playlist  constraint 
    "dbmaster".f_pl_media_a);
alter table "dbmaster".playlist_condition add constraint (foreign 
    key (playlist_id) references "dbmaster".playlist  constraint 
    "dbmaster".f_pl_condition_a);
alter table "dbmaster".playlist_attrib add constraint (foreign 
    key (playlist_id) references "dbmaster".playlist  constraint 
    "dbmaster".f_pl_attrib_a);
alter table "dbmaster".playlist_attrib add constraint (foreign 
    key (playlist_id,sequence) references "dbmaster".playlist_media 
     constraint "dbmaster".f_pl_attrib_b);
alter table "dbmaster".playlist_attrib add constraint (foreign 
    key (attrib_type) references "dbmaster".playlist_attrib_type 
     constraint "dbmaster".f_pl_attrib_c);
alter table "dbmaster".unit_status_sign add constraint (foreign 
    key (build_id) references "dbmaster".unit_build  constraint 
    "dbmaster".f_unit_ss_a);
alter table "dbmaster".itemchildren add constraint (foreign key 
    (parent) references "dbmaster".items  on delete cascade constraint 
    "dbmaster".authpar1);
alter table "dbmaster".itemchildren add constraint (foreign key 
    (child) references "dbmaster".items  on delete cascade constraint 
    "dbmaster".authchld1);
alter table "dbmaster".iconnex_menu_user add constraint (foreign 
    key (user_id) references "dbmaster".cent_user );
alter table "dbmaster".iconnex_workspace add constraint (foreign 
    key (user_id) references "dbmaster".cent_user );
alter table "dbmaster".iconnex_wsp_item add constraint (foreign 
    key (wsp_id) references "dbmaster".iconnex_workspace );


grant select on "dbmaster".v_loc_comp to "centrole" as "dbmaster";
grant select on "dbmaster".route_for_user to "public" as "dbmaster";
grant select on "dbmaster".route_visibility to "centrole" as "dbmaster";
grant select on "dbmaster".vehicle_visibility to "centrole" as "dbmaster";
grant select on "dbmaster".build_visibility to "centrole" as "dbmaster";
