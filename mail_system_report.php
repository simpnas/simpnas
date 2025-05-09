<?php 
  
  include("config.php");
  include("simple_vars.php");

  $date = date("Y-m-d H:i");

  $load_30min = exec("cat /proc/loadavg | awk '{print $3}'");
  $mem_usage_percent = floor(exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"));
  $swap_usage_percent = floor(exec("free | grep Swap | awk '{print $3/$2 * 100.0}'"));
  $uptime = exec("uptime");

  //bad hdd check
  exec("smartctl --scan | awk '{print $1}'", $hdd_array);

  $mail_body = "========================================================<br><b>$config_hostname Report</b><br>========================================================<br>";
  
  $mail_body .= "========================================================<br>Disk Health Report<br>========================================================<br>";

  foreach($hdd_array as $hdd){
  	$hdd_short_name = basename($hdd);

  	$hdd_status = exec("smartctl -H $hdd | grep 'overall-health' | awk '{print $6}'");

    $hdd_smart = exec("smartctl -i $hdd | grep 'SMART support is' | cut -d' ' -f 8-");

    $hdd_make = exec("smartctl -i $hdd | grep 'Device Model:' | awk '{print $3}'");
    if($hdd_make == 'WDC'){
      $hdd_make = 'Western Digital';
    }else{
      $hdd_make = '';
    }

    $hdd_vendor = exec("smartctl -i $hdd | grep 'Model Family:' | awk '{print $3,$4,$5}'");
    if(empty($hdd_vendor)){
      $hdd_vendor = exec("smartctl -i $hdd | grep 'Device Model:' | awk '{print $3,$4,$5}'");
    }
    if(empty($hdd_vendor)){
      $hdd_vendor = exec("smartctl -i $hdd | grep 'Vendor:' | awk '{print $2,$3,$4}'");
    }
    if(empty($hdd_vendor)){
      $hdd_vendor = "-";
    }

    $hdd_serial = exec("smartctl -i $hdd | grep 'Serial Number:' | awk '{print $3}'");
    if(empty($hdd_serial)){
      $hdd_serial = "-";
    }
    
    $hdd_label_size = exec("smartctl -i $hdd | grep 'User Capacity:' | cut -d '[' -f2 | cut -d ']' -f1");
  	
  	$mail_body .= "$hdd_short_name - $hdd_vendor - $hdd_serial ($hdd_label_size) - $hdd_status<br>";
  }

  //volume usage check
  exec("ls /volumes", $volume_array);

  $mail_body .= "<br>========================================================<br>Volume Usage Report<br>========================================================<br>";

  foreach($volume_array as $volume){
    $mounted = exec("df | grep $volume");
    if(!empty($mounted)){
      $total_space = exec("df | grep -w /volumes/$volume | awk '{print $2}'");
      $total_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $2}'");
      $used_space = exec("df | grep -w /volumes/$volume | awk '{print $3}'");
      $used_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $3}'");
      $free_space = exec("df | grep -w /volumes/$volume | awk '{print $4}'");
      $free_space_formatted = exec("df -h | grep -w /volumes/$volume | awk '{print $4}'");
      $used_space_percent = exec("df | grep -w /volumes/$volume | awk '{print $5}'");
    	
    	if($used_space_percent > 80){
    		$mail_body .= "Volume $volume is running out of space current usage is $used_space_percent.<br>";
      }
    }
  }

  $mail_body .= "<br>========================================================<br>System Resource Report<br>========================================================<br>";

  if($load_30min > 5.00){
  	$mail_body .= "High Load: $load_30min<br>";
  }

  if($mem_usage_percent > 90){
  	$mail_body .= "High Memory Usage: $mem_usage_percent<br>";
  }

  if($swap_usage_percent > 80){
  	$mail_body .= "High Swap Usage: $swap_usage_percent<br>";
  }

  //Service Checks
	$status_service_smbd = exec("systemctl status smbd | grep running");
  $status_service_nmbd = exec("systemctl status nmbd | grep running");
  $status_service_docker = exec("systemctl status docker | grep running");
  $status_service_ssh = exec("systemctl status ssh | grep running");

  $mail_body .= "<br>========================================================<br>Service Report<br>========================================================<br>";


  if(empty($status_service_smbd)){
  	$mail_body .= "Service Stopped: Samba (smbd)<br>";
  }

  if(empty($status_service_nmbd)){
  	$mail_body .= "Service Stopped: Samba (nmbd)<br>";
  }

  if(empty($status_service_docker)){
  	$mail_body .= "Service Stopped: docker<br>";
  }

  if(empty($status_service_ssh)){
  	$mail_body .= "Service Stopped: SSH<br>";
  }

  $mail_body .= "<br><hr>Uptime: $uptime<br>";

  // Import PHPMailer classes into the global namespace
  // These must be at the top of your script, not inside a function
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;

  require 'plugins/PHPMailer/PHPMailer.php';
  require 'plugins/PHPMailer/SMTP.php';

  // Instantiation and passing `true` enables exceptions
  $mail = new PHPMailer(true);

  try {
      //Server settings
      $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
      $mail->isSMTP();                                            // Send using SMTP
      $mail->Host       = $config[smtp_server];                    // Set the SMTP server to send through
      $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
      $mail->Username   = $config[smtp_username];                     // SMTP username
      $mail->Password   = $config[smtp_password];                               // SMTP password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
      $mail->Port       = $config[smtp_port];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

      //Recipients
      $mail->setFrom($config[smtp_username], "$config_hostname Bot");
      $mail->addAddress($config[mail_to]);     // Add a recipient

      // Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $config_hostname . ' ' . $date . ' - System Report';
      $mail->Body    = $mail_body;
      $mail->AltBody = $mail_body;

      $mail->send();
      echo 'Message has been sent';
  } catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }

?>