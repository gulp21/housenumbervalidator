<?php
	//! create a list of users which created the problems/dubes in the @param bbox
	
	header("Content-Type: text/html; charset=UTF-8");
	
	$_GET['areastat']=1;
	
	include("get_problematic.php");
	include("get_dupes.php");
	
?>
