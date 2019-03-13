<?php include("header.php"); ?>

<main role="main" class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
    <li class="breadcrumb-item active" aria-current="page">Step 1</li>
  </ol>
</nav>
  <h1>First Time Setup Wizard</h1>
  <h4>Give your NAS a name</h4>
  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Name:</label>
	    <input type="text" class="form-control" name="hostname">
	  </div>
	  <h4>Set the Administrator/Root Password</h4>
	  <div class="form-group">
	    <label>Password:</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  <h4>Select a disk and create your first volume <small>Home Directories and Docker Shares will be added</small></h4>
	  <div class="form-group">
	    <label>Disk:</label>
	    <select class="form-control" name="disk">
	  	<?php
			exec("smartctl --scan|awk '{ print $1 '}", $drive_list);
			foreach ($drive_list as $hdd) {
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
	    <label>Volume Name:</label>
	    <input type="text" class="form-control" name="volume_name">
	  </div>
	  <h4>Add your first User</h4>
	  <div class="form-group">
	    <label>Username:</label>
	    <input type="text" class="form-control" name="username">
	  </div>
	  <div class="form-group">
	    <label for="pwd">Password:</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  <button type="submit" name="setup" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>