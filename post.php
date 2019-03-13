<?php 

  include("functions.php"); 
  include("config.php");

?>

<?php

if(isset($_POST['user_add']))
{
  $username = $_POST['username'];
  $password = $_POST['password'];
 
  exec ("echo -e '$password\n$password' | adduser -G users $username -h /mnt/$config_home_volume/homes/$username");
  exec ("echo -e '$password\n$password' | smbpasswd -as $username");
  if(isset($_POST['group'])){
  	$group_array = $_POST['group'];
  	foreach($group_array as $group){
    	exec ("adduser $username $group");
  	}
  }
  exec ("chmod -R 700 /mnt/$config_home_volume/homes/$username");
  
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['user_edit']))
{
  $username = $_POST['username'];
  $group_array = implode(",", $_POST['group']);
  
  $group_count = count($group);
  if(!empty($_POST['password'])){
    $password = $_POST['password'];
    exec ("echo -e '$password\n$password' | passwd $username ");
    exec ("echo -e '$password\n$password' | smbpasswd $username");
  }
  
  exec ("usermod -G $group_array $username ");

  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_edit']))
{
  $old_group = $_POST['old_group'];
  $group = $_POST['group'];

  exec ("groupmod -n $group $old_group");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['general_edit']))
{
  $hostname = $_POST['hostname'];
  
  exec("echo $hostname > /etc/hostname");
  exec("echo '127.0.0.1     $hostname localhost.localdomain localhost' > /etc/hosts");
  exec("hostname $hostname");
  exec("service networking restart");
  echo "<script>window.location = 'general.php'</script>";
}


if(isset($_GET['unmount_hdd']))
{
  $hdd = $_GET['unmount_hdd'];
  $hdd = $hdd."1";
  exec ("sudo umount $hdd");
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['delete_volume']))
{
  $name = $_GET['delete_volume'];
  //check to make sure no shares are linked to the volume
  //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
  //the code to do that here
  $hdd = exec("findmnt -n -o SOURCE --target /mnt/$name");
  
  exec ("umount /mnt/$name");
  exec ("rm -rf /mnt/$name");
  exec ("wipefs -a $hdd");
  
  deleteLineInFile("/etc/fstab","$hdd");
  
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_GET['mount_hdd']))
{
  $hdd = $_GET['mount_hdd'];
  $hdd = $hdd."1";
  $hdd_mount_to = "/mnt/".basename($hdd);
  if (!(file_exists($hdd_mount_to))) {
	     exec ("sudo mkdir $hdd_mount_to");
	} 

  exec ("sudo mount $hdd $hdd_mount_to");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_POST['volume_add']))
{
  $name = $_POST['name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /mnt/$name");
  if(!empty($_POST['encrypt'])){
    $password = $_POST['password'];
    exec ("echo -e '$password' | cryptsetup -q luksFormat $hdd_part");
    exec ("echo -e '$password' | cryptsetup open $hdd_part crypt$name");
    exec ("mkfs.ext4 /dev/mapper/crypt$name");    
    exec ("mount /dev/mapper/crypt$name /mnt/$name");
  }else{

  exec ("mkfs.ext4 $hdd_part");
  exec ("mount $hdd_part /mnt/$name");  
  
  $myFile = "/etc/fstab";
     $fh = fopen($myFile, 'a') or die("can't open file");
     $stringData = "$hdd_part    /mnt/$name      ext4    rw,relatime,data=ordered 0 2\n";
     fwrite($fh, $stringData);
     fclose($fh);
}
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_POST['share_add']))
{
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/mnt/$volume/$name";
  $group = $_POST['group'];
  mkdir("$share_path");
  chgrp("$share_path", $group);
  chmod("$share_path", 0770);

  //exec ("mkdir '$share_path'");
  //exec ("chgrp root:$group '$share_path'");
  //exec ("chmod 2777 '$share_path'");
     
       $myFile = "/etc/samba/smb.conf";
	   $fh = fopen($myFile, 'a') or die("can't open file");
	   $stringData = "\n[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770\n\n";
	   fwrite($fh, $stringData);
	   fclose($fh);
  
       exec ("service samba reload");
  	   echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_POST['share_edit']))
{
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/mnt/$volume/$name";
  $group = $_POST['group'];
  $current_volume = $_POST['current_volume'];
  $current_name = $_POST['current_name'];
  $current_description = $_POST['current_description'];
  $current_share_path = "/mnt/$current_volume/$current_name";
  $current_group = $_POST['current_group'];

  if($group != $current_group){
      chgrp("$current_share_path", $group);
  }
  if($volume != $current_volume){
    exec("mv /mnt/$current_volume/$current_name /mnt/$volume");
  }
  if($name != $current_name){
    exec("mv $current_share_path $share_path");
  }

       
       echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_GET['wipe_hdd']))
{
  $hdd = $_GET['wipe_hdd'];
  $hdd_short_name = basename($hdd);

  exec ("sudo shred -v -n 1 $hdd 2> /tmp/shred-$hdd_short_name-progress&");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['kill_pid']))
{
  $pid = $_GET['kill_pid'];

  exec ("sudo kill -9 $pid");
  
  echo "<script>window.location = 'ps.php'</script>";
}

if(isset($_GET['kill_wipe']))
{
  $hdd = $_GET['kill_wipe'];

  exec ("ps axu |grep 'shred -v -n 1 /dev/$hdd' | awk '{print $2}'", $pid);
  foreach ($pid as $pids) {
  exec ("sudo kill -9 $pids");
  echo "Killing<br>$pids<br>";
  }

  exec ("sudo rm -rf /tmp/shred-$hdd-progress");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_POST['website_add']))
{
  $website = $_POST['website'];
  $username = $_POST['username'];

  exec ("sudo mkdir /webstore/$website");
  exec ("sudo mkdir /webstore/$website/logs");
  exec ("sudo mkdir /webstore/$website/public");
  exec ("sudo touch /webstore/$website/logs/access.log");
  exec ("sudo touch /webstore/$website/logs/error.log");
  exec ("sudo chown -R $username:www-data /webstore/$website");
  exec ("sudo chmod -R 755 /webstore/$website");

  echo "<script>window.location = 'website_list.php'</script>";
}

if(isset($_POST['user_change_password_submit']))
{
	$user_id = check_input($_POST['user_id']);
	$password = check_input($_POST['password']);

    $sql = "UPDATE users SET password = '$password' WHERE user_id = '$user_id'";
   echo $sql;
    mysql_query($sql);
    echo "    <div class='bs-example'>
      <div class='alert alert-warning fade in'>
        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
        Password Updated!
      </div>
    </div>";

}

if(isset($_GET['delete_user']))
{
	$username = $_GET['delete_user'];

	exec("deluser --remove-home $username");
	exec("smbpasswd -x $username");
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_add']))
{
	$group = $_POST['group'];

  exec ("addgroup $group");
  
  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['group_modify_submit']))
{
	$group_id = check_input($_POST['group_id']);
	$group_name = check_input(ucwords($_POST['group_name']));
	$security = check_input($_POST['security']);

    $sql = "UPDATE groups SET group_name = '$group_name', security = '$security' WHERE group_id = '$group_id'";

    mysql_query($sql);
    echo "
    <script>
		window.location = '$document_root/group_list.php'
	</script>";
}

if(isset($_GET['delete_group']))
{
	$group = $_GET['delete_group'];

	exec("delgroup $group");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['install_plex']))
{
  $volume = $_POST['volume'];
  
  exec ("addgroup media");
  $group_id = exec("getent group media | cut -d: -f3");

  mkdir("/mnt/$volume/media");
  mkdir("/mnt/$volume/media/tvshows");
  mkdir("/mnt/$volume/media/movies");
  mkdir("/mnt/$config_docker_volume/docker/plex");
  mkdir("/mnt/$config_docker_volume/docker/plex/library");
  mkdir("/mnt/$config_docker_volume/docker/plex/transcode");

  chgrp("/mnt/$volume/media","media");
  chgrp("/mnt/$volume/media/tvshows","media");
  chgrp("/mnt/$volume/media/movies","media");
  chgrp("/mnt/$config_docker_volume/docker/plex","media");
  chgrp("/mnt/$config_docker_volume/docker/plex/library","media");
  chgrp("/mnt/$config_docker_volume/docker/plex/transcode","media");
  
  chmod("/mnt/$volume/media",0770);
  chmod("/mnt/$volume/media/tvshows",0770);
  chmod("/mnt/$volume/media/movies",0770);
  chmod("/mnt/$config_docker_volume/docker/plex",0770);
  chmod("/mnt/$config_docker_volume/docker/plex/library",0770);
  chmod("/mnt/$config_docker_volume/docker/plex/transcode",0770);
     
       $myFile = "/etc/samba/smb.conf";
     $fh = fopen($myFile, 'a') or die("can't open file");
     $stringData = "\n[media]\n   comment = Media files used by Plex\n   path = /mnt/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770\n\n";
     fwrite($fh, $stringData);
     fclose($fh);
  
       exec ("service samba reload");

       exec("docker run -d --name plex --net=host --restart=always -e PGID=$group_id -e PUID=0 -v /mnt/$config_docker_volume/docker/plex/library:/config -v /mnt/$volume/media/tvshows:/data/tvshows -v /mnt/$volume/media/movies:/data/movies -v /mnt/$config_docker_volume/docker/plex/transcode:/transcode linuxserver/plex");
       echo "<script>window.location = 'packages.php'</script>";
}


if(isset($_POST['install_lychee']))
{
  $volume = $_POST['volume'];
  
  exec ("addgroup photos");
  $group_id = exec("getent group photos | cut -d: -f3");

  mkdir("/mnt/$volume/photos");
  mkdir("/mnt/$config_docker_volume/docker/lychee");
  mkdir("/mnt/$config_docker_volume/docker/lychee/config");

  chgrp("/mnt/$volume/photos","photos");
  
  chmod("/mnt/$volume/photos",0770);
     
       $myFile = "/etc/samba/smb.conf";
     $fh = fopen($myFile, 'a') or die("can't open file");
     $stringData = "\n[Photos]\n   comment = Photos for Lychee\n   path = /mnt/$volume/photos\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @photos\n   force group = photos\n   create mask = 0660\n   directory mask = 0770\n\n";
     fwrite($fh, $stringData);
     fclose($fh);
  
       exec ("service samba reload");

       exec("docker run -d --name lychee -p 4560:80 --restart=always -e PGID=$group_id -v /mnt/$config_docker_volume/docker/lychee/config:/config -v /mnt/$volume/photos:/pictures linuxserver/lychee");
       echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_POST['install_nextcloud']))
{
  $volume = $_POST['volume'];

  mkdir("/mnt/$config_docker_volume/docker/nextcloud");
  mkdir("/mnt/$config_docker_volume/docker/nextcloud/appdata");
  mkdir("/mnt/$config_docker_volume/docker/nextcloud/data");
     
  exec("docker run -d --name nextcloud -p 443:443 --restart=always -v /mnt/$config_docker_volume/docker/nextcloud/appdata:/config -v /mnt/$config_docker_volume/docker/nextcloud/data:/data -v /mnt:/mnt linuxserver/nextcloud");
  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_POST['group_modify_submit']))
{
  $group_id = check_input($_POST['group_id']);
  $group_name = check_input(ucwords($_POST['group_name']));
  $security = check_input($_POST['security']);

    $sql = "UPDATE groups SET group_name = '$group_name', security = '$security' WHERE group_id = '$group_id'";

    mysql_query($sql);
    echo "
    <script>
    window.location = '$document_root/group_list.php'
  </script>";
}

if(isset($_GET['delete_group']))
{
  $group = $_GET['delete_group'];

  exec("delgroup $group");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['install_dokuwiki']))
{
  $volume = $_POST['volume'];

  mkdir("/mnt/$config_docker_volume/docker/dokuwiki/");
  mkdir("/mnt/$config_docker_volume/docker/dokuwiki/data");

       exec("docker run -d --name dokuwiki -p 8080:8080 --restart=always -v /mnt/$config_docker_volume/docker/dokuwiki/data:/dokuwiki_data bambucha/dokuwiki");
       echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_syncthing']))
{
      mkdir("/mnt/$config_docker_volume/docker/syncthing/");
      mkdir("/mnt/$config_docker_volume/docker/syncthing/config");

      exec("docker run -d --name syncthing -p 8384:8384 -p 22000:22000 -p 21027:21027/udp --restart=always -v /mnt/$config_docker_volume/docker/syncthing/config:/config -v /mnt/$config_docker_volume/homes/johnny:/mnt/johnny -e PGID=100 -e PUID=1000 linuxserver/syncthing");
      echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_unifi']))
{
      mkdir("/mnt/$config_docker_volume/docker/unifi/");
      mkdir("/mnt/$config_docker_volume/docker/unifi/config");

      exec("docker run -d --name unifi -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=always -v /mnt/$config_docker_volume/docker/unifi/config:/config linuxserver/unifi");
      echo "<script>window.location = 'packages.php'</script>";
}


if(isset($_POST['install_transmission']))
{
  $volume = $_POST['volume'];
  
  exec ("addgroup download");
  $group_id = exec("getent group download | cut -d: -f3");

  mkdir("/mnt/$volume/downloads");
  mkdir("/mnt/$config_docker_volume/docker/transmission");
  mkdir("/mnt/$config_docker_volume/docker/transmission/config");
  mkdir("/mnt/$config_docker_volume/docker/transmission/watch");

  chgrp("/mnt/$volume/downloads","download");

  chmod("/mnt/$volume/downloads",0770);
     
       $myFile = "/etc/samba/smb.conf";
     $fh = fopen($myFile, 'a') or die("can't open file");
     $stringData = "\n[downloads]\n   comment = Torrent Downloads used by Transmission\n   path = /mnt/$volume/downloads\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @download\n   force group = download\n   create mask = 0660\n   directory mask = 0770\n\n";
     fwrite($fh, $stringData);
     fclose($fh);
  
       exec ("service samba reload");

       exec("docker run -d --name transmission --restart=always -e PGID=$group_id -v /mnt/$config_docker_volume/docker/transmission/config:/config -v /mnt/$config_docker_volume/docker/transmission/watch:/watch -v /mnt/$volume/downloads:/downloads -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
       echo "<script>window.location = 'packages.php'</script>";
}


if(isset($_POST['setup']))
{
  $volume_name = $_POST['volume_name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  $hostname = $_POST['hostname'];  
  $username = $_POST['username'];
  $password = $_POST['password'];

  exec("echo $hostname > /etc/hostname");
  exec("echo '127.0.0.1     $hostname localhost.localdomain localhost' > /etc/hosts");
  exec("hostname $hostname");
  exec("service networking restart");

  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /mnt/$volume_name");
  exec ("mkfs.ext4 $hdd_part");
  exec ("mount $hdd_part /mnt/$volume_name");

  exec ("mkdir /mnt/$volume_name/docker");
  exec ("mkdir /mnt/$volume_name/homes");

  exec ("echo -e '$password\n$password' | adduser -G users $username -h /mnt/$volume_name/homes/$username");
  exec ("echo -e '$password\n$password' | smbpasswd -as $username"); 

  
  exec ("chmod -R 700 /mnt/$volume_name/homes/$username");

  exec ("rm /etc/samba/smb.conf");
  exec ("cp /www/smb.conf /etc/samba/");

  exec("service samba restart");
  
  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "$hdd_part    /mnt/$volume_name      ext4    rw,relatime,data=ordered 0 2\n";
  fwrite($fh, $stringData);
  fclose($fh);

  echo "<script>window.location = 'dashboard.php'</script>";
}

if(isset($_GET['reset']))
{
  //Stop Samba
  exec("service samba stop");

  //Remove and stop all Dockers and docker images
  exec ("docker stop $(docker ps -aq)");
  exec ("docker rm $(docker ps -aq)");
  exec ("docker rmi $(docker images -q)");
  
  //Remove all created groups
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nobody | grep -v nogroup", $group_array);
  foreach ($group_array as $group) {
    exec("delgroup $group");
  }

  //Remove all created users
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  foreach ($username_array as $username) {
    exec("deluser --remove-home $username");
    exec("smbpasswd -x $username");
  }

  //Remove all Volumes and remove from automounting on boot
  exec("ls /mnt", $volume_array);
  foreach ($volume_array as $volume) {
    $hdd = exec("findmnt -n -o SOURCE --target /mnt/$volume");
    exec ("umount /mnt/$volume");
    exec("rm -rf /mnt/$volume");
    deleteLineInFile("/etc/fstab","$hdd");
  }

  //Wipe Each Disk
  exec("smartctl --scan|awk '{ print $1 '}", $drive_list);
  foreach ($drive_list as $disk) {
    exec("wipefs -a /dev/$disk");
  }

  //Remove Samba conf and replace it with the default
  exec ("rm /etc/samba/smb.conf");
  exec ("cp /www/smb.conf /etc/samba/");

  exec("service samba start");

  echo "<script>window.location = 'dashboard.php'</script>";
}

?>