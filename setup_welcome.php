<?php
	
	include("setup_header.php");

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item active"><a href="setup.php">Setup</a></li>
	  </ol>
	</nav>
  
  <h2>Welcome to SimpNAS</h2>
  <hr>
  Thank you for choosing SimpNAS to be your new Storage Vault. Before continuing please read the following
  <ul>
  	<li>Any Storage Device Attached may possibly be wiped, please backup your data before continuing.</li>
  	<li>Any user that was created initially will be deleted, they can be recreated in the webUI.</li>
  </ul>
  <a href="setup.php" class="btn btn-primary">Continue</a>
</main>

<?php include("footer.php"); ?>