<?php 
  
exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br", $net_devices_array);
 
?>

<div class="modal fade" id="addNetworkModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Network</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">
          
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
					<select class="form-control" name="method" id="method">
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
         
        <div class="modal-footer">
           <button type="submit" name="network_add" class="btn btn-primary">Create</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
