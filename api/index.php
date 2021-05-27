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


//
// File Formats
//
// https://ist.uwaterloo.ca/~schepers/formats.html
// https://www.infinite-loop.at/Power64/Documentation/Power64-ReadMe/AE-File_Formats.html
// https://www.backbit.io/downloads/Docs/BackBit%20Cartridge%20Documentation.pdf
//
//

require_once("config.php");

// Function to count number of set bits in n
function numOfSetBits( $n )
{
	// stores the total bits set in n
	$count = 0;

	while ($n != 0) {
		$n = $n & ($n - 1); // clear the least significant bit set
		$count++;
	}

	return $count;
}

function get_type($name)
{	
	if(is_dir($name) || !strlen($name))
	{
		$ext = "DIR";
	}
	else
	{
		$filename = pathinfo($name, PATHINFO_FILENAME);
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		if (strlen($ext) < 3)
			$ext = "PRG";
		
		if ($filename == "$")
			$ext = "DIR";
	}
	return strtoupper($ext);
}

function file_exists_ci($filename) 
{
	//echo $filename; exit();
	if (is_dir($filename))
	{
		return NULL;
	}
	if (file_exists($filename))
	{
		return basename( $filename );
	}

	$lowerfile = basename(strtolower($filename));

	foreach (glob(dirname($filename) . '/*')  as $file)
	{
		if (strpos( strtolower($file), $lowerfile) !== false)
		{
			return basename( $file );
		}
	}
	return basename ( $filename );
}

//echo intval(strpos($filename, "$")); exit();




//echo strlen( $image ); exit();
//echo $root; exit();
//echo $path; exit();
//echo $image; exit();
//echo $filename; exit();
//echo "$root$path$image"; exit();

if ( strlen( $image ) )
{
	$image = file_exists_ci( "$root$path$image" );
	$i_ext = get_type("$image");
	//echo $i_ext; exit();
	if ( $filename == '$' )
	{
		switch ( $i_ext )
		{
			case 'D64':
				$fileSystem = new IMG_D64("$root$path$image");
				$fileSystem->sendListing();
				break;
			
			case 'D71':
				$fileSystem = new IMG_D71("$root$path$image");
				$fileSystem->sendListing();
				break;
				
			case 'D81':
				$fileSystem = new IMG_D81("$root$path$image");
				$fileSystem->sendListing();
				break;
				
			case 'D8B':
				$fileSystem = new IMG_D8B("$root$path$image");
				$fileSystem->sendListing();
				break;
				
			case 'T64':
				$fileSystem = new IMG_T64("$root$path$image");
				$fileSystem->sendListing();
				break;

			case 'TCRT':
				$fileSystem = new IMG_TCRT("$root$path$image");
				$fileSystem->sendListing();
				break;
		}
	}
	else
	{
		switch ( $i_ext )
		{
			case 'D64':
				$fileSystem = new IMG_D64("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
				
			case 'D71':
				$fileSystem = new IMG_D71("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
				
			case 'D81':
				$fileSystem = new IMG_D81("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
				
			case 'D8B':
				$fileSystem = new IMG_D8B("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
				
			case 'T64':
				$fileSystem = new IMG_T64("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
				
			case 'TCRT':
				$fileSystem = new IMG_TCRT("$root$path$image");
				$fileSystem->sendFile( $filename );
				break;
		}
	}
}
else
{
	$filename = file_exists_ci( "$root$path$filename" );
	$f_ext = get_type("$filename");
	//echo $f_ext; exit();
	switch ( $f_ext )
	{
		case 'DIR':
			$fileSystem = new IMG_Native("$disk_name");
			$fileSystem->sendListing();
			break;

		case 'P00':
			$fileSystem = new IMG_P00("$root$path$filename");
			$fileSystem->sendFile();
			break;
			
		default:
			//sendLine( 0, "FILE NOT FOUND", "NFO" );
			//echo "\n"; // Empty line to indicate end of directory
			$fileSystem = new IMG_Native("$disk_name");
			$fileSystem->sendFile("$root$path$filename");
	}
}
?> 
