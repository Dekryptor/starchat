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
<style>
body {
animation-name: look;
animation-duration: 1s;
margin:0;
background-color: #dbdbdb;
color: #000000;
font-family: arial;
}
@keyframes look {
0% { background-color: #707070; }
100% { background-color: #dbdbdb; }
}
.buttona {
background-color: #ffffff;
color: #000000;
border: 1px solid #000000;
padding: 15px;
padding-top: 5px;
padding-bottom: 5px;
transition: .5s;
}
.buttona:hover {
background-color: #000000;
color: #ffffff;
}
.login {
position: absolute;
left: calc(50% - 180px);
padding: 20px;
background-color: #ffffff;
text-align: center;
top: calc(50% - 115px);
box-shadow: 0px 2px 5px #afafaf;
}
.textbox {
padding: 8px;
background-color: #ffffff;
color: #000000;
border: 1px solid #000000;
border-radius: 3px;
}
.starchat {
position: absolute;
left: 15px;
top: 15px;
animation-name: fallin;
animation-duration: 1s
}
@keyframes fallin {
0% { left: 15px; top: -150px; }
100% { left: 15px; top: 15px; }
}
.top {
position: absolute;
background-color: #7abaff;
top: 0px;
left: 0px;
width: 100%;
height: calc(50% + 25px);
}
.bottom {
position: absolute;
background-color: #e8e8e8;
top: calc(50% + 25px);
left: 0px;
width: 100%;
height: calc(50% - 25px);
}
</style>
</head>
<body>
<div class="top"></div>
<div class="bottom"></div>
<img src="img/logo.png" width="65" height="66" class="starchat">
<div class="login">
<h1>Change Password</h1>
<form action="changepw.php?trycreate=yes" method="post">
Username: <input type="text" id="username" name="username" class="textbox"><br><br>
Current Password: <input type="password" id="password" name="password" class="textbox"><br><br>
New Password: <input type="password" id="newpassword" name="newpassword" class="textbox"><br><br>
<input type="submit" value="Change Password" class="buttona"><br><br>
</form>
<a href="index.php">Go back to login.</a>
</div>
</body>
</html>
