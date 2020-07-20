<?php 
  
  $config = include("config.php");
 	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  if(empty($config_ad_enabled)){
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  }else{
  	$ad_builtin_groups_array = array("Performance Monitor Users", "Remote Desktop Users", "Read-only Domain Controllers", "IIS_IUSRS", "Denied RODC Password Replication Group", "DnsUpdateProxy", "Enterprise Admins", "Replicator", "Windows Authorization Access Group", "Domain Controllers", "Pre-Windows 2000 Compatible Access", "Certificate Service DCOM Access", "Domain Guests", "Enterprise Read-only Domain Controllers", "Schema Admins", "Distributed COM Users", "Domain Computers", "Performance Log Users", "Network Configuration Operators", "Account Operators", "Backup Operators", "Terminal Server License Servers", "DnsAdmins", "Guests", "Cert Publishers", "Incoming Forest Trust Builders", "Print Operators", "Administrators", "Server Operators", "RAS and IAS Servers", "Allowed RODC Password Replication Group", "Cryptographic Operators", "Group Policy Creator Owners", "Event Log Readers");

  	exec("samba-tool group list", $all_groups_array);
  
  	$group_array = array_diff($all_groups_array,$ad_builtin_groups_array);
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
	    <li class="breadcrumb-item active">Add User</li>
	  </ol>
	</nav>

  <h2>Add User</h2>

  <?php include("alert_message.php"); ?>
  
  <form method="post" action="post.php" autocomplete="off">
	  
	  <div class="form-group">
	    <label>Username</label>
	    <input type="text" class="form-control" name="username" required pattern="[a-zA-Z0-9]{1,20}" autofocus>
	  </div>
	  
	  <div class="form-group">
	    <label>Password:</label>
	    <input type="password" class="form-control" name="password" data-toggle="password" required autocomplete="new-password">
	  </div>

	  <div class="form-group">
	    <label>Description</label>
	    <input type="text" class="form-control" name="comment">
	  </div>
	  
	  <legend>Groups</legend>

		<div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" checked disabled>
	    <label class="form-check-label ml-1"><?php if(empty($config_ad_enabled)){ echo "users"; }else{ echo "Domain Users"; } ?></label>
	  </div>
	  
	  <?php foreach ($group_array as $group) { ?>
	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="group[]" value="<?php echo "$group"; ?>">
	    <label class="form-check-label ml-1"><?php echo "$group"; ?></label>
		</div>

	  <?php } ?>

	  <?php
	  if(!empty($config_ad_enabled)){
	  ?>
	  
	  <legend>Optional</legend>

	  <div class="form-group">
	    <label>First Name</label>
	    <input type="text" class="form-control" name="first_name" pattern="[a-zA-Z0-9]{1,20}">
	  </div>

	  <div class="form-group">
	    <label>Last Name</label>
	    <input type="text" class="form-control" name="last_name" pattern="[a-zA-Z0-9]{1,20}">
	  </div>

	  <div class="form-group">
	    <label>Description</label>
	    <input type="text" class="form-control" name="description">
	  </div>

	  <div class="form-group">
	    <label>Email</label>
	    <input type="email" class="form-control" name="email">
	  </div>

	  <div class="form-group">
	    <label>Phone</label>
	    <input type="text" class="form-control" name="phone">
	  </div>

	  <?php
		}
		?>

	  <button type="submit" name="user_add" class="btn btn-primary">Submit</button>
	
	</form>

</main>

<?php include("footer.php"); ?>