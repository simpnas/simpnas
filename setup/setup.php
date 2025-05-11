<?php
	
require_once "setup_header.php";

$current_time_zone = exec("timedatectl show -p Timezone --value");
exec("timedatectl list-timezones", $timezones_array);

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item active">Timezone</li>
	  </ol>
	</nav>
  
  <h2>Timezone Configuration</h2>
  <hr>

  <?php include("../alert_message.php"); ?>
  
  <form method="post" action="../post.php" autocomplete="off">

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

<?php require_once "setup_footer.php";
