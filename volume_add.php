<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="volumes.php">Volumes</a></li>
    <li class="breadcrumb-item active">Add Volume</li>
  </ol>
</nav>

  <h2>Add Volume</h2>

  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Disk:</label>
	    <select class="form-control" name="disk">
	  	<?php
			exec("smartctl --scan | awk '{print $1}'", $drive_list);
			foreach ($drive_list as $hdd){
				if($hdd == "$config_os_disk")continue;
				$hdd_short_name = basename($hdd);
                $hdd_serial = exec("smartctl -i $hdd | grep Serial|awk '{ print $3 '}");
                $hdd_model = exec("smartctl -i $hdd | grep 'Device Model:'|cut -d' ' -f 7-");
                $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity' | cut -d' ' -f 8-");
				$hdd_label_size = str_replace(["["], "", $hdd_label_size);
				$hdd_label_size = str_replace(["]"], "", $hdd_label_size);
				$hdd_label_size = str_replace([" "], "", $hdd_label_size);
				$hdd_label_size = str_replace([".00"], "", $hdd_label_size);
				$hdd_label_size = str_replace([".0"], "", $hdd_label_size);    		
			?>
			<option value="<?php echo $hdd; ?>"><?php echo "$hdd_short_name - $hdd_model ($hdd_label_size)"; ?></option>	

		<?php
			}
		?>

	  </select>
	  </div>
	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="name">
	  </div>
	  <div class="form-group">
	  <div class="custom-control custom-checkbox">
		  <input type="checkbox" class="custom-control-input" name="encrypt" value="1" id="encrypt">
		  <label class="custom-control-label" for="encrypt">Encrypt</label>
		</div>
	  </div>
	  <div class="form-group" id="passwordbox">
	    <label>Password</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  <button type="submit" name="volume_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>