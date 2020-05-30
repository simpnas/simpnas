<?php 

  session_start();
  
  $config = include("config.php");
  include("simple_vars.php");
  include("functions.php"); 

if(isset($_GET['upgrade_simpnas'])){
  exec("cd /simpnas");
  exec("git pull origin master");
  header("Location: index.php");
}

if(isset($_GET['upgrade_simpnas_overwrite_local_changes'])){
  exec("cd /simpnas");
  
  //git fetch downloads the latest from remote without trying to merge or rebase anything. Then the git reset resets the master branch to what you just fetched. The --hard option changes all the files in your working tree to match the files in origin/master

  exec("git fetch --all");
  exec("git reset --hard origin/master");

  header("Location: index.php");
}

if(isset($_POST['user_add'])){
  $username = $_POST['username'];
  $password = $_POST['password'];

  //Check if user exists
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $users_array);
  exec("awk -F: '$3 < 999 {print $1}' /etc/passwd", $system_users_array);
  
  if(in_array($username, $users_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "User $username already exists!";    
  }elseif(in_array($username, $system_users_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add user $username because the user is a system user!";
  }else{

    if(!file_exists("/$config_mount_target/$config_home_volume/$config_home_dir/")){
      mkdir("/$config_mount_target/$config_home_volume/$config_home_dir/");
    }
   
    exec ("mkdir /$config_mount_target/$config_home_volume/$config_home_dir/$username");
    exec ("chmod -R 700 /$config_mount_target/$config_home_volume/$config_home_dir/$username");  
    exec ("useradd -g users -d /$config_mount_target/$config_home_volume/$config_home_dir/$username $username -s /bin/false -p $password");
    exec ("chown -R $username:users /$config_mount_target/$config_home_volume/$config_home_dir/$username");
    
    $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
    if(empty($ad_enabled)){
      exec ("echo '$password\n$password' | smbpasswd -a $username");
      
    }else{
      exec ("samba-tool user create $username '$password'");
    }
    
    if(isset($_POST['group'])){
    	$group_array = $_POST['group'];
    	foreach($group_array as $group){
      	exec ("adduser $username $group");
    	}
    }

    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "User $username successfully added!";
  }
  header("Location: users.php");
}

if(isset($_POST['user_edit'])){
  $username = $_POST['username'];
  $group_array = implode(",", $_POST['group']);
  
  //$group_count = count($group);
  if(!empty($_POST['password'])){
    $password = $_POST['password'];
    exec ("echo '$password\n$password' | passwd $username");
    exec ("echo '$password\n$password' | smbpasswd $username"); //May not be needed
  }
  if(!empty($group_array)){
    exec ("usermod -G $group_array $username");
  }else{
    exec ("usermod -G users $username");
  }
  
  //exec("systemctl restart smbd");
  //exec("systemctl restart nmbd");

  header("Location: users.php");
}

if(isset($_GET['user_delete'])){
  $username = $_GET['user_delete'];


  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("smbpasswd -x $username");
  }else{
    exec ("samba-tool user delete $username");
  }
  
  exec("deluser --remove-home $username");

  //exec("systemctl restart smbd");
  //exec("systemctl restart nmbd");

  $_SESSION['alert_type'] = "danger";
  $_SESSION['alert_message'] = "User $username Deleted!";
  
  header("Location: users.php");
}

if(isset($_POST['group_add'])){
  $group = $_POST['group'];

  //check if group exists
  exec("awk -F: '$3 > 999 {print $1}' /etc/group", $groups_array);
  exec("awk -F: '$3 < 999 {print $1}' /etc/group", $system_groups_array);
  $docker_groups_array = array("media", "downloads", "video-surveillance", "docker");

  if(in_array($group, $groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Group $group already exists!";
  }elseif(in_array($group, $system_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add group $group because the group is a system group!";
  }elseif(in_array($group, $docker_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add group $group because the group $group is reserved for an App, the following group names are forbiddon media, downloads and video-surveillance!";
  }else{
    exec ("addgroup $group");
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Group $group successfully added!";
  }  
  header("Location: groups.php");
}

if(isset($_POST['group_edit'])){
  $old_group = $_POST['old_group'];
  $group = $_POST['group'];

  //check if group exists
  exec("awk -F: '$3 > 999 {print $1}' /etc/group", $groups_array);
  exec("awk -F: '$3 < 999 {print $1}' /etc/group", $system_groups_array);
  exec("find /$config_mount_target/*/* -maxdepth 0 -type d -group $group -printf '%f\n'",$group_owned_directories_array);
  $docker_groups_array = array("media", "downloads", "video-surveillance", "docker");

  if(in_array($group, $groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not rename group $old_group to $group because group $group already exists!";
  }elseif(in_array($group, $system_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not rename group $old_group to $group because the group is a system group!";
  }elseif(!empty($group_owned_directories_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not rename group $old_group to $group because the group is a currently being used by a File Share, to rename this group assign the file share a different group and try again!";
  }elseif(in_array($group, $docker_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not rename group $old_group to $group because the group $group is reserved for an App, the following group names are forbiddon media, downloads and video-surveillance!";
  }else{
    exec ("groupmod -n $group $old_group");
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Group $old_group renamed to $group successfully!";
  }  
  header("Location: groups.php");
}

if(isset($_GET['group_delete'])){
  $group = $_GET['group_delete'];

  exec("find /$config_mount_target/*/* -maxdepth 0 -type d -group $group -printf '%f\n'",$group_owned_directories_array);
  if(!empty($group_owned_directories_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete group $group as its currently being used by a file share, to delete this group, delete the file share or change the group on the share to another group and try again!";
  }else{

    exec("delgroup $group");

    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
    $_SESSION['alert_type'] = "danger";
    $_SESSION['alert_message'] = "Group $group deleted!";
  }
  header("Location: groups.php");
}

if(isset($_POST['general_edit'])){
  $hostname = $_POST['hostname'];
  $current_hostname = exec("hostname");
  $config['enable_beta'] = $_POST['enable_beta'];

  file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  
  sleep(3);
  
  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");
  
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");
  $new_hostname = exec("hostname");
  header("Location: http://$new_hostname:81/general.php");
}

if(isset($_POST['datetime_update'])){
  $timezone = $_POST['timezone'];
  
  exec("timedatectl set-timezone '$timezone'");
  header("Location: datetime.php");
}

if(isset($_GET['unmount_volume'])){
  $volume = $_GET['unmount_volume'];
  exec ("umount /$config_mount_target/$volume");
  
  
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd"); 
  }

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Volume $volume has been unmounted!";
  header("Location: volumes.php");
}

if(isset($_GET['mount_volume'])){
  $volume = $_GET['mount_volume'];
  exec ("mount /$config_mount_target/$volume");
  
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Mounted volume $volume successfully!";
  header("Location: volumes.php");
}

if(isset($_POST['volume_add'])){
  $name = trim($_POST['name']);
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  
  exec ("ls /$config_mount_target/",$volumes_array);

  if(in_array($name, $volumes_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add volume $name as it already exists!";
  }else{
    exec ("wipefs -a $hdd");
    exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
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
      
      $uuid = exec("blkid -o value --match-tag UUID $hdd_part");

      $myFile = "/etc/fstab";
      $fh = fopen($myFile, 'a') or die("can't open file");
      $stringData = "UUID=$uuid    /$config_mount_target/$name      ext4    rw,relatime,data=ordered 0 2\n";
      fwrite($fh, $stringData);
      fclose($fh);
    }
  }
  header("Location: volumes.php");
}

if(isset($_GET['volume_delete'])){
  $name = $_GET['volume_delete'];
  //check to make sure no shares are linked to the volume
  //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
  //the code to do that here
  $hdd = exec("findmnt -n -o SOURCE --target /$config_mount_target/$name");
  
  exec("ls /$config_mount_target/$name | grep -v lost+found", $directory_list_array);
  if(!empty($directory_list_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete volume $name as there are files shares, please delete the file shares accociated to volume $name and try again!";
  }else{
    exec ("umount -l /$config_mount_target/$name");
    exec ("rm -rf /$config_mount_target/$name");
    exec ("wipefs -a $hdd");
    $uuid = exec("blkid -o value --match-tag UUID $hdd");
    
    deleteLineInFile("/etc/fstab","$uuid");

  }
  
  header("Location: volumes.php");
}

if(isset($_POST['volume_add_raid'])){
  $disks = $_POST['disks'];
  foreach($_POST['disks'] as $disk){     
    $disk = basename(exec("findmnt -n -o SOURCE --target /$config_mount_target/$volume"));


    $name = trim($_POST['name']);
    $hdd = $_POST['disk'];
    $hdd_part = $hdd."1";
    
    exec ("ls /$config_mount_target/",$volumes_array);
  }

  if(in_array($name, $volumes_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add volume $name as it already exists!";
  }else{
    exec ("wipefs -a $hdd");
    exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
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
  }
  header("Location: volumes.php");
}

if(isset($_POST['volume_add_backup'])){
  $name = trim($_POST['name']);
  $name = "backup-$name";
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  
  exec ("ls /$config_mount_target/",$volumes_array);

  if(in_array($name, $volumes_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add volume $name as it already exists!";
  }else{
    exec ("wipefs -a $hdd");
    exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
    exec ("e2label $hdd_part $name");
    exec ("mkdir /$config_mount_target/$name");

    exec ("mkfs.ext4 $hdd_part");
    exec ("mount $hdd_part /$config_mount_target/$name");  
    
    $uuid = exec("blkid -o value --match-tag UUID /dev/$hdd_part");
    $myFile = "/etc/fstab";
    $fh = fopen($myFile, 'a') or die("can't open file");
    $stringData = "$uuid    /$config_mount_target/$name      ext4    rw,relatime,data=ordered 0 2\n";
    fwrite($fh, $stringData);
    fclose($fh);    
  }
  header("Location: volumes.php");
}

if(isset($_POST['share_add'])){
  $volume = $_POST['volume'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  
  //Checks
  exec("ls /etc/samba/shares",$existing_shares_array);
  exec("find /$config_mount_target/*/* -maxdepth 0 -type d -printf '%f\n'",$existing_diectories_array);
  $docker_shares_array = array("media", "downloads", "video-surveillance", "docker", "users");
  $mounted = exec("df | grep $volume");
  
  if(in_array($name, $existing_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "The share with the name $name already exists can not add share!";
  }elseif(in_array($name, $existing_directories_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Directory $name already exists can not add share with the name $name, would you like to share the existing directory instead (Note this will update the permissions to user root with RWX and group to RWX and everyone else to --- or would you like to delete the directory and its contents and create a new directory!";
  }elseif(in_array($name, $docker_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not create the share $name as it shares the same share name as an app. The followng share names are forbiddon media, downloads, video-surveillance, docker and users!";
  }elseif(empty($mounted)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not create the share $name because the volume $volume is not mounted";
  }else{
    mkdir("$share_path");
    chgrp("$share_path", $group);
    chmod("$share_path", 0770);

    $myFile = "/etc/samba/shares/$name";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group, @admins\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
    fwrite($fh, $stringData);
    fclose($fh);

    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'a') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/$name";
    fwrite($fh, $stringData);
    fclose($fh);

    $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
    if(empty($ad_enabled)){
      exec("systemctl restart smbd");
      exec("systemctl restart nmbd");
    }
  }
  header("Location: shares.php");
}

if(isset($_POST['share_edit'])){
  $volume = $_POST['volume'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  $current_volume = $_POST['current_volume'];
  $current_name = $_POST['current_name'];
  $current_description = $_POST['current_description'];
  $current_share_path = "/$config_mount_target/$current_volume/$current_name";
  $current_group = $_POST['current_group'];

  if($name <> $current_name){

    //Name Checks
    exec("ls /etc/samba/shares",$existing_shares_array);
    exec("find /$config_mount_target/*/* -maxdepth 0 -type d -printf '%f\n'",$existing_diectories_array);
    $docker_shares_array = array("media", "downloads", "video-surveillance", "docker", "users");
    
    if(in_array($name, $existing_shares_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "The share with the name $name already exists can not rename share $current_name!";
    }elseif(in_array($name, $existing_directories_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "Directory $name already exists can not rename share $current_name to $name!";
    }elseif(in_array($name, $docker_shares_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "Can not rename share $current_name to $name the share $name shares the same share name as an app. The followng share names are forbiddon media, downloads, video-surveillance, docker and users!";
    }else{
      exec("mv $current_share_path $share_path");
      exec("mv /etc/samba/shares/$current_name /etc/samba/shares/$name");
      deleteLineInFile("/etc/samba/shares.conf","$current_name");
      $myFile = "/etc/samba/shares.conf";
      $fh = fopen($myFile, 'w') or die("not able to write to file");
      $stringData = "\ninclude = /etc/samba/shares/$name";
      fwrite($fh, $stringData);
      fclose($fh);
    }
    
  }elseif($group != $current_group){
    chgrp("$current_share_path", $group);
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "changed group $current_group to $group on share $name successfully!";
  }elseif($volume != $current_volume){
    exec("mv /$config_mount_target/$current_volume/$current_name /$config_mount_target/$volume");
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Moved share $name from $current_volume to $volume successfully!";
  }

  $myFile = "/etc/samba/shares/$name";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }

  header("Location: shares.php");
}

if(isset($_GET['share_delete'])){
  $name = $_GET['share_delete'];

  $docker_shares_array = array("media", "downloads", "video-surveillance", "docker", "users");
  if(in_array($name, $docker_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete the share $name as it shares the same share name as an app thats using it. The followng share names are forbiddon to delete media, downloads, video-surveillance. These can be deleted by deleting the app that is associated with it.";
  }else{

    $path = exec("find /$config_mount_target/*/$name -name $name");

    exec ("rm -rf $path");
    exec ("rm -f /etc/samba/shares/$name");

    deleteLineInFile("/etc/samba/shares.conf","$name");

    $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
    if(empty($ad_enabled)){
      exec("systemctl restart smbd");
      exec("systemctl restart nmbd");
    }
  }
  header("Location: shares.php");
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
  
  header("Location: backups.php");

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
  
  header("Location: backups.php");

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
  
  header("Location: backups.php");

}

if(isset($_GET['wipe_hdd'])){
  $hdd = $_GET['wipe_hdd'];
  $hdd_short_name = basename($hdd);

  exec ("sudo shred -v -n 1 $hdd 2> /tmp/shred-$hdd_short_name-progress&");
  
  header("Location: disks.php");
}

if(isset($_GET['kill_pid'])){
  $pid = $_GET['kill_pid'];

  exec ("sudo kill -9 $pid");
  
  header("Location: ps.php");
}

if(isset($_GET['kill_wipe'])){
  $hdd = $_GET['kill_wipe'];

  exec ("ps axu |grep 'shred -v -n 1 /dev/$hdd' | awk '{print $2}'", $pid);
  
  foreach ($pid as $pids) {
    exec ("sudo kill -9 $pids");
    echo "Killing<br>$pids<br>";
  }

  exec ("sudo rm -rf /tmp/shred-$hdd-progress");
  
  header("Location: disks.php");
}

if(isset($_POST['mail_edit'])){
  $config['smtp_server'] = $_POST['smtp_server'];
  $config['smtp_port'] = $_POST['smtp_port'];
  $config['smtp_username'] = $_POST['smtp_username'];
  $config['smtp_password'] = $_POST['smtp_password'];
  $config['mail_from'] = $_POST['mail_from'];
  $config['mail_to'] = $_POST['mail_to'];

  //file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  sleep(3);

  header("Location: mail_settings.php");
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
    
    $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
    if(empty($ad_enabled)){
      exec("systemctl restart smbd");
      exec("systemctl restart nmbd");
    }

  }

  exec("docker run -d --name jellyfin --net=my-network --restart=unless-stopped -p 8096:8096 -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin:/config -v /$config_mount_target/$volume/media/tvshows:/tvshows -v /$config_mount_target/$volume/media/movies:/movies -v /$config_mount_target/$volume/media/music:/music linuxserver/jellyfin");
  
  header("Location: apps.php");
}

if(isset($_GET['update_jellyfin'])){

  $group_id = exec("getent group media | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/media -name 'media'");

  exec("docker pull linuxserver/jellyfin");
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  
  exec("docker run -d --name jellyfin --net=my-network --restart=unless-stopped -p 8096:8096 -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin:/config -v /$config_mount_target/$volume/media/tvshows:/tvshows -v /$config_mount_target/$volume/media/movies:/movies -v /$config_mount_target/$volume/media/music:/music linuxserver/jellyfin");

  exec("docker image prune");
  
  header("Location: apps.php");
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
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['install_airsonic'])){
  $volume = $_POST['volume'];
  
  if(!file_exists("/$config_mount_target/$config_docker_volume/docker/airsonic")) {
    
    $group_media_exists = exec("cat /etc/group | grep media");
    if(empty($group_media_exists)){
      exec ("addgroup media");
    }

    $group_id = exec("getent group media | cut -d: -f3");

    if(!file_exists("/$config_mount_target/$config_docker_volume/media")) {
      mkdir("/$config_mount_target/$volume/media");
      mkdir("/$config_mount_target/$volume/media/music");
      chgrp("/$config_mount_target/$volume/media","media");
      chgrp("/$config_mount_target/$volume/media/music","media");
      chmod("/$config_mount_target/$volume/media",0770);
      chmod("/$config_mount_target/$volume/media/music",0770);

      $myFile = "/etc/samba/shares/media";
      $fh = fopen($myFile, 'w') or die("not able to write to file");
      $stringData = "[media]\n   comment = Media files used by Airsonic\n   path = /$config_mount_target/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770";
      fwrite($fh, $stringData);
      fclose($fh);

      $myFile = "/etc/samba/shares.conf";
      $fh = fopen($myFile, 'a') or die("not able to write to file");
      $stringData = "\ninclude = /etc/samba/shares/media";
      fwrite($fh, $stringData);
      fclose($fh);
      
      $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
      if(empty($ad_enabled)){
        exec("systemctl restart smbd");
        exec("systemctl restart nmbd");
      }

    }
    
    mkdir("/$config_mount_target/$config_docker_volume/docker/airsonic");
    chgrp("/$config_mount_target/$config_docker_volume/docker/airsonic","media");
    chmod("/$config_mount_target/$config_docker_volume/docker/airsonic",0770);
    
  }

  exec("docker run -d --name airsonic --restart=unless-stopped -p 4040:4040 -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/airsonic:/config -v /$config_mount_target/$volume/media/music:/music linuxserver/airsonic");
  
  header("Location: apps.php");
}

if(isset($_POST['install_lychee'])){
  $volume = $_POST['volume'];
  
  exec ("addgroup photos");
  $group_id = exec("getent group photos | cut -d: -f3");

  mkdir("/$config_mount_target/$volume/photos");
  mkdir("/$config_mount_target/$config_docker_volume/docker/lychee");

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
    
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }     

  exec("docker run -d --name lychee --net=my-network -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee:/config -v /$config_mount_target/$volume/photos:/pictures linuxserver/lychee");
  
  header("Location: apps.php");
}

if(isset($_GET['update_lychee'])){

  $group_id = exec("getent group photos | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/photos -name 'photos'");

  exec("docker pull linuxserver/lychee");
  exec("docker stop lychee");
  exec("docker rm lychee");

  exec("docker run -d --name lychee -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee/config:/config -v $volume_path:/pictures linuxserver/lychee");

  exec("docker image prune");
  
  header("Location: apps.php");
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
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['install_nextcloud'])){

  $password = $_POST['password'];
  $enable_samba_auth = $_POST['enable_samba_auth'];
  $enable_samba_mount = $_POST['enable_samba_mount'];
  $install_apps = $_POST['install_apps'];

  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/appdata");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/data");

  mkdir("/$config_mount_target/$config_docker_volume/docker/mariadb_nextcloud");

  exec("docker run -d --name mariadb_nextcloud --net=my-network -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=nextcloud -e MYSQL_USER=nextcloud -e MYSQL_PASSWORD=password -p 3306:3306 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb_nextcloud:/config linuxserver/mariadb");

  exec("docker run -d --name nextcloud --net=my-network -p 6443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data linuxserver/nextcloud");

  exec("sleep 40");
  
  exec("docker exec nextcloud rm -rf /config/www/nextcloud/core/skeleton");
  if($enable_samba_mount == 1){
    exec("docker exec nextcloud mkdir /config/www/nextcloud/core/skeleton");
    exec("docker exec nextcloud mkdir /config/www/nextcloud/core/skeleton/Shared-Folders");
  }
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ maintenance:install --database='mysql' --database-name='nextcloud' --database-host='mariadb_nextcloud' --database-user='nextcloud' --database-pass='password' --database-table-prefix='' --admin-user='admin' --admin-pass='$password'");

  //Add Trusted Hosts
  $current_hostname = gethostname();
  $primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");
  $docker_gateway = exec("docker network inspect my-network | grep Gateway | awk '{print $2}' | sed 's/\\\"//g'");

  //Add Hostname and Primary IP to trusted_domains list
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set trusted_domains 2 --value=$current_hostname");
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set trusted_domains 3 --value=$primary_ip");

  //Disable Support, usage survey and first run wizard
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:disable support");
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:disable survey_client");
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:disable firstrunwizard");
  exec("docker exec nextcloud rm -rf /config/www/nextcloud/apps/support");
  exec("docker exec nextcloud rm -rf /config/www/nextcloud/apps/survey_client");
  exec("docker exec nextcloud rm -rf /config/www/nextcloud/apps/firstrunwizard");


  if($install_apps == 1){
    //Install Apps
    //Install Calendar
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install calendar");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable calendar");
    //Install Contacts
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install contacts");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable contacts");
    //Install Talk
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install spreed");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable spreed");
    //Install Community Document Server
    //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install documentserver_community");
    //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable documentserver_community");
    //Install OnlyOffice
    //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install onlyoffice");
    //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable onlyoffice");
    //Install Draw.IO
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install drawio");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable drawio");
    //Install Mail
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install mail");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable mail");
  }

  //Set Auth Backend to SAMBA - Install External User Auth Support (For SAMBA Auth)
  if($enable_samba_auth == 1){
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install user_external");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable user_external");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set user_backends 0 arguments 0 --value=$docker_gateway");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set user_backends 0 class --value=OC_User_SMB");
  }
  
  //Fix Setup DB Errors This may be able to removed in the future
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ db:add-missing-indices");
  exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ db:convert-filecache-bigint");

  if($enable_samba_mount == 1){
    //Enable External Files Support for Samba mounts
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable files_external");
    //Add Network Shares
    //Add Users Home folder
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ files_external:create Home 'smb' password::logincredentials -c host=$docker_gateway -c share='users/\$user' -c domain=WORKGROUP");
    //Enable Nextcloud Sharing on Users Home 
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ files_external:option 1 enable_sharing true");
    //Add All Other Shares
    exec("ls /etc/samba/shares", $share_list);
    foreach ($share_list as $share) {
      exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ files_external:create /Shared-Folders/$share 'smb' password::logincredentials -c host=$docker_gateway -c share='$share' -c domain=WORKGROUP");
    }
  }

  header("Location: apps.php");
}

if(isset($_GET['update_nextcloud'])){

  exec("docker pull linuxserver/nextcloud");
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");

  exec("docker pull linuxserver/mariadb_nextcloud");
  exec("docker stop mariadb_nextcloud");
  exec("docker rm mariadb_nextcloud");

  exec("docker run -d --name mariadb_nextcloud --net=my-network -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=nextcloud -e MYSQL_USER=nextcloud -e MYSQL_PASSWORD=password -p 3306:3306 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb_nextcloud:/config linuxserver/mariadb");

  exec("docker run -d --name nextcloud --net=my-network -p 6443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data linuxserver/nextcloud");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_nextcloud'])){
  //stop and delete docker container
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");
  exec("docker stop mariadb_nextcloud");
  exec("docker rm mariadb_nextcloud");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/nextcloud");
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/mariadb_nextcloud");
  
  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");

}

if(isset($_POST['configure_external_access'])){

  $domain = $_POST['domain'];
  $apps_array = $_POST['app'];
  foreach($apps_array as $app){
    if($app == 'nextcloud'){
      $sub_domains_array[] = 'cloud';
    }elseif($app == 'unifi-controller'){
      $sub_domains_array[] = 'unifi';
    }elseif($app == 'gitea'){
      $sub_domains_array[] = 'git';
    }elseif($app == 'dokuwiki'){
      $sub_domains_array[] = 'wiki';
    }elseif($app == 'bitwarden'){
      $sub_domains_array[] = 'vault';
    }else{
      $sub_domains_array[] = $app;
    }
  }

  $sub_domains = implode(',', $sub_domains_array);

  //stop and delete docker container
  exec("docker stop letsencrypt");
  exec("docker rm letsencrypt");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/letsencrypt");

  mkdir("/$config_mount_target/$config_docker_volume/docker/letsencrypt");

  exec("docker run -d --name letsencrypt --net=my-network --cap-add=NET_ADMIN -p 443:443 -p 80:80 --restart=unless-stopped -e URL='$domain' -e SUBDOMAINS='$sub_domains' -e VALIDATION=http -v /$config_mount_target/$config_docker_volume/docker/letsencrypt:/config linuxserver/letsencrypt");

  exec("sleep 1");

  foreach($apps_array as $app){
    exec("cp /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf.sample /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf");

    if($app == 'nextcloud'){
      exec("sed -i 's/server_name $app./server_name cloud./g' /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf");
      exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set trusted_domains 4 --value=cloud.$domain");
    }elseif($app == 'bitwarden'){
      exec("sed -i 's/server_name $app./server_name vault./g' /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf");
    }elseif($app == 'dokuwiki'){
      exec("sed -i 's/server_name $app./server_name wiki./g' /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf");
    }elseif($app == 'gitea'){
      exec("sed -i 's/server_name $app./server_name git./g' /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf");
    }
  }

  //Tell Bots to not index our pages
  exec("sed '/all ssl related config/ i add_header X-Robots-Tag \\\"noindex, nofollow, nosnippet, noarchive\\\";' /$config_mount_target/$config_docker_volume/docker/letsencrypt/nginx/site-confs/default");

  header("Location: configure_external_access.php");
}

if(isset($_GET['uninstall_letsencrypt'])){
  //stop and delete docker container
  exec("docker stop letsencrypt");
  exec("docker rm letsencrypt");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/letsencrypt");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_dokuwiki'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/dokuwiki/");

  exec("docker run -d --name dokuwiki --net=my-network -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki:/config linuxserver/dokuwiki");
  
  header("Location: apps.php");
}

if(isset($_GET['update_dokuwiki'])){

  exec("docker pull linuxserver/dokuwiki");
  exec("docker stop dokuwiki");
  exec("docker rm dokuwiki");

  exec("docker run -d --name dokuwiki --net=my-network -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki/config:/config linuxserver/dokuwiki");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_dokuwiki'])){
  //stop and delete docker container
  exec("docker stop dokuwiki");
  exec("docker rm dokuwiki");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/dokuwiki");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_bookstack'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/bookstack/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/mariadb_bookstack");

  exec("docker run -d --name mariadb_bookstack --net=my-network -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=bookstack -e MYSQL_USER=bookstack -e MYSQL_PASSWORD=password --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb_bookstack:/config linuxserver/mariadb");

  //$mariadb_ip = exec("docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' mariadb_bookstack");  

  exec("docker run -d --name bookstack --net=my-network -p 84:80 --restart=unless-stopped -e DB_HOST=mariadb_bookstack -e DB_USER=bookstack -e DB_PASS=password -e DB_DATABASE=bookstack -v /$config_mount_target/$config_docker_volume/docker/bookstack:/config linuxserver/bookstack");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_bookstack'])){
  //stop and delete docker container
  exec("docker stop bookstack");
  exec("docker rm bookstack");
  exec("docker stop mariadb_bookstack");
  exec("docker rm mariadb_bookstack");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/bookstack");
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/mariadb_bookstack");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_bitwarden'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/bitwarden/");

  exec("docker run -d --name bitwarden --net=my-network -v /$config_mount_target/$config_docker_volume/docker/bitwarden:/data/ -p 88:80 --restart=unless-stopped bitwardenrs/server:latest");
  
  header("Location: apps.php");
}

if(isset($_GET['update_bitwarden'])){

  exec("docker pull bitwardenrs/server:latest");
  exec("docker stop bitwarden");
  exec("docker rm bitwarden");

  exec("docker run -d --name bitwarden -v /$config_mount_target/$config_docker_volume/docker/bitwarden:/data/ -p 88:80 --restart=unless-stopped bitwardenrs/server:latest");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_bitwarden'])){
  //stop and delete docker container
  exec("docker stop bitwarden");
  exec("docker rm bitwarden");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/bitwarden");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_gitea'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/gitea");

  exec("docker run -d --name gitea --net=my-network -v /$config_mount_target/$config_docker_volume/docker/gitea:/data -p 3000:3000 -p 222:22 --restart=unless-stopped gitea/gitea:latest");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_gitea'])){
  //stop and delete docker container
  exec("docker stop gitea");
  exec("docker rm gitea");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/gitea");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_syncthing'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/config");

  exec("docker run -d --name syncthing -p 8384:8384 -p 22000:22000 -p 21027:21027/udp --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/syncthing/config:/config -v /$config_mount_target/$config_docker_volume/$config_home_dir/johnny:/$config_mount_target/johnny -e PGID=100 -e PUID=1000 linuxserver/syncthing");
  header("Location: apps.php");
}

if(isset($_GET['install_home-assistant'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/homeassistant");

  exec("docker run -d --name homeassistant --net=host --net=my-network --restart=unless-stopped -p 8123:8123 -v /$config_mount_target/$config_docker_volume/docker/homeassistant:/config homeassistant/home-assistant:stable");
  header("Location: apps.php");
}

if(isset($_GET['update_home-assistant'])){

  exec("docker pull homeassistant/home-assistant:stable");
  exec("docker stop home-assistant");
  exec("docker rm home-assistant");

  exec("docker run -d --name homeassistant --net=host --restart=unless-stopped -p 8123:8123 -v /$config_mount_target/$config_docker_volume/docker/home-assistant:/config homeassistant/home-assistant:stable");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_home-assistant'])){
  //stop and delete docker container
  exec("docker stop homeassistant");
  exec("docker rm homeassistant");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/homeassistant");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_unifi'])){
  mkdir("/$config_mount_target/$config_docker_volume/docker/unifi-controller/");

  exec("docker run -d --name unifi-controller --net=my-network -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi-controller:/config linuxserver/unifi-controller");
  header("Location: apps.php");
}

if(isset($_GET['update_unifi'])){

  exec("docker pull linuxserver/unifi-controller");
  exec("docker stop unifi");
  exec("docker rm unifi");

  exec("docker run -d --name unifi --net=my-network -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi:/config linuxserver/unifi-controller");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_unifi'])){
  //stop and delete docker container
  exec("docker stop unifi");
  exec("docker rm unifi");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/unifi");
  //redirect back to packages
  header("Location: apps.php");
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
    
    $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
    if(empty($ad_enabled)){
      exec("systemctl restart smbd");
      exec("systemctl restart nmbd");
    }

  }
  
  exec("docker run -d --name unifi-video --net=my-network --cap-add DAC_READ_SEARCH --restart=unless-stopped -p 10001:10001 -p 1935:1935 -p 6666:6666 -p 7080:7080 -p 7442:7442 -p 7443:7443 -p 7444:7444 -p 7445:7445 -p 7446:7446 -p 7447:7447 -e PGID=$group_id -e PUID=0 -e CREATE_TMPFS=no -e DEBUG=1 -v /$config_mount_target/$config_docker_volume/docker/unifi-video:/var/lib/unifi-video -v /$config_mount_target/$volume/video-surveillance:/var/lib/unifi-video/videos --tmpfs /var/cache/unifi-video pducharme/unifi-video-controller");
  
  header("Location: apps.php");

}

if(isset($_GET['update_unifi-video'])){

  $group_id = exec("getent group video-surveillance | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/video-surveillance -name 'video-surveillance'");

  exec("docker pull pducharme/unifi-video-controller");
  exec("docker stop unifi-video");
  exec("docker rm unifi-video");

  exec("docker run -d --name unifi-video --cap-add DAC_READ_SEARCH --restart=unless-stopped -p 10001:10001 -p 1935:1935 -p 6666:6666 -p 7080:7080 -p 7442:7442 -p 7443:7443 -p 7444:7444 -p 7445:7445 -p 7446:7446 -p 7447:7447 -e PGID=$group_id -e PUID=0 -e CREATE_TMPFS=no -e DEBUG=1 -v /$config_mount_target/$config_docker_volume/docker/unifi-video:/var/lib/unifi-video -v /$config_mount_target/$volume/video-surveillance:/var/lib/unifi-video/videos --tmpfs /var/cache/unifi-video pducharme/unifi-video-controller");

  exec("docker image prune");
  
  header("Location: apps.php");
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
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }
  //redirect back to packages
  header("Location: apps.php");
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
    
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }

  if($enable_vpn == 1){
    exec("docker run --cap-add=NET_ADMIN -d --name transmission --restart=unless-stopped -e CREATE_TUN_DEVICE=true -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG='$vpn_server' -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m $dns -v /etc/localtime:/etc/localtime:ro -v /$config_mount_target/$config_docker_volume/docker/transmission:/data/transmission-home -v /$config_mount_target/$volume/downloads/completed:/data/completed -v /$config_mount_target/$volume/downloads/incomplete:/data/incomplete -v /$config_mount_target/$volume/downloads/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn:latest$cpu_arch");
    echo "VPN Docker installed";
  }else{
    exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/transmission:/config -v /$config_mount_target/$volume/downloads/watch:/watch -v /$config_mount_target/$volume/downloads:/downloads -v /$config_mount_target/$volume/downloads/completed:/downloads/complete -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
  }
  
  header("Location: apps.php");
}

if(isset($_POST['transmission_update'])){

  $group_id = exec("getent group download | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/downloads -name 'downloads'");
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

  //exec("docker pull haugene/transmission-openvpn");
  exec("docker stop transmission");
  exec("docker rm transmission");
  exec("docker image prune");

  if($enable_vpn == 1){
    exec("docker run --cap-add=NET_ADMIN -d --name transmission --restart=unless-stopped -e CREATE_TUN_DEVICE=true -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG='$vpn_server' -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m $dns -v /etc/localtime:/etc/localtime:ro -v /$config_mount_target/$config_docker_volume/docker/transmission:/data/transmission-home -v $volume_path/completed:/data/completed -v $volume_path/incomplete:/data/incomplete -v $volume_path/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn:latest$cpu_arch");
    echo "VPN Docker installed";
  }else{
    exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/transmission:/config -v $volume_path/watch:/watch -v $volume_path:/downloads -v $volume_path/completed:/downloads/complete -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
  }

  exec("docker image prune");
  
  header("Location: apps.php");

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
  $ad_enabled = exec("cat /etc/samba/smb.conf | grep 'active directory domain controller'");
  if(empty($ad_enabled)){
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_doublecommander'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/doublecommander");

  exec("docker run --name doublecommander --restart=unless-stopped -e PGID=0 -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/doublecommander:/config -v /mnt/backupvol/bighunk:/data linuxserver/doublecommander");
  header("Location: apps.php");
}

if(isset($_GET['uninstall_doublecommander'])){
  //stop and delete docker container
  exec("docker stop doublecommander");
  exec("docker rm doublecommander");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/doublecommander");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_snipeit'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/snipeit/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/mariadb_snipeit");

  exec("docker run -d --name mariadb_snipeit -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=snipeit -e MYSQL_USER=snipeit -e MYSQL_PASSWORD=password --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb_snipeit:/config linuxserver/mariadb");

  //$mariadb_ip = exec("docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' mariadb_snipeit");  

  exec("docker run -d --name snipeit --net=my-network -p 83:80 --restart=unless-stopped -e DB_HOST=mariadb_snipeit -e MYSQL_USER=snipeit -e MYSQL_PASSWORK=password -e MYSQL_DATABASE=snipeit -v /$config_mount_target/$config_docker_volume/docker/snipeit:/config linuxserver/snipe-it");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_snipeit'])){
  //stop and delete docker container
  exec("docker stop snipeit");
  exec("docker rm snipeit");
  exec("docker stop mariadb_snipeit");
  exec("docker rm mariadb_snipeit");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/snipeit");
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/mariadb_snipeit");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_wireguard'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/wireguard");

  exec("docker run -d --name wireguard --net=my-network --cap-add=NET_ADMIN --cap-add=SYS_MODULE --restart=unless-stopped -e PEERS=1 -v /$config_mount_target/$config_docker_volume/docker/wireguard:/config -v /lib/modules:/lib/modules -p 51820:51820/udp --sysctl='net.ipv4.conf.all.src_valid_mark=1' linuxserver/wireguard");
  header("Location: apps.php");
}

if(isset($_GET['wireguard_qr'])){
  $peer = $_GET['peer'];

  // open the file in a binary mode
  $name = "/$config_mount_target/$config_docker_volume/docker/wireguard/$peer/$peer.png";
  $fp = fopen($name, 'rb');

  // send the right headers
  // - adjust Content-Type as needed (read last 4 chars of file name)
  // -- image/jpeg - jpg
  // -- image/png - png
  // -- etc.
  header("Content-Type: image/png");
  header("Content-Length: " . filesize($name));

  // dump the picture and stop the script
  fpassthru($fp);
  fclose($fp);
  exit;
}

if(isset($_GET['wireguard_config'])){
  $peer = $_GET['peer'];

  // open the file in a binary mode
  $name = "/$config_mount_target/$config_docker_volume/docker/wireguard/$peer/$peer.conf";
  $fp = fopen($name, 'rb');

  // send the right headers
  // - adjust Content-Type as needed (read last 4 chars of file name)
  // -- image/jpeg - jpg
  // -- image/png - png
  // -- etc.
  header("Content-Type: application/conf");
  header("Content-Length: " . filesize($name));
  header('Content-Disposition: attachment; filename="VPN.conf"');

  fpassthru($fp);
  fclose($fp);
  exit;
}

if(isset($_GET['uninstall_wireguard'])){
  //stop and delete docker container
  exec("docker stop wireguard");
  exec("docker rm wireguard");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/wireguard");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_openvpn'])){

  mkdir("/$config_mount_target/$config_docker_volume/docker/openvpn");

  exec("docker run -d --name openvpn --net=my-network --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/openvpn:/config -p 943:943 -p 9443:9443 -p 1194:1194/udp linuxserver/openvpn-as");
  header("Location: apps.php");
}

if(isset($_GET['uninstall_openvpn'])){
  //stop and delete docker container
  exec("docker stop openvpn");
  exec("docker rm openvpn");

  //delete docker config
  exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/openvpn");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['setup'])){
  $volume_name = $_POST['volume_name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  $hostname = $_POST['hostname'];  
  $username = $_POST['username'];
  $password = $_POST['password'];

  $server_type = $_POST['server_type'];
  $ad_domain = $_POST['ad_domain'];
  $ad_netbios_domain = $_POST['ad_netbios_domain'];
  $ad_admin_password = $_POST['ad_admin_password'];
  $ad_dns_forwarders = $_POST['ad_dns_forwarders'];

  $current_hostname = exec("hostname");
  $interface = $_POST['interface'];
  $method = $_POST['method'];
  $address = $_POST['address'];
  $gateway = $_POST['gateway'];
  $dns = $_POST['dns'];
  $collect = $_POST['collect'];

  $os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");

  $config_mount_target = "mnt";
  $config_home_dir = "users";

  //Create config.php file
  
  $file = fopen("config.php", "w");

  //$txt = "<?php\n\n\$config_mount_target = 'mnt';\n\$config_docker_volume = \"$volume_name\";\n\$config_home_volume = \"$volume_name\";\n\$config_home_dir = 'homes';\n\n"

  $data = "<?php\nreturn array(\n'mount_target' => '$config_mount_target',\n'docker_volume' => '$volume_name',\n'home_volume' => '$volume_name',\n'home_dir' => '$config_home_dir',\n'smtp_server' => '',\n'smtp_port' => '',\n'smtp_username' => '',\n'smtp_password' => '',\n'mail_from' => '',\n'mail_to' => '',\n'enable_beta' => '0'\n);\n?>";

  fwrite($file, $data);

  fclose($file);
  
  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");

  exec ("wipefs -a $hdd");
  exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /$config_mount_target/$volume_name");
  exec ("mkfs.ext4 $hdd_part");
  exec ("e2label $hdd_part $volume_name");
  exec ("mount $hdd_part /$config_mount_target/$volume_name");

  exec ("mkdir /$config_mount_target/$volume_name/docker");
  exec ("mkdir /$config_mount_target/$volume_name/users");  

  if($server_type == 'AD'){
    exec("DEBIAN_FRONTEND=noninteractive \apt -y install krb5-user winbind libpam-winbind libnss-winbind smbclient");
    exec("cp /simpnas/conf/krb5.conf /etc");
    exec("sed -i 's/NETBIOS/$ad_netbios_domain/g' /etc/krb5.conf");
    exec("sed -i 's/DOMAIN/$ad_domain/g' /etc/krb5.conf");
    exec("rm /etc/samba/smb.conf");
    exec("samba-tool domain provision --realm=$ad_domain --domain=$ad_netbios_domain --adminpass='$ad_admin_password' --server-role=dc --dns-backend=SAMBA_INTERNAL --use-rfc2307");
    exec("echo 'include = /etc/samba/shares.conf' >> /etc/samba/smb.conf");
    exec("echo domain $ad_domain >> /etc/resolv.conf");
    exec("systemctl stop smbd nmbd winbind");
    exec("systemctl disable smbd nmbd winbind");
    exec("systemctl unmask samba-ad-dc");
    exec("systemctl start samba-ad-dc");
    exec("systemctl enable samba-ad-dc");
  }else{
    $myFile = "/etc/samba/shares/users";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[users]\n   comment = Users Home Folders\n   path = /$config_mount_target/$volume_name/users\n   read only = no\n   force create mode = 0600\n   force directory mode = 0700\n   valid users = @admins";
    fwrite($fh, $stringData);
    fclose($fh);

    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'a') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/users";
    fwrite($fh, $stringData);
    fclose($fh);
    
    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");

  }

  //Check to see if theres already a user added and delete that user
  $existing_username = exec("cat /etc/passwd | grep 1000 | awk -F: '{print $1}'");
  if(!empty($existing_username)){
    exec("deluser --remove-home $existing_username");
  }

  //Create the new user
  exec ("mkdir /$config_mount_target/$volume_name/$config_home_dir/$username");
  exec ("chmod -R 700 /$config_mount_target/$volume_name/$config_home_dir/$username");
  exec ("useradd -g users -d /$config_mount_target/$volume_name/$config_home_dir/$username $username -p $password");
  exec ("chown -R $username:users /$config_mount_target/$volume_name/$config_home_dir/$username");
  exec ("usermod -a -G admins $username");
  exec ("usermod -a -G sudo $username");
  if($server_type == 'AD'){
    exec ("samba-tool user create $username $password");
  }else{
    exec ("echo '$password\n$password' | smbpasswd -a $username");
  }
  
  $uuid = exec("blkid -o value --match-tag UUID $hdd_part");
  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "UUID=$uuid    /$config_mount_target/$volume_name      ext4    rw,relatime,data=ordered 0 2\n";
  fwrite($fh, $stringData);
  fclose($fh);

  if($collect == 1){
    exec("curl https://simpnas.com/collect.php?'collect&machine_id='$(cat /etc/machine-id)''");
  }

  $new_hostname = exec("hostname");

  exec ("mv /etc/network/interfaces /etc/network/interfaces.save");
  exec ("systemctl enable systemd-networkd");

  if($method == 'DHCP'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
    fwrite($fh, $stringData);
    fclose($fh);
    //exec("sleep 1; systemctl restart systemd-networkd > /dev/null &");
    //exec("systemctl restart systemd-networkd");
    //echo "<script>window.location = 'http://$new_hostname/dashboard.php'</script>";
  }
  
  if($method == 'Static'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address\nGateway=$gateway\nDNS=$dns\n";
    fwrite($fh, $stringData);
    fclose($fh);
    $new_ip = substr($address, 0, strpos($address, "/"));
    //exec("systemctl restart systemd-networkd > /dev/null &");
    //echo "<script>window.location = 'http://$new_ip/dashboard.php'</script>";
  }
  header("Location: reboot.php");
}

?>
