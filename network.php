<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Network</h2>
    <a href="network_add.php" class="btn btn-outline-primary">Create Network</a>
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
          <td><span class="mr-2" data-feather="globe"></span><?php echo $name; ?></td>
          <td>Ethernet</td>
          <td><?php echo $address; ?></td>
          <td><?php echo $gateway; ?></td>
          <td class="text-success"><span data-feather="arrow-up"></span></td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="network_edit.php?name=<?php echo $name; ?>" class="btn btn-outline-secondary"><span data-feather="edit-2"></span></a>
        		<a href="post.php?network_delete=<?php echo $name; ?>" class="btn btn-outline-secondary"><span data-feather="trash"></span></a>
      		</div>
      	  </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
