<?php

function modulus($val)
{
    if ( $val < 0 ) 
        return -$val;
    else 
        return $val;
}

/*
 * Example
 * render.php?type=bus&elementTypes=route,lateness,fleetno,bearing&elementValues=17,600,804,90
 */

/**
 * Extract the parameters into an attributes array
 
*/
$type = $_GET["type"];
$elementTypes = $_GET["elementTypes"];
$elementValues = $_GET["elementValues"];
$attrTypes = explode(',', $elementTypes);
$attrValues = explode(',', $elementValues);
$attributes = array_combine($attrTypes, $attrValues);

if ($type == "Stop Event")
{
	$border = 3;
	$padding = 2;

	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 32;
	$imgY = 32;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocate($img, 255, 0, 0);
	$amber = imagecolorallocate($img, 127, 255, 0);
	$green = imagecolorallocate($img, 0, 255, 0);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 127);

	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);

	/**
	 * Add a stop image
	 */
	$img_stop = imagecreatefrompng("stop.png");
	$dim_stopX = 23;
	$dim_stopY = $imgY;
	$stop_posX = $imgX / 2 - ($dim_stopX / 2);
	$stop_posY = 0;
	imagecopyresampled($img, $img_stop, $stop_posX, $stop_posY, 0, 0, $dim_stopX, $dim_stopY, 15, 21);
	imagealphablending($img, true);


	$valueKey = array_search("Event Count", $attrTypes);
	if (isset($attributes["Event Count"]) )
	{
		/**
		 * Add the event count text
		 */
		$text = $attributes["Event Count"];
		$font = "fonts/LiberationSans-Regular.ttf";
		$fontSize = 10;
		$textDim = imagettfbbox($fontSize, 0, $font, $text);
		$textX = $textDim[2] - $textDim[0];
		$textY = $textDim[7] - $textDim[1];
		$text_posX = $border + $padding + ($imgX - 2 * ($border + $padding)) / 2 - ($textX / 2);
		$text_posY = $border + $padding - $textY;
		imagealphablending($img, true);
		imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
	}

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}

if ($type == "VehicleEvent")
{
	$imgX = 31;
	$imgY = 35;
	$botpad = 10;
	$fontSize = 8;
	$padding = 2;
	$border = 2;

	$img = imagecreatetruecolor($imgX, $imgY);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);

	/**
	 * Add a fill of a colour based on lateness
	 */
    $route = "";
    $lateness = 0;
    $event = 0;

    $metric = "Lateness Dep";
    
	if ( isset( $attributes["Event Id"] ) )
        $event = $attributes["Event Id"];
	if ( isset( $attributes["Route Code"] ) )
        $route = $attributes["Route Code"];
	if ( isset( $attributes["Metric"] ) )
        $metric = $attributes["Metric"];

    if ( $event == "206" || $event == "212" )
        draw_lateness_arrow($img, $attributes, $imgX, $imgY, $metric );
    else if ( $event == "235" || $event == "236" || $event == "237" || $event == "238"  )
        draw_driver_activity($img, $attributes, $imgX, $imgY, $event );
    else if ( $event == "121" || $event == "108" || $event == "109" || $event == "219" || $event == "207" )
        draw_vehicle($img, $attributes, $imgX, $imgY, $imgX / 4, $imgY / 4 );
    else if ( $event == "140" )
    {
        draw_pax_count($img, $imgX, $imgY, $attributes["Count In"], $attributes["Count Out"], $attributes["Occupancy"] );
    }
    else if ( $event == "219" || $event == "207" )
    {
        $text = "Delayed";
        $font = "fonts/LiberationSans-Regular.ttf";
        $fontSize = 7;
        $textDim = imagettfbbox($fontSize, 0, $font, $text);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = $border + $padding + 11;
        $text_posY = $imgY - 7;
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
	    imagealphablending($img, true);
    }
    else 
    {
        $text = "$event";
        $font = "fonts/LiberationSans-Regular.ttf";
        $fontSize = 7;
        $textDim = imagettfbbox($fontSize, 0, $font, $text);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = $border + $padding + 11;
        $text_posY = $imgY - 7;
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
	    imagealphablending($img, true);
    }

	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);

}
if ($type == "LatenessEvent")
{
	$imgX = 31;
	$imgY = 35;
	$botpad = 10;
	$fontSize = 8;

	$img = imagecreatetruecolor($imgX, $imgY);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);

	/**
	 * Add a fill of a colour based on lateness
	 */
    $route = "";
    $lateness = 0;

    $metric = "Lateness Dep";
	if ( isset( $attributes["Route Code"] ) )
        $route = $attributes["Route Code"];
	if ( isset( $attributes["Metric"] ) )
        $metric = $attributes["Metric"];

    draw_lateness_arrow($img, $attributes, $imgX, $imgY, $metric );

	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);

}
if ($type == "DespatcherLine")
{
	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 40;
	$imgY = 18;
	$botpad = 0;
	$padding = 2;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 0, 0, 60);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$grey = imagecolorallocate($img, 80, 80, 80);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);

	/**
	 * Add a border of a colour based on lateness
	 */
	$border_colour = $white;
	if ( $attributes["Lateness"] == "null" )
		$attributes["Lateness"] = 0;

	$border_colour = $red;

	if ($attributes["Lateness"] <= 120 )
		$border_colour = $amber;

	if ($attributes["Lateness"] <= 60 )
		$border_colour = $trangrey;

	if ($attributes["Lateness"] <= -60 )
		$border_colour = $trangreen;

	if ($attributes["Lateness"] <= -120 )
		$border_colour = $green;


	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $border_colour);


	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, $i, $w - $i, $i, $grey);
		imageline($img, $w - $i, $i, $w - $i, $h - $i, $grey);
		imageline($img, $w - $i, $h - $i, $i, $h - $i, $grey);
		imageline($img, $i, $h - $i, $i, $i, $grey);
	}
	imageline($img, $imgX / 2 - 1, 20, $imgX / 2 - 1,24, $grey);
	imageline($img, $imgX / 2 - 0, 20, $imgX / 2 - 0,30, $grey);
	imageline($img, $imgX / 2 + 1, 20, $imgX / 2 + 1,24, $grey);

	$arr=array( $imgX / 2 - 5, 25,
					$imgX / 2 + 5, 25,
					$imgX / 2 - 0,30 
				);
	imagefilledpolygon($img, $arr, 3, $grey);

	/** Add a nice circle   */
	imagefilledrectangle ( $img, $imgX - 20, $border, $imgX - ( $border * 2), $imgY - $botpad - ( $border * 2 ), $border_colour );
	
	/* Add operator
	$img_op = imagecreatefrompng("assets/operators/".$attributes["Operator Code"].".png");
	$img_op_origX = imagesx($img_op);
	$img_op_origY = imagesy($img_op);
	$img_opX = $imgX - (2 * $border) - (2 * $padding);
	$img_opY = ($img_opX / $img_op_origX) * $img_op_origY;
	$img_opY /= 2;
	$img_opX /= 2;
	imagecopyresampled($img, $img_op, $border + $padding, $border + $padding, 0, 0, $img_opX, $img_opY, $img_op_origX, $img_op_origY);
	imagealphablending($img, true);
	*/

	/**
	 * Add the route number text
	$text = "".$attributes["Service Name"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 5;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	$text_posY = 15;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
	 */

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Vehicle Code"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $border + $padding ;
	$text_posY = $imgY - $botpad - ($border + $padding) + 1;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
if ( $type == "DespatcherStop" )
{
	/**
	 * Create a base image with alpha as white
	 */
	if ( $type == "BusStop" )
	{
		$imgX = 40;
		$imgY = 10;
		$botpad = 10;
		$fontSize = 8;
	}
	else
	{
		$imgX = 20;
		$imgY = 20;
		$botpad = 5;
		$fontSize = 8;
	}
	if ( $attributes["Impact Count"] == "null" )
		$attributes["Impact Count"] = 0;
	if ( $attributes["Make"] == "null" )
		$attributes["Make"] = "TFT";
	if ( !$attributes["Make"] )
		$attributes["Make"] = "TFT";

    if ( isset ( $attributes["Activity Status"] ) )
	if ( $attributes["Activity Status"] == "Offline" && $type == "BusStop" )
	{
		$imgX = 32;
		$imgY = 30;
		$fontSize = 6;
	}
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocate($img, 255, 80, 80);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocate($img, 30, 255, 30);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$grey = imagecolorallocate($img, 210, 210, 210);
	$darkgrey = imagecolorallocate($img, 80, 80, 80);
	$stop_colour = $grey;
	if ( $attributes["Activity Status"] == "Online" )
	{
		$stop_colour = $green;
	}
	if ( $attributes["Activity Status"] == "Offline" )
		$stop_colour = $red;


    draw_stop_direction($img, $attributes, $imgX, $imgY, $stop_colour );

    if ( $attributes["Activity Status"] == "Online" || $attributes["Activity Status"] == "Offline" )
    {
        $circlex = 8;
        $circley = 8;
	    imagealphablending($img, true);
	    //imagefilledrectangle($img, 20, 20, $circlex, $circley, $alpha);
   	    imagefilledellipse($img, 14, 14, $circlex - 1, $circley - 1, $darkgrey);
   	    imagefilledellipse($img, 14, 14, $circlex - 3, $circley - 3, $stop_colour);
    }

	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
if ($type == "Service" || $type == "1DespatcherStop" )
{
	/**
	 * Create a base image with alpha as white
	 */
	if ( $type == "Service" )
	{
		$imgX = 200;
		$imgY = 32;
		$botpad = 10;
		$fontSize = 8;
	}
	else
	{
		$imgX = 20;
		$imgY = 20;
		$botpad = 5;
		$fontSize = 8;
	}
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 80, 80, 0);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$grey = imagecolorallocate($img, 210, 210, 210);
	$darkgrey = imagecolorallocate($img, 80, 80, 80);
	$stop_colour = $grey;

	/**
	 * Add a border of a colour based on lateness
	 */
	$border_colour = $white;

	$border_colour = $red;

	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);

	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, 0, $i, $imgY, $darkgrey);
		imageline($img, 0, $i, $imgX, $i, $darkgrey);
		imageline($img, $imgX - $i - 1, 0, $imgX - $i - 1, ( $imgY * (2/3) ), $darkgrey);
		imageline($img, 0, ( $imgY * (2/3) ) - $i, $imgX, ( $imgY * (2/3) ) - $i, $darkgrey);
	}

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Location Code"]." ".$attributes["Description"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$padding = 2;
	$text_posX = $border + $padding ;
	$text_posY = $imgY - ( $imgY *( 1 / 3 ) ) - 3;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
if ($type == "BusStop" || $type == "1DespatcherStop" )
{
	/**
	 * Create a base image with alpha as white
	 */
	if ( $type == "BusStop" )
	{
		$imgX = 48;
		$imgY = 45;
		$botpad = 10;
		$fontSize = 8;
	}
	else
	{
		$imgX = 20;
		$imgY = 20;
		$botpad = 5;
		$fontSize = 8;
	}
    if ( !isset($attributes["Make"]) )
		$attributes["Make"] = "TFT";
    if ( !isset($attributes["Impact Count"]) )
		$attributes["Impact Count"] = 0;
    if ( !isset($attributes["Activity Status"]) )
		$attributes["Activity Status"] = "Offline";
    if ( !isset($attributes["Routes"]) )
		$attributes["Routes"] = "U/K";
        
	if ( $attributes["Impact Count"] == "null" )
		$attributes["Impact Count"] = 0;
	if ( $attributes["Make"] == "null" )
		$attributes["Make"] = "TFT";
	if ( !$attributes["Make"] )
		$attributes["Make"] = "TFT";

	if ( $attributes["Activity Status"] == "Offline" && $type == "BusStop" )
	{
		$imgX = 32;
		$imgY = 30;
		$fontSize = 6;
	}
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 80, 80, 0);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$grey = imagecolorallocate($img, 210, 210, 210);
	$darkgrey = imagecolorallocate($img, 80, 80, 80);
	$stop_colour = $grey;
	if ( $attributes["Activity Status"] == "Online" )
	{
		$stop_colour = $green;
	}
	if ( $attributes["Activity Status"] == "Offline" )
		$stop_colour = $red;


	/**
	 * Add a border of a colour based on lateness
	 */
	$border_colour = $white;

	$border_colour = $red;

    if (isset($attributes["Impact Count"]))
	    if ($attributes["Impact Count"] <= 0 )
		    $border_colour = $trangrey;

	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);

	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, 0, $i, $imgY, $darkgrey);
		imageline($img, 0, $i, $imgX, $i, $darkgrey);
		imageline($img, $imgX - $i - 1, 0, $imgX - $i - 1, ( $imgY * (2/3) ), $darkgrey);
		imageline($img, 0, ( $imgY * (2/3) ) - $i, $imgX, ( $imgY * (2/3) ) - $i, $darkgrey);
	}

	/** Add a nice circle   */
	imagefilledrectangle ( $img, 2, 2, $imgX - 2, $imgY * ( 2 / 3  ) - 2, $stop_colour );
	
	//$img_op = imagecreatefrompng("assets/stopmakes/".$attributes["Make"].".png");
	//$img_op_origX = imagesx($img_op);
	//$img_op_origY = imagesy($img_op);
	//$img_opY = ($img_opX / $img_op_origX) * $img_op_origY;
	//$img_opY /= 2;
	//$img_opX /= 2;
	//imagecopyresampled($img, $img_op, $border + $padding, $border + $padding, 0, 0, $img_opX, $img_opY, $img_op_origX, $img_op_origY);
	//imagealphablending($img, true);

	/**
	 * Add the route number text
	 */
	if ( $type == "BusStop" )
	{
		$text = "".$attributes["Routes"];
		$font = "fonts/LiberationSans-Regular.ttf";
		$textDim = imagettfbbox($fontSize, 0, $font, $text);
		$textX = $textDim[2] - $textDim[0];
		$textY = $textDim[1] - $textDim[7];
		$text_posX = 4;
		$text_posY = $textY + 5;
		imagealphablending($img, true);
		imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
	}

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Stop Name"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$padding = 2;
	$text_posX = $border + $padding ;
	$text_posY = $imgY - ( $imgY *( 1 / 3 ) ) - 3;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
if ($type == "GPSEvent")
{
	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 64;
	$imgY = 42;
	$botpad = 10;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 0, 0, 60);
	$blue = imagecolorallocatealpha($img, 0, 0, 255, 60);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 134);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$grey = imagecolorallocate($img, 80, 80, 80);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$transparent = imagecolorallocatealpha($img, 130, 130, 130, 127);
    $padding = 2;


	/**
	 * Add a border of a colour based on lateness
	 */
	$plot_colour = $white;
	if ( $attributes["Route Code"] != "null" )
		$plot_colour = $green;

	$speed_colour = $white;
	if ( $attributes["Speed Mph"] == "null" )
		$attributes["Speed Mph"] = 99999;

	if ($attributes["Speed Mph"] <= 120 )
		$speed_colour = $amber;

	if ($attributes["Speed Mph"] <= 60 )
		$speed_colour = $trangrey;

	if ($attributes["Speed Mph"] <= -60 )
		$speed_colour = $trangreen;

	if ($attributes["Speed Mph"] <= -120 )
		$speed_colour = $green;

	$border = 1;

	imagealphablending($img, false);

    $eventtype = $attributes["Event Id"];	
    if ( $eventtype == "112" )
    {
        $bearing = $attributes["Bearing"];
        $dri = "arrn.png";
        if ( $bearing > 22 ) $dri = "arrne.png";
        if ( $bearing > 67 ) $dri = "arre.png";
        if ( $bearing > 112 ) $dri = "arrse.png";
        if ( $bearing > 157 ) $dri = "arrs.png";
        if ( $bearing > 202 ) $dri = "arrsw.png";
        if ( $bearing > 247 ) $dri = "arrw.png";
        if ( $bearing > 292 ) $dri = "arrnw.png";
        if ( $bearing > 315 ) $dri = "arrn.png";
	    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $transparent);
	    imagefilledellipse($img, $imgX / 2, $imgY - 10, 20, 20, $black);
        $img_ev = imagecreatefrompng("assets/misc/".$dri);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;
	    imagecopyresampled($img,  $img_ev, $imgX / 2 - 10, $imgY - 10 - 10, 0, 0, 20, 20, $img_ev_origX, $img_ev_origY);
	    imagealphablending($img, true);
    }
    else
    if ( $eventtype == "206" || $eventtype == "212" )
    {
        $bearing = $attributes["Bearing"];
        $dri = "bsarr.png";
        if ( $eventtype == "212" ) $dri = "bsdep.png";
	    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $transparent);
	    //imagefilledellipse($img, $imgX / 2, $imgY - 10, 20, 20, $black);
        $img_ev = imagecreatefrompng("assets/misc/".$dri);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 15;
	    $img_evY = 20;
	    imagecopyresampled($img,  $img_ev, $imgX / 2 - 15, $imgY - 36, 0, 0, 30, 36, $img_ev_origX, $img_ev_origY);
	    imagealphablending($img, true);
    }
    else
    if ( $eventtype == "219" || $eventtype == "207" )
    {
        $bearing = $attributes["Bearing"];
        $dri = "bsarr.png";
        if ( $eventtype == "212" ) $dri = "bsdep.png";
	    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $transparent);
	    imagefilledellipse($img, $imgX / 2, $imgY - 10, 40, 20, $black);
        $text = "Delayed";
        $font = "fonts/LiberationSans-Regular.ttf";
        $fontSize = 7;
        $textDim = imagettfbbox($fontSize, 0, $font, $text);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = $border + $padding + 11;
        $text_posY = $imgY - 7;
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
	    imagealphablending($img, true);
    }
    else
    {
    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $transparent);

	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, $i, $w - $i, $i, $grey);
		imageline($img, $w - $i, $i, $w - $i, $h - $i, $grey);
		imageline($img, $w - $i, $h - $i, $i, $h - $i, $grey);
		imageline($img, $i, $h - $i, $i, $i, $grey);
	}
		imageline($img, $imgX / 2 - 1, $imgY - $botpad, $imgX / 2 - 1,$imgY - 6, $grey);
		imageline($img, $imgX / 2 - 0, $imgY - $botpad, $imgX / 2 - 0,$imgY, $grey);
		imageline($img, $imgX / 2 + 1, $imgY - $botpad, $imgX / 2 + 1,$imgY - 6, $grey);

		$arr=array( $imgX / 2 - 5, $imgY - 5,
					$imgX / 2 + 5, $imgY - 5,
					$imgX / 2 - 0,$imgY 
				);
		imagefilledpolygon($img, $arr, 3, $grey);

	/** Add a nice circle   */
	imagefilledrectangle ( $img, $border, $border, $imgX - 21, $imgY - $botpad - ( $border * 2 ), $plot_colour );
	imagefilledrectangle ( $img, $imgX - 20, $border, $imgX - ( $border * 2), $imgY - $botpad - ( $border * 2 ), $speed_colour );

    if ( $eventtype == "244" || $eventtype == "238" ||$eventtype == "236" || $eventtype == "235"  ||
        $eventtype == "206" || $eventtype == "212" 
            )
    {
        if ( $eventtype == "244" || $eventtype == "238" )
	        $img_ev = imagecreatefrompng("assets/misc/etm.png");
        else if ( $eventtype == "235" || $eventtype == "236" )
	        $img_ev = imagecreatefrompng("assets/misc/etmbad.png");
        else if ( $eventtype == "212" )
	        $img_ev = imagecreatefrompng("assets/misc/bsarr.png");
        else if ( $eventtype == "206" )
	        $img_ev = imagecreatefrompng("assets/misc/bsdep.png");
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = $imgY - $botpad - ( $border * 2 );
	    //$img_evY = $img_evY / 4 - 5;
	    //$img_evX /= 4;
	    imagecopyresampled($img, $img_ev, $imgX - 20, $border, 0, 0, $img_evX, $img_evY, $img_ev_origX, $img_ev_origY);
	    imagealphablending($img, true);
    }

	/**
	 * Add the event code
	 */
    {
        $text = "Ev: ".$attributes["Event Id"];
        $font = "fonts/LiberationSans-Regular.ttf";
        $fontSize = 8;
        $textDim = imagettfbbox($fontSize, 0, $font, $text);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = $border + $padding ;
        $text_posY = 10;
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
    }

	/**
	 * Add the route number text
	 */
	$text = "Rt:".$attributes["Route Code"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 8;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
    $text_posX = $border + $padding ;
	$text_posY = 20;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Vehicle Code"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $border + $padding ;
	$text_posY = $imgY - $botpad - ($border + $padding) + 1;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
    }

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
if ($type == "Despatcher")
{
	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 64;
	$imgY = 42;
	$botpad = 10;
	$padding = 2;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 0, 0, 0);
	$blue = imagecolorallocatealpha($img, 200, 200, 255, 20);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$grey = imagecolorallocate($img, 80, 80, 80);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);

	/**
	 * Add a border of a colour based on lateness
	 */
	$lateness_colour = $white;
	if ( $attributes["Lateness"] == "null" )
		$attributes["Lateness"] = 0;

	$lateness_colour = $red;

	if ($attributes["Lateness"] <= 300 )
		$lateness_colour = $amber;

	if ($attributes["Lateness"] <= 120 )
		$lateness_colour = $trangrey;

	if ($attributes["Lateness"] <= -60 )
		$lateness_colour = $trangreen;

	if ($attributes["Lateness"] <= -120 )
		$lateness_colour = $green;

	if ($attributes["Trip Type"] == "Scheduled" )
		$lateness_colour = $blue;

	$cancelled = false;
	if ($attributes["Trip Status"] == "Cancelled" )
		$cancelled = true;

	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, $i, $w - $i, $i, $grey);
		imageline($img, $w - $i, $i, $w - $i, $h - $i, $grey);
		imageline($img, $w - $i, $h - $i, $i, $h - $i, $grey);
		imageline($img, $i, $h - $i, $i, $i, $grey);
	}
		imageline($img, $imgX / 2 - 1, $imgY - $botpad, $imgX / 2 - 1,$imgY - 6, $grey);
		imageline($img, $imgX / 2 - 0, $imgY - $botpad, $imgX / 2 - 0,$imgY, $grey);
		imageline($img, $imgX / 2 + 1, $imgY - $botpad, $imgX / 2 + 1,$imgY - 6, $grey);

		$arr=array( $imgX / 2 - 5, $imgY - 5,
					$imgX / 2 + 5, $imgY - 5,
					$imgX / 2 - 0,$imgY 
				);
		imagefilledpolygon($img, $arr, 3, $grey);

	/** Add a nice circle   */
	imagefilledrectangle ( $img, $border, $border, $imgX - 21, $imgY - $botpad - ( $border * 2 ), $white );
	imagefilledrectangle ( $img, $imgX - 20, $border, $imgX - ( $border * 2), $imgY - $botpad - ( $border * 2 ), $lateness_colour );
	
    if ( file_exists ( "assets/operators/".$attributes["Operator Code"].".png" ) )
    {
	    $img_op = imagecreatefrompng("assets/operators/".$attributes["Operator Code"].".png");
	    $img_op_origX = imagesx($img_op);
	    $img_op_origY = imagesy($img_op);
	    $padding = 2;
	    $img_opX = $imgX - (2 * $border) - (2 * $padding);
	    //$img_opY = ($img_opX / $img_op_origX) * $img_op_origY + 5;
	    $img_opY = 20;
	    $img_opY /= 2;
	    $img_opX /= 2;
	    imagecopyresampled($img, $img_op, $border + $padding, $border + $padding, 0, 0, $img_opX, $img_opY, $img_op_origX, $img_op_origY);
	    imagealphablending($img, true);
    }
    else
    {
        $padding = 2;
    }

    if ( isset ( $attributes["Bearing"] ) )
    {
        $bearing = $attributes["Bearing"];
        $dri = "only_arrn.png";
        if ( $bearing > 22 ) $dri = "only_arrne.png";
        if ( $bearing > 67 ) $dri = "only_arre.png";
        if ( $bearing > 112 ) $dri = "only_arrse.png";
        if ( $bearing > 157 ) $dri = "only_arrs.png";
        if ( $bearing > 202 ) $dri = "only_arrsw.png";
        if ( $bearing > 247 ) $dri = "only_arrw.png";
        if ( $bearing > 292 ) $dri = "only_arrnw.png";
        if ( $bearing > 315 ) $dri = "only_arrn.png";

        $drb = preg_replace("/only/", "black", $dri);

        $img_ev = imagecreatefrompng("assets/misc/".$drb);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;

	    imagealphablending($img_ev, false);
	    imagealphablending($img, true);
	    imagecopyresampled($img,  $img_ev, $imgX - 19, $imgY - 28, 0, 0, 18, 17, $img_ev_origX, $img_ev_origY);

        $img_ev = imagecreatefrompng("assets/misc/".$dri);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;


	    imagealphablending($img_ev, true);
	    //imagecopyresampled($img,  $img_ev, 0, $imgY - $imgX, 0, 0, $imgX, $imgX, $img_ev_origX, $img_ev_origY);
    }

	/**
	 * Add the route number text
	 */
    $routecolor = $black;
    if ( isset ( $attributes["Vehicle Status"] ) )
    {
        if ( $attributes["Vehicle Status"] == "Off Line" )
        {
            $routecolor = $red;
            $attributes["Service"] = "Off";
        }
        if ( $attributes["Vehicle Status"] == "Not Tracking" )
        {
            $routecolor = $red;
            $attributes["Service"] = "ETM?";
        }
    }
	$text = "".$attributes["Service"];
	$font = "fonts/LiberationMono-Bold.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	$text_posY = 12;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $routecolor, $font, $text);

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Vehicle Code"];
	if ($attributes["Trip Type"] == "Scheduled" )
		$text = "Sched";
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $border + $padding ;
	$text_posY = $imgY - $botpad - ($border + $padding) + 1;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	/**
	 * Add cancellation indicator
	 */
	if ( $cancelled )
	{
		for ($i = 0; $i <= 15; $i++)
		{
			imageline($img, 5 + $i, 5, $imgX - 5, $imgY - 5, $red);
			imageline($img, $imgX - 5 - $i, 5, 5, $imgY - 5, $red);
		}
	}
	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}


if ($type == "CarPark")
{
	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 32;
	$imgY = 42;
	$botpad = 10;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$red = imagecolorallocatealpha($img, 255, 30, 30, 0);
	$blue = imagecolorallocatealpha($img, 80, 80, 255, 0);
	$green = imagecolorallocatealpha($img, 80, 255, 80, 0);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$black = imagecolorallocate($img, 0, 0, 0);

	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY - $botpad - 1, $white);
	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, $i, $w - $i, $i, $blue);
		imageline($img, $w - $i, $i, $w - $i, $h - $i, $blue);
		imageline($img, $w - $i, $h - $i, $i, $h - $i, $blue);
		imageline($img, $i, $h - $i, $i, $i, $blue);
	}

    $arr = array(1, $imgY - 10,
                $imgX - 2, $imgY - 10,
                $imgX / 2 - 0,$imgY 
            );
    imagefilledpolygon($img, $arr, 3, $blue);
	
	$p = imagecreatefrompng("assets/carpark.png");
	$p_origX = imagesx($p);
	$p_origY = imagesy($p);
	$padding = 0;
	$pX = $imgX - (2 * $border) - (2 * $padding);
	$pY = ($pX / $p_origX) * $p_origY;
	$pY /= 1.5;
	$pX /= 1.5;
	imagecopyresampled($img, $p, $border + $padding, $border + $padding, 0, 0, $pX, $pY, $p_origX, $p_origY);
	imagealphablending($img, true);

	/**
	 * Add the capacity and spaces
	 */
	$spaces = "".$attributes["Spaces"];
	$capacity = "".$attributes["Capacity"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $capacity);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	$text_posY = 15;
	imagealphablending($img, true);
	//imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $capacity);

	$textDim = imagettfbbox($fontSize, 0, $font, $spaces);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = 2;
	$text_posY = 30;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $spaces);

    // Show occupancy as red bar
	imagefilledrectangle($img, 22, 3, $imgX - 3, $imgY - $botpad - 3, $green);
    $remaining = ( $imgY - 3 - $botpad - 3 ) * ( ( $spaces ) / $capacity );
	imagefilledrectangle($img, 22, $remaining + 3, $imgX - 3, $imgY - $botpad - 3, $red);

	/**
	 * Compile and render the image
	 */
	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}
   


if ($type == "VMS")
{
	/**
	 * Create a base image and allocate some colours
	 */
	$imgX = 160;
	$imgY = 36;
	$botpad = 10;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	$grey = imagecolorallocate($img, 169, 169, 169);
	$blue = imagecolorallocatealpha($img, 80, 80, 255, 0);
	$yellow = imagecolorallocate($img, 255, 204, 0);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);

    /**
     * Add a border
     */
    imagealphablending($img, false);
    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
    imagefilledrectangle($img, 0, 0, $imgX, $imgY - $botpad - 1, $black);
    $x = 0;
    $y = 0;
    $w = $imgX - 1;
    $h = $imgY - $botpad - 1;
    for ($i = 0; $i <= 0; $i++)
    {
        imageline($img, $i, $i, $w - $i, $i, $grey);
        imageline($img, $w - $i, $i, $w - $i, $h - $i, $grey);
        imageline($img, $w - $i, $h - $i, $i, $h - $i, $grey);
        imageline($img, $i, $h - $i, $i, $i, $grey);
    }

    /**
     * Add a little arrow underneath
     */
    $sidepad = ($imgX - 30) / 2;
    $arr = array(
        $sidepad, $imgY - $botpad,
        $imgX - $sidepad, $imgY - $botpad,
        $imgX / 2, $imgY
    );
    imagefilledpolygon($img, $arr, 3, $blue);

	/**
	 * Add the signtext
	 */
	$signtext = "".$attributes["Signtext"];
	$font = "fonts/LiberationMono-Regular.ttf";
    $signtext = preg_replace('/\s+/', ' ', $signtext);
    $line1 = substr($signtext, 0, 22);
	$fontSize = 8;
	$textDim = imagettfbbox($fontSize, 0, $font, $line1);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = 3;
	$text_posY = 11;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $yellow, $font, $line1);

    $line2 = substr($signtext, 22);
    if (strlen($line2) > 0)
    {
        $textDim = imagettfbbox($fontSize, 0, $font, $line2);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = 3;
        $text_posY = 22;
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $yellow, $font, $line2);
    }

	/**
	 * Compile and render the image
	 */
	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}


if ($type == "Works")
{
	$imgX = 32;
	$imgY = 32;

	$img = imagecreatetruecolor($imgX, $imgY);
    imagealphablending($img, false);
    imagesavealpha($img, true);
	$alpha = imagecolorallocatealpha($img, 0, 0, 0, 255);

	$p = imagecreatefrompng("assets/roadworks.png");
	imagecopy($img, $p, 0, 0, 0, 0, 32, 32);
	imagealphablending($img, true);

	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}

function draw_radius($img, $x1, $y1, $radius, $angle, $arrow_color, $arrow_length = 10, $arrow_width = 3)
{
    $x2 = $x1 + $radius * cos(deg2rad($angle-90));
    $y2 = $y1 + $radius * sin(deg2rad($angle-90));
    imageline($img, $x1, $y1, $x2, $y2, $arrow_color);

    $distance = sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
    $dx = $x2 + ($x1 - $x2) * $arrow_length / $distance;
    $dy = $y2 + ($y1 - $y2) * $arrow_length / $distance;
    $k = $arrow_width / $arrow_length;
    $x2o = $x2 - $dx;
    $y2o = $dy - $y2;
    $x3 = $y2o * $k + $dx;
    $y3 = $x2o * $k + $dy;
    $x4 = $dx - $y2o * $k;
    $y4 = $dy - $x2o * $k;
    imageline($img, $x1, $y1, $dx, $dy, $arrow_color);
    imageline($img, $x3, $y3, $x4, $y4, $arrow_color);
    imageline($img, $x3, $y3, $x2, $y2, $arrow_color);
    imageline($img, $x2, $y2, $x4, $y4, $arrow_color);

}

function draw_vehicle(&$img, &$attributes, $imgX, $imgY, $xoffset = 0, $yoffset = 0 )
{
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 127);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);

    $metriclabel = $attributes[$metric];
    $metricvalue = $attributes[$metric];

    $plotsizex = $imgX - ( $xoffset * 2 );
    $plotsizey = $imgY - ( $yoffset * 2 );

	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);

    $dri = false;
    if ( isset ( $attributes["Event Id"] ) )
    {
        $event = $attributes["Event Id"];

        if ( $event == "108" )
            $dri = "busoffroute.png";
        else if ( $event == "207" || $event == "219" )
            $dri = "busclock.png";
        else
            $dri = "bustrack.png";

	    imagealphablending($img_ev, true);
        $img_ev = imagecreatefrompng("assets/misc/".$dri);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);

	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;

	    imagecopyresampled($img,  $img_ev, $xoffset, $yoffset, 0, 0, $plotsizex, $plotsizey, $img_ev_origX, $img_ev_origY);
    }
	imagealphablending($img, false);

    if ( isset ( $attributes["Vehicle Code"] ) )
    {
	    /**
	    * Add the metric text */
	    $text = $attributes["Vehicle Code"];
	    $font = "fonts/LiberationSans-Regular.ttf";
	    $fontSize = 6;
	    $textDim = imagettfbbox($fontSize, 0, $font, $text);
	    $textX = $textDim[2] - $textDim[0];
	    $textY = $textDim[7] - $textDim[1];
	    $text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	    $text_posY = $imgY -   $textY - (  ( 20 - $textY ) / 2) - 1 + 2;
	    $text_posY = $fontSize + 5;
	    $text_posX = 3;
	    //$text_posY = $imgY / 2 + ($fontSize / 2 );
	    //$text_posY = 0;
	    imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
    }

}

function draw_stop_direction(&$img, &$attributes, $imgX, $imgY, $color )
{
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
	$white = imagecolorallocate($img, 255, 255, 255);

    $lateness_green = 200;
    $lateness_blue = 0;
    $lateness_red = 0;

    $dri = false;
    if ( isset ( $attributes["Bearing"] ) )
    {
        $bearing = $attributes["Bearing"];
        $dri = "mapmarker_n.png";
        if ( $bearing > 22 ) $dri = "mapmarker_ne.png";
        if ( $bearing > 67 ) $dri = "mapmarker_e.png";
        if ( $bearing > 112 ) $dri = "mapmarker_se.png";
        if ( $bearing > 157 ) $dri = "mapmarker_s.png";
        if ( $bearing > 202 ) $dri = "mapmarker_sw.png";
        if ( $bearing > 247 ) $dri = "mapmarker_w.png";
        if ( $bearing > 292 ) $dri = "mapmarker_nw.png";
        if ( $bearing > 315 ) $dri = "mapmarker_n.png";

        $img_ev = imagecreatefrompng("assets/misc/".$dri);

        imagecolortransparent($img, imagecolorallocatealpha($img_ev, 0, 0, 0, 127));
        imagealphablending($img, false);
        imagesavealpha($img, true);

	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;

	    imagecopyresampled($img,  $img_ev, 0, $imgY - $imgX, 0, 0, $imgX, $imgX, $img_ev_origX, $img_ev_origY);


	    //imagealphablending($img_ev, true);
        //imagefilter ( $img_ev, IMG_FILTER_COLORIZE, $lateness_red, $lateness_green, $lateness_blue, 20 );
	    //imagecopyresampled($img,  $img_ev, 0, $imgY - $imgX, 0, 0, $imgX - 10, $imgX - 10, $img_ev_origX, $img_ev_origY);
    }

}

function draw_lateness_arrow(&$img, &$attributes, $imgX, $imgY, $metric )
{
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$trangrey = imagecolorallocatealpha($img, 130, 130, 130, 87);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);

    $metriclabel = $attributes[$metric];
    $metricvalue = $attributes[$metric];

    //if ( $metric == "Lateness Dep" || $metric == "Lateness Gain"  )
    {
        $mins = round($metriclabel / 60, 0);
        $secs = modulus(round($metriclabel - ( $mins * 60 ), 0));
        if ( strlen($secs) == 0 )
            $metriclabel = $mins.":00";
        if ( strlen($secs) == 1 )
            $metriclabel = $mins.":0".$secs;
        else
            $metriclabel = $mins.":".$secs;
    }

    if ( $metric == "Dwell Time" )
    {
        $lower_range_from = 60;
        $lower_color_from = 60;
        $lower_color_upto = 0;
        $lower_color_multiplier = -1;
        $upper_color_from = 120;
        $upper_color_upto = 300;
        $upper_range_from = 60;
    }
    else 
    {
        $lower_color_from = -60;
        $lower_color_upto = -120;
        $lower_color_multiplier = -1;
        $lower_range_from = 0;
        $upper_color_from = 120;
        $upper_color_upto = 300;
        $upper_range_from = 0;
    } 

	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
	imagealphablending($img, true);
	imagefilledrectangle($img, 0, 0, $imgX, 10, $trangrey);

    $lateness_color = $metricvalue;
    if ( $lateness_color > $upper_color_upto ) $lateness_color  = $upper_color_upto;
    if ( $lateness_color < $lower_color_upto ) $lateness_color  = $lower_color_upto;

    $lateness_red = 0;
    $lateness_green = 0;
    $lateness_blue = 0;

    if ( $lateness_color > $upper_color_from ) 
    {
        $lateness_red = $lateness_color - $upper_range_from;
        $lateness_red =  ( $lateness_red / modulus($upper_color_upto - $upper_range_from) * 255 );
        $lateness_green =  255 - $lateness_red ;
        $lateness_blue =  255 - $lateness_red;
        $lateness_red = 255;
    }
    if ( $lateness_color < $lower_color_from) 
    {
        $lateness_green = $lower_range_from - $lateness_color;
        $lateness_green =  ( $lateness_green / modulus($lower_range_from - $lower_color_upto )  * 255 );
        $lateness_red =  255 - $lateness_green;
        $lateness_blue =  255 - $lateness_green;
        $lateness_green = 255;
    }


    if ( $lateness_green == 0 & $lateness_red == 0 )
    {
        $lateness_green = 255;
        $lateness_blue = 255;
        $lateness_red = 255;
    }


    $dri = false;
    if ( isset ( $attributes["Stop Bearing"] ) )
    {
        $bearing = $attributes["Stop Bearing"];
        $dri = "only_arrn.png";
        if ( $bearing > 22 ) $dri = "only_arrne.png";
        if ( $bearing > 67 ) $dri = "only_arre.png";
        if ( $bearing > 112 ) $dri = "only_arrse.png";
        if ( $bearing > 157 ) $dri = "only_arrs.png";
        if ( $bearing > 202 ) $dri = "only_arrsw.png";
        if ( $bearing > 247 ) $dri = "only_arrw.png";
        if ( $bearing > 292 ) $dri = "only_arrnw.png";
        if ( $bearing > 315 ) $dri = "only_arrn.png";

        $drb = preg_replace("/only/", "black", $dri);

        $img_ev = imagecreatefrompng("assets/misc/".$drb);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;

	    imagealphablending($img_ev, true);
	    imagecopyresampled($img,  $img_ev, 0, $imgY - $imgX, 0, 0, $imgX, $imgX, $img_ev_origX, $img_ev_origY);

        $img_ev = imagecreatefrompng("assets/misc/".$dri);
	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = 20;
	    $img_evY = 20;


	    imagealphablending($img_ev, true);
        imagefilter ( $img_ev, IMG_FILTER_COLORIZE, $lateness_red, $lateness_green, $lateness_blue, 0 );
	    imagecopyresampled($img,  $img_ev, 0, $imgY - $imgX, 0, 0, $imgX, $imgX, $img_ev_origX, $img_ev_origY);
    }

	/**
	 * Add the metric text */
	$text = $metriclabel;
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	$text_posY = $imgY -   $textY - (  ( 20 - $textY ) / 2) - 1 + 2;
	$text_posY = $fontSize;
	$text_posX = 3;
	//$text_posY = $imgY / 2 + ($fontSize / 2 );
	//$text_posY = 0;
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

}

function draw_driver_activity ( &$img, &$attributes, $imgX, $imgY, $eventtype )
{
        $border = 2;
        $padding = 2;
	    $white = imagecolorallocate($img, 255, 255, 255);
	    $trangrey = imagecolorallocatealpha($img, 130, 130, 130, 47);
	    $alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);

        // Make image transparent
	    imagealphablending($img, false);
	    imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
	    //imagealphablending($img, true);
	    //imagefilledrectangle($img, 0, 0, $imgX, 10, $trangrey);


        // Draw Driver Icon
        if ( $eventtype == "235" || $eventtype == "236" )
	        $img_ev = imagecreatefrompng("assets/misc/etmbad.png");
        else
	        $img_ev = imagecreatefrompng("assets/misc/etm.png");
	    imagealphablending($img_ev, true);

	    $img_ev_origX = imagesx($img_ev);
	    $img_ev_origY = imagesy($img_ev);
	    $padding = 2;
	    $img_evX = $imgX / 1.8;
	    $img_evY = $imgY;

	    imagealphablending($img_ev, true);
	    imagecopyresampled($img,  $img_ev, $imgX / 2, $imgY - $imgX, 0, 0, $img_evX, $imgX, $img_ev_origX, $img_ev_origY);


        // Draw RHS Grey area for text
        imagefilledrectangle($img, 0, 0, $imgX / 2, $imgY, $trangrey);

        // Draw ETM Text Info
        $route = false;
        $duty = false;
        $rb = false;
        $trip = false;
        $etmel = array();
        //if ( isset ( $attributes["Etm Route"] ) )
            //$etmel[] = "R".$attributes["Etm Route"];
        //if ( isset ( $attributes["Etm Runningno"] ) )
            //$etmel[] = "B".$attributes["Etm Runningno"];
        //if ( isset ( $attributes["Etm Duty"] ) )
            //$etmel[] = "D".$attributes["Etm Duty"];
        //if ( isset ( $attributes["Etm Trip"] ) )
            //$etmel[] = "T".$attributes["Etm Trip"];
        if ( isset ( $attributes["Etm Route"] ) )
            $etmel[] = $attributes["Etm Route"];
        if ( isset ( $attributes["Etm Runningno"] ) )
            $etmel[] = $attributes["Etm Runningno"];
        if ( isset ( $attributes["Etm Duty"] ) )
            $etmel[] = $attributes["Etm Duty"];
        if ( isset ( $attributes["Etm Trip"] ) )
            $etmel[] = $attributes["Etm Trip"];

        $font = "fonts/LiberationSans-Regular.ttf";
        $fontSize = 7;

        foreach ( $etmel as $k => $v )
        {
            $text = $v;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0;
            $text_posY = ( $k + 1 )  * ( $fontSize + 1 );
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
	        imagealphablending($img, true);
        }

        // Draw event id above driver's cap
        $fontSize = 6;
        $text = $eventtype;
        $textDim = imagettfbbox($fontSize, 0, $font, $text);
        $textX = $textDim[2] - $textDim[0];
        $textY = $textDim[7] - $textDim[1];
        $text_posX = $imgX / 2 + 4;
        $text_posY = ( $imgY - $fontSize + 1 );
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
        imagealphablending($img, true);


	    //imagealphablending($img_ev, true);
        //imagefilter ( $img_ev, IMG_FILTER_COLORIZE, $lateness_red, $lateness_green, $lateness_blue, 0 );
}

if ($type == "CountEvent")
{
	$w = 50;
	$h = 35;
	$botpad = 10;
	$fontSize = 8;
	$padding = 2;
	$border = 2;

	$img = imagecreatetruecolor($w, $h);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$tgrey = imagecolorallocatealpha($img, 127, 127, 127, 127);
	$red = imagecolorallocatealpha($img, 255, 120, 120, 0);
	$green = imagecolorallocatealpha($img, 120, 255, 120, 0);
	$blue = imagecolorallocatealpha($img, 120, 120, 255, 0);
    
//    $attributes["Event Id"] = "140";
//    draw_vehicle($img, $attributes, $w, $h );

    // transparent background
	imagesavealpha($img, true);
	imagealphablending($img, false);
    imagefill($img, 0, 0, $tgrey);
//	imagefilledrectangle($img, 0, 0, $w, $b / 2, $white);
	imagealphablending($img, true);

    // border
/*
    imageline($img, 0, 0, $r, 0, $black);
    imageline($img, $r, 0, $r, $b, $black);
    imageline($img, $r, $b, 0, $b, $black);
    imageline($img, 0, $b, 0, 0, $black);
*/

/*
    // stick figure
    imagearc($img, $r / 2 / 2, $b / 4, $r / 4, $b / 4, 0, 360, $black);
    imageline($img, $r / 2 / 2, $b / 3, $r / 2 / 2, 2 * $b / 3, $black);
    imageline($img, $r / 4 / 2, $b / 2, 3 * $r / 4 / 2, $b / 2, $black);
    imageline($img, $r / 2 / 2, 2 * $b / 3, $r / 3 / 2, $b * 7/8, $black);
    imageline($img, $r / 2 / 2, 2 * $b / 3, 2 * $r / 3 / 2, $b * 7/8, $black);
*/
    $occ_posX = 0;

    if (isset($attributes["Out Count"]))
        $out = $attributes["Out Count"];
    else if (isset($attributes["Sum Out"]))
        $out = $attributes["Sum Out"];
    
    if (isset($attributes["In Count"]))
        $in = $attributes["In Count"];
	else if (isset($attributes["Sum In"]))
        $in = $attributes["Sum In"];

    if (isset($attributes["Occupancy"]))
        $occ = $attributes["Occupancy"];
    else if (isset($attributes["Average Occupancy"]))
        $occ = $attributes["Average Occupancy"];

	if (isset($in) && isset($out))
	{
        if ($out > 0)
        {
            $text = "-".$out;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 12;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); // ($w * 1/2) - ($textX / 2);
            $text_posY = - $textY + $padding;

            $occ_posX = $text_posX + $textX;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $red);
            imageellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $black);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX - 4, $text_posY, $black, $font, $text);
        }

        if ($in > 0)
        {
            $text = "+".$in;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 12;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); //($w * 1/2) - ($textX / 2);
            $text_posY = $h - 2 * $padding;

            $occ_posX_2 = $text_posX + $textX;
            if ($occ_posX_2 > $occ_posX)
                $occ_posX = $occ_posX_2;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $green);
            imageellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $black);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX - 4, $text_posY, $black, $font, $text);
        }
	}

    if (isset($occ))
	{
		$text = $occ;
		$font = "fonts/LiberationMono-Bold.ttf";
		$fontSize = 8;
		$textDim = imagettfbbox($fontSize, 0, $font, $text);
		$textX = $textDim[2] - $textDim[0] + 6;
		$textY = $textDim[7] - $textDim[1] - 2;
//		$text_posX = ($w * 3/4) - ($textX / 2);
        $text_posX = $occ_posX;
		$text_posY = ($h * 1/2) - ($textY / 2);

        $ellipseX = $text_posX - 1 + $textX / 2;
        $ellipseY = $text_posY + 1 + $textY / 2;
        imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 1 * $padding, $textY - 1 * $padding, $blue);
        imageellipse($img, $ellipseX, $ellipseY, $textX + 1 * $padding, $textY - 1 * $padding, $black);
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
	}

	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}


if ($type == "1CountEvent")
{
	$w = 50;
	$h = 35;
	$botpad = 10;
	$fontSize = 8;
	$padding = 2;
	$border = 2;

	$img = imagecreatetruecolor($w, $h);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$tgrey = imagecolorallocatealpha($img, 127, 127, 127, 127);
	$red = imagecolorallocatealpha($img, 255, 40, 40, 0);
	$green = imagecolorallocatealpha($img, 0, 255, 0, 0);
	$blue = imagecolorallocatealpha($img, 80, 80, 255, 0);
    
//    $attributes["Event Id"] = "140";
//    draw_vehicle($img, $attributes, $w, $h );

    // transparent background
	imagesavealpha($img, true);
	imagealphablending($img, false);
    imagefill($img, 0, 0, $tgrey);
//	imagefilledrectangle($img, 0, 0, $w, $b / 2, $white);
	imagealphablending($img, true);

    // border
/*
    imageline($img, 0, 0, $r, 0, $black);
    imageline($img, $r, 0, $r, $b, $black);
    imageline($img, $r, $b, 0, $b, $black);
    imageline($img, 0, $b, 0, 0, $black);
*/

/*
    // stick figure
    imagearc($img, $r / 2 / 2, $b / 4, $r / 4, $b / 4, 0, 360, $black);
    imageline($img, $r / 2 / 2, $b / 3, $r / 2 / 2, 2 * $b / 3, $black);
    imageline($img, $r / 4 / 2, $b / 2, 3 * $r / 4 / 2, $b / 2, $black);
    imageline($img, $r / 2 / 2, 2 * $b / 3, $r / 3 / 2, $b * 7/8, $black);
    imageline($img, $r / 2 / 2, 2 * $b / 3, 2 * $r / 3 / 2, $b * 7/8, $black);
*/
    $occ_posX = 0;

    if (isset($attributes["Out Count"]))
        $out = $attributes["Out Count"];
    else if (isset($attributes["Sum Out"]))
        $out = $attributes["Sum Out"];
    
    if (isset($attributes["In Count"]))
        $in = $attributes["In Count"];
	else if (isset($attributes["Sum In"]))
        $in = $attributes["Sum In"];

    if (isset($attributes["Occupancy"]))
        $occ = $attributes["Occupancy"];
    else if (isset($attributes["Average Occupancy"]))
        $occ = $attributes["Average Occupancy"];

	if (isset($in) && isset($out))
	{
        if ($out > 0)
        {
            $text = $out;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 10;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); // ($w * 1/2) - ($textX / 2);
            $text_posY = - $textY + $padding;

            $occ_posX = $text_posX + $textX;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $red);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
        }

        if ($in > 0)
        {
            $text = $in;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 10;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); //($w * 1/2) - ($textX / 2);
            $text_posY = $h - 2 * $padding;

            $occ_posX_2 = $text_posX + $textX;
            if ($occ_posX_2 > $occ_posX)
                $occ_posX = $occ_posX_2;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $green);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
        }
	}

    if (isset($occ))
	{
		$text = $occ;
		$font = "fonts/LiberationMono-Bold.ttf";
		$fontSize = 8;
		$textDim = imagettfbbox($fontSize, 0, $font, $text);
		$textX = $textDim[2] - $textDim[0];
		$textY = $textDim[7] - $textDim[1];
//		$text_posX = ($w * 3/4) - ($textX / 2);
        $text_posX = $occ_posX;
		$text_posY = ($h * 1/2) - ($textY / 2);

        $ellipseX = $text_posX - 1 + $textX / 2;
        $ellipseY = $text_posY + 1 + $textY / 2;
        imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 1 * $padding, $textY - 1 * $padding, $blue);
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
	}

	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}

function draw_pax_count(&$img, $imgX, $imgY, $in, $out, $occ)
{
    $occ_posX = 0;

	$w = 31;
	$h = 35;
	$botpad = 10;
	$fontSize = 8;
	$padding = 2;
	$border = 2;

	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$tgrey = imagecolorallocatealpha($img, 127, 127, 127, 127);
	$red = imagecolorallocatealpha($img, 255, 40, 40, 0);
	$green = imagecolorallocatealpha($img, 0, 255, 0, 0);
	$blue = imagecolorallocatealpha($img, 80, 80, 255, 0);

	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, 50, $alpha);
	//imagealphablending($img, true);

        if ($out> 0)
        {
            $text = $out;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 10;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); // ($w * 1/2) - ($textX / 2);
            $text_posY = - $textY + $padding;

            $occ_posX = $text_posX + $textX;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $red);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
        }

        if ($in > 0)
        {
            $text = $in;
            $font = "fonts/LiberationMono-Bold.ttf";
            $fontSize = 10;
            $textDim = imagettfbbox($fontSize, 0, $font, $text);
            $textX = $textDim[2] - $textDim[0];
            $textY = $textDim[7] - $textDim[1];
            $text_posX = 0 + (2 * $padding); //($w * 1/2) - ($textX / 2);
            $text_posY = $h - 2 * $padding;

            $occ_posX_2 = $text_posX + $textX;
            if ($occ_posX_2 > $occ_posX)
                $occ_posX = $occ_posX_2;

            $ellipseX = $text_posX - 1 + $textX / 2;
            $ellipseY = $text_posY + 1 + $textY / 2;
            imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 3 * $padding, $textY - 2 * $padding, $green);
            imagealphablending($img, true);
            imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
        }

	{
		$text = $occ;
		$font = "fonts/LiberationMono-Bold.ttf";
		$fontSize = 8;
		$textDim = imagettfbbox($fontSize, 0, $font, $text);
		$textX = $textDim[2] - $textDim[0];
		$textY = $textDim[7] - $textDim[1];
//		$text_posX = ($w * 3/4) - ($textX / 2);
        $text_posX = $occ_posX;
		$text_posY = ($h * 1/2) - ($textY / 2);

        $ellipseX = $text_posX - 1 + $textX / 2;
        $ellipseY = $text_posY + 1 + $textY / 2;
        imagefilledellipse($img, $ellipseX, $ellipseY, $textX + 1 * $padding, $textY - 1 * $padding, $blue);
        imagealphablending($img, true);
        imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $white, $font, $text);
//echo $text_posX." ";
//echo "oo";
//echo $textX." ";
//echo "oo";
//die;
	}
}

if ($type == "VehicleOccupancy")
{
	/**
	 * Create a base image with alpha as white
	 */
	$imgX = 64;
	$imgY = 42;
	$botpad = 10;
	$img = imagecreatetruecolor($imgX, $imgY);
	$white = imagecolorallocatealpha($img, 255, 255, 255, 0);
	$black = imagecolorallocate($img, 0, 0, 0);
	$red = imagecolorallocatealpha($img, 255, 0, 0, 60);
	$blue = imagecolorallocatealpha($img, 0, 0, 255, 60);
	$amber = imagecolorallocatealpha($img, 255, 127, 127, 60);
	$trangreen = imagecolorallocatealpha($img, 80, 220, 80, 80);
	$green = imagecolorallocatealpha($img, 30, 255, 30, 60);
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 117);
	$yellow = imagecolorallocate($img, 255, 255, 0);
	$orange = imagecolorallocate($img, 255, 165, 0);
	$grey = imagecolorallocate($img, 80, 80, 80);
	$trangrey = imagecolorallocatealpha($img, 250, 250, 010, 0);
	$padding = 2;

	/**
	 * Add a border of a colour based on lateness
	 */
	$lateness_colour = $white;
	$lateness_colour = $trangrey;

	$border = 1;
	imagealphablending($img, false);
	imagefilledrectangle($img, 0, 0, $imgX, $imgY, $alpha);
	$x = 0;
	$y = 0;
	$w = $imgX - 1;
	$h = $imgY - $botpad - 1;
	for ($i = 0; $i <= 0; $i++)
	{
		imageline($img, $i, $i, $w - $i, $i, $grey);
		imageline($img, $w - $i, $i, $w - $i, $h - $i, $grey);
		imageline($img, $w - $i, $h - $i, $i, $h - $i, $grey);
		imageline($img, $i, $h - $i, $i, $i, $grey);
	}
		imageline($img, $imgX / 2 - 1, $imgY - $botpad, $imgX / 2 - 1,$imgY - 6, $grey);
		imageline($img, $imgX / 2 - 0, $imgY - $botpad, $imgX / 2 - 0,$imgY, $grey);
		imageline($img, $imgX / 2 + 1, $imgY - $botpad, $imgX / 2 + 1,$imgY - 6, $grey);

		$arr=array( $imgX / 2 - 5, $imgY - 5,
					$imgX / 2 + 5, $imgY - 5,
					$imgX / 2 - 0,$imgY 
				);
		imagefilledpolygon($img, $arr, 3, $grey);

    /** Add a white canvas box   */
    imagefilledrectangle ( $img, $border, $border, $imgX - 21, $imgY - $botpad - ( $border * 2 ), $white );
    imagefilledrectangle ( $img, $imgX - 20, $border, $imgX - ( $border * 2), $imgY - $botpad - ( $border * 2 ), $lateness_colour );

	/**
	 * Add Total In
	 */
    if ( $attributes["Total Out"] != "null" )
    {
	    $text = "O:".$attributes["Total Out"];
	    $font = "fonts/LiberationSans-Regular.ttf";
	    $fontSize = 8;
	    $textDim = imagettfbbox($fontSize, 0, $font, $text);
	    $textX = $textDim[2] - $textDim[0];
	    $textY = $textDim[7] - $textDim[1];
	    $text_posX = $imgX -  ($imgX / 2 ) -  $textX - (  ( 10 - $textX ) / 2) - 1;
	    $text_posX = 2;
	    $text_posY = 15;
	    imagealphablending($img, true);
	    imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
    }

    if ( $attributes["Total In"] != "null" )
    {
	    $text = "I: ".$attributes["Total Out"];
	    $font = "fonts/LiberationSans-Regular.ttf";
	    $fontSize = 8;
	    $textDim = imagettfbbox($fontSize, 0, $font, $text);
	    $textX = $textDim[2] - $textDim[0];
	    $textY = $textDim[7] - $textDim[1];
	    $text_posX = $imgX -   $textX - (  ( 20 - $textX ) / 2) - 1;
	    $text_posX = ( $imgX / 2 ) + 2;
	    $text_posY = 15;
	    imagealphablending($img, true);
	    imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);
    }

	/**
	 * Add the fleet number text
	 */
	$text = $attributes["Vehicle Code"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $border + $padding ;
	$text_posX = 2;
	$text_posY = 15;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, $text);

	/**
	 * Add the occupancy text
	 */
	$text = $attributes["Occupancy"];
	$font = "fonts/LiberationSans-Regular.ttf";
	$fontSize = 10;
	$textDim = imagettfbbox($fontSize, 0, $font, $text);
	$textX = $textDim[2] - $textDim[0];
	$textY = $textDim[7] - $textDim[1];
	$text_posX = $border + $padding ;
	$text_posX = 2;
	$text_posY = $imgY - $botpad - ($border + $padding) + 1;
	imagealphablending($img, true);
	imagettftext($img, $fontSize, 0, $text_posX, $text_posY, $black, $font, "Occ: " .$text);

	imagealphablending($img, false);
	imagesavealpha($img, true);
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
}

?>

