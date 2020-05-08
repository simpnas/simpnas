<?php 
    include("config.php");
    include("header.php");
    //include("side_nav.php");
?>

 <main class="col-md-12 pt-5">

<center>
	<h1 class="text-danger">Shutting Down!</h1>
</center>

</main>

<?php include("footer.php"); ?>

<?php
    exec("shutdown -h");
?>