<?php 
  
$usb_devices = "ConbeeII";
 
?>

<div class="modal fade" id="installHomeAssistantModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Install Home Assistant</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form method="post" action="post.php" autocomplete="off">

        <div class="modal-body">

          <div class="form-group">
            <label>Optional Zigbee USB Device</label>
            <select class="form-control" name="device">
              <option value="">None</option>
              <?php
              exec("ls /dev/serial/by-id/", $devices);
              foreach ($devices as $device) {
                $device_real_path = exec("readlink -f /dev/serial/by-id/$device");
              ?>
                <option value="<?php echo $device; ?>"><?php echo truncate($device, 40); ?></option>
              <?php
              }
              ?>
            </select>
          </div>

        </div>

        <div class="modal-footer">
           <button type="submit" name="install_homeassistant" class="btn btn-primary">Install</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
