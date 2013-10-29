SELECT location.location_code location_code, route.route_code route_code
FROM operator, route route,active_rt,active_rt_loc,publish_tt,employee,vehicle,location
WHERE 1 = 1
AND service.route_id = route.route_id
AND service.service_id = publish_tt.service_id
AND operator.operator_id = route.operator_id
AND active_rt.employee_id = employee.employee_id
AND active_rt.vehicle_id = vehicle.vehicle_id
AND active_rt_loc.schedule_id = active_rt.schedule_id
AND active_rt.pub_ttb_id = publish_tt.pub_ttb_id
AND active_rt_loc.location_id = location.location_id
AND route.route_id IN ('340' )
ORDER BY active_rt.actual_start ASC, active_rt.schedule_id ASC, route.route_code ASC, publish_tt.runningno ASC, active_rt_loc.rpat_orderby ASC
