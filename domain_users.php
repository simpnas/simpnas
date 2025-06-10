<?php 
  
require_once "includes/include_all.php";

exec("samba-tool user list | grep -v krbtgt | grep -v Guest", $username_array);
asort($username_array);

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Users</h2>
    <a href="user_add.php" class="btn btn-outline-primary">Add User</a>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>User</th>
          <th>Groups</th>
          <th>Used Space</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 	  
        foreach($username_array as $username){
          $groups = str_replace(' ',", ",exec("groups $username | sed 's/\($username\| : \)//g'")); //replace space with a , and a space makes it look neater
          $home_dir_usage = exec("du -sh /volumes/$config_home_volume/users/$username | awk '{print $1}'");
          $comment = exec("cat /etc/passwd | grep $username | awk -F: '{print $5}'");
          $user_disabled = exec("cat /etc/shadow | grep $username | grep '!'");
        ?>
          <tr>
            <td>
              <strong><span class="mr-2" data-feather="user"></span><?php echo $username; ?></strong><?php if(!empty($user_disabled)){ echo "<small class='text-muted'> (Disabled)</small>"; } ?>
              <br>
              <div class="ml-4 text-secondary"><?php echo $comment; ?></div>
            </td>
            <td><?php echo $groups; ?></td>
            <td><?php echo $home_dir_usage; ?>B</td>
            <td>
              <div class="btn-group mr-2">
                <a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
                <?php if($username !== "administrator"){ ?>
                  <a href="post.php?user_delete=<?php echo $username; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
                  <?php if(empty($user_disabled)){ ?>
                  <a href="post.php?disable_user=<?php echo $username; ?>" class="btn btn-outline-warning"><span data-feather="user-x"></span></a>
                  <?php }else{ ?>
                  <a href="post.php?enable_user=<?php echo $username; ?>" class="btn btn-outline-success"><span data-feather="user-check"></span></a>
                  <?php } ?>
                <?php } ?>

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