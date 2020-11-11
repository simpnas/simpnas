<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
	
  <h2>Power</h2>
	<hr>
	
  <?php include("alert_message.php"); ?>

	<a href="shutdown.php" class="btn btn-lg btn-outline-danger">Shutdown</a>
	<a href="restart.php" class="btn btn-lg btn-outline-secondary">Restart</a>
	<a href="reset.php" class="btn btn-lg btn-danger">Reset to Factory Defaults</a>

</main>

<?php include("footer.php"); ?>