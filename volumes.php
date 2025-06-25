<?php 
require_once "includes/include_all.php";

// Fetch the volume data using the getVolumes function

$volumes = getVolumes();
?>
  
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Volumes</h2>
    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#addVolumeModal">
      Create Volume
    </button>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Disk(s)</th>
        <th>Usage</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
      <?php
      foreach ($volumes as $volume_data) {   
        
        // Get the details from the volume data array
        $volume = $volume_data['volume'];
        $disk = $volume_data['disk'];
        $disk_base_name = basename($disk);
        $total_space = $volume_data['total_space'];
        $used_space = $volume_data['used_space'];
        $free_space = $volume_data['free_space'];
        $used_space_percent = $volume_data['use_percent'];
        $is_mounted = $volume_data['is_mounted'];
        $crypt_status = exec("cryptsetup status $volume | grep inactive");
        if (str_contains($disk, 'md')) {
          $is_raid = TRUE;
          $raid_level = strtoupper(preg_replace('/raid(\d+)/i', 'raid $1', exec("mdadm --detail $disk | grep 'Raid Level' | awk '{print \$4}'")));
        } else {
          $is_raid = FALSE;
        }

        // Error Check BTRFS error counts
        $volume_error = false;
        foreach (explode("\n", shell_exec("btrfs device stats /volumes/$volume/")) as $line) {
            if (preg_match('/\s(\d+)$/', $line, $m) && $m[1] > 0) {
                $volume_error = true;
                break;
            }
        }



      ?>
      
      <tr class="<?php if ($volume_error) { echo "table-danger"; } ?>">
        <td><span class="mr-2" data-feather="database"></span><strong><?php echo $volume; ?><?php if ($volume_error) { echo " <span data-feather='alert-triangle'></span>"; } ?></strong></td>
        <td>
          <?php if ($is_raid) { ?>
            <a href="raid_configuration.php?raid=<?php echo $disk_base_name; ?>"><?php echo $raid_level; ?></a>
          <?php } else { ?>
            Simple
          <?php } ?>
        </td>
        <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
        <td>
          <?php if ($is_mounted === 'yes') { ?>
          <div class="progress">
            <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
          </div>
          <small><span class="text-primary"><?php echo $used_space; ?>B</span> <span class="text-secondary">| <?php echo $total_space; ?>B</span></small>
          <?php } elseif(file_exists("/volumes/$volume/.uuid_map")) { ?>
            <p class="text-danger">Encrypted</p>
          <?php } else { ?>
          <p class="text-danger">Not Mounted</p>
          <?php } ?>
        </td>
        <td>
          <div class="btn-group mr-2">
            <?php if($config_home_volume != $volume && $is_mounted === 'yes') { ?>
            <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteVolume<?php echo $volume; ?>"><span data-feather="trash"></span></button>
            <?php if($crypt_status){ ?>
            <a class="btn btn-outline-dark" href="post.php?unmount_volume=<?php echo $volume; ?>">Unmount</a>
            <?php } ?>
             <?php } elseif($config_home_volume != $volume && $is_mounted === 'no' && !file_exists("/volumes/$volume/.uuid_map")) { ?>
            <a class="btn btn-dark" href="post.php?mount_volume=<?php echo $volume; ?>">Mount</a>
            <?php } ?>
            <?php
              if(file_exists("/volumes/$volume/.uuid_map")){
            ?>    
              <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#mountCrypt<?php echo $disk; ?>"><span data-feather="unlock"></span></button>
              
            <?php   
              }
            ?>
            <?php
            if(empty($crypt_status)){ ?>
              <a href="post.php?lock_volume=<?php echo $volume; ?>" class="btn btn-outline-secondary"><span data-feather="lock"></span></a>
            <?php } ?>
            <a class="btn btn-outline-dark" href="file_system_stats.php?volume_name=<?php echo $volume; ?>" title="File System Stats"><span data-feather="activity"></span></a>
          </div>
         
        </td>
      </tr>

      <div class="modal fade" id="deleteVolume<?php echo $volume; ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-trash"></i> Delete <?php echo $volume; ?></h5>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <center>
                <h3 class="text-secondary">Are you sure you want to</h3>
                <h1 class="text-danger">Delete <strong><?php echo $volume; ?></strong>?</h1>
                <h5>This will delete all data within the Volume</h5>
              </center>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
              <a href="post.php?volume_delete=<?php echo $volume; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span> Delete</a>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="mountCrypt<?php echo $disk; ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Unlock <?php echo $volume; ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="post" action="post.php" autocomplete="off">
              <input type="hidden" name="volume" value="<?php echo $volume; ?>">
              <div class="modal-body">

                <center><i class="fa fa-8x fa-unlock text-secondary mb-3"></i></center>
               
                <div class="form-group">
                  <input type="password" class="form-control" name="password" placeholder="Password" required autofocus autocomplete="new-password">
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="unlock_volume" class="btn btn-primary">Unlock</button>
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php 
      } 
      ?>
    </tbody>
  </table>

</div>

<?php 
require_once "modals/volume_add.php";
require_once "includes/footer.php";
