<?php
	$config_docker_volume = $config['docker_volume'];
	$config_home_volume = $config['home_volume'];
	$config_ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
	$primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");
?>