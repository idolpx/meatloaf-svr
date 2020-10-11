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

require_once("IMG_native.php");

class IMG_P00 extends IMG_Native {
	public $signature;
	public $filename;
	public $offset;
	public $length;
	


	function __construct( $image )
	{
		//echo $image; exit();
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');
			
			$this->signature	= fread($this->fp, 6); 			// $00-06: ASCII string "C64File"
			$unused  			= fread($this->fp, 1); 			//     07: Always $00
			$this->filename		= trim( fread($this->fp, 16) );	//  08-17: Filename in PETASCII, padded with $00
			$unused				= fread($this->fp, 1);			//     18: Always $00
			$unused    			= fread($this->fp, 2);			//     19: REL file record size ($00 if not a REL file)
			$this->offset		= 26;
			$this->length		= filesize($image) - 26;
			
			//echo "[".$image."]"; exit();
		}
	}
	
    function __destruct() {
        fclose($this->fp);
    }
	

	function sendListing() 
	{
		$this->sendHeader();
		
		$type = "PRG";
		$blocks = intval($this->length / 256);
		$block_spc = 3;
			if ($blocks > 9) $block_spc--;
			if ($blocks > 99) $block_spc--;
			if ($blocks > 999) $block_spc--;

		$line = sprintf("%s%-23s %s", str_repeat(" ", $block_spc), "\"".strtoupper($this->filename)."\"", $type);
		$this->sendLine( $blocks, $line, $type );
		
		$this->sendFooter();
	}


	function sendFile( $filename = '' )
	{
		//Set Content Type
		header('Content-Type: application/octet-stream');

		//Use Content-Disposition: attachment to specify the filename
		header('Content-Disposition: attachment; filename='.basename($this->filename));

		//No cache
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		//Define file size
		header('Content-Length: ' . $this->length);

		ob_clean();
		flush();
		fpassthru($this->fp);
	}
}
?>