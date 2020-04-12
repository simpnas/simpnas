<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd", $username_array);
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Shares</h2>
    <a href="share_add.php" class="btn btn-outline-primary">Add Share</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Volume</th>
          <th>Reference Group</th>
          <th>Size</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $smb = file('/etc/samba/smb.conf');
        $sambaConfigArray = parse_ini_file('/etc/samba/smb.conf', true );
        foreach ($smb as $line) {
            $trim_line = trim ($line);
            $begin_char = substr($trim_line, 0, 1);
            $end_char = substr($trim_line, -1);
              if (($begin_char == "#") || ($begin_char == ";" || $trim_line == "[global]")) { } 
              elseif (($begin_char == "[") && ($end_char == "]")) { 
              $section_name = substr ($trim_line, 1, -1);
      
              $path = $sambaConfigArray[$section_name]['path'];
              $volume = basename(dirname($path));
              $comment = $sambaConfigArray[$section_name]['comment'];
              $group = $sambaConfigArray[$section_name]['force group'];
              $free_space = disk_free_space("$path");
              $total_space = disk_total_space("$path");
              $used_space = $total_space - $free_space;
              $free_space = formatSize($free_space);
              $total_space = formatSize($total_space);
              $used_space = formatSize($used_space);
              if($section_name == "homes"){
                $volume = "-";
                $group = "-";
                $used_space = "-";
              }
        ?>

        <tr>
          <td><?php echo $section_name; ?></td>
          <td><?php echo $comment; ?></td>
          <td><?php echo $volume; ?></td>
          <td><?php echo $group; ?></td>
          <td><?php echo $used_space; ?></td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="share_edit.php?share=<?php echo $section_name; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
        		<button class="btn btn-outline-danger"><span data-feather="trash"></span></button>
      		</div>
      	  </td>
        </tr>
        <?php } }?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>