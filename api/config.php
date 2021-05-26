<?php

// PRG File
require_once("IMG_NATIVE.php");
require_once("IMG_P00.php");

// Disk Image
require_once("IMG_D64.php");
require_once("IMG_D71.php");
require_once("IMG_D81.php");
require_once("IMG_D8B.php");

// Tape Image
require_once("IMG_T64.php");
require_once("IMG_TCRT.php");

// Cartridge Image
require_once("IMG_CRT.php");



$url = "C64.MEATLOAF.CC";
$root = $_SERVER['DOCUMENT_ROOT']."/roms/";


$path = $_GET['p'].$_POST['p'];
if ($path == '')
	$path = "/";

$image = $_GET['i'].$_POST['i'];
$filename = $_GET['f'].$_POST['f'];
if ($filename == '')
	$filename = '$';

$disk_name = "MEATLOAF C64 ARCHIVE";
$show_nfo = true;		// Show selected URL, PATH, IMAGE in directory listing
$show_hidden = false;	// Show hidden/deleted files in directory listing
?>
