<?php

include "config.php";
// Check to see if setup is enabled
if (!isset($config_enable_setup) || $config_enable_setup == 1) {
    header("Location: setup/");
    exit;
}
require_once "simple_vars.php";
require_once "functions.php";
require_once "header.php";
require_once "side_nav.php";
require_once "content_wrapper.php";
require_once "alert_message.php";