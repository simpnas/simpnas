<?php 
    
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
    <li class="breadcrumb-item active">Add Group</li>
  </ol>
</nav>

  <h2>Add Group</h2>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
    <div class="form-group">
	    <label>Group</label>
	    <input type="text" class="form-control" name="group" required pattern="[a-z0-9-]{1,25}" autofocus>
	  </div>
	  <button type="submit" name="group_add" class="btn btn-primary">Submit</button>
	</form>

</main>

<?php include("footer.php"); ?>