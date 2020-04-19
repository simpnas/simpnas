<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");

    $current_time_zone = exec("timedatectl | grep zone | awk '{print $3}'");
    exec("timedatectl list-timezones", $timezones_array);
    

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
	<?php echo $current_time_zone; ?>
  <h2>Date and Time</h2>
  <div class="alert alert-danger">Work In Progress! Not Functioning!</div>
  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Timezone:</label>
	    <select class="form-control" name="timezone">
	    	<?php
	    	foreach ($timezones_array as $timezone) {
	    	?>
	    	<option <?php if($current_time_zone === $timezone){ echo "selected"; } ?> ><?php echo $timezone; ?></option>
	    	<?php
	    	}
	    	?>
	    </select>
	  </div>
	  <div class="form-group">
	    <label>Date:</label>
	    <input type="date" class="form-control" name="date">
	  </div>
	  
	  <div class="form-group">
	    <label>Time:</label>
	    <input type="time" class="form-control" name="time">
	  </div>
	  <button type="submit" name="datetime_update" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>