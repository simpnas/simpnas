<div class="bg-light p-2 mb-3">
  <ul class="nav nav-pills justify-content-center">
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['daemon'])){ echo "active"; } ?>" href="?daemon">Daemon</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['auth'])){ echo "active"; } ?>" href="?auth">Auth</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['messages'])){ echo "active"; } ?>" href="?messages">Messages</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['kernel'])){ echo "active"; } ?>" href="?kernel">Kernel</a>
    </li>
  </ul>
</div>
<hr>