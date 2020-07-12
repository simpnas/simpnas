<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  exec("ls /volumes | grep -v sys-vol", $volume_array);

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Volumes</h2>
    <a class="btn btn-outline-primary" href="volume_add.php">Create Volume</a>
    <a class="btn btn-outline-primary" href="volume_add_raid.php">Create RAID Volume</a>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Disk(s)</th>
          <th>Usage</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

        <?php 

        if(file_exists('/volumes/sys-vol')){
          $disk = basename(exec("findmnt -n -o SOURCE --target /volumes/sys-vol"));
          $total_space = exec("df -h | grep -w / | awk '{print $2}'");
          $used_space = exec("df -h | grep -w / | awk '{print $3}'");
          $free_space = exec("df -h | grep -w / | awk '{print $4}'");
          $used_space_percent = exec("df | grep -w / | awk '{print $5}'");
        
        ?>
        
          <tr>
            <td><span class="mr-2" data-feather="database"></span>sys-vol</td>
            <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?></td>
            <td>
              <div class="progress">
                <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
              </div>
              <small><?php echo $used_space; ?>B used of <?php echo $total_space; ?>B</small>
            </td>
            <td><div class="p-3"></div></td>
          </tr>
        <?php
        }
        ?>
        
        <?php

        foreach ($volume_array as $volume){     
          $mounted = exec("df | grep $volume");
          if(empty($mounted)){
            $disk = basename(exec("cat /etc/fstab | grep $volume | awk '{print $1}'"));
            $share_list = "-";
          }else{
            $disk = basename(exec("findmnt -n -o SOURCE --target /volumes/$volume"));
            $total_space = exec("df -h | grep -w /volumes/$volume | awk '{print $2}'");
            $used_space = exec("df -h | grep -w /volumes/$volume | awk '{print $3}'");
            $free_space = exec("df -h | grep -w /volumes/$volume | awk '{print $4}'");
            $used_space_percent = exec("df | grep -w /volumes/$volume | awk '{print $5}'");
            $is_raid = exec("lsblk -o PKNAME,PATH,TYPE | grep $disk | grep raid");
	          if(!empty($is_raid)){
	          	$raid_type = exec("lsblk -o PKNAME,PATH,TYPE | grep $disk | grep raid | awk '{print $3}'");
	          	if($raid_type == 'raid0'){
	          		$raid_type = 'RAID 0 (Striping)';
	          	}elseif($raid_type == 'raid1'){
	          		$raid_type = 'RAID 1 (Mirroring)';
	          	}elseif($raid_type == 'raid5'){
	          		$raid_type = 'RAID 5 (Parity)';
	          	}elseif($raid_type == 'raid6'){
	          		$raid_type = 'RAID 6 (Double Parity)';
	          	}elseif($raid_type == 'raid10'){
	          		$raid_type = 'RAID 10 (Mirror/Stripe)';
	          	}
	          	exec("lsblk -o PKNAME,PATH,TYPE | grep /dev/$disk | awk '{print $1}'",$array_disk_part_array);
	    				$disk_part_in_array  = implode(', ', $array_disk_part_array);

	    				foreach($array_disk_part_array as $array_disk_part){
					      $disk_in_array .= " " . exec("lsblk -n -o PKNAME,PATH | grep /dev/$array_disk_part | awk '{print $1}'");
					    }
	          }
          }
          
        ?>
        
        <tr>
          <td><span class="mr-2" data-feather="database"></span><?php echo $volume; ?></td>
          <td><span class="mr-2" data-feather="hard-drive"></span><?php echo $disk; ?>
          	<?php if(isset($disk_part_in_array)){ echo "<br><small class='text-secondary'>$raid_type: $disk_in_array</small>"; } ?>
        	</td>
          <td>
            <?php if(empty($mounted)){ ?>
            <div class="text-danger">Not Mounted</div>
            <?php }else{ ?>
            <div class="progress">
              <div class="progress-bar" style="width: <?php echo $used_space_percent; ?>"></div>
            </div>
            <small><?php echo $used_space; ?>B used of <?php echo $total_space; ?>B</small>
            <?php } ?>  
          </td>
          <td>
            <?php if($config_home_volume != $volume){ ?>
            <div class="btn-group mr-2">
              <?php if(!empty($is_raid)){ ?>
              	<a href="raid_configuration.php?raid=<?php echo $disk; ?>" class="btn btn-outline-secondary"><span data-feather="settings"></span></a>
              <?php } ?>
              <a href="post.php?volume_delete=<?php echo $volume; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
            <?php }else{ ?>
            <div class="p-3">
            <?php } ?>
          </td>
        </tr>
        <?php 
        } 
        ?>
      </tbody>
    </table>

  </div>
</main>

<?php include("footer.php"); ?>
