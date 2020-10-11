<?php
//
// File Formats
//
// https://ist.uwaterloo.ca/~schepers/formats.html
// https://www.infinite-loop.at/Power64/Documentation/Power64-ReadMe/AE-File_Formats.html
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
			$fileSystem = new IMG_Native();
			$fileSystem->sendListing();
			break;
		
		case 'PRG':
			$fileSystem = new IMG_Native();
			$fileSystem->sendFile("$root$path$filename");
			break;

		case 'P00':
			$fileSystem = new IMG_P00("$root$path$filename");
			$fileSystem->sendFile();
			break;
			
		//default:
		//	sendLine( 0, "FILE NOT FOUND", "NFO" );
		//	echo "\n"; // Empty line to indicate end of directory
	}
}
?> 