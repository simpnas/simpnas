<?php 
  
require_once "includes/include_all.php";
  
exec("ls /volumes", $volume_array);

foreach($volume_array as $volume){
	exec("lsblk -n -o pkname,mountpoint | grep -w volumes | awk '{print $1}'", $has_volume_disk_array);
	exec("lsblk -n -o pkname,mountpoint | grep -w / | awk '{print $1}'", $has_volume_disk_array); //adds OS Drive to the array
}

exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | awk '{print $1}'", $disk_list_array);

$not_in_use_disks_array = array_diff($disk_list_array, $has_volume_disk_array);

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
	<nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item"><a href="volumes.php">Volumes</a></li>
	    <li class="breadcrumb-item active">Add Backup Volume</li>
	  </ol>
	</nav>

  <h2>Add Backup Volume</h2>

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
	 
	  <label>Volume Name</label>
	 	<div class="input-group mb-3">
		  <div class="input-group-prepend">
		    <span class="input-group-text" id="basic-addon3">backup-</span>
		  </div>
		  <input type="text" class="form-control" name="volume_name" required pattern="[a-zA-Z0-9-]{1,15}">
		</div>  

	  <button type="submit" name="volume_add_backup" class="btn btn-primary">Submit</button>
	
	</form>

</main>

<?php require_once "includes/footer.php";
