<?php

class MeatloafTools 
{
	public $disk_name;
	public $disk_id;
	public $dos_type;
    public $file_list;

	function listing( $header, $directory )
	{
		$this->disk_name = $header;
		$this->disk_id = "ID";
		$this->dos_type = "99";

        $file_list = get_file_list( $directory, ".*" );
	}

    function cmp($a, $b)
    {
        return strcmp($a["name"], $b["name"]);
    }

    function get_file_list($directory, $filter = ".*")
    {
        $filter = str_replace("?", ".1?", $filter);
        echo $filter."\n";

        $arrFiles = array();
        $iterator = new FilesystemIterator($directory);



        foreach($iterator as $entry) {
            if(preg_match('/'.$filter.'/', $entry))
            {
                if ( $entry->isDir() )
                    $extension = "DIR";
                else
                {
                    $extension = strtoupper($entry->getExtension());
                    if (!$extension)
                        $extension = "PRG";
                }
                
                $arrFiles[] = [ "blocks"=>ceil($entry->getSize() / 256), "name"=>$entry->getFileName(), "extension"=>$extension ];            
            }

            // Sort Array
            usort($arrFiles, "cmp");
        }

        return $arrFiles;
    }

    function create_index( $path, $file_list )
    {
        echo "meatloaf file server";
    }
}


$path = "/Users/jjohnston/Downloads/c64/c64 software/D64/";

$mltools = new MeatloafTools();

$mltools->listing("meatloaf archive", $path);

var_dump($mltools->file_list);