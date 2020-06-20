<?php 
  
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br", $net_devices_array);

	if(isset($_GET['name'])){
		$name = $_GET['name'];
	}

	$networkConfigArray = parse_ini_file("/etc/systemd/network/$name.network");
	$address = $networkConfigArray['Address'];
	$gateway = $networkConfigArray['Gateway'];
	$dns = $networkConfigArray['DNS'];
	$dhcp = $networkConfigArray['DHCP'];

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item"><a href="network.php">Networks</a></li>
	    <li class="breadcrumb-item active">Edit Network</li>
	  </ol>
	</nav>

  <h2>Edit Network</h2>
  
  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
		
		<input type="hidden" name="interface" value="<?php echo $name; ?>">
		
		<div class="form-group">
			<label>Interface</label>
			<input type="text" class="form-control" value="<?php echo $name; ?>" disabled>
	  </div>
	  
	  <div class="form-group">
			<label>Method</label>
			<select class="form-control" name="method" id="method">
				<option <?php if($dhcp == 'ipv4'){ echo "selected"; } ?> id="dhcp">DHCP</option>
				<option <?php if(empty($dhcp)){ echo "selected"; } ?> id="static">Static</option>
			</select>
	  </div>
	  
	  <div id="staticSettings">
		  
		  <div class="form-group">
		    <label>Address/CIDR</label>
		    <input type="text" class="form-control" name="address" placeholder="ex 192.168.1.5/24" value="<?php echo $address; ?>">
		  </div>
		  
		  <div class="form-group">
		    <label>Gateway</label>
		    <input type="text" class="form-control" name="gateway" value="<?php echo $gateway; ?>">
		  </div>
		  
		  <div class="form-group">
		    <label>DNS Server(s)</label>
		    <input type="text" class="form-control" name="dns" value="<?php echo $dns; ?>">
		  </div>
	  
	  </div>

		<button type="submit" name="network_add" class="btn btn-primary">Submit</button>
	
	</form>

</main>

<?php include("footer.php"); ?>