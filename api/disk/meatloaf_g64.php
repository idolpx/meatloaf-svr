<?php
require_once("../meatloaf_file_system.php");

class MeatloafG64 extends MeatloafFileSystem {
	public $dos_version;
	public $disk_name;
	public $disk_id;
	public $dos_type;
	public $free_blocks;
	
	private $fp;

	private $directory_header = Array( 18, 0 );
	private $directory_list = Array( 18, 1 );

	function __construct( $image )
	{
		//echo $image."\r\n";
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');

			// Bytes: $0000-0007: File signature "GCR-1541"
			$this->signature = fread($this->fp, 8);	// 
			//			 0008: G64 version (presently only $00 defined)
			$this->version = fread($this->fp, 1);
			//			 0009: Number of tracks in image (usually $54, decimal 84)
			$this->track_count = fread($this->fp, 1);
			//		000A-000B: Maximum track size in bytes in LO/HI format
			$this->track_max_size = fread($this->fp, 2);


			$offset = $this->seekSectorArr( $this->directory_header );
			fseek($this->fp, 0x02 + $offset);
			$this->dos_version = fread($this->fp, 1);
			fseek($this->fp, 0x90 + $offset);
			$this->disk_name = str_replace(chr(0xA0), " ", fread($this->fp, 16));
			fseek($this->fp, 0xA2 + $offset);
			$this->disk_id = str_replace(chr(0xA0), " ", fread($this->fp, 2));
			fseek($this->fp, 0xA5 + $offset);
			$this->dos_type = str_replace(chr(0xA0), " ", fread($this->fp, 2));
			
			
			if (strlen($disk_name) == 0 )
				$this->$disk_name = '';
			
			
/*			echo 0x04 + $offset."\r\n";
			echo $this->dos_version."\r\n";
			echo "Disk Name: [".$this->disk_name."]\r\n";
			echo $this->disk_id."\r\n";
			echo $this->dos_type."\r\n";
			echo ftell($this->fp);
			exit();
*/
		}
	}
	
    function __destruct() {
		if( $this->fp )
			fclose($this->fp);
    }
	

	function seekSectorArr( $ts_array, $offset = 0x00 )
	{
		return $this->seekSector( $ts_array[0], $ts_array[1], $offset );
	}

	function seekSector( $track, $sector, $offset = 0x00 )
	{
		$track--;
		$sector_count = 0;

		switch ( true )
		{
			case ( $track < 17 ):
				$sector_count += ($track * 21) + $sector;
				break;

			case ($track < 24):
				$sector_count = 357;
				$sector_count += (($track - 17) * 19) + $sector;
				break;

			case ($track < 30):
				$sector_count = 490;
				$sector_count += (($track - 24) * 18) + $sector;
				break;

			case ($track > 29):
				$sector_count = 598;
				$sector_count += (($track - 30) * 17) + $sector;
				break;
		}
		
		fseek($this->fp, ($sector_count * 256));
		return ftell($this->fp);
	}

	function sendHeader()
	{
		global $url;
		global $path;
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
		global $show_hidden;
		
		$this->sendHeader();
		
		// Read Directory Entries
		$offset = $this->seekSectorArr( $this->directory_list );
		do
		{		
			$next_track = ord(fread($this->fp, 1));
			$next_sector = ord(fread($this->fp, 1));
			$file_type  = ord(fread($this->fp, 1));
			$start_track = ord(fread($this->fp, 1));
			$start_sector = ord(fread($this->fp, 1));
			$filename = str_replace(chr(0xA0), " ", fread($this->fp, 16));
			$rel_track = fread($this->fp, 1);
			$rel_sector = fread($this->fp, 1);
			$rel_length = fread($this->fp, 1);
			$unused = fread($this->fp, 6);
			$bytes = fread($this->fp, 2);
			$blocks = ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			
			//echo ord($file_type); exit();
			$hide = false;
			switch ( $file_type & 0b00000111 )
			{
				case 0x00:
					$type = "DEL";
					$hide = true;
					break;
					
				case 0x01:
					$type = "SEQ";
					break;

				case 0x02:
					$type = "PRG";
					break;
				
				case 0x03:
					$type = "USR";
					break;
					
				case 0x04:
					$type = "REL";
					break;
					
				case 0x05:
					$type = "CBM"; // Parition or sub-directory
					$hide = true;
					break;
			}
			switch ( $file_type & 0b11000000 )
			{
				case 0xC0:			// Bit 6: Locked flag (Set produces ">" locked files)
					$type .= "<";
					$hide = false;
					break;
					
				case 0x00:
					$type .= "*";	// Bit 7: Closed flag  (Not  set  produces  "*", or "splat" files)
					$hide = true;
					break;					
			}
			
			
			$block_spc = 3;
				if ($blocks > 9) $block_spc--;
				if ($blocks > 99) $block_spc--;
				if ($blocks > 999) $block_spc--;
			
			
/*			echo "Next Track: $next_sector\r\n";
			echo "Track Sector: $start_track/$start_sector\r\n";
			echo "Type: $file_type\r\n";
			echo "Length: $blocks\r\n";
			echo "Filename: $filename\r\n";
			exit();
*/
			if ( $file_type > 0 )
			{
				if ( !$hide || $show_hidden)
				{
					$line = sprintf("%s%-19s%s", str_repeat(" ", $block_spc), "\"".strtoupper($filename)."\"", $type);
					$this->sendLine( $blocks, $line, $type );
				}
			}
		} while ( $file_type > 0 );
		
		$this->sendFooter();
	}

	function sendFooter()
	{
		$this->sendLine( 65535, "BLOCKS FREE.", "NFO" );
		echo "\n"; // Empty line to indicate end of directory	
	}

	function seekFile( $entry )
	{
		// Read Directory Entries
		$offset = $this->seekSectorArr( $this->directory_list );
		do
		{		
			$next_track = ord(fread($this->fp, 1));
			$next_sector = ord(fread($this->fp, 1));
			$file_type  = ord(fread($this->fp, 1));
			$start_track = ord(fread($this->fp, 1));
			$start_sector = ord(fread($this->fp, 1));
			$filename = str_replace(chr(0xA0), " ", fread($this->fp, 16));
			$rel_track = fread($this->fp, 1);
			$rel_sector = fread($this->fp, 1);
			$rel_length = fread($this->fp, 1);
			$unused = fread($this->fp, 6);
			$bytes = fread($this->fp, 2);
			$blocks = ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$length = $blocks * 254;
			
			
			if ($file_type & 0b00000111 && $entry == "*")
			{
				$entry = $filename;
			}
			
			//echo $i." [$filename] [$entry]\r\n"; exit();
			if ( $entry == trim($filename ) )
			{
				//echo $entry." ".$filename." [".$offset."] [".$length."]"; exit();
				fseek($this->fp, $offset);
				return Array( 'filename'=>$filename, 'track'=>$start_track, 'sector'=>$start_sector, 'length'=>$length );
			}
		} while ( $file_type > 0 );
		
		return Array( 'track'=>0, 'sector'=>0, 'length'=>0 );
	}

	function sendFile( $filename = '' )
	{
		$ra = $this->seekFile( $filename );
		$this->seekSector( $ra['track'], $ra['sector']);
		
		//Set Content Type
		header('Content-Type: application/octet-stream');

		//Use Content-Disposition: attachment to specify the filename
		header('Content-Disposition: attachment; filename="'.$ra['filename'].'"');

		//No cache
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		//Define file size
		header('Content-Length: ' . $ra['length']);

		ob_clean();
		flush();
		$last_block = false;
		do
		{
			$next_track = ord(fread($this->fp, 1));
			$next_sector = ord(fread($this->fp, 1));
			
			if ($next_track == 0)
			{
				echo fread($this->fp, $next_sector);
				$last_block = true;
			}
			else
			{
				echo fread($this->fp, 254);
				$this->seekSector( $next_track, $next_sector);
			}
		} while ( !$last_block );
	}
}
?>