<?php 
  
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php"); 

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="backups.php">Backups</a></li>
    <li class="breadcrumb-item active">Add Backup</li>
  </ol>
</nav>

  <h2>Add Backup Task</h2>

  <form method="post" action="post.php" autocomplete="off">
	  <div class="form-group">
	    <label>Select shares to backup</label>
	  </div>
	    
		  	<?php
				exec("ls /volumes", $volume_list);
				foreach ($volume_list as $volume) {
				$mounted = exec("df | grep $volume");
				if(!empty($mounted)){
				?>
				<label><span data-feather="database"></span> <?php echo "$volume"; ?></label>
			  <?php
				exec("ls /volumes/$volume | grep -v 'lost+found'", $dir_list);
				foreach($dir_list as $dir){
				?>
				<div class="form-group form-check ml-4">
			    <input type="checkbox" class="form-check-input" name="backup_source[]" value="<?php echo "/volumes/$volume/$dir"; ?>" id="check<?php echo $dir; ?>">
			    <label class="form-check-label" for="check<?php echo $dir; ?>"><?php echo "$dir"; ?></label>
			  </div>
				<?php 
				} 
				?>
			<?php
			unset($volume_list);
			unset($dir_list);
			}
		}
			?>

	  <div class="form-group">
	    <label>Backup Destination</label>
	    <select class="form-control" name="destination" required>
	  		<option value=''>--Select A Destination--</option>
  			<?php
			exec("ls /dev/disk/by-uuid",$connected_uuid_array);
			exec("ls /mnt/backup--* | awk -f:-- '{print $2}'",$backup_volume_uuid_array);
			foreach ($backup_volume_uuid_array as $backup_volume_uuid) {
				if(in_array($backup_volume_uuid, $connected_uuid_array)){
					$connection_status = "Connected";
				}else{
					$connection_status = "Disconnected";
 				}
			?>
					<option><?php echo "$backup_volume_uuid - $connection_status"; ?></option>
			<?php
			}
			?>
	  	</select>
	  </div>
	  <div class="form-group">
	    <label>Occurance</label>
	    <select class="form-control" name="occurance" required>
	  		<option value=''>--Select a Time--</option>
	  		<option value='hourly'>Hourly</option>
	  		<option value='daily'>Daily</option>
	  		<option value='weekly'>Weekly</option>
	  		<option value='monthly'>Monthly</option>
	  	</select>
	  </div>
	  <button type="submit" name="backup_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>