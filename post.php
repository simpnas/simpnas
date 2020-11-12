<?php 

session_start();

$config = include("config.php");
include("simple_vars.php");
include("functions.php");

if(isset($_GET['upgrade_simpnas'])){
  exec("cd /simpnas");
  exec("git pull origin master");
  include("post_upgrade.php");
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
  $comment = escapeshellarg($_POST['comment']);

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

    if(!file_exists("/volumes/$config_home_volume/users/")){
      mkdir("/volumes/$config_home_volume/users/");
    }
    exec ("mkdir /volumes/$config_home_volume/users/$username");
    exec ("chmod -R 700 /volumes/$config_home_volume/users/$username");  
    
    exec ("useradd -g users -d /volumes/$config_home_volume/users/$username $username -c $comment -s /bin/false");
    exec ("echo '$password\n$password' | passwd $username");
    exec ("echo '$password\n$password' | smbpasswd -a $username");
    
    //Create the user under file browser
    exec("systemctl stop filebrowser");
    exec ("filebrowser -d /usr/local/etc/filebrowser.db users add $username $password --scope /volumes/$config_home_volume/users/$username");
    exec("systemctl start filebrowser");
  
    exec ("chown -R $username /volumes/$config_home_volume/users/$username");
    
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
  $comment = escapeshellarg($_POST['comment']);
  $group_array = implode(",", $_POST['group']);

  //$group_count = count($group);
  if(!empty($_POST['password'])){
    $password = $_POST['password'];
    
    exec ("echo '$password\n$password' | passwd $username");
    exec ("echo '$password\n$password' | smbpasswd $username"); //May not be needed
    //Modify user password under file browser
    exec("systemctl stop filebrowser");
    exec("filebrowser -d /usr/local/etc/filebrowser.db users update $username -p $password");
    exec("systemctl start filebrowser");
    
  }
  if(!empty($group_array)){
    exec ("usermod -G $group_array $username");
  }else{
    exec ("usermod -G users $username");
  }

  exec("usermod -c $comment $username");
  
  header("Location: users.php");
}

if(isset($_GET['user_delete'])){
  $username = $_GET['user_delete'];

  exec("smbpasswd -x $username");
  
  exec("deluser --remove-home $username");
  //Delete the user under file browser
  exec("systemctl stop filebrowser");
  exec("filebrowser -d /usr/local/etc/filebrowser.db users rm $username");
  exec("systemctl start filebrowser");

  $_SESSION['alert_type'] = "danger";
  $_SESSION['alert_message'] = "User $username removed and all their data in their home directory has been Deleted!";
  
  header("Location: users.php");
}

if(isset($_GET['disable_user'])){
  $username = $_GET['disable_user'];

  exec("usermod -L $username");
  exec("smbpasswd -d $username");
  
  $_SESSION['alert_type'] = "warning";
  $_SESSION['alert_message'] = "User $username Disabled!";
  
  header("Location: users.php");
}

if(isset($_GET['enable_user'])){
  $username = $_GET['enable_user'];

  exec("usermod -U $username");
  exec("smbpasswd -e $username");

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "User $username Enabled!";
  
  header("Location: users.php");
}

if(isset($_POST['group_add'])){
  $group = $_POST['group'];

  //check if group exists
  exec("awk -F: '$3 > 999 {print $1}' /etc/group", $groups_array);
  exec("awk -F: '$3 < 999 {print $1}' /etc/group", $system_groups_array);
  $docker_groups_array = array("media", "downloads", "docker");

  if(in_array($group, $groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Group $group already exists!";
  }elseif(in_array($group, $system_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add group $group because the group is a system group!";
  }elseif(in_array($group, $docker_groups_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add group $group because the group $group is reserved for an App, the following group names are forbiddon media and downloads!";
  }else{
   
    exec("addgroup $group");
    
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
  exec("find /volumes/*/* -maxdepth 0 -type d -group $group -printf '%f\n'",$group_owned_directories_array);
  $docker_groups_array = array("media", "downloads", "docker");

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
    $_SESSION['alert_message'] = "Can not rename group $old_group to $group because the group $group is reserved for an App, the following group names are forbiddon media and downloads!";
  }else{ 
    exec ("groupmod -n $group $old_group");
    
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Group $old_group renamed to $group successfully!";
  }  
  header("Location: groups.php");
}

if(isset($_GET['group_delete'])){
  $group = $_GET['group_delete'];

  exec("find /volumes/*/* -maxdepth 0 -type d -group $group -printf '%f\n'",$group_owned_directories_array);
  if(!empty($group_owned_directories_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete group $group as its currently being used by a file share, to delete this group, delete the file share or change the group on the share to another group and try again!";
  }else{

    exec("delgroup $group");
    
    $_SESSION['alert_type'] = "danger";
    $_SESSION['alert_message'] = "Group $group deleted!";
  }
  header("Location: groups.php");
}

if(isset($_POST['settings_hostname'])){
  $hostname = $_POST['hostname'];
  $current_hostname = exec("hostname");

  file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  
  sleep(3);
  
  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");
  
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Updated hostname successfully!";

  header("Location: http://$config_primary_ip:81/network.php");
}

if(isset($_POST['datetime_update'])){
  $timezone = $_POST['timezone'];
  
  exec("timedatectl set-timezone '$timezone'");
  header("Location: datetime.php");
}

if(isset($_GET['unmount_volume'])){
  $volume = $_GET['unmount_volume'];
  exec ("umount /volumes/$volume");
  
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd"); 

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Volume $volume has been unmounted!";
  header("Location: volumes.php");
}

if(isset($_GET['mount_volume'])){
  $volume = $_GET['mount_volume'];
  exec ("mount /volumes/$volume");
  
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Mounted volume $volume successfully!";
  header("Location: volumes.php");
}

if(isset($_POST['unlock_volume'])){
  $disk = $_POST['disk'];
  $volume = $_POST['volume'];
  $password = $_POST['password'];

  exec("echo $password | cryptsetup luksOpen /dev/disk/by-uuid/$disk $volume");
  $crypt_status = exec("cryptsetup status $volume | grep inactive");
  if(empty($crypt_status)){
    exec ("mount /dev/mapper/$volume /volumes/$volume");
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Unlocked Encrypted volume $volume successfully!";
  }else{
    $_SESSION['alert_type'] = "danger";
    $_SESSION['alert_message'] = "Wrong Secret Key Given!";
  } 
  
  header("Location: volumes.php");

}

if(isset($_GET['lock_volume'])){
  $volume = $_GET['lock_volume'];

  exec("umount -l /dev/mapper/$volume");
  exec("cryptsetup close $volume");
    
  $_SESSION['alert_type'] = "info";
  $_SESSION['alert_message'] = "Volume $volume Encrypted successfully!";
  header("Location: volumes.php");
}

if(isset($_POST['volume_add'])){
  $name = trim($_POST['name']);
  $disk = $_POST['disk'];
  
  exec ("ls /volumes/",$volumes_array);

  if(in_array($name, $volumes_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add volume $name as it already exists!";
  }else{
    exec ("wipefs -a /dev/$disk");
    exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
    $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print $2}'");
    //WIPE out any superblocks
    exec ("mdadm --zero-superblock /dev/$diskpart");
    exec ("e2label /dev/$diskpart $name");
    exec ("mkdir /volumes/$name");
    
    if(!empty($_POST['encrypt'])){
      $password = $_POST['password'];
      exec ("echo $password | cryptsetup -q luksFormat /dev/$diskpart");
      exec ("echo $password | cryptsetup open /dev/$diskpart $name");
      exec ("mkfs.ext4 -F /dev/mapper/$name");
      $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");
      exec("echo $uuid > /volumes/$name/.uuid_map");    
      exec ("mount /dev/mapper/$name /volumes/$name");
    }else{
      exec ("mkfs.ext4 -F /dev/$diskpart");
      exec ("mount /dev/$diskpart /volumes/$name");  
      
      $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");

      $myFile = "/etc/fstab";
      $fh = fopen($myFile, 'a') or die("can't open file");
      $stringData = "UUID=$uuid /volumes/$name ext4 defaults 0 1\n";
      fwrite($fh, $stringData);
      fclose($fh);
    }
  }
  header("Location: volumes.php");
}

if(isset($_POST['volume_add_raid'])){
  $name = trim($_POST['name']);
  $raid = $_POST['raid'];
  $disk_array = $_POST['disks'];

  $num_of_disks = count($disk_array);
  
  exec ("ls /volumes/",$volumes_array);

  if(in_array($name, $volumes_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not add volume $name as it already exists!";
  }else{
    foreach($disk_array as $disk){
      exec ("wipefs -a /dev/$disk");
      exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
      exec("lsblk -o PKNAME,KNAME,TYPE,PATH /dev/$disk | grep part | awk '{print $4}'",$diskpart_array);
    }

    $diskparts = implode(' ',$diskpart_array);

    //WIPE out any superblocks
    exec("mdadm --zero-superblock $diskparts");
    
    //Generate the next /dev/mdX Number
    //get the last md#
    $md = exec("ls /dev/md*");
    //extract the numbers out of md
    $md_num = preg_replace('/[^0-9]/', '', $md);
    //add 1 to the num
    $new_md_num = $md_num + 1;

    exec("yes | mdadm --create /dev/md$new_md_num --level=$raid --raid-devices=$num_of_disks $diskparts");

    exec ("mkdir /volumes/$name");

    exec ("mkfs.ext4 -F /dev/md$new_md_num");

    //sleep(10);
    
    exec ("mount /dev/md$new_md_num /volumes/$name");  
      
    $uuid = exec("blkid -o value --match-tag UUID /dev/md$new_md_num");

    $myFile = "/etc/fstab";
    $fh = fopen($myFile, 'a') or die("can't open file");
    $stringData = "UUID=$uuid /volumes/$name ext4 defaults 0 0\n";
    fwrite($fh, $stringData);
    fclose($fh);
  
  }

  header("Location: volumes.php");

}

if(isset($_POST['volume_add_backup'])){
  $name = trim($_POST['name']);
  $disk = $_POST['disk'];
  
  exec ("wipefs -a /dev/$disk");
  exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
  $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print $2}'");
  exec ("e2label /dev/$diskpart $name");
  exec ("mkfs.$filesystem -f /dev/$diskpart");

  $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");

  exec ("mkdir /mnt/backup--$name--$uuid");

  header("Location: volumes.php");
}

if(isset($_GET['volume_delete'])){
  $name = $_GET['volume_delete'];
  //check to make sure no shares are linked to the volume
  //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
  //the code to do that here
  $diskpart = exec("findmnt -o SOURCE --target /volumes/$name");
  $disk = exec("lsblk -o pkname $diskpart");
  $uuid = exec("blkid -o value --match-tag UUID $diskpart");
  
  exec("ls /volumes/$name | grep -v lost+found", $directory_list_array);
  if(!empty($directory_list_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete volume $name as there are files shares, please delete the file shares accociated to volume $name and try again!";
  }else{
    //UNMOUNTED CRYPT
    //Check to see if its an unmounted crypt volume if so replace $disk with new $disk
    if(file_exists("/volumes/$name/ -name .uuid_map")){
      $disk_part_uuid = exec("cat /volumes/$name/.uuid_map");
      $disk = exec("lsblk -o PKNAME,NAME,UUID | grep $disk_part_uuid | awk '{print $1}'");
    }

    exec ("umount -l /volumes/$name");
    exec ("cryptsetup close $name");
    exec ("rm -rf /volumes/$name");
    
    //RAID Remove
    //Get Disks and Partition number in the array 
    exec("lsblk -o PKNAME,PATH,TYPE | grep $diskpart | awk '{print \"/dev/\"$1}'",$array_disk_part_array);
    $disk_part_in_array  = implode(' ', $array_disk_part_array);
    
    exec("mdadm --stop $diskpart");

    exec("mdadm --zero-superblock $disk_part_in_array");

    foreach($array_disk_part_array as $array_disk_part){
      $disk_in_array = exec("lsblk -n -o PKNAME,PATH | grep $array_disk_part | awk '{print $1}'");
      exec ("wipefs -a /dev/$disk_in_array");
    }
    
    //END RAID Remove

    exec ("wipefs -a /dev/$disk");

    deleteLineInFile("/etc/fstab","$uuid");

  }
  
  header("Location: volumes.php");
}

if(isset($_POST['share_add'])){
  $volume = $_POST['volume'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $share_path = "/volumes/$volume/$name";
  $read_only = intval($_POST['read_only']);
  $group = $_POST['group'];
  
  if($read_only == 1){
    $read_only_value = "yes";
  }else{
    $read_only_value = "no";
  }
  
  //Checks
  exec("ls /etc/samba/shares",$existing_shares_array);
  exec("find /volumes/*/* -maxdepth 0 -type d -printf '%f\n'",$existing_diectories_array);
  $docker_shares_array = array("media", "downloads", "docker", "users");
  $mounted = exec("df | grep $volume");
  if($volume == "sys-vol"){
    $mounted = 1;
  }
  
  if(in_array($name, $existing_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "The share with the name $name already exists can not add share!";
  }elseif(in_array($name, $existing_directories_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Directory $name already exists can not add share with the name $name, would you like to share the existing directory instead (Note this will update the permissions to user root with RWX and group to RWX and everyone else to --- or would you like to delete the directory and its contents and create a new directory!";
  }elseif(in_array($name, $docker_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not create the share $name as it shares the same share name as an app. The followng share names are forbiddon media, downloads, docker and users!";
  }elseif(empty($mounted)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not create the share $name because the volume $volume is not mounted";
  }else{
    exec("mkdir $share_path");
    exec("chgrp $group $share_path");
    exec("chmod 0770 $share_path");

    $myFile = "/etc/samba/shares/$name";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = $read_only_value\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
    fwrite($fh, $stringData);
    fclose($fh);

    $myFile = "/etc/samba/shares.conf";
    $fh = fopen($myFile, 'a') or die("not able to write to file");
    $stringData = "\ninclude = /etc/samba/shares/$name";
    fwrite($fh, $stringData);
    fclose($fh);

    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  }

  header("Location: shares.php");
}

if(isset($_POST['share_edit'])){
  $volume = $_POST['volume'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $share_path = "/volumes/$volume/$name";
  $group = $_POST['group'];
  $current_volume = $_POST['current_volume'];
  $current_name = $_POST['current_name'];
  $current_description = $_POST['current_description'];
  $current_share_path = "/volumes/$current_volume/$current_name";
  $current_group = $_POST['current_group'];
  $read_only = intval($_POST['read_only']);

  if($read_only == 1){
    $read_only_value = "yes";
  }else{
    $read_only_value = "no";
  }

  if($name <> $current_name){

    //Name Checks
    exec("ls /etc/samba/shares",$existing_shares_array);
    exec("find /volumes/*/* -maxdepth 0 -type d -printf '%f\n'",$existing_diectories_array);
    $docker_shares_array = array("media", "downloads", "docker", "users");
    
    if(in_array($name, $existing_shares_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "The share with the name $name already exists can not rename share $current_name!";
    }elseif(in_array($name, $existing_directories_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "Directory $name already exists can not rename share $current_name to $name!";
    }elseif(in_array($name, $docker_shares_array)){
      $_SESSION['alert_type'] = "warning";
      $_SESSION['alert_message'] = "Can not rename share $current_name to $name the share $name shares the same share name as an app. The followng share names are forbiddon media, downloads, docker and users!";
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
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "changed group $current_group to $group on share $name successfully!";
  }elseif($volume != $current_volume){
    exec("mv /volumes/$current_volume/$current_name /volumes/$volume");
    $_SESSION['alert_type'] = "info";
    $_SESSION['alert_message'] = "Moved share $name from $current_volume to $volume successfully!";
  }

  //Update User Group Permssions no matter what
  exec("chown -R root:$group $share_path");

  $myFile = "/etc/samba/shares/$name";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = $read_only_value\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  header("Location: shares.php");
}

if(isset($_GET['share_delete'])){
  $name = $_GET['share_delete'];

  $docker_shares_array = array();

  if(file_exists("/volumes/$config_docker_volume/docker/jellyfin")){
    array_push($docker_shares_array, "media");
  }

  if(file_exists("/volumes/$config_docker_volume/docker/daapd")){
    array_push($docker_shares_array, "media");
  }

  if(file_exists("/volumes/$config_docker_volume/docker/transmission")){
    array_push($docker_shares_array, "downloads");
  }

  $system_shares_array = array();

  array_push($system_shares_array, "users", "docker");

  if(in_array($name, $docker_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete the share $name as it shares the same share name as an app thats using it, try deleting the app then deleting the share";
  }elseif(in_array($name, $system_shares_array)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Can not delete the share $name as it is a system share!";
  }else{
    $path = exec("find /volumes/*/$name -name $name");

    exec ("rm -rf $path");
    exec ("rm -f /etc/samba/shares/$name");

    deleteLineInFile("/etc/samba/shares.conf","$name");

    exec("systemctl restart smbd");
    exec("systemctl restart nmbd");
  
  }
  header("Location: shares.php");
}

if(isset($_POST['network_add'])){
  $interface = $_POST['interface'];
  $subnet = $_POST['subnet'];
  $method = $_POST['method'];
  $address = $_POST['address'];
  $gateway = $_POST['gateway'];
  $dns = $_POST['dns'];

  if($method == 'DHCP'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("systemctl restart systemd-networkd");
    echo "<script>window.location = 'http://$config_hostname:81/network.php'</script>";
  }
  if($method == 'Static'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address$subnet\nGateway=$gateway\nDNS=$dns\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("systemctl restart systemd-networkd");
    echo "<script>window.location = 'http://$address:81/network.php'</script>";
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
  $stringData = "rsync --verbose --log-file=/var/log/rsync.log --archive /volumes/$source/ /mnt/$destination/";
  fwrite($fh, $stringData);
  fclose($fh);

  //exec("rsync --verbose --log-file=/var/log/rsync.log --archive /volumes/$source/ /volumes/$destination/");
  
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
  $stringData = "rsync --verbose --log-file=/var/log/rsync.log --archive /volumes/$source/ /volumes/$destination/ --delete";
  fwrite($fh, $stringData);
  fclose($fh);

  //exec("rsync --verbose --log-file=/var/log/rsync.log --archive /volumes/$source/ /volumes/$destination/");
  
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

if(isset($_POST['wipe_disk'])){
  $hdd = $_GET['wipe_disk'];

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

if(isset($_POST['settings_notifications'])){
  $config['smtp_server'] = $_POST['smtp_server'];
  $config['smtp_port'] = $_POST['smtp_port'];
  $config['smtp_username'] = $_POST['smtp_username'];
  $config['smtp_password'] = $_POST['smtp_password'];
  $config['mail_from'] = $_POST['mail_from'];
  $config['mail_to'] = $_POST['mail_to'];
  $enable_system_report = $_POST['enable_system_report'];
  if($enable_system_report == 1){
    exec("echo 'php /simpnas/mail_system_report.php' > /etc/cron.daily/system-report");
    exec("chmod 755 /etc/cron.daily/system-report");
  }else{
    exec("rm -f /etc/cron.daily/system-report");
  }

  //file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  file_put_contents('config.php', '<?php return ' . var_export($config, true) . ';');
  sleep(3);

  header("Location: notifications.php");
}

//APP SECTION

if(isset($_POST['install_jellyfin'])){
  
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    $volume = $_POST['volume'];

    $media_volume_path = exec("find /volumes/*/media -name media");
    
    $group_id = exec("getent group media | cut -d: -f3");

    if(empty($group_id)){
      exec ("addgroup media");
      $group_id = exec("getent group media | cut -d: -f3");
    }

    if(!file_exists("$media_volume_path")) {

      mkdir("/volumes/$volume/media");
      mkdir("/volumes/$volume/media/tvshows");
      mkdir("/volumes/$volume/media/movies");
      mkdir("/volumes/$volume/media/music");
      

      chgrp("/volumes/$volume/media","media");
      chgrp("/volumes/$volume/media/tvshows","media");
      chgrp("/volumes/$volume/media/movies","media");
      chgrp("/volumes/$volume/media/music","media");
      
      
      chmod("/volumes/$volume/media",0770);
      chmod("/volumes/$volume/media/tvshows",0770);
      chmod("/volumes/$volume/media/movies",0770);
      chmod("/volumes/$volume/media/music",0770);
      
      
      $myFile = "/etc/samba/shares/media";
      $fh = fopen($myFile, 'w') or die("not able to write to file");
      $stringData = "[media]\n   comment = Video and Audio Media\n   path = /volumes/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770";
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

    mkdir("/volumes/$config_docker_volume/docker/jellyfin");
    mkdir("/volumes/$config_docker_volume/docker/jellyfin/config");
    mkdir("/volumes/$config_docker_volume/docker/jellyfin/cache");

    chgrp("/volumes/$config_docker_volume/docker/jellyfin","media");
    chgrp("/volumes/$config_docker_volume/docker/jellyfin/config","media");
    chgrp("/volumes/$config_docker_volume/docker/jellyfin/cache","media");

    chmod("/volumes/$config_docker_volume/docker/jellyfin",0770);
    chmod("/volumes/$config_docker_volume/docker/jellyfin/config",0770);
    chmod("/volumes/$config_docker_volume/docker/jellyfin/cache",0770);



    exec("docker run -d --name jellyfin --restart=unless-stopped -p 8096:8096 -e PGID=$group_id -e PUID=0 -v /volumes/$config_docker_volume/docker/jellyfin:/config -v /volumes/$volume/media/tvshows:/tvshows -v /volumes/$volume/media/movies:/movies -v /volumes/$volume/media/music:/music linuxserver/jellyfin");

  }
  
  header("Location: apps.php");
}

if(isset($_GET['update_jellyfin'])){

  $group_id = exec("getent group media | cut -d: -f3");
  $media_path = exec("find /volumes/*/media -name media");
  $docker_path = exec("find /volumes/*/docker/jellyfin -name jellyfin");

  exec("docker pull linuxserver/jellyfin");
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  
  exec("docker run -d --name jellyfin --restart=unless-stopped -p 8096:8096 -e PGID=$group_id -e PUID=0 -v $docker_path:/config -v $media_path/tvshows:/tvshows -v $media_path/movies:/movies -v $media_path/music:/music linuxserver/jellyfin");

  exec("docker image prune");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_jellyfin'])){
  //stop and delete docker container
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/jellyfin");
  //Remove unused docker images
  exec("docker image prune");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['install_daapd'])){
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    $volume = $_POST['volume'];
    
    $media_volume_path = exec("find /volumes/*/media -name media");
    
    $group_id = exec("getent group media | cut -d: -f3");

    if(empty($group_id)){
      exec ("addgroup media");
      $group_id = exec("getent group media | cut -d: -f3");
    }

    if(!file_exists("$media_volume_path")) {

      mkdir("/volumes/$volume/media");
      mkdir("/volumes/$volume/media/tvshows");
      mkdir("/volumes/$volume/media/movies");
      mkdir("/volumes/$volume/media/music");
      

      chgrp("/volumes/$volume/media","media");
      chgrp("/volumes/$volume/media/tvshows","media");
      chgrp("/volumes/$volume/media/movies","media");
      chgrp("/volumes/$volume/media/music","media");
      
      
      chmod("/volumes/$volume/media",0770);
      chmod("/volumes/$volume/media/tvshows",0770);
      chmod("/volumes/$volume/media/movies",0770);
      chmod("/volumes/$volume/media/music",0770);
      
      
      $myFile = "/etc/samba/shares/media";
      $fh = fopen($myFile, 'w') or die("not able to write to file");
      $stringData = "[media]\n   comment = Video and Audio Media\n   path = /volumes/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770";
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

    mkdir("/volumes/$config_docker_volume/docker/daapd");
    chgrp("/volumes/$config_docker_volume/docker/daapd","media");
    chmod("/volumes/$config_docker_volume/docker/daapd",0770);

    exec("docker run -d --name daapd --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /volumes/$config_docker_volume/docker/daapd:/config -v /volumes/$volume/media/music:/music linuxserver/daapd");
  }
  
  header("Location: apps.php");
}

if(isset($_POST['install_photoprism'])){
  
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    $volume = $_POST['volume'];

    $photos_volume_path = exec("find /volumes/*/photos -name photos");
    
    $group_id = exec("getent group photos | cut -d: -f3");

    if(empty($group_id)){
      exec ("addgroup photos");
      $group_id = exec("getent group photos | cut -d: -f3");
    }

    if(!file_exists("$photos_volume_path")) {

      mkdir("/volumes/$volume/photos");
      chgrp("/volumes/$volume/photos","photos");
      chmod("/volumes/$volume/photos",0770);
      
      $myFile = "/etc/samba/shares/photos";
      $fh = fopen($myFile, 'w') or die("not able to write to file");
      $stringData = "[photos]\n   comment = Photos\n   path = /volumes/$volume/photos\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @photos\n   force group = photos\n   create mask = 0660\n   directory mask = 0770";
      fwrite($fh, $stringData);
      fclose($fh);

      $myFile = "/etc/samba/shares.conf";
      $fh = fopen($myFile, 'a') or die("not able to write to file");
      $stringData = "\ninclude = /etc/samba/shares/photos";
      fwrite($fh, $stringData);
      fclose($fh);
      
      exec("systemctl restart smbd");
      exec("systemctl restart nmbd");

    }

    mkdir("/volumes/$config_docker_volume/docker/photoprism");
    mkdir("/volumes/$config_docker_volume/docker/photoprism/storage");
  
    chgrp("/volumes/$config_docker_volume/docker/photoprism","photos");
    chgrp("/volumes/$config_docker_volume/docker/photoprism/storage","photos");

    chmod("/volumes/$config_docker_volume/docker/photoprism",0770);
    chmod("/volumes/$config_docker_volume/docker/photoprism/storage",0770);

    exec("docker run -d --name photoprism --restart=unless-stopped -p 2342:2342 -e PGID=$group_id -e PUID=0 -e PHOTOPRISM_UPLOAD_NSFW=true -e PHOTOPRISM_ADMIN_PASSWORD=password --security-opt seccomp=unconfined --security-opt apparmor=unconfined -v /volumes/$config_docker_volume/docker/photoprism/storage:/photoprism/storage -v /volumes/$volume/photos:/photoprism/originals photoprism/photoprism");

  }
  
  header("Location: apps.php");
}

if(isset($_GET['update_photoprism'])){

  $group_id = exec("getent group photos | cut -d: -f3");
  $photos_path = exec("find /volumes/*/photos -name photos");
  $docker_path = exec("find /volumes/*/docker/photoprism -name photoprism");

  exec("docker pull photoprism/photoprism");
  exec("docker stop photoprism");
  exec("docker rm photoprism");

  exec("docker run -d --name photoprism --restart=unless-stopped -p 2342:2342 -e PGID=$group_id -e PUID=0 -e PHOTOPRISM_UPLOAD_NSFW=true -e PHOTOPRISM_ADMIN_PASSWORD=password --security-opt seccomp=unconfined --security-opt apparmor=unconfined -v $docker_path/storage:/photoprism/storage -v $photos_path:/photoprism/originals photoprism/photoprism");

  exec("docker image prune");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_photoprism'])){
  //stop and delete docker container
  exec("docker stop photoprism");
  exec("docker rm photoprism");
  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/photoprism");
  //Remove unused docker images
  exec("docker image prune");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['update_daapd'])){

  $group_id = exec("getent group media | cut -d: -f3");
  $media_path = exec("find /volumes/*/media -name media");
  $docker_path = exec("find /volumes/*/docker/daapd -name daapd");

  exec("docker pull linuxserver/daapd");
  exec("docker stop daapd");
  exec("docker rm daapd");
  
  exec("docker run -d --name daapd --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v $docker_path:/config -v $media_path/music:/music linuxserver/daapd");

  exec("docker image prune");
  
  header("Location: apps.php");
}

if(isset($_GET['uninstall_daapd'])){
  //stop and delete docker container
  exec("docker stop daapd");
  exec("docker rm daapd");
  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/daapd");
  //Remove unused docker images
  exec("docker image prune");
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['install_nextcloud'])){

  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    //create my-network if does not exist
    exec("docker network create nextcloud-net");

    $password = $_POST['password'];
    $enable_samba_auth = $_POST['enable_samba_auth'];
    $enable_samba_mount = $_POST['enable_samba_mount'];
    $install_apps = $_POST['install_apps'];
    $data_volume = $_POST['data_volume'];

    mkdir("/volumes/$config_docker_volume/docker/nextcloud");
    mkdir("/volumes/$config_docker_volume/docker/nextcloud/data");
    mkdir("/volumes/$data_volume/nextcloud_data");
    mkdir("/volumes/$data_volume/nextcloud_data/appdata");
    

    mkdir("/volumes/$config_docker_volume/docker/nextcloud_mariadb");

    exec("docker run -d --name nextcloud_mariadb --net=nextcloud-net -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=nextcloud -e MYSQL_USER=nextcloud -e MYSQL_PASSWORD=password -p 3306:3306 --restart=unless-stopped -v /volumes/$config_docker_volume/docker/nextcloud_mariadb:/config linuxserver/mariadb");

    exec("docker run -d --name nextcloud --net=nextcloud-net -p 6443:443 --restart=unless-stopped -v /volumes/$config_docker_volume/docker/nextcloud/data:/data -v /volumes/$data_volume/nextcloud_data/appdata:/config linuxserver/nextcloud");

    exec("echo '' > /var/log/nextcloud-counter.log");
    $var = '';
    $num = 0;
    while(empty($var)){
      sleep(1);
      $var = exec("docker logs nextcloud | grep 'starting services'");
      $timestamp = date("h:i:sa");
      $num = $num + 1;
      exec("echo '$num - $var - $timestamp' >> /var/log/nextcloud-counter.log");    
    }

    //exec("sleep 80");
    
    exec("docker exec nextcloud rm -rf /config/www/nextcloud/core/skeleton");
    if($enable_samba_mount == 1){
      exec("docker exec nextcloud mkdir /config/www/nextcloud/core/skeleton");
      exec("docker exec nextcloud mkdir /config/www/nextcloud/core/skeleton/Shared-Folders");
    }
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ maintenance:install --database='mysql' --database-name='nextcloud' --database-host='nextcloud_mariadb' --database-user='nextcloud' --database-pass='password' --admin-user='admin' --admin-pass='$password'");

    //Add Trusted Hosts
    $docker_gateway = exec("docker network inspect my-network | grep Gateway | awk '{print $2}' | sed 's/\\\"//g'");

    //Add Hostname and Primary IP to trusted_domains list
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set trusted_domains 2 --value=$config_hostname");
    exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ config:system:set trusted_domains 3 --value=$config_primary_ip");

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
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install spreed");
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable spreed");
      //Install Community Document Server
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install documentserver_community");
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable documentserver_community");
      //Install OnlyOffice
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install onlyoffice");
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable onlyoffice");
      //Install Draw.IO
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install drawio");
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable drawio");
      //Install Mail
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:install mail");
      //exec("docker exec nextcloud sudo -u abc php /config/www/nextcloud/occ app:enable mail");
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
  } //End Docker Check

  header("Location: apps.php");
}

if(isset($_GET['update_nextcloud'])){

  exec("docker pull linuxserver/nextcloud");
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");

  exec("docker pull linuxserver/nextcloud_mariadb");
  exec("docker stop nextcloud_mariadb");
  exec("docker rm nextcloud_mariadb");

  $nextcloud_mariadb_path = exec("find /volumes/*/docker/nextcloud_mariadb -name nextcloud_mariadb");
  $nextcloud_data_path = exec("find /volumes/*/nextcloud_data/appdata -name appdata");
  $nextcloud_app_data = exec("find /volumes/*/docker/nextcloud/data -name data");

  exec("docker run -d --name nextcloud_mariadb --net=nextcloud-net -p 3306:3306 --restart=unless-stopped -v $nextcloud_mariadb_path:/config linuxserver/mariadb");

  exec("docker run -d --name nextcloud --net=nextcloud-net -p 6443:443 --restart=unless-stopped -v $nextcloud_data_path:/config -v $nextcloud_app_data:/data linuxserver/nextcloud");

  exec("echo '' > /var/log/nextcloud-update-counter.log");
  $var = '';
  $num = 0;
  while(empty($var)){
    sleep(1);
    $var = exec("docker logs nextcloud | grep 'starting services'");
    $timestamp = date("h:i:sa");
    $num = $num + 1;
    exec("echo '$num - $var - $timestamp' >> /var/log/nextcloud-update-counter.log");    
  }

  //sleep(5);
 
  exec("docker exec nextcloud updater.phar --no-interaction");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_nextcloud'])){
  //stop and delete docker container
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");
  exec("docker stop nextcloud_mariadb");
  exec("docker rm nextcloud_mariadb");

  //Remove nextcloud-net
  exec("docker network rm nextcloud-net");

  $nextcloud_data_volume_path = exec("find /volumes/*/nextcloud_data -name nextcloud_data");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/nextcloud");
  exec ("rm -rf /volumes/$config_docker_volume/docker/nextcloud_mariadb");
  exec ("rm -rf $nextcloud_data_volume_path");
  
  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");

}

if(isset($_GET['install_bitwarden'])){

  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    mkdir("/volumes/$config_docker_volume/docker/bitwarden/");

    exec("docker run -d --name bitwarden -v /volumes/$config_docker_volume/docker/bitwarden:/data/ -p 88:80 --restart=unless-stopped bitwardenrs/server");
  }

  header("Location: apps.php");
}

if(isset($_GET['update_bitwarden'])){

  $docker_path = exec("find /volumes/*/docker/bitwarden -name bitwarden");

  exec("docker pull bitwardenrs/server");
  exec("docker stop bitwarden");
  exec("docker rm bitwarden");

  exec("docker run -d --name bitwarden -v $docker_path:/data/ -p 88:80 --restart=unless-stopped bitwardenrs/server");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_bitwarden'])){
  //stop and delete docker container
  exec("docker stop bitwarden");
  exec("docker rm bitwarden");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/bitwarden");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_nginx-proxy-manager'])){

  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    mkdir("/volumes/$config_docker_volume/docker/nginx-proxy-manager");

    exec("docker run -d --name nginx-proxy-manager -p 80:8080 -p 83:8181 -p 443:4443 -v /volumes/$config_docker_volume/docker/nginx-proxy-manager:/config --restart=unless-stopped jlesage/nginx-proxy-manager");
  }

  header("Location: apps.php");
}

if(isset($_GET['update_nginx-proxy-manager'])){

  $docker_path = exec("find /volumes/*/docker/nginx-proxy-manager -name nginx-proxy-manager");

  exec("docker pull jlesage/nginx-proxy-manager");
  exec("docker stop nginx-proxy-manager");
  exec("docker rm nginx-proxy-manager");

  exec("docker run -d --name nginx-proxy-manager -p 80:8080 -p 83:8181 -p 443:4443 -v $docker_path:/config --restart=unless-stopped jlesage/nginx-proxy-manager");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_nginx-proxy-manager'])){
  //stop and delete docker container
  exec("docker stop nginx-proxy-manager");
  exec("docker rm nginx-proxy-manager");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/nginx-proxy-manager");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_homeassistant'])){
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    mkdir("/volumes/$config_docker_volume/docker/homeassistant");

    exec("docker run -d --name homeassistant --restart=unless-stopped -p 8123:8123 -v /volumes/$config_docker_volume/docker/homeassistant:/config homeassistant/home-assistant:stable");
  }

  header("Location: apps.php");
}

if(isset($_GET['update_homeassistant'])){

  $docker_path = exec("find /volumes/*/docker/homeassistant -name homeassistant");

  exec("docker pull homeassistant/home-assistant:stable");
  exec("docker stop homeassistant");
  exec("docker rm homeassistant");

  exec("docker run -d --name homeassistant --restart=unless-stopped -p 8123:8123 -v $docker_path:/config homeassistant/home-assistant:stable");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_homeassistant'])){
  //stop and delete docker container
  exec("docker stop homeassistant");
  exec("docker rm homeassistant");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/homeassistant");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_unifi-controller'])){
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

    mkdir("/volumes/$config_docker_volume/docker/unifi-controller/");

    exec("docker run -d --name unifi-controller -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /volumes/$config_docker_volume/docker/unifi-controller:/config linuxserver/unifi-controller");
  }
  header("Location: apps.php");
}

if(isset($_GET['update_unifi-controller'])){

  $docker_path = exec("find /volumes/*/docker/unifi-controller -name unifi-controller");

  exec("docker pull linuxserver/unifi-controller");
  exec("docker stop unifi-controller");
  exec("docker rm unifi-controller");

  exec("docker run -d --name unifi-controller -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v $docker_path:/config linuxserver/unifi-controller");

  exec("docker image prune");
  
  header("Location: apps.php");

}

if(isset($_GET['uninstall_unifi-controller'])){
  //stop and delete docker container
  exec("docker stop unifi-controller");
  exec("docker rm unifi-controller");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/unifi-controller");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['install_transmission'])){ 
  // Check to see if docker is running
  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $_SESSION['alert_type'] = "warning";
    $_SESSION['alert_message'] = "Docker is not running therefore we cannot install!";
  }else{

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
      
    exec ("addgroup download");
    $group_id = exec("getent group download | cut -d: -f3");

    mkdir("/volumes/$volume/downloads");
    mkdir("/volumes/$volume/downloads/completed");
    mkdir("/volumes/$volume/downloads/incomplete");
    mkdir("/volumes/$volume/downloads/watch");
    mkdir("/volumes/$config_docker_volume/docker/transmission");

    chgrp("/volumes/$volume/downloads","download");
    chgrp("/volumes/$volume/downloads/watch","download");
    chgrp("/volumes/$volume/downloads/completed","download");
    chgrp("/volumes/$volume/downloads/incomplete","download");
    chgrp("/volumes/$volume/downloads/watch","download");
    chgrp("/volumes/$config_docker_volume/docker/transmission","download");

    chmod("/volumes/$volume/downloads",0770);
    chmod("/volumes/$volume/downloads/completed",0770);
    chmod("/volumes/$volume/downloads/incomplete",0770);
    chmod("/volumes/$volume/downloads/watch",0770);
    chmod("/volumes/$config_docker_volume/docker/transmission",0770);
    
    $myFile = "/etc/samba/shares/downloads";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[downloads]\n   comment = Torrent Downloads used by Transmission\n   path = /volumes/$volume/downloads\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @download\n   force group = download\n   create mask = 0660\n   directory mask = 0770";
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
      exec("docker run --cap-add=NET_ADMIN -d --name transmission --restart=unless-stopped -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG='$vpn_server' -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m $dns -v /etc/localtime:/etc/localtime:ro -v /volumes/$config_docker_volume/docker/transmission:/data/transmission-home -v /volumes/$volume/downloads/completed:/data/completed -v /volumes/$volume/downloads/incomplete:/data/incomplete -v /volumes/$volume/downloads/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn");
    }else{
      exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /volumes/$config_docker_volume/docker/transmission:/config -v /volumes/$volume/downloads/watch:/watch -v /volumes/$volume/downloads:/downloads -v /volumes/$volume/downloads/completed:/downloads/complete -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
    }
  } //End Docker Check
  
  header("Location: apps.php");
}

if(isset($_POST['transmission_update'])){

  $group_id = exec("getent group download | cut -d: -f3");
  $volume_path = exec("find /volumes/*/downloads -name 'downloads'");
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

  //exec("docker pull haugene/transmission-openvpn");
  exec("docker stop transmission");
  exec("docker rm transmission");
  exec("docker image prune");

  if($enable_vpn == 1){
    exec("docker run --cap-add=NET_ADMIN -d --name transmission --restart=unless-stopped -e OPENVPN_PROVIDER=$vpn_provider -e OPENVPN_CONFIG='$vpn_server' -e OPENVPN_USERNAME=$username -e OPENVPN_PASSWORD=$password -e WEBPROXY_ENABLED=false -e LOCAL_NETWORK=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16 -e PGID=$group_id -e PUID=0 -e TRANSMISSION_UMASK=0 --log-driver json-file --log-opt max-size=10m $dns -v /etc/localtime:/etc/localtime:ro -v /volumes/$config_docker_volume/docker/transmission:/data/transmission-home -v $volume_path/completed:/data/completed -v $volume_path/incomplete:/data/incomplete -v $volume_path/watch:/data/watch -p 9091:9091 haugene/transmission-openvpn");
  }else{
    exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /volumes/$config_docker_volume/docker/transmission:/config -v $volume_path/watch:/watch -v $volume_path:/downloads -v $volume_path/completed:/downloads/complete -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
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
  $path = exec("find /volumes/*/downloads -name downloads");
  //delete directory
  exec ("rm -rf $path"); //Delete
  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/transmission");
  //delete samba share
  exec ("rm -f /etc/samba/shares/downloads");
  deleteLineInFile("/etc/samba/shares.conf","downloads");
  //restart samba
  exec("systemctl restart smbd");
  exec("systemctl restart nmbd");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_wireguard'])){

  mkdir("/volumes/$config_docker_volume/docker/wireguard");

  exec("docker run -d --name wireguard --cap-add=NET_ADMIN --cap-add=SYS_MODULE --restart=unless-stopped -e PEERS=1 -v /volumes/$config_docker_volume/docker/wireguard:/config -v /lib/modules:/lib/modules -p 51820:51820/udp --sysctl='net.ipv4.conf.all.src_valid_mark=1' linuxserver/wireguard");
  header("Location: apps.php");
}

if(isset($_GET['wireguard_qr'])){
  $peer = $_GET['peer'];

  // open the file in a binary mode
  $name = "/volumes/$config_docker_volume/docker/wireguard/$peer/$peer.png";
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
  $name = "/volumes/$config_docker_volume/docker/wireguard/$peer/$peer.conf";
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
  exec ("rm -rf /volumes/$config_docker_volume/docker/wireguard");

  //delete images
  exec("docker image prune");

  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_GET['install_openvpn'])){

  mkdir("/volumes/$config_docker_volume/docker/openvpn");

  exec("docker run -d --name openvpn --restart=unless-stopped -v /volumes/$config_docker_volume/docker/openvpn:/config -p 943:943 -p 9443:9443 -p 1194:1194/udp linuxserver/openvpn-as");
  header("Location: apps.php");
}

if(isset($_GET['uninstall_openvpn'])){
  //stop and delete docker container
  exec("docker stop openvpn");
  exec("docker rm openvpn");

  //delete docker config
  exec ("rm -rf /volumes/$config_docker_volume/docker/openvpn");

  //delete images
  exec("docker image prune");
  
  //redirect back to packages
  header("Location: apps.php");
}

if(isset($_POST['setup_timezone'])){
  $timezone = $_POST['timezone'];
  
  exec("timedatectl set-timezone '$timezone'");

  header("Location: setup_network.php");
}

if(isset($_POST['setup_network'])){
  
  $hostname = $_POST['hostname'];
  $interface = $_POST['interface'];
  $method = $_POST['method'];
  $address = $_POST['address'];
  $netmask = $_POST['netmask'];
  $gateway = $_POST['gateway'];
  $dns = $_POST['dns'];

  $current_hostname = exec("hostname");

  exec("sed -i 's/$current_hostname/$hostname/g' /etc/hosts");
  exec("hostnamectl set-hostname $hostname");
  $primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");

  exec ("mv /etc/network/interfaces /etc/network/interfaces.save");
  exec ("systemctl enable systemd-networkd");

  if($method == 'DHCP'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nDHCP=ipv4\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("echo '127.0.0.1      localhost' > /etc/hosts");
    exec("echo '127.0.0.2     $hostname' >> /etc/hosts");
    //exec("systemctl restart systemd-networkd > /dev/null &");
    echo "<script>window.location = 'http://$primary_ip:81/setup_volume.php'</script>";
  }
  
  if($method == 'Static'){
    $myFile = "/etc/systemd/network/$interface.network";
    $fh = fopen($myFile, 'w') or die("not able to write to file");
    $stringData = "[Match]\nName=$interface\n\n[Network]\nAddress=$address$netmask\nGateway=$gateway\nDNS=$dns\n";
    fwrite($fh, $stringData);
    fclose($fh);
    exec("echo '127.0.0.1      localhost' > /etc/hosts");
    exec("echo '$address     $hostname' >> /etc/hosts");
    exec("systemctl restart systemd-networkd > /dev/null &");
    echo "<script>window.location = 'http://$address:81/setup_volume.php'</script>";
  }
}

if(isset($_POST['setup_volume'])){
  $volume_name = $_POST['volume_name'];
  $disk = $_POST['disk'];

  exec ("wipefs -a /dev/$disk");
  exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
  $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print $2}'");
  //WIPE out any superblocks
  exec("mdadm --zero-superblock /dev/$diskpart");
  exec ("mkdir /volumes/$volume_name");
  exec ("mkfs.ext4 -F /dev/$diskpart");
  exec ("e2label /dev/$diskpart $volume_name");
  exec ("mount /dev/$diskpart /volumes/$volume_name"); 

  $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");
  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "UUID=$uuid /volumes/$volume_name ext4 defaults 0 1\n";
  fwrite($fh, $stringData);
  fclose($fh);

  header("Location: setup_final.php");
}

if(isset($_POST['setup_volume_raid'])){
  $volume_name = trim($_POST['volume_name']);
  $raid = $_POST['raid'];
  $disk_array = $_POST['disks'];

  $num_of_disks = count($disk_array);
  
  foreach($disk_array as $disk){
    exec ("wipefs -a /dev/$disk");
    exec ("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");
    exec("lsblk -o PKNAME,KNAME,TYPE,PATH /dev/$disk | grep part | awk '{print $4}'",$diskpart_array);
  }

  $diskparts = implode(' ',$diskpart_array);

  //WIPE out any superblocks
  exec("mdadm --zero-superblock $diskparts");
  
  //Generate the next /dev/mdX Number
  //get the last md#
  $md = exec("ls /dev/md*");
  //extract the numbers out of md
  $md_num = preg_replace('/[^0-9]/', '', $md);
  //add 1 to the num
  $new_md_num = $md_num + 1;

  exec("yes | mdadm --create /dev/md$new_md_num --level=$raid --raid-devices=$num_of_disks $diskparts");

  exec ("mkdir /volumes/$volume_name");

  exec ("mkfs.ext4 -F /dev/md$new_md_num");
  
  exec ("mount /dev/md$new_md_num /volumes/$volume_name");  
    
  $uuid = exec("blkid -o value --match-tag UUID /dev/md$new_md_num");

  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "UUID=$uuid /volumes/$volume_name ext4 defaults 0 0\n";
  fwrite($fh, $stringData);
  fclose($fh);

  header("Location: setup_final.php");

}

if(isset($_POST['setup_final'])){
  $volume_name = exec("ls /volumes");
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  //Create config.php file
  
  $file = fopen("config.php", "w");

  $data = "<?php\nreturn array(\n'smtp_server' => '',\n'smtp_port' => '',\n'smtp_username' => '',\n'smtp_password' => '',\n'mail_from' => '',\n'mail_to' => '',\n'enable_beta' => '0'\n);\n?>";

  fwrite($file, $data);
  fclose($file);
  
  $myFile = "/etc/samba/shares/share";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[share]\n   comment = Shared files\n   path = /volumes/$volume_name/share\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @users\n   force group = users\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

  exec ("mkdir /volumes/$volume_name/docker");
  exec ("mkdir /volumes/$volume_name/users");
  exec ("mkdir /volumes/$volume_name/share");
  exec ("chmod 770 /volumes/$volume_name/share");

  $myFile = "/etc/samba/shares/users";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[users]\n   comment = Users Home Folders\n   path = /volumes/$volume_name/users\n   read only = no\n   create mask = 0600\n   directory mask = 0700\n";
  fwrite($fh, $stringData);
  fclose($fh);

  $myFile = "/etc/samba/shares.conf";
  $fh = fopen($myFile, 'a') or die("not able to write to file");
  $stringData = "\ninclude = /etc/samba/shares/users";
  fwrite($fh, $stringData);
  fclose($fh);

  $myFile = "/etc/samba/shares.conf";
  $fh = fopen($myFile, 'a') or die("not able to write to file");
  $stringData = "\ninclude = /etc/samba/shares/share";
  fwrite($fh, $stringData);
  fclose($fh);

  //Check to see if theres already a user added and delete that user
  $existing_username = exec("cat /etc/passwd | grep 1000 | awk -F: '{print $1}'");
  if(!empty($existing_username)){
    exec("deluser --remove-home $existing_username");
  }
  
  exec ("mkdir /volumes/$volume_name/users/$username");
  exec ("chmod -R 700 /volumes/$volume_name/users/$username");

  exec ("chgrp users /volumes/$volume_name/share");
  //Create the new user UNIX way
  exec ("useradd -g users -d /volumes/$volume_name/users/$username $username");
  exec ("echo '$password\n$password' | passwd $username");
  exec ("usermod -a -G admins $username");
  exec ("echo '$password\n$password' | smbpasswd -a $username");
  exec ("chown -R $username /volumes/$volume_name/users/$username");
  //Create the user under file browser
  exec("systemctl stop filebrowser");
  exec ("filebrowser -d /usr/local/etc/filebrowser.db users add $username $password --perm.admin=true");
  exec("systemctl start filebrowser");

  exec("apt install docker-ce docker-ce-cli containerd.io -y");
  exec("apt install docker.io -y");

  if($collect = 1){
    exec("curl https://simpnas.com/collect.php?'collect&machine_id='$(cat /etc/machine-id)''");
  }

  header("Location: restart.php");

  //header("Location: login.php");

}

?>