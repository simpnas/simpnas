<?php

require_once "includes/include_all.php";

$image_size = 48;

$status_service_docker = exec("systemctl status docker | grep running");
if (empty($status_service_docker)) {
    $status_service_docker = "<i class='fa fa-circle text-danger'></i>";
} else {
    $status_service_docker = "<i class='fa fa-circle text-success'></i>";
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Apps</h2>
</div>

<div class="table-responsive">
    <table class="table">
        <tbody>

            <?php 
            foreach ($apps_array as $app) {
            ?>
            
            <tr>
                <td class="text-center text-muted">
                    <img src="img/apps/<?php echo $app['image']; ?>" height="<?php echo $image_size; ?>" width="<?php echo $image_size; ?>" class="img-fluid rounded">
                    <br>
                    <?php echo $app['title']; ?>
                    <br>
                    <?php if (file_exists("/volumes/{$config_docker_volume}/docker/{$app['container_name']}")) { ?>
                        <small class="text-success"><span data-feather="check"></span>Installed</small>
                    <?php } ?>
                </td>
                <td>
                    <?php 
                    echo $app['description'];

                    if (file_exists("/volumes/{$config_docker_volume}/docker/{$app['container_name']}")) {
                    ?>
                        <br><br>
                        <small class="text-secondary">
                            <?php echo exec("docker inspect -f '{{ index .Config.Labels \"build_version\" }}' {$app['container_name']}"); ?>
                        </small>
                    <?php
                    }
                    ?>    
                </td>
                <td>
                    <div class="btn-group mr-2">
                        <?php 
                        if (file_exists("/volumes/{$config_docker_volume}/docker/{$app['container_name']}")) {
                        ?>
                            <a href="<?php echo $app['protocol'] . $config_primary_ip . ':' . $app['local_port']; ?>" target="_blank" class="btn btn-outline-primary">
                                <span data-feather="external-link"></span>
                            </a>

                            <?php if (!empty($app['update'])) { ?>
                                <a href="<?php echo $app['update']; ?>" class="btn btn-outline-secondary" onclick="$('#cover-spin').show(0)">
                                    <span data-feather="download"></span>
                                </a>
                            <?php } ?>

                            <?php if (!empty($app['config'])) { ?>
                                <a href="<?php echo $app['config']; ?>" class="btn btn-outline-secondary" onclick="$('#cover-spin').show(0)">
                                    <span data-feather="settings"></span>
                                </a>
                            <?php } ?>

                            <a href="post.php?uninstall_<?php echo $app['container_name']; ?>" class="btn btn-outline-danger" onclick="$('#cover-spin').show(0)">
                                <span data-feather="trash"></span>
                            </a>
                            <a href="docker_logs.php?docker_app=<?php echo $app['container_name']; ?>" class="btn btn-outline-secondary">
                                <span data-feather="clock"></span>
                            </a>

                            <?php if (file_exists("/volumes/{$config_docker_volume}/docker/letsencrypt/nginx/proxy-confs/{$app['container_name']}.subdomain.conf")) { ?>
                                <a href="https://<?php echo $app['external_hostname'] . '.' . $domain; ?>" target="_blank" class="btn btn-outline-dark">
                                    <span data-feather="cloud"></span>
                                </a>
                            <?php } ?>

                        <?php } else { ?>
                            <a href="<?php echo $app['install']; ?>" class="btn btn-outline-success" data-toggle="modal">
                                Install
                            </a>
                        <?php } ?>
                    </div>
                </td>
            </tr>

            <?php
            }
            ?>                
        
        </tbody>
    </table>
</div>

<?php 

require_once "modals/install_nextcloud.php";
require_once "modals/install_jellyfin.php";
require_once "modals/install_home_assistant.php";
require_once "modals/install_nginx_proxy_manager.php";
require_once "modals/install_transmission.php";
require_once "modals/install_photoprism.php";
require_once "includes/footer.php";
