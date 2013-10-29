<?php
$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");
$vh = $_criteria["vehicle"]->get_criteria_value("VALUE");

$dfdy = substr($dfrom, 1,2);
$dfmn = substr($dfrom, 4,2);
$dfyr = substr($dfrom, 7,4);
$dtdy = substr($dto, 1,2);
$dtmn = substr($dto, 4,2);
$dtyr = substr($dto, 7,4);

$sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
$ds->Execute($sql) or print $ds->ErrorMsg();

$sql = " CREATE TEMPORARY TABLE report_journeys ( journey_id INTEGER )";
$ds->Execute($sql) or print $ds->ErrorMsg();

$sql = "INSERT INTO report_journeys
SELECT DISTINCT timetable_journey_fact.fact_id
FROM timetable_journey_fact 
JOIN people_count_fact ON timetable_journey_fact.fact_id = people_count_fact.journey_fact_id
JOIN vehicle_dimension ON vehicle_dimension.vehicle_id = timetable_journey_fact.vehicle_id
WHERE date_format(timestamp, '%d/%m/%Y' ) BETWEEN $dfrom AND $dto
";

if ( $vh  ) 
    $sql .= " AND vehicle_code = $vh";

$ds->Execute($sql) or print $ds->ErrorMsg();

?>

