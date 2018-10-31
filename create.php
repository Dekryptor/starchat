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

$resu = $conn->prepare("SELECT id FROM accounts WHERE firstname = ?");
$resu->bind_param('s', $_POST["username"]);
$resu->execute();
$resu->store_result();

if ($resu->num_rows == 0) {
echo "Name doesnt exist, thats good, lets keep going<br>";
}else{
die("Name is used, please go back and try a different username");
}

$getn = $conn->prepare("INSERT INTO accounts (firstname, password, anonid, contacts) VALUES (?, ?, ?, ?)");
$getn->bind_param('ssss', $_POST["username"], password_hash($_POST["password"], PASSWORD_BCRYPT), generateRandomString());
$getn->execute();


$conn->close();
header("Location: index.php?created=yes");
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
<img src="img/logo.png" width="65" height="66" class="starchat">
<div class="login">
<h3>Create Account</h3>
<form action="create.php?trycreate=yes" method="post">
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
