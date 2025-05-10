<?php
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  exec("ls /volumes", $volume_array);
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Volumes</h2>
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
        Create
      </button>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="volume_add.php">Simple</a>
        <a class="dropdown-item" href="volume_add_raid.php">RAID</a>
      </div>
    </div>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Disk(s)</th>
          <th>Usage</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($volume_array as $volume):
          $mount_path = "/volumes/$volume";
          $device = trim(shell_exec("findmnt -n -o SOURCE --target $mount_path"));
          $mounted = !empty($device);
          $disk = basename($device);
          $used_space = $total_space = $used_space_percent = $raid_type = $disk_in_array = '';
          $is_crypt = false;

          if ($mounted) {
            $total_space = trim(shell_exec("df -h --output=size $mount_path | tail -n1"));
            $used_space = trim(shell_exec("df -h --output=used $mount_path | tail -n1"));
            $used_space_percent = trim(shell_exec("df --output=pcent $mount_path | tail -n1"));

            exec("btrfs filesystem show $mount_path | grep 'devid' | awk '{print \$NF}'", $btrfs_devices);
            if (count($btrfs_devices) > 1) {
              $raid_type = trim(shell_exec("btrfs filesystem df $mount_path | grep -m1 'Data' | awk '{print \$NF}'"));
              $disk_in_array = implode(', ', array_map('basename', $btrfs_devices));
            }

            if (strpos($device, '/dev/mapper/') === 0) {
              $is_crypt = true;
            }
          }
        ?>
        <tr>
          <td><span class="mr-2" data-feather="database"></span><strong><?= $volume ?></strong></td>
          <td>
            <span class="mr-2" data-feather="hard-drive"></span><?= htmlspecialchars($disk) ?>
            <?php if (!empty($raid_type)): ?>
              <br><small class='text-secondary'><?= strtoupper($raid_type) ?>: <?= htmlspecialchars($disk_in_array) ?></small>
            <?php endif; ?>
            <?php if ($is_crypt): ?>
              <br><small class='text-secondary'>Encrypted Volume</small>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!$mounted): ?>
              <div class="text-danger">Not Mounted</div>
            <?php else: ?>
              <div class="progress">
                <div class="progress-bar" style="width: <?= $used_space_percent ?>"></div>
              </div>
              <small><?= $used_space ?>B used of <?= $total_space ?>B</small>
            <?php endif; ?>
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php if (!empty($raid_type)): ?>
                <a href="raid_configuration.php?raid=<?= htmlspecialchars($disk) ?>" class="btn btn-outline-secondary"><span data-feather="settings"></span></a>
              <?php endif; ?>
              <?php if ($config_home_volume !== $volume): ?>
                <?php if (!$mounted && $is_crypt): ?>
                  <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#mountCrypt<?= $disk ?>"><span data-feather="unlock"></span></button>
                <?php endif; ?>
                <?php if ($mounted && $is_crypt): ?>
                  <a href="post.php?lock_volume=<?= $volume ?>" class="btn btn-outline-secondary"><span data-feather="lock"></span></a>
                <?php endif; ?>
                <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteVolume<?= $volume ?>"><span data-feather="trash"></span></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteVolume<?= $volume ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-trash"></i> Delete <?= $volume ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center">
                <h3 class="text-secondary">Are you sure you want to</h3>
                <h1 class="text-danger">Delete <strong><?= $volume ?></strong>?</h1>
                <h5>This will delete all data within the Volume</h5>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="post.php?volume_delete=<?= $volume ?>" class="btn btn-outline-danger"><span data-feather="trash"></span> Delete</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Unlock Modal -->
        <?php if (!$mounted && $is_crypt): ?>
        <div class="modal fade" id="mountCrypt<?= $disk ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Unlock <?= $volume ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="post.php" autocomplete="off">
                <input type="hidden" name="disk" value="<?= htmlspecialchars($disk) ?>">
                <input type="hidden" name="volume" value="<?= htmlspecialchars($volume) ?>">
                <div class="modal-body text-center">
                  <i class="fa fa-8x fa-unlock text-secondary mb-3"></i>
                  <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required autofocus autocomplete="new-password">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="unlock_volume" class="btn btn-primary">Unlock</button>
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
