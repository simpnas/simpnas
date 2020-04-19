<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Logs</h2>
    <a href="user_add.php" class="btn btn-outline-danger">Clear Log</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>                
        <tr>
          <td>2018-2-19 21:45:22</td>
          <td>Delete User</td>
          <td>User Johnny was deleted</td>
        </tr>
        <tr>
          <td>2018-2-19 21:45:22</td>
          <td>Add User</td>
          <td>Share public created assigned to executive group</td>
        </tr>
        <tr>
          <td>2018-2-19 21:45:22</td>
          <td>SMART Error</td>
          <td>Disk 1 - 5 Bad Sectors found</td>
        </tr>
        <tr>
          <td>2018-2-19 21:45:22</td>
          <td>Low Space</td>
          <td>Volume "Media" is low on space and has only 1GB left</td>
        </tr>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>