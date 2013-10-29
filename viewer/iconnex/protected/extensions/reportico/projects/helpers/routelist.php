<?php

include ("odsconnector.php");

$ods = new odsconnector();
$ods->connect();

if ( !$ods->pdo )
{
    echo "<select></select>";
}

$sql = "SELECT route_id, route_code FROM route";
$stat = $ods->executeSQL($sql);



echo "<select>";
while ($row = $stat->fetch()) {
    echo "<option value='".$row[0]."'>".$row[1]."</option>";
}
echo "</select>";

?>
