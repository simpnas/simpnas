<?php 
  
  include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<?php
	$vpn_user = exec("docker exec transmission sed -n 1p /config/openvpn-credentials.txt");
	$vpn_pass = exec("docker exec transmission sed -n 2p /config/openvpn-credentials.txt");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
    <li class="breadcrumb-item active">Configure Transmission</li>
  </ol>
</nav>

  <h2>Configure Transmission VPN</h2>
  <h4>VPN IP: <small><?php $vpn_ip = exec("docker exec transmission curl ifconfig.co"); echo $vpn_ip; ?></small></h4>
 
  <form method="post" action="post.php">
		  
	  <div class="form-group">
	    <label>VPN Provider <strong class="text-danger">*</strong></label>
	    <select class="form-control" name="vpn_provider">
	  		<option>PIA</option>
	  	</select>
	  	<small class="form-text text-muted">We only support PIA VPN for right now.</small>
	  </div>
	  
	  <div class="form-group">
	    <label>VPN Region / Server <small class="text-secondary">(Optional)</small></label>
	    <input type="text" class="form-control" name="vpn_server" placeholder="Optional (Random by Default)">
	  </div>
	  
	  <div class="form-group">
	    <label>VPN Username <strong class="text-danger">*</strong></label></label>
	    <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo $vpn_user; ?>">
	  </div>
	  
	  <div class="form-group">
	    <label>VPN Password <strong class="text-danger">*</strong></label></label>
	    <input type="password" class="form-control" name="password" placeholder="Password" value="<?php echo $vpn_pass; ?>">
	  </div>
	  
	  <div class="form-group">
	    <label>DNS Server <small class="text-secondary">(Optional)</small></label>
	    <input type="text" class="form-control" name="dns" placeholder="1.1.1.1">
	  	<small class="form-text text-muted">Required if your current dns server isn't accessable outside its own network (If you have Comcast then user a public DNS) Common DNS Servers 8.8.8.8 (Google) 1.1.1.1 (Cloudflare) 9.9.9.9 (Quad9)</small>
	  </div>
 		
 		<button type="submit" name="transmission_update" class="btn btn-primary">Submit</button>
	 
	</form>

</main>

<?php include("footer.php"); ?>