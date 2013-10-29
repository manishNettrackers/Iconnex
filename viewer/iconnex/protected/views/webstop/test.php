<?php

	$conn_id = false;
	$conn_host_name = "127.0.0.1";
	$conn_server = "ods";
	$conn_protocol = "onsoctcp";
	$conn_database = "centurion";
	$conn_username = "dbmaster";
	$conn_password = "read109!!";
	$cnstr =
		"informix:".
		"host=".$conn_host_name."; ".
		"server=".$conn_server."; ".
		"protocol=".$conn_protocol."; ".
		"database=".$conn_database;

	try 
	{
echo $cnstr."\n";
		$conn_id = new PDO($cnstr, $conn_username, $conn_password);
echo $conn_id;
	}
	catch ( Exception $ex )
	{
    	echo "DB Connection Error : ";
		echo $ex->getCode()."\n";
		echo $ex->getMessage()."\n";
		return $conn_id;
	}

	$conn_id->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);




?>
