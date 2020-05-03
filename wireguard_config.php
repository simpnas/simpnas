<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    
    exec("ls /$config_mount_target/$config_docker_volume/docker/wireguard/peer* | grep -v nobody", $username_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Users</h2>
    <a href="user_add.php" class="btn btn-outline-primary">Add User</a>
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
    <table class="table table-striped" id="dt">
      <thead>
        <tr>
          <th>User</th>
          <th><span data-feather="users"></span></th>
          <th>Used Space</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 	  
        foreach($username_array as $username){
          $groups = str_replace(' ',", ",exec("groups $username | sed 's/\($username\| : \)//g'")); //replace space with a , and a space makes it look neater
          $home_dir_usage = exec("du -sh /$config_mount_target/$config_home_volume/$config_home_dir/$username | awk '{print $1}'");
        ?>
          <tr>
            <td><span class="mr-2" data-feather="user"></span><?php echo $username; ?></td>
            <td><?php echo $groups; ?></td>
            <td><?php echo $home_dir_usage; ?></td>
            <td>
              <div class="btn-group mr-2">
              <a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
              <a href="post.php?user_delete=<?php echo $username; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
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
