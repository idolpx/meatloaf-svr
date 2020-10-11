<?php
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