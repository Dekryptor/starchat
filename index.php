<?php
session_start();
if(!file_exists('mysqlinfo.php')) {
    header("Location: setup.php");
    die("You should be <a href='setup.php'>here</a>");
}

require 'mysqlinfo.php';

$is_error = false;
$error;
$is_success = false;
$success;

if (isset($_GET["trylogin"])) {

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doesexist = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
$doesexist->bind_param('s', $POST["username"]);
$doesexist->execute();
$doesexistresult = $doesexist->get_result();
$doesexistrows = $doesexist->num_rows;

function generateRandomString($length = 32) {
	if (function_exists("random_bytes")) {
		return bin2hex(random_bytes($length)); // Supported Random Generator
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		return bin2hex(openssl_random_pseudo_bytes($length)); // Supported Random Generator
	} else {
		die("Secure random number not generated, your PHP version is most likely out of date");
	}
}

if ($doesexistnumrows >= 0 ) {
	while ($row = $doesexistresults->fetch_assoc()) {
		if (password_verify($_POST["password"], $row['password'])) {
			$_SESSION["usernamedata"] = $_POST["username"];
			$_SESSION["passworddata"] = $_POST["password"];

			$token = generateRandomString();
			$token_date = date("Y-m-d H:i:s");
			$token_check = $conn->prepare("SELECT token FROM tokens WHERE token=?");
			$token_check->bind_param('s', $token);
			$token_check->execute();
			$token_check_results = $token_check->get_result();
			$token_check_rows = $token_check_results->num_rows;

			if ($token_check_rows > 0) {
			die("Token has been used, please try again.");
			}
			$token_query = $conn->prepare("INSERT INTO tokens (token, username, created) VALUES (?, ?, ?)");
			$token_query->bind_param('sss', $token, $_POST["username"], $token_date);
			$token_query->execute();
			setcookie("stoken", $token, time() + (86400 * 30), "/");

			$conn->close();
			header("Location: client/");
			die("Redirecting...");
		}else {
			$is_error = true;
			$error = "Invalid login credentinals";
		}
	}
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
<a href="create/">Don't have an account? Create it.</a>
</form>
</div>
</body>
</html>
