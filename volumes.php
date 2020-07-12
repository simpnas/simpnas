<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  exec("ls /volumes | grep -v sys-vol", $volume_array);

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Volumes</h2>
    <a class="btn btn-outline-primary" href="volume_add.php">Create Volume</a>
    <a class="btn btn-outline-primary" href="volume_add_raid.php">Create RAID Volume</a>
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

        if(file_exists('/volumes/sys-vol')){
          $disk = basename(exec("findmnt -n -o SOURCE --target /volumes/sys-vol"));
          $total_space = exec("df -h | grep -w / | awk '{print $2}'");
          $used_space = exec("df -h | grep -w / | awk '{print $3}'");
          $free_space = exec("df -h | grep -w / | awk '{print $4}'");
          $used_space_percent = exec("df | grep -w / | awk '{print $5}'");
        
        ?>
        
          <tr>
            <td><span class="mr-2" data-feather="database"></span>sys-vol</td>
            <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
            <td>
              <div class="progress">
                <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
              </div>
              <small><?php echo $used_space; ?>B used of <?php echo $total_space; ?>B</small>
            </td>
            <td><div class="p-3"></div></td>
          </tr>
        <?php
        }
        ?>
        
        <?php

        foreach ($volume_array as $volume){     
          $mounted = exec("df | grep $volume");
          if(empty($mounted)){
            $disk = basename(exec("cat /etc/fstab | grep $volume | awk '{print $1}'"));
            $share_list = "-";
          }else{
            $disk = basename(exec("findmnt -n -o SOURCE --target /volumes/$volume"));
            $total_space = exec("df -h | grep -w /volumes/$volume | awk '{print $2}'");
            $used_space = exec("df -h | grep -w /volumes/$volume | awk '{print $3}'");
            $free_space = exec("df -h | grep -w /volumes/$volume | awk '{print $4}'");
            $used_space_percent = exec("df | grep -w /volumes/$volume | awk '{print $5}'");
          }
          
        ?>
        
        <tr>
          <td><span class="mr-2" data-feather="database"></span><?php echo $volume; ?></td>
          <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
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
            <?php if($config_home_volume != $volume){ ?>
            <div class="btn-group mr-2">
              <a href="post.php?volume_delete=<?php echo $volume; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
            <?php }else{ ?>
            <div class="p-3">
            <?php } ?>
          </td>
        </tr>
        <?php 
        } 
        ?>
      </tbody>
    </table>

  </div>
</main>

<?php include("footer.php"); ?>
