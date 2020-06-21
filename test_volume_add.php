<?php 
  
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  exec("ls /volumes", $volume_array);
  foreach($volume_array as $volume){
  	exec("findmnt -n -o SOURCE --target /volumes/$volume | cut -c -8", $has_volume_disk);
  	exec("findmnt -n -o SOURCE --target / | cut -c -8", $has_volume_disk); //adds OS Drive to the array
  }
  exec("smartctl --scan | awk '{print $1}'", $drive_list);
  $not_in_use_disks_array = array_diff($drive_list, $has_volume_disk);
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="volumes.php">Volumes</a></li>
    <li class="breadcrumb-item active">Add Volume</li>
  </ol>
</nav>

  <h2>Create Volume</h2>

  <form method="post" action="post.php" autocomplete="off">
	  <div class="form-group">
	    <label>Disk</label>
	    <select class="form-control" name="disk" required>
	  		<option value=''>--Select A Drive--</option>
	  	<?php
			foreach($not_in_use_disks_array as $hdd){
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
	  </div>
	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="name" required pattern="[a-zA-Z0-9-]{1,15}">
	  </div>
	  
	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="encrypt" value="1" id="encrypt">
	    <label class="form-check-label ml-1">Encrypt</label>
	  </div>

	  <div class="form-group" id="passwordbox">
	    <label>Disk Password</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  <button type="submit" name="volume_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>