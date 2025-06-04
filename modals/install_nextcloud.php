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
          <li>Samba Auth allow you to use nas logins instead of recreating new logins for nextcloud</li>
          <li>Mount Home and Shares will automatically mount shares on nextcloud</li>
          <li>When Installation is complete you can access Nextcloud by visiting https://<?php echo $config_primary_ip; ?>:6443</li>
        </ul>
       
        <form method="post" action="post.php" autocomplete="off">
          <div class="form-group">
            <label>Nextcloud Admin Password</label>
            <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
          </div>

          <div class="form-group">
            <label>Choose a volume for your Nextcloud Data</label>
            <select class="form-control" name="data_volume" required>
              <?php
              exec("ls /volumes", $volume_list);
              foreach ($volume_list as $volume) {
                $mounted = exec("df | grep $volume");
                if(!empty($mounted) OR file_exists('/volumes/sys-vol')){
              ?>
                <option><?php echo "$volume"; ?></option> 
                <?php 
                } 
                ?>
              <?php
              }
              ?>

            </select>
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
