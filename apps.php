<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
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
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/nextcloud.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Nextcloud
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/nextcloud")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            Access and share your files anywhere over the Internet
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/nextcloud")) {
              ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:443" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_nextcloud.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_nextcloud" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_nextcloud.php" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/jellyfin.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Jellyfin
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/jellyfin")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/jellyfin")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:8096" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_jellyfin.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_jellyfin" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_jellyfin.php" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/dokuwiki.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Dokuwiki
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/dokuwiki")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Make some Notes</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/dokuwiki")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:85" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_dokuwiki.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_dokuwiki" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_dokuwiki" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/unifi.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Unifi Controller
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi-controller")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Allow you to configure and manage Unifi network devices</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi-controller")) {
              ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:8443" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_unifi.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_unifi" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_unifi" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/unifi-video2.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Unifi Video
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi-video")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            Unifi NVR
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi-video")) {
              ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:7443" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_unifi-video.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_unifi-video" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_unifi-video.php" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/homeassistant.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Home Assistant
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/homeassistant")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            Home Automation (Control Lights, switches, smart devices)
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/homeassistant")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:8123" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_home-assistant.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_home-assistant" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_home-assistant" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/wireguard.jpg" height="48" width="48" class="img-fluid rounded">
            <br>
            WireGuard VPN Server
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/wireguard")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Turn your NAS into WireGuard VPN Server, connect to your home network and access everything at home as if you were on your network locally</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/wireguard")) {
              ?>
                <a href="wireguard_config.php" class="btn btn-outline-primary"><span data-feather="lock"></span></a>
                <a href="update_wireguard.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_wireguard" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_wireguard" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        
        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/openvpn.png" height="48" width="48" class="img-fluid rounded">
            <br>
            OpenVPN Server
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/openvpn")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Turn your NAS into VPN Server, connect to your home network and access everything at home as if you were on your network locally</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/openvpn")) {
              ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:943" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_openvpn.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_openvpn" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_openvpn" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>

        <tr>
          <td class="text-center text-muted">
            <img src="img/apps/bitwarden.png" height="48" width="48" class="img-fluid rounded">
            <br>
            Bitwarden RS
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/bitwarden")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Password Manager</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/bitwarden")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:88" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_bitwarden.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_bitwarden" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_bitwarden" class="btn btn-outline-success">Install</a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
      
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
