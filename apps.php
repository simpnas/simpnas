<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Apps</h2>
  </div>

  <div class="row">
    
    <div class="col-md-3">
      <div class="card text-center">
        <center>
          <img src="img/apps/jellyfin.png" class="card-img-top" alt="...">
        </center>
        <div class="card-body">
          <h5 class="card-title">Jellyfin</h5>
          <p class="card-text">Organize and stream your music, movies and TV Shows.</p>
          <div class="text-success"><span data-feather="check"></span>Installed (Running)</div>
        </div>
        <div class="card-body bg-light border-top">
          <?php 
            if(file_exists("/$config_mount_target/$config_docker_volume/docker/jellyfin")) {
          ?>
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:8096" class="card-link" target="_blank">Access</a>
            <a href="update_jellyfin.php" class="card-link">Update</a>
            <a href="post.php?uninstall_jellyfin" class="card-link text-danger">Uninstall</a>
          <?php
          }else{
          ?>
          <a href="post.php?install_nextcloud" class="card-link">Install</a>
          <a href="https://jellyfin.org" class="card-link">Website</a>
          <?php  
          }
          ?>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center">
        <center>
          <img src="img/apps/nextcloud.png" class="card-img-top " alt="...">
        </center>
        <div class="card-body">
          <h5 class="card-title">Nextcloud</h5>
          <p class="card-text">Access and share your files anywhere over the Internet.</p>
        </div>
        <div class="card-body bg-light border-top">
          <a href="#" class="card-link">Install</a>
          <a href="#" class="card-link">Website</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center">
        <center>
          <img src="img/apps/unifi.png" class="card-img-top" alt="...">
        </center>
        <div class="card-body">
          <h5 class="card-title">Unifi Controller</h5>
          <p class="card-text">Manage your network at home.</p>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">Cras justo odio</li>
          <li class="list-group-item">Dapibus ac facilisis in</li>
          <li class="list-group-item">Vestibulum at eros</li>
        </ul>
        <div class="card-body bg-light border-top">
          <a href="#" class="card-link">Install</a>
          <a href="#" class="card-link">Website</a>
        </div>
      </div>
    </div>

  </div>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>App</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>                
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Nextcloud
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
              <a href="post.php?install_nextcloud" class="btn btn-outline-success"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Jellyfin
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
              <a href="install_jellyfin.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Dokuwiki
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
              <a href="post.php?install_dokuwiki" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td><span class="mr-2" data-feather="package"></span>Syncthing</td>
          <td>Sync those Thingx</td>
          <td><a href="post.php?install_syncthing" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Transmission
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/transmission")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Torrent downloads</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/transmission")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:9091" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_transmission.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_transmission" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_transmission.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Transmission/OpenVPN
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/transmission-ovpn")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed VPN IP <?php $vpn_ip = exec("docker exec -ti transmission-ovpn curl ifconfig.co"); echo $vpn_ip; ?></small>
            <?php } ?>
          </td>
          <td>Torrent downloads</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/transmission-ovpn")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:9091" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_transmission_ovpn.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_transmission_ovpn" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_transmission-ovpn.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Unifi
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Allow you to configure and manage Unifi network devices</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi")) {
              ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>:8443" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_unifi.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_unifi" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_unifi" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Unifi Video
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
              <a href="install_unifi-video.php" class="btn btn-outline-success"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Home Assistant
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/home-assistant")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>
            Home Automation (Control Lights, switches, smart devices)
          </td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/home-assistant")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:8123" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_home-assistant.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_home-assistant" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="post.php?install_home-assistant" class="btn btn-outline-success"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>OpenVPN Server
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/openvpn")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Turn your NAS a VPN Server</td>
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
              <a href="post.php?install_openvpn" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
              <?php  
              }
              ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <span class="mr-2" data-feather="package"></span>Lychee
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/lychee")) { ?>
            <small class="text-success"><span data-feather="check"></span>Installed</small>
            <?php } ?>
          </td>
          <td>Web Based Photo Viewer</td>
          <td>
            <div class="btn-group mr-2">
              <?php 
                if(file_exists("/$config_mount_target/$config_docker_volume/docker/lychee")) {
              ?>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>:4560" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
                <a href="update_lychee.php" class="btn btn-outline-success"><span data-feather="arrow-up"></span></a>
                <a href="post.php?uninstall_lychee" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
              <?php
              }else{
              ?>
              <a href="install_lychee.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
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
