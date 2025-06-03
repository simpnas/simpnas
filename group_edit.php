<?php 
    
require_once "includes/include_all.php";
  
if(isset($_GET['group'])){
  $group = $_GET['group'];
}

?>

<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="users.php">Home</a></li>
    <li class="breadcrumb-item"><a href="groups.php">Groups</a></li>
    <li class="breadcrumb-item active">Edit Group</li>
  </ol>
</nav>

<h2>Edit Group</h2>

<form method="post" action="post.php" autocomplete="off">
  <input type="hidden" name="old_group" value="<?php echo $group; ?>">
  <div class="form-group">
    <label>Group</label>
    <input type="text" class="form-control" name="group" value="<?php echo $group; ?>" required pattern="[a-z0-9-]{1,25}">
  </div>
  <button type="submit" name="group_edit" class="btn btn-primary">Submit</button>
</form>

<?php require_once "includes/footer.php";
