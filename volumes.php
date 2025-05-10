<?php 
  
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  exec("ls /volumes", $volume_array);

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Volumes</h2>
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
        Create
      </button>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="volume_add.php">Simple</a>
        <a class="dropdown-item" href="volume_add_raid.php">RAID</a>
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

        foreach ($volume_array as $volume) {

          $mount_path = "/volumes/$volume";
          $device = trim(shell_exec("findmnt -n -o SOURCE --target $mount_path"));
          $mounted = !empty($device);
          $disk = $used_space = $total_space = $free_space = $used_space_percent = '';
          $raid_type = $disk_in_array = '';
          $disk_part_in_array = $array_disk_part_array = [];
          $is_crypt = false;

          if ($mounted) {
            $disk = basename($device);

            // Disk usage stats
            $total_space = trim(shell_exec("df -h --output=size $mount_path | tail -n1"));
            $used_space = trim(shell_exec("df -h --output=used $mount_path | tail -n1"));
            $free_space = trim(shell_exec("df -h --output=avail $mount_path | tail -n1"));
            $used_space_percent = trim(shell_exec("df --output=pcent $mount_path | tail -n1"));

            // Encrypted volume detection (if mounted via /dev/mapper/)
            if (strpos($device, '/dev/mapper/') === 0) {
              $is_crypt = true;
            }

            // Btrfs RAID detection
            exec("btrfs filesystem show $mount_path | grep 'devid' | awk '{print \$NF}'", $btrfs_devices);
            if (count($btrfs_devices) > 1) {
              $raid_type_raw = trim(shell_exec("btrfs filesystem df $mount_path | grep -m1 'Data' | awk '{print \$NF}'"));
              $raid_type = match ($raid_type_raw) {
                'RAID0' => 'RAID 0 (Striping)',
                'RAID1' => 'RAID 1 (Mirroring)',
                'RAID10' => 'RAID 10 (Mirror/Stripe)',
                default => strtoupper($raid_type_raw)
              };
              $disk_in_array = implode(', ', array_map('basename', $btrfs_devices));
            }
          } else {
            // Not mounted: try to find disk from /etc/fstab
            $fstab_entry = trim(shell_exec("grep '/volumes/$volume' /etc/fstab | awk '{print \$1}'"));
            $disk = basename($fstab_entry);
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
</main>

<?php include("footer.php"); ?>