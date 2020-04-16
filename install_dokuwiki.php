<?php 
    include("config.php");
	include("header.php");
    include("side_nav.php");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="packages.php">Packages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Install Dokuwiki</li>
  </ol>
</nav>

  <h2>Install Dokuwiki</h2>
  <ul>
  	<li>/<?php echo $config_docker_volume; ?>/docker/dokuwiki will be created.</li>
  	<li>Once installed dokuwiki can be accessed by visiting http://<?php echo gethostname(); ?>:8080 or http://<?php echo $_SERVER['SERVER_ADDR']; ?>:8080</li>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume for Data Files:</label>
	    <select class="form-control" name="volume">
	  	<option></option>
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
 	<button type="submit" name="install_dokuwiki" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
