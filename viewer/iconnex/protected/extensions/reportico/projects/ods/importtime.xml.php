<?php

include "iconnex.php";
include "geohash.class.php";
include "nominatim.php";
include "odsconnector.php";


class timeconnector extends odsconnector
{

	function applyDIM (  $dt )
	{
		$ret = false;
		if ( !$ret )
		{
			$timeid = $dt->format("His");
			$hr12 = $dt->format("h");
			$hr24 = $dt->format("H");
			$hhmmss = $dt->format("H:i:s");
			$min = $dt->format("i");
			$sec = $dt->format("s");
			//echo "$downo $dmy $ymd $mon $month $downame $year<br>";
			$sql = "INSERT INTO time_dimension
				(  time_id, hhmmss, hour_no, minute_no, second_no )
				VALUES
				( $timeid,
				'". $hhmmss."',
				". $hr24.",
				". $min.",
				". $sec."
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	

	function time_import()
	{
		$ret = $this->executeSQL ( "DELETE FROM time_dimension" );
                $start = DateTime::createFromFormat('H:i:s', '00:00:00');
		for ( $ct = 0; $ct < 60 * 60 * 24; $ct++ )
		{
			$ret = $this->applyDIM($start);
                        $start->add(DateInterval::createFromDateString('1 second'));
			if ( !$ret )
				break;
		}
	}
}

$cn = new timeconnector($_pdo);
$cn->debug = false;
$cn->time_import();

?>
