<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <h2>SMTP Mail Settings</h2>
  
  <?php
    //Alert Feedback
    if(!empty($_SESSION['alert_message'])){
      ?>
        <div class="alert alert-success alert-<?php echo $_SESSION['alert_type']; ?>" id="alert">
          <?php echo $_SESSION['alert_message']; ?>
          <button class='close' data-dismiss='alert'>&times;</button>
        </div>
      <?php
      
      $_SESSION['alert_type'] = '';
      $_SESSION['alert_message'] = '';

    }

  ?>

  <form method="post" action="post.php">
	  <div class="form-group">
	  		<label>SMTP Server</label>
	  		<input type="text" class="form-control" name="smtp_server">
	  </div>
    <div class="form-group">
        <label>SMTP Port</label>
        <select class="form-control" name="smtp_port">
          <option>587</option>
          <option>465</option>
          <option>25</option>
        </select>
    </div>
    <div class="form-group">
        <label>SMTP Username</label>
        <input type="text" class="form-control" name="smtp_username">
    </div>
    <div class="form-group">
        <label>SMTP Password</label>
        <input type="text" class="form-control" name="smtp_password">
    </div>
    <div class="form-group">
        <label>Default Mail From</label>
        <input type="text" class="form-control" name="mail_from">
    </div>
    <div class="form-group">
        <label>Send Notification Email to</label>
        <input type="text" class="form-control" name="mail_to">
    </div>
	  <button type="submit" name="mail_edit" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>