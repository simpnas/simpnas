<?php

	include("setup_header.php");
	$os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item active">Volume</li>
	  </ol>
	</nav>

	<?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>
  
  <h2>Volume Creation</h2>
  <hr>
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="volume_name" required autofocus>
	  </div>

	  <div class="form-group">
	    <label>Select Disk</label>
	    <select class="form-control" name="disk" required>
	  	<?php
			exec("smartctl --scan | awk '{print $1}'", $drive_list);
			foreach ($drive_list as $hdd) {
				if( $hdd == "$os_disk" )continue;
				$hdd_short_name = basename($hdd);
                $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family:' | awk '{print $3,$4,$5}'");
			    if(empty($hdd_vendor)){
			      $hdd_vendor = exec("smartctl -i $hdd | grep 'Device Model:' | awk '{print $3,$4,$5}'");
			    }
			    if(empty($hdd_vendor)){
			      $hdd_vendor = exec("smartctl -i $hdd | grep 'Vendor:' | awk '{print $2,$3,$4}'");
			    }
			    if(empty($hdd_vendor)){
			      $hdd_vendor = "-";
			    }
			    $hdd_serial = exec("smartctl -i $hdd | grep 'Serial Number:' | awk '{print $3}'");
			    if(empty($hdd_serial)){
			      $hdd_serial = "-";
			    }
			    $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity:' | cut -d '[' -f2 | cut -d ']' -f1");
			?>
			<option value="<?php echo $hdd; ?>"><?php echo "$hdd_short_name - $hdd_vendor ($hdd_label_size)"; ?></option>

			<?php
			}
			?>

	  	</select>
	  	<small class="form-text text-muted">This volume will home for your app configuration and Users</small>
	  </div>
	  
	  <button type="submit" name="setup_volume" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
	</form>
</main>

<?php include("footer.php"); ?>