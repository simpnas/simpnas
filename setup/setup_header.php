<?php

	require_once "../config.php";
  // Check to see if setup is enabled
  if (!isset($config_enable_setup) || $config_enable_setup == 0) {
      header("Location: ../login.php");
      exit;
  }

	require_once "../includes/functions.php";

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SimpNAS | Setup</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="../plugins/bootstrap/css/bootstrap.min.css" >
    <link rel="stylesheet" type="text/css" href="../css/loader.css">
    <link rel="stylesheet" type="text/css" href="../plugins/fontawesome-free/css/all.min.css">

    <!-- Custom styles for this template -->
    <link href="../css/dashboard.css" rel="stylesheet">
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <div class="navbar-brand col-sm-3 col-md-2 mr-0"><span data-feather="box"></span> SimpNAS Setup</div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div id="cover-spin"></div>