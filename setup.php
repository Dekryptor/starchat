<?php

//     Starcat - Open source chat protocol
//    Copyright (C) 2018  SadError256, Hyland B.

//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.

//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.

//    You should have received a copy of the GNU General Public License
//    along with this program. If not, see <http://www.gnu.org/licenses/>.

if(isset($_POST["usetype"]) && isset($_POST["license"])) {
$usetype = $_POST["usetype"];
$license = $_POST["license"];

$username = $_POST["username"];
$password = $_POST["password"];
$location = $_POST["location"];

if ($license == "unread") {
die("Please view LICENSE file and restart the installer, or if you don't agree, uninstall this software.");
}

if ($usetype = "public") {
$conn = new mysqli($location, $username, $password);
if ($conn->connect_error) {
die("Connection to MYSQL server failed! Reason: ".$conn->connect_error);
}

$info = "
<?php
die("This file is restricted from viewing, and permitted only to server side software only");

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
?>
";

file_put_contents("mysqlinfo.php", $info);

$sql = "CREATE DATABASE starcat";
if ($conn->query($sql) === TRUE) {
echo "Database created successfully!";
}else{
echo "Error creating database: ".$conn->error;
}
$sql = "CREATE TABLE accounts (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(30) NOT NULL,
password VARCHAR(60) NOT NULL,
anonid VARCHAR(50) NOT NULL,
reg_date TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tables created successfully! Redirecting to index.php";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();

header("Location: index.php");
die("END");

}else{
$conn = new mysqli($location, $username, $password, "starcat");
if ($conn->connect_error) {
die("Connection to MYSQL server failed! Reason: ".$conn->connect_error);
}


$info = "
<?php
die("This file is restricted from viewing, and permitted only to server side software only");

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
\$loconly = \"yes\";
?>
";

file_put_contents("mysqlinfo.php", $info);

$sql = "CREATE DATABASE starcat";
if ($conn->query($sql) === TRUE) {
echo "Database created successfully!";
}else{
echo "Error creating database: ".$conn->error;
}

$sql = "CREATE TABLE accounts (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(30) NOT NULL,
password VARCHAR(60) NOT NULL,
anonid VARCHAR(50) NOT NULL,
reg_date TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tables created successfully! Redirecting privaccount.php";
} else {
    echo "Error creating table: " . $conn->error;
}


$conn->close();
header("Location: privaccount.php"); 
die("END");
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat Setup</title>
<style>
body {
background-color: #afafaf;
color: black;
font-family: arial;
}
.pushed {
padding: 60px;
margin: 15px;
background-color: #ffffff;
border: 1px solid #000000;
}
</style>
</head>
<body>
<div class="pushed">
<h1>Starcat Install</h1>
<p>You are here because you are doing the install. To proceed, the installer will create and setup mysql databases. Once this setup is done, starcat should be ready for production.</p>
<form action="setup.php" method="post">
<p>Lets setup some preferences first</p>
<b>Are you using starcat for public use or private use? if you are using for public, then account creations and other tweaks will be enabled [recommended]. If not, then only one account (the master account) will be used, and nobody can create an account.</b><br>
<input type="radio" name="usetype" value="public" checked> Public use<br>
<input type="radio" name="usetype" value="private"> Private use<br><br>
<b>Have you read and chose to agree to the GPLv3 license?</b><br>
<input type="radio" name="license" value="read" checked> I read it and im fine with it<br>
<input type="radio" name="license" value="unread"> I didn't read it, or chose not to agree<br><br>
Your <b>MYSQL</b> user: <input type="text" name="username" value="root"><br>
Your <b>MYSQL</b> password: <input type="password" name="password"><br>
MYSQL server url: <input type="text" name="location" value="localhost"> (leave default if unsure or if you are currently on the same server where mysql is installed and running)<br><br>
<input type="submit" value="Submit">
</form>
</div>
</body>
</html>
