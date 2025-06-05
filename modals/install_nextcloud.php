<?php 
  
$media_volume = exec("find /volumes/*/media -name media | awk -F/ '{print $3}'");
 
?>

<div class="modal fade" id="installNextcloudModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Install Nextcloud</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">

        <ul>
          <li>Nextcloud comes preconfigured to allow login with your SimpNAS users</li>
          <li>Nextcloud will automatically Mount home folders of users and Shares</li>
          <li>When Installation is complete you can access Nextcloud by visiting https://<?php echo $config_primary_ip; ?>:6443</li>
        </ul>
       
        <form method="post" action="post.php" autocomplete="off">
          <div class="form-group">
            <label>Nextcloud Admin Password</label>
            <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
          </div>

        </div>
         
        <div class="modal-footer">
           <button type="submit" name="install_nextcloud" class="btn btn-primary">Install</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
