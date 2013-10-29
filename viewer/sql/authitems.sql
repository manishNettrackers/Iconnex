drop table items;
drop table assignments;
drop table itemchildren;
create table items 
  (
    name varchar(64) not null ,
    type integer not null ,
    description varchar(64),
    bizrule varchar(64),
    data varchar(64),
    primary key (name) 
  );

create table assignments 
  (
    itemname varchar(64) not null ,
    userid varchar(64) not null ,
    bizrule varchar(64),
    data varchar(64),
    primary key (itemname,userid) 
  );

create table itemchildren 
  (
    parent varchar(64) not null ,
    child varchar(64) not null ,
    primary key (parent,child) 
  );


insert into items values ('Authority','2','','','');
insert into items values ('Administrator','2','','','');
insert into items values ('User','2','','','');
insert into items values ('Vehicle Manager','1','','','s:0:"";');
insert into items values ('User Manager','1','','','');
insert into items values ('Vehicle Viewer','1','','','s:0:"";');
insert into items values ('Create Vehicle','0','','','s:0:"";');
insert into items values ('Edit Vehicle','0','','','s:0:"";');
insert into items values ('View Vehicle','0','','','s:0:"";');
insert into items values ('Delete Vehicle','0','','','s:0:"";');
insert into items values ('Create User','0','','','');
insert into items values ('Edit User','0','','','');
insert into items values ('View User','0','','','');
insert into items values ('Reporter','2','','','s:0:"";');
insert into items values ('Bus Operator','2','Bus Operator','','s:0:"";');


insert into itemchildren values ('Administrator','Vehicle Manager');


insert into assignments select 'Administrator', userid, ' ', 's:0:"";' from cent_user where usernm = "admin";
insert into assignments select 'Authority', userid, ' ', 's:0:"";' from cent_user where usernm = "rbc";
insert into assignments select 'Operator', userid, ' ', 's:0:"";' from cent_user where usernm = "rgbdesp";

