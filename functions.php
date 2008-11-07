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

function nice_date($indate) {
	return date("H:i j M",$indate);
}
function nice_view($f) {
	if ($f == "inbox") return "Inbox";
	elseif ($f == "arc") return "Archive";
	elseif ($f == "star") return "Starred";
	elseif ($f == "bin") return "Bin";
	else return $f;
}
function nice_addr_list($list) {
	$strout = "";
	$first = true;
	foreach ($list as $item) {
		if ($first) $first = false;
		else $strout .= ", ";
		$strout .= $item->personal." &lt;".$item->mailbox."@".$item->host."&gt;";
	}
	return $strout;
}
function nice_re($sub) {
	if (ereg('Re: .*',$sub))
		return $sub;
	else return "Re: ".$sub;
}
function nice_subject($sub) {
	if ($sub) return $sub;
	else return "(no subject)";
}
function nice_s($num) {
	if ($num == 1)
		return "";
	else
		return "s";
}
function indent($mess) {
	return "> ".ereg_replace("\n","\n> ",$mess);
}
function enewtext($to, $cc, $bcc, $sub, $con) {
	return "<form method=\"post\" action=\"$me?do=send\" id=\"form\">
	To: <input name=\"to\" value=\"$to\"/><br/>
	CC: <input name=\"cc\" value=\"$cc\"/><br/>
	BCC: <input name=\"bcc\" value=\"$bcc\"/><br/>
	Subject: <input name=\"subject\" value=\"$sub\"><br/>
	<textarea rows=\"20\" cols=\"60\" name=\"content\">".$con."\n\n\n".get_setting("sig")."</textarea><br/>
	<button type=\"submit\">Send<button>
</form>";
}
function actions() {
	global $view;
	if ($view == "inbox")
		$atext = "<button type=\"button\" onClick=\"javascript:moreact('arc')\">Archive</button>";
	elseif ($view == "arc")
		$atext = "<button type=\"button\" onClick=\"javascript:moreact('unarc')\">Move to Inbox</button>";
	if ($view == "bin")
		$atext .= " <button type=\"button\" onClick=\"javascript:moreact('realdel')\">Delete Forever</button> <button type=\"button\" onClick=\"javascript:moreact('undel')\">Restore</button>";
	else
		$atext .= " <button type=\"button\" onClick=\"javascript:moreact('del')\">Delete</button>";
	$atext .= "<select><option>More Actions</option>";
	if ($view == "arc") $atext .= "<option onClick=\"javascript:moreact('arc')\">Archive</option>";
	elseif ($view!="inbox" && $view!="bin") $atext .= "<option onClick=\"javascript:moreact('unarc')\">Move to Inbox</option><option onClick=\"javascript:moreact('arc')\">Archive</option>";
	$atext .= "<option onClick=\"javascript:moreact('read')\">Mark as Read</option><option onClick=\"javascript:moreact('unread')\">Mark as Unread</option><option onClick=\"javascript:moreact('star')\">Add star</option><option onClick=\"javascript:moreact('unstar')\">Remove star</option></select> <a href=\"$self\">Refresh</a>";
	return $atext;
}
function add_setting($name, $value) {
	global $con;
	global $db_prefix;
	global $user;
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."settings` WHERE account='$user' AND name='$name'",$con)); else die(mysql_error());
	if (mysql_fetch_array($result)) {
		if (mysql_query("UPDATE `".$db_prefix."settings` SET value='$value' WHERE account='$user' AND name='$name'", $con)); else die(mysql_error());
	}
	else {
		if (mysql_query("INSERT INTO `".$db_prefix."settings` (account, name, value) VALUES('$user', '$name', '$value')", $con)); else die(mysql_error());
	}
}
function get_setting($name) {
	global $con;
	global $db_prefix;
	global $user;$atext .= " <button type=\"button\" onClick=\"javascript:moreact('del')\">Delete</button>";
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."settings` WHERE account='$user' AND name='$name'",$con)); else die(mysql_error());
	if ($row=mysql_fetch_array($result)) {
		return $row["value"];
	}
}
function starpic($star, $convo) {
	if ($star)
		return "<a href=\"$me?do=listaction&type=unstar&range=$convo\"><img src=\"star_fill.png\" alt=\"{*}\" border=\"0\"/></a>";
	else
		return "<a href=\"$me?do=listaction&type=star&range=$convo\"><img src=\"star_nofill.png\" alt=\"{ }\" border=\"0\"/></a>";
}

function get_mess($messid, $name) {
	global $con;
	global $db_prefix;
	global $user;
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."mess` WHERE account='$user' AND messid='$messid'",$con)); else die(mysql_error());
	if ($row=mysql_fetch_array($result)) {
		return $row[$name];
	}
}

function count_mess($cond) {
	global $con;
	global $db_prefix;
	global $user;
	if ($result = mysql_query("SELECT COUNT(DISTINCT messid) FROM `".$db_prefix."mess` WHERE account='$user' AND ".$cond,$con)); else die(mysql_error());
	if ($row = mysql_fetch_array($result)) {
		return $row["COUNT(DISTINCT messid)"];
	}
}

function do_action($name,$value,$text,$selection) {
	global $convos;
	global $mbox;
	global $con;
	global $db_prefix;
	global $user;
	foreach ($selection as $convo) {
		if (mysql_query("UPDATE `".$db_prefix."convos` SET `$name`=$value WHERE account='$user' AND id='$convo'", $con)); else die(mysql_error());
	}
	$notif = sizeof($selection)." message".nice_s(sizeof($selection))." ".$text;
}


function do_actions() {
	global $_SESSION;
	global $_GET;
	global $mbox;
	$convos = $_SESSION['convos'];
	$selection = split(",",$_GET['range']);
	if ($_GET['type'] == "del")
		do_action("deleted", 1 ,"sent to the bin.",$selection);
	elseif ($_GET['type'] == "undel")
		do_action("deleted", 0, "restored.",$selection);
	elseif ($_GET['type'] == "arc") {
		do_action("archived", 1, "sent to archive",$selection);
	}
	elseif ($_GET['type'] == "unarc") {
		do_action("archived", 0, "returned to inbox",$selection);
	}
	else {
		global $con;
		global $db_prefix;
		global $user;
		$msglist = "";
		$first = true;
		foreach ($selection as $convo) {
			$firstonly = "";
			if ($_GET['type'] == "star" || $_GET['type'] == "unstar") $firstonly = "AND pos=1";
			if ($result = mysql_query("SELECT uid FROM `".$db_prefix."mess` WHERE convo=$convo AND account='$user'".$firstonly,$con)); else die(mysql_error());
			while ($row = mysql_fetch_assoc($result)) {
				if ($_GET['type'] == "realdel") {
					imap_delete($mbox,imap_msgno($mbox,$row['uid']));
				}
				else {
					if ($first) $first = false;
					else $msglist .= ",";
					$msglist .= imap_msgno($mbox,$row['uid']);
				}
			}
			if ($_GET['type'] == "realdel") {
				if ($result = mysql_query("DELETE FROM `".$db_prefix."convos` WHERE id=$convo AND account='$user'",$con));
				if ($result = mysql_query("DELETE FROM `".$db_prefix."mess` WHERE convo=$convo AND account='$user'",$con)); else die(mysql_error());
			}
		}
		if ($_GET['type'] == "read") {
			imap_setflag_full($mbox,$msglist,"\\Seen");
			do_action("read", 1, "marked as read",$selection);
		}
		elseif ($_GET['type'] == "unread") {
			imap_clearflag_full($mbox,$msglist,"\\Seen");
			do_action("read", 0, "marked as unread",$selection);
		}
		elseif ($_GET['type'] == "star") {
			imap_setflag_full($mbox,$msglist,"\\Flagged");
			do_action("starred", 1, "starred",$selection);
		}
		elseif ($_GET['type'] == "unstar") {
			imap_clearflag_full($mbox,$msglist,"\\Flagged");
			do_action("starred", 0, "unstarred",$selection);
		}
		elseif ($_GET['type'] == "realdel") {
			imap_expunge($mbox);
			$notif = sizeof($selection)." message".nice_s(sizeof($selection))." deleted FOREVER.";
		}
	}
	
	if ($_GET['do'] == "messaction") {
		if ($_GET['type'] == "star" || $_GET['type'] == "unstar") {
			$_GET['do'] = "message";
		}
	/*	if ($_GET['type'] == "arc") {
			$view = "arc";
			$_SESSION['view'] = "arc";
		}
		if ($_GET['type'] == "unarc") {
			$view = "inbox";
			$_SESSION['view'] = "inbox";
		} */
	}
}

$con = mysql_connect($db_host,$db_name,$db_pass);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
if (mysql_select_db($db_db, $con)); else die(mysql_error()); 

session_start();
if ($_POST['username']) {
	$_SESSION['username'] = $_POST['username'];
	$_SESSION['password'] = $_POST['password'];
}
if ($_POST['domain']) $_SESSION['domain'] = $_POST['domain'];
$user = $_SESSION['username'].$_SESSION['domain'];
$uname = $_SESSION['username'];
$pass = $_SESSION['password'];

$view = $_GET['view'];
if ($view) $_SESSION['view'] = $view;
else {
	$view = $_SESSION['view'];
	if (!$view) {
		$view = "inbox";
		$_SESSION['view'] = "inbox";
	}
}

$folder = "INBOX";
$convos = $_SESSION['convos'];
?>
