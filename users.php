<?php 
  
require_once "includes/include_all.php";
  
exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);

asort($username_array);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Users</h2>
  <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#addUser">Add User</button>
</div>

<?php include("alert_message.php"); ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>User</th>
        <th>Groups</th>
        <th>Home Usage</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php 	  
      foreach($username_array as $username){
        $groups = str_replace(' ',", ",exec("groups $username | sed 's/users //g' | sed 's/users//g' | sed 's/\($username\| : \)//g'")); //replace space with a , and a space makes it look neater and remove users group from the groups dislay
        if(empty($groups)){
          $groups = "-";
        }
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
              <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteUser<?php echo $username; ?>"><span data-feather="trash"></span></button>
              <?php 
              if(empty($user_disabled)){ 
              ?>
                <a href="post.php?disable_user=<?php echo $username; ?>" class="btn btn-outline-warning"><span data-feather="user-x"></span></a>
              <?php 
              }else{ 
              ?>
                <a href="post.php?enable_user=<?php echo $username; ?>" class="btn btn-outline-success"><span data-feather="user-check"></span></a>
              <?php 
              } 
              ?>
            </div>
          </td>
        </tr>

        <div class="modal fade" id="deleteUser<?php echo $username; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-trash"></i> Delete <?php echo $username; ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <center>
                  <h3 class="text-secondary">Are you sure you want to</h3>
                  <h1 class="text-danger">Delete <strong><?php echo $username; ?></strong>?</h1>
                  <h5>This will delete all the users data in their home Directory</h5>
                </center>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="post.php?user_delete=<?php echo $username; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span> Delete</a>
              </div>
            </div>
          </div>
        </div>

      <?php 
      } 
      ?>
    </tbody>
  </table>
</div>

<?php 

require_once "user_add.php";
require_once "includes/footer.php";
