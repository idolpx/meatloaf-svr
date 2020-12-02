<?php
/*
 *  Meat Loaf Server
 *  ---------------
 *
 *  Copyright (C) 2020, Jaime Johnston <jaime@idolpx.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

require_once("IMG_NATIVE.php");
require_once("IMG_P00.php");
require_once("IMG_D64.php");
require_once("IMG_D71.php");
require_once("IMG_D81.php");
require_once("IMG_D8B.php");
require_once("IMG_T64.php");
require_once("IMG_TCRT.php");

$url = "C64.IDOLPX.COM";
$root = $_SERVER['DOCUMENT_ROOT']."/64/";


$path = $_GET['p'].$_POST['p'];
if ($path == '')
	$path = "/";

$image = $_GET['i'].$_POST['i'];
$filename = $_GET['f'].$_POST['f'];
if ($filename == '')
	$filename = '$';


$show_nfo = true;		// Show selected URL, PATH, IMAGE in directory listing
$show_hidden = false;	// Show hidden/deleted files in directory listing
?>