<?php
require_once("IMG_native.php");

class IMG_CRT extends IMG_Native {
	public $signature;
	public $version;
	public $data_address;
	public $data_length;
	public $call_address;
	public $filename;
	public $flags;
	public $file_size;
	
	private $fp;

	function __construct( $image )
	{
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');
			
			// CARTRIDGE
			$this->signature	= fread($this->fp, 16); 							// Signature
			$bytes   			= fread($this->fp, 4); 	
			$this->header_size = ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000) + (ord($bytes[4]) * 0x10000);												  // Header Size
			$bytes   			= fread($this->fp, 2); 	
			$this->crt_version = ord($bytes[0]) + (ord($bytes[1]) * 0x100);				// Cart Version
			$bytes   			= fread($this->fp, 2); 	
			$this->hw_version = ord($bytes[0]) + (ord($bytes[1]) * 0x100);				// Cart HW Type
			$this->exrom		= ord(fread($this-fp, 1);							// EXROM Line Status
			$this->game			= ord(fread($this-fp, 1);							// GAME Line Status
			$bytes   			= fread($this->fp, 6);								// Reserved
			$this->filename    	= fread($this->fp, 32);								// Filename
			$this->flags		= fread($this->fp, 2);								// Flags
			
			// CHIP
			
			
			//$bytes				= fread($this->fp, 171);						// Loader Code
			fseek($this->fp, 0xD4 + $offset);
			$bytes				= fread($this->fp, 4);								// File Size
			$this->$file_size = ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000) + (ord($bytes[4]) * 0x10000);

/*			
			echo "Signature: $this->signature\r\n";
			echo "Version: $this->version\r\n";
			echo "Data Address: $this->data_address\r\n";
			echo "Data Length: $this->data_length\r\n";
			echo "Call Address: $this->call_address\r\n";
			echo "Filename: $this->filename\r\n";
			echo "Flags: $this->flags\r\n";
			echo "File Size: $this->file_size\r\n";
			echo ftell($this->fp);
			exit();
*/
		}
	}
	
    function __destruct() {
		if( $this->fp )
			fclose($this->fp);
    }
	

	function sendHeader()
	{
		global $url;
		global $path;
		global $image;
		global $show_nfo;
		
		// Send List HEADER
		$this->sendLine(0, sprintf("\x12\"%-24s\" TCRT", $this->header), "NFO" );
		
		// Send Extra INFO
		if ( $show_nfo )
		{
			$this->sendLine(0, sprintf("\"%-24s\" NFO", "[URL]"), "NFO" );
			$this->sendLine(0, sprintf("\"%-24s\" NFO", $url), "NFO" );
			if (strlen($path) > 1)
			{
				$this->sendLine(0, sprintf("\"%-24s\" NFO", "[PATH]"), "NFO" );
				$this->sendLine(0, sprintf("\"%-24s\" NFO", $path), "NFO" );
			}
			if (strlen($image) > 1)
			{
				$this->sendLine(0, sprintf("\"%-24s\" NFO", "[IMAGE]"), "NFO" );
				$this->sendLine(0, sprintf("\"%-24s\" NFO", $image), "NFO" );
			}
			$this->sendLine(0, "\"------------------------\" NFO", "NFO" );
		}
	}

	function sendListing() 
	{
		$this->sendHeader();
		
		// Read Directory Entries
		fseek($this->fp, 0x107);
		do
		{	
			$filename 		= trim(fread($this->fp, 16));
			$file_type		= ord(fread($this->fp, 1));
			$bytes			= fread($this->fp, 2);
			$data_offset	= (ord($bytes[0]) + (ord($bytes[1]) * 0x100) << 8) + 216;
			$bytes			= fread($this->fp, 3);
			$file_size		= ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000);
			$start_address  = fread($this->fp, 2);
			$unused			= fread($this->fp, 8);

			$blocks = intval($file_size / 256);
			
			$type = "PRG";
			
			$block_spc = 3;
				if ($blocks > 9) $block_spc--;
				if ($blocks > 99) $block_spc--;
				if ($blocks > 999) $block_spc--;
			
			
/*			echo "Filename: $filename\r\n";
			echo "Type: $type\r\n";
			echo "Data Offset: $data_offset\r\n";
			echo "File Size: $file_size\r\n";
			echo "Start Address: $start_address\r\n";
			echo "Length: $blocks\r\n";
			exit();
*/
			if ( $file_type != 0xFF )
			{
				$line = sprintf("%s%-24s%s", str_repeat(" ", $block_spc), "\"".strtoupper($filename)."\"", $type);
				$this->sendLine( $blocks, $line, $type );
			}
		} while ( $file_type != 0xFF );
		
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
		fseek($this->fp, 0x107);
		do
		{		
			$filename 		= trim(fread($this->fp, 16));
			$file_type		= ord(fread($this->fp, 1));
			$bytes			= fread($this->fp, 2);
			$data_offset	= (ord($bytes[0]) + (ord($bytes[1]) * 0x100) << 8) + 216;
			$bytes			= fread($this->fp, 3);
			$file_size		= ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000);
			$start_address  = fread($this->fp, 2);
			$unused			= fread($this->fp, 8);

			$blocks = intval($file_size / 256);
			
			//echo "$i: [".$entry."] [$filename]\r\n";
			if ( $entry == strtoupper($filename) )
			{
				//echo $entry." ".$filename." [".$data_offset."] [".$file_size."]"; exit();
				fseek($this->fp, $data_offset);
				return Array( 'start'=>$start_address, 'length'=>$file_size );
			}
		} while ( $file_type != 0xFF );
		echo "NOPE!"; exit();
		return Array( 'start'=>0, 'length'=>0 );
	}

	function sendFile( $filename = '' )
	{
		//echo $filename; exit();
		$ra = $this->seekFile( $filename );
		
		//Set Content Type
		header('Content-Type: application/octet-stream');

		//Use Content-Disposition: attachment to specify the filename
		header('Content-Disposition: attachment; filename="'.basename($filename).'"');

		//No cache
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		//Define file size
		header('Content-Length: ' . $ra['length']);

		ob_clean();
		flush();
		echo $ra['start'];
		$byte_count = $ra['length'];
		do
		{

			if ( $byte_count < 256 )
			{
				echo fread($this->fp, $byte_count);
				$byte_count = 0;
			}
			else
			{
				echo fread($this->fp, 256);
				$byte_count -= 256;
			}
		} while ( $byte_count > 0 );
	}
}
?>