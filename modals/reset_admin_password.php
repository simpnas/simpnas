<div class="modal fade" id="resetAdminPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Admin Password</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">
          
          <div class="form-group">
            <label>New Password:</label>
            <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
          </div>
         
        <div class="modal-footer">
           <button type="submit" name="reset_admin_password" class="btn btn-primary">Reset</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
