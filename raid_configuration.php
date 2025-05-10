<?php 
  
require_once "includes/include_all.php";

if(isset($_GET['raid'])){
	$raid = $_GET['raid'];
}

?>

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

<?php require_once "includes/footer.php";
