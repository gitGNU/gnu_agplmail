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
$me = $_SERVER['SCRIPT_NAME'];

?>
<html>
<head>
<title>AGPLMail</title>
<link rel>
<link rel="stylesheet" type="text/css" href="default.css"></script>
</head>
<body>

<h1>AGPLMail</h1>
<?php

if ($_GET['do'] == logout) {
	session_destroy(); ?>
<h2>Logged out</h2> <a href="<?php echo $me ?>">Return to login</a>?
<?php }
elseif (!$_SESSION['username']) {
echo "<br/>".$customhome;
?>

<h2>Login</h2>
<form method="post" action="<?php echo $me ?>">
	User: <input name="username"></input><br/>
	Password: <input name="password" type="password"></input><br/>
	<button type="submit">Submit</button>
</form>

<?php }
else {

$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);

if (!$mbox) {
	session_destroy(); ?>
<h2>Sorry login failed</h2> <a href="<?php echo $me ?>">Try again</a>?
<?php }
else {

echo "<div id=\"intro\">Welcome ".$uname." it is ".date("H:i").". <a href=\"$me?do=logout\">Logout</a>?</div>";

echo "<div id=\"sidebar\">";
echo "<a href=\"$me?do=new\">New Email</a>";
echo "<h2>Folders</h2>\n";
/*
$folders = imap_list($mbox, "{".$server."}", "*");

foreach ($folders as $f) {
	$f = ereg_replace("\{.*\}","",$f);
    echo "<a href=\"$me?do=list&folder=".$f."\">".nice_folder($f)."</a><br />\n";
}
*/
?>
<a href="<?php echo $me ?>?do=list&pos=0&view=inbox">Inbox</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=arc">Archive</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=star">Starred</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=bin">Bin</a><br/>
<br/>
<a href="<?php echo $me ?>?do=settings">Settings</a><br/>
<?php

echo "</div><div id=\"main\">";

if ($_GET['do'] == "listaction" || $_GET['do'] == "messaction") {
	do_actions();
}

########################### Settings ###########################
if ($_GET['do'] == "settings") {
	if ($_POST['name']) {
		add_setting("name",$_POST['name']);
	}
	?>
<h2>Settings</h2>
<form method="post" action="<?php echo $me ?>?do=settings">
	Name: <input name="name" value="<?php echo get_setting("name"); ?>"></input><br/>
	<button type="submit">Submit</button>
</form>
	<?php
}
########################### Send Message ###########################
elseif ($_GET['do'] == "send") {
#	print_r($_POST);
#	if ($_SESSION['username'] == "demo") {
#		echo "<h2>This is a Demo</h2>Sorry, sending is disabled.";
#		$_SESSION["headers"] = "";
#	}
#	else {
		imap_mail($_POST["to"], $_POST["subject"], $_POST["content"], $_SESSION["headers"]."Content-Type: text/plain; charset=\"utf-8\"\n", $_POST["cc"], $user.", ".$_POST["bcc"], get_setting("name")." <$user>");
		$_SESSION["headers"] = "";
?>
<h2>Message Sent</h2>
<a href="<?php echo $me ?>">Return to inbox</a>?
<?php } #}
########################### New Message ###########################
elseif ($_GET['do'] == "new") {
	echo "<h2>New Email</h2>";
	echo enewtext("","","","","");
}
########################### View Message ###########################
elseif ($_GET['do'] == "message") {
	if ($_GET['range']) {
		$convo = $_GET['range'];
	}
	else {
		$convo = $_GET['convo'];
	}
?>
<script>
function moreact(value) {
	location.href = "<?php echo $me ?>?do=messaction&type="+value+"&range="+"<?php echo $convo ?>";
}
</script>	
<?php
	echo "<a href=\"$me?do=list\">&laquo; Back to ".nice_view($view)."</a> ".actions()."<br>";
	$header = imap_headerinfo($mbox,$convos[$convo][0]);
	echo "<h2>".$header->subject."</h2>";
	foreach ($convos[$convo] as $key => $msgno) {
		$header = imap_headerinfo($mbox,$msgno);
		$body = nl2br(htmlspecialchars(imap_body($mbox, $msgno)));
		echo "<div class=\"emess\"><div class=\"ehead\">From: ".nice_addr_list($header->from)."<br/>";
		if ($header->to) echo "To: ".nice_addr_list($header->to)."<br/>";
		if ($header->cc) echo "CC: ".nice_addr_list($header->cc)."<br/>";
		echo "Date: ".date("j F Y H:i",$header->udate)."<br/>";
		echo "Subject: ".$header->subject."</div><br/>";
#		print_r($header);
		echo "<div class=\"econ\">".$body."</div>"; ?>
	<script language="javascript">
function reply<?php echo $e ?>() {
	ajax("msgno=<?php echo $msgno ?>", "esend<?php echo $e ?>", false);
}
</script>
<br/><div class="efoot"><a href="javascript:reply<?php echo $e ?>()">Reply</a> Reply to All Forward</div><div id="esend<?php echo $e ?>"></div></div>
	<?php
		$e++;
	}
}
########################### View Folder ###########################
else {
	echo "<h2>".nice_view($view)."</h2>\n";

	if ($notif) {
		echo "<div id=\"notif\">".$notif."</div>";
	}

	$status = imap_status($mbox, "{".$server."}".$folder, SA_ALL);
#	print_r($status);
#	echo "There are ".$status->messages." messages in the ".nice_inf($folder).".<br><br>\n";
	if ($status->messages != 0) {
		$threads = imap_thread($mbox);
		$self = "$me?do=list&folder=$folder";
		$threadlen = 0;
		$convos = array();
		$i = 0;
		foreach ($threads as $key => $val) {
			$tree = explode('.', $key);
			if ($tree[1] == 'num' && $val != 0) {
				$threadlen++;
				$convos[$i][] = $val;
			} elseif ($tree[1] == 'branch') {
				if ($threadlen != 0) {
					$i++;
				}
				$threadlen = 0;
			}
		}
		
		$listlen = 50;
		if ($_GET['pos'] != "") {
			$liststart = $_GET['pos'];
			$_SESSION['pos'] = $_GET['pos'];
		}
		elseif ($_SESSION['pos']) {
			$liststart = $_SESSION['pos'];
		}
		else {
			$liststart = 0;
		}
		
		// This code fails because it counts the number of archived *messages*, not conversations
		/* if ($view=="inbox") {
			$arc = count_mess("archived=1 AND deleted=0");
			print sizeof($convos) ." ". $arc;
			$total = sizeof($convos) - $arc;
		}
		elseif ($view=="arc") {
			$total = count_mess("archived=1 AND deleted=0");
		} */
		$total = "???";
		
		$messrows = array();
		$i = sizeof($convos);
		$convoend = $i;
		$count = 0;
		$next = true;
		while ($count < $listlen) {
			$i--;$convostart = $i;
			$seen = true;
			$star = false;
			$allarchived = true;
			$del = false;
			if (!$convos[$i]) {
				$next = false;
				break;
			}
			foreach($convos[$i] as $val) {
				$tmpheader = imap_headerinfo($mbox, $val);
				if ($tmpheader->Unseen == "U" || $tmpheader->Recent == "N") $seen = false;
				if ($tmpheader->Flagged == "F") $star = true;
				if (get_mess($tmpheader->message_id, "archived") != 1) $allarchived = false;
				if (get_mess($tmpheader->message_id, "deleted") == 1) $del = true;
			}
			$header = $tmpheader = imap_headerinfo($mbox, $convos[$i][0]);
			if ( ( ((!$allarchived && $view=="inbox") || ($allarchived && $view=="arc") || ($star && $view=="star")) && !$del )
				|| ( $del && $view=="bin" )  ) {
					if ($seen) $class = "read";
					else $class = "unread";
					$messrows[] = "<tr class=\"$class\" id=\"mess$i\"><td width=\"3%\"><input type=\"checkbox\" id=\"tick$i\" name=\"check_$class\" onchange=\"javascript:hili($i,'$class')\"></td><td width=\"3%\">".starpic($star,$i)."</td><td width=\"30%\">".$header->fromaddress." (".sizeof($convos[$i]).")</td><td><a href=\"$me?do=message&convo=$i\" width=\"55%\">".nice_subject($header->subject)."</a></td><td width=\"15%\">".nice_date($header->udate)."</td></tr>\n";
					$count++;
			}
		}
		$convostart = $i;
		$listend = $liststart + $count;

		echo "<script language=\"javascript\" src=\"list.js\"></script>";
		echo "<script language=\"javascript\">imin=$convostart; imax=$convoend;</script>";
		echo "<table width=\"100%\" id=\"list\"><form name=\"form\">";
		echo "<tr class=\"header\"><td colspan=\"4\">".actions()."<br/>Select: <a href=\"javascript:selall()\">All</a>, <a href=\"javascript:selnone()\">None</a>, <a href=\"javascript:selread()\">Read</a>, <a href=\"javascript:selunread()\">Unread</a></td>";
		echo "<td>".($liststart+1)." - $listend of $total<br/>";
		if ($liststart > 0) echo "<a href=\"$me?do=list&view=$view&pos=".($liststart-$listlen)."\">&larr;Prev</a> ";
		if ($next) echo "<a href=\"$me?do=list&view=$view&pos=$listend\">Next&rarr;</a>";
		echo "</td></tr>";
		foreach ($messrows as $messrow) {
			echo $messrow;
		}
		echo "</form></table>";
		$_SESSION['convos'] = $convos;
		
	}	
}

echo "</div>";

imap_close($mbox);

} } ?>

<br/><br/><a href="http://freedomdreams.co.uk/wiki/AGPLMail">AGPLMail</a> is released under the <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">AGPL v3</a>. Care to see the <a href="source.php">Source Code</a>?

</body>
</html>
