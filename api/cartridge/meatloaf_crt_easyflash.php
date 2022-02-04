<?php
/*
 *  Meat Loaf Server
 *  ---------------
 *
 *  Copyright (C) 2020, Jaime Johnston <jaime@idolpx.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */
 

class MeatloafCRTEasyFlash {
	
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