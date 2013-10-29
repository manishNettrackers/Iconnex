CREATE TEMPORARY TABLE t_routeloc AS ( SELECT DISTINCT location_id, route.route_id, route_code, operator.operator_id, operator_code FROM service_patt, service, route, operator WHERE service_patt.service_id = service.service_id AND service.route_id = route.route_id AND route.operator_id = operator.operator_id 
#AND CURDATE() BETWEEN wef_date AND wet_date 
);
SELECT count(*) from t_routeloc;
CREATE INDEX i_t_routeloc ON t_routeloc ( location_id );
CREATE TEMPORARY TABLE t_stops_make AS ( 
select a.build_id, a.build_code, a.build_code parent, param_desc, param_value, a.unit_type 
from unit_build a, unit_param b, component c, parameter d 
where a.build_id = b.build_id 
and b.component_id = c.component_id 
and b.param_id = d.param_id 
and component_code = 'STOPDISPLAYDEVICE' 
and ( param_desc = 'make' ) 
and unit_type = 'BUSSTOP' 
and param_value is not null 
and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) 
and param_value != '' 
and a.build_id in ( select build_id from display_point ) ) ;
insert into t_stops_make select distinct a.build_id, a.build_code, pa.build_code parent, param_desc, param_value, a.unit_type from unit_build a, unit_param b, component c, parameter d, unit_build pa where 1 = 1 and a.build_parent = pa.build_id and pa.build_id = b.build_id and b.component_id = c.component_id and b.param_id = d.param_id and component_code = 'STOPDISPLAYDEVICE' and ( param_desc = 'make' ) and param_value is not null and param_value != '' and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) and a.unit_type = 'BUSSTOP' and a.build_id in ( select build_id from display_point ) ;
insert into t_stops_make select distinct a.build_id, a.build_code, pa.build_code parent, param_desc, param_value, a.unit_type from unit_build a, unit_param b, component c, parameter d, unit_build pa, unit_build ppa where 1 = 1 and a.build_parent = pa.build_id and pa.build_parent = ppa.build_id and ppa.build_id = b.build_id and b.component_id = c.component_id and b.param_id = d.param_id and component_code = 'STOPDISPLAYDEVICE' and ( param_desc = 'make' ) and param_value is not null and param_value != '' and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) and a.unit_type = 'BUSSTOP' and a.build_id in ( select build_id from display_point ) ;
CREATE TEMPORARY TABLE t_stops_maxTextWidth AS ( select a.build_id, a.build_code, a.build_code parent, param_desc, param_value, a.unit_type from unit_build a, unit_param b, component c, parameter d where a.build_id = b.build_id and b.component_id = c.component_id and b.param_id = d.param_id and component_code = 'STOPDISPLAYDEVICE' and ( param_desc = 'maxTextWidth' ) and unit_type = 'BUSSTOP' and param_value is not null and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) and param_value != '' and a.build_id in ( select build_id from display_point ) ) ;
insert into t_stops_maxTextWidth select distinct a.build_id, a.build_code, pa.build_code parent, param_desc, param_value, a.unit_type from unit_build a, unit_param b, component c, parameter d, unit_build pa where 1 = 1 and a.build_parent = pa.build_id and pa.build_id = b.build_id and b.component_id = c.component_id and b.param_id = d.param_id and component_code = 'STOPDISPLAYDEVICE' and ( param_desc = 'maxTextWidth' ) and param_value is not null and param_value != '' and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) and a.unit_type = 'BUSSTOP' and a.build_id in ( select build_id from display_point ) ;
insert into t_stops_maxTextWidth select distinct a.build_id, a.build_code, pa.build_code parent, param_desc, param_value, a.unit_type from unit_build a, unit_param b, component c, parameter d, unit_build pa, unit_build ppa where 1 = 1 and a.build_parent = pa.build_id and pa.build_parent = ppa.build_id and ppa.build_id = b.build_id and b.component_id = c.component_id and b.param_id = d.param_id and component_code = 'STOPDISPLAYDEVICE' and ( param_desc = 'maxTextWidth' ) and param_value is not null and param_value != '' and param_value not in ( '1BDIS', '1Infotec', '1Infotec (LX800)' ) and a.unit_type = 'BUSSTOP' and a.build_id in ( select build_id from display_point ) ;
CREATE TEMPORARY TABLE t_events AS (SELECT unit_build.build_id, '101' message_type, '1111' last_alert, count(*) alert_count FROM unit_build, display_point WHERE 1 = 1 AND display_point.build_id = unit_build.build_id GROUP BY 1,2);
CREATE TEMPORARY TABLE t_locs AS (SELECT l.location_id, location_code location_code, l.bay_no bay_no, l.description description, ra.route_area_code route_area_code, latitude_degrees latitude_degrees, latitude_minutes latitude_minutes, latitude_heading latitude_heading, longitude_degrees longitude_degrees, longitude_minutes longitude_minutes, longitude_heading longitude_heading, 'UNKNOWN' build_code, CURTIME() message_time, '10.0.0.123' ip_address , 'Surtronic' make , CURTIME() last_impact, 10 impact_count, CURTIME() last_bootup, 10 bootup_count , '99' last_active_hour, '99' last_active_day 
FROM location l left join display_point dp on dp.location_id = l.location_id and dp.display_type = 'B'
left join route_area ra on ra.route_area_id = l.route_area_id 
left join unit_build u on dp.build_id = u.build_id 
WHERE 1 = 1 and l.location_id in 
	( select location_id from t_routeloc) 
and l.point_type = 'S' 
);



CREATE TEMPORARY TABLE t_loconrt ( location_id INTEGER, routes CHAR(40) );
SELECT DISTINCT location_id, route_code FROM service_patt, service, route 
WHERE service_patt.service_id = service.service_id
AND service.route_id = route.route_id 
AND location_id IN ( SELECT location_id FROM service_patt ) ORDER BY location_id
;
CREATE INDEX i_t_loconrt ON t_loconrt ( location_id );
SELECT count(*) from t_locs;
SELECT count(*) from t_loconrt;
