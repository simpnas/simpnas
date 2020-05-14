<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  if(isset($_GET['daemon'])){
    $log = shell_exec("cat /var/log/daemon.log");
  }elseif(isset($_GET['auth'])){
    $log = shell_exec("cat /var/log/auth.log");
  }elseif(isset($_GET['messages'])){
    $log = shell_exec("cat /var/log/messages");
  }elseif(isset($_GET['kernel'])){
    $log = shell_exec("cat /var/log/kern.log");
  }

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <?php include("nav_logs.php"); ?>
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Logs</h2>
    <a href="user_add.php" class="btn btn-outline-danger">Clear Log</a>
  </div>
  <hr>

  <?php
  echo "<pre>$log</pre>";
  ?>

</main>

<?php include("footer.php"); ?>