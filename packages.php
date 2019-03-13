<?php 
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
                  <td><a href="install_nextcloud.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
                </tr>
                <tr>
                  <td>Plex</td>
                  <td>Turn your NAS into a video streaming platform</td>
                  <td><a href="install_plex.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
                </tr>
                <tr>
                  <td>Deluge</td>
                  <td>BitTorrent Web Client</td>
                  <td><a href="install_deluge.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
                </tr>
                <tr>
                  <td>Dokuwiki</td>
                  <td>Make some Notes</td>
                  <td><a href="install_plex.php" class="btn btn-outline-secondary"><span data-feather="download"></span></a></td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
<?php include("footer.php"); ?>
