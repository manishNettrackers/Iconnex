#SELECT vehicle_code vehicle_code, dmy dmy, ymd ymd, hour_no hour_no, addr_suburb addr_suburb, addr_road addr_road, geohash geohash, latitude latitude, longitude longitude, hhmmss hhmmss, speed_mph speed_mph, route_code route_code, trip_no trip_no, running_no running_no, employee_code employee_code, fullname driver_name, duty_no duty_no, event_description event_type, bearing bearing, event_dimension.event_id event_id 
SELECT distinct usernm
FROM vehicle_visibility vehicle_dimension, gis_dimension, date_dimension, time_dimension, gps_fact_real_time gps_fact left join trip_dimension on (trip_dimension.trip_id = gps_fact.trip_id) left join driver_dimension on ( driver_dimension.driver_id = gps_fact.driver_id ), event_dimension 
WHERE 1 = 1 

#AND usernm = 'first' 
AND gps_fact.event_id = event_dimension.event_id 
AND gps_fact.vehicle_id = vehicle_dimension.vehicle_id 
AND gps_fact.gis_id = gis_dimension.gis_id 
AND gps_fact.date_id = date_dimension.date_id 
AND gps_fact.time_id = time_dimension.time_id 
AND latitude < 180 
AND ymd BETWEEN '2012-06-01' AND '2012-07-08' 
AND hhmmss >= '00:00:00' AND hhmmss <= '23:59:59' 
