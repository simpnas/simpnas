<?php

	if(file_exists('config.php')){
	  header("Location: login.php");
	}

	include("functions.php");
	$os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");
	exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth", $net_devices_array);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#"><?php echo gethostname(); ?></a>
      <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
          <a class="nav-link" href="login.php">Logout</a>
        </li>
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
    <li class="breadcrumb-item active">Step 1</li>
  </ol>
</nav>

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
  
  <h2>First Time Setup Wizard</h2>
  <hr>
  <form method="post" action="post.php">
	  <legend>Naming Setup</legend>

	  <div class="form-group">
	    <label>Hostname</label>
	    <input type="text" class="form-control" name="hostname" value="<?php echo gethostname(); ?>">
	  </div>
	  
	  <legend>Disk and Volume Setup</legend>

	  <div class="form-group">
	    <label>Disk</small></label>
	    <select class="form-control" name="disk">
	  	<?php
			exec("smartctl --scan | awk '{print $1}'", $drive_list);
			foreach ($drive_list as $hdd) {
				if( $hdd == "$os_disk" )continue;
				$hdd_short_name = basename($hdd);
                $hdd_serial = exec("smartctl -i $hdd | grep Serial|awk '{ print $3 '}");
                $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family' | cut -d' ' -f 6-");
				if(empty($hdd_vendor)){
					$hdd_vendor = exec("smartctl -i $hdd | grep 'Vendor' | cut -d' ' -f 6-");
				}
                $hdd_model = exec("smartctl -i $hdd | grep 'Device Model:'|cut -d' ' -f 7-");
                $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity' | cut -d' ' -f 8-");
				$hdd_label_size = str_replace(["["], "", $hdd_label_size);
				$hdd_label_size = str_replace(["]"], "", $hdd_label_size);
				$hdd_label_size = str_replace([" "], "", $hdd_label_size);
				$hdd_label_size = str_replace([".00"], "", $hdd_label_size);
				$hdd_label_size = str_replace([".0"], "", $hdd_label_size);
			?>
			<option value="<?php echo $hdd; ?>"><?php echo "$hdd_short_name - $hdd_vendor ($hdd_label_size)"; ?></option>	

		<?php
			}
		?>

	  </select>
	  <small class="form-text text-muted">Select a disk to create your first volume on. user home and docker directories will be created here, note you cannot use the disk your OS is installed on and will not show here</small>
	  </div>
	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="volume_name">
	  </div>

	  <legend>Network Setup</legend>

	  <div class="form-group">
	  	<label>Network Interface</label>
		<select class="form-control" name="interface">
			<?php
			foreach ($net_devices_array as $net_device) {
			?>
			<option><?php echo $net_device; ?></option>
			<?php
			}
			?>
		</select>
	  </div>
	  
	  <div class="form-group">
		<label>Method</label>
		<select class="form-control" name="method" id="method">
			<option id="dhcp">DHCP</option>
			<option id="static">Static</option>
		</select>
		<small class="form-text text-muted">If DHCP is selected there is no need to fill out address, gateway or dns.</small>
	  </div>
	  
	  <div id="staticSettings">
		  <div class="form-group">
		    <label>Address/CIDR</label>
		    <input type="text" class="form-control" name="address" placeholder="ex 192.168.1.5/24">
		  </div>
		  
		  <div class="form-group">
		    <label>Gateway</label>
		    <input type="text" class="form-control" name="gateway">
		  </div>
		  
		  <div class="form-group">
		    <label>DNS Server(s)</label>
		    <input type="text" class="form-control" name="dns">
		  </div>
	  </div>

	  <legend>Setup User</legend>

	  <div class="form-group">
	    <label>Username</label>
	    <input type="text" class="form-control" name="username">
	  </div>
	  
	  <div class="form-group">
	    <label>Password</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  
	  <button type="submit" name="setup" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>