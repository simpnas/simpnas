<?php 
	
	$config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  if(isset($_GET['share'])){
		$share = $_GET['share'];
	}
  
  $shareConfigArray = parse_ini_file("/etc/samba/shares/$share");
  $path = $shareConfigArray['path'];
  $mounted_volume = basename(dirname($path));
  $comment = $shareConfigArray['comment'];
  $share_group = $shareConfigArray['force group'];
  $read_only = $shareConfigArray['read only'];

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item"><a href="shares.php">Shares</a></li>
	    <li class="breadcrumb-item active">Edit Share</li>
	  </ol>
	</nav>

  <h2>Edit Share</h2>

  <?php include("alert_message.php"); ?>
 
  <form method="post" action="post.php" autocomplete="off">

  	<input type="hidden" name="current_name" value="<?php echo $share; ?>">
	  <input type="hidden" name="current_volume" value="<?php echo $mounted_volume; ?>">
	  <input type="hidden" name="current_description" value="<?php echo $description; ?>">
	  <input type="hidden" name="current_group" value="<?php echo $share_group; ?>">

	  <div class="form-group">
	    <label>Share Name</label>
	    <input type="text" class="form-control" name="name" value="<?php echo $share; ?>" required pattern="[a-zA-Z0-9-]{1,25}">
	  </div>

	  <div class="form-group">
	    <label>Volume</label>
	    <select class="form-control" name="volume" required>
		  	<?php
				exec("ls /volumes", $volume_list);
				foreach ($volume_list as $volume) {
					$mounted = exec("df | grep $volume");
					if(!empty($mounted) OR file_exists('/volumes/sys-vol')){
				?>
						<option <?php if($volume == $mounted_volume){ echo "selected";} ?> ><?php echo "$volume"; ?></option>
					<?php
					}
					?>	

				<?php
				}
				?>
	  	</select>
	  </div>
	  
	  <div class="form-group">
	    <label>Description</label>
	    <textarea class="form-control" name="description" rows=3><?php echo $comment; ?></textarea>
	  </div>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="read_only" value=1 <?php if($read_only == 1){ echo "checked"; } ?>>
	    <label class="form-check-label ml-1">Read Only</label>
		</div>
	  
	  <div class="form-group">
			<label>Group Access</label>
			<select class="form-control" name="group" required>
			  	<option>users</option>
			  	<?php
					exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
					foreach ($group_array as $group) {
					?>
					<option <?php if($group == $share_group){ echo "selected"; } ?> ><?php echo "$group"; ?></option>	

				<?php
					}
				?>

	  	</select>  
	  </div>
 	  
 	  <button type="submit" name="share_edit" class="btn btn-primary">Submit</button>
	 
	</form>

</main>

<?php include("footer.php"); ?>