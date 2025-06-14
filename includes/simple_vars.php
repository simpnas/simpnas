<?php
	
	$config_primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");
	$config_hostname = exec("hostname");
	$config_docker_volume = exec("find /volumes/*/docker -name docker | awk -F/ '{print $3}'");
	$config_home_volume = exec("find /volumes/*/users -name users | awk -F/ '{print $3}'");
  $config_ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
	$config_os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");
  if(!empty($config_ad_enabled)){
    $config_netbios_domain = exec("samba-tool domain info 127.0.0.1 | grep Netbios | awk '{print $4}'");
  }

  // APPs
    $apps_array = array(
      array(
      "title" => "Nextcloud",
      "category" => "Groupware",
      "description" => "Groupware, File mangement and sharing platform",
      "website" => "https://nextcloud.com",
      "image" => "nextcloud.png",
      "container_name" => "nextcloud",
      "external_hostname" => "cloud",
      "local_port" => 6443,
      "protocol" => "https://",
      "install" => "#installNextcloudModal",
      "update" => "post.php?update_nextcloud",
      "config" => "",
    ),
    array(
      "title" => "Jellyfin",
      "category" => "Media",
      "description" => "Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc",
      "website" => "https://jellyfin.com",
      "image" => "jellyfin.png",
      "container_name" => "jellyfin",
      "external_hostname" => "jellyfin",
      "local_port" => 8096,
      "protocol" => "http://",
      "install" => "#installJellyfinModal",
      "update" => "post.php?update_jellyfin",
      "config" => "",
    ),
    array(
      "title" => "PhotoPrism",
      "category" => "Media",
      "description" => "Manage Photos.",
      "website" => "https://photoprism.org",
      "image" => "photoprism.png",
      "container_name" => "photoprism",
      "external_hostname" => "photos",
      "local_port" => 2342,
      "protocol" => "http://",
      "install" => "#installPhotoprismModal",
      "update" => "post.php?update_photoprism",
      "config" => "",
    ),
    array(
      "title" => "Nginx Proxy Manager",
      "category" => "Proxy",
      "description" => "Proxy services from the outside in using LetsEncrypt Certs",
      "website" => "https://nginxproxymanager.com",
      "image" => "nginx-proxy-manager.png",
      "container_name" => "nginx-proxy-manager",
      "external_hostname" => "proxy",
      "local_port" => 83,
      "protocol" => "http://",
      "install" => "#installNginxProxyManagerModal",
      "update" => "post.php?update_nginx-proxy-manager",
      "config" => "",
    ),
    array(
      "title" => "Transmission",
      "category" => "Downloads",
      "description" => "Web based BitTorrent Download Client",
      "website" => "https://transmission.org",
      "image" => "transmission.png",
      "container_name" => "transmission",
      "external_hostname" => "transmission",
      "local_port" => 9091,
      "protocol" => "http://",
      "install" => "#installTransmissionModal",
      "update" => "",
      "config" => "configure_transmission.php",
    ),
    array(
      "title" => "Home Assistant",
      "category" => "Smart Home",
      "description" => "Home Automation (Control Lights, switches, smart devices etc)",
      "website" => "https://homeassistant.com",
      "image" => "home-assistant.png",
      "container_name" => "homeassistant",
      "external_hostname" => "homeassistant",
      "local_port" => 8123,
      "protocol" => "http://",
      "install" => "#installHomeAssistantModal",
      "update" => "post.php?update_homeassistant",
      "config" => "",
    ),
	);