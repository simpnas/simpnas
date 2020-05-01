#!/bin/bash
echo ==================================================================================
echo Checking For Updates...
echo ==================================================================================
apt update
echo ==================================================================================
echo Installing Updates...
echo ==================================================================================
apt dist-upgrade -y
echo ==================================================================================
echo Installing Additional Required Packages...
echo Samba, PHP, SMARTmonTools, Rsync, mdadm (RAID) etc
echo ==================================================================================
apt install samba smartmontools rsync php-cgi cryptsetup git mdadm apt-transport-https apt-transport-https curl gnupg-agent software-properties-common curl -y
echo Install Docker Repo and latest docker
curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
add-apt-repository "deb [arch=$(dpkg --print-architecture)] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
apt-get update
apt-get install docker-ce docker-ce-cli containerd.io
echo ==================================================================================
echo Allowing SSH Root Login...
echo ==================================================================================
sed -i 's/prohibit-password/yes/' /etc/ssh/sshd_config
cd /
echo ==================================================================================
echo Downloading the Latest SimpNAS from GIT repo...
echo ==================================================================================
git clone https://github.com/johnnyq/simpnas.git
echo ==================================================================================
echo Setting up Samba configuration
echo ==================================================================================
mv /etc/samba/smb.conf /etc/samba/smb.conf.ori
cp /simpnas/conf/smb.conf /etc/samba/
touch /etc/samba/shares.conf
mkdir /etc/samba/shares
echo ==================================================================================
echo Installing and Enabling SimpNAS Service during Startup...
echo ==================================================================================
cp /simpnas/conf/simpnas.service /etc/systemd/system/
chmod 755 /etc/systemd/system/simpnas.service
systemctl enable simpnas
echo ==================================================================================
echo                                  Almost There! 
Your                     Your system will now reboot in 3 seconds!
echo  Visit http://$HOSTNAME in your web browser to complete installation after reboot
echo ==================================================================================
sleep 3
reboot