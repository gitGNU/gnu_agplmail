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

function do_actions(value, tagname, range) {
		extra = "";
		if (value == "newtag") {
			tagname = prompt("Enter a name for the new tag:","newtag");
			value = "tag";
		}
		if (value == "tag" || value == "untag") {
			extra = "&name="+tagname;
		}
		if (tagname != null) {
			location.href = "index.php?do=listaction&type="+value+extra+"&range="+range;
		}
}

