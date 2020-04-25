<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <h2>General Settings</h2>
  
  <?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>

  <form method="post" action="post.php">
	  <div class="form-group">
	  		<label>Hostname</label>
	  		<input type="text" class="form-control" name="hostname" value="<?php echo gethostname(); ?>" required pattern="[a-zA-Z0-9-]{1,15}">
	  </div>
	  <button type="submit" name="general_edit" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>