<?php

// PRG File
require_once("../meatloafFileSystem.php");
require_once("meatloafP00.php");

// Disk Image
require_once("disk/meatloafD64.php");
require_once("disk/meatloafD71.php");
require_once("disk/meatloafD81.php");
require_once("disk/meatloafDNP.php");
require_once("disk/meatloafD8B.php");

// Tape Image
require_once("tape/meatloafT64.php");
require_once("tape/meatloafTCRT.php");

// Cartridge Image
require_once("cartridge/meatloafCRT.php");

// Archives
require_once("archive/meatloafLNX.php");


$url = "C64.MEATLOAF.CC";
$media = "/roms/";
$root = $_SERVER['DOCUMENT_ROOT'].$media;
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
	 $media_root = "https://";   
else  
	 $media_root = "http://";   
 
// Append the host(domain name, ip) to the URL.   
$media_root.= $_SERVER['HTTP_HOST'];   
$media_root.= $media;

$partition = $_GET['pt'].$_POST['pt'];
$path = $_GET['p'].$_POST['p'];
if ($path == '')
	$path = "/";

$archive = $_GET['a'].$_POST['a'];
$image = $_GET['i'].$_POST['i'];
$filename = $_GET['f'].$_POST['f'];
if (substr($filename, -1) == '')
	$filename = '$';

$disk_name = "MEATLOAF C64 ARCHIVE";
$disk_id = "ID 99";
$block_size = 1024;

$show_nfo = true;		// Show selected URL, PATH, IMAGE in directory listing
$show_hidden = false;	// Show hidden/deleted files in directory listing
$show_datetime = false;
$show_filecount = true;
?>