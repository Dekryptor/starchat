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

let tmpid = null;
let calling = false;
if (jitsi === undefined) {
	// True and false in string because it is derived from a PHP variable
	jitsi = false;
}

// Simple cookie managers
function setCookie(cname, cvalue, exdays) {
	let d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	let expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	let name = cname + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for(let i = 0; i <ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) === ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) === 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function scrollBottom(body) {
	$(body).animate({ scrollTop: $(body).height() }, "slow");
}

let username = getCookie("usernamedata");
let token = getCookie("stoken");

// Needed to send to websocket
function messageToJson(username, msg, userid) {
	let jsonMsg = {
		user: username,
		message: msg,
		id: userid
	}

	return JSON.stringify(jsonMsg);
}

function buildMessage(username, msg) {
	let message = "<div class='message'><img class='profile-pic' src='../img/user.png'>" +
								"<div class='message-right'><div class='username'>"+username.replace(/<(?:.|\n)*?>/gm, '')+"</div>" +
								"<div class='contents'>"+msg.replace(/<(?:.|\n)*?>/gm, '')+"</div></div></div>";
	$("#message-box").append(message);
}

function openSettings() {
	$("#settings").css({"visibility": "visible"});
}

function fetchConversation() {
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
			$("#message-box").html("");
			for (let x = 0; x < result.length; x++) {
				buildMessage(result[x].username, result[x].message);
			}
		}
	});
}

// Used for switching themes
function swapSheet(sheet) {
	document.getElementById("stylesheeta").setAttribute("href", sheet);
}

// Remember previous theme
if (getCookie("starchattheme") !== "") {
	swapSheet(getCookie("starchattheme"));
}


function themeChange() {
	setCookie("starchattheme", "../themes/"+document.getElementById("theme").value+".css", 365);
	swapSheet(getCookie("starchattheme"));
}

// Websocket stuff
let conn = new WebSocket(wsType+'://'+wsUrl+':'+wsPort+'/'+wsUri+"?"+token);
conn.onopen = function(event) {
	console.log(wsType.toUpperCase()+": Connected established to "+wsType+"://"+wsUrl+":"+wsPort+"/"+wsUri);
}
conn.onmessage = function(event) {
	let msg = JSON.parse(event.data);
	buildMessage(msg.user, msg.message);
	scrollBottom("#message-box");
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

// Sends message by listening for Enter key
function checkKey(event) {
	if (event.key === "Enter") {
		sendMessage();
	}
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
			for (let x = 0; x <= contacts.length-1; x++) {
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
	$("#message-box").html("<div class='loading'></div>");
	tmpid = vals;
	fetchConversation();
}


function sendMessage() {
	let usermessage = document.getElementById("chatbox").value;
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
			// Send message to websocket
			conn.send(messageToJson(username, usermessage, tmpid));
		}
	});
}

function addContact() {
	let toadd = prompt("Please Enter Username to Add");
	$.ajax({
		url: "../api/v1/",
		type: 'GET',
		data: {
			'token': token,
			'addcontact': toadd
		},
		success: function(code) {
			loadContacts();
		}
	});
}
