<?php 
  
$config = include("config.php");
include("simple_vars.php");

session_start();

if(isset($_POST['login'])){
  
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  $logged_in = exec("bash /simpnas/verify.sh $username $password");

  if($logged_in == 1){
    $_SESSION['username'] = $username;
    $_SESSION['logged'] = TRUE;
    header("Location: dashboard.php");
  }else{
    $response = "
      <div class='alert alert-danger'>
        Incorrect username or password.
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

    <title><?php echo gethostname(); ?> | Login</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <form class="form-signin" method="post">
      <h2><?php echo gethostname(); ?></h2>
      <?php 
      if(!empty($response)){
        echo $response;
      }
      ?>
      <input type="text" id="inputUsername" name="username" class="form-control" placeholder="Username" required autofocus>
      <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
      <button type="submit" class="btn btn-primary p-2 btn-block" name="login">Sign In</button>
    </form>
  </body>
</html>