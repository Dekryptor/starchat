<?php

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

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
?>
";

file_put_contents("mysqlinfo.php", $info);

$sql = "CREATE DATABASE starcat";
if ($conn->query($sql) === TRUE) {
echo "Database created successfully!<br>";
}else{
echo "Error creating database: ".$conn->error . "<br>";
}
$conn->close();

$conns = new mysqli($location, $username, $password, "starcat");

$sql = "CREATE TABLE accounts (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(70) NOT NULL,
password VARCHAR(100) NOT NULL,
anonid VARCHAR(70) NOT NULL,
contacts VARCHAR(21000) NOT NULL
)";

if ($conns->query($sql) === TRUE) {
    echo "Tables created successfully! Redirecting to index.php<br>";
} else {
    echo "Error creating table: " . $conns->error . "<br>";
}


$conns->close();
header("Location: index.php");
die("END");

}else{
$conn = new mysqli($location, $username, $password);
if ($conn->connect_error) {
die("Connection to MYSQL server failed! Reason: ".$conn->connect_error);
}


$info = "
<?php

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
\$loconly = \"yes\";
?>
";

file_put_contents("mysqlinfo.php", $info);

$sql = "CREATE DATABASE starcat";
if ($conn->query($sql) === TRUE) {
echo "Database created successfully!<br>";
}else{
echo "Error creating database: ".$conn->error . "<br>";
}
$conn->close();

$conns = new mysqli($location, $username, $password, "starcat");

$sql = "CREATE TABLE accounts (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(70) NOT NULL,
password VARCHAR(100) NOT NULL,
anonid VARCHAR(70) NOT NULL,
contacts VARCHAR(21000) NOT NULL
)";

if ($conns->query($sql) === TRUE) {
    echo "Tables created successfully! Redirecting privaccount.php<br>";
} else {
    echo "Error creating table: " . $conns->error . "<br>";
}

$conns->close();
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
<b>Have you read and chose to agree to the MIT license?</b><br>
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
