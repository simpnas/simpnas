<?php 
    include("header.php");
    include("side_nav.php");
    exec("smartctl --scan|awk '{ print $1 '}", $drive_list);

?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

           <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
            <h2>Disks</h2>
            <button class="btn btn-outline-secondary">Refresh</button>
          </div>

          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Disk</th> 
                  <th>Vendor</th>
                  <th>Serial</th>
                  <th>Capacity</th>
                  <th>Disk Type</th>
                  <th>On Hours</th>
                  <th>Bad Blks</th>
                  <th>Temp</th>
                  <th>Health</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php

            foreach ($drive_list as $hdd) {
                $hdd_short_name = basename($hdd);
                $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family'|cut -d' ' -f 6-");
                $hdd_serial = exec("smartctl -i $hdd | grep Serial|awk '{ print $3 '}");
                $hdd_model = exec("smartctl -i $hdd | grep 'Device Model:'|cut -d' ' -f 6-");
                $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity' | awk '{ print $5 '}");
              $hdd_label_size = str_replace(["["], "", $hdd_label_size);
              $hdd_label_size = str_replace(["]"], "", $hdd_label_size);
              //$hdd_label_size = str_replace([" "], "", $hdd_label_size);
              $hdd_label_size = str_replace([".00"], "", $hdd_label_size);
              $hdd_label_size = str_replace([".0"], "", $hdd_label_size);
              //$hdd_part_valid = exec("fdisk -l $hdd | grep 'Device Boot'");
              $hdd_temp = exec("smartctl -a $hdd | grep  'Temperature'|awk '{ print $10 '}");
              $hdd_power_on_hours = exec("smartctl -a $hdd | grep 'Power_On_Hours'|awk '{ print $10 '}");
              $hdd_power_on_days = $hdd_power_on_hours / 24;
              $hdd_power_on_days = floor($hdd_power_on_days);
             $hdd_bad_blocks = exec("smartctl -a $hdd | grep 'Reallocated_Sector_Ct'|awk '{ print $10 '}");
            $hdd_type = exec("smartctl -i $hdd | grep 'Rotation Rate'|cut -d' ' -f 6-");
            $hdd_health = exec("smartctl -H $hdd | grep 'SMART overall-health'|awk '{ print $6 '}");
  
              //$hdd_size = exec("df -h | grep '$hdd'|awk '{ print $2 '}");
              //$hdd_used = exec("df -h | grep '$hdd'|awk '{ print $3 '}");
              //$hdd_available = exec("df -h | grep '$hdd'|awk '{ print $4 '}");
              //$hdd_percent_used = exec("df -h | grep '$hdd'|awk '{ print $5 '}");
          ?>
                <tr>
                  <td><span data-feather="hard-drive"></span> <?php echo $hdd_short_name; ?></td>
                  <td><?php echo $hdd_vendor; ?><br><small><?php echo $hdd_model; ?></small></td>
                  <td><?php echo $hdd_serial; ?></td>
                  <td><?php echo $hdd_label_size; ?></td>
                  <td><?php echo $hdd_type; ?></td>
                  <td><?php echo $hdd_power_on_hours; ?> Hours<br><small><?php echo $hdd_power_on_days; ?> Days</small></td>
                  <td><?php echo $hdd_bad_blocks; ?></td>
                  <td><?php echo $hdd_temp; ?>&#176;C</td>
                  <td><p class="text-success"><?php echo $hdd_health; ?></p></td>
                  <td>
                    <div class="btn-group mr-2">
                    <a href="hdd_info.php?hdd=<?php echo $hdd_short_name; ?>" class="btn btn-outline-secondary"><span data-feather="info"></span></a>
                    <button class="btn btn-outline-danger"><span data-feather="zap"></span></button>
                  </div>
                  </td>
                </tr>
<?php } ?>
              </tbody>
            </table>
          </div>
          </div>
        </main>
<?php include("footer.php"); ?>
