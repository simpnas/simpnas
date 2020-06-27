<?php 
  
  $config = include("config.php");
  include("simple_vars.php");
  include("header.php");
  include("side_nav.php");

  if(empty($config_ad_enabled)){
    exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $group_array);
    array_push($group_array,"users");
  }else{
    $ad_builtin_groups_array = array("Performance Monitor Users", "Remote Desktop Users", "Read-only Domain Controllers", "IIS_IUSRS", "Denied RODC Password Replication Group", "DnsUpdateProxy", "Enterprise Admins", "Replicator", "Windows Authorization Access Group", "Domain Controllers", "Pre-Windows 2000 Compatible Access", "Certificate Service DCOM Access", "Domain Guests", "Enterprise Read-only Domain Controllers", "Schema Admins", "Distributed COM Users", "Domain Computers", "Performance Log Users", "Network Configuration Operators", "Account Operators", "Backup Operators", "Terminal Server License Servers", "DnsAdmins", "Guests", "Cert Publishers", "Incoming Forest Trust Builders", "Print Operators", "Administrators", "Server Operators", "RAS and IAS Servers", "Allowed RODC Password Replication Group", "Cryptographic Operators", "Group Policy Creator Owners", "Event Log Readers", "Users");

    exec("samba-tool group list", $all_groups_array);
    
    $group_array = array_diff($all_groups_array,$ad_builtin_groups_array);
  }

?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
    <h2>Groups</h2>
    <a href="group_add.php" class="btn btn-outline-primary">Add Group</a>
  </div>

  <?php include("alert_message.php"); ?>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Group</th>
          <th>Users</span>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        foreach ($group_array as $group){
          if(empty($config_ad_enabled)){
            $users = str_replace(',',', ',exec("awk -F: '/^$group/ {print $4;}' /etc/group"));
          }else{
            exec("samba-tool group listmembers '$group' | grep -v krbtgt",$group_list_array);
            $users = implode(", ",$group_list_array); 
          }
          if(empty($users)){
            $users = "-";
          }
          
        ?>
        
        <tr>    
          <td><span class="mr-2" data-feather="users"></span><?php echo $group; ?></td>
          <td><?php echo $users; ?></td>
          <td>
            <?php if($group !== "users" AND $group !== "admins" AND $group !== "Domain Users" AND $group !== "Domain Admins" ){ ?>
            <div class="btn-group mr-2">
              <a href="group_edit.php?group=<?php echo $group; ?>" class="btn btn-outline-secondary"><span data-feather="edit"></span></a>
              <a href="post.php?group_delete=<?php echo $group; ?>" class="btn btn-outline-danger"><span data-feather="trash"></span></a>
            </div>
            <?php }else{ ?>
            <div class="p-3"></div> 
            <?php } ?>  
          </td>
        </tr>
        
        <?php 
        unset($group_list_array);
        } 

        ?>
      
      </tbody>
    </table>
  
  </div>

</main>

<?php include("footer.php"); ?>