<?php

	if(!file_exists('config.php')){
  	header("Location: setup/setup.php");
	}else{
		header("Location: dashboard.php");
	}

?>