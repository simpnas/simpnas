<?php 
  
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  //CRYPT UNMOUNTED
	exec("ls -a /volumes/*/.uuid_map",$unmounted_crypt_ls_array);
	foreach($unmounted_crypt_ls_array as $unmounted_crypt){
		exec("cat $unmounted_crypt", $unmounted_crypt_uuid_array);
	}

	foreach($unmounted_crypt_uuid_array as $unmounted_crypt_uuid){
		exec("lsblk -o PKNAME,NAME,UUID | grep $unmounted_crypt_uuid | awk '{print $1}'", $has_volume_disk_array);
	}

	//CRYPT MOUNTED
	exec("lsblk -o PKNAME,NAME,TYPE | grep crypt | awk '{print $1}'", $mounted_crypt_diskparts_array);
	foreach($mounted_crypt_diskparts_array as $mounted_crypt_diskpart){
		exec("lsblk -o PKNAME,NAME | grep $mounted_crypt_diskpart | awk '{print $1}'", $has_volume_disk_array);
	}

  exec("ls /volumes", $volume_array);

  exec("lsblk -n -o PKNAME,TYPE | grep raid | awk '{print $1}'", $raids_array);
  foreach($raids_array as $raid){
  	exec("lsblk -n -o PKNAME,PATH | grep /dev/$raid | awk '{print $1}'", $has_volume_disk_array);
  }
  
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
	    <li class="breadcrumb-item active">Create RAID Volume</li>
	  </ol>
	</nav>

<?php
if(count($not_in_use_disks_array) > 1){ 
?>

	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <h2>Create Volume</h2>
    <a href="volume_add_raid.php" class="btn btn-outline-secondary">Rescan for new Disks</a>
  </div>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
	  
	  <div class="form-group">
	    <label>RAID Type</label>
	    <select class="form-control" name="raid" required>
	  		<option value=''>--Select RAID--</option>
	  		<option value='0'>RAID 0 (Striping)</option>
	  		<option value='1'>RAID 1 (Mirroring)</option>
	  		<?php 
	  		if(count($not_in_use_disks_array) > 2){ ?>
	  			<option value='5'>RAID 5 (Parity)</option>
	  		<?php
	  		}
	  		?>
	  		<?php 
	  		if(count($not_in_use_disks_array) > 3){ ?>
	  			<option value='6'>RAID 6 (Double Parity)</option>
	  			<option value='10'>RAID 10 (Mirror / Sripe)</option>
	  		<?php
	  		}
	  		?>
	  	</select>
	  </div>
	  
	  <div class="form-group">
	    <label>Select Disks</label>
	    
			  	<?php
				foreach($not_in_use_disks_array as $disk){
		        $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Model Family:' | awk '{print $3,$4,$5}'");
				  if(empty($disk_vendor)){
				    $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Device Model:' | awk '{print $3,$4,$5}'");
				  }
				  if(empty($disk_vendor)){
            $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Model Number:' | awk '{print $3,$4,$5,$6}'");
          }
				  if(empty($disk_vendor)){
				    $disk_vendor = exec("lsblk -n -o kname,type,vendor /dev/$disk | grep disk  | awk '{print $3}'");
				  }
				  if(empty($disk_vendor)){
				    $disk_vendor = exec("lsblk -n -o kname,type,model /dev/$disk | grep disk  | awk '{print $3}'");
				  }
			    $disk_serial = exec("lsblk -n -o kname,type,serial /dev/$disk | grep disk  | awk '{print $3}'");
			    $disk_size = exec("lsblk -n -o kname,type,size /dev/$disk | grep disk | awk '{print $3}'");
				?>
				
			  <div class="form-group form-check">
			    <input type="checkbox" class="form-check-input" name="disks[]" value="<?php echo "$disk"; ?>">
			    <label class="form-check-label ml-1"><?php echo "$disk - $disk_vendor ($disk_size"."B)"; ?></label>
				</div>

				<?php
				}
				?>

	  	</select>
	  	<small class="form-text text-danger"><strong>Warning:</strong> This will <u>Delete</u> all Data on the selected Storage Devices.</small>
	  </div>

	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="name" pattern="[a-zA-Z0-9-_]{1,15}" required>
	  </div>

	  <button type="submit" name="volume_add_raid" class="btn btn-primary">Submit</button>
	
	</form>

<?php
}else{
?>
<h2 class="text-secondary mt-5 text-center">You must have at lease 2 disks available<br>to create a RAID volume</h2>
<?php
} 
?>

</main>

<?php include("footer.php"); ?>