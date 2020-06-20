<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  exec("ls /volumes", $volume_array);
  exec("ls /dev/mapper/crypt*", $encrypted_volume_array);

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <?php include("nav_volume.php"); ?>
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">

    <h2>Encrypted Volumes</h2>
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

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
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
