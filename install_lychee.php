<?php 
    include("header.php");
    include("side_nav.php");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="packages.php">Packages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Install Lychee</li>
  </ol>
</nav>

  <h2>Install Plex</h2>
  <ul>
  	<li>A group called photos will be created.</li>
  	<li>We will create a share called photos on the volume you select</li>
  	<li>You will need to assign users to the photos group if you want users to access the photos share.</li>
  	<li>When Installation is done you can access and setup Lychee by going to http://yourIP:4560</li>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume to create Photos Share:</label>
	    <select class="form-control" name="volume">
	  	<option></option>
	  	<?php
			exec("ls /mnt", $volume_list);
			foreach ($volume_list as $volume) {
			?>
			<option><?php echo "$volume"; ?></option>	

		<?php
			}
		?>

	  </select>
	  </div>
 	<button type="submit" name="install_lychee" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
