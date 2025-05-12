<?php 
require_once "includes/include_all.php";

// Fetch the volume data using the getVolumes function
$volumes = getVolumes();

?>
  
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Volumes</h2>
  <div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
      Create
    </button>
    <div class="dropdown-menu dropdown-menu-right">
      <a class="dropdown-item" href="#addVolumeModal" data-toggle="modal">Simple</a>
      <a class="dropdown-item" href="#addRaidVolumeModal" data-toggle="modal">RAID</a>
    </div>
  </div>
</div>

<?php include("alert_message.php"); ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
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
        $total_space = $volume_data['total_space'];
        $used_space = $volume_data['used_space'];
        $free_space = $volume_data['free_space'];
        $used_space_percent = $volume_data['use_percent'];
        $is_mounted = $volume_data['is_mounted'];
      ?>
      
      <tr>
        <td><span class="mr-2" data-feather="database"></span><strong><?php echo $volume; ?></strong></td>
        <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
        <td>
          <?php if ($is_mounted === 'yes') { ?>
          <div class="progress">
            <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
          </div>
          <small><?php echo $used_space; ?>B used of <?php echo $total_space; ?>B</small>
          <?php } else { ?>
          <p class="text-danger">Not Mounted</p>
          <?php } ?>
        </td>
        <td>
          <div class="btn-group mr-2">
            <?php if($config_home_volume != $volume && $is_mounted === 'yes') { ?>
            <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteVolume<?php echo $volume; ?>"><span data-feather="trash"></span></button>
            <a class="btn btn-warning" href="post.php?unmount_volume=<?php echo $volume; ?>">Unmount</a>
             <?php } elseif($config_home_volume != $volume && $is_mounted === 'no') { ?>
            <a class="btn btn-dark" href="post.php?mount_volume=<?php echo $volume; ?>">Mount</a>
            <?php } ?>
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
              <input type="hidden" name="disk" value="<?php echo $disk; ?>">
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
require_once "modals/volume_add_raid.php";
require_once "includes/footer.php";
