<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /$config_mount_target", $volume_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">

    <h2>Volumes</h2>
    <a class="btn btn-outline-primary" href="volume_add.php">Create Volume</a>

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

  </div>
</main>

<?php include("footer.php"); ?>
