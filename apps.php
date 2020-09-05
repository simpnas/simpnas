<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  $image_size = 48;

  if(file_exists("/volumes/$config_docker_volume/docker/letsencrypt/")){ 
    $domain = exec("cat /volumes/$config_docker_volume/docker/letsencrypt/donoteditthisfile.conf | awk -F\\\" '{print $2}'");
  }

  $status_service_docker = exec("systemctl status docker | grep running");
  if(empty($status_service_docker)){
    $status_service_docker = "<i class='fa fa-circle text-danger'></i>";
  }else{
    $status_service_docker = "<i class='fa fa-circle text-success'></i>";
  }
  
  $apps_array = array(
    array(
      "title" => "Nextcloud",
      "category" => "Groupware",
      "description" => "Groupware, file sharing platform",
      "website" => "https://nextcloud.com",
      "image" => "nextcloud.png",
      "container_name" => "nextcloud",
      "external_hostname" => "cloud",
      "local_port" => 6443,
      "protocol" => "https://",
      "install" => "install_nextcloud.php",
      "update" => "post.php?update_nextcloud",
      "config" => "",
    ),
    array(
      "title" => "Jellyfin",
      "category" => "Media",
      "description" => "Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc",
      "website" => "https://jellyfin.com",
      "image" => "jellyfin.png",
      "container_name" => "jellyfin",
      "external_hostname" => "jellyfin",
      "local_port" => 8096,
      "protocol" => "http://",
      "install" => "install_jellyfin.php",
      "update" => "post.php?update_jellyfin",
      "config" => "",
    ),
    array(
      "title" => "DAAPD",
      "category" => "Media",
      "description" => "iTunes Server Music Streaming App",
      "website" => "https://ejurgensen.github.io/forked-daapd/",
      "image" => "daapd.png",
      "container_name" => "daapd",
      "external_hostname" => "",
      "local_port" => 3689,
      "protocol" => "http://",
      "install" => "install_daapd.php",
      "update" => "post.php?update_daapd",
      "config" => "",
    ),
    array(
      "title" => "Transmission",
      "category" => "Downloads",
      "description" => "Web based BitTorrent Download Client",
      "website" => "https://transmission.org",
      "image" => "transmission.png",
      "container_name" => "transmission",
      "external_hostname" => "transmission",
      "local_port" => 9091,
      "protocol" => "http://",
      "install" => "install_transmission.php",
      "update" => "",
      "config" => "configure_transmission.php",
    ),
    array(
      "title" => "Bitwarden RS",
      "category" => "Password Manager",
      "description" => "Password Manager -- Note: Bitwarden will not work properly unless remote access is enabled because Bitwarden requires HTTPS.",
      "website" => "https://bitwarden.org",
      "image" => "bitwarden.png",
      "container_name" => "bitwarden",
      "external_hostname" => "vault",
      "local_port" => 88,
      "protocol" => "http://",
      "install" => "post.php?install_bitwarden",
      "update" => "post.php?update_bitwarden",
      "config" => "",
    ),
    array(
      "title" => "Home Assistant",
      "category" => "Smart Home",
      "description" => "Home Automation (Control Lights, switches, smart devices etc)",
      "website" => "https://homeassistant.com",
      "image" => "homeassistant.png",
      "container_name" => "homeassistant",
      "external_hostname" => "homeassistant",
      "local_port" => 8123,
      "protocol" => "http://",
      "install" => "post.php?install_homeassistant",
      "update" => "post.php?update_homeassistant",
      "config" => "",
    ),
    array(
      "title" => "Unifi Controller",
      "category" => "Unifi Apps",
      "description" => "Manage Ubiquiti network devices.",
      "website" => "https://dokuwiki.com",
      "image" => "unifi.png",
      "container_name" => "unifi-controller",
      "external_hostname" => "unifi",
      "local_port" => 8443,
      "protocol" => "https://",
      "install" => "post.php?install_unifi-controller",
      "update" => "post.php?update_unifi-controller",
      "config" => "",
    ),
    //array(
      //"title" => "Wireguard VPN Server",
      //"description" => "Allow secure external access inside your network",
      //"website" => "https://wireguard.com",
      //"image" => "wireguard2.png",
      //"container_name" => "wireguard",
      //"external_hostname" => "vpn",
      //"local_port" => 0,
      //"protocol" => "http://",
      //"install" => "post.php?install_wireguard",
    //),
    //array(
      //"title" => "OpenVPN VPN Server",
      //"description" => "Allow secure external access inside your network",
      //"website" => "https://openvpn.org",
      //"image" => "openvpn.png",
      //"container_name" => "openvpn",
      //"external_hostname" => "vpn",
      //"local_port" => 943,
      //"protocol" => "http://",
      //"install" => "post.php?install_openvpn",
    //),
  );

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Apps</h2>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <?php 
        foreach ($apps_array as $app){
        ?>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/<?php echo $app[image]; ?>" height="<?php echo $image_size; ?>" width="<?php echo $image_size; ?>" class="img-fluid rounded">
            <br>
            <?php echo $app['title']; ?>
            <br>
            <?php if(file_exists("/volumes/$config_docker_volume/docker/$app[container_name]")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            <?php 
              echo $app[description];
             
              if(file_exists("/volumes/$config_docker_volume/docker/$app[container_name]")) {
            ?>
            <br><br><small class="text-secondary"><?php echo exec("docker inspect -f '{{ index .Config.Labels \"build_version\" }}' $app[container_name]"); ?></small>

            <?php
            }
            ?>    

          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
              if(file_exists("/volumes/$config_docker_volume/docker/$app[container_name]")) {
              ?>
                <a href="<?php echo $app[protocol]; ?><?php echo $config_primary_ip; ?>:<?php echo $app[local_port]; ?>" target="_blank" class="btn btn-outline-primary"><span data-feather="external-link"></span></a>
                <?php 
                if(!empty($app[update])){ ?>
                  <a href="<?php echo $app[update]; ?>" class="btn btn-outline-secondary" onclick="$('#cover-spin').show(0)"><span data-feather="download"></span></a>
                <?php
                }
                ?>
                <?php 
                if(!empty($app[config])){ ?>
                  <a href="<?php echo $app[config]; ?>" class="btn btn-outline-secondary" onclick="$('#cover-spin').show(0)"><span data-feather="settings"></span></a>
                <?php
                }
                ?>

                <a href="post.php?uninstall_<?php echo $app[container_name]; ?>" class="btn btn-outline-danger" onclick="$('#cover-spin').show(0)"><span data-feather="trash"></span></a>
                <a href="docker_logs.php?docker_app=<?php echo $app[container_name]; ?>" class="btn btn-outline-secondary"><span data-feather="clock"></span></a>

                <?php 
                if(file_exists("/volumes/$config_docker_volume/docker/letsencrypt/nginx/proxy-confs/$app[container_name].subdomain.conf")){ ?>
                  <a href="https://<?php echo "$app[external_hostname].$domain"; ?>" target="_blank" class="btn btn-outline-dark"><span data-feather="cloud"></span></a>
                <?php
                }
                ?>
              <?php
              }else{
              ?>
              <a href="<?php echo $app[install]; ?>" class="btn btn-outline-success" onclick="$('#cover-spin').show(0)">Install</a>
              <?php  
              }
              ?>

            </div>
          </td>
        </tr>


        <?php
        }
        ?>                
      
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>