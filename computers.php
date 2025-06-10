<?php 
    
require_once "includes/include_all.php";
  
exec("samba-tool computer list", $computers_array);

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Computers</h2>
    <a href="user_computer.php" class="btn btn-outline-primary">Add Computer</a>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Computer</th>
          <th>Operating System</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 	  
        foreach($computers_array as $computer){
          $computer = rtrim($computer,"$");
          $os = exec("samba-tool computer show $computer | grep operatingSystem: | awk -F: '{print $2}'");
        ?>
          <tr>
            <td><span class="mr-2" data-feather="monitor"></span><?php echo $computer; ?></td>
            <td><?php echo $os; ?></td>
            <td>
              <div class="btn-group mr-2">
              <a href="computer_edit.php?username=<?php echo $computer; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
              <a href="post.php?computer_delete=<?php echo $computer; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
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