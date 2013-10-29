<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2012 Peter Deed

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        reportico.php
 *
 * Reportico runner script
 * !!! Note this script will run reports in SAFE design mode
 * !!! This means that users of the reports will be able to 
 * !!! design reports but not be able to save them of modify custom source code
 *
 * To run, project and execute_mode should be specified
 * Uncomment the ob_start/ob_end_flush lines to turn on output buffering
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2012 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: runsafe.php,v 1.7 2012-08-24 23:08:48 peter Exp $
 */

	ini_set("memory_limit","100M");
	error_reporting(E_ALL);
    date_default_timezone_set(@date_default_timezone_get());


	//ob_start();
	require_once('reportico.php');

	$q = new reportico();
	$q->allow_maintain = "SAFE";
	$q->allow_debug = true;
	$q->execute($q->get_execute_mode(), true);
	//ob_end_flush();
?>
