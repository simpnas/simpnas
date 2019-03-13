<?php 
    include("header.php");
    include("side_nav.php");
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

           <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
            <h2>Updates</h2>
            <a href="user_add.php" class="btn btn-outline-primary">Check For Updates</a>
            <a href="user_add.php" class="btn btn-outline-secondary">Update</a>
          </div>
       
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Package</th>
                  <th>Current Version</th>
                  <th>New Version</th>
                  <th>Update Type</th>
                  <th>Change Log</th>
                </tr>
              </thead>
              <tbody>                
                <tr>
                  <td>Openssh</td>
                  <td>3.6.5</td>
                  <td>3.6.5.2</td>
                  <td>Security</td>
                  <td>Check here</td>
                </tr>
                <tr>
                  <td>PHP</td>
                  <td>7.1.4</td>
                  <td>7.2.2</td>
                  <td>Feature</td>
                  <td>Check here</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
<?php include("footer.php"); ?>
