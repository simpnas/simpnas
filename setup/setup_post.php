<?php

session_start();

include "../config.php";
require_once "../includes/simple_vars.php";
require_once "../includes/functions.php";

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
    echo "<script>window.location = 'http://$primary_ip:81/setup/setup_volume.php'</script>";
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
    echo "<script>window.location = 'http://$address:81/setup/setup_volume.php'</script>";
  }
}

if (isset($_POST['setup_volume'])) {
    $volume_name = $_POST['volume_name'];
    $raid = $_POST['raid'] ?? '';
    $disk_array = $_POST['disks'] ?? [];

    $num_of_disks = count($disk_array);

    if ($raid && $num_of_disks >= 2) {
        // RAID logic
        foreach ($disk_array as $rdisk) {
            exec("mdadm --stop --scan");
            exec("mdadm --zero-superblock --force /dev/$rdisk");
            exec("wipefs -a --force /dev/$rdisk");
        }

        $prefixed_array = preg_filter('/^/', '/dev/', $disk_array);
        $disks = implode(' ', $prefixed_array);

        // Find the lowest unused md number
        $new_md_num = 0;
        while (file_exists("/dev/md$new_md_num")) {
            $new_md_num++;
        }

        exec("yes | mdadm --create --verbose /dev/md$new_md_num --level=$raid --raid-devices=$num_of_disks --metadata=1.2 --force $disks");

        exec("mkdir -p /volumes/$volume_name");

        exec("mkfs.btrfs -f -L $volume_name /dev/md$new_md_num");
        exec("mount /dev/md$new_md_num /volumes/$volume_name");
        $uuid = exec("blkid -o value --match-tag UUID /dev/md$new_md_num");
        $fstab_entry = "UUID=$uuid /volumes/$volume_name btrfs defaults 0 0\n";
        file_put_contents("/etc/fstab", $fstab_entry, FILE_APPEND);

        exec("mdadm --detail --scan | tee -a /etc/mdadm/mdadm.conf");

    } elseif ($num_of_disks === 1) {
        // Single disk logic
        $disk = $disk_array[0];

        exec("wipefs -a /dev/$disk");
        exec("(echo g; echo n; echo p; echo 1; echo; echo; echo w) | fdisk /dev/$disk");

        $diskpart = exec("lsblk -o PKNAME,KNAME,TYPE /dev/$disk | grep part | awk '{print \$2}'");
        exec("mdadm --zero-superblock /dev/$diskpart");

        exec("mkdir -p /volumes/$volume_name");

        exec("mkfs.btrfs -f -L $volume_name /dev/$diskpart");
        exec("mount /dev/$diskpart /volumes/$volume_name");
        $uuid = exec("blkid -o value --match-tag UUID /dev/$diskpart");
        $fstab_entry = "UUID=$uuid /volumes/$volume_name btrfs defaults 0 0\n";
        file_put_contents("/etc/fstab", $fstab_entry, FILE_APPEND);
    }

    // Redirect after setup
    header("Location: setup_final.php");
    exit;
}

if(isset($_POST['setup_final'])){
  $volume_name = exec("ls /volumes");
  $password = $_POST['password'];
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $configFile = '../config.php';

  // Read current configuration
  $configContent = file_get_contents($configFile);

  $updatedContent = preg_replace_callback(
    "/(\\\$config_admin_password\s*=\s*)['\"].*?['\"];/",
    function($matches) use ($hashedPassword) {
          return $matches[1] . "'" . $hashedPassword . "';";
      },
      $configContent
  );

  file_put_contents($configFile, $updatedContent);

  // Replace $config_enable_setup if it already exists (set it to 0)
    $updatedContent = preg_replace(
        "/(\\\$config_enable_setup\s*=\s*)\d+;/",
        "$1 0;",
        $updatedContent
    );

    // Write the updated content back to config.php
    file_put_contents($configFile, $updatedContent);

  $network_int_file = exec("ls /etc/systemd/network");
  $network_int = exec("ls /etc/systemd/network | awk -F'.' '{print $1}'");

  exec ("mkdir /volumes/$volume_name/docker");
  exec ("mkdir /volumes/$volume_name/users");
  exec ("mkdir /volumes/$volume_name/share");
  exec ("chmod 770 /volumes/$volume_name/share");
  
  $myFile = "/etc/samba/shares/share";
  $fh = fopen($myFile, 'w') or die("not able to write to file");
  $stringData = "[share]\n   comment = Shared files\n   path = /volumes/$volume_name/share\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @users @admins\n   force group = users\n   create mask = 0660\n   directory mask = 0770";
  fwrite($fh, $stringData);
  fclose($fh);

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

  exec("systemctl restart smbd nmbd");

  if($collect = 1){
    exec("curl https://simpnas.com/collect.php?'collect&machine_id='$(cat /etc/machine-id)''");
  }

  header("Location: ../login.php");
}