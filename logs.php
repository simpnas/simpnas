<?php

require_once "includes/include_all.php";

$log = '';
$title = 'Logs';

if (isset($_GET['systemd'])) {
    $title = 'Systemd Logs';
    $log = shell_exec("journalctl SYSLOG_IDENTIFIER=systemd -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['auth'])) {
    $title = 'Authentication Logs';
    $log = shell_exec("journalctl SYSLOG_IDENTIFIER=sshd -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['kernel'])) {
    $title = 'Kernel Logs';
    $log = shell_exec("journalctl -k -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['network'])) {
    $title = 'Network Logs';
    $log = shell_exec("journalctl SYSLOG_IDENTIFIER=NetworkManager -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['boot'])) {
    $title = 'Boot Logs';
    $log = shell_exec("journalctl -b -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['ssh'])) {
    $title = 'SSH Logs';
    $log = shell_exec("journalctl _COMM=sshd -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['services'])) {
    $title = 'Service Logs';
    $log = shell_exec("journalctl -u cron.service -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['web'])) {
    $title = 'Web Server Logs';
    $log = shell_exec("journalctl -u nginx.service -n 500 --no-pager -r 2>&1"); // Change to apache2.service if needed
} elseif (isset($_GET['php'])) {
    $title = 'PHP Logs';
    $log = shell_exec("journalctl _COMM=php -n 500 --no-pager -r 2>&1");
} elseif (isset($_GET['disk'])) {
    $title = 'Disk & SMART Logs';
    $log = shell_exec("(journalctl -u smartd.service -n 250; echo ''; journalctl -k | grep -Ei 'smart|fail|ata|nvme|sd[a-z]' ) --no-pager -r 2>&1");
} else {
    $log = shell_exec("journalctl -n 500 --no-pager -r 2>&1");
    $title = 'System Logs';
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2><?php echo $title; ?></h2>
  <ul class="nav nav-pills">
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['systemd'])) echo "active"; ?>" href="?systemd">Systemd</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['auth'])) echo "active"; ?>" href="?auth">Authentication</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['kernel'])) echo "active"; ?>" href="?kernel">Kernel</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['network'])) echo "active"; ?>" href="?network">Network</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['boot'])) echo "active"; ?>" href="?boot">Boot</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['ssh'])) echo "active"; ?>" href="?ssh">SSH</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['services'])) echo "active"; ?>" href="?services">Services</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['php'])) echo "active"; ?>" href="?php">PHP</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['disk'])) echo "active"; ?>" href="?disk">Disk & SMART Logs</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['web'])) echo "active"; ?>" href="?web">Web Server</a>
    </li>
  </ul>
</div>

<hr>

<pre style="font-size: 0.9rem; max-height: 75vh; overflow-y: auto;"><?php echo htmlentities($log); ?></pre>

<?php require_once "includes/footer.php"; ?>
