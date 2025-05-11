<?php 
require_once "includes/include_all.php";

// Fetch the volume data using the getVolumes function
$volumes = getVolumes('volume', 'disk', 'total_space', 'used_space', 'free_space', 'use_percent');

// The dropdown and header remain the same
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

        // Check if the volume is RAID or encrypted (you can add custom logic for RAID/encryption if needed)
        $is_raid = exec("lsblk -o PKNAME,PATH,TYPE | grep $disk | grep raid");
        $is_crypt = exec("lsblk -o PKNAME,PATH,TYPE | grep $disk | grep crypt");
        if(!empty($is_raid)){
            // Check RAID type if needed, like RAID0, RAID1, etc.
            $raid_type = exec("lsblk -o PKNAME,PATH,TYPE | grep $disk | grep raid | awk '{print $3}'");
            if($raid_type == 'raid0'){
                $raid_type = 'RAID 0 (Striping)';
            } elseif($raid_type == 'raid1') {
                $raid_type = 'RAID 1 (Mirroring)';
            } elseif($raid_type == 'raid5') {
                $raid_type = 'RAID 5 (Parity)';
            } elseif($raid_type == 'raid6') {
                $raid_type = 'RAID 6 (Double Parity)';
            } elseif($raid_type == 'raid10') {
                $raid_type = 'RAID 10 (Mirror/Stripe)';
            }
            exec("lsblk -o PKNAME,PATH,TYPE | grep /dev/$disk | awk '{print $1}'",$array_disk_part_array);
            $disk_part_in_array = implode(', ', $array_disk_part_array);

            foreach($array_disk_part_array as $array_disk_part){
                $disk_in_array .= " " . exec("lsblk -n -o PKNAME,PATH | grep /dev/$array_disk_part | awk '{print $1}'");
            }
        }
      ?>
      
      <tr>
        <td><span class="mr-2" data-feather="database"></span><strong><?php echo $volume; ?></strong></td>
        <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?>
            <?php if(isset($disk_part_in_array)){ echo "<br><small class='text-secondary'>$raid_type: $disk_in_array</small>"; } ?>
            <?php if(!empty($is_crypt)){ echo "<br><small class='text-secondary'>Encrypted Volume</small>"; } ?>
        </td>
        <td>
          <?php if(empty($mounted)){ ?>
          <div class="text-danger">Not Mounted</div>
          <?php }else{ ?>
          <div class="progress">
            <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
          </div>
          <small><?php echo $used_space; ?>B used of <?php echo $total_space; ?>B</small>
          <?php } ?>  
        </td>
        <td>
          <div class="btn-group mr-2">
            <?php if(!empty($is_raid)){ ?>
              <a href="raid_configuration.php?raid=<?php echo $disk; ?>" class="btn btn-outline-secondary"><span data-feather="settings"></span></a>
            <?php } ?>
            <?php if($config_home_volume != $volume){ ?>
            <?php
              if(file_exists("/volumes/$volume/.uuid_map")){
            ?>    
              <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#mountCrypt<?php echo $disk; ?>"><span data-feather="unlock"></span></button>
              
            <?php   
              }
            ?>
            <?php if(!empty($is_crypt)){ ?>
              <a href="post.php?lock_volume=<?php echo $volume; ?>" class="btn btn-outline-secondary"><span data-feather="lock"></span></a>
            <?php } ?>
            <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteVolume<?php echo $volume; ?>"><span data-feather="trash"></span></button>
          </div>
          <?php }else{ ?>
          <div class="p-3">
          <?php } ?>
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
      unset($disk_part_in_array);
      unset($array_disk_part_array);
      unset($disk_in_array);
      unset($is_crypt);
      } 
      ?>
    </tbody>
  </table>

</div>

<?php 
require_once "modals/volume_add.php";
require_once "modals/volume_add_raid.php";
require_once "includes/footer.php";
