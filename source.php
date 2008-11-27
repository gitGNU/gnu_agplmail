<?php
/*
# Copyright (C) 2008 Ben Webb <dreamer@freedomdreams.co.uk>
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

$pages = array("ajax.php", "default.css", "default_ie.css", "functions.php", "index.php", "install.php", "LICENSE", "list.js", "main.js", "mess.js", "source.php", "star_fill.png", "star_nofill.png", "update.php");

if ($_GET['page'] == "") { ?>
The following are the absolute current sources for this copy of AGPLMail, as the AGPL requires. If you want an easy way to download a usable copy of gmail, <a href="http://dev.libreapps.com/wiki/AGPLMail#Download">see here</a> instead.<br/><br/>
<?php
	foreach ($pages as $page) {
		if (substr($page, -4) == ".php") {
			echo "<a href=\"source.php?page=$page\">$page</a><br/>";
		} else {
			echo "<a href=\"$page\">$page</a><br/>";
		}	
	}
}
elseif (in_array($_GET['page'], $pages)) {
	header('Content-type: text/plain');
	$file = file($_GET['page']);
	foreach ($file as $line)
		echo $line;
	die();
}
else {
	echo "Access denied.";
}
?>
