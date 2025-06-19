<?php

require_once "setup_header.php";
	
$os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");
$network_int_file = exec("ls /etc/systemd/network");
$dhcp_set = exec("cat /etc/systemd/network/$network_int_file | grep DHCP");

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item"><a href="setup_volume.php">Volume</a></li>
	    <li class="breadcrumb-item active">Admin Password</li>
	  </ol>
	</nav>
  
  <h2>Admin Password</h2>
  <hr>

  <?php include("../alert_message.php"); ?>
  
  <form method="post" action="../post.php" autocomplete="off">

	  <div class="form-group">
	    <label>WebUI Password</label>
	    <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
	  </div>

	  <div class="form-group">
	  	<div class="custom-control custom-checkbox">
			  <input type="checkbox" class="custom-control-input" name="collect" value="1" id="collect">
			  <label class="custom-control-label" for="collect">Yes Collect Statistic Data</label>
				<small class="form-text text-muted">This will collect a Unique Machine ID and used as an install count on our website.</small>
			</div>
	  </div>
	  
	  <button type="submit" name="setup_final" class="btn btn-primary">Finish and Reboot <span data-feather="check"></span></button>
	</form>

<?php require_once "setup_footer.php";