SELECT geohash geohash, addr_suburb addr_suburb, addr_road addr_road, latitude latitude,
         longitude longitude, event_id event_id,
         duration duration, ymd ymd, threshold threshold,
         hhmmss hhmmss, driver_dimension.employee_code employee_code, trip_dimension.trip_no trip_no,
         trip_dimension.route_code route_code, trip_dimension.running_no running_no,
         trip_dimension.duty_no duty_no, trip_dimension.actual_end actual_end,
	driver_dimension.driver_id,
         fullname
FROM vehicle_dimension, gis_dimension, date_dimension, time_dimension
        JOIN telem_paest_fact
        LEFT JOIN trip_dimension on (trip_dimension.trip_id = telem_paest_fact.trip_id)
        JOIN driver_dimension on (telem_paest_fact.driver_id = driver_dimension.driver_id)
WHERE 1 = 1
AND telem_paest_fact.vehicle_id = vehicle_dimension.vehicle_id
AND telem_paest_fact.gis_id = gis_dimension.gis_id
AND telem_paest_fact.date_id = date_dimension.date_id
AND telem_paest_fact.time_id = time_dimension.time_id
AND hhmmss >= '00:00:00' AND hhmmss <= '23:59:59'
AND ymd BETWEEN '2011-11-01' AND '2011-12-01'
AND employee_code is not null
AND telem_paest_fact.event_id = 8
ORDER BY ymd ASC, hhmmss ASC

