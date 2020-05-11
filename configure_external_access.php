<?php 
    $config = include("config.php");
  	include("simple_vars.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /$config_mount_target/$config_docker_volume/docker | grep -v mariadb", $app_sub_domains_array);
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
    <li class="breadcrumb-item active">Configure External Access</li>
  </ol>
</nav>

  <h2>External Access to your Apps</h2>
  <ul>
  	<li>This will enable external access to the following apps</li>
  	<li>Make sure the following A or CNAME DNS records exist for your domain and that they are pointing to your public IP</li>
  	<li><?php echo implode(',', $app_sub_domains_array); ?></li>
  	<li>Configure you firewall or router to port forward port 80 and port 443 TCP to IP</li>
  </ul>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Domain</label>
	    <input type="text" class="form-control" name="domain" placeholder="example.com">
	  </div>
	  
	  <legend>Select apps you would like to configure External Access for</legend>
	  <?php 
	  foreach($app_sub_domains_array as $app_sub_domain){
	  ?>
		<div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="sub_domains[]" value="<?php echo $app_sub_domain; ?>">
	    <label class="form-check-label"><?php echo $app_sub_domain; ?></label>
		</div>
	  
	  <?php
	  }
	  ?>

 	  <button type="submit" name="configure_external_access" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
