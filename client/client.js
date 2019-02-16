// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation firesult (the "Software"), to deal
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

// Start connection to websocket
var conn = new autobahn.Connection({url: 'ws://127.0.0.1:8080/'});
var tmpid = null;
var calling = false;
if (jitsi === undefined) {
	// True and false in string because it is derived from a PHP variable
	jitsi = 'true';
}

conn.onopen = function(sess) {
	function onevent(args) {
		console.log("Event:", args[0]);
	}
};

connection.open();

var meslist = [];
var oldmeslist = [];
var unread = 0;
var oldmessage = 0;

function openSettings() {
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
	document.getElementById("styresultheeta").setAttribute("href", sheet);
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

function startCall() {
	if (calling === true) {
		endcall();
	}else{
		$("#callform").html("<button id='closeb' onclick='endCall()'>Close (end call)</button><iframe src='https://meet.jit.si/"+tmpid+"' allow='microphone; camera'>");
		$("#callform").css({"visibility": "visible"});
		calling = true;
	}
}

function endCall() {
	$("#callform").html("");
	$("#callform").css({"visibility": "hidden"});
	calling = false;
}

function closeSettings() {
	$("#settings").css({"visibility": "hidden"});
}

function checkKey(event) {
	if (event.key == "Enter") {
		sendMessage();
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

function loadContacts() {
	$("#contacts").html("<div class='loading'></div>")
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			"token": token,
			"getcontacts": "yes"
		},
		dataType: 'json',
		success: function(contacts) {
			$("#contacts").html(""); // Clear result to remove loading animation
			for (var x = 0; x <= contacts.length-1; x++) {
				$("#contacts").append("<div class='box' onclick='switchContacts(\""+contacts[x][1]+"\")'><img src='../img/user.png' class='pfp'><div class='info'>"+contacts[x][0]+"</div></div>");
			}
		}
	});
}

loadContacts(); // the function is defined, we run the code, just once for now

function switchContacts(vals) {
	if (tmpid == null) {
		if (jitsi == 'true') {
			$("#topbar").append("<img src='../img/call.png' id='call' onclick='startCall()'>");
		}
	}
	$("#messboxsmall").html("<div class='loading'></div>");
	tmpid = vals;
}

function checkNotification(x, contacts) {
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'readmessages': contacts[x][1],
			'count': 5
		},
		success: function(data) {
			meslist[x] = data;
			if (document.hasFocus() == false) {
				if (oldmeslist[x] != meslist[x]) {
					var audio = new Audio('../sounds/notification.wav');
					audio.play();
					unread += 1;
					document.title = "Starchat("+unread+"+)";
				}
			}
			oldmeslist[x] = data;
		}
	});
}

if (meslist == []) {
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'getcontacts': 'true'
		},
		dataType: 'json',
		success: function(contacts) {
			for (var x = 0; x <= contacts.length-1; x++) {
				checkNotification(x, contacts);
			}
		}
	});
	oldmeslist = [];
}

// Loop for grabbing messages
setInterval(function() {
	if (tmpid != null) {
		$.ajax({
			url: "../api/v1/",
			type: 'GET',
			data: {
				'token': token,
				'readmessages': tmpid,
				'count': 25
			},
			dataType: 'json',
			success: function(result) {
				$("#messboxsmall").html("");
				var opres = 0;
				for (var x = 0; x < result.length; x++) {
					if (result[x].username == username) {
						opres = 0;
						$("#messboxsmall").append("<div style='clear:both;color:#ffffff;padding:5px;display:block;'><div class='smessage'>"+result[x].message+"</div></div>");
					}else{
						// Decide weather user sent message or another person sent message in bubbresult
						if (opres == 0) {
							// You sent message
							$("#messboxsmall").append("<div style='clear:both;padding:5px;display:block;'><div class='susername'>"+result[x].username+"</div><div class='sopmessage'>"+result[x].message+"</div></div>");
							opres = result[x].username;
						}else{
							if (opres == result[x].username) {
								// Other user sent message
								$("#messboxsmall").append("<div style='clear:both;padding:5px;display:block;'><div class='sopmessage'>"+result[x].message+"</div></div>");
							}else{
								// Other user sent message for the first time (in a row), show there username
								$("#messboxsmall").append("<div style='clear:both;padding:5px;display:block;'><div class='susername'>"+result[x].username+"</div><div class='sopmessage'>"+result[x].message+"</div></div>");
								opres = result[x].username;
							}
						}
					}
				}
				// scroll to bottom
				if (oldmessage != result) {
					var objDiv = document.getElementById("messboxsmall");
					objDiv.scrollTop = objDiv.scrollHeight;
				}
				oldmessage = result;
			}
		});
	}

	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'getcontacts': true
		},
		dataType: 'json',
		success: function(contacts) {
			if (unread == 0) {
				for (var x = 0; x <= contacts.length-1; x++) {
					checkNotification(x, contacts);
				}
			}
		}
	});

	if (document.hasFocus()) {
		unread = 0;
		document.title = "Starchat";
	}

},2000)

function sendMessage() {
	var usermessage = document.getElementById("chatbox").value;
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'sendmessage': usermessage,
			'sendmessageto': tmpid
		},
		dataType: 'json',
		success: function(data) {
			document.getElementById("chatbox").value = "";
			unread = 0;
			prevmsg = false;
			$.ajax({
				url: "../api/v1/",
				type: 'GET',
				data: {
					'token': token,
					'getcontacts': 'true'
				},
				dataType: 'json',
				success: function(contacts) {
					for (var x = 0; x <= contacts.length-1; x++) {
						//console.log(contacts[x][1]);
						checkNotification(x, contacts);
					}
				}
			});
		}
	});
}

function addContact() {
	var toadd = encodeURIComponent(prompt("Please Enter Username to Add"));
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'addcontact': toadd
		},
		success: function(code) {
			location.reload()
		}
	});
}
