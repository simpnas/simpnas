<?php 
  	include("config.php");
    include("header.php");
    include("side_nav.php");
    if(isset($_GET['share'])){
  		$share = $_GET['share'];
  	}
    $smb = file('/etc/samba/smb.conf');
    $sambaConfigArray = parse_ini_file('/etc/samba/smb.conf', true );
    $path = $sambaConfigArray[$share]['path'];
    $mounted_volume = basename(dirname($path));
    $comment = $sambaConfigArray[$share]['comment'];
    $share_group = $sambaConfigArray[$share]['force group'];

?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="shares.php">Shares</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Share</li>
  </ol>
</nav>

  <h2>Edit Share</h2>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume:</label>
	    <select class="form-control" name="volume">
	  	<?php
			exec("ls /$config_mount_target", $volume_list);
			foreach ($volume_list as $volume) {
			?>
			<option <?php if($volume == $mounted_volume){ echo "selected";} ?> ><?php echo "$volume"; ?></option>	

		<?php
			}
		?>

	  </select>
	  </div>
	  <input type="hidden" name="current_name" value="<?php echo $share; ?>">
	  <input type="hidden" name="current_volume" value="<?php echo $mounted_volume; ?>">
	  <input type="hidden" name="current_description" value="<?php echo $description; ?>">
	  <input type="hidden" name="current_group" value="<?php echo $share_group; ?>">

	  <div class="form-group">
	    <label>Share Name:</label>
	    <input type="text" class="form-control" name="name" value="<?php echo $share; ?>">
	  </div>
	  
	  <div class="form-group">
	    <label>Description:</label>
	    <textarea class="form-control" name="description" rows=3><?php echo $comment; ?></textarea>
	  </div>
	  <div class="form-group">
		<label>Group Access:</label>
		<select class="form-control" name="group">
	  	<option>users</option>
	  	<?php
			exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup | grep -v nobody", $group_array);
			foreach ($group_array as $group) {
			?>
			<option <?php if($group == $share_group){ echo "selected"; } ?> ><?php echo "$group"; ?></option>	

		<?php
			}
		?>

	  </select>  
	</div>
 	<button type="submit" name="share_add" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
