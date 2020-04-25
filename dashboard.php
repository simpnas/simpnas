<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");

  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  array_push($group_array, "users");

  exec("smartctl --scan | awk '{print $1}'", $drive_list);

  exec("ls /$config_mount_target", $volume_array);

  $free_memory = exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
  $free_memory = floor($free_memory);
  $cpu_usage = exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\([0-9.]*\)%* id.*/\1/' | awk '{print 100 - $1'%'}'");
  $load_avg = exec("uptime | awk -F 'average: ' '{ print $2}'");
  $uptime = exec("uptime -p | cut -c 4-");
  $system_time = exec("date");
  $machine_id = exec("hostnamectl | grep 'Machine ID:' | awk '{print $3}'");
  $cpu_model = exec("lscpu | grep 'Model name:' | sed -r 's/Model name:\s{1,}//g'");
  $cpu_cores = exec("lscpu | grep 'CPU(s):' | awk '{print $3}'");
  $cpu_speed = round(exec("lscpu | grep 'CPU max MHz:' | awk '{print $4}'"));
  $memory_installed = formatSize(exec("free -b | grep 'Mem:' | awk '{print $2}'"));
  $OS= exec("hostnamectl | grep 'Operating System:' | awk '{print $3, $4, $5, $6}'");
  $kernel = exec("hostnamectl | grep 'Kernel:' | awk '{print $3}'");
  $num_of_users = count($username_array);
  $num_of_groups = count($group_array);
  $num_of_volumes = count($volume_array);
  $num_of_disks = count($drive_list);
  $num_of_shares = exec("ls /etc/samba/shares | wc -l");
  $num_of_apps = exec("docker ps | wc -l") - 1;
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
  </div>
  <div class="row">
    <div class="col-md-6">
      <legend>Overview</legend>
      <table class="table mb-5">
        
        <tr>
          <td>Hostname</td>
          <td><?php echo gethostname(); ?></td>
        </tr>

        <tr>
          <td>OS</td>
          <td><?php echo $OS; ?></td>
        </tr>

        <tr>
          <td>Kernel</td>
          <td><?php echo $kernel; ?></td>
        </tr>

        <tr>
          <td>Machine ID</td>
          <td><?php echo $machine_id; ?></td>
        </tr>

        <tr>
          <td>Processor</td>
          <td>
            <?php echo $cpu_model; ?>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $cpu_usage; ?>%">
                </div>
            </div>
            (<?php echo get_server_cpu_usage(); ?>% Used)

          </td>
        </tr>
        
        <tr>
          <td>Memory</td>
          <td>
            Total: <?php echo $memory_installed; ?>
            <div class="progress">
                <div class="progress-bar <?php if($free_memory > 85){ echo "bg-danger"; } ?>" style="width: <?php echo $free_memory; ?>%">
                </div>
            </div>
            (<?php echo $free_memory; ?>% Used)
          </td>
        </tr>
        
        <tr>
          <td>Load Average</td>
          <td><?php echo $load_avg; ?></td>
        </tr>

        <tr>
          <td>System Time</td>
          <td><?php echo $system_time; ?></td>
        </tr>
        
        <tr>
          <td>Uptime</td>
          <td><?php echo $uptime; ?></td>
        </tr>
      
      </table>
      
      <div class="row">
          <div class="col-md-12">
            <legend>Stats</legend>
            <hr>
          </div>
          
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Users</h5>
                <p class="card-text"><?php echo $num_of_users; ?></p>
              </div>
            </div>
          </div>
    
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Groups</h5>
                <p class="card-text"><?php echo $num_of_groups; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Apps</h5>
                <p class="card-text"><?php echo $num_of_apps; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center border-t-danger">
              <div class="card-body">
                <h5 class="card-title">Disks</h5>
                <p class="card-text"><?php echo $num_of_disks; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Volumes</h5>
                <p class="card-text"><?php echo $num_of_volumes; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Shares</h5>
                <p class="card-text"><?php echo $num_of_shares; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Backups</h5>
                <p class="card-text">WIP</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title">Net Devices</h5>
                <p class="card-text">WIP</p>
              </div>
            </div>
          </div>
        
      
      </div> <!-- nested /row -->


    </div> <!-- /col-6 -->
    
    <div class="col-md-6">
      <legend class="text-center mb-3">Volumes</legend>
      <?php
      foreach($volume_array as $volume){
        $disk = exec("findmnt -n -o SOURCE --target /$config_mount_target/$volume");
        $hdd_vendor = exec("smartctl -i $disk | grep 'Model Family' | cut -d' ' -f 6-");
        if(empty($hdd_vendor)){
          $hdd_vendor = exec("smartctl -i $disk | grep 'Vendor' | cut -d' ' -f 6-");
        }
      ?>
          <div class="col-md-12 mb-4">
            <h4 class="text-center"><?php echo $volume; ?></h4>
            <h5 class="text-center text-secondary"><?php echo $hdd_vendor; ?></h5>
            <canvas id="doughnutChart<?php echo $volume; ?>"></canvas>
          </div>
        <?php } ?>
    </div> <!-- /col-6 -->
  </div><!-- /row -->

</main>


        <!-- Graphs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
  <?php foreach($volume_array as $volume){
    $free_space = formatSizeWo(disk_free_space("/$config_mount_target/$volume/"));
    $total_space = formatSizeWo(disk_total_space("/$config_mount_target/$volume/"));
    $used_space = $total_space - $free_space;
    $disk_used_percent = sprintf('%.0f',($used_space / $total_space) * 100);
    $disk = exec("findmnt -n -o SOURCE --target /$config_mount_target/$volume");
    $hdd_vendor = exec("smartctl -i $disk | grep 'Model Family' | cut -d' ' -f 6-");
    if(empty($hdd_vendor)){
      $hdd_vendor = exec("smartctl -i $disk | grep 'Vendor' | cut -d' ' -f 6-");
    }
  ?>


  new Chart(document.getElementById("doughnutChart<?php echo $volume; ?>"), {
type: 'doughnut',
data: {
  labels: ["<?php echo $used_space ?>GB Used", "<?php echo $free_space ?>GB Free"],
  datasets: [
    {
      label: "<?php echo "$volume"; ?>",
      backgroundColor: ["#99999", "#007bff"],
      data: [<?php echo $used_space; ?>,<?php echo $free_space; ?>]
    }
  ]
},
options: {
  title: {
    display: false,
    text: '<?php echo "$volume$hdd_vendor"; ?>'
  }
}
});

<?php } ?>

</script>

<?php include("footer.php"); ?>