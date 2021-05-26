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

function sendLine($blocks, $line)
{
	echo "$blocks\t$line\n";
	//echo "$line\n";
}

function sendListing($dir, $exp)
{
	global $url;
	global $root;
	
	// Send List HEADER
    sendLine(0, "\x12\"  MEAT LOAF 64  \" 08 2A");

	//echo "[$dir]"; exit();
    $dh = @opendir($root.$dir);
	//echo $dir."\r\n";
	
	// Send Extra INFO
	sendLine(0, sprintf("\"%-19s\" NFO", "[URL]"));
	sendLine(0, sprintf("\"%-19s\" NFO", $url));
	if (strlen($dir) > 1)
	{
		sendLine(0, sprintf("\"%-19s\" NFO", "[PATH]"));
		sendLine(0, sprintf("\"%-19s\" NFO", $dir));
	}
	sendLine(0, "\"-------------------\" NFO");

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
				sendLine( $blocks, $line );
            }
        }
        closedir($dh);
    }
    sendLine( 65536, "BLOCKS FREE" );
}

function sendFile()
{
	
}

$path = $_GET['p'];
if ($path == '')
	$path = "/";

$directory = sendListing($path, '/(?!^\.&|^\.\.$|.*?\.php)([a-z0-9]+)$/i');


//header('Content-Type: application/json');
//echo json_encode($directory, JSON_PRETTY_PRINT);
?> 