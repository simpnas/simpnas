<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  $current_hostname = gethostname();
  $primary_ip = exec("ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'");

  $image_size = 48;
  $apps_array = array(
    array(
      "title" => "Nextcloud",
      "description" => "Access and share your files anywhere over the Internet",
      "website" => "https://nextcloud.com",
      "image" => "nextcloud.png",
      "container_name" => "nextcloud",
      "external_hostname" => "cloud",
      "local_port" => 6443,
      "protocol" => "https://",
      "install" => "install_nextcloud.php",
    ),
    array(
      "title" => "Jellyfin",
      "description" => "Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc",
      "website" => "https://jellyfin.com",
      "image" => "jellyfin.png",
      "container_name" => "jellyfin",
      "external_hostname" => "jellyfin",
      "local_port" => 8096,
      "protocol" => "http://",
      "install" => "install_jellyfin.php",
    ),
    array(
      "title" => "Transmission",
      "description" => "BitTorrent Download Client",
      "website" => "https://transmission.org",
      "image" => "transmission.png",
      "container_name" => "transmission",
      "external_hostname" => "transmission",
      "local_port" => 9091,
      "protocol" => "http://",
      "install" => "install_transmission.php",
    ),
    array(
      "title" => "Bitwarden RS",
      "description" => "Password Manager",
      "website" => "https://bitwarden.org",
      "image" => "bitwarden.png",
      "container_name" => "bitwarden",
      "external_hostname" => "vault",
      "local_port" => 88,
      "protocol" => "http://",
      "install" => "post.php?install_bitwarden",
    ),
    array(
      "title" => "Home Assistant",
      "description" => "Home Automation (Control Lights, switches, smart devices)",
      "website" => "https://homeassistant.com",
      "image" => "homeassistant.png",
      "container_name" => "homeassistant",
      "external_hostname" => "homeassistant",
      "local_port" => 8123,
      "protocol" => "http://",
      "install" => "post.php?install_homeassistant",
    ),
    array(
      "title" => "Dokuwiki",
      "description" => "Make some notes.",
      "website" => "https://dokuwiki.com",
      "image" => "dokuwiki.png",
      "container_name" => "dokuwiki",
      "external_hostname" => "wiki",
      "local_port" => 85,
      "protocol" => "http://",
      "install" => "post.php?install_dokuwiki",
    ),
    array(
      "title" => "Unifi Controller",
      "description" => "Manage Unifi network devices.",
      "website" => "https://dokuwiki.com",
      "image" => "unifi.png",
      "container_name" => "unifi-controller",
      "external_hostname" => "unifi",
      "local_port" => 8443,
      "protocol" => "https://",
      "install" => "post.php?install_unifi-controller",
    ),
    array(
      "title" => "Unifi Video",
      "description" => "NVR",
      "website" => "https://ui.com",
      "image" => "unifi-video2.png",
      "container_name" => "unifi-video",
      "external_hostname" => "unifi-video",
      "local_port" => 7443,
      "protocol" => "https://",
      "install" => "install_unifi-video.php",
    ),
    array(
      "title" => "Wireguard VPN Server",
      "description" => "Allow secure external access inside your network",
      "website" => "https://wireguard.com",
      "image" => "wireguard2.png",
      "container_name" => "wireguard",
      "external_hostname" => "vpn",
      "local_port" => 0,
      "protocol" => "http://",
      "install" => "post.php?install_wireguard",
    ),
    array(
      "title" => "OpenVPN VPN Server",
      "description" => "Allow secure external access inside your network",
      "website" => "https://openvpn.org",
      "image" => "openvpn.png",
      "container_name" => "openvpn",
      "external_hostname" => "vpn",
      "local_port" => 943,
      "protocol" => "http://",
      "install" => "post.php?install_openvpn",
    ),
  );

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Apps</h2>
  </div>

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

  ?>

  <div class="table-responsive">
    <table class="table">
      <tbody>

        <?php 
        foreach ($apps_array as $app){
        ?>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/<?php echo $app['image']; ?>" height="<?php echo $image_size; ?>" width="<?php echo $image_size; ?>" class="img-fluid rounded">
            <br>
            <?php echo $app['title']; ?>
            <br>
            <?php if(file_exists("/volumes/$config_docker_volume/docker/".$app['container_name']."")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            <?php echo $app['description']; ?>
            <?php if($app['title'] == 'Transmission'){ ?> 
               <p class="text-secondary">VPN IP: <strong><?php $vpn_ip = exec("docker exec -i transmission curl ifconfig.co"); echo $vpn_ip; ?></strong></p>
            <?php } ?>
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/volumes/$config_docker_volume/docker/".$app['container_name']."")) {
              ?>
                <a href="<?php echo $app['protocol']; ?><?php echo $primary_ip; ?>:<?php echo $app['local_port']; ?>" target="_blank" class="btn btn-outline-primary"><span data-feather="external-link"></span></a>
                <a href="post.php?uninstall_<?php echo $app['container_name']; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="<?php echo $app['install']; ?>" class="btn btn-outline-success" onclick="$('#cover-spin').show(0)">Install</a>
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
