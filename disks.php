<?php 

  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Disks</h2>
    <a href="disks.php" class="btn btn-outline-secondary">Rescan</a>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Disk</th> 
          <th>Vendor</th>
          <th>Serial</th>
          <th>Capacity</th>
          <th>Type</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | awk '{print $1}'", $disk_list_array);
        foreach ($disk_list_array as $disk) {
          $hdd_smart = exec("smartctl -i /dev/$disk | grep 'SMART support is' | cut -d' ' -f 8-");

          $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Model Family:' | awk '{print $3,$4,$5,$6}'");
          if(empty($disk_vendor)){
            $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Device Model:' | awk '{print $3,$4,$5,$6}'");
          }
          if(empty($disk_vendor)){
            $disk_vendor = exec("lsblk -n -o kname,type,vendor /dev/$disk | grep disk  | awk '{print $3}'");
          }
          if(empty($disk_vendor)){
            $disk_vendor = exec("lsblk -n -o kname,type,model /dev/$disk | grep disk  | awk '{print $3}'");
          }
          if(empty($disk_vendor)){
            $disk_vendor = "-";
          }
          $disk_serial = exec("lsblk -n -o kname,type,serial /dev/$disk | grep disk  | awk '{print $3}'");
          $disk_size = exec("lsblk -n -o kname,type,size /dev/$disk | grep disk | awk '{print $3}'");
          
          $disk_type = exec("smartctl -i /dev/$disk | grep 'Rotation Rate:' | awk '{print $3,$4,$5}'");
          if($disk_type == '7200 rpm'){
            $disk_type = "HDD";
          }elseif($disk_type == '5400 rpm'){
            $disk_type = "HDD";
          }elseif($disk_type == 'Solid State Device'){
            $disk_type = "SSD";
          }else{
            $disk_type = "-";
          }

          ?>
        <tr>
          <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
          <td><?php echo $disk_vendor; ?></td>
          <td><?php echo $disk_serial; ?></td>
          <td><?php echo $disk_size; ?>B</td>
          <td><?php echo $disk_type; ?></td>
          <td>
            <div class="btn-group mr-2">
              <a href="hdd_info.php?hdd=<?php echo $disk; ?>" class="btn btn-outline-secondary btn-sm">Health Info</a>
            </div>
          </td>
        </tr>
    <?php } ?>
      </tbody>
    </table>
  </div>

</main>

<?php include("footer.php"); ?>