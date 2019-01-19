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
session_start();
require '../mysqlinfo.php';

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
$doesexist = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
$doesexist->bind_param('s', $_SESSION["usernamedata"]);
$doesexist->execute();
$doesexistresult = $doesexist->get_result();
$doesexistnumrows = $doesexistresult->num_rows;

if ($doesexistnumrows >= 0 ) {
	while($row = $doesexistresult->fetch_assoc()) {
		if (password_verify($_SESSION["passworddata"],$row['password'])) {
			// logged in, move on
		}else{
			$conn->close();
			unset($_SESSION["usernamedata"]);
			unset($_SESSION["passworddata"]);
			header("Location: index.php");
			die("redirecting...");
			exit();
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- Starchat Web Client v0.8.1 -->
<head>
	<title>Starchat</title>
	<link rel="stylesheet" type="text/css" href="../libs/bootstrap/css/bootstrap.min.css">
	<link id="stylesheeta" rel="stylesheet" type="text/css" href="../themes/default.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="../libs/jquery.min.js"></script>
</head>
<body>
	<div id="messbox">
		<div id="messboxsmall">

		</div>
	</div>
	<input type="text" id="chatbox" onkeypress="checkkey(event)">
	<input type="button" value="Send" onclick="sendmessage()" id="chatboxbutton">
	<div id="contacts"> <div class="loading"></div> </div>

	<div id="topbar">
		<img src="../img/hammenu.png" class="logo" onclick="opensettings()"> <span class="logotext">Starchat</span>
	</div>

	<div id="callform">

	</div>

	<div id="settings">
		<a href="#" onclick="closesettings()">Close Options</a>
		<h1>Options</h1>
		<p><select id="theme" onchange="themeChange();">
			<option value="default">Default</option>
			<option value="night-blue">Night Blue</option>
			<option value="hacker-green">Hacker Green</option>
			<option value="sky-blue">Sky Blue</option>
			<option value="material-pink">Material Pink</option>
		</select></p>
		<p><input type="button" value="Add Contact" onclick="addacontact()" class="btn btn-light"><!-- TODO change this dialog to own function instead of alert --></p>
		<p><input type="button" value="Add Contact to active conversation" disabled="disabled" onclick="addbcontact()" class="btn btn-light"> (group chats) (Currently Broken)</p>
		<p><a href="../logout.php" class="btn btn-danger">Logout</a></p>
	</div>
	<script>
		var jitsi = '<?php echo htmlspecialchars($jitsi); ?>';
	</script>
	<script src="client.js"></script>
</body>
</html>
