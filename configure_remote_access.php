<?php 
  
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  exec("ls /volumes/$config_docker_volume/docker | grep -v mariadb | grep -v letsencrypt | grep -v transmission", $apps_array);
  foreach($apps_array as $app){
  	if($app == 'nextcloud'){
  		$sub_domains_array[] = 'cloud';
  	}elseif($app == 'unifi-controller'){
  		$sub_domains_array[] = 'unifi';
  	}elseif($app == 'gitea'){
  		$sub_domains_array[] = 'git';
  	}elseif($app == 'dokuwiki'){
  		$sub_domains_array[] = 'wiki';
  	}elseif($app == 'bitwarden'){
  		$sub_domains_array[] = 'vault';
  	}else{
  		$sub_domains_array[] = $app;
  	}
  }
  if(file_exists("/volumes/$config_docker_volume/docker/letsencrypt/")){ 
  	 $domain = exec("cat /volumes/$config_docker_volume/docker/letsencrypt/donoteditthisfile.conf | awk -F\\\" '{print $2}'");
  }
  
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item active">Configure Remote Access</li>
	  </ol>
	</nav>

  <h2>Configure Remote Access</h2>
  <ul>
  	<li>This will enable external access to the following apps and assign a valid SSL Cert using LetsEncrypt Service</li>
  	<li>Make sure the following A or CNAME DNS records exist for your domain and that they are pointing to your public IP</li>
		<ul>
			<?php
			foreach($sub_domains_array as $sub_domain){
				//$resolvable_ip = exec("nslookup $sub_domain.dojo.pittpc.com | grep -v 127.0.0 | grep Address | awk '{print $2}'");
			?>
			<li><?php echo $sub_domain; ?> <?php echo $resolvable_ip; ?></li>
			<?php
			}
			?>
		</ul>
  	<li>Configure your firewall or router to port forward port 80 and port 443 TCP to your internal IP address <?php echo $config_primary_ip; ?></li>
  </ul>
 
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Domain</label>
	    <input type="text" class="form-control" name="domain" placeholder="example.com" value="<?php echo $domain; ?>" required>
	  </div>
	  
	  <legend>Select apps you would like to configure remote access for</legend>
	  <?php 
	  	foreach($apps_array as $app){
	  ?>
		<div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="app[]" value="<?php echo $app; ?>" <?php if(file_exists("/volumes/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app.subdomain.conf")) { echo "checked"; } ?> >
	    <label class="form-check-label"><?php echo $app; ?></label>
		</div>
	  
	  <?php
	  }
	  ?>

 	  <button type="submit" name="configure_remote_access" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
