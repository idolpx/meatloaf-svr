<?php
//
// Meatloaf - A Commodore 64/128 multi-device emulator
// https://github.com/idolpx/meatloaf
// Copyright(C) 2022 James Johnston
//
// Meatloaf Server Script-----------------------------------------
// Create a directory listing as a Commodore Basic Program
// Responds with binary PRG file ready to load and list
// ---------------------------------------------------------------
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
//

//
// https://gist.github.com/idolpx/ab8874f8396b6fa0d89cc9bab1e4dee2
//


$basic_start = 0x0801;
$next_entry = $basic_start;

if(!isset($root))
{
    $url = "C64.MEATLOAF.CC";
    $root = $_SERVER["DOCUMENT_ROOT"]."/";
	//echo $root."\n";
}


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
        if (strlen($ext) < 3)
            $ext = "PRG";
    }
    return strtoupper($ext);
}

function sendLine($blocks, $line)
{
    global $next_entry;
    
    $line .= "\x00";
    $next_entry = $next_entry + 4 + strlen($line);
    echo pack('v', $next_entry);
    echo pack('v', $blocks);
    echo strtoupper("$line");
}

function sendListing($dir, $exp)
{
    global $url, $root, $basic_start;
    
    // Send basic load address
    echo pack('v', $basic_start);
    
    // Send List HEADER
    sendLine(0, "\x12\"MEATLOAF ARCHIVE\" 08 2A");

    //echo "[$dir]"; exit();
    $dh = @opendir($root.$dir);
    
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
    sendLine( 65535, "BLOCKS FREE" );
    
    // Send 0000 to end basic program
    echo "\x00\x00";
}

$path = $_GET['p'];
if ($path == '')
	$path = "/";

//Set Content Type
header('Content-Type: application/octet-stream');

//Use Content-Disposition: attachment to specify the filename
header('Content-Disposition: attachment; filename="index.prg"');

sendListing($path, '/(?!^\..*?$|^.*?.html|^.*?.php|^api$|^web.config$)^.*?$/i');

?>  