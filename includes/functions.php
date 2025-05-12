<?php 

function formatSize( $bytes ){
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
                return( round( $bytes, 0 ) . " " . $types[$i] ); //change $bytes to 2 to get a decimal reading f ex 3.95GB instead 4GB 
}

function formatSizeWo( $bytes ){
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
                return( round( $bytes, 0 )); //change $bytes to 2 to get a decimal reading f ex 3.95GB instead 4GB 
}

function deleteLineInFile($file,$string){
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

function get_server_memory_usage(){
	
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
	$memory_usage = $mem[2]/$mem[1]*100;

	return $memory_usage;
}

function get_server_cpu_usage(){

	$load = sys_getloadavg();
	return $load[0];

}

function getVolumes(...$fields) {
    $scriptPath = '/simpnas/scripts/list_volumes.sh';

    // Fixed field output order from shell script
    $allFields = [
        'volume', 'disk', 'model', 'serial', 'health', 'temp', 'size',
        'use_percent', 'total_space', 'used_space', 'free_space',
        'partuuid', 'filesystem', 'is_raid', 'is_crypt', 'is_mounted', 'type'
    ];

    // Default to all fields if none are provided
    $requestedFields = empty($fields) ? $allFields : $fields;

    // Filter valid fields
    $validRequested = array_values(array_filter($requestedFields, fn($f) => in_array($f, $allFields)));

    // Exit early if no valid fields
    if (empty($validRequested)) {
        return [];
    }

    // Call the shell script with all fields (in fixed order)
    $command = escapeshellcmd($scriptPath) . ' ' . implode(' ', array_map('escapeshellarg', $allFields));
    $output = shell_exec("$command 2>&1");

    if (!$output) {
        return [];
    }

    // Parse each line
    $lines = explode("\n", trim($output));
    $volumes = [];

    foreach ($lines as $line) {
        $parts = explode('|', $line);
        $entry = [];

        foreach ($validRequested as $field) {
            $index = array_search($field, $allFields);
            $entry[$field] = $parts[$index] ?? null;
        }

        $volumes[] = $entry;
    }

    return $volumes;
}




?>