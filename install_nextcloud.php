<?php 
    include("config.php");
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
  	<li>When Installation is complete you can access Nextcloud by visiting https://<?php echo $_SERVER['HTTP_HOST']; ?></li>
  </ul>
 
  	<form method="post" action="post.php">
  		<div class="form-group">
		    <label>Nextcloud Admin Password</label>
		    <input type="text" class="form-control" name="password" required>
		</div>
		<div class="form-group">
			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input" name="enable_samba_auth" value="1" id="sambaAuth">
				<label class="custom-control-label" for="sambaAuth">Samba Authentication</label>
			</div>
		</div>
		<div class="form-group">
			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input" name="enable_samba_mount" value="1" id="sambaMount">
				<label class="custom-control-label" for="sambaMount">Samba Mount Home and Shares</label>
			</div>
		</div>
		<div class="form-group">
			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input" name="install_apps" value="1" id="installApps">
				<label class="custom-control-label" for="installApps">Install Groupware Apps (Talk, Calendar, Contacts, Mail)</label>
			</div>
		</div>

		<button type="submit" name="install_nextcloud" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
