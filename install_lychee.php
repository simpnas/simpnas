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
    <li class="breadcrumb-item active">Install Lychee</li>
  </ol>
</nav>

  <h2>Install Lychee</h2>
  <ul>
  	<li>A group called photos will be created.</li>
  	<li>We will create a share called photos on the volume you select.</li>
  	<li>You will need to assign users to the photos group if you want users to access the photos share over the network.</li>
  	<li>When installation is complete you can access and setup Lychee by visiting to http://<?php echo $_SERVER['HTTP_HOST']; ?>:4560</li>
  </ul>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume to create photos Share</label>
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
 	<button type="submit" name="install_lychee" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
