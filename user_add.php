<?php 
  
require_once "includes/include_all.php";
  
exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
 
?>

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add User</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">
          
          <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" name="username" required pattern="[a-z0-9]{1,20}" autofocus>
          </div>
          
          <div class="form-group">
            <label>Password:</label>
            <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
          </div>

          <div class="form-group">
            <label>Description</label>
            <input type="text" class="form-control" name="comment">
          </div>
          
          <legend>Groups</legend>
          
          <?php foreach ($group_array as $group) { ?>
          <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" name="group[]" value="<?php echo "$group"; ?>">
            <label class="form-check-label ml-1"><?php echo "$group"; ?></label>
        	</div>

          <?php } ?>

         
        <div class="modal-footer">
           <button type="submit" name="user_add" class="btn btn-primary">Submit</button>
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once "includes/footer.php";
