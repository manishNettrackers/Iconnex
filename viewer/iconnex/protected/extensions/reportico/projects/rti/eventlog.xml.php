<?php


include "iconnexextra.class.php";

$iconnex = new iconnex_extra($_pdo);
$user = $iconnex->getUser();


$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$veh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$mtype = $_criteria["messagetype"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$ftm = $_criteria["fromTime"]->get_criteria_value("VALUE");
$ttm = $_criteria["toTime"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dfdy = substr($dto, 1,2);
$dfmn = substr($dto, 4,2);
$dfyr = substr($dto, 7,4);


$ifrom = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );
$ito = mktime ( 0, 0, 0, $dfmn, $dfdy, $dfyr );

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex-> create_temp_times($ftm, $ttm) ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dto) ) return false;

$sql = "SELECT b.event_type, a.*, '                               ' message_text
FROM log_time a
JOIN event_type b ON a.message_type = b.event_type
JOIN vehicle c ON a.reference_id = c.build_id
WHERE 1 = 1
AND EXTEND(receipt_time, HOUR TO SECOND) BETWEEN $ftm AND $ttm
AND date(receipt_time) BETWEEN $dfrom AND $dto
";

if ( $mtype ) $sql .= " AND a.message_type IN (".$mtype.")";
if ( $veh ) $sql .= " AND c.vehicle_id IN (".$veh.")";

$sql .= " INTO TEMP t_events WITH NO LOG " ;

if ( !$iconnex->executeSQL($sql) ) return false;

$sql = "
    UPDATE t_events 
        SET message_text = ( SELECT 'Rt:' || service_code || 
                    ' Rb:' || running_board ||
                    ' Dt:' || duty_number ||
                    ' Drv:' || driver_code  
                    FROM log_journey_details 
                    WHERE log_entry_id = t_events.log_id)
    WHERE message_type IN ( SELECT event_type FROM event_type WHERE event_class = 'journey_details' )";
if ( !$iconnex->executeSQL($sql) ) return false;


$sql = "
    UPDATE t_events 
        SET message_text = ( SELECT latitude || ',' || longitude
                    FROM log_position_update
                    LEFT JOIN gis_dimension ON gis_dimension.gis_id = log_position_update.gis_id
                    WHERE log_entry_id = t_events.log_id)
    WHERE message_type IN ( SELECT event_type FROM event_type WHERE event_class = 'position_update' )";
if ( !$iconnex->executeSQL($sql) ) return false;

$sql = "
    UPDATE t_events 
        SET message_text = 'Log On'
    WHERE message_type IN ( SELECT event_type FROM event_type WHERE description = 'DAIP Log On' )";
if ( !$iconnex->executeSQL($sql) ) return false;

$sql = "
    UPDATE t_events 
        SET message_text = 'Log Off'
    WHERE message_type IN ( SELECT event_type FROM event_type WHERE description = 'DAIP Log Off' )";
if ( !$iconnex->executeSQL($sql) ) return false;
?>
