// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.


var tmpid = null;
var calling = false;
if (jitsi === undefined) {
	// True and false because it is derived from a PHP variable
	jitsi = 'true';
}

var mesapn = [];
var oldmesapn = [];
var unread = 0;
var oldmessage = 0;

function opensettings() {
	$("#settings").css({"visibility": "visible"});
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}


function swapSheet(sheet) {
	document.getElementById("stylesheeta").setAttribute("href", sheet);
}

if (getCookie("starchattheme") !== "") {
	swapSheet(getCookie("starchattheme"));
}

var username = getCookie("usernamedata");
var token = getCookie("stoken");

function themeChange() {
	setCookie("starchattheme", "../themes/"+document.getElementById("theme").value+".css", 365);
	swapSheet(getCookie("starchattheme"));
}

function startcall() {
	if (calling === true) {
		endcall();
	}else{
		$("#callform").html("<button id='closeb' onclick='endcall()'>Close (end call)</button><iframe src='https://meet.jit.si/"+tmpid+"' allow='microphone; camera'>");
		$("#callform").css({"visibility": "visible"});
		calling = true;
	}
}

function endcall() {
	$("#callform").html("");
	$("#callform").css({"visibility": "hidden"});
	calling = false;
}

function closesettings() {
	$("#settings").css({"visibility": "hidden"});
}

function checkkey(event) {
	if (event.key == "Enter") {
		sendmessage();
	}
}

function httpGet(theUrl, callback) {
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.onreadystatechange = function() {
		if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
			callback(xmlHttp.responseText);
	}
	xmlHttp.open("GET", theUrl, true); // true for asynchronous, we need async to keep everything working
	xmlHttp.send(null);
}

function splitcontacts() {
	$.ajax({
		url: "../api/v1/", 
		type: 'GET',
		data: {
			"token": token,
			"getcontacts": "yes"
		},
		dataType: 'json',
		success: function(contacts) {
			for (var x = 0; x <= contacts.length; x++) {
				$("#contacts").append("<div class='box' onclick='switchcontact(\""+contacts[x][1]+"\")'><img src='../img/user.png' class='pfp'><div class='info'>"+contacts[x][0]+"</div></div>");
			}
		}
	});
}

splitcontacts(); // the function is defined, we run the code, just once for now

function switchcontact(vals) {
	if (tmpid == null) {
		if (jitsi == 'true') {
			$("#topbar").append("<img src='../img/call.png' id='call' onclick='startcall()'>");
		}
	}
	tmpid = vals;
}

function meshan(x, contacts) {
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'readmessages': contacts[x][1],
			'count': 5
		},
		success: function(data) {
			mesapn[x] = data;
			if (document.hasFocus() == false) {
				if (oldmesapn[x] != mesapn[x]) {
					var audio = new Audio('../sounds/notification.wav');
					audio.play();
					unread += 1;
					document.title = "Starchat("+unread+"+)";
				}
			}
			oldmesapn[x] = data;
		}
	});
}

if (mesapn == []) {
	// TODO here
	$.ajax("../api/v1/?token="+token+"&getcontacts=yes", function(data) {
		var contacts = JSON.parse(data);
		for (var x = 0; x <= contacts.length-1; x++) {
			meshan(x, contacts);
		}
	});
	oldmesapn = [];
}

setInterval(function() {
	if (tmpid != null) {
		httpGet("../api/v1/?token="+token+"&readmessages="+tmpid+"&count=25", function(resu) {
			var les = JSON.parse(resu);
			document.getElementById("messboxsmall").innerHTML = "";
			var opres = 0;
			for (var x = 0; x < les.length; x++) {
				if (les[x].username == username) {
					opres = 0;
				}
			}
		}
		document.getElementById("messboxsmall").innerHTML += "<div style='clear:both;color:#ffffff;padding:5px;display:block;'><div class='smessage'>"+les[x].message+"</div></div>"; // In 0.8 we will include datetime float datetime to right
	}else{
		if (opres == 0) {
			document.getElementById("messboxsmall").innerHTML += "<div style='clear:both;padding:5px;display:block;'><div class='susername'>"+les[x].username+"</div><div class='sopmessage'>"+les[x].message+"</div></div>"; // In 0.8 we will include datetime float datetime to right
			opres = les[x].username;
		}else{
			if (opres == les[x].username) {
				document.getElementById("messboxsmall").innerHTML += "<div style='clear:both;padding:5px;display:block;'><div class='sopmessage'>"+les[x].message+"</div></div>"; // In 0.8 we will include datetime float datetime to right
			}else{
			document.getElementById("messboxsmall").innerHTML += "<div style='clear:both;padding:5px;display:block;'><div class='susername'>"+les[x].username+"</div><div class='sopmessage'>"+les[x].message+"</div></div>"; // In 0.8 we will include datetime float datetime to right
					opres = les[x].username;
			}
		}
	}
}

// scroll to bottom
if (oldmessage != resu) {
	var objDiv = document.getElementById("messboxsmall");
	objDiv.scrollTop = objDiv.scrollHeight;
}
oldmessage = resu;
});
	}

	httpGet("../api/v1/?token="+token+"&getcontacts=yes", function(data) {
		if (unread == 0) {
			var contacts = JSON.parse(data);
			for (var x = 0; x <= contacts.length-1; x++) {
				meshan(x, contacts);
			}
		}
	});

	if (document.hasFocus()) {
		unread = 0;
		document.title = "Starchat";
	}

},2000)

function sendmessage() {
	var usermessage = document.getElementById("chatbox").value;
	usermessage = encodeURIComponent(usermessage);
	httpGet("../api/v1/?token="+token+"&sendmessage="+usermessage+"&sendmessageto="+tmpid, function() {
		document.getElementById("chatbox").value = "";
		unread = 0;
		prevmsg = false;
		httpGet("../api/v1/?token="+token+"&getcontacts=yes", function(data) {
			var contacts = JSON.parse(data);
			for (var x = 0; x <= contacts.length-1; x++) {
//console.log(contacts[x][1]);
meshan(x, contacts);
}
});
	});
}

function addacontact() {
	var toadd = encodeURIComponent(prompt("Please Enter Username to Add"));
	httpGet("../api/v1/?token="+token+"&addcontact="+toadd, function(code) {
		location.reload()
	});
}

function addbcontact() {
	if (tmpid == null) {
		alert("Please actually select a contact");
	}else{
		var toadd = prompt("Please Enter Username to Add");
		httpGet("../api/v1/?token="+token+"&addtoconvo="+toadd+"&convoid="+tmpid, function(code) {
			if (code != "\n0") {
				alert("User not found or the conversation you are in does not exist (you should not get the second reason on the web client)");
			}
		});
	}
}