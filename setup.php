<?php

if(isset($_POST["jitsi"])) {
// $usetype = $_POST["usetype"];
$usetype = "public";

$username = $_POST["username"];
$password = $_POST["password"];
$location = $_POST["location"];


if ($usetype = "public") {
$conn = new mysqli($location, $username, $password);
if ($conn->connect_error) {
die("Connection to MYSQL server failed! Reason: ".$conn->connect_error);
}

if ($_POST["jitsi"] == "true") {
$info = "
<?php

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
\$jitsi = \"true\";
\$conn = new mysqli(\$mysqlurl, \$user, \$pass, 'starchat');
?>
";
}else{
$info = "
<?php

\$user = \"$username\";
\$pass = \"$password\";
\$mysqlurl = \"$location\";
\$jitsi = \"false\";
\$conn = new mysqli(\$mysqlurl, \$user, \$pass, 'starchat');
?>
";
}

file_put_contents("mysqlinfo.php", $info);

$sql = "CREATE DATABASE starchat";
if ($conn->query($sql) === TRUE) {
echo "Database created successfully!<br>";
}else{
echo "Error creating database: ".$conn->error . "<br>";
}
$conn->close();

$conns = new mysqli($location, $username, $password, "starchat");

$sql = "CREATE TABLE accounts (
id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(70) NOT NULL,
password VARCHAR(100) NOT NULL,
anonid VARCHAR(70) NOT NULL,
contacts VARCHAR(2100) NOT NULL
)";

if ($conns->query($sql) === TRUE) {
	// Tables created
} else {
    echo "Error creating table: " . $conns->error . "<br>";
}


$sqla = "CREATE TABLE messages (
id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
chatid VARCHAR(70) NOT NULL,
dt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
username VARCHAR(50) NOT NULL,
message VARCHAR(2100) NOT NULL
)";

if ($conns->query($sqla) === TRUE) {
	// Tables created
} else {
    echo "Error creating table: " . $conns->error . "<br>";
}

$conns->close();
header("Location: index.php");
die("END");

}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Starchat Setup</title>
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
<h1>Starchat Install</h1>
<p>You are here because you are doing the install. To proceed, the installer will create and setup mysql databases. Once this setup is done, starchat should be ready for production.</p>
<form action="setup.php" method="post">
<p>Lets setup some preferences first</p>
<b>Do you want Jit.si support (video calling)?</b><br>
<input type="radio" name="jitsi" value="true" checked> Yes<br>
<input type="radio" name="jitsi" value="false"> No<br><br>
Your <b>MYSQL</b> user: <input type="text" name="username" value="root"><br>
Your <b>MYSQL</b> password: <input type="password" name="password"><br>
MYSQL server url: <input type="text" name="location" value="localhost"> (leave default if unsure or if you are currently on the same server where mysql is installed and running)<br><br>
<input type="submit" value="Submit">
</form>
</div>
</body>
</html>
