<?php

if(!file_exists('config.php')){
  header("Location: setup.php");
}

session_start();

$startScriptTime=microtime(TRUE);

?>

<?php include("functions.php"); ?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.20/datatables.min.css"/>
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="index.php"><span data-feather="box"></span> SimpNAS <small>(<?php echo gethostname(); ?>)</small></a>
      <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
          <a class="nav-link" href="login.php">Logout <span data-feather="log-out"></span></a>
        </li>
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">