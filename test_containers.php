<?php 
  
require_once "includes/include_all.php";

//$json_docker_stats = exec("docker stats --no-stream --format '{{ json . }}'");
//$data = json_decode($json_docker_stats);
//exec("docker stats --no-stream", $containers_array);
$containers = shell_exec("docker stats --no-stream");

?>
  
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">

  <h2>Containers</h2>

</div>

<?php

//print_r($data);
echo "<pre>$containers</pre>"; 
?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Container</th>
        <th>CPU %</th>
        <th>Memory Usage</th>
        <th>Net I/O</th>
        <th>Disk I/O</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
      <?php
        
      ?>
      
      <tr>

      </tr>
      
    </tbody>
  </table>
</div>

<?php require_once "includes/footer.php";
