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
if (!$yuiloc) $yuiloc = "http://yui.yahooapis.com/2.6.0/";

########################### Attachments ###########################
if ($_GET['do'] == "att") {
	$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);
	$msgno = imap_msgno($mbox, $_GET['mess']);
	$struct = imap_fetchstructure($mbox,$msgno);
	$file = imap_fetchbody($mbox, $msgno, $_GET['part']);
	if ($_GET['enc'] == 3) $file = imap_base64($file);
	if ($_GET['type']) header("Content-type: ".$_GET['type']);
	if ($_GET['down']) header("Content-Disposition: attachment; filename=\"".$_GET['name']."\"");
	echo $file;
	die();
}
?>
<html>
<head>
<title>AGPLMail</title>
<link rel="stylesheet" type="text/css" href="default.css"></link>
<!--[if IE ]>
<link rel="stylesheet" type="text/css" href="default_ie.css"></link>
<![endif]-->
<script language="javascript" src="ajax.js"></script>
<script language="javascript" src="main.js"></script>
<script language="javascript" src="whizzywig.js"></script>

<!-- YUI Controls for Autocomplete -->
<link rel="stylesheet" type="text/css" href="<?php echo $yuiloc ?>build/assets/skins/sam/skin.css" />
<script type="text/javascript" src="<?php echo $yuiloc ?>build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/connection/connection-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/animation/animation-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/autocomplete/autocomplete-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/element/element-beta-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/container/container-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/menu/menu-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/button/button-min.js"></script>
<script type="text/javascript" src="<?php echo $yuiloc ?>build/editor/editor-min.js"></script>

</head>
<body class=" yui-skin-sam">

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

$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);

if (!$mbox) {
	session_destroy(); ?>
<h2>Sorry login failed</h2> <a href="<?php echo $me ?>">Try again</a>?
<?php }
else {

if ($_GET['do'] == "listaction" || $_GET['do'] == "messaction") {
	do_actions();
}

echo "<div id=\"intro\">Welcome ".get_setting("name").", it is ".date("H:i").". <a href=\"$me?do=logout\">Logout</a>?</div>";

echo "<div id=\"sidebar\">";
echo "<a href=\"$me?do=new\">New Email</a>";
echo "<h2>Folders</h2>\n";
?>
<a href="<?php echo $me ?>?do=list&pos=0&view=inbox">Inbox</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=arc">All Mail</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=star">Starred</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=sent">Sent</a><br/>
<a href="<?php echo $me ?>?do=list&pos=0&view=bin">Bin</a><br/>
<br/>
<a href="<?php echo $me ?>?do=contacts">Contacts</a><br/>
<br/>
<a href="<?php echo $me ?>?do=settings">Settings</a><br/>
<br/>
<h2>Tags</h2>
<?php
if ($result = mysql_query("SELECT DISTINCT name FROM `".$db_prefix."tags` WHERE account='$user'",$con)); else die(mysql_error());
while ($row=mysql_fetch_array($result)) {
	echo "<a href=\"?do=list&pos=0&view=tag&name=".$row["name"]."\">".$row["name"]."</a><br/>";
}


echo "</div><div id=\"main\">";

########################### Settings ###########################
if ($_GET['do'] == "settings") {
	if ($_POST['name']) add_setting("name",$_POST['name']);
	if ($_POST['listlen']) add_setting("listlen",$_POST['listlen']);
	if ($_POST['sig']) add_setting("sig",$_POST['sig']);
	add_setting("html",$_POST['html']);
	?>
<h2>Settings</h2>
<form method="post" action="<?php echo $me ?>?do=settings">
	Name: <input name="name" value="<?php echo get_setting("name"); ?>"></input><br/>
	Convos per page: <input name="listlen" value="<?php echo get_setting("listlen"); ?>"></input><br/>
	<?php if (get_setting("html") == "false") $pc = " checked"; else $hc = " checked";
	?> Message composition format: Plain:<input type="radio" name="html" value="false"<?php echo $pc; ?>> HTML:<input type="radio" name="html" value="true"<?php echo $hc; ?>><br/>
	Signature: <textarea name="sig" style="width: 50%; height: 100px;"><?php echo get_setting("sig"); ?></textarea>
	<button type="submit">Submit</button>
</form>
	<?php
}
########################### Contacts ###########################
elseif ($_GET['do'] == "contacts") {
	#print_r($_POST);
	if ($_POST['addr']) add_address($_POST['name'], $_POST['addr'], 2); ?>
	<h2>Contacts</h2>
	<form method="post" action="index.php?do=contacts">
	Add Contact - Name:<input name="name"/> Email Address:<input name="addr"/> <button type="submit">Add</button>
	</form><br />
<?php
	if ($result = mysql_query("SELECT * FROM `".$db_prefix."addressbook` WHERE account='$user' ORDER BY priority DESC, name")); else die(mysql_error());
	echo "<table><tr><td>Name</td><td>Email Address</td><td></td></tr>";
	while($row=mysql_fetch_array($result)) {
		if ($row['address'] != "" && $row['address'] == urldecode($_GET['addr'])) {
			echo "<tr><form method=\"post\" action=\"index.php?do=contacts\"><td><input name=\"name\"/ value=\"".$row['name']."\"></td><td><input name=\"addr\" value=\"".$row['address']."\"/></td><td><button type=\"submit\">Edit</button></td></from></tr>";
		} else {
			echo "<tr><td>".$row['name']."</td><td><a href=\"?do=new&to=".urlencode($row['name']." ".$row['address'])."\">".$row['address']."</a></td><td><a href=\"index.php?do=contacts&addr=".urlencode($row['address'])."\">edit</a></td></tr>";
		}
	}
	echo "</table>";
}
########################### Send Message ###########################
elseif ($_GET['do'] == "send") {
#	print_r($_POST);
#	if ($_SESSION['username'] == "demo") {
#		echo "<h2>This is a Demo</h2>Sorry, sending is disabled.";
#		$_SESSION["in_reply_to"] = "";
#	}
#	else {
		if (get_magic_quotes_gpc()) {
			$_POST = array_map('stripslashes', $_POST);
		}
		$part["type"] = TYPETEXT;
		if ($_POST["html"] == "true") {
			$part["subtype"] = "HTML";
			$html = 1;
		} else {
			$part["subtype"] = "PLAIN";
			$html = 0;
		}
		$part["description"] = "test";
		$part["contents.data"] = $_POST["content"];
		$envelope["message_id"] =  "<".md5(uniqid(microtime()))."@".$_SERVER["SERVER_NAME"].">";
		$envelope["from"] = get_setting("name")." <$user>";
		$envelope["cc"] = $_POST["cc"];
		$envelope["bcc"] = $_POST["bcc"];
		if ($_SESSION["in_reply_to"]) $envelope["in_reply_to"] = $_SESSION["in_reply_to"];
		$envelope["to"] = $_POST["to"];
		$envelope["subject"] = $_POST["subject"];
		$comp = imap_mail_compose($envelope, array($part));
		list($t_header,$t_body)=split("\r\n\r\n",$comp,2);
		imap_mail($_POST["to"], $_POST["subject"], $t_body, $t_header);
		
		if ($_GET['convo']) {
			$convo = $_GET['convo'];
			if ($result = mysql_query("SELECT nomsgs FROM `".$db_prefix."convos` WHERE id='$convo' AND account='$user'",$con)); else die(mysql_error());
			if ($row=mysql_fetch_array($result)) {
				$pos = $row['nomsgs']+1;
				if (mysql_query("UPDATE `".$db_prefix."convos` SET modified='".date("Y-m-d H:i:s")."', nomsgs=$pos, saved=1 WHERE account='$user' AND id='$convo'")); else die(mysql_error());
			} else die("This software is insane!");
		} else {
			$pos = 1;
		}
		if ($pos == 1) {
			$convo = get_setting("convotick");
			if (!$convo) $convo = 1;
			add_setting("convotick", $convo+1);
			if (mysql_query("INSERT INTO `".$db_prefix."convos` (account, modified, id, saved, archived, `read`) VALUES('$user', '".date("Y-m-d H:i:s")."', $convo, 1, 1, 1)")); else die(mysql_error());
		}
		$savedid = get_setting("savedtick");
		if (!$savedid) $savedid = 1;
		add_setting("savedtick", $savedid+1);
		if (mysql_query("INSERT INTO `".$db_prefix."mess` (account, uid, messid, pos, convo, date, saved) VALUES('$user', 'S$savedid', '".$envelope["message_id"]."', $pos, $convo, '".date("j F Y H:i")."', 1)", $con)); else die(mysql_error());
		if (mysql_query("INSERT INTO `".$db_prefix."saved` (account, id, headers, body, html, date) VALUES('$user', 'S$savedid', '".mysql_real_escape_string($t_header)."', '".mysql_real_escape_string($_POST["content"])."', $html, '".date("Y-m-d H:i:s")."')", $con)); else die(mysql_error());
		
		$header = imap_rfc822_parse_headers($t_header);
		foreach ($header->to as $item) {
			add_address(decode_qprint($item->personal), $item->mailbox."@".$item->host, 1);
		}
		$_SESSION["in_reply_to"] = "";
?>
<h2>Message Sent</h2>
<a href="<?php echo $me ?>">Return to inbox</a>?
<?php } #}
########################### New Message ###########################
elseif ($_GET['do'] == "new") {
	$to = "";
	if ($_GET['to']) $to = $_GET['to']; 
	echo "<h2>New Email</h2>";
	echo enewtext($to,"","","","");
}
########################### View Message ###########################
elseif ($_GET['do'] == "message") {
	if ($_GET['range']) {
		$convo = $_GET['range'];
	} else {
		$convo = $_GET['convo'];
	}
	if ($_GET['expand']) {
		if ($_GET['expand'] == "all") $exall = 1;
		expand_mess($_GET['expand'],1);
	}
	if ($_GET['collapse']) {
		if ($_GET['collapse'] == "all") $exall = 0;
		expand_mess($_GET['collapse'],0);
	}
?>
<script>
function moreact(value) {
	do_actions(value, "", "<?php echo $convo ?>")
}
function moreacts(vaule,tagname) {
	do_actions(value, tagname, "<?php echo $convo ?>");
}
</script>	
<?php
	echo " <div style=\"float: right\"><a href=\"?do=message&convo=$convo&expand=all\">Expand All</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?do=message&convo=$convo&collapse=all\">Collapse All</a></div>";
	echo "<a href=\"$me?do=list\">&laquo; Back to ".nice_view($view)."</a> ".actions()."<br />";
	if ($result = mysql_query("SELECT uid,saved,expanded FROM `".$db_prefix."mess` WHERE convo=$convo AND account='$user' ORDER BY pos",$con)); else die(mysql_error());
	$first = true;
	$last = false;
	$oldrow = mysql_fetch_assoc($result);
	function get_row() {
		global $row; global $result; global $oldrow;  global $last; global $first;
		$row = $oldrow;
		if ($oldrow = mysql_fetch_assoc($result)) {
		} else {
			$last = true;
		}
		if ($first);
		return true;
	}
	while (get_row()) {
		if ($first) {
			echo "<h2>".$header->subject."</h2>";
			$first = false;
		}
		if ($exall !== NULL) expand_mess($row['uid'],$exall);
		
		$uid = $row['uid'];
		$msgno = imap_msgno($mbox,$row['uid']);
		if ($row['saved'] == 1) {
			if ($result2 = mysql_query("SELECT * FROM `".$db_prefix."saved` WHERE id='".$row['uid']."' AND account='$user'",$con)); else die(mysql_error());
			if ($row2 = mysql_fetch_assoc($result2)) {
				$header = imap_rfc822_parse_headers($row2['headers']);
				$body = $row2['body'];
				$timestamp = strtotime($row2['date']);
			}
		} else {
			$header = imap_headerinfo($mbox,$msgno);
			#print_r($header);
			$timestamp = $header->udate;
			$unseen = $header->Unseen;
		}
		
		if (( $unseen == "U" || $row['expanded'] || ($last && $row['expanded'] !== '0') ) && $exall !== 0 || $exall == 1) {	
			if ($row['saved'] != 1) {
				$body = "";
				$struct = imap_fetchstructure($mbox,$msgno);
				$sect = array();
				$avail = array();
				$enc = array();
				$charset = array();
				$count = array();
				$att = array();
				$emo = array();
				partloop(array($struct),0);	
			
				if ($avail["HTML"]) $mode = "HTML";
				else $mode = "PLAIN";
				$wantedpart = $sect[$mode];
				if (!$wantedpart) $body = imap_body($mbox, $msgno);
				else {
					$body = imap_fetchbody($mbox, $msgno, $wantedpart);
					if ($enc[$mode] == 3) $body = imap_base64($body);
					if ($charset[$mode]) $body = iconv($charset[$mode],"UTF-8",$body);
					if ($mode == "HTML") {
						foreach($emo as $id => $src) {
							$body = str_replace("cid:".$id, $src, $body);
						}
					}
					if ($mode == "PLAIN") $body = nice_plain($body);
				}
				if (sizeof($att) > 0) $body .= "<br/><br/><h3>Attatchments</h3>";
				foreach ($att as $anatt) {
					if ($anatt['type'] == 5) $body .= "<img src=\"".$anatt['link']."\"/><br/>";
					$body.= "<a href=\"".$anatt['link']."&down=1&name=".$anatt['name']."\">".$anatt['name']."</a><br/><br/>";
				}
				#$body .= "<br/><br/><br/>".nl2br(htmlspecialchars(imap_body($mbox, $msgno)));
			}
			
			echo "<div class=\"emess\" id=\"mess".$row['uid']."\">";
			echo "<div class=\"etitle\"><a href=\"?do=message&convo=$convo&collapse=".$row['uid']."#mess".$row['uid']."\">".nice_list_from($header->from)."</a></div>";
			echo "<div class=\"ehead\">From: ".nice_addr_list($header->from)."<br/>";
			if ($header->to) echo "To: ".nice_addr_list($header->to)."<br/>";
			if ($header->cc) echo "CC: ".nice_addr_list($header->cc)."<br/>";
			echo "Date: ".date("j F Y H:i",$timestamp)."<br/>";
			echo "Subject: ".decode_qprint($header->subject)."</div><br/>";
			#print_r($struct);
			echo "<div class=\"econ\">".$body."</div>"; ?>
<br/><div class="efoot"><a href="index.php?do=message&convo=<?php echo $convo."&reply=".$row['uid']; ?>#esend">Reply</a> Reply to All Forward</div><?php 
	if ($_GET['reply'] == $row['uid']) {
		echo "<div id=\"esend\">".enewtext($header->reply_toaddress,"","",nice_re($header->subject),"On ".date("j F Y H:i",$header->udate).", ".$header->fromaddress." wrote:\n".indent($body),"&convo=$convo")."</div>";
		$_SESSION["in_reply_to"] = $header->message_id;
	}imap_rfc822_parse_headers
?></div>
	<?php
		}
		else {
			echo "<div class=\"etitle\"><a href=\"?do=message&convo=$convo&expand=".$row['uid']."#mess".$row['uid']."\">".nice_addr_list($header->from)."</a></div>";
		}	
		if ($last) break;
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
			foreach ($header->from as $item) {
				add_address(decode_qprint($item->personal), $item->mailbox."@".$item->host, 0);
			}
			if ($header->in_reply_to) {
				if ($result = mysql_query("SELECT convo FROM `".$db_prefix."mess` WHERE messid='".mysql_real_escape_string($header->in_reply_to)."' AND account='$user'",$con)); else die(mysql_error());
				if ($row=mysql_fetch_array($result)) {
					$convoid = $row['convo'];
					if ($result = mysql_query("SELECT nomsgs FROM `".$db_prefix."convos` WHERE id='$convoid' AND account='$user'",$con)); else die(mysql_error());
					if ($row=mysql_fetch_array($result)) {
						$pos = $row['nomsgs']+1;
						if (mysql_query("UPDATE `".$db_prefix."convos` SET modified='".date("Y-m-d H:i:s", $header->udate)."', nomsgs=$pos, `read`=0, archived=0 WHERE account='$user' AND id='$convoid'")); else die(mysql_error());
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
	} elseif ($view == "sent") {
	    $cond = "saved=1";
	} elseif ($view == "bin") {
	    $cond = "deleted=1";
	} elseif ($view == "star") {
	    $cond = "starred=1 AND deleted=0";
	} else {
	    $cond = "archived=0 AND deleted=0";
	}
	
	if ($view == "tag") {
		if ($_GET['name']) $_SESSION['name'] = $_GET['name'];
		$tagname = $_SESSION['name'];
		if ($result = mysql_query("SELECT COUNT(distinct convo) FROM `".$db_prefix."tags` WHERE name='$tagname' AND account='$user'",$con)); else die(mysql_error());
		$sel = "distinct convo";
	} else {	
		if ($result = mysql_query("SELECT COUNT(*) FROM `".$db_prefix."convos` WHERE ".$cond." AND account='$user'",$con)); else die(mysql_error());
		$sel = "*";
	}
	if ($row = mysql_fetch_array($result)) {
		$total = $row["COUNT($sel)"];
	}

	$listend = $liststart + $listlen;
	$next = true;
	if ($listend > $total) {
	    $listend = $total;
	    $next = false;
	}
	
	if ($view == "tag") {
		if ($result = mysql_query("SELECT DISTINCT * FROM `".$db_prefix."tags` WHERE name='$tagname' AND account='$user' ORDER BY convo DESC",$con)); else die(mysql_error());
		$convotitle = "convo";
	} else {
		if ($result = mysql_query("SELECT * FROM `".$db_prefix."convos` WHERE ".$cond." AND account='$user' ORDER BY modified DESC",$con)); else die(mysql_error());
		$convotitle = "id";
	}
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
			$jarray .= $row[$convotitle];
			if ($result2 = mysql_query("SELECT * FROM `".$db_prefix."mess` WHERE convo=".$row[$convotitle]." AND pos=1 AND account='$user'",$con)); else die(mysql_error());
			while ($row2 = mysql_fetch_assoc($result2)) {
				if ($row2['saved'] == 1) {
					if ($result5 = mysql_query("SELECT * FROM `".$db_prefix."saved` WHERE id='".$row2['uid']."' AND account='$user'",$con)); else die(mysql_error());
					if ($row5 = mysql_fetch_assoc($result5)) {
						$header = imap_rfc822_parse_headers($row5['headers']);
						print_r($row5['header']);
					}
				} else {
					$header = imap_headerinfo($mbox, imap_msgno($mbox, $row2['uid']));
				}
				$i = $row[$convotitle];
				$star = $row['starred'];
				$tagtext = "";
				if ($row['archived']==0 && $view!="inbox" && $view!="bin") $tagtext .= "<span class=\"inboxtag\">INBOX</span>";
				if ($result3 = mysql_query("SELECT DISTINCT name FROM `".$db_prefix."tags` WHERE account='$user' AND convo='$i'",$con)); else die(mysql_error());
				while ($row3=mysql_fetch_array($result3)) {
					$tagtext .= " <span class=\"normaltag\">".$row3["name"]."</span>";
				}			
				$jlink = "onclick=\"location.href='$me?do=message&convo=$i'\" onmouseover=\"document.body.style.cursor='pointer'\" onmouseout=\"document.body.style.cursor='auto'\"";
				if ($view == "tag") {
					if ($result4 = mysql_query("SELECT * FROM `".$db_prefix."convos` WHERE id=".$row[$convotitle]." AND account='$user'",$con)); else die(mysql_error());
					if ($row4 = mysql_fetch_assoc($result4)) {
						$nomsgs = $row4['nomsgs'];
						$date = nice_date(strtotime($row4['modified']));
						$isread = $row4['read'];
					}
				} else {
					$nomsgs = $row['nomsgs'];
					$date = nice_date(strtotime($row['modified']));
					$isread = $row['read'];
				}
				if ($isread) $class = "read";
				else $class = "unread";
				$messrows[] = "<tr class=\"$class\" id=\"mess$i\"><td width=\"3%\"><input type=\"checkbox\" id=\"tick$i\" name=\"check_$class\" onchange=\"javascript:hili($i,'$class')\"></td><td width=\"3%\">".starpic($star,$i)."</td><td width=\"30%\" $jlink>".nice_list_from($header->from)." ($nomsgs)</td><td colspan=\"2\" $jlink>".$tagtext." "."<a href=\"$me?do=message&convo=$i\">".nice_subject($header->subject)."</a></td><td width=\"15%\" $jlink>$date</td></tr>\n";
			}
		}
		$count ++;
	}
	
	$navi = ($liststart+1)." - $listend of $total<br/>";
	if ($liststart > 0) $navi .= "<a href=\"$me?do=list&view=$view&pos=".($liststart-$listlen)."\">&larr;Prev</a> ";
	if ($next) $navi .= "<a href=\"$me?do=list&view=$view&pos=$listend\">Next&rarr;</a>";
	$actions = actions();
	$selecttools = "Select: <a href=\"javascript:selall()\">All</a>, <a href=\"javascript:selnone()\">None</a>, <a href=\"javascript:selread()\">Read</a>, <a href=\"javascript:selunread()\">Unread</a>";
	
	echo "<script language=\"javascript\" src=\"list.js\"></script>";
	echo "<script language=\"javascript\">convoarr = [$jarray]</script>";
	echo "<table width=\"100%\" id=\"list\"><form name=\"form\">";
	echo "<tr class=\"header\"><td colspan=\"4\">$actions<br/>$selecttools</td><td colspan=\"2\" align=\"right\">$navi</td></tr>\n";
	foreach ($messrows as $messrow) {
		echo $messrow;
	}
	echo "<tr class=\"header\"><td colspan=\"4\">$selecttools<br/>$actions</td><td colspan=\"2\" align=\"right\">$navi</td></tr></form></table>\n";
	$_SESSION['convos'] = $convos;
}

echo "</div>";

imap_close($mbox);
mysql_close($con);

} } ?>

<br/><br/><a href="http://freedomdreams.co.uk/wiki/AGPLMail">AGPLMail</a> is released under the <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">AGPL v3</a>. Care to see the <a href="source.php">source code</a>?

</body>
</html>
