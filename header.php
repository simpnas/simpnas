<?php

if(!file_exists('config.php')){
  header("Location: setup.php");
}

session_start();

if(!$_SESSION['logged']){
  header("Location: logout.php");
  die;
}

?>

<?php include("functions.php"); ?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo gethostname(); ?></title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="plugins/bootstrap/css/bootstrap.min.css" >

    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="css/dashboard.css" >
    <link rel="stylesheet" type="text/css" href="css/loader.css">
    <link rel="stylesheet" type="text/css" href="plugins/fontawesome-free/css/all.min.css">
    
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <div class="navbar-brand col-md-3 col-lg-2 mr-0 px-3"><span data-feather="box"></span> SimpNAS <small>(<?php echo gethostname(); ?>)</small></div>
      <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
          <a class="nav-link" href="logout.php"><strong>Logout</strong> <span data-feather="log-out"></span></a>
        </li>
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div id="cover-spin"></div>