<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");

    if(isset($_GET['hdd'])){
  		$hdd = $_GET['hdd'];
  	}

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="disks.php">Disks</a></li>
    <li class="breadcrumb-item active">Disk Info</li>
  </ol>
</nav>



<h2>Disk <?php echo $hdd; ?> Info</h2>
<?php

  $cmd = "smartctl -a /dev/$hdd";

  $output = shell_exec($cmd);

  echo "<pre>$output</pre>";

?>


</main>

<?php include("footer.php"); ?>