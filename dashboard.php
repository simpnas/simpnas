<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  array_push($group_array, "users");

  exec("smartctl --scan | awk '{print $1}'", $drive_list);

  exec("ls /volumes", $volume_array);

  $simpnas_version = exec("git rev-parse --short HEAD");

  $free_memory = exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
  $free_memory = floor($free_memory);
  //$cpu_usage = exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\([0-9.]*\)%* id.*/\1/' | awk '{print 100 - $1'%'}'");
  $free_swap = exec("free | grep Swap | awk '{print $3/$2 * 100.0}'");
  $free_swap = floor($free_swap);

  $load = exec("cat /proc/loadavg | awk '{print $1}'");

  //$load_avg = exec("uptime | awk -F 'average: ' '{ print $2}'");
  $uptime = exec("uptime -p | cut -c 4-");
  $system_time = exec("date");
  $machine_id = exec("cat /etc/machine-id");
  $cpu_model = exec("lscpu | grep 'Model name:' | sed -r 's/Model name:\s{1,}//g'");
  $cpu_cores = exec("lscpu | grep 'CPU(s):' | awk '{print $3}'");
  $cpu_speed = round(exec("lscpu | grep 'CPU max MHz:' | awk '{print $4}'"));
  $memory_installed = formatSize(exec("free -b | grep 'Mem:' | awk '{print $2}'"));
  $swap_total = formatSize(exec("free -b | grep 'Swap:' | awk '{print $2}'"));
  $OS = exec("hostnamectl | grep 'Operating System:' | awk '{print $3, $4, $5, $6}'");
  $kernel = exec("hostnamectl | grep 'Kernel:' | awk '{print $3}'");
  
  //Stats
  $num_of_users = count($username_array);
  $num_of_groups = count($group_array);
  $num_of_volumes = count($volume_array);
  $num_of_disks = count($drive_list);
  $num_of_shares = exec("ls /etc/samba/shares | wc -l");
  $num_of_apps = exec("docker ps | wc -l") - 1;
  exec("ls /etc/systemd/network", $network_list);
  $num_of_network_devices = count($network_list);
  exec("find /etc/cron.*/ -type f -name backup-* -printf '%f\n'", $backup_jobs_array);
  $num_of_backup_jobs = count($backup_jobs_array);
  
  //Service Status
  $status_service_smbd = exec("systemctl status smbd | grep running");
  $status_service_nmbd = exec("systemctl status nmbd | grep running");
  $status_service_docker = exec("systemctl status docker | grep running");
  $status_service_ssh = exec("systemctl status ssh | grep running");
  if(empty($status_service_smbd)){
    $status_service_smbd = "<i class='fa fa-circle text-danger'></i>";
  }else{
    $status_service_smbd = "<i class='fa fa-circle text-success'></i>";
  }
  if(empty($status_service_docker)){
    $status_service_docker = "<i class='fa fa-circle text-danger'></i>";
  }else{
    $status_service_docker = "<i class='fa fa-circle text-success'></i>";
  }
  if(empty($status_service_ssh)){
    $status_service_ssh = "<i class='fa fa-circle text-danger'></i>";
  }else{
    $status_service_ssh = "<i class='fa fa-circle text-success'></i>";
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="row">
    <div class="col-md-6">
      <div class="alert alert-success">
        <i class="fa fa-2x fa-check"></i> System is Healthy
      </div>
      <legend>Overview</legend>

      <table class="table mb-5">
        
        <tr>
          <td>Hostname</td>
          <th><?php echo $config_hostname; ?></th>
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
          <td>Version</td>
          <td><?php echo $simpnas_version; ?></td>
        </tr>

        <tr>
          <td>IP</td>
          <td><?php echo $config_primary_ip; ?></td>
        </tr>  

        <tr>
          <td>Processor</td>
          <td>
            <?php echo $cpu_model; ?>
          </td>
        </tr>
        
        <tr>
          <td>Memory</td>
          <td>
            Total: <?php echo $memory_installed; ?>
            <div class="progress">
                <div class="progress-bar <?php if($free_memory > 70){ echo "bg-warning"; } ?> <?php if($free_memory > 90){ echo "bg-danger"; } ?>" style="width: <?php echo $free_memory; ?>%">
                </div>
            </div>
            (<?php echo $free_memory; ?>% Used)
          </td>
        </tr>

        <tr>
          <td>Swap</td>
          <td>
            Total: <?php echo $swap_total; ?>
            <div class="progress">
                <div class="progress-bar <?php if($free_swap > 70){ echo "bg-warning"; } ?> <?php if($free_swap > 90){ echo "bg-danger"; } ?>" style="width: <?php echo $free_swap; ?>%">
                </div>
            </div>
            (<?php echo $free_swap; ?>% Used)
          </td>
        </tr>
        
        <tr>
          <td>Load</td>
          <td><?php echo $load; ?></td>
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
        
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-user text-secondary"></i> Users</h5>
              <p class="card-text"><?php echo $num_of_users; ?></p>
            </div>
          </div>
        </div>
  
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-users text-secondary"></i> Groups</h5>
              <p class="card-text"><?php echo $num_of_groups; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-folder text-secondary"></i> Shares</h5>
              <p class="card-text"><?php echo $num_of_shares; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-cube text-secondary"></i> Apps</h5>
              <p class="card-text"><?php echo $num_of_apps; ?></p>
            </div>
          </div>
        </div>    
      
      </div> <!-- nested /row -->

      <div class="row">
          <div class="col-12">
            <legend>Services</legend>
            <hr>
          </div>
          
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title"><i class="fa fa-server"></i> Samba</h5>
                <p class="card-text"><?php echo $status_service_smbd; ?></p>
              </div>
            </div>
          </div>
    
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title"><i class="fa fa-terminal"></i> SSH</h5>
                <p class="card-text"><?php echo $status_service_ssh; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title"><i class="fab fa-docker"></i> Docker</h5>
                <p class="card-text"><?php echo $status_service_docker; ?></p>
              </div>
            </div>
          </div>
      
      </div> <!-- nested /row -->


    </div> <!-- /col-6 -->




    
    <div class="col-md-6">

      <legend class="text-center mb-3">Volumes</legend>

      <?php
      foreach($volume_array as $volume){
        //check to see if mounted
        $mounted = exec("df | grep -w $volume");
        if($volume == "sys-vol"){
          $mounted = 1;
        }
        if(!empty($mounted)){
          $hdd = exec("findmnt -n -o SOURCE --target /volumes/$volume | cut -c -8");
          $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family:' | awk '{print $3,$4,$5}'");
          if(empty($hdd_vendor)){
            $hdd_vendor = exec("smartctl -i $hdd | grep 'Device Model:' | awk '{print $3,$4,$5}'");
          }
          if(empty($hdd_vendor)){
            $hdd_vendor = exec("smartctl -i $hdd | grep 'Vendor:' | awk '{print $2,$3,$4}'");
          }
          if(empty($hdd_vendor)){
            $hdd_vendor = "-";
          }
          $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity:' | cut -d '[' -f2 | cut -d ']' -f1");
        
      ?>
          <div class="col-md-12 mb-4">
            <h4 class="text-center"><?php echo $volume; ?></h4>
            <canvas id="doughnutChart<?php echo $volume; ?>"></canvas>
          </div>
        <?php 
          } 
        }
        ?>
    </div> <!-- /col-6 -->
  </div><!-- /row -->

</main>


        <!-- Graphs -->
<script src="plugins/Chart.js/Chart.min.js"></script>
<script>  

  <?php
  if(file_exists('/volumes/sys-vol')){
    $total_space = exec("df | grep -w / | awk '{print $2}'");
    $total_space_formatted = exec("df -h | grep -w / | awk '{print $2}'");
    $used_space = exec("df | grep -w / | awk '{print $3}'");
    $used_space_formatted = exec("df -h | grep -w / | awk '{print $3}'");
    $free_space = exec("df | grep -w /volumes/$volume | awk '{print $4}'");
    $free_space_formatted = exec("df -h | grep -w / | awk '{print $4}'");
    $used_space_percent = exec("df | grep -w / | awk '{print $5}'");
  ?>

    new Chart(document.getElementById("doughnutChartsys-vol"), {
      type: 'doughnut',
      data: {
        labels: ["<?php echo $used_space_formatted; ?> Used", "<?php echo $free_space_formatted; ?> Available"],
        datasets: [
          {
            backgroundColor: ["<?php if($used_space_percent > 85){ echo '#d9534f'; }else{ echo '#007bff'; } ?>", "#99999"],
            data: [<?php echo $used_space; ?>,<?php echo $free_space; ?>]    
          }
        ]
      }
    });
  
  <?php
  }
  ?>

  <?php 
  foreach($volume_array as $volume){
    $mounted = exec("df | grep $volume");
    if(!empty($mounted)){

      $total_space = exec("df | grep -w /volumes/$volume | awk '{print $2}'");
      $total_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $2}'");
      $used_space = exec("df | grep -w /volumes/$volume | awk '{print $3}'");
      $used_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $3}'");
      $free_space = exec("df | grep -w /volumes/$volume | awk '{print $4}'");
      $free_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $4}'");
      $used_space_percent = exec("df | grep -w /volumes/$volume | awk '{print $5}'");

  ?>

  new Chart(document.getElementById("doughnutChart<?php echo $volume; ?>"), {
    type: 'doughnut',
    data: {
      labels: ["<?php echo $used_space_formatted; ?> Used", "<?php echo $free_space_formatted; ?> Available"],
      datasets: [
        {
          backgroundColor: ["<?php if($used_space_percent > 85){ echo '#d9534f'; }else{ echo '#007bff'; } ?>", "#99999"],
          data: [<?php echo $used_space; ?>,<?php echo $free_space; ?>]    
        }
      ]
    }
  });

<?php 

  } 

}

?>

</script>

<?php include("footer.php"); 

?>