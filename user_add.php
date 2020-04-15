<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nobody | grep -v nogroup", $group_array);
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="users.php">Home</a></li>
    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add User</li>
  </ol>
</nav>

  <h2>Add User</h2>
  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Username:</label>
	    <input type="text" class="form-control" name="username">
	  </div>
	  <div class="form-group">
	    <label for="pwd">Password:</label>
	    <input type="password" class="form-control" name="password">
	  </div>
	  <legend>Groups</legend>
	  
	  <div class="form-group">
	  	<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input" checked>
			<label class="custom-control-label" for="customCheck">users</label>
		</div>
	  </div>
	  
	  <?php foreach ($group_array as $group) { ?>
	  <div class="form-group">
	  <div class="custom-control custom-checkbox">
		  <input type="checkbox" class="custom-control-input" name="group[]" value="<?php echo "$group"; ?>" id="customCheck<?php echo $group; ?>">
		  <label class="custom-control-label" for="customCheck<?php echo $group; ?>"><?php echo "$group"; ?></label>
		</div>
	</div>
	  <?php } ?>

	  <button type="submit" name="user_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>