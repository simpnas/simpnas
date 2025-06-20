<?php

require_once "includes/include_all.php";

$log = '';

if (isset($_GET['daemon'])) {
    $log = shell_exec("journalctl -u cron.service -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['auth'])) {
    $log = shell_exec("journalctl SYSLOG_IDENTIFIER=sshd -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['messages'])) {
    $log = shell_exec("journalctl -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['kernel'])) {
    $log = shell_exec("journalctl -k -n 500 --no-pager -r 2>&1");
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Logs</h2>
  <ul class="nav nav-pills">
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['daemon'])){ echo "active"; } ?>" href="?daemon" onclick="$('#cover-spin').show(0)">Daemon</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['auth'])){ echo "active"; } ?>" href="?auth" onclick="$('#cover-spin').show(0)">Auth</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['messages'])){ echo "active"; } ?>" href="?messages" onclick="$('#cover-spin').show(0)">Messages</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['kernel'])){ echo "active"; } ?>" href="?kernel" onclick="$('#cover-spin').show(0)">Kernel</a>
    </li>
  </ul>
</div>

<hr>

<?php
echo "<pre>" . htmlentities($log) . "</pre>";
?>

<?php require_once "includes/footer.php"; ?>