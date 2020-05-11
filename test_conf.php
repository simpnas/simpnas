<?php
$config = include("config.php");
include("simple_vars.php");

$config_


file_put_contents('config.php', '<?php return ' . var_export($config_mount_target, true) . ';');


?>

$config_mount_target = 'mnt';
$config_docker_volume = "vol";
$config_home_volume = "vol";
$config_home_dir = 'homes';