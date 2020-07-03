<?php

//Post Upgrade Tasks

//Install BTRFS to enable support for the filesystem
$btrfs_installed = exec("apt list --installed btrfs-progs | grep installed");
if(empty($btrf_installed)){
	exec("apt install btrfs-progs -y");
}

?>