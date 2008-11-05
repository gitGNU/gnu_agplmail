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
$msgno = $_POST["msgno"];
$mbox = @imap_open("{".$server."/imap/notls}".$folder, $user, $pass);
$header = imap_headerinfo($mbox,$msgno);
$body = imap_body($mbox, $msgno);
echo enewtext($header->reply_toaddress,"","",nice_re($header->subject),"On ".date("j F Y H:i",$header->udate).", ".$header->fromaddress." wrote:\n".indent($body));
$_SESSION["headers"] = "In-Reply-To: ".$header->message_id."\n";
die();
?>
