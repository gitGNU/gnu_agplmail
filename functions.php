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
	elseif ($f == "sent") return "Sent";
	elseif ($f == "tag") {
		global $tagname;
		return "Tag: ".$tagname;
	}
	else return $f;
}
function nice_addr_list($list) {
	$strout = "";
	$first = true;
	foreach ($list as $item) {
		if ($first) $first = false;
		else $strout .= ", ";
		$strout .= decode_qprint($item->personal)." &lt;".$item->mailbox."@".$item->host."&gt;";
	}
	return $strout;
}
function nice_re($sub) {
	if (ereg('Re: .*',$sub))
		return $sub;
	else return "Re: ".$sub;
}
function nice_subject($sub) {
	if ($sub) return decode_qprint($sub);
	else return "(no subject)";
}
function nice_s($num) {
	if ($num == 1)
		return "";
	else
		return "s";
}
function nice_list_from($list) {
	$from = $list[0]->personal;
	if (!$from) $from = $list[0]->mailbox."@".$list[0]->host;
	return decode_qprint($from);
}
function nice_plain($text) {
	$split = preg_split("!([/\w\-\.\?\&\=\#]{3,}:/{2}[\w\.]{2,}[/\w\-\.\?\&\=\#]*)!e", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$out = "";
	foreach ($split as $key => $bit) {
		if ($key%2) {
			$out .= "<a href=\"$bit\">$bit</a>";
		} else {
			$out .= htmlspecialchars($bit);
		}
	}
	return nl2br($out);
}
function decode_qprint($text) {
	return htmlentities(imap_utf8($text),ENT_QUOTES,"UTF-8");
}

function indent($mess) {
	if (get_setting("html") == "false") {
		global $mode;
		if ($mode == "HTML") $mess = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", preg_replace("[\r\n]", "", $mess))); 
		return "> ".ereg_replace("\n","\n> ",$mess);
	} else {
		return "<blockquote>".$mess."</blockquote>";
	}
}
function enewtext($to, $cc, $bcc, $sub, $con, $extra="") {
	$html = get_setting("html");
	if ($html == "false") {
		$sig = "\n\n\n".get_setting("sig");
	} else {
		$sig = "<br /><br />".nice_plain(get_setting("sig"));
	}
	$text .= "<form method=\"post\" action=\"index.php?do=send$extra\" id=\"form\">
	<div class=\"messlabel\">To:</div><div id=\"toac\" class=\"messac\"><input id=\"to\" name=\"to\" value=\"$to\"/><div class=\"cont\" id=\"tocont\"></div></div>";
	$text .= "<div id=\"ccrow\"><div class=\"messlabel\">CC:</div><div id=\"ccac\" class=\"messac\"><input id=\"cc\" name=\"cc\" value=\"$cc\"/><div class=\"cont\" id=\"cccont\"></div></div></div>";
	$text .= "<div id=\"bccrow\"><div class=\"messlabel\">BCC:</div><div id=\"bccac\" class=\"messac\"><input id=\"bcc\" name=\"bcc\" value=\"$bcc\"/><div class=\"cont\" id=\"bcccont\"></div></div></div>";
	$text .= "Subject: <input id=\"subject\" name=\"subject\" value=\"$sub\"><br/>
	<textarea id=\"messe\" name=\"content\" style=\"width:100%; height:300px;\">".$con.$sig."</textarea><br/>";
	$text .= "<script language=\"javascript\" src=\"mess.js\"></script>";
	if ($html != "false") {
		$text .= "<script language=\"javascript\">messhtml();
		document.write('<input name=\"html\" value=\"true\" style=\"visibility: hidden; position:absolute;\"/>');</script>";
	}
	$text .= "<button type=\"submit\">Send</button></form>";
	return $text;
}
function actions() {
	global $view;
	// TODO making all this global is silly
	global $_GET;
	$atext = "";
	if ($view == "inbox")
		$atext .= "<button type=\"button\" onclick=\"javascript:moreact('arc')\">Archive</button>";
	elseif ($view == "arc")
		$atext .= "<button type=\"button\" onclick=\"javascript:moreact('unarc')\">Move to Inbox</button>";
	elseif ($view == "tag")
		$atext .= "<button type=\"button\" onclick=\"javascript:moreacts('untag','".$_GET['name']."')\">Remove tag</button>";
	if ($view == "bin")
		$atext .= " <button type=\"button\" onclick=\"javascript:moreact('realdel')\">Delete Forever</button> <button type=\"button\" onclick=\"javascript:moreact('undel')\">Restore</button>";
	else
		$atext .= " <button type=\"button\" onclick=\"javascript:moreact('del')\">Delete</button>";
	$atext .= " <select><option>More Actions</option>";
	if ($view == "arc") $atext .= "<option onclick=\"javascript:moreact('arc')\">&nbsp;&nbsp;Archive</option>";
	elseif ($view!="inbox" && $view!="bin") $atext .= "<option onclick=\"javascript:moreact('unarc')\">&nbsp;&nbsp;Move to Inbox</option><option onclick=\"javascript:moreact('arc')\">&nbsp;&nbsp;Archive</option>";
	$atext .= "<option onclick=\"javascript:moreact('read')\">&nbsp;&nbsp;Mark as Read</option><option onclick=\"javascript:moreact('unread')\">&nbsp;&nbsp;Mark as Unread</option><option onclick=\"javascript:moreact('star')\">&nbsp;&nbsp;Add star</option><option onclick=\"javascript:moreact('unstar')\">&nbsp;&nbsp;Remove star</option>";
	$atext .= "<option>Add Tag</option><option onclick=\"moreacts('newtag','')\">&nbsp;&nbsp;New Tag...</option>";
	global $con;
	global $db_prefix;
	global $user;
	if ($result = mysql_query("SELECT DISTINCT name FROM `".$db_prefix."tags` WHERE account='$user'",$con)); else die(mysql_error());
	while($row=mysql_fetch_array($result)) {
		$atext .= "<option onclick=\"moreacts('tag','".$row["name"]."')\">&nbsp;&nbsp;".$row["name"]."</option>";
	}
	$atext .= "</select> <a href=\"$self\">Refresh</a>";
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
	global $user;$atext .= " <button type=\"button\" onclick=\"javascript:moreact('del')\">Delete</button>";
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."settings` WHERE account='$user' AND name='$name'",$con)); else die(mysql_error());
	if ($row=mysql_fetch_array($result)) {
		return $row["value"];
	}
}
function starpic($star, $convo) {
	if ($star)
		return "<a href=\"$me?do=listaction&amp;type=unstar&amp;range=$convo\"><img src=\"star_fill.png\" alt=\"{*}\" style=\"border: 0\"/></a>";
	else
		return "<a href=\"$me?do=listaction&amp;type=star&amp;range=$convo\"><img src=\"star_nofill.png\" alt=\"{ }\" style=\"border: 0\"/></a>";
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
	global $con;
	global $db_prefix;
	global $user;
	global $notif;
	foreach ($selection as $convo) {
		if (mysql_query("UPDATE `".$db_prefix."convos` SET `$name`=$value WHERE account='$user' AND id='$convo'", $con)); else die(mysql_error());
	}
	$notif = sizeof($selection)." message".nice_s(sizeof($selection))." ".$text;
}

function tag($name,$selection) {
	if ($name) {
		global $con;
		global $db_prefix;
		global $user;
		foreach ($selection as $convo) {
			if (mysql_query("INSERT INTO `".$db_prefix."tags` (account, name, convo) VALUES('$user', '$name', '$convo')", $con)); else die(mysql_error());
		}
		$notif = sizeof($selection)." message".nice_s(sizeof($selection))." have been tagged ".$name.".";
	}
}

function untag($name,$selection) {
	if ($name) {
		global $con;
		global $db_prefix;
		global $user;
		foreach ($selection as $convo) {
			if (mysql_query("DELETE FROM `".$db_prefix."tags` WHERE name='$name' AND convo='$convo'", $con)); else die(mysql_error());
		}
		$notif = sizeof($selection)." message".nice_s(sizeof($selection))." have had the tag ".$name." removed.";
	}
}

function do_actions() {
	global $_SESSION;
	global $_GET;
	global $mbox;
	$convos = $_SESSION['convos'];
	$selection = split(",",$_GET['range']);	
	if ($_GET['type'] == "del") {
		do_action("deleted", 1 ,"sent to the bin.",$selection);
	} elseif ($_GET['type'] == "undel") {
		do_action("deleted", 0, "restored.",$selection);
	} elseif ($_GET['type'] == "arc") {
		do_action("archived", 1, "sent to archive",$selection);
	} elseif ($_GET['type'] == "unarc") {
		do_action("archived", 0, "returned to inbox",$selection);
	} elseif ($_GET['type'] == "tag") {
		tag($_GET['name'],$selection);
	} elseif ($_GET['type'] == "untag") {
		untag($_GET['name'],$selection);
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
		if ($_GET['type'] == "star" || $_GET['type'] == "unstar" || $_GET['type'] == "tag") {
			$_GET['do'] = "message";
		}
	}
}

function partname($count,$level) {
	for ($i=1; $i<$level; $i++) {
		$out .= $count[$i].".";
	}
	$out .= $count[$level];
	return $out;
}
function partloop($parts,$level) {
	global $sect;
	global $avail;
	global $enc;
	global $charset;
	global $count;
	if (!$parts) return true;
	foreach ($parts as $part) {
		$count[$level]++;
		if ($part->type == 0 && ($part->subtype == "HTML" || $part->subtype == "PLAIN")) {
			$sect[$part->subtype] = partname($count,$level);
			$avail[$part->subtype] = true;
			$enc[$part->subtype] = $part->encoding;
			if ($part->parameters) {
				foreach ($part->parameters as $par) {
					if ($par->attribute == "charset") $charset[$part->subtype] = $par->value;
				}
			}
		}
		elseif ($part->type > 2) {
		#http://freedomdreams.co.uk/agplmail/index.php?do=att&mess=145&part=2&enc=3&amp;type=image/jpeg&down=1&amp;name=me_cam.jpg
			global $mbox;
			global $msgno;
			global $uid;
			$file = imap_fetchbody($mbox, $msgno, partname($count,$level));
			if ($part->encoding == 3) $file = imap_base64($file);
			if ($part->subtype) $ext = strtolower($part->subtype);
			if ($part->id && $part->type == 5) {
				global $emo;
				$name = substr($part->id,1,-1);
				$emo[$name] = "?do=att&mess=$uid&part=2&enc=".$part->encoding."&amp;type=image/$ext";
			} else {
				if ($part->parameters) {
					foreach ($part->parameters as $par) {
						if ($par->attribute == "name") $name = $par->value;
					}
				}
				if ($part->type == 3) {
					$type = "application";
				} elseif ($part->type == 4) {
					$type = "audio";
				} elseif ($part->type == 5) {
					$type = "image";
				} elseif ($part->type == 6) {
					$type = "video";
				} elseif ($part->type == 7) {
					$type = "other";
				}
				if ($name) {
					global $att;
					$att[] = array('name'=>$name, 'link'=>"?do=att&mess=$uid&part=2&enc=".$part->encoding."&amp;type=$type/$ext", 'type'=>$part->type);
				}
			}
		}
		partloop($part->parts,$level+1);
	}
}

function add_address($name, $addr, $priority) {
	// Add a message to the adressbook
	// $priority is the priority, where:
	//		0 is recieved an email from
	//		1 is sent an email to
	//		2 is added to the adressbook manually
	global $con;
	global $db_prefix;
	global $user;
	if ($result = mysql_query("SELECT priority,name FROM `".$db_prefix."addressbook` WHERE account='$user' AND address='$addr'",$con)); else die(mysql_error());
	if ($row = mysql_fetch_array($result)) {
		if ($priority > $row['priority']) {
			if (mysql_query("UPDATE `".$db_prefix."addressbook` SET priority=$priority WHERE account='$user' AND address='$addr'", $con)); else die(mysql_error());
		}
		if ($row['name'] == "" || $priority >=  $row['priority']) {
			if (mysql_query("UPDATE `".$db_prefix."addressbook` SET name='$name' WHERE account='$user' AND address='$addr'", $con)); else die(mysql_error());
		}
	} else {
		if (mysql_query("INSERT INTO `".$db_prefix."addressbook` (account, name, address, priority) VALUES('$user', '$name','$addr', $priority)", $con)); else die(mysql_error());
	}
}

function expand_mess($uid,$value) {
	global $con;
	global $db_prefix;
	global $user;
	if (mysql_query("UPDATE `".$db_prefix."mess` SET expanded=$value WHERE account='$user' AND uid='$uid'", $con)); else die(mysql_error());	
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
elseif (sizeof($domain) == 1) $_SESSION['domain'] = $domain[0];
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
if ($view == "tag") {
	if ($_GET['name']) $_SESSION['name'] = $_GET['name'];
	$tagname = $_SESSION['name'];
}

$folder = "INBOX";
?>
