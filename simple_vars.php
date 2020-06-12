<?php
	$config_mount_target = $config['mount_target'];
	$config_docker_volume = $config['docker_volume'];
	$config_home_volume = $config['home_volume'];
	$config_home_dir = $config['home_dir'];
	$config_ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
?>