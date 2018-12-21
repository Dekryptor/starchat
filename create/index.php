<?php
require '../mysqlinfo.php';

if (isset($_GET["trycreate"])) {

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["username"])) {
if(preg_match('/\w{3,30}$/', $_POST["username"])) {
   echo "Username passed";
}else{
die("Please choose a username with, a-z, A-Z, or _ characters only, and below 30 characters, and above 3 characters");
}
}else{
die("You need a username");
}

if (isset($_POST["password"])) {
  if(strlen($_POST["password"]) >= 8) {
    if (strlen($_POST["password"]) <= 256) {
      echo "Password is set, will be encrypted";
    }else{
      die("Please choose a password above 8 characters and below 256 characters");
    }
  }else{
    die("Please choose a password above 8 characters and below 256 characters");
  }
}else{
  die("You need to enter a password for major security reasons");
}

function generateRandomString($length = 32) {
	if (function_exists("random_bytes")) {
		return bin2hex(random_bytes($length)); // Supported Random Generator
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		return bin2hex(openssl_random_pseudo_bytes($length)); // Supported Random Generator
	} else {
		starchat_error("Secure random number not generated, your PHP version is most likely out of date");
	}
}

$resu = $conn->prepare("SELECT id FROM accounts WHERE username = ?");
$resu->bind_param('s', $_POST["username"]);
$resu->execute();
$resu->get_result();

if ($resu->num_rows == 0) {
echo "Name doesnt exist, thats good, lets keep going<br>";
}else{
die("Name is used, please go back and try a different username");
}

$getn = $conn->prepare("INSERT INTO accounts (username, password, anonid, contacts) VALUES (?, ?, ?, \"\")");
$getn->bind_param('sss', $_POST["username"], password_hash($_POST["password"], PASSWORD_BCRYPT), generateRandomString());
$getn->execute();


$conn->close();
header("Location: ../index.php?created=yes");
die("Finished, redirecting");


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Starchat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="../libs/bootstrap/css/bootstrap.min.css">
<style>
body {
margin:0;
background-color: #fafafa;
color: #000000;
font-family: arial;
}
.login {
position: absolute;
left: calc(50% - 140px);
padding: 20px;
background-color: #ffffff;
text-align: center;
top: calc(50% - 130px);
box-shadow: 0px 7px 20px #afafaf;
border-radius: 10px;
}
.starchat {
position: absolute;
left: 15px;
top: 15px;
}
</style>
</head>
<body>
<div class="top"></div>
<div class="bottom"></div>
<img src="../img/logo.png" width="65" height="66" class="starchat">
<div class="login">
<h3>Create Account</h3>
<form action="index.php?trycreate=yes" method="post">
<div class="form-group">
    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
</div>
<div class="form-group">
    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
  </div>
  <div class="form-group">
<button type="submit" class="btn btn-outline-success">Create Account</button>
</div>
</form>
</div>
</body>
</html>
