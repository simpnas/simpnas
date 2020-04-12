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
          <td>Jellyfin</td>
          <td>Turn your NAS into a media streaming platform for your Smart TVs, Smart devices (Roku, Amazon TV, Apple TV, Google TV), computers, phones etc</td>
          <td><a href="install_jellyfin.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
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
          <td>Transmission</td>
          <td>Torrent some Movies</td>
          <td><a href="install_transmission.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
        </tr>
        <tr>
          <td>Unifi</td>
          <td>Turn your NAS into a video streaming platform</td>
          <td><a href="post.php?install_unifi" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
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
