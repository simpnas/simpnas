<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>
<?php exec("reboot > /dev/null &"); ?>


 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">SimpNAS <small>v0.7 (2020-04-19)</small></h1>
    <p>A NAS for Grandpa!</p>
</div>

<center>
	<h1 class="text-danger">Rebooting!</h1>
	<h3>Redirecting to home after <span id="countdown">60</span> seconds</h3>
</center>

</main>

<!-- JavaScript part -->
<script type="text/javascript">
    
    // Total seconds to wait
    var seconds = 60;
    
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