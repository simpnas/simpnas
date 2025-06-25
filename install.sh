#!/bin/bash

# Exit on any error
set -e

# Require root
if [[ $(id -u) -ne 0 ]]; then
    echo "=============================================================="
    echo "Please run this script as root or with sudo: sudo ./install.sh"
    echo "=============================================================="
    exit 1
fi

echo "=================================================================================="
echo "Checking for system updates..."
echo "=================================================================================="
apt update

echo "Installing updates..."
DEBIAN_FRONTEND=noninteractive apt dist-upgrade -y

echo "=================================================================================="
echo "Installing required packages: Samba, PHP, Rsync, mdadm, cryptsetup, Docker, etc."
echo "=================================================================================="
DEBIAN_FRONTEND=noninteractive apt install -y \
    samba smbclient rsync php-cgi git mdadm cryptsetup apt-transport-https \
    curl gnupg-agent dnsutils rclone avahi-daemon \
    sudo smartmontools btrfs-progs nut

echo "=================================================================================="
echo "Installing Docker using official script..."
echo "=================================================================================="
curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh

echo "=================================================================================="
echo "Creating 'admins' group and configuring sudo access..."
echo "=================================================================================="
groupadd -f admins
echo '%admins ALL=(ALL:ALL) ALL' > /etc/sudoers.d/admins

echo "=================================================================================="
echo "Downloading simpNAS from GitHub..."
echo "=================================================================================="
cd /
rm -rf /simpnas
git clone https://github.com/simpnas/simpnas.git

echo "=================================================================================="
echo "Configuring Samba..."
echo "=================================================================================="
mv /etc/samba/smb.conf /etc/samba/smb.conf.ori || true
cp /simpnas/conf/smb.conf /etc/samba/
touch /etc/samba/shares.conf
mkdir -p /etc/samba/shares

echo "=================================================================================="
echo "Creating /volumes directory for data mounts..."
echo "=================================================================================="
mkdir -p /volumes

echo "=================================================================================="
echo "Installing and enabling simpNAS systemd service..."
echo "=================================================================================="
cp /simpnas/conf/simpnas.service /etc/systemd/system/
chmod 755 /etc/systemd/system/simpnas.service
systemctl enable simpnas
systemctl start simpnas

# Get network IP address
IP="$(ip -o -4 addr show scope global | grep -v docker | grep -v br- | awk '{print $4}' | cut -d/ -f1 | head -n1)"
HOSTNAME="$(hostname)"

echo "=================================================================================="
echo "Checking for and removing the default user (UID 1000)..."
echo "=================================================================================="
existing_username=$(awk -F: '$3 == 1000 { print $1 }' /etc/passwd)
if [[ -n "$existing_username" ]]; then
    echo "User '$existing_username' found. Deleting..."
    deluser --remove-home "$existing_username"
else
    echo "No user with UID 1000 found."
fi

echo "=================================================================================="
echo "Creating simpNAS configuration file..."
echo "=================================================================================="
password="helloSimp"
hashed_password=$(php -r "echo password_hash('$password', PASSWORD_DEFAULT);")

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
\$config_enable_setup = 1;

EOF

# Register installation
machine_id=$(cat /etc/machine-id)
curl -s "https://simpnas.com/collect.php?collect&machine_id=$machine_id" >/dev/null

echo "=================================================================================="
echo "     ALMOST THERE! Finish the setup proicess by pointing your web browser to:     "
echo "                     http://$IP:81                                                "
echo "=================================================================================="
