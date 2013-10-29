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

 * File:        imageget.php
 *
 * Script to take location of an image within the database
 * and output it to the browser. Used by the Reportico engine
 * as a URL that can be embeeded in report browser output.
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2012 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: imageget.php,v 1.6 2012-08-24 23:08:21 peter Exp $
 */


include_once('adodb/adodb.inc.php');
define('DIR_SEP', "/");
include_once ("swutil.php");

error_reporting(E_ALL);

session_start();


set_project_environment();

if (isset($HTTP_GET_VARS['hostname'])) 
  $hostname = $HTTP_GET_VARS['hostname'];
else 
  $hostname = "localhost";

if (isset($HTTP_GET_VARS['driver'])) 
  $driver = $HTTP_GET_VARS['driver'];
else 
  $driver = "ppp";

if (isset($HTTP_GET_VARS['dbname'])) 
  $dbname = $HTTP_GET_VARS['dbname'];
else 
  $dbname = "x";

if (isset($_SESSION['hostname'])) 
{
	$hostname =  $_SESSION['hostname'];
}

if (isset($_SESSION['database'])) 
{
	$dbname =  $_SESSION['database'];
}

if (isset($_SESSION['driver'])) 
{
	$driver =  $_SESSION['driver'];
}

$imagesql = $_REQUEST["imagesql"];

$username='';
$password='';

if (isset($_SESSION['userid'])) 
	$username =  $_SESSION['userid'];

if (isset($_SESSION['password'])) 
	$password =  $_SESSION['password'];

if ( SW_DB_CONNECT_FROM_CONFIG )
{
	$driver = SW_DB_DRIVER;
	$password = SW_DB_PASSWORD;
	$username = SW_DB_USER;
	$hostname = SW_DB_HOST;
	$dbname = SW_DB_DATABASE;
	$server = SW_DB_SERVER;
	$protocol = SW_DB_PROTOCOL;
}



$ado_connection = false;
if ( $driver == "pdo_informix" )
{
	$cnstr =
		"informix:".
		"host=".$hostname."; ".
		"server=".$server."; ".
		"protocol=".$protocol."; ".
		"database=".$dbname;

	$db = new PDO($cnstr, $username,$password); 
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->setAttribute( PDO::ATTR_CASE, PDO::CASE_LOWER );

	$stmt = $db->prepare($imagesql);
	$stmt->execute();
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
	$row = $stmt->fetch(PDO::FETCH_BOUND);
	fpassthru($data);
	$stmt = null;
}
else
if ( $driver == "pdo_oci" )
{
	$cnstr =
		"oci:".
		"host=".$hostname."; ".
		"dbname=".$dbname;

	$db = new PDO($cnstr, $username,$password); 
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->setAttribute( PDO::ATTR_CASE, PDO::CASE_LOWER );

	$stmt = $db->prepare(stripslashes($imagesql));
	$stmt->execute();
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
	$row = $stmt->fetch(PDO::FETCH_BOUND);
	echo $data;
	//fpassthru($data);
	$stmt = null;
}
else
if ( $driver == "pdo_pgsql" )
{
	$cnstr =
		"pgsql:".
		"host=".$hostname."; ".
		"user=".$username."; ".
		"password=".$password."; ".
		"dbname=".$dbname;

	$db = new PDO($cnstr, $username,$password); 
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->setAttribute( PDO::ATTR_CASE, PDO::CASE_LOWER );

	$stmt = $db->prepare(stripslashes($imagesql));
	$stmt->execute();
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
	$row = $stmt->fetch(PDO::FETCH_BOUND);
	echo $data;
	//fpassthru($data);
	$stmt = null;
}
else
if ( $driver == "pdo_mysql" )
{
	$cnstr =
		"mysql:".
		"host=".$hostname."; ".
		"dbname=".$dbname;

	$db = new PDO($cnstr, $username,$password); 
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->setAttribute( PDO::ATTR_CASE, PDO::CASE_LOWER );

	$stmt = $db->prepare(stripslashes($imagesql));
	$stmt->execute();
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
	$row = $stmt->fetch(PDO::FETCH_BOUND);
	echo $data;
	//fpassthru($data);
	$stmt = null;
}
else
if ( $driver == "pdo_sqlite3" )
{
	$cnstr =
		"sqlite:".
		$hostname.$dbname;

	$db = new PDO($cnstr, $username,$password); 
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db->setAttribute( PDO::ATTR_CASE, PDO::CASE_LOWER );

	$stmt = $db->prepare(stripslashes($imagesql));
	$stmt->execute();
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
	$row = $stmt->fetch(PDO::FETCH_BOUND);
	echo $data;
	//fpassthru($data);
	$stmt = null;
}
else
{
	$ado_connection = NewADOConnection($driver);
	$ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
	$ado_connection->PConnect($hostname,$username,$password,$dbname);

	$rs = $ado_connection->Execute($imagesql) 
		or die("Query failed : " . $ado_connection->ErrorMsg());
	$line = $rs->FetchRow();

	//header('Content-Type: image/gif');
	foreach ( $line as $col )
	{
		$data = $col;
		break;
	}
}


/**
 * Function set_project_environment
 *
 * Analyses configuration and current session to identify which project area
 * is to be used. 
 * If a project is specified in the HTTP parameters then that is used, otherwise
 * the current SESSION project is used. If none of these are specified then the default
 * "reports" project is used
 */
function set_project_environment()
{
	global $g_project;
	global $g_menu;
	global $g_menu_title;

	$project = session_request_item("project", "reports");
	$menu = false;
	$menu_title = "Set Menu Title";

	// Now we now the project include the relevant config.php
	$projpath = "projects/".$project;
	$configfile = $projpath."/config.php";
	$menufile = $projpath."/menu.php";

	if ( !is_file($projpath) )
		find_file_to_include($projpath, $projpath);

	if ( !$projpath )
	{
		find_file_to_include("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		$g_menu_title = "";
		$old_error_handler = set_error_handler("ErrorHandler");
		handle_error("Project Directory $project not found. Check INCLUDE_PATH or project name");
		return;
	}
	
	if ( !is_file($configfile) )
		find_file_to_include($configfile, $configfile);
	if ( !is_file($menufile) )
		find_file_to_include($menufile, $menufile);
	
	if ( $configfile )
	{
		include_once($configfile);
		if ( is_file($menufile) )
			include_once($menufile);
		else
			handle_error("Menu Definition file menu.php not found in project $project", E_USER_WARNING);
	}
	else
	{
		find_file_to_include("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		$g_menu_title = "";
		$old_error_handler = set_error_handler("ErrorHandler");
		handle_error("Configuration Definition file config.php not found in project $project", E_USER_ERROR);
	}

	$g_project = $project;
	$g_menu = $menu;
	$g_menu_title = $menu_title;
	return $project;
}
?>
