<?php

//GET UUID of unmounted Crypt VOL
exec("ls -a /volumes/*/.uuid_map",$unmounted_crypt_ls_array);
foreach($unmounted_crypt_ls_array as $unmounted_crypt){
	exec("cat $unmounted_crypt", $unmounted_crypt_uuid_array);
}

//GET DISK name from UUID
foreach($unmounted_crypt_uuid_array as $unmounted_crypt_uuid){
	exec("lsblk -o PKNAME,NAME,UUID | grep $unmounted_crypt_uuid | awk '{print $1}'", $unmounted_crypt_disk_array);
}

print_r($unmounted_crypt_ls_array);

print_r($unmounted_crypt_uuid_array);

print_r($unmounted_crypt_disk_array);


//GET mounted Crypts

exec("lsblk -o PKNAME,NAME,TYPE | grep crypt | awk '{print $1}'", $unmounted_crypt_disk_array);

?>