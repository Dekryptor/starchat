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

if(file_exists('config.php')) {
	die('Config file exists. Please delete the config file to start the setup for security reasons');
}

if(isset($_POST["username"])) {
	$usetype = "public";

	$username = $_POST["username"];
	$password = $_POST["password"];
	$location = $_POST["location"];
	$dbname = $_POST["dbname"];
	$websocketurl = $_POST["websocket"];
	$websocketport = $_POST["wsport"];


	if ($usetype = "public") {
		$conn = new mysqli($location, $username, $password);
		if ($conn->connect_error) {
			die("Connection to MYSQL server failed! Reason: ".$conn->connect_error);
		}

		// Make sure user does not bypass numeric port
		if (filter_var($websocketport, FILTER_VALIDATE_INT) === false) {
			die("Please check that your websocket port is an integer");
		}

		// Setting these values as strings so they can output into setup file as boolean
		if ($_POST["jitsi"] === true) {
			$isJitsi = "true";
		}else{
			$isJitsi = "false";
		}

		// Setup file
		$info = "
		<?php

		\$user = \"".addslashes($username)."\";
		\$pass = \"".addslashes($password)."\";
		\$mysqlurl = \"".addslashes($location)."\";
		\$dbname = \"".addslashes($dbname)."\";
		\$jitsi = $isJitsi;
		\$websocketUrl = \"".addslashes($websocketurl)."\";
		\$wsport = $websocketport;
		\$conn = new mysqli(\$mysqlurl, \$user, \$pass, \$dbname);
		?>
		";

		file_put_contents("config.php", $info);

		// The database will not be created if it does not exist
		// Setup will crash otherwise
		// DO NOT use prepared statement to create object
		// We instead will use preg_replace and mysqli real escape string, that is safe enough
		// In the future, do not use real escape string, it is insecure
		$sql = "CREATE DATABASE IF NOT EXISTS ".$conn->real_escape_string($dbname);
		if ($conn->query($sql) === FALSE) {
			echo "Error creating database: " . $conn->error . "<br>";
		}

		$conns = new mysqli($location, $username, $password, $dbname);

		$sql = "CREATE TABLE accounts (
		id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		username VARCHAR(70) NOT NULL,
		password VARCHAR(100) NOT NULL,
		anonid VARCHAR(70) NOT NULL,
		contacts VARCHAR(2100) NOT NULL
		)";

		if ($conns->query($sql) === FALSE) {
			echo "Error creating table: " . $conns->error . "<br>";
		}


		$sqla = "CREATE TABLE messages (
		id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		chatid VARCHAR(70) NOT NULL,
		dt timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		username VARCHAR(50) NOT NULL,
		message VARCHAR(2100) NOT NULL
		)";

		if ($conns->query($sqla) === FALSE) {
			echo "Error creating table: " . $conns->error . "<br>";
		}

		$sqlb = "CREATE TABLE tokens (
		id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		token VARCHAR(70) NOT NULL,
		username VARCHAR(50) NOT NULL,
		created VARCHAR(50) NOT NULL
		)";

		if ($conns->query($sqlb) === FALSE) {
			echo "Error creating table: " . $conns->error . "<br>";
		}

		$conns->close();
		unlink(__FILE__); // Delete the setup, just to prevent future issues
		header("Location: index.php");
		die("END");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Starchat Setup</title>
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<h1>Starchat Install</h1>
		<p>You are here because you are doing the install. To proceed, the installer will create and setup mysql databases. Once this setup is done, starchat should be ready for production.</p>
		<i>Note: The setup file will be removed once you press submit</i>
		<form action="setup.php" method="post">
			<p>Lets setup some preferences first</p>
			<b>Do you want Jit.si support in web client (video calling)?</b><br>
			<input type="radio" name="jitsi" value="true" checked> Yes<br>
			<input type="radio" name="jitsi" value="false"> No<br><br>
			Your <b>MYSQL</b> user: <input type="text" class="form-control" name="username" value="root"><br>
			Your <b>MYSQL</b> password: <input type="password" class="form-control" name="password"><br>
			MYSQL server url: <input type="text" class="form-control" name="location" value="localhost"> (leave default if unsure or if you are currently on the same server where mysql is installed and running)<br>
			MYSQL Database name: <input type="text" class="form-control" name="dbname" value="starchat"><br>
			Websocket Host: <input type="text" class="form-control" name="websocket" value="<?php echo $_SERVER['SERVER_NAME']; ?>"><br>
			<p>Note: Port 8080 will work on some free services that anonymize your servers IP address</p>
			Websocket Port: <input type="number" class="form-control" name="wsport" value="8080"><br><br>
			<input type="submit" class="btn btn-submit" value="Submit">
		</form>
	</div>
</body>
</html>
