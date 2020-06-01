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
  <h2>Logs</h2>
  <?php include("nav_logs.php"); ?>

  <?php
  echo "<pre>$log</pre>";
  ?>

</main>

<?php include("footer.php"); ?>