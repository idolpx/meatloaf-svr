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

$url = "C64.IDOLPX.COM";
$root = ".";

function get_type($name)
{
	global $root;
	
	if(is_dir($root.$name))
	{
		$ext = "DIR";
	}
	else
	{
		$ext = pathinfo($root.$name, PATHINFO_EXTENSION);
		if (!strlen($ext))
			$ext = "PRG";
	}
	return strtoupper($ext);
}

function sendListing($dir, $exp)
{
	global $url;
	global $root;
	
	// Send List HEADER
    $line = array( 'blocks'=>0, 'line'=>"\x12\"  MEAT LOAF 64  \" 08 2A" );
	$r = array();
	array_push($r, $line);
	//echo "[$dir]"; exit();
    $dh = @opendir($root.$dir);
	//echo $dir."\r\n";
	
	// Send Extra INFO
	$line = sprintf("\"%-19s\" NFO", "[URL]");
	$f = array( 'blocks'=>0, 'line'=>$line, 'type'=>"NFO" );
	array_push($r, $f);
	$line = sprintf("\"%-19s\" NFO", $url);
	$f = array( 'blocks'=>0, 'line'=>$line, 'type'=>"NFO");
	array_push($r, $f);
	if (strlen($dir) > 1)
	{
		$line = sprintf("\"%-19s\" NFO", "[PATH]");
		$f = array( 'blocks'=>0, 'line'=>$line, 'type'=>"NFO" );
		array_push($r, $f);
		$line = sprintf("\"%-19s\" NFO", $dir);
		$f = array( 'blocks'=>0, 'line'=>$line, 'type'=>"NFO" );
		array_push($r, $f);		
	}
	$line = array( 'blocks'=>0, 'line'=>"\"-------------------\" NFO", 'type'=>"NFO" );
	array_push($r, $line);

    if ($dh) {
        while (($fname = readdir($dh)) !== false) {
			//echo $fname."\r\n";
            if (preg_match($exp, $fname)) {
                $stat = stat("$root$dir/$fname");
				$type = get_type("$dir/$fname");
				$blocks = intval($stat['size']/256);
				$block_spc = 3;
				if ($blocks > 9) $block_spc--;
				if ($blocks > 99) $block_spc--;
		
				$line = sprintf("%s%-18s %s", str_repeat(" ", $block_spc), "\"".$fname."\"", $type);
				$f = array( 'blocks'=>$blocks, 'line'=>$line, 'type'=>$type );
				array_push($r, $f);
            }
        }
        closedir($dh);
    }
    $line = array( 'blocks'=>65536, 'line'=>"BLOCKS FREE" );
	array_push($r, $line);	
    return( $r );
}

$path = $_GET['p'];
if ($path == '')
	$path = "/";

$directory = sendListing($path, '/(?!^\.&|^\.\.$|.*?\.php)([a-z0-9]+)$/i');


header('Content-Type: application/json');
echo json_encode($directory, JSON_PRETTY_PRINT);
?> 