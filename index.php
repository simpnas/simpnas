<?php

	if(!file_exists('config.php')){
  	header("Location: setup.php");
	}else{
		header("Location: dashboard.php");
	}

?>