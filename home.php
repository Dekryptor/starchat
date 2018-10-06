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
  <input type="text" id="chatbox">
  <input type="button" value="Send" onclick="sendmessage()" id="chatboxbutton">
  <div id="contacts">

  </div>

  <div id="topbar">
  <img src="img/logo.png" class="logo" onclick="addacontact()"> <!-- temporary button, please remove in futures --> <span class="logotext">Starcat</span>
  </div>
  <script type="text/javascript">
    var username = '<?php echo htmlspecialchars($_SESSION["usernamedata"]); ?>';
    var password = '<?php echo htmlspecialchars($_SESSION["passworddata"]); ?>';
    var tmpid = "EMPTY";
    function httpGet(theUrl) {
      // Allows us to do a http get and then return the contents
      var xmlHttp = new XMLHttpRequest();
      xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
      xmlHttp.send( null );
      return xmlHttp.responseText;
    }
    function splitcontacts() {
      var str = httpGet("api.php?username="+username+"&password="+password+"&getcontacts=yes");
      var contacts = str.split('&&&&&'); // we will use for loop to create next var

      for (var x = 0; x <= contacts.length; x++) {
        document.getElementById("contacts").innerHTML += "<div id='contacts'><div class='box' onclick='switchcontact(\""+contacts[x].split("|||||")[1]+"\")'><img src='' class='pfp'><div class='info'>"+contacts[x].split("|||||")[0]+"</div></div></div>";
      }
    }
    splitcontacts(); // the function is defined, we run the code, just once for now
    function switchcontact(vals) {
      var resu = httpGet("api.php?username="+username+"&password="+password+"&readmessages="+vals);
      document.getElementById("messbox").innerHTML = resu;
      tmpid = vals;
      console.log(resu);
      console.log(vals);
    }
    function sendmessage() {
      var errorcode = httpGet("api.php?username="+username+"&password="+password+"&sendmessage="+document.getElementById("chatbox").value+"&sendmessageto="+tmpid);
      console.log(tmpid)
    }
    function addacontact() {
      var toadd = prompt("Please Enter Username to Add");
      httpGet("api.php?username="+username+"&password="+password+"&addcontact="+toadd);
    }
  </script>
</body>
</html>
