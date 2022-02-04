<?php

class IMG_Native {
	public $filename;
	public $signature;
	public $version;
	public $dir_max;
	public $dir_count;
	public $header;
	public $disk_name;
	public $disk_id;
	public $dos_type;

	protected $fp;

	function __construct( $header )
	{
		$this->disk_name = $header;
		$this->disk_id = "ML 21";
		$this->dos_type = "99";
	}

	function sendLine($blocks, $line, $type)
	{
		//$line = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $line);
		$line = urlencode($line);
		$r = array( 'blocks'=>$blocks, 'line'=>$line, 'type'=>$type );
		echo json_encode($r)."\n";
	}

	function sendHeader()
	{
		global $url;
		global $path;
		global $archive;
		global $image;
		global $show_nfo;
		
		// Send List HEADER
		$this->sendLine(0, sprintf("\x12\"%-16s\" %s %s", $this->disk_name, $this->disk_id, $this->dos_type), "NFO" );
		
		// Send Extra INFO
		if ( $show_nfo )
		{
			$this->sendLine(0, sprintf("\"%-19s\" NFO", "[URL]"), "NFO" );
			$this->sendLine(0, sprintf("\"%-19s\" NFO", $url), "NFO" );
			if (strlen($path) > 1)
			{
				$this->sendLine(0, sprintf("\"%-19s\" NFO", "[PATH]"), "NFO" );
				$this->sendLine(0, sprintf("\"%-19s\" NFO", $path), "NFO" );
			}
			if (strlen($archive) > 1)
			{
				$this->sendLine(0, sprintf("\"%-19s\" NFO", "[ARCHIVE]"), "NFO" );
				$this->sendLine(0, sprintf("\"%-19s\" NFO", $archive), "NFO" );
			}
			if (strlen($image) > 1)
			{
				$this->sendLine(0, sprintf("\"%-19s\" NFO", "[IMAGE]"), "NFO" );
				$this->sendLine(0, sprintf("\"%-19s\" NFO", $image), "NFO" );
			}
			$this->sendLine(0, "\"-------------------\" NFO", "NFO" );
		}
	}

	function sendListing()
	{
		global $root;
		global $path;
		global $media_root;
		global $block_size;
		
		$this->sendHeader();
		header("ml_media_root: ".$media_root);
		header("ml_media_header: ".$this->disk_name);
		header("ml_media_id: ".$this->disk_id);
		$blocks_free = disk_free_space("$root$path$fname") / $block_size;
		header("ml_media_blocks_free: ".$blocks_free);
		header("ml_media_block_size: ".$block_size);

		$dh = @opendir($root.$path);
		if ($dh) {
			while (($fname = readdir($dh)) !== false) {
				//echo $fname."\r\n";
				if (preg_match('/^((?!\.|\.\.|.*\.php)).*$/i', $fname)) {
					$stat = stat("$root$path$fname");
					$type = get_type("$root$path$fname");
					$dir = is_dir("$root$path$fname");
					//$blocks = intval($stat['size']/256);
					//$block_spc = 3;
					//if ($blocks > 9) $block_spc--;
					//if ($blocks > 99) $block_spc--;
					//if ($blocks > 999) $block_spc--;
					
					//$line = sprintf("%s%-18s %s", str_repeat(" ", $block_spc), "\"".strtoupper($fname)."\"", $type);
					//$this->sendLine( $blocks, $line, $type );
					
					$r = array( 'name'=>$fname, 'size'=>$stat['size'], 'dir'=>$dir );
					echo json_encode($r)."\n";
				}
			}
			closedir($dh);
		}
		
		$this->sendFooter();
	}

	function sendFooter( $blocks = 65535 )
	{
		$this->sendLine( $blocks, "BLOCKS FREE.", "NFO" );
		echo "\n"; // Empty line to indicate end of directory	
	}

	function sendFile( $filename = '' )
	{
		if(file_exists("$filename")){

			//Get file type and set it as Content Type
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			header('Content-Type: ' . finfo_file($finfo, $filename));
			finfo_close($finfo);

			//Use Content-Disposition: attachment to specify the filename
			header('Content-Disposition: attachment; filename='.basename($filename));

			//No cache
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');

			//Define file size
			header('Content-Length: ' . filesize($filename));

			ob_clean();
			flush();
			readfile($filename);
			exit;
		}
	}
}
?>