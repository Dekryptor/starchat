<?php
session_start();
require 'mysqlinfo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$doesexist = $conn->query("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = '".$conn->real_escape_string($_SESSION["usernamedata"])."'");

$row = $doesexist->fetch_array(MYSQLI_NUM);

if (password_verify($_SESSION["passworddata"],$row[2])) {
// logged in, move on
}else{
$conn->close();
unset($_SESSION["usernamedata"]);
unset($_SESSION["passworddata"]);
header("Location: index.php");
die("redirecting...");
exit();
}



?>

<!DOCTYPE html>
<html lang="en">
	
<!-- Starchat Web Client v0.7.2 -->
	
<head>
  <title>Starchat</title>
  <link id="stylesheeta" rel="stylesheet" type="text/css" href="themes/default.css">

<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div id="messbox">

  </div>
  <input type="text" id="chatbox" onkeypress="checkkey(event)">
  <input type="button" value="Send" onclick="sendmessage()" id="chatboxbutton">
  <div id="contacts">

  </div>

  <div id="topbar">
  <img src="img/hammenu.png" class="logo" onclick="opensettings()"> <span class="logotext">Starchat</span>
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
  <p><a href="logout.php" class="btn btn-danger">Logout</a></p>
  </div>
  <script type="text/javascript">
    var username = '<?php echo htmlspecialchars($_SESSION["usernamedata"]); ?>';
    var password = '<?php echo htmlspecialchars($_SESSION["passworddata"]); ?>';
    var tmpid = "EMPTY";
    var calling = "no";
    var jitsi = '<?php echo htmlspecialchars($jitsi); ?>';
    var mesapn = [];
    var prevmsg = false;
    var unread = 0;
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
	  function themeChange() {
  	setCookie("starchattheme", "themes/"+document.getElementById("theme").value+".css", 365);
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
      httpGet("api/v1/?username="+username+"&password="+password+"&getcontacts=yes", function(str) {
		  
        var contacts = JSON.parse(str);
        for (var x = 0; x <= contacts.length; x++) {
          document.getElementById("contacts").innerHTML += "<div class='box' onclick='switchcontact(\""+contacts[x][1]+"\")'><img src='img/user.png' class='pfp'><div class='info'>"+contacts[x][0]+"</div></div>";
        }
      });
    }
    splitcontacts(); // the function is defined, we run the code, just once for now
    function switchcontact(vals) {
	if (tmpid == "EMPTY") {
	    if (jitsi == 'true') {
		document.getElementById("topbar").innerHTML += "<img src='img/call.png' id='call' onclick='startcall()'>";
	    }

	}
      tmpid = vals;
    }
    function meshan(x, contacts) {
       httpGet("api/v1/?username="+username+"&password="+password+"&readmessages="+contacts[x][1], function(data2) {
         if (prevmsg == true) {
             if (mesapn[x] != data2) {
                 if (unread == 0) {
                     var audio = new Audio('sounds/notification.wav');
                     audio.play();
                     document.title = "New Message!!";
                     unread = 1;
                }
             }
         }else{
             setTimeout(function() {
             mesapn[x] = data2;
             prevmsg = true;
             // do nothing
             }, 5000);
          }
       });
	}
    setInterval(function() { 
      if (tmpid != "EMPTY") {
        httpGet("api/v1/?username="+username+"&password="+password+"&readmessages="+tmpid, function(resu) {
			var les = JSON.parse(resu);
			document.getElementById("messbox").innerHTML = "";
			for (var x = 0; x < les.length; x++) {
				document.getElementById("messbox").innerHTML += "<div class='smessage'><span class='susername'>"+les[x].username+"</span> "+les[x].message+"</div>"; // In 0.8 we will include datetime float datetime to right
			}
			
			// scroll to bottom
			var objDiv = document.getElementById("messbox");
			objDiv.scrollTop = objDiv.scrollHeight;
        });
      }
              
         if (document.hasFocus() == false) {
            httpGet("api/v1/?username="+username+"&password="+password+"&getcontacts=yes", function(data) {
                var contacts = JSON.parse(data);
                for (var x = 0; x <= contacts.length-1; x++) {
                    //console.log(contacts[x][1]);
                    meshan(x, contacts);
                }
            });
        }else{
        if (unread == 1) {
            unread = 0;
            prevmsg = false;
            document.title = "Starchat";
            httpGet("api/v1/?username="+username+"&password="+password+"&getcontacts=yes", function(data) {
                var contacts = JSON.parse(data);
                for (var x = 0; x <= contacts.length-1; x++) {
                    //console.log(contacts[x][1]);
                    meshan(x, contacts);
                }
            });
        }
        }

    },1000)
    function sendmessage() {
      httpGet("api/v1/?username="+username+"&password="+password+"&sendmessage="+document.getElementById("chatbox").value+"&sendmessageto="+tmpid, function() {
        document.getElementById("chatbox").value = "";
            unread = 0;
            prevmsg = false;
            httpGet("api/v1/?username="+username+"&password="+password+"&getcontacts=yes", function(data) {
                var contacts = JSON.parse(data);
                for (var x = 0; x <= contacts.length-1; x++) {
                    //console.log(contacts[x][1]);
                    meshan(x, contacts);
                }
            });
      });
    }
    function addacontact() {
      var toadd = prompt("Please Enter Username to Add");
      httpGet("api/v1/?username="+username+"&password="+password+"&addcontact="+toadd, function(code) {
	      if (code == "\n0") {
		      location.reload()
	      }else{
		      alert("User not found");
	      }
      });
    }

    function addbcontact() {
	if (tmpid == "EMPTY") {
		alert("Please actually select a contact");
	}else{
      var toadd = prompt("Please Enter Username to Add");
      httpGet("api/v1/?username="+username+"&password="+password+"&addtoconvo="+toadd+"&convoid="+tmpid, function(code) {
	      if (code != "\n0") {
		      alert("User not found or the conversation you are in does not exist (you should not get the second reason on the web client)");
	      }
      });
	}
    }
  </script>
</body>
</html>
