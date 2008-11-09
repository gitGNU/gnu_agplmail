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

if ($_GET['page'] == "") { ?>
<a href="ajax.js">ajax.js</a><br/>
<a href="source.php?page=ajax.php">ajax.php</a><br/>
<a href="source.php?page=config.php.example">config.php.example</a><br/>
<a href="default.css">defualt.css</a><br/>
<a href="source.php?page=functions.php">functions.php</a><br/>
<a href="source.php?page=index.php">index.php</a><br/>
<a href="LICENSE">README</a><br/>
<a href="list.js">list.js</a><br/>
<a href="main.js">main.js</a><br/>
<a href="README">README</a><br/>
<a href="source.php?page=source.php">source.php</a><br/>
<a href="star_fill.png">star_fill.png</a><br/>
<a href="star_nofill.png">star_nofill.png</a><br/>
<a href="structure.sql">structure.sql</a>
<a href="whizzywig.js">whizzywig.js</a><br/>
<?php }
elseif ($_GET['page'] == "index.php" || $_GET['page'] == "functions.php" || $_GET['page'] == "ajax.php" || $_GET['page'] == "config.php.example") {
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
