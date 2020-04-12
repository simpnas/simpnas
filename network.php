<?php 
  include("config.php");
  include("header.php");
  include("side_nav.php");
?>

 <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Network</h2>
    <a href="user_add.php" class="btn btn-outline-primary">Create Network</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Interface</th>
          <th>Type</th>
          <th>IP Address</th>
          <th>Netmask</th>
          <th>Gateway</th>
          <th>DNS</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>                
        <tr>
          <td>Eth0</td>
          <td>Ethernet</td>
          <td><?php  ?></td>
          <td>255.255.255.0</td>
          <td>192.168.1.1</td>
          <td>192.168.1.1</td>
          <td>UP</td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit-2"></span></a>
        		<a href="post.php?delete_user=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="x"></span></a>
      		</div>
      	  </td>
        </tr>
        <tr>
          <td>Eth0.10</td>
          <td>VLAN</td>
          <td>192.168.1.247</td>
          <td>255.255.255.0</td>
          <td>192.168.1.1</td>
          <td>192.168.1.1</td>
          <td>UP</td>
          <td>
          	<div class="btn-group mr-2">
        		<a href="user_edit.php?username=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="edit-2"></span></a>
        		<a href="post.php?delete_user=<?php echo $username; ?>" class="btn btn-outline-secondary"><span data-feather="x"></span></a>
      		</div>
      	  </td>
        </tr>
      </tbody>
    </table>
  </div>
</main>

<?php include("footer.php"); ?>
