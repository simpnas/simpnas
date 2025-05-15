<?php 
require_once "includes/include_all.php";
$disks = getDisks();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Disks</h2>
  <a href="disks.php" class="btn btn-outline-secondary">Rescan</a>
</div>

<?php include("alert_message.php"); ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Disk</th>
        <th>Vendor</th>
        <th>Serial</th>
        <th>Capacity</th>
        <th>Type</th>
        <th>Health</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php

      foreach ($disks as $disk) {
        if (isset($disk['name'])) {
          $name = $disk['name'];
        } else {
          $name = '-';
        }

        if (!empty($disk['vendor'])) {
          $vendor = $disk['vendor'];
        } else {
          $vendor = '-';
        }

        if (!empty($disk['serial'])) {
          $serial = $disk['serial'];
        } else {
          $serial = '-';
        }

        if (!empty($disk['size'])) {
          $size = $disk['size'] . 'B';
        } else {
          $size = '-';
        }

        if (!empty($disk['type'])) {
          $type = $disk['type'];
        } else {
          $type = '-';
        }

        if (!empty($disk['health'])) {
          $health = $disk['health'];
        } else {
          $health = 'Unknown';
        }

        if (isset($disk['has_smart']) && $disk['has_smart'] === 'yes') {
          $has_smart = true;
        } else {
          $has_smart = false;
        }
      ?>
        <tr>
          <td>
            <span class="mr-2" data-feather="hard-drive"></span>
            <?php echo htmlspecialchars($name); ?>
          </td>
          <td><?php echo htmlspecialchars($vendor); ?></td>
          <td><?php echo htmlspecialchars($serial); ?></td>
          <td><?php echo htmlspecialchars($size); ?></td>
          <td><?php echo htmlspecialchars($type); ?></td>
          <td>
            <?php
            $health_lower = strtolower($health);
            if ($health_lower === 'passed') {
              echo '<span class="text-success">' . htmlspecialchars($health) . '</span>';
            } elseif ($health_lower === 'failed') {
              echo '<span class="text-danger">' . htmlspecialchars($health) . '</span>';
            } else {
              echo '<span class="text-muted">' . htmlspecialchars($health) . '</span>';
            }
            ?>
          </td>
          <td>
            <div class="btn-group">
              <?php
              if ($has_smart) {
                echo '<a href="hdd_info.php?hdd=' . urlencode($name) . '" class="btn btn-outline-secondary btn-sm">Health Info</a>';
              } else {
                echo '<span class="btn btn-sm btn-light disabled">No SMART</span>';
              }
              ?>
            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once "includes/footer.php"; ?>
