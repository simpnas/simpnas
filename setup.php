<?php
	include("setup_header.php");

	$current_time_zone = exec("timedatectl show -p Timezone --value");
  //$current_local_date = exec("timedatectl show -p TimeUSec --value | awk '{print $2}'");
  //$current_local_time = exec("timedatectl show -p TimeUSec --value | awk '{print $3}'");
  exec("timedatectl list-timezones", $timezones_array);
?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item active">Timezone</li>
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
  
  <h2>Timezone Configuration</h2>
  <hr>
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <select class="form-control" name="timezone" required>
	    	<?php
	    	foreach ($timezones_array as $timezone) {
	    	?>
	    	<option <?php if($current_time_zone === $timezone){ echo "selected"; } ?> ><?php echo $timezone; ?></option>
	    	<?php
	    	}
	    	?>
	    </select>
	  </div>
	  
	  <button type="submit" name="setup_timezone" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
	</form>
</main>

<?php include("footer.php"); ?>