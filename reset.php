<?php 
  
  require_once "config.php";
  require_once "includes/simple_vars.php";
  require_once "includes/header.php";

?>

<main class="col-md-12 pt-5">

  <center>
  	<h1 class="text-danger">Deleting all Data, Configuration and Resetting SimpNAS back to Factory Defaults!</h1>
  	<h3>Redirecting to setup page in <span id="countdown">45</span> seconds</h3>
  </center>

</main>

<script>
    
  // Total seconds to wait
  var seconds = 45;
  
  function countdown() {
    seconds = seconds - 1;
    if (seconds < 0) {
        // Chnage your redirection link here
        window.location = "index.php";
    } else {
        // Update remaining seconds
        document.getElementById("countdown").innerHTML = seconds;
        // Count down using javascript
        window.setTimeout("countdown()", 1000);
    }
  }

  // Run countdown function
  countdown();

</script>

<?php require_once "includes/footer.php"; ?>

<?php
  //Stop Samba
  exec("systemctl stop smbd");
  exec("systemctl stop nmbd");

  //Remove and stop all Dockers and docker images
  exec ("docker stop $(docker ps -aq)");
  exec ("docker rm $(docker ps -aq)");
  exec ("docker rmi $(docker images -q)");

  //Remove all created groups
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup | grep -v admins", $group_array);
  foreach ($group_array as $group) {
    exec("delgroup $group");
  }

  //Remove all created users
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody | grep -v admins", $username_array);
  foreach ($username_array as $username) {
    exec("smbpasswd -x $username");
    exec("deluser --remove-home $username");
  }

  //Remove all Volumes and remove from fstab.conf to prevent automounting on boot  
  exec("ls /volumes", $volume_array);
  foreach ($volume_array as $volume) {
    exec("rm -rf /volumes/$volume/*");
    exec ("umount /volumes/$volume");
    deleteLineInFile("/etc/fstab","$volume");
  }
  exec("rm -rf /volumes/*");

  //Wipe Each Disk
  $os_disk = exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");
  exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v $os_disk | awk '{print $1}'", $disk_list_array);
  foreach ($disk_list_array as $disk) {
    exec("wipefs -a /dev/$disk");
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

  exec("sleep 1 && reboot > /dev/null &");
