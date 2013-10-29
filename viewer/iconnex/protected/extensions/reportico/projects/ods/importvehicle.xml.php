<?php

	include "iconnex.php";


	function show_debug  ( $txt )
	{
		global $_debug;
		if ( $_debug )
		{
			echo $txt . "<BR>";
		}
	}

	function executeSQL ( $pdo, $sql )
	{
		show_debug ( $sql );
		$stat = $pdo->query($sql);
		if ( !$stat )
		{
			$info = $pdo->errorInfo();
			trigger_error("$sql <BR>Error ".$pdo->errorCode()." occurred in SQL statement.<BR>". 
			$info[2], E_USER_ERROR);
			return $stat;
		}
		return $stat;
	}
	
	function fetch1SQL ( $pdo, $sql )
	{
		show_debug ( $sql );
		$stat = $pdo->query($sql);
		if ( !$stat )
		{
			$info = $pdo->errorInfo();
			trigger_error("$sql <BR>Error ".$pdo->errorCode()." occurred in SQL statement.<BR>".$info[2], E_USER_ERROR);
			return false;
		}
		$row = $stat->fetch();
		return ( $row );
	}
	
	function applyVehicle ( $pdo, $ar )
	{
		$sql = "SELECT vehicle_code FROM vehicle_dimension 
				WHERE system_code = 'iconnex'
				AND vehicle_code = '".$ar["VEHICLE_CODE"]."'
				AND operator_code = '".$ar["OPERATOR_CODE"]."'";
		$ret = fetch1SQL ( $pdo, $sql );
		if ( $pdo->errorCode() != 0 )
			return false;
		if ( !$ret )
		{

			$sql = "INSERT INTO vehicle_dimension
				(  vehicle_id, system_code, operator_code, vehicle_code, inventory_code, vehicle_reg, wheelchair_flag )
				VALUES
				( 0,
				'iconnex',
				'".trim($ar["OPERATOR_CODE"])."',
				'".trim($ar["VEHICLE_CODE"])."',
				'".trim($ar["BUILD_CODE"])."',
				'".trim($ar["VEHICLE_REG"])."',
				".trim($ar["WHEELCHAIR_ACCESS"])."
				)";
			$ret = executeSQL ( $pdo, $sql );
		}
		return $ret;
	}
	

	function vehicle_import($pdo)
	{
		$iconnex = new iconnex();
		if ( !$iconnex->connect() )
		{
			return false;
		}

		//$sql = "DELETE FROM vehicle_dimension WHERE system_code = 'iconnex'";
		//$stat = executeSQL($pdo, $sql);
		//if ( !$stat )
			//return false;
		
		$sql = "SELECT operator_code, vehicle_code, build_code, vehicle_reg, wheelchair_access
			FROM operator a, vehicle b, unit_build c
			WHERE a.operator_id = b.operator_id
			AND b.build_id  = c.build_id";

		$stat = $iconnex->executeSQL($sql);
		if ( !$stat )
			return false;
		while ( $row = $iconnex->fetch() )
		{
			if ( !$ret = applyVehicle($pdo, $row) )
				break;
		}
	}

	global $_debug ;
	$_debug = false;
	vehicle_import($_pdo);

?>
