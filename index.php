<?php
session_start();
include 'mysqlinfo.php';
$is_error = false;
$error;
$is_success = false;
$success;

if (isset($_GET["trylogin"])) {

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doesexist = $conn->query("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = '".$conn->real_escape_string($_POST["username"])."'");

$row = $doesexist->fetch_array(MYSQLI_NUM);

// akx says use bcrypt, i agree
if (password_verify($_POST["password"], $row[2])) {
$_SESSION["usernamedata"] = $_POST["username"];
$_SESSION["passworddata"] = $_POST["password"];

$conn->close();
header("Location: home.php");
die("Redirecting...");
}

else {
	$is_error = true;
	$error = "Invalid login credentinals";
}

$conn->close();

if(isset($_GET["created"])) {
	$is_success = true;
	$success = "Account successfully created";
}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Starchat</title>
<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
top: calc(50% - 110px);
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<body>
<div class="top"></div>
<div class="bottom"></div>
<img src="img/logo.png" width="65" height="66" class="starchat">
<div class="login">
<?php if($is_error) { echo "<div class='alert alert-danger' role='alert'>$error</div>"; } ?>
<?php if($is_success) { echo "<div class='alert alert-success' role='alert'>$success</div>"; } ?>
<form action="index.php?trylogin=yes" method="post">
<div class="form-group">
    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
</div>
<div class="form-group">
    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
  </div>
  <div class="form-group">
<button type="submit" class="btn btn-outline-success">Login</button>
</div>
<a href="create.php">Don't have an account? Create it.</a>
</form>
</div>
</body>
</html>
