<?php 
  
require_once "config.php";
require_once "includes/simple_vars.php";
require_once "includes/header.php";

exec("sleep 1 && halt -p > /dev/null &");

?>

<main class="col-md-12 pt-5">

	<center>
		<h1 class="text-danger">Shutting Down!</h1>
	</center>


<?php require_once "includes/footer.php";
