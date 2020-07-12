<?php
exec("lsblk -o PKNAME,PATH,TYPE | grep /dev/md0 | awk '{print \"/dev/\"$1}'",$array_disk_part_array);
    $disks  = implode(' ', $array_disk_part_array);
    echo $disks;

?>