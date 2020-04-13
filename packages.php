<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Packages</h2>
  </div>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Package</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>                
        <tr>
          <td>Nextcloud</td>
          <td>Access and share your files anywhere over the Internet</td>
          <td><a href="install_nextcloud.php" class="btn btn-outline-success"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>
            Jellyfin
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/jellyfin")) { ?>
            <small class="text-success">Installed</small>
            <?php } ?>
          </td>
          <td>Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc</td>
          <td>
            <?php 
              if(file_exists("/$config_mount_target/$config_docker_volume/docker/jellyfin")) {
            ?>
              <a href="http://<?php echo gethostname(); ?>:8096" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
              <a href="uninstall_jellyfin.php" class="btn btn-outline-danger"><span data-feather="x"></span></a>
            <?php
            }else{
            ?>
            <a href="install_jellyfin.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
            <?php  
            }
            ?>
          </td>
        </tr>
        <tr>
          <td>Dokuwiki</td>
          <td>Make some Notes</td>
          <td><a href="install_dokuwiki.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>Syncthing</td>
          <td>Sync those Thingx</td>
          <td><a href="post.php?install_syncthing" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>
            Transmission
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/transmission")) { ?>
            <small class="text-success">Installed</small>
            <?php } ?>
          </td>
          <td>Torrent downloads</td>
          <td>
            <?php 
              if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi")) {
            ?>
              <a href="http://<?php echo gethostname(); ?>:9091" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
              <a href="uninstall_transmission.php" class="btn btn-outline-danger"><span data-feather="x"></span></a>
            <?php
            }else{
            ?>
            <a href="install_transmission.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
            <?php  
            }
            ?>
          </td>
        </tr>
        <tr>
          <td>
            Unifi
            <br>
            <?php if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi")) { ?>
            <small class="text-success">Installed</small>
            <?php } ?>
          </td>
          <td>Allow you to configure and manage Unifi network devices</td>
          <td>
            <?php 
              if(file_exists("/$config_mount_target/$config_docker_volume/docker/unifi")) {
            ?>
              <a href="https://<?php echo gethostname(); ?>:8443" target="_blank" class="btn btn-outline-primary"><span data-feather="eye"></span></a>
              <a href="uninstall_unifi.php" class="btn btn-outline-danger"><span data-feather="x"></span></a>
            <?php
            }else{
            ?>
            <a href="post.php?install_unifi" class="btn btn-outline-secondary"><span data-feather="download"></span></a>
            <?php  
            }
            ?>
          </td>
        </tr>
        <tr>
          <td>OpenVPN Server</td>
          <td>Turn your NAS a VPN Server</td>
          <td><a href="post.php?install_openvpn" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>Lychee</td>
          <td>Web Based Photo Viewer</td>
          <td><a href="install_lychee.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
