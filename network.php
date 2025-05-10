<?php 
  
require_once "includes/include_all.php";

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Network Settings</h2>
  <a href="network_add.php" class="btn btn-outline-primary">Create Network</a>
</div>

<?php include("alert_message.php"); ?>

<form method="post" action="post.php" autocomplete="off">

  <div class="form-group">
    <label>Hostname</label>
    <input type="text" class="form-control" name="hostname" value="<?php echo $config_hostname; ?>" required>
  </div>
  
  <button type="submit" name="settings_hostname" class="btn btn-primary">Submit</button>

</form>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2 mt-3">
  <h2>Network Interfaces</h2>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Interface</th>
        <th>Type</th>
        <th>IP Address</th>
        <th>Gateway</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      
        exec("ls /etc/systemd/network", $network_list);
        foreach ($network_list as $network) {

          $networkConfigArray = parse_ini_file("/etc/systemd/network/$network");
          $name = $networkConfigArray['Name'];
          $address = $networkConfigArray['Address'];
          $gateway = $networkConfigArray['Gateway'];
          $dns = $networkConfigArray['DNS'];
          $dhcp = $networkConfigArray['DHCP'];
          if($dhcp == "ipv4"){
            $address = "DHCP";
            $gateway = "DHCP";
            $dns = "DHCP";
          }
      ?>                
      <tr>
        <td><i class="mr-2 fas fa-ethernet text-secondary"></i><?php echo $name; ?></td>
        <td>Ethernet</td>
        <td><?php echo $address; ?></td>
        <td><?php echo $gateway; ?></td>
        <td class="text-success"><span data-feather="arrow-up"></span></td>
        <td>
        	<div class="btn-group mr-2">
      		<a href="network_edit.php?name=<?php echo $name; ?>" class="btn btn-outline-secondary"><span data-feather="edit-2"></span></a>
    		</div>
    	  </td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once "footer.php";
