<?php 
    
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  
  if(isset($_GET['group'])){
    $group = $_GET['group'];
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="users.php">Home</a></li>
    <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
    <li class="breadcrumb-item active">Edit Group</li>
  </ol>
</nav>

  <h2>Edit Group</h2>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
	  <input type="hidden" name="old_group" value="<?php echo $group; ?>">
    <div class="form-group">
	    <label>Group</label>
	    <input type="text" class="form-control" name="group" value="<?php echo $group; ?>" required pattern="[a-z0-9-]{1,25}">
	  </div>
	  <button type="submit" name="group_edit" class="btn btn-primary">Submit</button>
	</form>

</main>

<?php include("footer.php"); ?>