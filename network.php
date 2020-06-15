<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Network Settings</h2>
    <a href="network_add.php" class="btn btn-outline-primary">Create Network</a>
  </div>

  <?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>

  <form method="post" action="post.php" autocomplete="off">
    
    <div id="target" class="spinner-border" style="width: 3rem; height: 3rem; display: none"></div>

    <div class="form-group">
        <label>Hostname</label>
        <input type="text" class="form-control" name="hostname" value="<?php echo gethostname(); ?>" required>
    </div>
    <div class="form-group">
        <label>DNS Servers</label>
        <input type="text" class="form-control" name="dns">
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
          <td><span class="mr-2" data-feather="globe"></span><?php echo $name; ?></td>
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
</main>

<?php include("footer.php"); ?>
