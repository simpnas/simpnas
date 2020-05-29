<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    //include("side_nav.php");
    //exec("sleep 1; reboot > /dev/null &");
    $docker_gateway = exec("docker network inspect my-network | grep Gateway | awk '{print $2}' | sed 's/\\\"//g'");

?>

 <main class="col-md-12 pt-5">

<center>
	<h1 class="text-danger">Rebooting!</h1>
	<h3>Redirecting to the Dashboard after <span id="countdown">25</span> seconds</h3>
</center>
   <?php echo $docker_gateway; ?>
</main>

<?php include("footer.php"); ?>