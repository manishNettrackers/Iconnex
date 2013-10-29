create table login_audit 
  (
    login_time datetime ,
    in_out char(1) not null ,
    login_name char(20),
    success char(1) not null ,
    source_ip char(16),
    source_url char(120)
  );
