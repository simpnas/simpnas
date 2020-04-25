<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /$config_mount_target", $volume_array);
    exec("ls /dev/mapper/crypt*", $encrypted_volume_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Volumes</h2>
    <a href="volume_add.php" class="btn btn-outline-primary">Add Volume</a>
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
          $disk = basename(exec("findmnt -n -o SOURCE --target /$config_mount_target/$volume"));
          $free_space = disk_free_space("/$config_mount_target/$volume/");
          $total_space = disk_total_space("/$config_mount_target/$volume/");
          $used_space = $total_space - $free_space;
          $disk_used_percent = sprintf('%.0f',($used_space / $total_space) * 100);
          //$disk_used_percent = sprintf('%.2f',($used_space / $total_space) * 100); //Add 2 decimal to Percent
          $free_space = formatSize($free_space);
          $total_space = formatSize($total_space);
          $used_space = formatSize($used_space);
          exec("ls /$config_mount_target/$volume | grep -v docker | grep -v lost+found", $share_list_array);
          
        ?>
        
        <tr>
          <td><span class="mr-2" data-feather="database"></span><?php echo $volume; ?></td>
          <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
          <td><span class="mr-2" data-feather="folder"></span><?php echo implode(", ",$share_list_array); ?></td>
          <td>
            <div class="progress">
              <div class="progress-bar" style="width: <?php echo $disk_used_percent; ?>%"></div>
            </div>
            <small><?php echo $used_space; ?> used of <?php echo $total_space; ?></small>  
          </td>
          <td>
            <div class="btn-group mr-2">
              <button class="btn btn-outline-secondary"><span data-feather="edit"></span></button>
              <a href="post.php?unmount_volume=<?php echo $volume; ?>" class="btn btn-outline-warning"><span data-feather="stop-circle"></span></a>
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

    <h2>Encrypted Volumes</h2>

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
