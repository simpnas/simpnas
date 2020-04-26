<?php 
    
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("smartctl --scan | awk '{print $1}'", $drive_list);

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

 <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Disks</h2>
  <a href="disks.php" class="btn btn-outline-secondary">Refresh</a>
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

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Disk</th> 
        <th>Vendor</th>
        <th>Serial</th>
        <th>Capacity</th>
        <th>Disk Type</th>
        <th>On Time</th>
        <th>Temp</th>
        <th>Health</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php

  foreach ($drive_list as $hdd) {
    $hdd_short_name = basename($hdd);
    $hdd_smart = exec("smartctl -i /dev/sda | grep 'SMART support is' | cut -d' ' -f 8-");
    
    if($hdd_smart == "Unavailable - device lacks SMART capability."){
      $hdd_temp = "-";
      $hdd_power_on_hours = "-";
      //$hdd_bad_blocks = "-";
      $hdd_health = "-";
    }else{
      $hdd_temp = exec("smartctl -a $hdd | grep '194 Temperature_Celsius' | awk '{ print $10 '}");
      $hdd_temp = ($hdd_temp * 1.8) + 32;
      $hdd_temp = "$hdd_temp &#176;F";
      $hdd_power_on_hours = exec("smartctl -a $hdd | grep 'Power_On_Hours' | awk '{ print $10 '}");
      $hdd_power_on_hours = "$hdd_power_on_hours Hours";
      $hdd_power_on_days = $hdd_power_on_hours / 24;
      $hdd_power_on_days = floor($hdd_power_on_days);
      $hdd_power_on_days = "$hdd_power_on_days Days";
      $hdd_health = exec("smartctl -H $hdd | grep 'SMART overall-health' | awk '{print $6}'");
      //$hdd_bad_blocks = exec("smartctl -a $hdd | grep 'Reallocated_Sector_Ct' | awk '{ print $10 '}");
    }

    $hdd_make = exec("smartctl -i $hdd | grep 'Device Model:' | awk '{print $3}'");
    if($hdd_make == 'WDC'){
      $hdd_make = 'Western Digital';
    }else{
      $hdd_make = '';
    }

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

    $hdd_serial = exec("smartctl -i $hdd | grep 'Serial Number:' | awk '{print $3}'");
    if(empty($hdd_serial)){
      $hdd_serial = "-";
    }
    
    $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity:' | cut -d '[' -f2 | cut -d ']' -f1");
    
    $hdd_type = exec("smartctl -i $hdd | grep 'Rotation Rate:' | awk '{print $3,$4,$5}'");
    if($hdd_type == '7200 rpm'){
      $hdd_type = "HDD";
    }elseif($hdd_type == '5400 rpm'){
      $hdd_type = "HDD";
    }elseif($hdd_type == 'Solid State Device'){
      $hdd_type = "SSD";
    }else{
      $hdd_type = "-";
    }

?>
      <tr>
        <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $hdd_short_name; ?></td>
        <td><?php echo $hdd_vendor; ?></td>
        <td><?php echo $hdd_serial; ?></td>
        <td><?php echo $hdd_label_size; ?></td>
        <td><?php echo $hdd_type; ?></td>
        <td><?php echo $hdd_power_on_hours; ?><br><small><?php echo $hdd_power_on_days; ?></small></td>
        <td><?php echo $hdd_temp; ?></td>
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