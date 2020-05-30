<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    //include("side_nav.php");

 //Remove all Volumes and remove from fstab.conf to prevent automounting on boot
    exec("ls /$config_mount_target", $volume_array);
    foreach ($volume_array as $volume) {
        //exec("rm -rf /$config_mount_target/$volume/*");
        //exec ("umount /$config_mount_target/$volume");
        //deleteLineInFile("/etc/fstab","$volume");
    }
    //exec("rm -rf /$config_mount_target/*");

    //Wipe Each Disk
    //unset($volume_array);
    exec("ls /$config_mount_target", $volume_array);
    foreach($volume_array as $volume){
        exec("findmnt -n -o SOURCE --target / | cut -c -8", $has_volume_disk); //adds OS Drive to the array
    }
    exec("smartctl --scan | awk '{print $1}'", $drive_list);
    $not_in_use_disks_array = array_diff($drive_list, $has_volume_disk);

    foreach($not_in_use_disks_array as $disk){
        echo "$disk<br>";
    }

?>

<?php include("footer.php"); ?>