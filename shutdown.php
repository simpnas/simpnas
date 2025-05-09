<?php 
  
  include("config.php");
	include("simple_vars.php");
  include("header.php");
  
  exec("sleep 1 && halt -p > /dev/null &");

?>

<main class="col-md-12 pt-5">

	<center>
		<h1 class="text-danger">Shutting Down!</h1>
	</center>

</main>

<?php include("footer.php"); ?>