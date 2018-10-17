<?php
include 'mysqlinfo.php';

if (isset($_GET["trycreate"])) {

// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starchat");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$safename = $conn->real_escape_string(htmlspecialchars($_GET["username"]));

$resu = $conn->query("SELECT id FROM accounts WHERE firstname = '$safename'");
if ($resu->num_rows == 0) {
	// Ok
}else{
	die("User does not exist");
}

$resi = $conn->query("SELECT password FROM accounts WHERE firstname='$safename'");
$resi = $resi->fetch_array(MYSQLI_NUM);

if (password_verify($_POST["password"],$resi[0])) {
	$newpass = $conn->real_escape_string(password_hash($_POST["newpassword"],PASSWORD_BCRYPT));
	$conn->query("UPDATE accounts SET password='$newpass' WHERE firstname='$safename'");
}else{
	die("Password does not match");
}


$conn->close();
header("Location: index.php");
die("Finished, redirecting");


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Starchat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
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
top: calc(50% - 140px);
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
<img src="img/logo.png" width="65" height="66" class="starchat">
<div class="login">
<h3>Create Account</h3>
<form action="create.php?trycreate=yes" method="post">
<div class="form-group">
    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
</div>
<div class="form-group">
    <input type="password" class="form-control" id="password" name="password" placeholder="Current Password">
  </div>
  <div class="form-group">
    <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Current Password">
  </div>
<button type="submit" class="btn btn-outline-success">Login</button>
</form>
</div>
</body>
</html>
