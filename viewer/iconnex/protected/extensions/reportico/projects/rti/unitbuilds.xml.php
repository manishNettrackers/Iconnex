<?php
$loc = $_criteria["location"]->get_criteria_value("VALUE");
$cop = $_criteria["operator"]->get_criteria_value("VALUE");
$veh = $_criteria["vehicle"]->get_criteria_value("VALUE");
$ver = $_criteria["version"]->get_criteria_value("VALUE");

// -------------------------------------------------------
// Find vehicles 
// -------------------------------------------------------
$sql =
"
SELECT UNIQUE a.build_id, b.build_code, vehicle_id, vehicle_code, version
FROM vehicle a, unit_build b, soft_ver c
WHERE a.build_id = b.build_id
AND b.version_id = c.version_id
";

if ( $veh )$sql .= " AND a.vehicle_id IN ($veh)";

if ( $ver )$sql .= " AND version.version_id IN ($ver)";

$sql .= " INTO TEMP t_vehbld";

$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();


// -------------------------------------------------------
// Find locations on selected routes
// -------------------------------------------------------
$sql =
"
SELECT UNIQUE b.build_id, build_code, a.location_id, location_code, a.description, version
FROM location a, display_point b, unit_build, soft_ver c
WHERE a.location_id = b.location_id
AND b.build_id = unit_build.build_id
and unit_build.version_id =  c.version_id
";

if ( $loc )$sql .= " AND a.location_id IN ($loc)";

if ( $ver )$sql .= " AND version.version_id IN ($ver)";

$sql .= " INTO TEMP t_locbld";

$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();

$sql = "
SELECT build_id, build_code, vehicle_id, vehicle_code, 0 location_id, \"\" location_code, \"\" location_name, version
FROM t_vehbld a
UNION 
SELECT build_id, build_code, 0, \"\", location_id, location_code, description location_name, version
FROM t_locbld a
INTO TEMP t_builds
";

$_connection->Execute($sql) or
      print $sql." ".$_connection->ErrorMsg();


?>
