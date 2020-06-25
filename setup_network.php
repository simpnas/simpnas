<?php

	include("setup_header.php");

	exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br-", $net_devices_array);

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
	<nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item active">Network</li>
	  </ol>
	</nav>
  
  <h2>Network Configuration</h2>
  <hr>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Server Name</label>
	    <input type="text" class="form-control" name="hostname" required autofocus>
	  </div>

	  <div class="form-group">
	  	<label>Network Interface</label>
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
			<select class="form-control" name="method" id="method" required>
				<option id="dhcp">DHCP</option>
				<option id="static">Static</option>
			</select>
	  </div>
	  
	  <div id="staticSettings">
		  <div class="form-group">
		    <label>Address</label>
		    <input type="text" class="form-control" name="address" placeholder="ex 192.168.1.5">
		  </div>

		  <div class="form-group">
		  	<label>Netmask</label>
				<select class="form-control" name="netmask">
					<option value="/24">255.255.255.0</option>
				</select>
		  </div>
		  
		  <div class="form-group">
		    <label>Gateway</label>
		    <input type="text" class="form-control" name="gateway" placeholder="ex 192.168.1.1">
		  </div>
		  
		  <div class="form-group">
		    <label>DNS Server(s)</label>
		    <input type="text" class="form-control" name="dns" placeholder="ex 192.168.1.1">
		  </div>
	  </div>
	  
	  <button type="submit" name="setup_network" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
	  <a href="setup_volume.php" class="btn btn-secondary">Skip <span data-feather="arrow-right"></span></a>
	</form>
</main>

<?php include("footer.php"); ?>