<?php
require_once("IMG_native.php");

class IMG_TAP extends IMG_Native {
	public $filename;
	public $signature;
	public $version;
	public $dir_max;
	public $dir_count;
	public $header;



	function sendLine($blocks, $line, $type)
	{
		$r = array( 'blocks'=>$blocks, 'line'=>$line, 'type'=>$type );
		echo json_encode($r)."\n";
	}

	function sendHeader()
	{
		global $url;
		global $path;
		
		// Send List HEADER
		sendLine(0, "\x12\"  MEAT LOAF 64  \" 08 2A", "NFO" );
		
		// Send Extra INFO
		sendLine(0, sprintf("\"%-19s\" NFO", "[URL]"), "NFO" );
		sendLine(0, sprintf("\"%-19s\" NFO", $url), "NFO" );
		if (strlen($path) > 1)
		{
			sendLine(0, sprintf("\"%-19s\" NFO", "[PATH]"), "NFO" );
			sendLine(0, sprintf("\"%-19s\" NFO", $path), "NFO" );
		}
		sendLine(0, "\"-------------------\" NFO", "NFO" );	
	}

	function sendListing($exp)
	{
		global $root;
		global $path;
		
		sendHeader();

		$dh = @opendir($root.$path);
		if ($dh) {
			while (($fname = readdir($dh)) !== false) {
				//echo $fname."\r\n";
				if (preg_match($exp, $fname)) {
					$stat = stat("$root$path$fname");
					$type = get_type("$root$path$fname");
					$blocks = intval($stat['size']/256);
					$block_spc = 3;
					if ($blocks > 9) $block_spc--;
					if ($blocks > 99) $block_spc--;
					$line = sprintf("%s%-18s %s", str_repeat(" ", $block_spc), "\"".strtoupper($fname)."\"", $type);
					sendLine( $blocks, $line, $type );
				}
			}
			closedir($dh);
		}
		
		sendFooter();
	}

	function sendFooter()
	{
		sendLine( 65536, "BLOCKS FREE", "NFO" );
		echo "\n"; // Empty line to indicate end of directory	
	}

	function sendFile($filename)
	{
		if(file_exists($filename) == 1){

			//Get file type and set it as Content Type
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			header('Content-Type: ' . finfo_file($finfo, $filename));
			finfo_close($finfo);
			
			$fp = fopen($filename, 'rb');
			
			$signature = fread($fp, 12); 	// Get Signature
			$version   = fread($fp, 1); 	// Tape Version
			$bytes   = fread($fp, 3);		// Future Expansion
			$bytes   = fread($fp, 4);		// Data Length
			$length = ord($bytes[0]) + (ord($bytes[1]) * 0xFF) + (ord($bytes[3]) * 0xFFFF) + (ord($bytes[4]) * 0xFFFFFF);
			
			$bytes = fread($fp, 2);			// Total number of used Directory Entries
			$dir_count = ord($bytes[0]) + (ord($bytes[1]) >> 0xFF);
			$unused    = fread($fp, 2);		// Unused bytes
			$header    = fread($fp, 24);	// Tape container name
			
			
	//		exit();
			//Use Content-Disposition: attachment to specify the filename
			header('Content-Disposition: attachment; filename='.basename($filename));

			//No cache
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');

			//Define file size
			header('Content-Length: ' . $length);

			ob_clean();
			flush();
			fseek($fp, $offset);
			echo $startx;
			fpassthru($fp);
			fclose($fp);
			exit;
		}
	}
}
?>