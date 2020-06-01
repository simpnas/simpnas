<?php 
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $username_array);
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Shares</h2>
    <a href="share_add.php" class="btn btn-outline-primary">Add Share</a>
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
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Share</th>
          <th>Description</th>
          <th>Group</th>
          <th>Size</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        
        <?php
        
        exec("ls /etc/samba/shares", $share_list);
          foreach ($share_list as $share) {

            $sambaConfigArray = parse_ini_file("/etc/samba/shares/$share");
            $path = $sambaConfigArray['path'];
            $volume = basename(dirname($path));
            $comment = $sambaConfigArray['comment'];
            $group = $sambaConfigArray['force group'];
            $used_space = exec("du -sh $path | awk '{print $1}'");

        ?>

        <tr>
          <td>
            <span class="mr-2" data-feather="folder"></span><strong><?php echo $share; ?></strong>
            <br>
            <div class="ml-4 text-secondary"><?php echo $volume; ?></div>
          </td>
          <td><?php echo $comment; ?></td>
          <td><?php echo $group; ?></td>
          <td><?php echo $used_space; ?></td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="share_edit.php?share=<?php echo $share; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
        		<a href="post.php?share_delete=<?php echo $share; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
      		</div>
      	  </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

  </div>
</main>

<?php include("footer.php"); ?>