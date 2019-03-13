<?php 
    include("header.php");
    include("side_nav.php");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
<?php



?>

<table class="table">
	<?php
	    $smb = file('/etc/samba/smb.conf');
	    $sambaConfigArray = parse_ini_file('/etc/samba/smb.conf', true );
	    foreach ($smb as $line) {
	        $trim_line = trim ($line);
	        $begin_char = substr($trim_line, 0, 1);
	        $end_char = substr($trim_line, -1);
          	if (($begin_char == "#") || ($begin_char == ";" || $trim_line == "[global]")) { } 
            elseif (($begin_char == "[") && ($end_char == "]")) { 
        		$section_name = substr ($trim_line, 1, -1);
   	
				$path = $sambaConfigArray[$section_name]['path'];
				$volume = basename(dirname($path));
				$comment = $sambaConfigArray[$section_name]['comment'];
				$group = $sambaConfigArray[$section_name]['force group'];
	?>
	<tr>
		<td><?php echo $section_name; ?></td>
		<td><?php echo $comment; ?></td>
		<td><?php echo $volume; ?></td>
		<td><?php echo $group; ?></td>
	</tr>
<?php }} ?>
</table>
</main>

<?php include("footer.php"); ?>