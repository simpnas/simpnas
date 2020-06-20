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
	    <label>Source Volume</label>
	    <select class="form-control" name="source" required>
	  		<option value=''>--Select A Source--</option>
		  	<?php
				exec("ls /volumes | grep -v backup-", $volume_list);
				foreach ($volume_list as $volume) {
				$mounted = exec("df | grep $volume");
				if(!empty($mounted)){
			?>
				<option><?php echo "$volume"; ?></option>	
				<?php 
				} 
				?>
			<?php
			unset($volume_list);
			}
			?>

	  	</select>
	  </div>
	  <div class="form-group">
	    <label>Destination Backup Volume</label>
	    <select class="form-control" name="destination" required>
	  		<option value=''>--Select A Destination--</option>
  			<?php
			exec("find /mnt -type d -name backup-* -printf '%f\n'", $backup_volume_array);
			foreach ($backup_volume_array as $backup_volume) {
				$mounted = exec("df | grep $backup_volume");
				if(!empty($mounted)){
			?>
					<option><?php echo "$backup_volume"; ?></option>	
				<?php 
				} 
				?>
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