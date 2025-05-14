<?php 

require_once "includes/include_all.php";

// Get user data using new shell script wrapper
$users = getUsers();

print_r($users);

// Sort alphabetically by username
usort($users, fn($a, $b) => strcmp($a['username'], $b['username']));

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Users</h2>
  <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#addUserModal">New User</button>
</div>

<?php include("alert_message.php"); ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>User</th>
        <th>Groups</th>
        <th>Home Usage</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($users as $user): 
        $username = $user['username'];
        $groups = !empty($user['groups']) ? implode(', ', $user['groups']) : '-';
        $comment = $user['comment'] ?: '-';
        $home_usage = $user['space_used'] ?: '-';
        $user_disabled = $user['user_enabled'] === 'no';
      ?>
        <tr>
          <td>
            <strong><span class="mr-2" data-feather="user"></span><?= htmlspecialchars($username) ?></strong>
            <?php if ($user_disabled): ?>
              <small class='text-muted'>(Disabled)</small>
            <?php endif; ?>
            <br>
            <div class="ml-4 text-secondary"><?= htmlspecialchars($comment) ?></div>
          </td>
          <td><?= htmlspecialchars($groups) ?></td>
          <td><?= htmlspecialchars($home_usage) ?>B</td>
          <td>
            <div class="btn-group mr-2">
              <a href="user_edit.php?username=<?= urlencode($username) ?>" class="btn btn-outline-secondary">
                <span data-feather="edit"></span>
              </a>
              <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteUser<?= htmlspecialchars($username) ?>">
                <span data-feather="trash"></span>
              </button>
              <?php if (!$user_disabled): ?>
                <a href="post.php?disable_user=<?= urlencode($username) ?>" class="btn btn-outline-warning">
                  <span data-feather="user-x"></span>
                </a>
              <?php else: ?>
                <a href="post.php?enable_user=<?= urlencode($username) ?>" class="btn btn-outline-success">
                  <span data-feather="user-check"></span>
                </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>

        <div class="modal fade" id="deleteUser<?= htmlspecialchars($username) ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-trash"></i> Delete <?= htmlspecialchars($username) ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <center>
                  <h3 class="text-secondary">Are you sure you want to</h3>
                  <h1 class="text-danger">Delete <strong><?= htmlspecialchars($username) ?></strong>?</h1>
                  <h5>This will delete all the user's data in their home directory.</h5>
                </center>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="post.php?user_delete=<?= urlencode($username) ?>" class="btn btn-outline-danger">
                  <span data-feather="trash"></span> Delete
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php 
require_once "modals/user_add.php";
require_once "includes/footer.php"; 
?>
