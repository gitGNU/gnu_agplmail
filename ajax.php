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
include "functions.php";
#$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);

if ($_GET['do'] == "contactac") {
	$addrs = split("[,;]", $_GET['query']);
	$bit = trim(array_pop($addrs));
	$prev = "";
	foreach ($addrs as $addr) {
		$prev .= trim($addr).", ";
	}
	$comp = "%$bit%";
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."addressbook` WHERE account='$user' AND ( name like '$comp' OR address like '$comp' ) ORDER BY priority DESC, name")); else die(mysql_error());
	#echo $_SERVER['REQUEST_URI']."\t".$_SERVER['REQUEST_URI']."\n";
	while($row=mysql_fetch_array($result)) {
		echo $prev.$row['name']." <".$row['address'].">\t".str_ireplace($bit, "<strong>$bit</strong>", $row['name']." &lt;".$row['address']."&gt;")."\n";
	}
}

?>
