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
	
	function applyEvent ( $pdo, $ar )
	{
		$sql = "SELECT event_id FROM event_dimension 
				WHERE event_id = '".$ar["MSG_TYPE"]."'";
		$ret = fetch1SQL ( $pdo, $sql );
		if ( $pdo->errorCode() != 0 )
			return false;
		if ( !$ret )
		{

			$sql = "INSERT INTO event_dimension
				(  event_id, event_description )
				VALUES
				( 
				'".trim($ar["MSG_TYPE"])."',
				'".trim($ar["DESCRIPTION"])."'
				)";
			$ret = executeSQL ( $pdo, $sql );
		}
		return $ret;
	}
	

	function event_import($pdo)
	{
		$iconnex = new iconnex();
		if ( !$iconnex->connect() )
		{
			return false;
		}

		//$sql = "DELETE FROM event_dimension WHERE system_code = 'iconnex'";
		//$stat = executeSQL($pdo, $sql);
		//if ( !$stat )
			//return false;
		
		$sql = "SELECT msg_type, description
			FROM message_type
			";

		$stat = $iconnex->executeSQL($sql);
		if ( !$stat )
			return false;
		while ( $row = $iconnex->fetch() )
		{
			if ( !$ret = applyEvent($pdo, $row) )
				break;
		}
	}

	global $_debug ;
	$_debug = false;
	event_import($_pdo);

?>
