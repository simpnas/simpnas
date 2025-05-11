<div class="modal fade" id="addGroupModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Group</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">  
         <div class="form-group">
           <label>Group</label>
           <input type="text" class="form-control" name="group" required pattern="[a-z0-9-]{1,25}" autofocus>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="group_add" class="btn btn-primary">Create</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    
    </div>
  </div>
</div>
