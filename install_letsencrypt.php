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
    <li class="breadcrumb-item active">Install LetsEncrypt</li>
  </ol>
</nav>

  <h2>Install LetsEncrypt</h2>
  <ul>
  	<li>Samba Auth allow you to use nas logins instead of recreating new logins for nextcloud</li>
  	<li>Mount Home and Shares will automatically mount shares on nextcloud</li>
  	<li>When Installation is complete you can access Nextcloud by visiting http://<?php echo $_SERVER['HTTP_HOST']; ?></li>
  </ul>
 
  	<form method="post" action="post.php" autocomplete="off">
  		<div class="form-group">
		    <label>Domain</label>
		    <input type="text" class="form-control" name="domain" required>
		</div>
		<div class="form-group">
		    <label>Sub Domains</label>
		    <input type="text" class="form-control" name="sub_domains" required>
		</div>

		<button type="submit" name="install_letsencrypt" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
