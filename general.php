<?php 
    
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <h2>General Settings</h2>
  
  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">
    <div class="form-group">
	  		<label>Hostname</label>
	  		<input type="text" class="form-control" name="hostname" value="<?php echo $config_hostname; ?>" required>
	  </div>
    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" name="enable_beta" value="1" <?php if($config['enable_beta'] == 1){ echo "checked"; } ?> >
      <label class="form-check-label ml-1 text-danger">Enable Beta Features</label>
    </div>
	  <button type="submit" name="general_edit" class="btn btn-primary">Submit</button>
	</form>
</main>

<?php include("footer.php"); ?>