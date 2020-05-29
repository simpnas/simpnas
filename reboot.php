<?php 
    $config = include("config.php");
    include("simple_vars.php");
    include("header.php");
    //Using && is safer than ; because it ensures that command ... will run only if the sleep timer expires.
    exec("sleep 1 && reboot > /dev/null &");
?>

 <main class="col-md-12 pt-5">

<center>
	<h1 class="text-danger">Rebooting!</h1>
	<h3>Redirecting to the Dashboard after <span id="countdown">25</span> seconds</h3>
</center>

</main>

<?php ?>

<!-- JavaScript part -->
<script type="text/javascript">
    
    // Total seconds to wait
    var seconds = 25;
    
    function countdown() {
        seconds = seconds - 1;
        if (seconds < 0) {
            // Chnage your redirection link here
            window.location = "dashboard.php";
        } else {
            // Update remaining seconds
            document.getElementById("countdown").innerHTML = seconds;
            // Count down using javascript
            window.setTimeout("countdown()", 1000);
        }
    }

    // Run countdown function
    countdown();

</script>

<?php include("footer.php"); ?>