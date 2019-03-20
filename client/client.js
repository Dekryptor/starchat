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

let id = null;
let calling = false;
if (jitsi === undefined) {
	// True and false in string because it is derived from a PHP variable
	jitsi = false;
}

// Parse message
String.prototype.parseString = function() {
	return this.replace(/<(?:.|\n)*?>/gm, '');
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

let username = getCookie("usernamedata");
let token = getCookie("stoken");
let api = new Starchat(token);

function createNotification(html) {
	$(html).hide().fadeIn(100).delay(3000).fadeOut(1000).appendTo("#notification-container");
}

console.log = function(msg) {
	let html = "<div class='alert alert-light shadow-sm fade show'>"+
		"<strong>Error</strong><br>"+
		" "+msg.parseString()+"</div>"
	createNotification(html);
}

console.error = console.warn = console.info = console.log;

function scrollBottom(body, speed = "slow") {
	$(body).animate({ scrollTop: $(body).height() + $(body).scrollTop() }, speed);
}


// Needed to send to websocket
function messageToJson(username, msg, userid) {
	let jsonMsg = {
		user: username,
		message: msg,
		id: userid
	}

	return JSON.stringify(jsonMsg);
}



// Upload profile picture
$("#upload-file-btn").click(function() {
	$.ajax({
		url: "../api/?token="+token,
		type: "POST",
		data: new FormData($("#pfp-upload")[0]),
		cache: false,
		processData: false,
		contentType: false,
		xhr: function() {
			var file = $.ajaxSettings.xhr();
			file.upload;
			return file;
		}
	});
})

// Custom context menu builder
function buildContextMenu(items, cx, cy) {
	$(".menu").hide();
	let menu = $("<div class='menu'></div>");
	for (let i = 0; i < items.length; i++) {
		let item = $("<a class='menu-item'>"+items[i].text+"</a>").click(items[i].run);
		$(menu).append(item);
	}
	$(menu).css({
		"position": "absolute",
		"left": cx-2,
		"top": cy-2
	}).mouseleave(function() {$(this).hide(100)}).appendTo("body");
}

function openSettings() {
	$("#settings").slideToggle(500);
}

function closeSettings() {
	$("#settings").slideToggle(100);
}

function buildMessage(musername, msg, doFade = true, id = "", pfp = "../img/user.png") {
	let message = $("<div class='message'><img class='profile-pic' src='"+((pfp === null) ? "../img/user.png" : "../img/profiles/"+pfp)+"'>" +
			"<div style='display:hidden;' class='id'>"+id.toString().parseString()+"</div><div class='message-right'><div class='username'>"+musername.parseString()+"</div>" +
			"<div class='contents'>"+msg.parseString()+"</div></div></div>");
	let msgUsername = $(message).find(".username").text();
	$(message).contextmenu(function(e) {
		buildContextMenu([
			{
				text: "Delete message",
				run: function() {
					if (msgUsername === username) {
						api.deleteMessage(id);
						fetchConversation();
					}else{
						console.error("Cannot delete someone elses message.")
					}
				}
			}], e.pageX, e.pageY);
		return false;
	});
	if (doFade === true) {
		$(message).hide().appendTo("#message-box").fadeIn(200);
	}else{
		$(message).appendTo("#message-box");
	}
}

function fetchConversation() {
	let result = api.readMessages(id, function(result) {
		$("#message-box").html("");
		for (let x = 0; x < result.length; x++) {
			// False for param doFade because we do not want fade on old messages
			buildMessage(result[x].username, result[x].message, false, result[x].id, result[x].profile_picture);
		}
		scrollBottom("#message-box", 0);
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

// Websocket connection
var conn = new WebSocket(wsType+'://'+wsUrl+':'+wsPort+'/'+wsUri+"?"+token);

conn.onerror = function(error) {
	console.error("Error connecting to websocket");
}

conn.onmessage = function(event) {
	let msg = JSON.parse(event.data);
	if (msg.id === id) {
		buildMessage(msg.user, msg.message);
		scrollBottom("#message-box");
	}else{
		let html = "<div onclick='switchContacts(\""+msg.id+"\");scrollBottom(\"#message-box\", 400);' class='alert alert-light shadow-sm fade show'>"+
		"<strong>"+msg.user.parseString()+"</strong><br>"+
		" "+msg.message.parseString()+"</div>"
		createNotification(html);
	}
}

function startCall() {
	if (calling === true) {
		endcall();
	}else{
		$("#callform").html("<button id='closeb' onclick='endCall()'>Close (end call)</button><iframe src='https://meet.jit.si/"+id+"' allow='microphone; camera'>");
		$("#callform").css({"visibility": "visible"});
		calling = true;
	}
}

function endCall() {
	$("#callform").html("");
	$("#callform").css({"visibility": "hidden"});
	calling = false;
}

// Sends message by listening for Enter key
function checkKey(event) {
	if (event.key === "Enter") {
		sendMessage();
	}
}

function loadContacts() {
	$("#contacts").html("<div class='loading'></div>");
	api.getContacts(function(contacts) {
		$("#contacts").html(""); // Clear result to remove loading animation
		for (let x = 0; x <= contacts.length-1; x++) {
			$("#contacts").append("<div class='box' onclick='switchContacts(\""+contacts[x]["chat_id"]+"\")'>"+
				"<img src='../img/user.png' class='pfp'><div class='info'>"+contacts[x]["roomname"]+"</div></div>");
		}
		$(".box").contextmenu(function(e) {
			buildContextMenu([
				{
					text: "Remove Contact",
					run: function() {
						alert("hi");
					}
				},
				{
					text: "Change info",
					run: function() {
						alert("hi");
					}
				}], e.pageX, e.pageY);
			return false;
		});
	});
}

$(document).contextmenu(function() {
	return false;
})

$(document).click(function() {
	$(".menu").hide();
})

loadContacts(); // the function is defined, we run the code, just once for now

function switchContacts(vals) {
	if (id == null) {
		if (jitsi == 'true') {
			$("#topbar").append("<img src='../img/call.png' id='call' onclick='startCall()'>");
		}
	}
	$("#message-box").html("<div class='loading'></div>");
	id = vals;
	fetchConversation();
}


function sendMessage() {
	let usermessage = document.getElementById("chatbox").value;
	api.sendMessage(usermessage, id);

	// Send message to websocket
	document.getElementById("chatbox").value = "";
	conn.send(messageToJson(username, usermessage, id));
}

function addContact() {
	let toAdd = prompt("Please Enter Username to Add");
	api.addContact(toAdd, function() {
		loadContacts();
	});
}

