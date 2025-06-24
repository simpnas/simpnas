<?php 
  
require_once "config.php";
// Check to see if setup is enabled
if (!isset($config_enable_setup) || $config_enable_setup == 1) {
    header("Location: setup/");
    exit;
}
require_once "includes/simple_vars.php";

session_start();

if(isset($_POST['login'])){
  
  $password = $_POST['password'];

  if (password_verify($password, $config_admin_password)) {
    $_SESSION['logged'] = TRUE;
    header("Location: dashboard.php");
    exit();
  } else {
    $response = "
      <div class='alert alert-danger'>
        Incorrect Password!
        <button class='close' data-dismiss='alert'>&times;</button>
      </div>
    ";
  }
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo gethostname(); ?> login</title>

    <!-- Bootstrap core CSS -->
    <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="plugins/fontawesome-free/css/all.min.css">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <form class="form-signin" method="post">
      <h1 class="mb-3"><i class="fa fa-cube mr-2"></i>Simp</span><strong>NAS</strong><br><small class="text-secondary">[<?php echo gethostname(); ?>]</small></h1>
      <div id ="alert">
        <?php 
        if(!empty($response)){
          echo $response;
        }
        ?>
      </div>
      <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" autofocus required>
      <button type="submit" class="btn btn-primary p-2 btn-block" traget="_blank" name="login"><strong>Login</strong></button>
      <a href="https://<?php echo $config_primary_ip; ?>:6443" class="btn btn-secondary p-2 btn-block"><i class="fa fa-folder"></i> File Manager</a>
    </form>
  
<?php require_once "includes/footer.php";