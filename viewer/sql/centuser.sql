drop table cent_user;
create table cent_user 
  (
    userid int auto_increment,
    usernm char(15),
    narrtv char(20) not null ,
    operator_id integer,
    passwd char(10),
    passwd_md5 char(40),
    emailad char(30),
    maxsess smallint default 99,
    langcd char(5) default 'en_gb' not null ,
    menucd char(6) default 'MASTER' not null ,
    primary key (userid)
  );

insert into cent_user values ('32','gordon','Gordon Bishop','9',':bV\'PKdddd','c292dd30b8c7655b9a5158f430757275','','10','en_gb','MASTER');
insert into cent_user values ('33','rgbrep','RGB Reports','','_+S4:S/Kdd','f24d8e553eb54a17affa092178919306','','','en_gb','MASTER');
insert into cent_user values ('34','thmrep','Thames Reporting Use','11','dddddddddd','6bb8aac97f4c05e04ed9b604c37a22fd','','','en_gb','MASTER');
insert into cent_user values ('35','firrep','First Reports','12','_+8bSV9ddd','200f474aa6de7f50f266754540e85dcc','','','en_gb','MASTER');
insert into cent_user values ('36','rgbimp','RGB Import account','9','S4:_(mrrdd','496e959cc54d067cb78d6440123bdc92','','99','en_gb','MASTER');
insert into cent_user values ('37','transept','Transept Admin login','','b9+((pV6hd','501c962cd5d7f7707ee9a48982be3f5a','','99','en_gb','ADMIN');
insert into cent_user values ('38','tranrep','Transept Report User','','dddddddddd','b6039de6c446c0de2f75d8badf4685ab','','','en_gb','MASTER');
insert into cent_user values ('39','rgbgen','General Reports','','dddddddddd','aceace0eef0e26d65e2b000748d68e23','','','en_gb','MASTER');
insert into cent_user values ('40','newb','Newbury Admin','9','W~c:_+(hdd','39bb75bf5a775a22353f316f73a477ed','','','en_gb','ADMIN');
insert into cent_user values ('41','voda','Vodaphone','15','_+6PJ-dddd','0427025713609632aa10a4c91d940084','','','en_gb','ADMIN');
insert into cent_user values ('42','weaway','WEAVERWAY','16','_+c/-c-vdd','ffdc59f2ea5a54f90a36cacfbda8fad5','','','en_gb','ADMIN');
insert into cent_user values ('43','nwbrep','Newbury Reports','14','dddddddddd','e41bd2bf814fd47013290a17204d3a48','','','en_gb','MASTER');
insert into cent_user values ('44','wearep','Weavaway Reports','16','dddddddddd','490c19b103d357a977a699e90caa6239','','','en_gb','MASTER');
insert into cent_user values ('45','vodrep','Vodaphone Reports','15','dddddddddd','58538575e0cd7116fa78c8f191da0cce','','','en_gb','MASTER');
insert into cent_user values ('46','wb_newb','WBerks for Newb Bus','14','c:OWJ/VKdd','0814f0e1e494ed499ecb133de6ce6af0','','','en_gb','ADMIN');
insert into cent_user values ('47','wb_wway','WBerks for Weavaway','16','c:OcJ/VKdd','741586b4c1a3981d9b42b26c1ed1a4d8','','','en_gb','ADMIN');
insert into cent_user values ('48','sign','sign login','7','Vb4Wdddddd','dd9864e15ac346001a4d8927136be656','','','en_gb','ADMIN');
insert into cent_user values ('49','TRAPEZE_SURREY2','Trapeze SIRI','13',' ','5feb5728dff99e2c7ca334c8b1f48d0a','Peter.Rowley@trapezegroup.co.u','0','en_gb','MASTER');
insert into cent_user values ('50','CNZSIRIP','CNZ SIRI Producer','','12CNZSIRIP','0e66d210020b3ae5d67eb986591bd0ac','','0','en_gb','MASTER');
insert into cent_user values ('51','READING','Reading SIRI','13',' ','0f89f16c6a113d3cf66eb954f24ed5df','tom@glossa.co.uk','0','en_gb','MASTER');
insert into cent_user values ('52','wbsrep','multi operator login','','_+c:/S`Vdd','65e3e6ecfc42cc4d059060c508f76256','','','en_gb','MASTER');
insert into cent_user values ('53','unirep','university','','dddddddddd','b783f550006f3f8a8259df28f755f92d','','','en_gb','ADMIN');
insert into cent_user values ('26','readb','Reading Buses Desp','','S/-J:ddddd','f098602f92a3846f1831917554e2a483','','99','en_gb','NEWM10');
insert into cent_user values ('54','CNZSIRIC','CNZ SIRI Consumer','','12CNZSIRIC','dadaf582bac2647d165d15d2dbf25b7c',' ','0','en_gb','MASTER');
insert into cent_user values ('23','admin','Amin Despatcher','8','-JNbWddddd','f8def8bcecb2e7925a2b42d60d202deb','','','en_gb','MASTER');
insert into cent_user values ('25','infsupp','Infocell Support','','dddddddddd','1f7e0a18edc5e501d474eb3994169ef7','','9','en_gb','NEWM10');
insert into cent_user values ('1','dbmaster','The Database Master','','','3eeb9edc5d4df6712146e729b77dc720','','99','en_gb','MASTER');
insert into cent_user values ('6','appadmin','Application Admin','','','111e0eafead0cfd8892761db1bb0d9a1','','99','en_gb','MASTER');
insert into cent_user values ('21','readg','anjum','7','S/-J4ddddd','860bcfee583d60425ccfd23619838954','','99','en_gb','MASTER');
insert into cent_user values ('29','thames','Thames Travel Desp','11','9S-6/Ldddd','330a0d8e0d7463ed8d25c284cd754a23','','','en_gb','NEWM10');
insert into cent_user values ('55','rbc','Reading BC','',' ','13b21570fe866ababece0789f04c714c','','','en_gb','MASTER');
insert into cent_user values ('56','rgbdesp','Reading BC','',' ','2bfcfcc30a8190f5cbedc87b06c62803','','','en_gb','MASTER');
insert into cent_user values ('59','mallen','Marc Allen','',' ','bd55a50973ef23375af8cee706e98eb1','','','en_gb','MASTER');
insert into cent_user values ('60','swise','swise','',' ','74a77326ff788d60c0925a00845075f1','','','en_gb','MASTER');
insert into cent_user values ('61','jhall','jhall','',' ','07fc507f6a9221b809ca3c03bfe934c9','','','en_gb','MASTER');
