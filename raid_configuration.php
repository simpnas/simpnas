<?php 
  
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  if(isset($_GET['raid'])){
		$raid = $_GET['raid'];
	}

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
      <li class="breadcrumb-item"><a href="volumes.php">Volumes</a></li>
      <li class="breadcrumb-item active">RAID Array</li>
    </ol>
  </nav>

  <h2>RAID Array Configuration</h2>
  <hr>
  <?php

    $raid_configuration = shell_exec("mdadm -D /dev/$raid");

    echo "<pre>$raid_configuration</pre>";

  ?>

</main>

<?php include("footer.php"); ?>