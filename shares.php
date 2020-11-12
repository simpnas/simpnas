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
  
  <?php include("alert_message.php"); ?>

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
            if(empty($comment)){
              $comment = "-";
            }
            $group = $sambaConfigArray['force group'];
            if(empty($group)){
              $group = "-";
            }
            $read_only = $sambaConfigArray['read only'];
            $used_space = exec("du -sh $path | awk '{print $1}'");

        ?>

        <tr>
          <td>
            <span class="mr-2" data-feather="folder"></span><strong><?php echo $share; ?></strong>
            <?php if($read_only == 1){ echo "<small class='text-danger'>Read Only</small>"; } ?>
            <br>
            <div class="ml-4 text-secondary"><?php echo $volume; ?></div>
          </td>
          <td><?php echo $comment; ?></td>
          <td>
            <?php echo $group; ?>
          </td>
          <td><?php echo $used_space; ?>B</td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="share_edit.php?share=<?php echo $share; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
        		<button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteShare<?php echo $share; ?>"><span data-feather="trash"></span></button>
      		</div>
      	  </td>
        </tr>
        
        <div class="modal fade" id="deleteShare<?php echo $share; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-trash"></i> Delete <?php echo $share; ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <center>
                  <h3 class="text-secondary">Are you sure you want to</h3>
                  <h1 class="text-danger">Delete <strong><?php echo $share; ?></strong>?</h1>
                  <h5>This will delete all data within the share</h5>
                </center>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="post.php?share_delete=<?php echo $share; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span> Delete NOW</a>
              </div>
            </div>
          </div>
        </div>


        <?php } ?>
      </tbody>
    </table>

  </div>
</main>

<?php include("footer.php"); ?>