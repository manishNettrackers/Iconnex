CREATE TEMPORARY TABLE t_days ( day date, dtime datetime, dayno int );

INSERT INTO t_days VALUES ( '2012-04-15', '2012-04-15', 0 );

CREATE TEMPORARY TABLE t_timetable ( day date, dtime datetime, route_code char(8), service_code char(14), route_id integer, operator_code char(8), service_id int, pub_ttb_id int, runningno char(5), event_code char(8), event_id int, over_midnight char(1) );

select weekday(day), weekday(dtime)
from t_days;

select  * from t_days;
#INSERT INTO t_timetable 
SELECT t_days.day, t_days.dtime, route_code, service_code, t_route.route_id, operator.operator_code operator_code, service.service_id, publish_tt.pub_ttb_id pub_ttb_id, publish_tt.runningno runningno, event.event_code event_code, event.event_id, substring(notes, 0, 1) over_midnight 
FROM operator,route_visibility t_route,service, t_days, publish_tt,event_pattern,event 
WHERE 1 = 1 
AND usernm = 'dbmaster' 
AND operator.operator_id = t_route.operator_id 
AND t_route.route_id = service.route_id 
AND publish_tt.service_id = service.service_id 
and publish_tt.evprf_id = event_pattern.evprf_id 
and event_pattern.event_id = event.event_id 
#and t_days.day between service.wef_date and service.wet_date 
#and weekday(t_days.day) between rpdy_start and rpdy_end 
AND start_time between '00:00:00' and '23:59:59' 
;

