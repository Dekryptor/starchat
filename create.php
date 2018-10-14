<?php
include 'mysqlinfo.php';

if (isset($_GET["trycreate"])) {

// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starchat");
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
if(preg_match('/(.*){3,50}/', $_POST["password"])) {
    echo "Password is set, will be encrypted";
}else{
die("Please choose a password above 3+ characters and below 50 characters");
}
}else{
die("You need to enter a password for security reasons");
}

function generateRandomString($length = 40) {
    $characters = '0123456789!?-+=abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$resu = $conn->query("SELECT id FROM accounts WHERE firstname = '".$conn->real_escape_string($_POST["username"])."'");

if ($resu->num_rows == 0) {
echo "Name doesnt exist, thats good, lets keep going<br>";
}else{
die("Name is used, please go back and try a different username");
}

$sql = "INSERT INTO accounts (firstname, password, anonid, contacts)
VALUES (\"".$conn->real_escape_string($_POST["username"])."\", \"".$conn->real_escape_string(hash("sha256",$_POST["password"]))."\", \"".generateRandomString()."\", \"\")";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: <br>" . $conn->error;
}

$conn->close();
header("Location: index.php?created=yes");
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
<h1>Account creation</h1>
<form action="create.php?trycreate=yes" method="post">
Username: <input type="text" id="username" name="username" class="textbox"><br><br>
Password: <input type="text" id="password" name="password" class="textbox"><br><br>
<input type="submit" value="Create Account" class="buttona"><br><br>
</form>
<a href="index.php">Go back to login.</a>
</div>
</body>
</html>
