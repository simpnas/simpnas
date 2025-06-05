<?php 
  
$media_volume = exec("find /volumes/*/media -name media | awk -F/ '{print $3}'");
 
?>

<div class="modal fade" id="installJellyfinModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Install Jellyfin</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">

          <ul>
            <li>A group called media will be created.</li>
            <li>We will create a share called media based on the volume you select with the following subdirectories: movies, tvshow and music</li>
            <li>You will need to assign users to the media group if you want users to be able access and write to the media share over the network.</li>
            <li>We will also create a directory called jellyfin under the docker directory.</li>
            <li>When Installation is complete you can access and setup jellyfin by visiting http://<?php echo $config_primary_ip; ?>:8096</li>
          </ul>
          
          <?php if(empty($media_volume)){ ?>

            <div class="form-group">
              <label>Volume to create media Share</label>
              <select class="form-control" name="volume">
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

          <?php } else { ?>

            <input type="hidden" name="volume" value="<?php echo $media_volume; ?>">
            <div class="form-group">
              <label>Media Share already exists, will use the existing share.</label>
              <select class="form-control" name="volume" readonly>
                <option><?php echo $media_volume; ?></option>
              </select>
            </div>

          <?php } ?>

        </div>
         
        <div class="modal-footer">
           <button type="submit" name="install_jellyfin" class="btn btn-primary">Install</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php UNSET($volume_list); ?>