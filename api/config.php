<?php
// Meatloaf Server
// https://github.com/idolpx/meatloaf-svr
// Copyright(C) 2020 James Johnston
//
// Meatloaf is free software : you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// 
// Meatloaf is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with Meatloaf. If not, see <http://www.gnu.org/licenses/>.

require_once("IMG_NATIVE.php");
require_once("IMG_P00.php");
require_once("IMG_D64.php");
require_once("IMG_D71.php");
require_once("IMG_D81.php");
require_once("IMG_D8B.php");
require_once("IMG_T64.php");
require_once("IMG_TCRT.php");

// Base URL for server
$url = "C64.MEATLOAF.CC";

// Set this to the folder where your roms are stored
$root = $_SERVER['DOCUMENT_ROOT']."/roms/";

$disk_name = "MEATLOAF 64 ARCHIVE"; // Set default listing header
$show_nfo = true;		            // Show selected URL, PATH, IMAGE in directory listing
$show_hidden = false;          		// Show hidden/deleted files in directory listing


$path = $_GET['p'].$_POST['p'];
if ($path == '')
	$path = "/";

$image = $_GET['i'].$_POST['i'];
$filename = $_GET['f'].$_POST['f'];
if ($filename == '')
	$filename = '$';

?>
