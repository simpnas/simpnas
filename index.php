<?php 
    $config = include("config.php");
    include("header.php");
    include("side_nav.php");
    $config_home_dir = $config['home_dir'];

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">SimpNAS</h1>
    <p>A NAS for Grandpa!</p>
</div>
<?php

print_r($config);

?>

<hr>

<?php

echo $config['mount_target'];

?>

<br><br>

<?php

echo "/".$config['mount_target']."/".$config['home_volume']."/".$config['home_dir']."/";

?>


</main>

<?php include("footer.php"); ?>