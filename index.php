<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $username_array);
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

         <canvas id="doughnut-chart" width="300" height="50"></canvas>
         <canvas id="doughnut-chart2" width="300" height="50"></canvas>

          <canvas class="my-4" id="myChart" width="900" height="380"></canvas>
        </main>
<?php include("footer.php"); ?>
