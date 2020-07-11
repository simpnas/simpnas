<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  
  <h2>Notifications</h2>
  
  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
	  
    <legend>SMTP Mail Server Settings</legend>

    <div class="form-group">
  		<label>SMTP Server</label>
  		<input type="text" class="form-control" name="smtp_server" value="<?php echo $config['smtp_server']; ?>">
	  </div>
    
    <div class="form-group">
        <label>SMTP Port</label>
        <input type="number" class="form-control" name="smtp_port" value="<?php echo $config['smtp_port']; ?>">
    </div>
    
    <div class="form-group">
      <label>SMTP Username</label>
      <input type="text" class="form-control" name="smtp_username" value="<?php echo $config['smtp_username']; ?>">
    </div>
   
    <div class="form-group">
      <label>SMTP Password</label>
      <input type="text" class="form-control" name="smtp_password" value="<?php echo $config['smtp_password']; ?>" autocomplete="new-password">
    </div>
    
    <legend>Notification Settings</legend>

    <div class="form-group">
      <label>Default Mail From</label>
      <input type="text" class="form-control" name="mail_from" value="<?php echo $config['mail_from']; ?>">
    </div>
    
    <div class="form-group">
      <label>Send Notification Email to</label>
      <input type="text" class="form-control" name="mail_to" value="<?php echo $config['mail_to']; ?>">
    </div>

    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" name="enable_system_report" value="1" <?php if(file_exists('/etc/cron.daily/system-report')){ echo "checked"; } ?>>
      <label class="form-check-label ml-1">Enable Daily System Report</label>
    </div>
	  
    <button type="submit" name="notifications-settings" class="btn btn-primary">Submit</button>
	
  </form>

</main>

<?php include("footer.php"); ?>