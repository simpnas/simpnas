<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
	<h2>Power</h2>
	<hr>
	<a href="post.php?shutdown" class="btn btn-lg btn-outline-danger">Shutdown</a>
	<a href="post.php?reboot" class="btn btn-lg btn-outline-secondary">Reboot</a>
	<a href="post.php?reset" class="btn btn-lg btn-danger">DESTROY!</a>
</main>

<?php include("footer.php"); ?>