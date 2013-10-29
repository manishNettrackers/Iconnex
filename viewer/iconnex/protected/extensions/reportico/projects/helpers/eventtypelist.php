<?php

include ("odsconnector.php");

$ods = new odsconnector();
$ods->connect();

if ( !$ods->pdo )
{
    echo "<select></select>";
}

$sql = "SELECT event_id, event_code FROM event WHERE event_tp = '3' and operator_id = 12";
$stat = $ods->executeSQL($sql);

echo "<select>";
    echo "<option value='9999999'></option>";
while ($row = $stat->fetch()) {
    echo "<option value='".$row[0]."'>".$row[1]."</option>";
}
echo "</select>";

?>
