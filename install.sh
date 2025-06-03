#!/bin/bash
if [[ $(id -u) -ne 0 ]] ; 
	then
	echo "=========================================================" ;
	echo "Enter root password and then rerun install.sh to continue" ; 
	echo "=========================================================" ;
	su ;
	exit 1 ; 
fi
echo "=================================================================================="
echo "Checking For Updates..."
echo "=================================================================================="
apt update
echo "=================================================================================="
echo "Installing Updates..."
echo "=================================================================================="
DEBIAN_FRONTEND=noninteractive \apt dist-upgrade -y
echo "=================================================================================="
echo "Installing Additional Required Packages..."
echo "Samba, PHP, Rsync, mdadm (RAID), cryptsetup (LUKS Encryption) etc"
echo "=================================================================================="
DEBIAN_FRONTEND=noninteractive \apt install samba smbclient rsync php-cgi git mdadm cryptsetup apt-transport-https curl gnupg-agent software-properties-common dnsutils rclone avahi-daemon sudo smartmontools btrfs-progs -y
echo "================================================================================="
echo "Install Docker Repo"
echo "================================================================================="
curl -fsSL get.docker.com -o get-docker.sh && sh get-docker.sh
echo "=================================================================================="
echo "Adding group admins and adding the group to the sudoers allow list                "
echo "=================================================================================="
groupadd admins
echo '%admins   ALL=(ALL:ALL) ALL' > /etc/sudoers.d/admins
echo "=================================================================================="
echo "Allowing SSH Root Login..."
echo "=================================================================================="
#sed -i 's/prohibit-password/yes/' /etc/ssh/sshd_config
cd /
echo "=================================================================================="
echo "Downloading the Latest simpNAS from GIT repo..."
echo "=================================================================================="
git clone https://github.com/simpnas/simpnas.git
echo "=================================================================================="
echo "Setting up Samba configuration"
echo "=================================================================================="
mv /etc/samba/smb.conf /etc/samba/smb.conf.ori
cp /simpnas/conf/smb.conf /etc/samba/
touch /etc/samba/shares.conf
mkdir /etc/samba/shares
echo "=================================================================================="
echo "Creating Volumes Directory for Volume mounts"
echo "=================================================================================="
mkdir /volumes
echo "=================================================================================="
echo "Installing and Enabling SimpNAS Service during Startup..."
echo "=================================================================================="
cp /simpnas/conf/simpnas.service /etc/systemd/system/
chmod 755 /etc/systemd/system/simpnas.service
systemctl enable simpnas
systemctl start simpnas
IP="$(ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||')";
HOSTNAME="$(hostname)";
echo "=================================================================================="
echo "Removing any additonal users"
echo "=================================================================================="
# Check if a user with UID 1000 exists (typically the first user created on Linux systems)
existing_username=$(cat /etc/passwd | grep ':1000:' | awk -F: '{print $1}')
# If the username exists, delete the user and remove their home directory
if [ ! -z "$existing_username" ]; then
    echo "User $existing_username found. Deleting user and home directory..."
    sudo deluser --remove-home "$existing_username"
else
    echo "No user with UID 1000 found."
fi
echo "=================================================================================="
echo "Creating Config File"
echo "================================================================================="
# Define the password
password="helloSimp"
# Use PHP to hash the password and store the result in a variable
hashed_password=$(php -r "echo password_hash('$password', PASSWORD_DEFAULT);")
# Create the PHP config file
cat <<EOF > /simpnas/config.php
<?php

\$config_admin_password = '$hashed_password';
\$config_smtp_server = '';
\$config_smtp_port = '';
\$config_smtp_username = '';
\$config_smtp_password = '';
\$config_mail_from = '';
\$config_mail_to = '';
\$config_theme = '';
\$config_audit_logging = 0;
\$config_enable_beta = 0;

EOF
# Get Instance Count
machine_id=$(cat /etc/machine-id)
# Send the GET request using curl
curl "https://simpnas.com/collect.php?collect&machine_id=$machine_id"
echo "==============================================================================================================================="
echo "                                                   Almost There!																                               "
echo "               Visit http://$IP in your web browser to complete installation								 	                                 "
echo "==============================================================================================================================="