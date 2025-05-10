<?php 
  
require_once "includes/include_all.php";

if(isset($_GET['docker_app'])){
  $docker_app = $_GET['docker_app'];
  $log = shell_exec("docker logs $docker_app");
}

exec("ls /volumes/$config_docker_volume/docker", $apps_array);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Docker Logs</h2>
  <ul class="nav nav-pills">
    <?php

    foreach($apps_array as $app){

    ?>

    <li class="nav-item">
      <a class="nav-link <?php if($_GET['docker_app'] == "$app"){ echo "active"; } ?>" href="?docker_app=<?php echo $app; ?>" onclick="$('#cover-spin').show(0)"><?php echo $app; ?></a>
    </li>
    
    <?php
    }
    ?>

  </ul>
</div>
  
<hr>

<?php
echo "<pre>$log</pre>";
?>

<?php require_once "includes/footer.php";
