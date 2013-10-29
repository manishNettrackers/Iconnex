SET ISOLATION TO DIRTY READ;

CREATE TEMP TABLE t_times ( from_time CHAR(8), to_time CHAR(8) ) WITH NO LOG;

INSERT INTO t_times VALUES ( "00:00:00", "23:59:00" ) ;

CREATE TEMP TABLE t_days ( day date, dtime datetime year to day , dayno integer );

INSERT INTO t_days VALUES ( '09/05/2012', '2012-05-09', 0 );

UPDATE t_days SET dayno = WEEKDAY(dtime);

CREATE TEMP TABLE t_timetable ( day date, dtime datetime year to second, route_code char(8), service_code char(14), route_id integer, operator_code char(8), service_id int, pub_ttb_id int, runningno char(5), event_code char(8), event_id int, over_midnight char(1), trip_no char(10), duty_no char(6), operator_id integer, start_time datetime hour to second ) WITH NO LOG;

SELECT t_days.day, t_days.dtime, route_code, service_code, t_route.route_id, operator.operator_code operator_code, service.service_id, publish_tt.pub_ttb_id pub_ttb_id, publish_tt.runningno runningno, event.event_code event_code, event.event_id, notes [1, 1] over_midnight, trip_no, duty_no, operator.operator_id, start_time 
FROM operator,route_visibility t_route,service, t_days, publish_tt,event_pattern,event 
WHERE 1 = 1 
AND usernm = 'dbmaster' 
AND operator.operator_id = t_route.operator_id 
AND t_route.route_id = service.route_id 
AND publish_tt.service_id = service.service_id 
and publish_tt.evprf_id = event_pattern.evprf_id 
and event_pattern.event_id = event.event_id 
and t_days.day between service.wef_date and service.wet_date 
and t_days.dayno between rpdy_start and rpdy_end 
AND start_time between "14:00:00" and "15:00:00" 
--AND t_route.route_id in ( "17" );
