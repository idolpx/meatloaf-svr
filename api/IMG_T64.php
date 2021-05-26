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

require_once("IMG_native.php");

class IMG_T64 extends IMG_Native {
	public $signature;
	public $version;
	public $dir_max;
	public $dir_count;
	public $header;
	


	function __construct( $image )
	{
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');
			
			$this->signature	= fread($this->fp, 32); 	// Get Signature
			$bytes   			= fread($this->fp, 2); 	// Tape Version
			$this->version = ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$bytes   			= fread($this->fp, 2);		// Max Directory Entries
			$this->dir_max 	= ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$bytes 				= fread($this->fp, 2);			// Total number of used Directory Entries
			$this->dir_count 	= ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$unused    			= fread($this->fp, 2);		// Unused bytes
			$this->header    	= str_replace(chr(0xA0), " ", fread($this->fp, 24));	// Tape container name
		}
	}
	
    function __destruct() {
		if( $this->fp )
			fclose($this->fp);
    }
	

	function sendListing() 
	{
		$this->sendHeader();
		
		// Read Directory Entries
		for ( $i = 0; $i < $this->dir_count; $i++)
		{		
			$img_type = ord(fread($this->fp, 1));	// C64s filetype (0 = free, 1 = Normal, 3 = Snapshot, 2-255 Reserved)
			$file_type = ord(fread($this->fp, 1));	// 1541 filetype (0x82 = PRG, 0x81 = SEQ, etc.)
			$start_address  = fread($this->fp, 2);	// Start address
			$start = ord($start_address[0]) + (ord($start_address[1]) * 0x100);
			$bytes     = fread($this->fp, 2);	// End address
			$end = ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$unused  = fread($this->fp, 2);	// Unused bytes
			$bytes  = fread($this->fp, 4);	// Offset from beginning where file starts
			$offset = ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000) + (ord($bytes[4]) * 0x10000);
			$unused  = fread($this->fp, 4);	// Unused bytes
			$filename = trim(fread($this->fp, 16)); // C64 filename padded with $20 (spaces)
			$file_size = $end - $start;
			
			//echo ord($file_type); exit();
			$type = "PRG";
			switch ( ord($file_type) )
			{
				case 0x00:
					if ( $img_type > 1 )
						$type = "FRZ";
				
				case 0x80:
					$type = "DEL";
					break;
					
				case 0x81:
					$type = "SEQ";
					break;

				case 0x82:
					$type = "PRG";
					break;
				
				case 0x83:
					$type = "USR";
					break;
					
				case 0x84:
					$type = "REL";
					break;
			}
			
			$blocks = intval($file_size / 256);
			$block_spc = 3;
				if ($blocks > 9) $block_spc--;
				if ($blocks > 99) $block_spc--;
				if ($blocks > 999) $block_spc--;
				
 /*			echo "Start: $start\r\n";
			echo "End: $end\r\n";
			echo "Offset: $offset\r\n";
			echo "File Size: $file_size\r\n";
			echo "Filename: $filename\r\n";
*/
			$line = sprintf("%s%-23s %s", str_repeat(" ", $block_spc), "\"".strtoupper($filename)."\"", $type);
			$this->sendLine( $blocks, $line, $type );
		}
		
		$this->sendFooter();
	}


	function seekFile( $entry )
	{
		// Read Directory Entries
		for ( $i = 0; $i < $this->dir_count; $i++)
		{		
			$ft_c64s = fread($this->fp, 1);	// C64s filetype (0 = free, 1 = Normal, 3 = Snapshot, 2-255 Reserved)
			$file_type = fread($this->fp, 1);	// 1541 filetype (0x82 = PRG, 0x81 = SEQ, etc.)
			$start_address  = fread($this->fp, 2);	// Start address
			$start = ord($start_address[0]) + (ord($start_address[1]) * 0x100);
			$bytes     = fread($this->fp, 2);	// End address
			$end = ord($bytes[0]) + (ord($bytes[1]) * 0x100);
			$unused  = fread($this->fp, 2);	// Unused bytes
			$bytes  = fread($this->fp, 4);	// Offset from beginning where file starts
			$offset = ord($bytes[0]) + (ord($bytes[1]) * 0x100) + (ord($bytes[3]) * 0x1000) + (ord($bytes[4]) * 0x10000);
			$unused  = fread($this->fp, 4);	// Unused bytes
			$filename = trim(fread($this->fp, 16)); // C64 filename padded with $20 (spaces)
			$length = $end - $start - 1;
			
			//echo $i." [".$filename."]\r\n";
			if ($entry == $filename )
			{
				//echo $entry." ".$filename." [".$offset."] [".$length."]"; exit();
				fseek($this->fp, $offset);
				return Array( 'start'=>$start_address, 'length'=>$length );
			}
		}
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
		fpassthru($this->fp);
	}
}
?>