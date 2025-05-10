<?php 
  
require_once "includes/include_all.php";

?>
	
<h2>Power</h2>
<hr>

<?php include("alert_message.php"); ?>

<a href="shutdown.php" class="btn btn-lg btn-outline-danger">Shutdown</a>
<a href="restart.php" class="btn btn-lg btn-outline-secondary">Restart</a>
<a href="reset.php" class="btn btn-lg btn-danger">Reset to Factory Defaults</a>

<?php require_once "includes/footer.php";
