<?php 

function formatSize( $bytes )
{
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
                return( round( $bytes, 0 ) . " " . $types[$i] ); //change $bytes to 2 to get a decimal reading f ex 3.95GB instead 4GB 
}

function deleteLineInFile($file,$string)
{
	$i=0;$array=array();
	
	$read = fopen($file, "r") or die("can't open the file");
	while(!feof($read)) {
		$array[$i] = fgets($read);	
		++$i;
	}
	fclose($read);
	
	$write = fopen($file, "w") or die("can't open the file");
	foreach($array as $a) {
		if(!strstr($a,$string)) fwrite($write,$a);
	}
	fclose($write);
}

?>