<?php

require_once "setup_header.php";
	
$os_disk = exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");
exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v $os_disk | awk '{print $1}'", $disk_list_array);

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="index.php">Welcome</a></li>
	    <li class="breadcrumb-item"><a href="setup_timezone.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item active">Volume</li>
	  </ol>
	</nav>
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <h2>Volume Creation</h2>
    <a href="setup_volume.php" class="btn btn-outline-secondary">Rescan for new Disks</a>
  </div>

  <hr>

  <?php include("../alert_message.php"); ?>

  <form method="post" action="setup_post.php" autocomplete="off">
        <div class="modal-body">
          <div class="form-group">
            <label>Volume Name</label>
            <input type="text" class="form-control" name="volume_name" pattern="[a-zA-Z0-9-_]{1,15}" required>
          </div>

          <div class="form-group">
            <label>Select Disks</label>
            <?php
							foreach($disk_list_array as $disk){
					       $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Device Model:' | awk '{print $3,$4,$5}'");
							  if(empty($disk_vendor)){
				          $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Model Number:' | awk '{print $3,$4,$5,$6}'");
				        }
							  if(empty($disk_vendor)){
							    $disk_vendor = exec("lsblk -n -o kname,type,vendor /dev/$disk | grep disk  | awk '{print $3}'");
							  }
							  if(empty($disk_vendor)){
							    $disk_vendor = exec("lsblk -n -o kname,type,model /dev/$disk | grep disk  | awk '{print $3}'");
							  }
						    $disk_serial = exec("lsblk -n -o kname,type,serial /dev/$disk | grep disk  | awk '{print $3}'");
						    $disk_size = exec("lsblk -n -o kname,type,size /dev/$disk | grep disk | awk '{print $3}'");
							?>
              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input disk-checkbox" name="disks[]" value="<?php echo $disk; ?>" id="disk_<?php echo $disk; ?>">
                <label class="custom-control-label" for="disk_<?php echo $disk; ?>"> 
                  <span class="mr-2" data-feather="hard-drive"></span><?php echo "$disk_vendor ($disk_size"."B)<div class='text-secondary ml-4'>$disk_serial</div><small class='ml-4'>$disk</small>"; ?>
                </label>
              </div>
            <?php } ?>
            <small class="form-text text-danger">
              <strong>Warning:</strong> This will <u>Delete</u> all Data on the selected Storage Devices.
            </small>
          </div>

          <div class="form-group raid-options" style="display:none;">
            <label>RAID Type</label>
            <select class="form-control" name="raid">
              <option value="">--No RAID--</option>
              <option value="1" data-min="2">RAID 1 (Mirroring)</option>
              <option value="5" data-min="3">RAID 5 (Parity)</option>
              <option value="6" data-min="4">RAID 6 (Double Parity)</option>
              <option value="10" data-min="4">RAID 10 (Mirror/Stripe)</option>
            </select>
          </div>

          <button type="submit" name="setup_volume" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
        </div>
      </form>

      <script>
        document.querySelectorAll('.disk-checkbox').forEach(cb => cb.addEventListener('change', () => {
          const selectedDisks = document.querySelectorAll('.disk-checkbox:checked').length;
          document.querySelector('.raid-options').style.display = selectedDisks > 1 ? 'block' : 'none';
          document.querySelectorAll('.raid-options option').forEach(option => {
            option.disabled = selectedDisks < parseInt(option.getAttribute('data-min'));
          });
        }));
      </script>
</main>

<?php require_once "setup_footer.php";
