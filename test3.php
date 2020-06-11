<?php

$ad_domain = "pittsburgh.company.int";

$ad_netbios_domain = strtoupper(strtok($ad_domain, '.'));

echo $ad_netbios_domain;

?>