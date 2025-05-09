<?php 
  
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  if(isset($_GET['daemon'])){
    $log = shell_exec("tac /var/log/daemon.log");
  }elseif(isset($_GET['auth'])){
    $log = shell_exec("tac /var/log/auth.log");
  }elseif(isset($_GET['messages'])){
    $log = shell_exec("tac /var/log/messages");
  }elseif(isset($_GET['kernel'])){
    $log = shell_exec("tac /var/log/kern.log");
  }

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

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
  echo "<pre>$log</pre>";
  ?>

</main>

<?php include("footer.php"); ?>