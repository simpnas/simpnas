<?php 
  
require_once "includes/include_all.php";

if(isset($_GET['backup'])){
  $backup = $_GET['backup'];
  $occurance = $_GET['occurance'];
  $source = explode("--",$backup)[1];
  $destination = explode("--",$backup)[2];
}

?>

<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="backups.php">Backups</a></li>
    <li class="breadcrumb-item active">Edit Backup</li>
  </ol>
</nav>

<h2>Edit Backup Task</h2>

<form method="post" action="post.php" autocomplete="off">
  <input type="hidden" name="current_backup" value="<?php echo $backup; ?>">
  <input type="hidden" name="current_occurance" value="<?php echo $occurance; ?>">

  <div class="form-group">
    <label>Source Volume</label>
    <select class="form-control" name="source" required>
  		<option value=''>--Select A Source--</option>
	  	<?php
		exec("ls /volumes", $volume_list);
		foreach ($volume_list as $volume) {
			$mounted = exec("df | grep $volume");
			if(!empty($mounted)){	
		?>
				<option <?php if($source == $volume){ echo "selected"; } ?>><?php echo "$volume"; ?></option>	

			<?php
				unset($volume_list);
			}
			?>
		<?php
		}
		?>
  	</select>
  </div>
  <div class="form-group">
    <label>Destination Volume</label>
    <select class="form-control" name="destination" required>
  		<option value=''>--Select A Destination--</option>
			<?php
		exec("ls /volumes", $volume_list);
		foreach ($volume_list as $volume) {
			$mounted = exec("df | grep $volume");
			if(!empty($mounted)){	
		?>
				<option <?php if($destination == $volume){ echo "selected"; } ?>><?php echo "$volume"; ?></option>	

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
  		<option <?php if($occurance == 'hourly'){ echo "selected"; } ?> value='hourly'>Hourly</option>
  		<option <?php if($occurance == 'daily'){ echo "selected"; } ?> value='daily'>Daily</option>
  		<option <?php if($occurance == 'weekly'){ echo "selected"; } ?> value='weekly'>Weekly</option>
  		<option <?php if($occurance == 'monthly'){ echo "selected"; } ?> value='monthly'>Monthly</option>
  	</select>
  </div>
  <button type="submit" name="backup_edit" class="btn btn-primary">Submit</button>
</form>

<?php require_once "includes/footer.php";
