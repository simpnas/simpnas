<?php

$num = "/dev/md128";

$new = preg_replace('/[^0-9]/', '', $num);

echo $new+1;

?>