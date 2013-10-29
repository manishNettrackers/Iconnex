pwd=`pwd`
mysql -u root infohost < schema.sql

mysql -u root infohost <<!
create view route_visibility (usernm,userid,route_id,route_code,operator_id,description,outbound_desc,inbound_desc) as 
  select x1.usernm ,x1.userid ,x2.route_id ,x2.route_code ,x2.operator_id 
    ,x2.description ,x2.outbound_desc ,x2.inbound_desc from user_route x0 ,cent_user x1 ,route x2 
    where ((x0.userid = x1.userid ) AND (x2.operator_id = x0.operator_id 
    ) )  union select x4.usernm ,x4.userid ,x5.route_id ,x5.route_code 
    ,x5.operator_id ,x5.description ,x5.outbound_desc ,x5.inbound_desc 
    from user_route x3 ,cent_user x4 ,route x5 where ((x3.userid = x4.userid ) AND (x5.route_id 
    = x3.route_id ) )  union select x7.usernm ,x7.userid ,x6.route_id 
    ,x6.route_code ,x6.operator_id ,x6.description ,x6.outbound_desc 
    ,x6.inbound_desc from route x6 ,cent_user 
    x7 where (((x6.operator_id = x7.operator_id ) OR (x7.operator_id 
    IS NULL ) ) AND (x7.userid != ALL (select x8.userid from 
    user_route x8 ) ) ) ;                  


create view vehicle_visibility (usernm,userid,vehicle_id,vehicle_code,vehicle_type_id,operator_id,vehicle_reg,orun_code,vetag_indicator,modem_addr,build_id,wheelchair_access) as 
  select x1.usernm ,x1.userid ,x2.vehicle_id ,x2.vehicle_code 
    ,x2.vehicle_type_id ,x2.operator_id ,x2.vehicle_reg ,x2.orun_code 
    ,x2.vetag_indicator ,x2.modem_addr ,x2.build_id ,x2.wheelchair_access 
    from user_vehicle x0 ,cent_user x1 ,vehicle x2 where ((x0.userid = x1.userid ) AND (x2.operator_id 
    = x0.operator_id ) )  union select x4.usernm ,x4.userid ,
    x5.vehicle_id ,x5.vehicle_code ,x5.vehicle_type_id ,x5.operator_id 
    ,x5.vehicle_reg ,x5.orun_code ,x5.vetag_indicator ,x5.modem_addr 
    ,x5.build_id ,x5.wheelchair_access from user_vehicle 
    x3 ,cent_user x4 ,vehicle x5 where ((x3.userid 
    = x4.userid ) AND (x5.vehicle_id = x3.vehicle_id ) )  union 
    select x7.usernm ,x7.userid ,x6.vehicle_id ,x6.vehicle_code 
    ,x6.vehicle_type_id ,x6.operator_id ,x6.vehicle_reg ,x6.orun_code 
    ,x6.vetag_indicator ,x6.modem_addr ,x6.build_id ,x6.wheelchair_access 
    from vehicle x6 ,cent_user x7 where (((x6.operator_id 
    = x7.operator_id ) OR (x7.operator_id IS NULL ) ) AND (x7.userid 
    != ALL (select x8.userid from user_vehicle x8 ) 
    ) ) ; 
!

for i in `ls pop/*.unl`
do
    tab=`basename $i | cut -d"." -f1`

    mysql -u root infohost <<!
LOAD DATA LOCAL INFILE '$pwd/$i' INTO TABLE $tab
  FIELDS TERMINATED BY '|'
  LINES TERMINATED BY '\n';
!

done
