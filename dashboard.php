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

  $uptime = exec("uptime");

  $num_of_users = count($username_array);
  $num_of_groups = count($group_array);
  $num_of_volumes = count($volume_array);
  $num_of_disks = count($drive_list);
  $num_of_shares = count($shares);
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
      <h1 class="h2">Dashboard</h1>
      <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
          <button class="btn btn-sm btn-outline-secondary">Share</button>
          <button class="btn btn-sm btn-outline-secondary">Export</button>
        </div>
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
          <span data-feather="calendar"></span>
          This week
        </button>
      </div>
    </div>
    <div class="alert alert-warning" role="alert">
  This is a warning alert—check it out!
  </div>
  <div class="alert alert-success" role="alert">
  System is running just fine!
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
  </div>
  <div class="row">
    <div class="col-md-6">
      RAM
      <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: <?php echo $free_memory; ?>%"></div>
      
      </div>
    </div>
    </div>
    <div class="row">
    <?php
    	foreach($volume_array as $volume){
    ?>
      	<div class="col-md-4">
      		<canvas id="doughnutChart<?php echo $volume; ?>"></canvas>
      	</div>
    <?php	} ?>
   	</div>

    <canvas class="my-4" id="myChart" width="900" height="380"></canvas>
  </main>


        <!-- Graphs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
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
      backgroundColor: ["#3e95cd", "#007bff"],
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

  var ctx = document.getElementById("myChart");
  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
      datasets: [{
        data: [15339, 21345, 18483, 24003, 23489, 24092, 12034],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff'
      }]
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false
          }
        }]
      },
      legend: {
        display: false,
      }
    }
  });
</script>

<?php include("footer.php"); ?>