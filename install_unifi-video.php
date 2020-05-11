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
    <li class="breadcrumb-item active">Install Unifi-Video</li>
  </ol>
</nav>

  <h2>Install Unifi-Video</h2>
  <ul>
    <li>A group called video-surveillance will be created.</li>
  	<li>We will create a share called video-surveillance based on the volume you select</li>
  	<li>You will need to assign users to the video-surveillance group if you want users to be able access video surveillance from the network.</li>
  	<li>We will also create a directory called unifi-video under the docker folder.</li>
  	<li>When installation is complete you can access and setup Unifi Video by visiting https://<?php echo $_SERVER['HTTP_HOST']; ?>:7443</li>
  </ul>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume to create video-surveillance Share</label>
	    <select class="form-control" name="volume">
	  	<?php
      exec("ls /$config_mount_target", $volume_list);
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
 	<button type="submit" name="install_unifi-video" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
