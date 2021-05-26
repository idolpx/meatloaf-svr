<?php
// Meatloaf - A Commodore 64/128 multi-device emulator
// https://github.com/idolpx/meatloaf
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
 
require_once("IMG_native.php");
require_once("CRT_HW_TYPE.php");

class IMG_CRT extends IMG_Native {
	public $signature;
	public $header_length;
	public $crt_version;
	public $hw_type;
	public $exrom;
	public $game;
	public $filename;
	public $flags;
	public $file_size;
	

	function __construct( $image )
	{
		global $hw_type_array;
		
		if(file_exists($image) == 1)
		{
			$this->file_size = filesize( $image );
			
			$this->fp = fopen($image, 'rb');
			
			// CARTRIDGE
			$this->signature	= fread($this->fp, 16); 							// Signature
			$bytes   			= fread($this->fp, 4); 	
			$this->header_length = (ord($bytes[0]) * 0x10000) + (ord($bytes[1]) * 0x1000) + (ord($bytes[2]) * 0x100) + ord($bytes[3]);												  // Header Length
			$bytes   			= fread($this->fp, 2); 	
			$this->crt_version = ord($bytes[0]).".".ord($bytes[1]);			// Cart Version
			$bytes   			= fread($this->fp, 2); 	
			$this->hw_type = $hw_type_array[(ord($bytes[0]) * 0x100) + ord($bytes[1])];	// Cart HW Type
			$this->exrom		= ord(fread($this-fp, 1));							// EXROM Line Status
			$this->game			= ord(fread($this-fp, 1));							// GAME Line Status
			$bytes   			= fread($this->fp, 6);								// Reserved
			$this->filename    	= strtoupper(trim(fread($this->fp, 32)));			// Filename
			$this->flags		= fread($this->fp, 2);								// Flags
			
			// Minimum header length is $40
			if ( $this->header_length < 0x40 )
				$this->header_length = 0x40;

/*
			echo "File Size: $this->file_size\r\n";
			echo "Cartridge Signature: $this->signature\r\n";
			echo "Header Length: $this->header_length\r\n";
			echo "Cartridge Version: $this->crt_version\r\n";
			echo "Hardware Type: $this->hw_type\r\n";
			echo "EXROM Line Status: $this->exrom\r\n";
			echo "GAME Line Status: $this->game\r\n";
			echo "Filename: $this->filename\r\n";
			echo "Flags: $this->flags\r\n";
			echo ftell($this->fp);
			echo "\r\n\r\n\r\n";
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
		$this->sendLine(0, sprintf("\x12\"%-24s\" CRT", $this->filename), "NFO" );
		
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
			$this->sendLine(0, sprintf("\"%-24s\" NFO", "[HW TYPE]"), "NFO" );
			$this->sendLine(0, sprintf("\"%-24s\" NFO", $this->hw_type), "NFO" );
			$this->sendLine(0, "\"------------------------\" NFO", "NFO" );
		}
	}

	function sendListing() 
	{
		$this->sendHeader();
		
		// Read Directory Entries
		$chip = 0;
		$next_chip = 0;
		fseek($this->fp, $this->header_length);
		do
		{	
			$signature   		= fread($this->fp, 4);								// CHIP header
			$bytes   			= fread($this->fp, 4); 	
			$packet_length = (ord($bytes[0]) * 0x10000) + (ord($bytes[1]) * 0x1000) + (ord($bytes[2]) * 0x100) + ord($bytes[3]);
			$bytes   			= fread($this->fp, 2); 	
			$chip_type    = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Chip Type
			$bytes   			= fread($this->fp, 2); 	
			$bank_number  = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Bank Number
			$bytes   			= fread($this->fp, 2);
			$load_address		= (ord($bytes[0]) * 0x100) + ord($bytes[1]); 		// Load Address				
			$bytes   			= fread($this->fp, 2); 	
			$file_size   = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Image Size
			
			$filename = "Bank $bank_number, $".dechex($load_address);

			$next_chip = ftell( $this->fp ) + $file_size;

			$blocks = intval($file_size / 256);
			
			$type = "PRG";
			
			$block_spc = 3;
				if ($blocks > 9) $block_spc--;
				if ($blocks > 99) $block_spc--;
				if ($blocks > 999) $block_spc--;
			
/*
			echo "CHIP: $chip\r\n";
			echo "Signature: $signature\r\n";
			echo "Type: $type\r\n";
			echo "Packet Length: $packet_length\r\n";
			echo "Chip Type: $chip_type\r\n";
			echo "Bank Number: $bank_number\r\n";
			echo "Load Address: $load_address\r\n";
			echo "File Size: $file_size\r\n";
			echo "Blocks: $blocks\r\n";
			echo "Next CHIP: ".dechex($next_chip)."\r\n\r\n";
			exit();
*/
			$line = sprintf("%s%-24s%s", str_repeat(" ", $block_spc), "\"".strtoupper($filename)."\"", $type);
			$this->sendLine( $blocks, $line, $type );

			$chip++;
			fseek( $this->fp, $file_size, SEEK_CUR );
		}while ( $next_chip < $this->file_size );
		
		$this->sendFooter();
	}

	function seekFile( $entry )
	{
		// Read Directory Entries
		$chip = 0;
		$next_chip = 0;
		fseek($this->fp, $this->header_length);
		do
		{		
			$signature   		= fread($this->fp, 4);								// CHIP header
			$bytes   			= fread($this->fp, 4); 	
			$packet_length = (ord($bytes[0]) * 0x10000) + (ord($bytes[1]) * 0x1000) + (ord($bytes[2]) * 0x100) + ord($bytes[3]);
			$bytes   			= fread($this->fp, 2); 	
			$chip_type    = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Chip Type
			$bytes   			= fread($this->fp, 2); 	
			$bank_number  = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Bank Number
			$bytes   			= fread($this->fp, 2);
			$start_address   	= strrev($bytes);
			$load_address		= (ord($bytes[0]) * 0x100) + ord($bytes[1]); 		// Load Address
			$bytes   			= fread($this->fp, 2); 	
			$file_size   = (ord($bytes[0]) * 0x100) + ord($bytes[1]);				// Image Size	

//			$bytes   			= fread($this->fp, 2);
//			$start_address   	= fread($this->fp, 2); 								// Start Address

			$filename = "Bank $bank_number, $".dechex($load_address);
			
			$next_chip = ftell( $this->fp ) + $file_size;

			$blocks = intval($file_size / 256);
			
			//echo "$load_address: [".$entry."] [$filename]\r\n"; exit();

			if ( $entry == strtoupper($filename) )
			{
				//echo $entry." ".$filename." [".$data_offset."] [".$file_size."]"; exit();
				return Array( 'start'=>$start_address, 'length'=>$file_size );
			}
			
			$chip++;
			fseek( $this->fp, $file_size, SEEK_CUR );
		} while ( $next_chip < $this->file_size );

		return Array( 'start'=>0, 'length'=>0 );
	}

	function sendFile( $filename = '' )
	{
		//echo $filename; exit();
		$ra = $this->seekFile( $filename );
		
		//echo $ra['start']."\r\n";
		//echo $ra['length']."\r\n";
		//exit();
		
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