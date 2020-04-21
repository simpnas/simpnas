<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("sleep 1; reboot > /dev/null &");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

<center>
	<h1 class="text-danger">Rebooting!</h1>
	<h3>Redirecting to home after <span id="countdown">25</span> seconds</h3>
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
            window.location = "index.php";
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