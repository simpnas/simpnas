<?php 
require_once "includes/include_all.php";
$disks = getDisks();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Disks</h2>
  <a href="disks.php" class="btn btn-outline-secondary">Rescan</a>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Disk</th>
        <th>Serial</th>
        <th>Capacity</th>
        <th>Type</th>
        <th>Health</th>
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
            <span class="mr-2" data-feather="hard-drive"></span><strong><?php echo htmlspecialchars($vendor); ?></strong>
            <br>
            <div class="ml-4 text-secondary"><?php echo htmlspecialchars($name); ?></div>
          </td>
          <td><?php echo htmlspecialchars($serial); ?></td>
          <td><?php echo htmlspecialchars($size); ?></td>
          <td><?php echo htmlspecialchars($type); ?></td>
          <td>
            <?php
            $health_lower = strtolower($health);
            if ($health_lower === 'passed') {
              echo '<a href="hdd_info.php?hdd=' . urlencode($name) . '"><span class="badge badge-pill p-2 badge-success">' . htmlspecialchars($health) . '</span></a>';
            } elseif ($health_lower === 'failed') {
              echo '<a href="hdd_info.php?hdd=' . urlencode($name) . '"><span class="badge badge-pill p-2 badge-danger">' . htmlspecialchars($health) . '</span></a>';
            } else {
              echo '<span class="text-muted">' . htmlspecialchars($health) . '</span>';
            }
            ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once "includes/footer.php"; ?>
