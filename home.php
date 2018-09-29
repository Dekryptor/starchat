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
  <div id="contacts">
  <div class="box">
  <img src="placeholder" class="pfp">
  <div class="info">
  SadErr
  </div>
  </div>
  </div>

  <div id="topbar">
  <img src="img/logo.png" class="logo"> <span class="logotext">Starcat</span>
  </div>
</body>
</html>
