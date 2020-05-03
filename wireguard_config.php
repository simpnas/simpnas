<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    
    exec("find /mnt/MisterSMALL/docker/wireguard/peer* -type d -printf '%f\n'", $vpn_peers_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>VPN</h2>
    <a href="vpn_add.php" class="btn btn-outline-primary">Add Peer</a>
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

  <div class="table-responsive">
    <table class="table table-striped" id="dt">
      <thead>
        <tr>
          <th>Peer</th>
          <th>Config</th>
          <th>Peer IP</th>
          <th>Status</th>
          <th>End Point IP</th>
          <th>Connection Time</th>
          <th>Recieved</th>
          <th>Sent</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 	  
        foreach($vpn_peers_array as $vpn_peer){
          $peer_pub_key = exec("cat /$config_mount_target/$config_docker_volume/docker/wireguard/$vpn_peer/publickey-$peer.conf");
          $peer_ip = exec("cat /$config_mount_target/$config_docker_volume/docker/wireguard/$vpn_peer/$vpn_peer.conf | grep Address | awk '{print $3}'");
          $end_point_ip = exec("docker exec -i wireguard wg | grep endpoint | awk '{print $2}'");
          $connection_time = exec("docker exec -i wireguard wg | grep 'latest handshake' | awk '{print $3, $4, $5, $6}'");
          $transfer_recieved = exec("docker exec -i wireguard wg | grep transfer | awk '{print $2, $3}'");
          $transfer_sent = exec("docker exec -i wireguard wg | grep transfer | awk '{print $5, $6}'");
          if(empty($connection_time)){
            $connection_status = "<div class='text text-secondary'>Not Connected</div>";
          }else{
            $connection_status = "<div class='text text-success'>Connected</div>";
          }
        ?>
          <tr>
            <td><span class="mr-2" data-feather="key"></span><?php echo $vpn_peer; ?></td>
            <td>
              <img src="post.php?wireguard_qr&peer=<?php echo $vpn_peer; ?>">
              <br>
              <a href="post.php?wireguard_config&peer=<?php echo $vpn_peer; ?>">Download Config</a>
            </td>
            <td><?php echo $peer_ip; ?></td>
            <td><?php echo $connection_status; ?></td>
            <td><?php echo $end_point_ip; ?></td>
            <td><?php echo $connection_time; ?></td>
            <td><?php echo $transfer_recieved; ?></td>
            <td><?php echo $transfer_sent; ?></td>
            <td>
              <div class="btn-group mr-2">
              <a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
              <a href="post.php?user_delete=<?php echo $username; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
            </td>
          </tr>
        <?php 
        } 
        ?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
