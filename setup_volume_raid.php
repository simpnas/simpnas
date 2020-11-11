<?php

	include("setup_header.php");
	
	$os_disk = exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");
	exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v $os_disk | awk '{print $1}'", $disk_list_array);


?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item active">RAID Volume</li>
	  </ol>
	</nav>
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <h2>Volume Creation</h2>
    <a href="setup_volume.php" class="btn btn-outline-secondary">Use Simple Volume</a>
    <a href="setup_volume_raid.php" class="btn btn-outline-secondary">Rescan for new Disks</a>
  </div>

  <hr>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">

	 	<div class="form-group">
	    <label>RAID Type</label>
	    <select class="form-control" name="raid" required>
	  		<option value=''>--Select RAID--</option>
	  		<option value='0'>RAID 0 (Striping)</option>
	  		<option value='1'>RAID 1 (Mirroring)</option>
	  		<?php 
	  		if(count($disk_list_array) > 2){ ?>
	  			<option value='5'>RAID 5 (Parity)</option>
	  		<?php
	  		}
	  		?>
	  		<?php 
	  		if(count($disk_list_array) > 3){ ?>
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
				foreach($disk_list_array as $disk){
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
	  	<small class="form-text text-muted">This volume will house your apps configs and user home directories.</small>
	  </div>

	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="volume_name" pattern="[a-zA-Z0-9-_]{1,15}" required>
	  </div>
	  
	  <button type="submit" name="setup_volume_raid" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
	</form>
</main>

<?php include("footer.php"); ?>