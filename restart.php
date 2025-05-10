<?php 
  
require_once "config.php";
require_once "includes/simple_vars.php";
require_once "includes/header_nologin.php";

?>

<main class="col-md-12 pt-5">

  <center>
  	<h1 class="text-danger">Restarting...</h1>
  	<h3>Redirecting to the Dashboard after <span id="countdown"></span> seconds</h3>
  </center>

</main>

<!-- JavaScript part -->
<script type="text/javascript">
    
  // Total seconds to wait
  var seconds = 90;
  
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

<?php 

require_once "includes/footer.php"; 

//Using && is safer than ; because it ensures that command ... will run only if the sleep timer expires.
exec("sleep 2 && reboot > /dev/null &");
