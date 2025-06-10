<?php 
require_once "includes/include_all.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2 mt-3">
  <h2>UPS Configuration (WIP)</h2>
</div>

<form method="POST" action="post.php">
  <div class="form-group">
    <label for="upsSelect">Available UPS Devices (USB):</label>
    <select name="ups_id" id="upsSelect" class="form-control">
      <?php
        exec("nut-scanner -U", $output, $return_var);

        $device = [];
        foreach ($output as $line) {
          $line = trim($line);

          if (empty($line)) continue;

          if ($line === "[nutdev1]") {
            if (!empty($device)) {
              // Build option from previously collected device info
              $label = $device['product'] . " (" . $device['serial'] . ")";
              $value = base64_encode(json_encode($device));
              echo "<option value=\"$value\">" . htmlspecialchars($label) . "</option>";
              $device = [];
            }
          } else {
            list($key, $val) = explode("=", $line, 2);
            $key = trim($key);
            $val = trim($val, "\" ");
            $device[$key] = $val;
          }
        }

        // Add the last device
        if (!empty($device)) {
          $label = $device['product'] . " (" . $device['serial'] . ")";
          $value = base64_encode(json_encode($device));
          echo "<option value=\"$value\">" . htmlspecialchars($label) . "</option>";
        }
      ?>
    </select>
  </div>

  <button type="submit" name="ups_add" class="btn btn-primary">Add UPS</button>
</form>

<?php 
require_once "includes/footer.php";
?>