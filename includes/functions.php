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

function getDisks(...$fields) {
    $scriptPath = '/simpnas/scripts/list_disks.sh';

    // All supported fields, in fixed order as returned by the script
    $allFields = [
        'name',
        'vendor',
        'serial',
        'size',
        'type',
        'has_smart',
        'health',
        'temp',
        'bad_blocks',
        'power_on',
        'power_cycle'
    ];

    // Default to all fields if none specified
    $requestedFields = empty($fields) ? $allFields : $fields;

    // Validate requested fields
    $validRequested = array_values(array_filter($requestedFields, fn($f) => in_array($f, $allFields)));
    if (empty($validRequested)) return [];

    // Build and run the shell command
    $cmd = escapeshellcmd($scriptPath) . ' ' . implode(' ', array_map('escapeshellarg', $validRequested));
    $output = shell_exec("$cmd 2>&1");

    if (!$output) return [];

    $lines = explode("\n", trim($output));
    $disks = [];

    foreach ($lines as $line) {
        $parts = explode('|', $line);

        // Guard: skip malformed rows
        if (count($parts) < count($allFields)) {
            continue;
        }

        $entry = [];
        foreach ($validRequested as $field) {
            $index = array_search($field, $allFields);
            $entry[$field] = $parts[$index] ?? null;
        }

        $disks[] = $entry;
    }

    return $disks;
}

function getVolumes(...$fields) {
    $scriptPath = '/simpnas/scripts/list_volumes.sh';
    $allFields = [
        'volume', 'disk', 'model', 'serial', 'health', 'temp', 'size',
        'use_percent', 'total_space', 'used_space', 'free_space',
        'partuuid', 'filesystem', 'is_raid', 'is_crypt', 'is_mounted', 'type'
    ];
    $requestedFields = empty($fields) ? $allFields : $fields;
    $validRequested = array_values(array_filter($requestedFields, fn($f) => in_array($f, $allFields)));

    if (empty($validRequested)) return [];

    $command = escapeshellcmd($scriptPath) . ' ' . implode(' ', array_map('escapeshellarg', $validRequested));
    $output = shell_exec("$command 2>&1");

    if (!$output) return [];

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

function getUsers(...$fields) {
    $scriptPath = '/simpnas/scripts/list_users.sh';

    $allFields = [
        'username',
        'groups',
        'home_directory',
        'shell',
        'space_used',
        'comment',
        'user_enabled'
    ];

    $requestedFields = empty($fields) ? $allFields : $fields;

    $validRequested = array_values(array_filter($requestedFields, fn($f) => in_array($f, $allFields)));
    if (empty($validRequested)) {
        return [];
    }

    $cmd = escapeshellcmd($scriptPath) . ' ' . implode(' ', array_map('escapeshellarg', $validRequested));
    $output = shell_exec("$cmd 2>&1");

    if (!$output) {
        return [];
    }

    $lines = explode("\n", trim($output));
    $users = [];

    foreach ($lines as $line) {
        $parts = explode('|', $line);

        // Ensure we have the right number of fields
        if (count($parts) !== count($allFields)) {
            continue;
        }

        // Build user entry from requested fields
        $entry = [];
        foreach ($validRequested as $field) {
            $index = array_search($field, $allFields);
            $value = $parts[$index] ?? null;
            if ($field === 'groups') {
                $entry[$field] = explode(',', $value);
            } else {
                $entry[$field] = $value;
            }
        }

        $users[] = $entry;
    }

    return $users;
}

function truncate($text, $chars) {
    if (strlen($text) <= $chars) {
        return $text;
    }
    $text = $text . " ";
    $text = substr($text, 0, $chars);
    $lastSpacePos = strrpos($text, ' ');
    if ($lastSpacePos !== false) {
        $text = substr($text, 0, $lastSpacePos);
    }
    return $text . "...";
}
