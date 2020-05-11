<?php 
    $config = include("config.php");
 	include("simple_vars.php");
    include("header.php");
    include("side_nav.php");
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
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
  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Username</label>
	    <input type="text" class="form-control" name="username" required pattern="[a-zA-Z0-9]{1,20}">
	  </div>
	  <div class="form-group">
	    <label>Password:</label>
	    <input type="password" class="form-control" name="password" required>
	  </div>
	  <legend>Groups</legend>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" id="checkAll">
	    <label class="form-check-label ml-1">Select All</label>
		</div>

	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" checked>
	    <label class="form-check-label ml-1">users</label>
		</div>
	  
	  <?php foreach ($group_array as $group) { ?>
	  <div class="form-group form-check">
	    <input type="checkbox" class="form-check-input" name="group[]" value="<?php echo "$group"; ?>">
	    <label class="form-check-label ml-1"><?php echo "$group"; ?></label>
		</div>

	  <?php } ?>

	  <button type="submit" name="user_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>