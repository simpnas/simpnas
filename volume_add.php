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

	//RAID
  exec("ls /volumes", $volume_array);
  exec("lsblk -n -o PKNAME,TYPE | grep raid | awk '{print $1}'", $raids_array);
  foreach($raids_array as $raid){
  	exec("lsblk -n -o PKNAME,PATH | grep /dev/$raid | awk '{print $1}'", $has_volume_disk_array);
  }
  
  //SIMPLE VOLUMES

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
	    <li class="breadcrumb-item active">Add Volume</li>
	  </ol>
	</nav>

<?php
if(count($not_in_use_disks_array) > 0){ 
?>

	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <h2>Create Volume</h2>
    <a href="volume_add.php" class="btn btn-outline-secondary">Rescan for new Disks</a>
  </div>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
	  
	  <div class="form-group">
	    <label>Disk</label>
	    <select class="form-control" name="disk" required>
	  		<option value=''>--Select Disk--</option>
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
				<option value="<?php echo $disk; ?>"><?php echo "$disk - $disk_vendor ($disk_size"."B)"; ?></option>

				<?php
				}
				?>

	  	</select>
	  </div>

	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="name" pattern="[a-zA-Z0-9-_]{1,15}" required>
	  </div>

	  <div class="form-group">
	  	<div class="custom-control custom-checkbox">
			  <input type="checkbox" class="custom-control-input" name="encrypt" value="1" id="encrypt">
			  <label class="custom-control-label" for="encrypt">Encrypt</label>
			</div>
	  </div>
	  
	  <div class="form-group" id="passwordbox">
	    <label>Disk Password</label>
	    <input type="password" class="form-control" name="password" data-toggle="password" autocomplete="new-password">
	  </div>

	  <button type="submit" name="volume_add" class="btn btn-primary">Submit</button>
	
	</form>

<?php
}else{
?>
<h2 class="text-secondary mt-5 text-center">You must add another disk to create a new volume</h2>
<?php
} 
?>

</main>

<?php include("footer.php"); ?>