<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");

  if($_GET['check']){
    $exec("apt update");
  }

  if($_GET['upgrade']){
    $exec("apt upgrade -y");
  }

?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Updates</h2>
    <a href="updates.php?check" class="btn btn-outline-primary">Check For OS Updates</a>
    <a href="post.php?upgrade" class="btn btn-outline-secondary">Upgrade OS Packages</a>
    <a href="post.php?upgrade_simpnas_overwrite_local_changes" class="btn btn-outline-secondary">Upgrade SimpNAS</a>
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
        <?php
        exec("git log --pretty=format:'%h:%an:%ar:%s'", $git_log_array);
        
        foreach($packages_array as $package){
          $nice_package_name = exec("apt list --upgradeable | grep '$package' | awk -F/ '{print $1}'");
          $current_version = str_replace(']','',exec("apt list --upgradeable | grep '$package' | awk '{print $6}'"));
          $new_version = exec("apt list --upgradeable | grep '$package' | awk '{print $2}'");

        ?>
        <tr>
          <td><?php echo $nice_package_name; ?></td>
          <td><?php echo $current_version; ?></td>
          <td><?php echo $new_version; ?></td>
          <td>Security</td>
          <td>Check here</td>
        </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>