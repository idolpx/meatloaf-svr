<?php

//
// Meatloaf Server Script
// Create a directory listing as a Commodore Basic Program
// Responds with binary PRG file ready to load and list
//
//

$basic_start = 0x0801;
$next_entry = $basic_start + 2;

if(!isset($url))
{
    $url = "C64.MEATLOAF.CC";
    $root = getcwd()."/roms/";
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
    $next_entry = $next_entry + strlen($line);
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
	sendLine(0, "\x12\"MEATLOAF ARCHIVE\"\x0A08\x0A2A");

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
            if (preg_match($exp, $fname) && $fname != "index.php") {
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
sendListing($path, '/(?!^\.&|^\.\.$|.*?\.php)([a-z0-9]+)$/i');
?> 