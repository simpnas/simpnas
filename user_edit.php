<?php 
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  if(isset($_GET['username'])){
		$username = $_GET['username'];
	}
  
  if(empty($config_ad_enabled)){
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
    array_push($group_array,"users");
    $group_member_array = explode(' ',exec("groups $username"));
  }else{
  	$ad_builtin_groups_array = array("Performance Monitor Users", "Remote Desktop Users", "Read-only Domain Controllers", "IIS_IUSRS", "Denied RODC Password Replication Group", "DnsUpdateProxy", "Enterprise Admins", "Replicator", "Windows Authorization Access Group", "Domain Controllers", "Pre-Windows 2000 Compatible Access", "Certificate Service DCOM Access", "Domain Guests", "Enterprise Read-only Domain Controllers", "Schema Admins", "Distributed COM Users", "Domain Computers", "Performance Log Users", "Network Configuration Operators", "Account Operators", "Backup Operators", "Terminal Server License Servers", "DnsAdmins", "Guests", "Cert Publishers", "Incoming Forest Trust Builders", "Print Operators", "Administrators", "Server Operators", "RAS and IAS Servers", "Allowed RODC Password Replication Group", "Cryptographic Operators", "Group Policy Creator Owners", "Event Log Readers");

  	exec("samba-tool group list", $all_groups_array);
  
  	$group_array = array_diff($all_groups_array,$ad_builtin_groups_array);
  	$group_member_array = explode(' ',exec("groups $username"));

  	$first_name = exec("samba-tool user show $username | grep givenName: | cut -d\  -f2-");
	  $last_name = exec("samba-tool user show $username | grep sn: | cut -d\  -f2-");
	  $description = exec("samba-tool user show $username | grep description: | cut -d\  -f2-");
	  $email = exec("samba-tool user show $username | grep mail: | cut -d\  -f2-");
	  $phone = exec("samba-tool user show $username | grep telephoneNumber: | cut -d\  -f2-");
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
	    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
	    <li class="breadcrumb-item active">Edit User</li>
	  </ol>
	</nav>

  <h2>Edit User</h2>
  
  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
	  
	  <div class="form-group">
	    <label>Username</label>
	    <input type="text" class="form-control" name="username" value="<?php echo $username; ?>" readonly>
	  </div>
	  
	  <div class="form-group">
	    <label>Password</label>
	    <input type="password" class="form-control" name="password" placeholder="Leave blank for no change" data-toggle="password" autocomplete="new-password">
	  </div>
	  
	  <legend>Groups</legend>
	  
	  <?php 
	  foreach($group_array as $group){ 
	  ?>
	  		
		  <div class="form-group form-check">
		    <input type="checkbox" class="form-check-input" name="group[]" <?php foreach($group_member_array as $group_member){ if($group == $group_member){ echo "checked"; } ?> value="<?php echo $group; ?>" <?php } ?> <?php if($group == 'Users' OR $group == 'users'){ echo "readonly"; } ?>>
		    <label class="form-check-label ml-1"><?php echo "$group"; ?></label>
		  </div>
		  
		<?php } ?>

	  <?php
	  if(!empty($config_ad_enabled)){
	  ?>
	  
	  <legend>Optional</legend>

	  <div class="form-group">
	    <label>First Name</label>
	    <input type="text" class="form-control" name="first_name" value="<?php echo $first_name; ?>" pattern="[a-zA-Z0-9]{1,20}">
	  </div>

	  <div class="form-group">
	    <label>Last Name</label>
	    <input type="text" class="form-control" name="last_name" value="<?php echo $last_name; ?>" pattern="[a-zA-Z0-9]{1,20}">
	  </div>

	  <div class="form-group">
	    <label>Description</label>
	    <input type="text" class="form-control" name="description" value="<?php echo $description; ?>">
	  </div>

	  <div class="form-group">
	    <label>Email</label>
	    <input type="email" class="form-control" name="email" value="<?php echo $email; ?>">
	  </div>

	  <div class="form-group">
	    <label>Phone</label>
	    <input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>">
	  </div>

	  <?php
		}
		?>

	  <button type="submit" name="user_edit" class="btn btn-primary">Submit</button>
   
   </form>

</main>

<?php include("footer.php"); ?>