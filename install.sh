#!/bin/bash
echo Checking For Updates...
apt update; apt dist-upgrade -y
echo Installing additional required packages...
apt install samba smartmontools php-cgi cryptsetup git apt-transport-https apt-transport-https curl gnupg-agent software-properties-common -y
echo Install Docker Repo and latest docker
curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
add-apt-repository "deb [arch=$(dpkg --print-architecture)] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
apt-get update
apt-get install docker-ce docker-ce-cli containerd.io
echo Allowing Root Login through SSH...
sed -i 's/prohibit-password/yes/' /etc/ssh/sshd_config
cd /
echo Downloading the latest simpnas from GIT repo....
git clone https://github.com/johnnyq/simpnas.git
echo Making backup of existing smb.conf, copying new smb.conf to /etc/samba
mv /etc/samba/smb.conf /etc/samba/smb.conf.ori
cp /simpnas/conf/smb.conf /etc/samba/
touch /etc/samba/shares.conf
mkdir /etc/samba/shares
systemctl restart smbd
echo Installing and enabling simpnas service at bootup
cp /simpnas/conf/simpnas.service /etc/systemd/system/
chmod 755 /etc/systemd/system/simpnas.service
systemctl enable simpnas
echo Starting SimpNAS Web UI...
systemctl start simpnas
echo All Setup! Please reboot and then you can access your system by visiting http://$HOSTNAME in your web browser!!