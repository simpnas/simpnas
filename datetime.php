<?php 
    include("header.php");
    include("side_nav.php");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <h2>Date and Time</h2>
  <form method="post" action="post.php">
	  <div class="form-group">
	    <label>Timezone:</label>
	    <select class="form-control" name="timezone">
	    <option>Eastern</option>
	    <option>UTC</option>
	    </select>	
	  </div>
	  <div class="form-group">
	    <label>Date:</label>
	    <input type="date" class="form-control" name="date">
	  </div>
	  
	  <div class="form-group">
	    <label>Time:</label>
	    <input type="time" class="form-control" name="time">
	  </div>
	  <button type="submit" name="datetime_update" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>