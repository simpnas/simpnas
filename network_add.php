<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth", $net_devices_array);
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="network.php">Networks</a></li>
    <li class="breadcrumb-item active">Add Network</li>
  </ol>
</nav>

  <h2>Add Network</h2>
  <form method="post" action="post.php">
	<div class="form-group">
		<label>Interface</label>
		<select class="form-control" name="interface">
			<?php
			foreach ($net_devices_array as $net_device) {
			?>
			<option><?php echo $net_device; ?></option>
			<?php
			}
			?>
		</select>
	  </div>
	  <div class="form-group">
		<label>Method</label>
		<select class="form-control" name="method">
			<option>DHCP</option>
			<option>Static</option>
		</select>
	  </div>
	  <div class="form-group">
	    <label>Address/CIDR</label>
	    <input type="text" class="form-control" name="address" placeholder="ex 192.168.1.5/24">
	  </div>
	  <div class="form-group">
	    <label>Gateway</label>
	    <input type="text" class="form-control" name="gateway">
	  </div>
	  <div class="form-group">
	    <label>DNS Server(s)</label>
	    <input type="text" class="form-control" name="dns">
	  </div>

	  <button type="submit" name="network_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>