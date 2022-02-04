<?php
require_once("../meatloaf_file_system.php");

class MeatloafD81 extends MeatloafD64 {
	public $dos_version;
	public $disk_name;
	public $disk_id;
	public $dos_type;
	public $free_blocks;

	function __construct( $image )
	{
		$this->directory_header = Array( 40, 0 );
		$this->directory_list = Array( 40, 3 );
		// BAM Data: Track, Sector, Offset, Start Track, End Track, Byte Count
		$this->block_allocation_map = Array( [40, 1, 0x10, 1, 40, 6], [40, 2, 0x10, 41, 80, 6] );
		
		//echo $image."\r\n";
		if(file_exists($image) == 1)
		{
			$this->fp = fopen($image, 'rb');

			$offset = $this->seekSectorArr( $this->directory_header );
			fseek($this->fp, 0x02 + $offset);
			$this->dos_version = fread($this->fp, 1);
			fseek($this->fp, 0x04 + $offset);
			$this->disk_name = str_replace(chr(0xA0), " ", fread($this->fp, 16));
			fseek($this->fp, 0x16 + $offset);
			$this->disk_id = str_replace(chr(0xA0), " ", fread($this->fp, 2));
			fseek($this->fp, 0x19 + $offset);
			$this->dos_type = str_replace(chr(0xA0), " ", fread($this->fp, 2));
			
			if (strlen($disk_name) == 0 )
				$this->$disk_name = '';

			
/*			echo 0x04 + $offset."\r\n";
			echo $this->dos_version."\r\n";
			echo "Disk Name: [".$this->disk_name."]\r\n";
			echo $this->disk_id."\r\n";
			echo $this->dos_type."\r\n";
			echo ftell($this->fp);
			var_dump( $this->directory_list );
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
		$track--;
		$sector_count = ($track * 40) + $sector;
		
		fseek($this->fp, ($sector_count * 256));
		return ftell($this->fp);
	}

}
?>