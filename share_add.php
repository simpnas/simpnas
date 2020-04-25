<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="shares.php">Shares</a></li>
    <li class="breadcrumb-item active">Add Share</li>
  </ol>
</nav>

  <h2>Add Share</h2>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume:</label>
	    <select class="form-control" name="volume">
	  	<?php
			exec("ls /$config_mount_target", $volume_list);
			foreach ($volume_list as $volume) {
			?>
			<option><?php echo "$volume"; ?></option>	

		<?php
			}
		?>

	  </select>
	  </div>
	  
	  <div class="form-group">
	    <label>Share Name:</label>
	    <input type="text" class="form-control" name="name" required pattern="[a-zA-Z0-9-]{1,25}">
	  </div>
	  
	  <div class="form-group">
	    <label>Description:</label>
	    <textarea class="form-control" name="description" rows=3></textarea>
	  </div>
	  <div class="form-group">
		<label>Group Access:</label>
		<select class="form-control" name="group" required>
		  	<option>users</option>
		  	<?php
				exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
				foreach ($group_array as $group) {
				?>
				<option><?php echo "$group"; ?></option>	

			<?php
				}
			?>

		  </select>  
		</div>
 		<button type="submit" name="share_add" class="btn btn-primary">Submit</button> 
	</form>
</main>

<?php include("footer.php"); ?>
