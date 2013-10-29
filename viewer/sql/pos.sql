SELECT dmy dmy, event_id
FROM vehicle_visibility vehicle_dimension, gis_dimension, date_dimension, time_dimension, gps_fact_real_time gps_fact 
left join location_dimension location ON (gps_fact.location_id = location.location_id )
left join route_dimension route ON (gps_fact.route_id = route.route_id  )
#left join trip_dimension on (trip_dimension.trip_id = gps_fact.trip_id) 
#left join driver_dimension on ( driver_dimension.driver_id = gps_fact.driver_id )
WHERE 1 = 1 
#AND route.usernm = 'nandd' 
AND vehicle_dimension.usernm = 'rgb' 
AND gps_fact.vehicle_id = vehicle_dimension.vehicle_id 
AND gps_fact.date_id = date_dimension.date_id 
AND gps_fact.time_id = time_dimension.time_id 
AND gps_fact.gis_id = gis_dimension.gis_id 
AND latitude < 180 
AND event_id = 235
AND ymd BETWEEN '2012-07-16' 
AND '2012-07-16' AND hhmmss >= '14:00:00' AND hhmmss <= '23:59:59' ;
select * from gps_fact_real_time where event_id = 235 limit 3; 

