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
DEBIAN_FRONTEND=noninteractive \apt install samba rsync php-cgi git mdadm cryptsetup apt-transport-https curl gnupg-agent software-properties-common dnsutils -y
echo "================================================================================="
echo "Install Docker Repo"
echo "================================================================================="
curl -fsSL https://download.docker.com/linux/$(lsb_release -s -i | tr '[:upper:]' '[:lower:]')/gpg | apt-key add -
add-apt-repository "deb [arch=$(dpkg --print-architecture)] https://download.docker.com/linux/$(lsb_release -s -i | tr '[:upper:]' '[:lower:]') $(lsb_release -cs) stable"
#Check to see if its a standard Debian install or Armbian if its Debian then add the backports REPO as this repo is already added on Armbian by default.
if [ ! -f /etc/armbian-release ]; then
  add-apt-repository "deb http://deb.debian.org/debian buster-backports main contrib non-free"
fi
apt update
echo "=================================================================================="
echo "Installing Backport version of SMARTmonTools"
echo "=================================================================================="
DEBIAN_FRONTEND=noninteractive \apt -t buster-backports install smartmontools -y
echo "================================================================================="
#apt install docker-ce docker-ce-cli containerd.io -y
#apt install docker.io -y
#echo "=================================================================================="
#echo "Creating docker network"
#echo "=================================================================================="
#docker network create my-network
echo "=================================================================================="
echo "Adding group admins and adding the group to the sudoers allow list                "
echo "=================================================================================="
groupadd admins
echo '%admins   ALL=(ALL:ALL) ALL' > /etc/sudoers.d/admins
echo "=================================================================================="
echo "Allowing SSH Root Login..."
echo "=================================================================================="
sed -i 's/prohibit-password/yes/' /etc/ssh/sshd_config
cd /
echo "=================================================================================="
echo "Downloading the Latest SimpNAS from GIT repo..."
echo "=================================================================================="
git clone https://github.com/johnnyq/simpnas.git
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
echo "==============================================================================================================================="
echo "                                                   Almost There!																                               "
echo "                    		Visit http://$IP:81 in your web browser to complete installation								 	                     "
echo "==============================================================================================================================="