<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
  $git_log = shell_exec("git log --pretty=format:'<tr><td>%h</td><td>%an</td><td>%ar</td><td>%s</td></tr>'");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Updates</h2>
    <a href="updates.php?check" class="btn btn-outline-primary">Check For OS Updates</a>
    <a href="post.php?upgrade" class="btn btn-outline-secondary">Upgrade OS Packages</a>
    <a href="post.php?upgrade_simpnas_overwrite_local_changes" class="btn btn-outline-secondary">Upgrade SimpNAS</a>
  </div>
  <table class="table ">
    <thead>
      <tr>
        <th>ID</th>
        <th>By</th>
        <th>When</th>
        <th>Changes</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      echo $git_log;
      ?>
    </tbody>
  </table>
</main>

<?php include("footer.php"); ?>