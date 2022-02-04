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
 
require_once("../meatloaf_file_system.php");
require_once("meatloaf_crt_hardware.php");

class MeatloafCRT extends MeatloafFileSystem {
	public $signature;
	public $header_length;
	public $crt_version;
	public $hw_id;
	public $hw_type;
	public $exrom;
	public $game;
	public $filename;
	public $flags;
	public $file_size;
	public $cart;
	

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
			$this->header_length = (ord($bytes[0]) * 0x10000) + (ord($bytes[1]) * 0x1000) + (ord($bytes[2]) * 0x100) + ord($bytes[3]);												  		// Header Length
			$bytes   			= fread($this->fp, 2); 	
			$this->crt_version = ord($bytes[0]).".".ord($bytes[1]);					// Cart Version
			$bytes   			= fread($this->fp, 2);
			$this->hw_id		= (ord($bytes[0]) * 0x100) + ord($bytes[1]);		// Cart HW ID
			$this->hw_type 		= $hw_type_array[$this->hw_id];						// Cart HW Type
			$this->exrom		= ord(fread($this-fp, 1));							// EXROM Line Status
			$this->game			= ord(fread($this-fp, 1));							// GAME Line Status
			$bytes   			= fread($this->fp, 6);								// Reserved
			$this->filename    	= strtoupper(trim(fread($this->fp, 32)));			// Filename
			$this->flags		= fread($this->fp, 2);								// Flags
			
			// Minimum header length is $40
			if ( $this->header_length < 0x40 )
				$this->header_length = 0x40;
			
			$this->cart = selectCart($this->hw_type);

			$this->sendListing = $this->cart->sendListing();
			$this->seekFile = $this->cart->seekFile();
			$this->sendFile = $this->cart->sendFile();

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
			$this->sendLine(0, sprintf("\"%-24s\" NFO", "[HARDWARE]"), "NFO" );
			$this->sendLine(0, sprintf("\"%-24s\" NFO", $this->hw_type), "NFO" );
			$this->sendLine(0, "\"------------------------\" NFO", "NFO" );
		}
	}
}
?>