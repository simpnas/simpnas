<?php 
  
require_once "includes/include_all.php";

if(isset($_GET['hdd'])){
	$hdd = $_GET['hdd'];
}

?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="disks.php">Disks</a></li>
    <li class="breadcrumb-item active">Disk Info</li>
  </ol>
</nav>

<h2>Disk <?php echo $hdd; ?> Info</h2>
<?php

  $hdd_info = shell_exec("smartctl -i /dev/$hdd | grep -v 'smartctl' | grep -v 'Copyright' | grep -v '=== START'");
  $hdd_attributes = shell_exec("smartctl -A /dev/$hdd | grep -v 'smartctl' | grep -v 'Copyright' | grep -v '=== START' | grep -v 'revision number' | grep -v 'Vendor Specific SMART'");

  echo "<pre>$hdd_info</pre>";
  echo "<pre>$hdd_attributes</pre>";

?>

<?php require_once "includes/footer.php";
