<?php 
  $config = include("config.php");
	include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  if(isset($_GET['username'])){
		$username = $_GET['username'];
	}
  $primary_group = exec("id -g -n $username");
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
  $group_member_array = explode(' ',exec("groups $username"));
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
  <form method="post" action="post.php" autocomplete="off">
	  
	  <div class="form-group">
	    <label>Username</label>
	    <input type="text" class="form-control" name="username" value="<?php echo $username; ?>" readonly>
	  </div>
	  
	  <div class="form-group">
	    <label>Password</label>
	    <input type="password" class="form-control" name="password" placeholder="Leave blank for no change" autocomplete="new-password">
	  </div>
	  
	  <legend>Groups</legend>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" checked>
	    <label class="form-check-label ml-1">users</label>
	  </div>
	  
	  <?php 
	  foreach($group_array as $group){ 
	  ?>
	  		
		  <div class="form-group form-check">
		    <input type="checkbox" class="form-check-input" name="group[]" <?php foreach($group_member_array as $group_member){ if($group == $group_member){ echo "checked"; } ?> value="<?php echo $group; ?>" <?php } ?>>
		    <label class="form-check-label ml-1"><?php echo "$group"; ?></label>
		  </div>
		  
		<?php } ?>

	  <button type="submit" name="user_edit" class="btn btn-primary">Submit</button>
   </form>
</main>

<?php include("footer.php"); ?>