<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
    <li class="breadcrumb-item active">Install Wireguard</li>
  </ol>
</nav>

  <h2>Install Config</h2>
  

<img src='post.php?wireguard_qr&peer=1'>


</main>

<?php include("footer.php"); ?>
