<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    //include("side_nav.php");
?>

 <main class="col-md-12 pt-5">

<center>
	<h1 class="text-danger">Deleting all Data, Configuration and Resetting SimpNAS back to Factory Defaults!</h1>
	<h3>Redirecting to setup page in <span id="countdown">25</span> seconds</h3>
</center>

</main>

<?php ?>

<!-- JavaScript part -->
<script type="text/javascript">
    
    // Total seconds to wait
    var seconds = 25;
    
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

<?php include("footer.php"); ?>

<?php
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

    exec("shutdown -r");
?>