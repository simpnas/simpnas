<?php 
  
  include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  $image_size = 48;

  if(file_exists("/volumes/$config_docker_volume/docker/swag/")){ 
    $domain = exec("cat /volumes/$config_docker_volume/docker/swag/donoteditthisfile.conf | awk -F\\\" '{print $2}'");
  }

  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $status_service_docker = "<i class='fa fa-circle text-danger'></i>";
  }else{
    $status_service_docker = "<i class='fa fa-circle text-success'></i>";
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Available Apps</h2>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <?php 
        foreach ($apps_array as $app){
        ?>
        <?php if(!file_exists("/volumes/$config_docker_volume/docker/$app[container_name]")) { ?>
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/<?php echo $app[image]; ?>" height="<?php echo $image_size; ?>" width="<?php echo $image_size; ?>" class="img-fluid rounded">
            <br>
            <?php echo $app['title']; ?>
          </td>
          <td><?php echo $app[description]; ?></td>
          <td>
            <a href="<?php echo $app[install]; ?>" class="btn btn-outline-success" onclick="$('#cover-spin').show(0)">Install</a>
          </td>
        </tr>


        <?php
        }
        }
        ?>                
      
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>