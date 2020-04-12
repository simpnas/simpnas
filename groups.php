<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nobody | grep -v nogroup", $group_array);

?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

 <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
            <h2>Groups</h2>
            <a href="group_add.php" class="btn btn-outline-primary">Add Group</a>
          </div>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Name</th>
                  <th><span data-feather="user"></span>
                  <th>Reference Shares</th>
		  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($group_array as $group) {
                $users = exec("awk -F: '/^$group/ {print $4;}' /etc/group");
                ?>
                <tr>    
                  <td><?php echo $group; ?></td>
                  <td><?php echo $users; ?></td>
                  <td>Documents</td>
		  <td>
                    <div class="btn-group mr-2">
                    <a href="group_edit.php?group=<?php echo $group; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
                    <a href="post.php?delete_group=<?php echo $group; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
                  </div>
                  </td>
                </tr>
               <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
<?php include("footer.php"); ?>
