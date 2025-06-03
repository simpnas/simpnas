<?php 
    
require_once "includes/include_all.php";

$current_time_zone = exec("timedatectl show -p Timezone --value");
$current_local_date = exec("timedatectl show -p TimeUSec --value | awk '{print $2}'");
$current_local_time = exec("timedatectl show -p TimeUSec --value | awk '{print $3}'");
exec("timedatectl list-timezones", $timezones_array);

?>
  
<h2>Date and Time</h2>

<form method="post" action="post.php" autocomplete="off">
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
    <label>Local Date:</label>
    <input type="date" class="form-control" name="date" value="<?php echo $current_local_date; ?>" disabled>
  </div>
  
  <div class="form-group">
    <label>Local Time:</label>
    <input type="time" class="form-control" name="time" value="<?php echo $current_local_time; ?>" disabled>
  </div>
  
  <button type="submit" name="datetime_update" class="btn btn-primary">Submit</button>
</form>

<?php require_once "includes/footer.php";
