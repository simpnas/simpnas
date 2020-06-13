<?php

	if(file_exists('config.php')){
	  header("Location: login.php");
	}

	include("setup_header.php");
	$os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item"><a href="setup_volume.php">Volume</a></li>
	    <li class="breadcrumb-item active">Final</li>
	  </ol>
	</nav>

	<?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>
  
  <h2>Final Configuration</h2>
  <hr>
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Administrator Password</label>
	    <input type="password" class="form-control" name="password" required autocomplete="new-password" autofocus>
	  </div>

	  <div class="form-group">
			<label>Server Type</label>
			<select class="form-control" name="server_type" id="serverType">
				<option id="standAlone" value="standalone">File Server</option>
				<option id="activeDirectory" value="AD">Directory / File Server</option>
			</select>
	  </div>

	  <div id="activeDirectorySettings">
		  <div class="form-group">
		    <label>Domain Name</label>
		    <input type="text" class="form-control" name="ad_domain" placeholder="ex. company.int">
		  </div>
	  </div>

	  <div class="form-group">
	  	<div class="custom-control custom-checkbox">
			  <input type="checkbox" class="custom-control-input" name="collect" value="1" id="collect">
			  <label class="custom-control-label" for="collect">Yes Collect Statistic Data</label>
				<small class="form-text text-muted">This will collect a Unique Machine ID used for Unique Installs on our Webpage.</small>
			</div>
	  </div>
	  
	  <button type="submit" name="setup_server" class="btn btn-primary">Complete <span data-feather="arrow-right"></span></button>
	</form>
</main>

<?php include("footer.php"); ?>