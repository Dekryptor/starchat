<?php
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
session_start();
require '../mysqlinfo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$doesexist = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
$doesexist->bind_param('s', $_SESSION["usernamedata"]);
$doesexist->execute();
$doesexistresult = $doesexist->get_result();
$doesexistnumrows = $doesexistresult->num_rows;

if ($doesexistnumrows >= 0 ) {
	while($row = $doesexistresult->fetch_assoc()) {
		if (password_verify($_SESSION["passworddata"],$row['password'])) {
			// logged in, move on
		}else{
			$conn->close();
			unset($_SESSION["usernamedata"]);
			unset($_SESSION["passworddata"]);
			header("Location: index.php");
			die("redirecting...");
			exit();
		}
	}
}



?>

<!DOCTYPE html>
<html lang="en">

<!-- Starchat Web Client v0.8.0 -->

<head>
  <title>Starchat</title>
  <link rel="stylesheet" type="text/css" href="../libs/bootstrap/css/bootstrap.min.css">
  <link id="stylesheeta" rel="stylesheet" type="text/css" href="../themes/default.css">

  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div id="messbox">
	  <div id="messboxsmall">

	  </div>
  </div>
  <input type="text" id="chatbox" onkeypress="checkkey(event)">
  <input type="button" value="Send" onclick="sendmessage()" id="chatboxbutton">
  <div id="contacts">

  </div>

  <div id="topbar">
  <img src="../img/hammenu.png" class="logo" onclick="opensettings()"> <span class="logotext">Starchat</span>
  </div>

  <div id="callform">

  </div>

  <div id="settings">
	<a href="#" onclick="closesettings()">Close Options</a>
	<h1>Options</h1>
	<p><select id="theme" onchange="themeChange();">
    <option value="default">Default</option>
    <option value="night-blue">Night Blue</option>
    <option value="hacker-green">Hacker Green</option>
    <option value="sky-blue">Sky Blue</option>
    <option value="material-pink">Material Pink</option>
  </select></p>
	<p><input type="button" value="Add Contact" onclick="addacontact()" class="btn btn-light"><!-- TODO change this dialog to own function instead of alert --></p>
	<p><input type="button" value="Add Contact to active conversation" disabled="disabled" onclick="addbcontact()" class="btn btn-light"> (group chats) (Currently Broken)</p>
  <p><a href="../logout.php" class="btn btn-danger">Logout</a></p>
  </div>
  <script type="text/javascript">
  	
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

	var tmpid = "EMPTY";
	var calling = "no";
	var jitsi = '<?php echo htmlspecialchars($jitsi); ?>';
	var mesapn = [];
	var oldmesapn = [];
	var unread = 0;
	var oldmessage = 0;

	function opensettings() {
		document.getElementById("settings").style.visibility = "visible";
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

	if (getCookie("starchattheme") != "") {
		swapSheet(getCookie("starchattheme"));
	}

	var token = getCookie("stoken");

	function themeChange() {
		setCookie("starchattheme", "../themes/"+document.getElementById("theme").value+".css", 365);
		swapSheet(getCookie("starchattheme"));
	}

	function startcall() {
		if (calling == "yes") {
			endcall();
		}else{
			document.getElementById("callform").innerHTML = "<button id='closeb' onclick='endcall()'>Close (end call)</button><iframe src='https://meet.jit.si/"+tmpid+"' allow='microphone; camera'>";
			document.getElementById("callform").style.visibility = "visible";
			calling = "yes";
		}
	}

	function endcall() {
		document.getElementById("callform").innerHTML = "";
		document.getElementById("callform").style.visibility = "hidden";
		calling = "no";
	}

	function closesettings() {
		document.getElementById("settings").style.visibility = "hidden";
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
		httpGet("../api/v1/?token="+token+"&getcontacts=yes", function(str) {

		var contacts = JSON.parse(str);
		for (var x = 0; x <= contacts.length; x++) {
			document.getElementById("contacts").innerHTML += "<div class='box' onclick='switchcontact(\""+contacts[x][1]+"\")'><img src='../img/user.png' class='pfp'><div class='info'>"+contacts[x][0]+"</div></div>";
		}
		});
	}

	splitcontacts(); // the function is defined, we run the code, just once for now

	function switchcontact(vals) {
		if (tmpid == "EMPTY") {
			if (jitsi == 'true') {
				document.getElementById("topbar").innerHTML += "<img src='../img/call.png' id='call' onclick='startcall()'>";
			}
		}
		tmpid = vals;
	}

  function meshan(x, contacts) {
		httpGet("../api/v1/?token="+token+"&readmessages="+contacts[x][1]+"&count=5", function(data) {
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
		});
	}

  if (mesapn == []) {
    httpGet("../api/v1/?token="+token+"&getcontacts=yes", function(data) {
      var contacts = JSON.parse(data);
      for (var x = 0; x <= contacts.length-1; x++) {
        meshan(x, contacts);
      }
    });
    oldmesapn = [];
  }




	setInterval(function() {
		if (tmpid != "EMPTY") {
			httpGet("../api/v1/?token="+token+"&readmessages="+tmpid+"&count=25", function(resu) {
				var les = JSON.parse(resu);
				document.getElementById("messboxsmall").innerHTML = "";
				var opres = 0;
				for (var x = 0; x < les.length; x++) {
					if (les[x].username == "<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION["usernamedata"]); ?>") {
						opres = 0;
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
		if (tmpid == "EMPTY") {
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
  </script>
</body>
</html>
