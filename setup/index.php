<?php
	
require_once "setup_header.php";

?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4 text-center">

  <h2>Welcome to Simp<strong>NAS</strong></h2>
  <hr>
  <p>Thank you for choosing SimpNAS to be your new Storage Vault. Before continuing please read the following</p>
  <p>
    Any Storage Device Attached will be wiped, please backup your data before continuing.
    <br>
    Any user that was created initially will be deleted, they can be recreated in the webUI.
  </p>
  <br>
  <a href="setup_timezone.php" class="btn btn-primary"><strong>Continue Setup<i class="fas fa-arrow-right ml-2"></i></strong></a>
</main>

<?php require_once "setup_footer.php";