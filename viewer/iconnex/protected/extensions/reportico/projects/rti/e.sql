SELECT route.* FROM route, cent_user WHERE 1 = 1 AND ( route.operator_id = cent_user.operator_id OR cent_user.operator_id IS NULL ) AND cent_user.usernm = USER INTO TEMP t_route WITH NO LOG;

CREATE TEMP TABLE t_days ( day date, dtime datetime year to day ) with no log;

INSERT INTO t_days VALUES ( '18/03/2011', '2011-03-18' );

DELETE FROM t_days WHERE WEEKDAY(day) NOT IN ( '0','1','2','3','4','5','6' );

SELECT t_days.day, t_days.dtime, t_route.route_id, service.service_id, publish_tt.pub_ttb_id pub_ttb_id, over_midnight over_midnight, service_patt.location_id, service_patt.rpat_orderby FROM operator,t_route,service, t_days, publish_tt, event, event_pattern, service_patt WHERE 1 = 1 AND operator.operator_id = t_route.operator_id AND t_route.route_id = service.route_id AND publish_tt.service_id = service.service_id and t_days.day between service.wef_date and service.wet_date AND publish_tt.evprf_id = event_pattern.evprf_id AND event_pattern.event_id = event.event_id and event.event_tp = 3 and weekday(t_days.day) between rpdy_start and rpdy_end and service.service_id = service_patt.service_id and service_patt.location_id in ( '10447' ) and ( current > extend(start_time, year to second) or date(current) > t_days.day) INTO TEMP t_timetable_froms WITH NO LOG;

SELECT archive_rt.schedule_id, archive_rt_loc.location_id, archive_Rt_loc.rpat_orderby, arrival_time, archive_rt.pub_ttb_id, departure_time, arrival_time_pub, departure_time_pub, employee_id, vehicle_id, actual_start FROM t_timetable_froms, archive_rt,archive_rt_loc WHERE 1 = 1 AND archive_rt_loc.schedule_id = archive_rt.schedule_id AND archive_rt.pub_ttb_id = t_timetable_froms.pub_ttb_id AND departure_status = 'A' AND archive_rt_loc.location_id in ( '10447' ) AND date(actual_start) = t_timetable_froms.day INTO TEMP t_actloc1 WITH NO LOG;

SELECT t_actloc1.schedule_id, archive_rt_loc.location_id, archive_rt_loc.arrival_time, archive_rt_loc.departure_time, archive_rt_loc.arrival_time_pub, archive_rt_loc.departure_time_pub, ( INTERVAL(00) MINUTE(4) TO MINUTE + ( archive_rt_loc.arrival_time - t_actloc1.departure_time ) ) || '' duration FROM t_actloc1, archive_rt_loc WHERE 1 = 1 AND archive_rt_loc.schedule_id = t_actloc1.schedule_id AND arrival_status = 'A' AND archive_rt_loc.rpat_orderby > t_actloc1.rpat_orderby AND archive_rt_loc.location_id in ( '10400' ) INTO TEMP t_actloc2 WITH NO LOG ;
