<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Group</li>
  </ol>
</nav>

  <h2>Add Group</h2>
  <form method="post" action="post.php">
    <div class="form-group">
	    <label>Group:</label>
	    <input type="text" class="form-control" name="group">
	  </div>
	  <button type="submit" name="group_add" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>