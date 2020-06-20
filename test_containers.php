<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    include("side_nav.php");
    //$json_docker_stats = exec("docker stats --no-stream --format '{{ json . }}'");
    //$data = json_decode($json_docker_stats);
    //exec("docker stats --no-stream", $containers_array);
    $containers = shell_exec("docker stats --no-stream");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">

    <h2>Containers</h2>
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
        Add
      </button>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="volume_add.php">Container</a>
        <a class="dropdown-item" href="volume_add_raid.php">RAID Volume</a>
        <a class="dropdown-item" href="volume_add_backup.php">Backup Volume</a>
        <a class="dropdown-item" href="#">Encrypted Volume</a>
      </div>
    </div>

  </div>

  <?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>
  
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
</main>

<?php include("footer.php"); ?>
