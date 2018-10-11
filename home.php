<?php
session_start();
include 'mysqlinfo.php';

// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starcat");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$doesexist = $conn->query("SELECT id, firstname, password, anonid, contacts FROM accounts WHERE firstname = '".$conn->real_escape_string($_SESSION["usernamedata"])."'");

$row = $doesexist->fetch_array(MYSQLI_NUM);

if ($row[2] == hash("sha256",$_SESSION["passworddata"])) {
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
<head>
  <title>Starcat</title>
  <link rel="stylesheet" type="text/css" href="stylesheet.css">

</head>
<body>
  <div id="messbox">

  </div>
  <input type="text" id="chatbox" onkeypress="checkkey(event)">
  <input type="button" value="Send" onclick="sendmessage()" id="chatboxbutton">
  <div id="contacts">

  </div>

  <div id="topbar">
  <img src="img/logo.png" class="logo" onclick="addacontact()"> <!-- temporary button. please remove in futures --> <span class="logotext">Starcat</span>
  </div>
  <script type="text/javascript">
    var username = '<?php echo htmlspecialchars($_SESSION["usernamedata"]); ?>';
    var password = '<?php echo htmlspecialchars($_SESSION["passworddata"]); ?>';
    var tmpid = "EMPTY";
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
      httpGet("api.php?username="+username+"&password="+password+"&getcontacts=yes", function(str) {
        var contacts = str.split('&&&&&'); // we will use for loop to create next variable

        for (var x = 0; x <= contacts.length; x++) {
          document.getElementById("contacts").innerHTML += "<div class='box' onclick='switchcontact(\""+contacts[x].split("|||||")[1]+"\")'><img src='img/user.png' class='pfp'><div class='info'>"+contacts[x].split("|||||")[0]+"</div></div>";
        }
      });
    }
    splitcontacts(); // the function is defined, we run the code, just once for now
    function switchcontact(vals) {

      tmpid = vals;
    }
    setInterval(function() {
      if (tmpid != "EMPTY") {
        httpGet("api.php?username="+username+"&password="+password+"&readmessages="+tmpid, function(resu) {
          var les = resu.replace(/\n/g, "</div><br><div class='smessage'>");
          document.getElementById("messbox").innerHTML = ("<div>"+les+"</div>");
	  var objDiv = document.getElementById("messbox");
	  objDiv.scrollTop = objDiv.scrollHeight;
        });
      }
    },500)
    function sendmessage() {
      httpGet("api.php?username="+username+"&password="+password+"&sendmessage="+document.getElementById("chatbox").value+"&sendmessageto="+tmpid, function() {
        document.getElementById("chatbox").value = "";
      });
    }
    function addacontact() {
      var toadd = prompt("Please Enter Username to Add");
      httpGet("api.php?username="+username+"&password="+password+"&addcontact="+toadd, function() {
        console.log("Added User")
      });
    }
  </script>
</body>
</html>
