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

toAC = function() {
    // Use an XHRDataSource
    var oDS = new YAHOO.util.XHRDataSource("ajax.php?do=contactac&");
    // Set the responseType
    oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
    // Define the schema of the delimited results
    oDS.responseSchema = {
        recordDelim: "\n",
        fieldDelim: "\t",
        fields: ["full","nice"]
    };
    // Enable caching
    oDS.maxCacheEntries = 5;

    // Instantiate the AutoComplete
    var oAC = new YAHOO.widget.AutoComplete("to", "tocont", oDS);
    oAC.queryQuestionMark = false;
    //oAC.useIFrame = true;
    oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
		return oResultData[1];
	};
    
    return {
        oDS: oDS,
        oAC: oAC
    };
}();

function messhtml() {	
    //Setup some private variables 
    var Dom = YAHOO.util.Dom, 
    Event = YAHOO.util.Event; 

    //The Editor config 
    var myConfig = { 
        height: '300px', 
        width: '600px', 
        animate: true, 
        dompath: true,
        handleSubmit: true 
    }; 
 
    //Now let's load the Editor.. 
    var myEditor = new YAHOO.widget.Editor('messe', myConfig); 
    myEditor.render(); 
}

