<?php
	
	$primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");
	$config_docker_volume = exec("find /volumes/*/docker -name docker | awk -F/ '{print $3}'");
	$config_home_volume = exec("find /volumes/*/users -name users | awk -F/ '{print $3}'");
	$config_ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
	$config_os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");
	if(!empty($ad_enabled)){
    $config_netbios_domain = exec("samba-tool domain info 127.0.0.1 | grep Netbios | awk '{print $4}'");
  }
  
?>