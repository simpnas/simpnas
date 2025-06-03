<?php
$volumes = getVolumes();
?>

<div class="modal fade" id="addHomeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Users Home</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">

			  <div class="form-group">
			    <label>Volume</label>
			    <select class="form-control" name="volume_name" required>
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

        <div class="modal-footer">
          <button type="submit" name="create_home" class="btn btn-primary">Create</button> 
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>