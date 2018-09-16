<?php
session_start();
include 'mysqlinfo.php';

if (isset($_GET["trylogin"])) {

// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starcat");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doesexist = $conn->query("SELECT id, firstname, password, anonid FROM accounts WHERE firstname = '".$conn->real_escape_string($_POST["username"])."'");

$row = $doesexist->fetch_array(MYSQLI_NUM);

if ($row[2] == crypt($_POST["password"])) {
$_SESSION["usernamedata"] = $_POST["username"];
$_SESSION["passworddata"] = $_POST["password"];

$conn->close();
header("Location: home.php");
die("Redirecting...");
}

$conn->close();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat</title>
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
top: calc(50% - 100px);
box-shadow: 0px 2px 5px #afafaf;
}
.textbox {
padding: 8px;
background-color: #ffffff;
color: #000000;
border: 1px solid #000000;
border-radius: 3px;
}
.starcat {
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
<img src="img/logo.png" width="65" height="66" class="starcat">
<div class="login">
<form action="index.php?trylogin=yes" method="post">
Username: <input type="text" id="username" name="username" class="textbox"><br><br>
Password: <input type="text" id="password" name="password" class="textbox"><br><br>
<input type="submit" value="Login" class="buttona"><br><br>
<?php
if(isset($_GET["created"])) {
echo "<br>Account created, try loggin in now<br><br>";
}
?>
<a href="create.php">Don't have an account? Create it.</a>
</div>
</body>
</html>
