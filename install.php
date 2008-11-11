<?php
/*
# Copyright (C) 2008 Aaron Williamson <aaron@copiesofcopies.org>
# This file is part of AGPLMail.
#
# AGPLMail is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# AGPLMail is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with AGPLMail.  If not, see <http://www.gnu.org/licenses/>.
*/

?>
<html>
<head>
<title>AGPLMail</title>
<link rel="stylesheet" type="text/css" href="default.css"></link>
<script language="javascript" src="ajax.js"></script>
<script language="javascript" src="main.js"></script>
<script language="javascript" src="whizzywig.js"></script>
</head>
<body>

<h1>AGPLMail Install</h1>
<p/>
<?php
if($_SERVER['REQUEST_METHOD'] == 'GET') {
?>
<script language="javascript">
	function adaptForm() {
		var dbtype = document.getElementById("dbtype");
		var mysqlroot = document.getElementById("mysqlrootdiv").style;

		mysqlroot.display = (mysqlroot.display==''||mysqlroot.display=='block')?'none':'block';
	}
	function validateForm(form) {
		var pwd = document.getElementById("agplmpwd").value;
		var pwd2 = document.getElementById("agplmpwd_confirm").value;

		if(pwd != pwd2) {
			alert("MySQL user passwords do not match.");
			return false;
		}
		
		return true;
	}
</script>
<form name="install_options" action="install.php" method="post" onSubmit="return validateForm(self);">
	<div style="width:100%; float:left;">
		<div style="width:300px; text-align:left; float:left;">
		Use existing MySQL database:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input name="dbtype" id="dbtype" type="radio" value="existing" checked onChange="adaptForm();"/>
		</div>
	</div>
	<div style="width:100%; float:left;">
		<div style="width:300px; text-align:left; float:left;">
		Create new MySQL database:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input name="dbtype" id="dbtype" type="radio" value="new" onChange="adaptForm();"/>
		</div>
	</div>
	<div id="mysqlrootdiv" style="width:100%; float:left; margin-top:40px; display:none;">
		<div style="width:400px; text-align:left; float:left;">
		MySQL root password:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input name="mysqlrootpwd" id="mysqlrootpwd" type="password" size="10"/>
		</div>
	</div>
        <div style="width:100%; margin-top:40px; float:left;">
                <div style="width:400px; text-align:left; float:left;">
                MySQL host for AGPLMail:
	        </div>
	        <div style="width:200px; text-align:left; float:left;">
	        <input name="dbhost" id="dbhost" type="text" size="10" value="localhost"/>
	        </div>
	</div>
        <div style="width:100%; float:left;">
                <div style="width:400px; text-align:left; float:left;">
                MySQL database for AGPLMail:
	        </div>
	        <div style="width:200px; text-align:left; float:left;">
	        <input name="dbname" id="dbname" type="text" size="10"/>
	        </div>
	</div>
        <div style="width:100%; float:left;">
                <div style="width:400px; text-align:left; float:left;">
                Prefix for AGPLMail tables:
	        </div>
	        <div style="width:200px; text-align:left; float:left;">
	        <input	 name="prefix" id="prefix" type="text" size="10" value="agplmail_"/>
	        </div>
	</div>
	<div style="width:100%; margin-top: 40px; float:left;">
	        <div style="width:400px; text-align:left; float:left;">
	        MySQL user for AGPLMail:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input	name="agplmuser" id="agplmuser" type="text" size="10"/>
		</div>
	</div>
	<div style="width:100%; float:left;">
	        <div style="width:400px; text-align:left; float:left;">
	        Password for AGPLMail MySQL user:
	        </div>
	        <div style="width:200px; text-align:left; float:left;">
	        <input  name="agplmpwd" id="agplmpwd" type="password" size="10"/>
	        </div>
	</div>
	<div style="width:100%; float:left;">
	        <div style="width:400px; text-align:left; float:left;">
	        Confirm password:
	        </div>
	        <div style="width:200px; text-align:left; float:left;">
	        <input  name="agplmpwd_confirm" id="agplmpwd_confirm" type="password" size="10"/>
	        </div>
	</div>
	<div style="width:100%; margin-top: 40px; float:left;">
	        <div style="width:400px; text-align:left; float:left;">
	        IMAP server:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input	name="server" id="server" type="text" size="10"/>
		</div>
	</div>
	<div style="width:100%; float:left;">
	        <div style="width:400px; text-align:left; float:left;">
	        Email user suffix:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<input	name="userprefix" id="userprefix" type="text" size="10"/>
		</div>
	</div>
	<div style="width:100%; float:left; margin-top:40px;">
	        <div style="width:400px; text-align:left; float:left;">
	        Custom home message:
		</div>
		<div style="width:200px; text-align:left; float:left;">
		<textarea name="customhome" id="customhome" cols="40" rows="3" />Find out more about AGPLMail at our <a href="http://freedomdreams.co.uk/wiki/AGPLMail">wiki page.</a></textarea>
		</div>
	</div>

	<div style="width:100%; float:left; margin-top:40px;">
		<div style="width:100%; text-align:left; float:left;">
                <input type="submit" value="Submit"/>
                </div>
	</div>

</form>
<?
} else {
	$dbtype = $_POST["dbtype"];
	$dbhost = $_POST["dbhost"];
	$dbname = $_POST["dbname"];
	$prefix = $_POST["prefix"];
	$mysqlrootpwd = $_POST["mysqlrootpwd"];
	$agplmuser = $_POST["agplmuser"];
	$agplmpwd = $_POST["agplmpwd"];
	$server = $_POST["server"];
	$userprefix = $_POST["userprefix"];
	$customhome = $_POST["customhome"];

	if($dbtype == "existing") {
?>
<p>Connecting to database...
<?php
		$con = mysql_connect($dbhost,$agplmuser,$agplmpwd);
		if (!$con) {
			die('Could not connect to MySQL database: ' . mysql_error());
		}
		if (mysql_select_db($dbname, $con)); else die('Could not access database ' . $dbname . ': ' . mysql_error());
?>
<font color="green">success!</font></p>
<?php
	} elseif ($dbtype == "new") {
?>
<p>Creating database...
<?php
		$con = mysql_connect($dbhost, 'root', $mysqlrootpwd);
                if (!$con) {
	                die('Could not connect to MySQL database as root: ' . mysql_error());
                }
		$query  = 'CREATE DATABASE ' . $dbname;
		mysql_query($query) or die ('Cannot create new database ' . $dbname . ': ' . mysql_error());
?>
<font color="green">success!</font></p>
<p>Connecting to database...
<?php
		$query  = "GRANT ALL PRIVILEGES ON " . $dbname .".* TO '" . $agplmuser . "'@'%' IDENTIFIED BY '" . $agplmpwd . "'";
		mysql_query($query) or die ('Cannot give access on ' . $dbname . ' to ' . $agplmuser . ': ' . mysql_error());
		mysql_select_db($dbname) or die('Cannot select new database ' . $dbname . ': ' . mysql_error()); 
?>
<font color="green">success!</font></p>
<?php
	}
?>
<p>Creating database structure...
<?php
	$sql = "CREATE TABLE `" . $prefix . "convos` (";
	$sql .= "  `id` int(11) NOT NULL,";
	$sql .= "  `modified` datetime NOT NULL,";
	$sql .= "  `archived` tinyint(1) NOT NULL default '0',";
	$sql .= "  `deleted` tinyint(1) NOT NULL default '0',";
	$sql .= "  `account` text NOT NULL,";
	$sql .= "  `starred` tinyint(1) NOT NULL default '0',";
	$sql .= "  `read` tinyint(1) NOT NULL,";
	$sql .= "  `nomsgs` int(11) NOT NULL default '1',";
	$sql .= "  `saved` int(11) NOT NULL default '0'";
	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";

	mysql_query($sql) or die ('Cannot create convos table: ' . mysql_error());

	$sql = "CREATE TABLE `" . $prefix . "mess` (";
	$sql .= "  `account` varchar(100) NOT NULL,";
	$sql .= "  `uid` varchar(16) NOT NULL,";
	$sql .= "  `messid` varchar(200) NOT NULL,";
	$sql .= "  `pos` int(11) NOT NULL default '1',";
	$sql .= "  `convo` int(11) NOT NULL,";
	$sql .= "  `date` datetime NOT NULL,";
	$sql .= "  `deleted` tinyint(1) NOT NULL,";
	$sql .= "  `saved` int(11) NOT NULL default '0'";
	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1; ";

	mysql_query($sql) or die ('Cannot create mess table: ' . mysql_error());

	$sql = "CREATE TABLE `" . $prefix . "saved` (";
	$sql .= "  `id` varchar(16) NOT NULL,";
	$sql .= "  `headers` text NOT NULL,";
	$sql .= "  `body` text NOT NULL,";
	$sql .= "  `html` tinyint(1) NOT NULL default '0',";
	$sql .= "  `account` varchar(100) NOT NULL,";
	$sql .= "  `date` datetime NOT NULL";
	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1; ";

	mysql_query($sql) or die ('Cannot create saved table: ' . mysql_error());

	$sql = "CREATE TABLE `" . $prefix . "settings` (";
	$sql .= "  `account` text NOT NULL,";
	$sql .= "  `name` text NOT NULL,";
	$sql .= "  `value` text NOT NULL";
	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1; ";

	mysql_query($sql) or die ('Cannot create settings table: ' . mysql_error());

	$sql = "CREATE TABLE `" . $prefix . "tags` (";
	$sql .= "  `account` varchar(100) NOT NULL,";
	$sql .= "  `name` varchar(50) NOT NULL,";
	$sql .= "  `convo` int(11) NOT NULL";
	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";

	mysql_query($sql) or die ('Cannot create tags table: ' . mysql_error());
?>
<font color="green">success!</font></p>
<p>Writing config.php...
<?php
	$handle = fopen("config.php", 'w') or die("<font color=red>Can't open config.php for writing -- please make sure your www user has write permission in this directory.</font>");

	$config = '<?php' . "\n";
	$config .= '$db_host = "' . $dbhost . "\";\n";
	$config .= '$db_name = "' . $agplmuser . "\";\n";
	$config .= '$db_pass = "' . $agplmpwd . "\";\n";
	$config .= '$db_db = "' . $dbname . "\";\n";
	$config .= '$db_prefix = "' . $prefix . "\";\n\n";
	$config .= '$server = "' . $server . "\";\n";
	$config .= '$userprefix = "' . $userprefix . "\";\n";
	$config .= '$customhome = "' . addslashes($customhome) . "\";\n";
	$config .= '?>';

	fwrite($handle,$config);
	fclose($handle);
?>
<font color="green">success!</font>
<p>Changing permissions on config.php and install.php to prevent web user access:
<?php
chmod("config.php",0644) or die("<font color=red>Could not change permissions on config.php</font>");
chmod("install.php",0644) or die("<font color=red>Could not change permissions on install.php</font>");
?>
<font color="green">success!</font>
<?php
}
?>
</body>
</html>

