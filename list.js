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

function hili(num,base) {
	if (document.getElementById("tick"+num).checked == true) {
		document.getElementById("mess"+num).className = base+"_sel";
	}
	else {
		document.getElementById("mess"+num).className = base;
	}
}
function tick(chk,val) {
	if (chk) {
		if (chk.length) {
			for (i = 0; i < chk.length; i++) {
				chk[i].checked = val;
				chk[i].onchange();
			}
		}
		else {
			chk.checked = val;
			chk.onchange();
		}
	}
}
function selall() {
	tick(document.form.check_read,true);
	tick(document.form.check_unread,true);
}
function selnone() {
	tick(document.form.check_read,false);
	tick(document.form.check_unread,false);
}
function selread() {
	tick(document.form.check_read,true);
	tick(document.form.check_unread,false);
}
function selunread() {
	tick(document.form.check_unread,true);
	tick(document.form.check_read,false);
}
function moreact(value) {
	moreacts(value,"");
}
function moreacts(value,tagname) {
	range="";
	first=true;
	for (i in convoarr) {
		if (document.getElementById("tick"+convoarr[i])) {
			if (document.getElementById("tick"+convoarr[i]).checked) {
				if (first) {
					first = false;
				}
				else {
					range += ",";
				}
				range += convoarr[i];
			}
		}
		i++;
	}
	if (range == "") {
		alert("Please select one or more messages.");
	}
	else {
		do_actions(value, tagname, range);
	}
}
