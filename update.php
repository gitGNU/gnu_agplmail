<?php
	include "config.php";
	$con = mysql_connect($db_host,$db_name,$db_pass);
	if (!$con) {
	  die('Could not connect: ' . mysql_error());
	}
	if (mysql_select_db($db_db, $con)); else die(mysql_error());
	
	if (mysql_query("ALTER TABLE `".$db_prefix."mess` ADD `expanded` tinyint(1) NOT NULL", $con)); else echo mysql_error();
	
?>
