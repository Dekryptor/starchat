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
  <script type="text/javascript">
    var username = '<?php echo htmlspecialchars($_SESSION["usernamedata"]); ?>';
    var password = '<?php echo htmlspecialchars($_SESSION["passworddata"]); ?>';
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
      var contacts_inside = [];

      console.log(contacts);


      for (var x = 0; x <= contacts.length; x++) {
        contacts_inside.push(contacts[x].split('|||||'));
        document.getElementById("contacts").innerHTML += "<div id='contacts'><div class='box'><img src='' class='pfp'><div class='info'>"+contacts_inside[0]+"</div><div class='nosee'>"+contacts_inside[1]+"</div></div></div>";
      }

      console.log(contacts_inside);

    }
  </script>
</head>
<body>
  <div id="contacts">

  </div>

  <div id="topbar">
  <img src="img/logo.png" class="logo"> <span class="logotext">Starcat</span>
  </div>
</body>
</html>
