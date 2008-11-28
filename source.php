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
include "config.php";
$appname = "Mail";
$pages = array("ajax.php", "default.css", "default_ie.css", "functions.php", "index.php", "install.php", "LICENSE", "list.js", "main.js", "mess.js", "source.php", "star_fill.png", "star_nofill.png", "update.php");

if ($_GET['page'] == "") {
if ($libreapps) { include "../header.php"; ?>
<div id="la-content"><?php } ?>
<h2>AGPLMail Sources</h2>
The following are the absolute current sources for this copy of AGPLMail, as the AGPL requires. If you want an easy way to download a usable copy of AGPLMail, <a href="http://dev.libreapps.com/wiki/AGPLMail#Download">see here</a> instead.
<ul>
<?php
	foreach ($pages as $page) {
		if (substr($page, -4) == ".php") {
			echo "<li><a href=\"source.php?page=$page\">$page</a></li>";
		} else {
			echo "<li><a href=\"$page\">$page</a></li>";
		}	
	}
	echo "</ul>";
	if ($libreapps) { echo "</div>";
	include "../footer.php"; }
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
