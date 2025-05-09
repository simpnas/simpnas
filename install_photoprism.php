<?php 
  
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  $photos_volume = exec("find /volumes/*/photos -name photos | awk -F/ '{print $3}'");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
      <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
      <li class="breadcrumb-item active">Install Photoprism</li>
    </ol>
  </nav>

  <h2>Install Photoprism</h2>
  <ul>
  	<li>A group called photos will be created.</li>
  	<li>We will create a share called photos based on the volume you select.</li>
  	<li>You will need to assign users to the photos group if you want users to be able access and write to the photos share over the network.</li>
  	<li>We will also create a directory called photoprism under the docker directory.</li>
  	<li>When Installation is complete you can access photoprism by visiting http://<?php echo $config_primary_ip; ?>:2342</li>
  </ul>
 
  <form method="post" action="post.php" autocomplete="off">

	  <?php if(empty($photos_volume)){ ?>

      <div class="form-group">
  	    <label>Volume to create photos Share</label>
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

    <?php }else{ ?>

      <input type="hidden" name="volume" value="<?php echo $photos_volume; ?>">
      <div class="form-group">
        <label>Photos Share already exists, will use the existing share.</label>
        <select class="form-control" name="volume" readonly>
          <option><?php echo $photos_volume; ?></option>
        </select>
      </div>

    <?php } ?>

    <button type="submit" name="install_photoprism" class="btn btn-primary">Submit</button>
	 
	</form>

</main>

<?php include("footer.php"); ?>
