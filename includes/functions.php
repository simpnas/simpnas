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
    
    // If no fields are provided, execute the script without arguments (get all data)
    if (empty($fields)) {
        $output = shell_exec("$scriptPath");
    } else {
        // Prepare an array of arguments for the shell script based on requested fields
        $validFields = [
            'volume', 'disk', 'model', 'serial', 'health', 'temp', 'size', 
            'use_percent', 'total_space', 'used_space', 'free_space'
        ];
        
        // Filter valid fields and create the shell command arguments
        $arguments = [];
        foreach ($fields as $field) {
            if (in_array($field, $validFields)) {
                $arguments[] = $field;
            }
        }
        
        // Run the shell script with the relevant arguments
        $command = $scriptPath . ' ' . implode(' ', $arguments);
        $output = shell_exec("$command 2>&1");
    }

    // If the script has no output, return an empty array
    if (!$output) {
        return [];
    }

    // Process the output from the shell script
    $lines = explode("\n", trim($output));
    $volumes = [];

    // Process each line (volume info)
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) >= 1) {
            $entry = [];
            $map = [
                'volume' => 0,
                'disk' => 1,
                'model' => 2,
                'serial' => 3,
                'health' => 4,
                'temp' => 5,
                'size' => 6,
                'total_space' => 7,
                'used_space' => 8,
                'free_space' => 9,
                'use_percent' => 10,
            ];

            // Only return requested fields
            foreach ($fields as $field) {
                if (isset($map[$field])) {
                    $entry[$field] = $parts[$map[$field]] ?? null;
                }
            }

            $volumes[] = $entry;
        }
    }

    return $volumes;
}

?>