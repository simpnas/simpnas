<?php 

  include("config.php");
  include("functions.php"); 

if(isset($_GET['reboot'])){
  exec("reboot");
  echo "<script>window.location = 'rebooting.php'</script>";
}

if(isset($_GET['shutdown'])){
  exec("halt -p");
  echo "<script>window.location = 'index.php'</script>";
}

if(isset($_GET['upgrade_simpnas'])){
  exec("cd /simpnas");
  exec("git pull origin master");
  echo "<script>window.location = 'index.php'</script>";
}

if(isset($_GET['upgrade_simpnas_overwrite_local_changes'])){
  exec("cd /simpnas");
  
  //git fetch downloads the latest from remote without trying to merge or rebase anything. Then the git reset resets the master branch to what you just fetched. The --hard option changes all the files in your working tree to match the files in origin/master

  exec("git fetch --all");
  exec("git reset --hard origin/master");

  echo "<script>window.location = 'index.php'</script>";
}

if(isset($_POST['user_add'])){
  $username = $_POST['username'];
  $password = $_POST['password'];

  if(!file_exists("/$config_mount_target/$config_home_volume/$config_home_dir/")){
    mkdir("/$config_mount_target/$config_home_volume/$config_home_dir/");
  }
 
  exec ("useradd -g users -m -d /$config_mount_target/$config_home_volume/$config_home_dir/$username $username -p $password");
  exec ("echo '$password\n$password' | smbpasswd -a $username");
  
  if(isset($_POST['group'])){
  	$group_array = $_POST['group'];
  	foreach($group_array as $group){
    	exec ("adduser $username $group");
  	}
  }
  
  exec ("chmod -R 700 /$config_mount_target/$config_home_volume/$config_home_dir/$username");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['user_edit'])){
  $username = $_POST['username'];
  $group_array = implode(",", $_POST['group']);
  
  //$group_count = count($group);
  if(!empty($_POST['password'])){
    $password = $_POST['password'];
    exec ("echo '$password\n$password' | passwd $username");
    exec ("echo '$password\n$password' | smbpasswd $username");
  }
  if(!empty($group_array)){
    exec ("usermod -G $group_array $username");
  }else{
    exec ("usermod -G users $username");
  }
  
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_edit'])){
  $old_group = $_POST['old_group'];
  $group = $_POST['group'];

  exec ("groupmod -n $group $old_group");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_GET['delete_group'])){
  $group = $_GET['delete_group'];

  exec("delgroup $group");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['general_edit'])){
  $hostname = $_POST['hostname'];
  $current_hostname = exec("hostname");
  
  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  $new_hostname = $exec("hostname");
  echo "<script>window.location = 'http://$new_hostname/general.php'</script>";
}


if(isset($_GET['unmount_volume'])){
  $vol = $_GET['unmount_volume'];
  exec ("umount /$config_mount_target/$vol");
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_GET['delete_volume'])){
  $name = $_GET['delete_volume'];
  //check to make sure no shares are linked to the volume
  //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
  //the code to do that here
  $hdd = exec("find $config_mount_target -n -o SOURCE --target /$config_mount_target/$name");
  
  exec ("umount /$config_mount_target/$name");
  exec ("rm -rf /$config_mount_target/$name");
  exec ("wipefs -a $hdd");
  
  deleteLineInFile("/etc/fstab","$hdd");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_GET['mount_hdd'])){
  $hdd = $_GET['mount_hdd'];
  $hdd = $hdd."1";
  $hdd_mount_to = "/$config_mount_target/".basename($hdd);
  if(!(file_exists($hdd_mount_to))){
    exec ("sudo mkdir $hdd_mount_to");
	} 

  exec ("sudo mount $hdd $hdd_mount_to");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_POST['volume_add'])){
  $name = trim($_POST['name']);
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("e2label $hdd_part $name");
  exec ("mkdir /$config_mount_target/$name");
  
  if(!empty($_POST['encrypt'])){
    $password = $_POST['password'];
    exec ("echo -e '$password' | cryptsetup -q luksFormat $hdd_part");
    exec ("echo -e '$password' | cryptsetup open $hdd_part crypt$name");
    exec ("mkfs.ext4 /dev/mapper/crypt$name");    
    exec ("mount /dev/mapper/crypt$name /$config_mount_target/$name");
  }else{
    exec ("mkfs.ext4 $hdd_part");
    exec ("mount $hdd_part /$config_mount_target/$name");  
    
    $myFile = "/etc/fstab";
    $fh = fopen($myFile, 'a') or die("can't open file");
    $stringData = "$hdd_part    /$config_mount_target/$name      ext4    rw,relatime,data=ordered 0 2\n";
    fwrite($fh, $stringData);
    fclose($fh);
  }
  
  echo "<script>window.location = 'volumes.php'</script>";

}

if(isset($_POST['share_add'])){
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  mkdir("$share_path");
  chgrp("$share_path", $group);
  chmod("$share_path", 0770);

  //exec ("mkdir '$share_path'");
  //exec ("chgrp root:$group '$share_path'");
  //exec ("chmod 2777 '$share_path'");
     
     //$myFile = "/etc/samba/smb.conf";
	   //$fh = fopen($myFile, 'a') or die("can't open file");
	   //$stringData = "\n[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770\n\n";
	   //fwrite($fh, $stringData);
	   //fclose($fh);

  $myFile = "/etc/samba/shares/$name";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  $myFile = "/etc/samba/shares.conf";
  $fh = fopen($myFile, 'a') or die("not able to write to file");
  $stringData = "\ninclude = /etc/samba/shares/$name";
  fwrite($fh, $stringData);
  fclose($fh);

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_POST['share_edit'])){
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  $current_volume = $_POST['current_volume'];
  $current_name = $_POST['current_name'];
  $current_description = $_POST['current_description'];
  $current_share_path = "/$config_mount_target/$current_volume/$current_name";
  $current_group = $_POST['current_group'];

  if($group != $current_group){
    chgrp("$current_share_path", $group);
  }
  if($volume != $current_volume){
    exec("mv /$config_mount_target/$current_volume/$current_name /$config_mount_target/$volume");
  }
  if($name != $current_name){
    exec("mv $current_share_path $share_path");
    exec("mv /etc/samba/shares/$current_name /etc/samba/shares/$name");
    deleteLineInFile("/etc/samba/shares.conf","$current_name");
    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/$name";
    fwrite($fh, $stringData);
    fclose($fh);
  }

  $myFile = "/etc/samba/shares/$name";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  echo "<script>window.location = 'shares.php'</script>";

}

if(isset($_GET['share_delete'])){
  $name = $_GET['share_delete'];

  $path = exec("find /$config_mount_target/*/$name -name $name");

  exec ("rm -rf $path");
  exec ("rm -f /etc/samba/shares/$name");

  deleteLineInFile("/etc/samba/shares.conf","$name");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  
  echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_POST['network_add'])){
  $interface = $_POST['interface'];
  $method = $_POST['method'];
  $address = $_POST['address'];
  $gateway = $_POST['gateway'];
  $dns = $_POST['dns'];
  $hostname = gethostname();

  if($method == 'DHCP'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("systemctl restart systemd-networkd");
    echo "<script>window.location = 'http://$hostname/network.php'</script>";
  }
  if($method == 'Static'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address\nGateway=$gateway\nDNS=$dns\n";
    fwrite($fh, $stringData);
    fclose($fh);
    $new_ip = substr($address, 0, strpos($address, "/"));
    exec("systemctl restart systemd-networkd");
    echo "<script>window.location = 'http://$new_ip/network.php'</script>";
  }
  
}

if(isset($_GET['network_delete'])){
  $interface = $_GET['network_delete'];
  
  exec ("rm -f /etc/systemd/network/$interface.network");

  exec("systemctl restart systemd-networkd");

  echo "<script>window.location = 'network.php'</script>";
}

if(isset($_POST['backup_add'])){
  $source = $_POST['source'];
  $destination = $_POST['destination'];
  $occurance = $_POST['occurance'];

  $myFile = "/etc/cron.$occurance/backup--$source--$destination";

  echo $myFile;
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "rsync --verbose --log-file=/var/log/rsync.log --archive /$config_mount_target/$source/ /$config_mount_target/$destination/";
  fwrite($fh, $stringData);
  fclose($fh);

  //exec("rsync --verbose --log-file=/var/log/rsync.log --archive /$config_mount_target/$source/ /$config_mount_target/$destination/");
  
  echo "<script>window.location = 'backups.php'</script>";

}

if(isset($_POST['backup_edit'])){
  $current_backup = $_POST['current_backup'];
  $current_occurance = $_POST['current_occurance'];
  $source = $_POST['source'];
  $destination = $_POST['destination'];
  $occurance = $_POST['occurance'];

  exec ("rm -f /etc/cron.$current_occurance/$current_backup");

  $myFile = "/etc/cron.$occurance/backup--$source--$destination";

  echo $myFile;
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "rsync --verbose --log-file=/var/log/rsync.log --archive /$config_mount_target/$source/ /$config_mount_target/$destination/ --delete";
  fwrite($fh, $stringData);
  fclose($fh);

  //exec("rsync --verbose --log-file=/var/log/rsync.log --archive /$config_mount_target/$source/ /$config_mount_target/$destination/");
  
  echo "<script>window.location = 'backups.php'</script>";

}

if(isset($_GET['backup_delete'])){
  $backup = $_GET['backup_delete'];
  $occurance = $_GET['occurance'];

  exec ("rm -f /etc/cron.$occurance/$backup");
  
  echo "<script>window.location = 'backups.php'</script>";
}

if(isset($_GET['backup_run'])){
  $backup = $_GET['backup_run'];
  $occurance = $_GET['occurance'];

  exec("bash /etc/cron.$occurance/$backup");
  
  echo "<script>window.location = 'backups.php'</script>";

}

if(isset($_GET['wipe_hdd'])){
  $hdd = $_GET['wipe_hdd'];
  $hdd_short_name = basename($hdd);

  exec ("sudo shred -v -n 1 $hdd 2> /tmp/shred-$hdd_short_name-progress&");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['kill_pid'])){
  $pid = $_GET['kill_pid'];

  exec ("sudo kill -9 $pid");
  
  echo "<script>window.location = 'ps.php'</script>";
}

if(isset($_GET['kill_wipe'])){
  $hdd = $_GET['kill_wipe'];

  exec ("ps axu |grep 'shred -v -n 1 /dev/$hdd' | awk '{print $2}'", $pid);
  
  foreach ($pid as $pids) {
    exec ("sudo kill -9 $pids");
    echo "Killing<br>$pids<br>";
  }

  exec ("sudo rm -rf /tmp/shred-$hdd-progress");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['delete_user'])){
	$username = $_GET['delete_user'];

  exec("smbpasswd -x $username");
	exec("deluser --remove-home $username");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
	
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_add'])){
	$group = $_POST['group'];

  exec ("addgroup $group");
  
  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_GET['delete_group'])){
	$group = $_GET['delete_group'];

	exec("delgroup $group");

  echo "<script>window.location = 'groups.php'</script>";
}

//APP SECTION

if(isset($_POST['install_jellyfin'])){
  $volume = $_POST['volume'];
  
  if(!file_exists("/$config_mount_target/$config_docker_volume/jellyfin")) {
    exec ("addgroup media");
    $group_id = exec("getent group media | cut -d: -f3");

    mkdir("/$config_mount_target/$volume/media");
    mkdir("/$config_mount_target/$volume/media/tvshows");
    mkdir("/$config_mount_target/$volume/media/movies");
    mkdir("/$config_mount_target/$volume/media/music");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin/config");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache");

    chgrp("/$config_mount_target/$volume/media","media");
    chgrp("/$config_mount_target/$volume/media/tvshows","media");
    chgrp("/$config_mount_target/$volume/media/movies","media");
    chgrp("/$config_mount_target/$volume/media/music","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin/config","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache","media");
    
    chmod("/$config_mount_target/$volume/media",0770);
    chmod("/$config_mount_target/$volume/media/tvshows",0770);
    chmod("/$config_mount_target/$volume/media/movies",0770);
    chmod("/$config_mount_target/$volume/media/music",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin/config",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache",0770);
    
    $myFile = "/etc/samba/shares/media";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[media]\n   comment = Media files used by Jellyfin\n   path = /$config_mount_target/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770";
    fwrite($fh, $stringData);
    fclose($fh);

    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'a') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/media";
    fwrite($fh, $stringData);
    fclose($fh);
    
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");

  }

  exec("docker run -d --name jellyfin --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin/config:/config -v /$config_mount_target/$volume/media/tvshows:/tvshows -v /$config_mount_target/$volume/media/movies:/movies -v /$config_mount_target/$volume/media/music:/music -v /$config_mount_target/$config_docker_volume/docker/jellyfin/cache:/cache jellyfin/jellyfin");
  
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_jellyfin'])){

  $group_id = exec("getent group media | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/media -name 'media'");

  exec("docker pull jellyfin/jellyfin");
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  
  exec("docker run -d --name jellyfin --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin/config:/config -v $volume_path/tvshows:/tvshows -v $volume_path/movies:/movies -v $volume_path/music:/music -v /$config_mount_target/$config_docker_volume/docker/jellyfin/cache:/cache jellyfin/jellyfin");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_jellyfin'])){
  //stop and delete docker container
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  //delete media group
  exec ("delgroup media");
  //get path to media directory
  $path = exec("find /$config_mount_target/*/media -name media");
  //delete media directory
  exec ("rm -rf $path"); //Delete
  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/jellyfin");
  //delete samba share
  exec ("rm -f /etc/samba/shares/media");
  deleteLineInFile("/etc/samba/shares.conf","media");
  //restart samba
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_POST['install_lychee'])){
  $volume = $_POST['volume'];
  
  exec ("addgroup photos");
  $group_id = exec("getent group photos | cut -d: -f3");

  mkdir("/$config_mount_target/$volume/photos");
  mkdir("/$config_mount_target/$config_docker_volume/docker/lychee");
  mkdir("/$config_mount_target/$config_docker_volume/docker/lychee/config");

  chgrp("/$config_mount_target/$volume/photos","photos");
  
  chmod("/$config_mount_target/$volume/photos",0770);
     
  $myFile = "/etc/samba/shares/photos";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[photos]\n   comment = Photos for Lychee\n   path = /$config_mount_target/$volume/photos\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @photos\n   force group = photos\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  $myFile = "/etc/samba/shares.conf";
  $fh = fopen($myFile, 'a') or die("not able to write to file");
  $stringData = "\ninclude = /etc/samba/shares/photos";
  fwrite($fh, $stringData);
  fclose($fh);
    
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");     

  exec("docker run -d --name lychee -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee/config:/config -v /$config_mount_target/$volume/photos:/pictures linuxserver/lychee");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_lychee'])){

  $group_id = exec("getent group photos | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/photos -name 'photos'");

  exec("docker pull linuxserver/lychee");
  exec("docker stop lychee");
  exec("docker rm lychee");

  exec("docker run -d --name lychee -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee/config:/config -v $volume_path:/pictures linuxserver/lychee");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_lychee'])){
  //stop and delete docker container
  exec("docker stop lychee");
  exec("docker rm lychee");
  //delete media group
  exec ("delgroup photos");
  //get path to media directory
  $path = exec("find /$config_mount_target/*/photos -name photos");
  //delete media directory
  exec ("rm -rf $path"); //Delete
  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/lychee");
  //delete samba share
  exec ("rm -f /etc/samba/shares/photos");
  deleteLineInFile("/etc/samba/shares.conf","photos");
  //restart samba
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_nextcloud'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/appdata");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/data");

  mkdir("/$config_mount_target/$config_docker_volume/docker/mariadb");

  exec("docker run -d --name mariadb -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=nextcloud -e MYSQL_USER=nextcloud -e MYSQL_PASSWORD=password -p 3306:3306 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb:/config linuxserver/mariadb");
     
  exec("docker run -d --name nextcloud -p 443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data -v /$config_mount_target:/$config_mount_target linuxserver/nextcloud");

  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_nextcloud'])){

  exec("docker pull linuxserver/nextcloud");
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");

  exec("docker run -d --name nextcloud -p 443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data -v /$config_mount_target:/$config_mount_target linuxserver/nextcloud");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['uninstall_nextcloud'])){
  //stop and delete docker container
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");
  exec("docker stop mariadb");
  exec("docker rm mariadb");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/nextcloud");
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/mariadb");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_dokuwiki'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/dokuwiki/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/dokuwiki/config");

  exec("docker run -d --name dokuwiki -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki/config:/config linuxserver/dokuwiki");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_dokuwiki'])){

  exec("docker pull linuxserver/dokuwiki");
  exec("docker stop dokuwiki");
  exec("docker rm dokuwiki");

  exec("docker run -d --name dokuwiki -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki/config:/config linuxserver/dokuwiki");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['uninstall_dokuwiki'])){
  //stop and delete docker container
  exec("docker stop dokuwiki");
  exec("docker rm dokuwiki");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/dokuwiki");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_syncthing'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/config");

  exec("docker run -d --name syncthing -p 8384:8384 -p 22000:22000 -p 21027:21027/udp --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/syncthing/config:/config -v /$config_mount_target/$config_docker_volume/$config_home_dir/johnny:/$config_mount_target/johnny -e PGID=100 -e PUID=1000 linuxserver/syncthing");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_home-assistant'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/home-assistant");

  exec("docker run -d --name home-assistant --net=host --restart=unless-stopped -p 8123:8123 -v /$config_mount_target/$config_docker_volume/docker/home-assistant:/config homeassistant/home-assistant:stable");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_home-assistant'])){

  exec("docker pull homeassistant/home-assistant:stable");
  exec("docker stop home-assistant");
  exec("docker rm home-assistant");

  exec("docker run -d --name home-assistant --net=host --restart=unless-stopped -p 8123:8123 -v /$config_mount_target/$config_docker_volume/docker/home-assistant:/config homeassistant/home-assistant:stable");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['uninstall_home-assistant'])){
  //stop and delete docker container
  exec("docker stop home-assistant");
  exec("docker rm home-assistant");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/home-assistant");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_unifi'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/unifi/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/unifi/config");

  exec("docker run -d --name unifi -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi/config:/config linuxserver/unifi-controller > /dev/null &");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_unifi'])){

  exec("docker pull linuxserver/unifi-controller");
  exec("docker stop unifi");
  exec("docker rm unifi");

  exec("docker run -d --name unifi -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi/config:/config linuxserver/unifi-controller");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['uninstall_unifi'])){
  //stop and delete docker container
  exec("docker stop unifi");
  exec("docker rm unifi");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/unifi");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_POST['install_unifi-video'])){
  $volume = $_POST['volume'];
  
  if(!file_exists("/$config_mount_target/$config_docker_volume/unifi-video")) {
    exec ("addgroup video-surveillance");
    $group_id = exec("getent group video-surveillance | cut -d: -f3");

    mkdir("/$config_mount_target/$volume/video-surveillance");
    mkdir("/$config_mount_target/$config_docker_volume/docker/unifi-video");

    chgrp("/$config_mount_target/$volume/video-surveillance","video-surveillance");
    chgrp("/$config_mount_target/$config_docker_volume/docker/unifi-video","video-surveillance");
    
    chmod("/$config_mount_target/$volume/video-surveillance",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/unifi-video",0770);
    
    $myFile = "/etc/samba/shares/video-surveillance";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[video-surveillance]\n   comment = Surveillance Videos for Unifi Video\n   path = /$config_mount_target/$volume/video-surveillance\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @video-surveillance\n   force group = video-surveillance\n   create mask = 0660\n   directory mask = 0770";
    fwrite($fh, $stringData);
    fclose($fh);

    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'a') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/video-surveillance";
    fwrite($fh, $stringData);
    fclose($fh);
    
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");

  }
  
  exec("docker run -d --name unifi-video --cap-add DAC_READ_SEARCH --restart=unless-stopped -p 10001:10001 -p 1935:1935 -p 6666:6666 -p 7080:7080 -p 7442:7442 -p 7443:7443 -p 7444:7444 -p 7445:7445 -p 7446:7446 -p 7447:7447 -e PGID=$group_id -e PUID=0 -e CREATE_TMPFS=no -e DEBUG=1 -v /$config_mount_target/$config_docker_volume/docker/unifi-video:/var/lib/unifi-video -v /$config_mount_target/$volume/video-surveillance:/var/lib/unifi-video/videos --tmpfs /var/cache/unifi-video pducharme/unifi-video-controller");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['update_unifi-video'])){

  $group_id = exec("getent group video-surveillance | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/video-surveillance -name 'video-surveillance'");

  exec("docker pull pducharme/unifi-video-controller");
  exec("docker stop unifi-video");
  exec("docker rm unifi-video");

  exec("docker run -d --name unifi-video --cap-add DAC_READ_SEARCH --restart=unless-stopped -p 10001:10001 -p 1935:1935 -p 6666:6666 -p 7080:7080 -p 7442:7442 -p 7443:7443 -p 7444:7444 -p 7445:7445 -p 7446:7446 -p 7447:7447 -e PGID=$group_id -e PUID=0 -e CREATE_TMPFS=no -e DEBUG=1 -v /$config_mount_target/$config_docker_volume/docker/unifi-video:/var/lib/unifi-video -v /$config_mount_target/$volume/video-surveillance:/var/lib/unifi-video/videos --tmpfs /var/cache/unifi-video pducharme/unifi-video-controller");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_unifi-video'])){
  //stop and delete docker container
  exec("docker stop unifi-video");
  exec("docker rm unifi-video");
  //delete media group
  exec ("delgroup video-surveillance");
  //get path to media directory
  $path = exec("find /$config_mount_target/*/video-surveillance -name video-surveillance");
  //delete media directory
  exec ("rm -rf $path"); //Delete
  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/unifi-video");
  //delete samba share
  exec ("rm -f /etc/samba/shares/video-surveillance");
  deleteLineInFile("/etc/samba/shares.conf","video-surveillance");
  //restart samba
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_POST['install_transmission'])){
  $volume = $_POST['volume'];
  $enable_vpn = $_POST['enable_vpn'];
  if($enable_vpn == 1){
    $vpn_provider = $_POST['vpn_provider'];
    $vpn_server = $_POST['vpn_server'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dns = $_POST['dns'];
    if(!empty($dns)){
      $dns = "--dns $dns";
    }
  }

  $cpu_arch = exec("dpkg --print-architecture");
  if($cpu_arch == "amd64"){
    $cpu_arch = "";
  }else{
    $cpu_arch = "-$cpu_arch";
  }
    
  exec ("addgroup download");
  $group_id = exec("getent group download | cut -d: -f3");

  mkdir("/$config_mount_target/$volume/downloads");
  mkdir("/$config_mount_target/$volume/downloads/completed");
  mkdir("/$config_mount_target/$volume/downloads/incomplete");
  mkdir("/$config_mount_target/$volume/downloads/watch");
  mkdir("/$config_mount_target/$config_docker_volume/docker/transmission");

  chgrp("/$config_mount_target/$volume/downloads","download");
  chgrp("/$config_mount_target/$volume/downloads/watch","download");
  chgrp("/$config_mount_target/$volume/downloads/completed","download");
  chgrp("/$config_mount_target/$volume/downloads/incomplete","download");
  chgrp("/$config_mount_target/$volume/downloads/watch","download");
  chgrp("/$config_mount_target/$config_docker_volume/docker/transmission","download");

  chmod("/$config_mount_target/$volume/downloads",0770);
  chmod("/$config_mount_target/$volume/downloads/completed",0770);
  chmod("/$config_mount_target/$volume/downloads/incomplete",0770);
  chmod("/$config_mount_target/$volume/downloads/watch",0770);
  chmod("/$config_mount_target/$config_docker_volume/docker/transmission",0770);
  
  $myFile = "/etc/samba/shares/downloads";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[downloads]\n   comment = Torrent Downloads used by Transmission\n   path = /$config_mount_target/$volume/downloads\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @download\n   force group = download\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  $myFile = "/etc/samba/shares.conf";
  $fh = fopen($myFile, 'a') or die("not able to write to file");
  $stringData = "\ninclude = /etc/samba/shares/downloads";
  fwrite($fh, $stringData);
  fclose($fh);
    
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  if($enable_vpn == 1){
    exec("docker run --cap-add=NET_ADMIN -d --name transmission --restart=unless-stopped -e CREATE_TUN_DEVICE=true -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG=$config -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m $dns -v /etc/localtime:/etc/localtime:ro -v /$config_mount_target/$config_docker_volume/docker/transmission:/data/transmission-home -v /$config_mount_target/$volume/downloads/completed:/data/completed -v /$config_mount_target/$volume/downloads/incomplete:/data/incomplete -v /$config_mount_target/$volume/downloads/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn:latest$cpu_arch");
    echo "VPN Docker installed";
  }else{
    exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/transmission:/config -v /$config_mount_target/$volume/downloads/watch:/watch -v /$config_mount_target/$volume/downloads:/downloads -v /$config_mount_target/$volume/downloads/completed:/downloads/complete -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
  }
  
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['update_transmission'])){

  $group_id = exec("getent group download | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/downloads -name 'downloads'");

  exec("docker pull haugene/transmission-openvpn");
  exec("docker stop transmission-ovpn");
  exec("docker rm transmission-ovpn");

  exec("docker run --cap-add=NET_ADMIN -d --name transmission-ovpn -e CREATE_TUN_DEVICE=true -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG=$config -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m -v /etc/localtime:/etc/localtime:ro -v /$config_mount_target/$config_docker_volume/docker/transmission-ovpn:/data/transmission-home -v /$config_mount_target/$volume/downloads/completed:/data/completed -v /$config_mount_target/$volume/downloads/incomplete:/data/incomplete -v /$config_mount_target/$volume/downloads/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn");

  exec("docker image prune");
  
  echo "<script>window.location = 'apps.php'</script>";

}

if(isset($_GET['uninstall_transmission'])){
  //stop and delete docker container
  exec("docker stop transmission");
  exec("docker rm transmission");
  //delete group
  exec ("delgroup download");
  //get path to media directory
  $path = exec("find /$config_mount_target/*/downloads -name downloads");
  //delete directory
  exec ("rm -rf $path"); //Delete
  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/transmission");
  //delete samba share
  exec ("rm -f /etc/samba/shares/downloads");
  deleteLineInFile("/etc/samba/shares.conf","downloads");
  //restart samba
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_doublecommander'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/doublecommander");

  exec("docker run --name doublecommander --restart=unless-stopped -e PGID=0 -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/doublecommander:/config -v /mnt/backupvol/bighunk:/data linuxserver/doublecommander");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_doublecommander'])){
  //stop and delete docker container
  exec("docker stop doublecommander");
  exec("docker rm doublecommander");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/doublecommander");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_wireguard'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/wireguard");

  exec("docker run --cap-add=NET_ADMIN --cap-add=SYS_MODULE -d --name wireguard --restart=unless-stopped -e PEERS=1 -v /$config_mount_target/$config_docker_volume/docker/wireguard:/config -v /lib/modules:/lib/modules -p 51820:51820/udp --sysctl='net.ipv4.conf.all.src_valid_mark=1' linuxserver/wireguard");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_wireguard'])){
  //stop and delete docker container
  exec("docker stop wireguard");
  exec("docker rm wireguard");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/wireguard");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['install_openvpn'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/openvpn");
  mkdir("/$config_mount_target/$config_docker_volume/docker/openvpn/config");

  exec("docker run -d --name openvpn --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/openvpn/config:/config -p 943:943 -p 9443:9443 -p 1194:1194/udp linuxserver/openvpn-as");
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_GET['uninstall_openvpn'])){
  //stop and delete docker container
  exec("docker stop openvpn");
  exec("docker rm openvpn");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/openvpn");
  //redirect back to packages
  echo "<script>window.location = 'apps.php'</script>";
}

if(isset($_POST['setup'])){
  $volume_name = $_POST['volume_name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  $hostname = $_POST['hostname'];  
  $username = $_POST['username'];
  $password = $_POST['password'];

  $current_hostname = exec("hostname");
  $interface = $_POST['interface'];
  $method = $_POST['method'];
  $address = $_POST['address'];
  $gateway = $_POST['gateway'];
  $dns = $_POST['dns'];

  $os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");

  //Create config.php file
  
  $myfile = fopen("config.php", "w");

  $txt = "<?php\n\n\$config_os_disk = \"$os_disk\";\n\$config_mount_target = 'mnt';\n\$config_docker_volume = \"$volume_name\";\n\$config_home_volume = \"$volume_name\";\n\$config_home_dir = 'homes';\n\n?>";

  fwrite($myfile, $txt);

  fclose($myfile);

  include("config.php");
  
  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");

  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /$config_mount_target/$volume_name");
  exec ("mkfs.ext4 $hdd_part");
  exec ("e2label $hdd_part $volume_name");
  exec ("mount $hdd_part /$config_mount_target/$volume_name");

  exec ("mkdir /$config_mount_target/$volume_name/docker");
  exec ("mkdir /$config_mount_target/$volume_name/homes");

  exec ("useradd -g users -m -d /$config_mount_target/$config_home_volume/$config_home_dir/$username $username -p $password");
  exec ("echo '$password\n$password' | smbpasswd -a $username");

  exec ("chmod -R 700 /$config_mount_target/$volume_name/$config_home_dir/$username");

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  
  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "$hdd_part    /$config_mount_target/$volume_name      ext4    rw,relatime,data=ordered 0 2\n";
  fwrite($fh, $stringData);
  fclose($fh);

  $new_hostname = exec("hostname");

  exec ("mv /etc/network/interfaces /etc/network/interfaces.save");
  exec ("systemctl enable systemd-networkd");

  if($method == 'DHCP'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("sleep 1; systemctl restart systemd-networkd > /dev/null &");
    echo "<script>window.location = 'http://$new_hostname/dashboard.php'</script>";
  }
  
  if($method == 'Static'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address\nGateway=$gateway\nDNS=$dns\n";
    fwrite($fh, $stringData);
    fclose($fh);
    $new_ip = substr($address, 0, strpos($address, "/"));
    exec("systemctl restart systemd-networkd > /dev/null &");
    echo "<script>window.location = 'http://$new_ip/dashboard.php'</script>";
  }

}

if(isset($_GET['reset'])){
  //Stop Samba
  exec("systemctl stop smbd");
  exec("systemctl stop nmbd");

  //Remove and stop all Dockers and docker images
  exec ("docker stop $(docker ps -aq)");
  exec ("docker rm $(docker ps -aq)");
  exec ("docker rmi $(docker images -q)");
  
  //Remove all created groups
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  foreach ($group_array as $group) {
    exec("delgroup $group");
  }

  //Remove all created users
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  foreach ($username_array as $username) {
    exec("smbpasswd -x $username");
    exec("deluser --remove-home $username");
  }

  //Remove all Volumes and remove from fstab.conf to prevent automounting on boot
  exec("ls /$config_mount_target", $volume_array);
  foreach ($volume_array as $volume) {
    exec("rm -rf /$config_mount_target/$volume/*");
    exec ("umount /$config_mount_target/$volume");
    deleteLineInFile("/etc/fstab","$volume");
  }
  exec("rm -rf /$config_mount_target/*");

  //Wipe Each Disk
  exec("smartctl --scan | awk '{ print $1 '}", $drive_list);
  foreach ($drive_list as $disk) {
    exec("wipefs -a $disk");
  }

  //Remove any backup cron scripts
  exec ("rm -f /etc/cron.*/backup*");

  //Remove Samba conf and replace it with the default
  exec ("rm -f /etc/samba/smb.conf");
  exec ("rm -f /etc/samba/shares.conf");
  exec ("rm -f /etc/samba/shares/*");
  exec ("cp /simpnas/conf/smb.conf /etc/samba/");
  exec ("touch /etc/samba/shares.conf");
  exec ("rm -f /simpnas/config.php");

  $current_hostname = exec("hostname");
  
  exec("sed -i 's/$current_hostname/simpnas/g' /etc/hosts");
  exec("hostnamectl set-hostname simpnas");

  exec("reboot");

  echo "<script>window.location = 'dashboard.php'</script>";
}

?>