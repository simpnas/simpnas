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
	    <li class="breadcrumb-item active">Install Nextcloud</li>
	  </ol>
	</nav>

  <h2>Install Nextcloud</h2>
  <ul>
  	<li>Samba Auth allow you to use nas logins instead of recreating new logins for nextcloud</li>
  	<li>Mount Home and Shares will automatically mount shares on nextcloud</li>
  	<li>When Installation is complete you can access Nextcloud by visiting https://<?php echo $config_primary_ip; ?>:6443</li>
  </ul>
 
  <form method="post" action="post.php" autocomplete="off">
  	<div class="form-group">
		  <label>Nextcloud Admin Password</label>
		  <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
		</div>

		<div class="form-group">
	    <label>Choose a volume for your Nextcloud Data</label>
	    <select class="form-control" name="data_volume" required>
  	  	<?php
        exec("ls /volumes", $volume_list);
        foreach ($volume_list as $volume) {
          $mounted = exec("df | grep $volume");
          if(!empty($mounted) OR file_exists('/volumes/sys-vol')){
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
		
		<div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="enable_samba_auth" value="1" id="sambaAuth">
	    <label class="form-check-label ml-1">Samba Authentication</label>
	  </div>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="enable_samba_mount" value="1" id="sambaMount">
	    <label class="form-check-label ml-1">Samba Mount Home and Shares</label>
	  </div>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="install_apps" value="1" id="installApps">
	    <label class="form-check-label ml-1">Install Groupware Apps (Talk, Calendar, Contacts, Mail)</label>
	  </div>

		<button type="submit" name="install_nextcloud" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>