<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");

  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nobody | grep -v nogroup", $group_array);
  array_push($group_array, "users");

  exec("smartctl --scan|awk '{ print $1 '}", $drive_list);

  exec("ls /$config_mount_target", $volume_array);

  $free_memory = exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
  $free_memory = floor($free_memory);
  $cpu_usage = exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\([0-9.]*\)%* id.*/\1/' | awk '{print 100 - $1'%'}'");
  $load_avg = exec("uptime |awk -F 'average:' '{ print $2}'");
  $uptime = exec("uptime -p | cut -c 4-");
  $system_time = exec("date");

  $cpu_model = exec("lscpu | grep 'Model name:' | sed -r 's/Model name:\s{1,}//g'");
  $memory_installed = exec("dmidecode --type memory | grep Size | awk '{print $2}'");

  $num_of_users = count($username_array);
  $num_of_groups = count($group_array);
  $num_of_volumes = count($volume_array);
  $num_of_disks = count($drive_list);
  $num_of_shares = exec("ls /etc/samba/shares | wc -l");
  $num_of_packages = exec("docker ps | wc -l") - 1;
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
      <h1 class="h2">Dashboard</h1>
    </div>
    <div class="row">
      <div class="col-md-6">
        <legend>Overview</legend>
        <table class="table">
          <tr>
            <td>Hostname</td>
            <td><?php echo gethostname(); ?></td>
          </tr>

          <tr>
            <td>Processor:</td>
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
              Total: <?php echo $memory_installed; ?> MB
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
          <tr>
            <td>System Time</td>
            <td><?php echo $system_time; ?></td>
          </tr>
          <tr>
            <td>Uptime</td>
            <td><?php echo $uptime; ?></td>
          </tr>
        </table>
      </div>
      <div class="col-md-6">
        <?php
          foreach($volume_array as $volume){
        ?>
            <div class="col-md-12">
              <canvas id="doughnutChart<?php echo $volume; ?>"></canvas>
            </div>
        <?php } ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-2">
        <div class="card text-center">
  	  <div class="card-body">
  	    <h5 class="card-title">Users</h5>
  	    <p class="card-text"><?php echo $num_of_users; ?></p>
  	  </div>
  	</div>
  </div>
  <div class="col-md-2">
        <div class="card text-center">
  	  <div class="card-body">
  	    <h5 class="card-title">Groups</h5>
  	    <p class="card-text"><?php echo $num_of_groups; ?></p>
  	  </div>
  	</div>
  </div>
  <div class="col-md-2">
        <div class="card text-center">
  	  <div class="card-body">
  	    <h5 class="card-title">Disks</h5>
  	    <p class="card-text"><?php echo $num_of_disks; ?></p>
  	  </div>
  	</div>
  </div>
  <div class="col-md-2">
        <div class="card text-center">
  	  <div class="card-body">
  	    <h5 class="card-title">Volumes</h5>
  	    <p class="card-text"><?php echo $num_of_volumes; ?></p>
  	  </div>
  	</div>
  </div>
  <div class="col-md-2">
        <div class="card text-center">
      <div class="card-body">
        <h5 class="card-title">Shares</h5>
        <p class="card-text"><?php echo $num_of_shares; ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-2">
        <div class="card text-center">
      <div class="card-body">
        <h5 class="card-title">Packages</h5>
        <p class="card-text"><?php echo $num_of_packages; ?></p>
      </div>
    </div>
  </div>
  </div>

  </main>


        <!-- Graphs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
  <?php foreach($volume_array as $volume){
  		$free_space = disk_free_space("/mnt/$volume/");
        $total_space = disk_total_space("/mnt/$volume/");
        $used_space = $total_space - $free_space;
        $disk_used_percent = sprintf('%.0f',($used_space / $total_space) * 100);
        //$disk_used_percent = sprintf('%.2f',($used_space / $total_space) * 100); //Add 2 decimal to Percent
        //$free_space = formatSize($free_space);
        //$total_space = formatSize($total_space);
        //$used_space = formatSize($used_space);
  ?>


  new Chart(document.getElementById("doughnutChart<?php echo $volume; ?>"), {
type: 'doughnut',
data: {
  labels: ["Used", "Free"],
  datasets: [
    {
      label: "<?php echo $volume; ?>",
      backgroundColor: ["#99999", "#007bff"],
      data: [<?php echo $used_space; ?>,<?php echo $free_space; ?>]
    }
  ]
},
options: {
  title: {
    display: true,
    text: '<?php echo $volume; ?>'
  }
}
});

<?php } ?>

</script>

<?php include("footer.php"); ?>