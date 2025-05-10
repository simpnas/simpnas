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
