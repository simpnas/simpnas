<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /$config_mount_target", $volume_array);
    exec("ls /dev/mapper/crypt*", $encrypted_volume_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <?php include("nav_volume.php"); ?>
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">

    <h2>Volumes</h2>
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
        Add
      </button>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="volume_add.php">Volume</a>
        <a class="dropdown-item" href="volume_add_raid.php">RAID Volume</a>
        <a class="dropdown-item" href="volume_add_backup.php">Backup Volume</a>
        <a class="dropdown-item" href="#">Encrypted Volume</a>
      </div>
    </div>

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
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Disk(s)</th>
          <th>Share Reference</th>
          <th>Usage</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        
        <?php

        foreach ($volume_array as $volume){     
          $mounted = exec("df | grep $volume");
          if(empty($mounted)){
            $disk = basename(exec("cat /etc/fstab | grep $volume | awk '{print $1}'"));
            $share_list = "-";
          }else{
            $disk = basename(exec("findmnt -n -o SOURCE --target /$config_mount_target/$volume"));
            $total_space = exec("df -h | grep /$config_mount_target/$volume | awk '{print $2}'");
            $used_space = exec("df -h | grep /$config_mount_target/$volume | awk '{print $3}'");
            $free_space = exec("df -h | grep /$config_mount_target/$volume | awk '{print $4}'");
            $used_space_percent = exec("df | grep /$config_mount_target/$volume | awk '{print $5}'");
            exec("ls /$config_mount_target/$volume | grep -v docker | grep -v lost+found", $share_list_array);
            $share_list = implode(", ",$share_list_array);
          }
          
        ?>
        
        <tr>
          <td><span class="mr-2" data-feather="database"></span><?php echo $volume; ?></td>
          <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
          <td><span class="mr-2" data-feather="folder"></span><?php echo $share_list ?></td>
          <td>
            <?php if(empty($mounted)){ ?>
            <div class="text-danger">Not Mounted</div>
            <?php }else{ ?>
            <div class="progress">
              <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
            </div>
            <small><?php echo $used_space; ?> used of <?php echo $total_space; ?></small>
            <?php } ?>  
          </td>
          <td>
            <div class="btn-group mr-2">
              
              <button class="btn btn-outline-secondary"><span data-feather="edit"></span></button>
              <?php
              if(empty($mounted)){
              ?>
                <a href="post.php?mount_volume=<?php echo $volume; ?>" class="btn btn-outline-success"><span data-feather="play-circle"></span></a>
              <?php
              }else{
              ?>
                <a href="post.php?unmount_volume=<?php echo $volume; ?>" class="btn btn-outline-warning"><span data-feather="stop-circle"></span></a>
              <?php
              }
              ?>
              <a href="post.php?volume_delete=<?php echo $volume; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
          </td>
        </tr>
        <?php 
        unset($share_list_array);
        } 
        ?>
      </tbody>
    </table>

    <hr>

    <h4>Encrypted Volumes</h4>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        
        <?php

        foreach ($encrypted_volume_array as $encrypted_volume){     
          
        ?>
        
        <tr>
          <td><span class="mr-2" data-feather="lock"></span><?php echo basename($encrypted_volume); ?></td>
          <td>
            <div class="btn-group mr-2">
              <a href="post.php?unmount_volume=<?php echo $volume; ?>" class="btn btn-outline-primary"><span data-feather="unlock"></span></a>
              <a href="post.php?delete_volume=<?php echo $volume; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
          </td>
        </tr>
        <?php 
        unset($share_list_array);
        $share_list = '';
        } 
        ?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
