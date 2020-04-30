<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  exec("find /$config_mount_target/*/* -maxdepth 0 -type d -group users -printf '%f\n'", $users_owned_directories_array); ?>
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
      <h2>Groups</h2>
      <a href="group_add.php" class="btn btn-outline-primary">Add Group</a>
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
            <th><span data-feather="user"></span>
            <th>Reference Shares</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>    
            <td><span class="mr-2" data-feather="users"></span>users <small class="text-secondary">(Cannot be removed)</small><br><br></td>
            <td>Everyone</td>
            <td><?php echo implode(", ",$users_owned_directories_array); ?></td>
            <td>-</td>
          </tr>
          
          <?php 
          foreach ($group_array as $group){
            $users = exec("awk -F: '/^$group/ {print $4;}' /etc/group");
            if(empty($users)){
              $users = "-";
            }

            exec("find /$config_mount_target/*/* -maxdepth 0 -type d -group $group -printf '%f\n'",$group_owned_directories_array);
            $group_owned_directories = implode(", ",$group_owned_directories_array);
            if(empty($group_owned_directories)){
              $group_owned_directories = "-";
            }
            
          ?>
          
          <tr>    
            <td><span class="mr-2" data-feather="users"></span><?php echo $group; ?></td>
            <td><?php echo $users; ?></td>
            <td><?php echo $group_owned_directories; ?></td>
            <td>
              <div class="btn-group mr-2">
                <a href="group_edit.php?group=<?php echo $group; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
                <a href="post.php?group_delete=<?php echo $group; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              </div>
            </td>
          </tr>
         <?php 
          unset($group_owned_directories_array);
          } 

          ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<?php include("footer.php"); ?>