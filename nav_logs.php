<div class="bg-light p-2 mb-3">
  <ul class="nav nav-pills justify-content-center">
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['daemon'])){ echo "active"; } ?>" href="?daemon" onclick="$('#cover-spin').show(0)">Daemon</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['auth'])){ echo "active"; } ?>" href="?auth" onclick="$('#cover-spin').show(0)">Auth</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['messages'])){ echo "active"; } ?>" href="?messages" onclick="$('#cover-spin').show(0)">Messages</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if(isset($_GET['kernel'])){ echo "active"; } ?>" href="?kernel" onclick="$('#cover-spin').show(0)">Kernel</a>
    </li>
  </ul>
</div>
<hr>