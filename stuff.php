<?php

//DISKS SHARES AND VOLUMES
//Get list of hard drives and store into an array
exec("smartctl --scan | awk '{ print $1 '}", $drive_list);
//Get list of volumes that were created into an array
exec("ls /$config_mount_target", $volume_list);
//get list of shares into an array
exec("ls /etc/samba/shares", $share_list);


//USERS AND GROUPS
//Get list of users greater than UID 999 into an array (Doesn't grab system users)
exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $username_array);
//Get list of groups greater than GUID 999 into an array removes nogroup as its above 999 (Doesn't grab system groups)
exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);



//Get list of ethernet devices and put them in an array remove virtual ethernet docker and localhost
exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth", $net_devices_array);



//get the disk that a volume is attached to
$disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");

?>
