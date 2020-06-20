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
      <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
      <li class="breadcrumb-item active">Install Jellyfin</li>
    </ol>
  </nav>

  <h2>Install Jellyfin</h2>
  <ul>
  	<li>A group called media will be created.</li>
  	<li>We will create a share called media based on the volume you select with the following subdirectories: movies, tvshow and music</li>
  	<li>You will need to assign users to the media group if you want users to be able access and write to the media share over the network.</li>
  	<li>We will also create a directory called jellyfin under the docker directory.</li>
  	<li>When Installation is complete you can access and setup jellyfin by visiting http://<?php echo $config_primary_ip; ?>:8096</li>
  </ul>
 
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Volume to create media Share</label>
	    <select class="form-control" name="volume">
  	  	<?php
        exec("ls /volumes", $volume_list);
        foreach ($volume_list as $volume) {
          $mounted = exec("df | grep $volume");
          if(!empty($mounted)){
        ?>
          <option><?php echo "$volume"; ?></option> 
          <?php 
          } 
          ?>
        <?php
        }
        ?>

	   </select>
	  </div>
 	  
    <button type="submit" name="install_jellyfin" class="btn btn-primary">Submit</button>
	 
	</form>

</main>

<?php include("footer.php"); ?>
