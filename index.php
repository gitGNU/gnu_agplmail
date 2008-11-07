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
<link rel="stylesheet" type="text/css" href="default.css"></link>
<script language="javascript" src="ajax.js"></script>
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
	User: <input name="username"></input>
<?php
if ($domain) {
	echo "<select name=\"domain\">";
	foreach ($domain as $dom) echo "<option value=\"$dom\">$dom</option>";
	echo "</select>";
} else echo "<input name=\"domain\"></input>";
?>
	<br/>
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
?>
<a href="<?php echo $me ?>?do=list&pos=0&view=inbox">Inbox</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=arc">All Mail</a><br/>
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
	if ($_POST['name']) add_setting("name",$_POST['name']);
	if ($_POST['listlen']) add_setting("listlen",$_POST['listlen']);
	if ($_POST['sig']) add_setting("sig",$_POST['sig']);
	?>
<h2>Settings</h2>
<form method="post" action="<?php echo $me ?>?do=settings">
	Name: <input name="name" value="<?php echo get_setting("name"); ?>"></input><br/>
	Convos per page: <input name="listlen" value="<?php echo get_setting("listlen"); ?>"></input><br/>
	Signature: <textarea name="sig"><?php echo get_setting("sig"); ?></textarea>
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
	} else {
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
	if ($result = mysql_query("SELECT uid FROM `".$db_prefix."mess` WHERE convo=$convo AND account='$user' ORDER BY pos",$con)); else die(mysql_error());
	$first = true;
	while ($row = mysql_fetch_assoc($result)) {
		if ($first) {
			echo "<h2>".$header->subject."</h2>";
			$first = false;
		}
		$msgno = imap_msgno($mbox,$row['uid']);
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
function reply<?php echo $msgno ?>() {
	ajax("msgno=<?php echo $msgno ?>", "esend<?php echo $msgno ?>", false);
}
</script>
<br/><div class="efoot"><a href="javascript:reply<?php echo $msgno ?>()">Reply</a> Reply to All Forward</div><div id="esend<?php echo $msgno ?>"></div></div>
	<?php
	}
	// Mark the conversation as read in the sql
	if (mysql_query("UPDATE `".$db_prefix."convos` SET `read`=1 WHERE account='$user' AND id='$convo'", $con)); else die(mysql_error());
}
########################### View Folder ###########################
else {
	echo "<h2>".nice_view($view)."</h2>\n";

	if ($notif) {
		echo "<div id=\"notif\">".$notif."</div>";
	}

	$lastdate = get_setting("lastdate");
	if ($lastdate != NULL) $datesearch = "SINCE \"$lastdate\"";
	$mess = imap_sort($mbox, SORTARRIVAL, 0, NULL, $datesearch); 
	$header = "";
	foreach ($mess as $msgno) {
		$header = imap_headerinfo($mbox, $msgno);
		if ($result = mysql_query("SELECT messid FROM `".$db_prefix."mess` WHERE messid='".mysql_real_escape_string($header->message_id)."' AND account='$user'",$con)); else die(mysql_error());
		if (!mysql_fetch_array($result)) {
			if ($header->in_reply_to) {
				if ($result = mysql_query("SELECT convo FROM `".$db_prefix."mess` WHERE messid='".mysql_real_escape_string($header->in_reply_to)."' AND account='$user'",$con)); else die(mysql_error());
				if ($row=mysql_fetch_array($result)) {
					$convoid = $row['convo'];
					if ($result = mysql_query("SELECT nomsgs FROM `".$db_prefix."convos` WHERE id='$convoid' AND account='$user'",$con)); else die(mysql_error());
					if ($row=mysql_fetch_array($result)) {
						$pos = $row['nomsgs']+1;
						if (mysql_query("UPDATE `".$db_prefix."convos` SET modified='".date("Y-m-d H:i:s", $header->udate)."', nomsgs=$pos WHERE account='$user' AND id='$convoid'")); else die(mysql_error());
					} else die("SQL database is insane!");
				}
				else {
					$pos = 1;
				}
			}
			else {
				$pos = 1;
			}
			if ($pos == 1) {
				$convoid = get_setting("convotick");
				if (!$convoid) $convoid = 1;
				add_setting("convotick", $convoid+1);
				if (mysql_query("INSERT INTO `".$db_prefix."convos` (account, modified, id) VALUES('$user', '".date("Y-m-d H:i:s", $header->udate)."', $convoid)")); else die(mysql_error());
			}
			if (mysql_query("INSERT INTO `".$db_prefix."mess` (account, uid, messid, pos, convo, date) VALUES('$user', '".imap_uid($mbox, $msgno)."', '".mysql_real_escape_string($header->message_id)."', $pos, $convoid, '".date("Y-m-d H:i:s", $header->udate)."')", $con)); else die(mysql_error());
		}
	}
	add_setting("lastdate", $header->date);
	
	$listlen = get_setting("listlen");
	if (!$listlen) $listlen = 50;
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
	
	if ($view == "arc") {
	    $cond = "deleted=0";
	} elseif ($view == "bin") {
	    $cond = "deleted=1";
	} elseif ($view == "star") {
	    $cond = "starred=1 AND deleted=0";
	} else {
	    $cond = "archived=0 AND deleted=0";
	}
	
	if ($result = mysql_query("SELECT COUNT(*) FROM `".$db_prefix."convos` WHERE ".$cond." AND account='$user'",$con)); else die(mysql_error());
	if ($row = mysql_fetch_array($result)) {
		$total = $row["COUNT(*)"];
	}
	$listend = $liststart + $listlen;
	$next = true;
	if ($listend > $total) {
	    $listend = $total;
	    $next = false;
	}
	
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."convos` WHERE ".$cond." AND account='$user' ORDER BY modified DESC",$con)); else die(mysql_error());
	$count = 1;
	$first = true;
	$messrows = array();
	while ($row = mysql_fetch_assoc($result)) {
	    if ($count > $listend) break;
		if ($count > $liststart) {
			if ($first) {
				$first = false;
			} else {
				$jarray .= ",";
			}
			$jarray .= $row['id'];
			if ($result2 = mysql_query("SELECT * FROM `".$db_prefix."mess` WHERE convo=".$row['id']." AND pos=1 AND account='$user'",$con)); else die(mysql_error());
			while ($row2 = mysql_fetch_assoc($result2)) {
				$header = imap_headerinfo($mbox, imap_msgno($mbox, $row2['uid']));
				if ($row['read']) $class = "read";
				else $class = "unread";
				$i = $row['id'];
				$star = $row['starred'];
				$tagtext = "";
				if ($row['archived']==0 && $view!="inbox" && $view!="bin") $tagtext = "<span class=\"inboxtag\">INBOX</span>";
				$messrows[] = "<tr class=\"$class\" id=\"mess$i\"><td width=\"3%\"><input type=\"checkbox\" id=\"tick$i\" name=\"check_$class\" onchange=\"javascript:hili($i,'$class')\"></td><td width=\"3%\">".starpic($star,$i)."</td><td width=\"30%\">".$header->fromaddress." (".$row['nomsgs'].")</td><td>".$tagtext." "."<a href=\"$me?do=message&convo=$i\" width=\"55%\">".nice_subject($header->subject)."</a></td><td width=\"15%\">".nice_date(strtotime($row['modified']))."</td></tr>\n";
			}
		}
		$count ++;
	}
	
	echo "<script language=\"javascript\" src=\"list.js\"></script>";
	echo "<script language=\"javascript\">convoarr = [$jarray]</script>";
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

echo "</div>";

imap_close($mbox);
mysql_close($con);

} } ?>

<br/><br/><a href="http://freedomdreams.co.uk/wiki/AGPLMail">AGPLMail</a> is released under the <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">AGPL v3</a>. Care to see the <a href="source.php">source code</a>?

</body>
</html>
