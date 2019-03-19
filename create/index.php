<?php
// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
require '../config.php';


// TODO Move all below to use Starchat Class API
// Warning: Spaghetti code below

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

$image_url = generateRandomString(16);

$check = $conn->prepare("SELECT image FROM accounts WHERE image=?");
$check->bind_param('s', $image_url);
$check->execute();
$check->get_result();

if ($check->num_rows !== 0) {
die("Please re-run, PFP id collided.");
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

$getn = $conn->prepare("INSERT INTO accounts (username, password, image, anonid, contacts) VALUES (?, ?, ?, ?, \"\")");
$getn->bind_param('ssss', $_POST["username"], password_hash($_POST["password"], PASSWORD_BCRYPT), $image_url, generateRandomString());
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
font-family: Arial, Helvetica, sans-serif;
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
<a href="../">&larr; Go back</a>
</div>
</body>
</html>
