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

class IMG_D71 extends IMG_D64 {
	public $dos_version;
	public $disk_name;
	public $disk_id;
	public $dos_type;
	public $free_blocks;

	function __construct( $image )
	{
		$this->directory_header = Array( 18, 0 );
		$this->directory_list = Array( 18, 1 );	
		
		//echo $image."\r\n";
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');

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
	
	function seekSector( $track, $sector, $offset = 0x00 )
	{

		if ($track > 35)
		{
			$sector_count = 683;
			$track -= 35;
		}
		
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

}
?>