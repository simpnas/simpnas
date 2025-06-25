<?php 
  
require_once "includes/include_all.php";

if(isset($_GET['volume_name'])){
	$volume_name = $_GET['volume_name'];

  $mountpoint = "/volumes/$volume_name/";
  $rawOutput = shell_exec("btrfs device stats $mountpoint");


}

?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="volumes.php">Volumes</a></li>
    <li class="breadcrumb-item active">File System Stats</li>
  </ol>
</nav>

<h2>File system Stats</h2>
<?php
$lines = explode("\n", trim($rawOutput));
echo "<table class='table'>";
echo "<tr><th>Error Type</th><th>Count</th></tr>";

foreach ($lines as $line) {
    // Match: [/dev/md127].write_io_errs    0
    if (preg_match('/\[(.*?)\]\.(\w+)\s+(\d+)/', $line, $matches)) {
        $errorType = $matches[2]; // write_io_errs, read_io_errs, etc.
        $errorCount = $matches[3];
        echo "<tr><td>" . htmlspecialchars($errorType) . "</td><td>" . htmlspecialchars($errorCount) . "</td></tr>";
    }
}

echo "</table>";

require_once "includes/footer.php";
