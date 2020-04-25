<?php 
    
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("smartctl --scan | awk '{ print $1 '}", $drive_list);

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
    $hdd_smart = exec("smartctl -i /dev/sda | grep 'SMART support is' | cut -d' ' -f 8-");
    if($hdd_smart == "Unavailable - device lacks SMART capability."){
      $hdd_temp = "-";
      $hdd_power_on_hours = "-";
      $hdd_bad_blocks = "-";
    }else{
      $hdd_temp = exec("smartctl -a $hdd | grep  'Temperature' | awk '{ print $10 '}");
      $hdd_temp = ($hdd_temp * 1.8) + 32;
      $hdd_temp = "$hdd_temp &#176;F";
      $hdd_power_on_hours = exec("smartctl -a $hdd | grep 'Power_On_Hours' | awk '{ print $10 '}");
      $hdd_power_on_hours = "$hdd_power_on_hours Hours";
      $hdd_power_on_days = $hdd_power_on_hours / 24;
      $hdd_power_on_days = floor($hdd_power_on_days);
      $hdd_power_on_days = "$hdd_power_on_days Days";
      $hdd_bad_blocks = exec("smartctl -a $hdd | grep 'Reallocated_Sector_Ct' | awk '{ print $10 '}");
    }

    $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family' | cut -d' ' -f 6-");
    if(empty($hdd_vendor)){
      $hdd_vendor = exec("smartctl -i $hdd | grep 'Vendor' | cut -d' ' -f 6-");
    }
    $hdd_serial = exec("smartctl -i $hdd | grep Serial | awk '{ print $3 '}");
    if(empty($hdd_serial)){
      $hdd_serial = "-";
    }
    $hdd_model = exec("smartctl -i $hdd | grep 'Device Model:' | cut -d' ' -f 6-");
    $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity' | awk '{ print $5 '}");
    $hdd_label_size = str_replace(["["], "", $hdd_label_size);
    $hdd_label_size = str_replace(["]"], "", $hdd_label_size);
    //$hdd_label_size = str_replace([" "], "", $hdd_label_size);
    $hdd_label_size = str_replace([".00"], "", $hdd_label_size);
    $hdd_label_size = str_replace([".0"], "", $hdd_label_size);
    $hdd_label_size = round($hdd_label_size);
    //$hdd_part_valid = exec("fdisk -l $hdd | grep 'Device Boot'");
    $hdd_type = exec("smartctl -i $hdd | grep 'Rotation Rate' | cut -d' ' -f 6-");
    if(empty($hdd_type)){
      $hdd_type = "-";
    }
    $hdd_health = exec("smartctl -H $hdd | grep 'SMART overall-health'| awk '{ print $6 '}");

?>
      <tr>
        <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $hdd_short_name; ?></td>
        <td><?php echo $hdd_vendor; ?><br><small><?php echo $hdd_model; ?></small></td>
        <td><?php echo $hdd_serial; ?></td>
        <td><?php echo $hdd_label_size; ?>GB</td>
        <td><?php echo $hdd_type; ?></td>
        <td><?php echo $hdd_power_on_hours; ?><br><small><?php echo $hdd_power_on_days; ?></small></td>
        <td><?php echo $hdd_bad_blocks; ?></td>
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
