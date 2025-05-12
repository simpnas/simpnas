<?php
$volumes = getVolumes();
?>

<div class="modal fade" id="addShareModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Share</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">
          
          <div class="form-group">
			    <label>Share Name</label>
			    <input type="text" class="form-control" name="name" required pattern="[a-zA-Z0-9-]{1,25}" autofocus>
			  </div>

			  <div class="form-group">
			    <label>Volume</label>
			    <select class="form-control" name="volume" required>
			    	<option value="">- Select Volume -</option>
			  		<?php
						foreach ($volumes as $volume_data) {
			        $volume = $volume_data['volume'];
			        $disk = $volume_data['disk'];
			        $total_space = $volume_data['total_space'];
			        $used_space = $volume_data['used_space'];
			        $free_space = $volume_data['free_space'];
			        $used_space_percent = $volume_data['use_percent'];
			        $is_mounted = $volume_data['is_mounted'];
							
						?>
							<?php if ($is_mounted === 'yes') { ?>
 							<option><?php echo $volume; ?></option>	
							<?php } ?>
						<?php
						}
						?>

				  </select>
			  </div>
			  
			  <div class="form-group">
			    <label>Description</label>
			    <textarea class="form-control" name="description" rows=3></textarea>
			  </div>

			  <div class="form-group form-check">
			    <input type="checkbox" class="form-check-input" name="read_only" value=1>
			    <label class="form-check-label ml-1">Read Only</label>
				</div>
			  
			  <div class="form-group">
					<label>Group Access</label>
					<select class="form-control" name="group" required>
				  	<option>users</option>
				  	<?php
						exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
						foreach ($group_array as $group) {
						?>
						<option><?php echo "$group"; ?></option>	

						<?php
						}
						?>

				  </select>  
				</div>

        <div class="modal-footer">
          <button type="submit" name="share_add" class="btn btn-primary">Create</button> 
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>