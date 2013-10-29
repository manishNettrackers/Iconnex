<?php

include "iconnex.php";
include "geohash.class.php";
include "nominatim.php";
include "odsconnector.php";


class dateconnector extends odsconnector
{

	function applyDIM (  $dt )
	{
		$ret = false;
		if ( !$ret )
		{
			$downo = $dt->format("N");
			$downame = $dt->format("l");
			$dmy = $dt->format("d-m-Y");
			$ymd = $dt->format("Y-m-d");
			$mon = $dt->format("M");
			$monthno = $dt->format("m");
			$month = $dt->format("F");
			$year = $dt->format("Y");
			$day_no = $dt->format("d");
			$date_id = $dt->format("Ymd");
			//echo "$downo $dmy $ymd $mon $month $downame $year<br>";
			$sql = "INSERT INTO date_dimension
				(  date_id, dmy, ymd, year, month_no, month_name, month_short, dow_no, dow_name, day_no )
				VALUES
				( $date_id,
				'".$dmy."',
				'".$ymd."',
				'".$year."',
				".$monthno.",
				'". $month."',
				'". $mon."',
				". $downo.",
				'". $downame."',
				". $day_no."
				)";
			$ret = $this->executeSQL ( $sql );
		}
		return $ret;
	}
	

	function date_import()
	{
		$start = new DateTime();
		$start->sub(DateInterval::createFromDateString('100 days'));
		for ( $ct = 0; $ct < 1000; $ct++ )
		{
			$ret = $this->applyDIM($start);
			$start->add(DateInterval::createFromDateString('1 day'));
		}
	}
}

$cn = new dateconnector($_pdo);
$cn->debug = false;
$cn->date_import();

?>
