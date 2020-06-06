<?php

	if(file_exists('config.php')){
	  header("Location: login.php");
	}

	include("functions.php");
	exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br-", $net_devices_array);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SimpNAS | Setup - Network</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="index.php"><span data-feather="box"></span> SimpNAS <small>(<?php echo gethostname(); ?>)</small></a>
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
    <li class="breadcrumb-item active">Network Configuration</li>
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
  
  <h2>Step 1 - Network Configuration</h2>
  <hr>
  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Server Name</label>
	    <input type="text" class="form-control" name="hostname" value="<?php echo gethostname(); ?>" required>
	  </div>

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
			<select class="form-control" name="method" id="method" required>
				<option id="dhcp">DHCP</option>
				<option id="static">Static</option>
			</select>
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
	  
	  <button type="submit" name="setup_network" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>