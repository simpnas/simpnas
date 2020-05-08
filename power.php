<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
	<h2>Power</h2>
	<hr>
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
	<a href="shutdown.php" class="btn btn-lg btn-outline-danger">Shutdown</a>
	<a href="reboot.php" class="btn btn-lg btn-outline-secondary">Reboot</a>
	<a href="reset.php" class="btn btn-lg btn-danger">Reset to Factory Defaults</a>
</main>

<?php include("footer.php"); ?>