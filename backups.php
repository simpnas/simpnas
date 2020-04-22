<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("ls /etc/cron.*/backup*", $backups_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Backups</h2>
    <a href="backup_add.php" class="btn btn-outline-primary">Add Backup</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Source</th>
          <th>Destination</th>
          <th>When</th>
          <th>Last-Backup</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($backups_array as $backup){
          $source = explode("-",$backup)[1];
          $destination = explode("-",$backup)[2];
          $occurance = explode(".",$backup)[1];
          $occurance = substr($occurance, 0, strpos($occurance, '/'))
        ?>

        <tr>
          <td><?php echo $source; ?></td>
          <td><?php echo $destination; ?></td>
          <td><?php echo $occurance; ?></td>
          <td>NEVER</td>
          <td>
            <div class="btn-group mr-2">
              <a href="backup_edit.php?backup=<?php echo basename($backup); ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
              <a href="post.php?backup_delete=<?php echo basename($backup); ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
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
