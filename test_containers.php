<?php 
require_once "includes/include_all.php";

// Get the container stats as JSON
$json_docker_stats = shell_exec("docker stats --no-stream --format '{{json .}}'");

// Decode the JSON string into an array
$containers_array = explode("\n", $json_docker_stats); // Each container's stats will be a new line in the output

// Filter out empty results (just in case there's an empty line at the end)
$containers_array = array_filter($containers_array);

// Now we will parse each container's stats into a structured format
$containers = [];
foreach ($containers_array as $container_stats) {
    $container_data = json_decode($container_stats, true); // Decode the JSON for each container
    if ($container_data) {
        $containers[] = $container_data;
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-2">
  <h2>Containers</h2>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Container</th>
        <th>CPU %</th>
        <th>Memory Usage</th>
        <th>Net I/O</th>
        <th>Disk I/O</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($containers as $container) { ?>
        <tr>
          <td><?php echo htmlspecialchars($container['Name']); ?></td>
          <td><?php echo htmlspecialchars($container['CPUPerc']); ?>%</td>
          <td><?php echo htmlspecialchars($container['MemUsage']); ?></td>
          <td><?php echo htmlspecialchars($container['NetIO']); ?></td>
          <td><?php echo htmlspecialchars($container['BlockIO']); ?></td>
          <td>
            <div class="btn-group mr-2">
              <a href="container_action.php?name=<?php echo urlencode($container['Name']); ?>" class="btn btn-outline-secondary">
                <span data-feather="edit-2"></span>
              </a>
            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once "includes/footer.php"; ?>
