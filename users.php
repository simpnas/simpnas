<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
    
    exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

           <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
            <h2>Users</h2>
            <a href="user_add.php" class="btn btn-outline-primary">Add User</a>
          </div>
       
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>User</th>
                  <th><span data-feather="users"></span></th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
<?php 	  foreach ($username_array as $username) {
          $groups = str_replace(' ',", ",exec("groups $username | sed 's/\($username\| : \)//g'")); //replace space with a , and a space makes it look neater
?>
                <tr>
                  <td><span class="mr-2" data-feather="user"></span><?php echo $username; ?></td>
                  <td><?php echo $groups; ?></td>
                  <td>
                    <div class="btn-group mr-2">
                    <a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
                    <a href="post.php?delete_user=<?php echo $username; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
                  </div>
                  </td>
                </tr>
<?php } ?>
              </tbody>
            </table>
          </div>
        </main>

<?php include("footer.php"); ?>
