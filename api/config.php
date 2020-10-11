<?php
require_once("IMG_NATIVE.php");
require_once("IMG_P00.php");
require_once("IMG_D64.php");
require_once("IMG_D71.php");
require_once("IMG_D81.php");
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