<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
  <div class="sidebar-sticky pt-3">
    
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="home"></span>
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="http://<?php echo $config_primary_ip; ?>:82" otarget="_blank">
          <span data-feather="file"></span>
          File Manager
        </a>
      </li>
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
      <span>Storage</span>
    </h6>
    
    <ul class="nav flex-column mb-2">
      
      <li class="nav-item">
        <a class="nav-link" href="disks.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="hard-drive"></span>
          Disks
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="volumes.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="database"></span>
          Volumes
        </a>
      </li>
    
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
      <span>Users and Shares</span>
    </h6>
    
    <ul class="nav flex-column mb-2">
      
      <li class="nav-item">
        <a class="nav-link" href="users.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="user"></span>
          Users
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="groups.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="users"></span>
          Groups
        </a>
      </li>
      
      <?php if(!empty($config_ad_enabled)){ ?>
      <li class="nav-item">
        <a class="nav-link" href="computers.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="monitor"></span>
          Computers
        </a>
      </li>
      <?php } ?>
      
      <li class="nav-item">
        <a class="nav-link" href="shares.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="folder"></span>
          Shares
        </a>
      </li>
    
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
      <span>Settings</span>
    </h6>
    
    <ul class="nav flex-column mb-2">
      
      <li class="nav-item">
        <a class="nav-link" href="datetime.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="clock"></span>
          Date & Time
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="network.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="globe"></span>
          Network
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="configure_remote_access.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="cloud"></span>
          Remote Access
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="notifications.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="mail"></span>
          Notifications
        </a>
      </li>
      
      
    
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
      <span>Maintenance</span>
    </h6>
    
    <ul class="nav flex-column mb-2">
      
      <li class="nav-item">
        <a class="nav-link" href="updates.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="download"></span>
          Updates
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="apps.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="package"></span>
          Apps
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="logs.php?daemon" onclick="$('#cover-spin').show(0)">
          <span data-feather="book"></span>
          Logs
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="power.php" onclick="$('#cover-spin').show(0)">
          <span data-feather="power"></span>
          Power
        </a>
      </li>
    
    </ul>                      
  </div>
</nav>