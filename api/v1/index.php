<?php

// Starchat API v0.8.1-git

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

// Usually for debugging, but also hide html warnings and errors if the users config is set up strangely
error_reporting(0); // Set to E_ALL for error reporting
ini_set('display_errors', 0);

require '../../config.php';
// Check if connection works
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

function starchat_error($message) {
	die("Starchat Error: ".htmlspecialchars($message));
}

// We start with the most secure options then slowly drop, great for supporting older releases
function generateRandomString($length = 32) {
	if (function_exists("random_bytes")) {
		return bin2hex(random_bytes($length)); // Supported Random Generator
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		return bin2hex(openssl_random_pseudo_bytes($length)); // Supported Random Generator
	} else {
		starchat_error("Secure random number not generated, your PHP version is most likely out of date");
	}
}

// API Login System
if (isset($_GET["username"])) {
	$check_login = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
	$check_login->bind_param('s', $_GET["username"]);
	$check_login->execute();
	$check_login_results = $check_login->get_result();
	$check_login_numrows = $check_login_results->num_rows;


	if ($check_login_numrows >= 0) {
		while($row = $check_login_results->fetch_assoc()) {
			if (password_verify($_GET["password"],$row['password'])) {

				// At this point the only thing the user can do is generate a token.
				if (isset($_GET["gentoken"])) {
					$token = generateRandomString();
					$token_date = date("Y-m-d H:i:s");

					$token_check = $conn->prepare("SELECT token FROM tokens WHERE token=?");
					$token_check->bind_param('s', $token);
					$token_check->execute();
					$token_check_results = $token_check->get_result();
					$token_check_rows = $token_check_results->num_rows;

					if ($token_check_rows > 0) {
						starchat_error("Token has been used, please try again.");
					}

					$token_query = $conn->prepare("INSERT INTO tokens (token, username, created) VALUES (?, ?, ?)");
					$token_query->bind_param('sss', $token, $_GET["username"], $token_date);
					$token_query->execute();
					echo $token;
					$conn->close();
					exit();
				}
			}
		}
	}
}

if (isset($_GET["token"])) {
	$token_use = $conn->prepare("SELECT * FROM tokens WHERE token=?");
	$token_use->bind_param('s', $_GET["token"]);
	$token_use->execute();
	$token_use_results = $token_use->get_result();
	$token_use_rows = $token_use_results->num_rows;

	if ($token_use_rows == 1) {
		while($row = $token_use_results->fetch_assoc()) {
			if (strtotime($row["created"]) > strtotime("-24 hours")) {
				$retrieve_info = $conn->prepare("SELECT * FROM accounts WHERE username=?");
				$retrieve_info->bind_param('s', $row['username']);
				$retrieve_info->execute();
				$retrieve_info_results = $retrieve_info->get_result();
				$retrieve_info_rows = $retrieve_info_results->num_rows;

				if ($retrieve_info_rows == 1) {
					while($rowa = $retrieve_info_results->fetch_assoc()) {
						// User is logged in
						$qid = $rowa['id'];
						$qusername = $rowa['username']; // Returns username
						$qpassword = $rowa['password']; // Returns our encrypted password
						$qanonid = $rowa['anonid']; // Returns anonymous id
						$qcontact = $rowa['contacts']; // Returns our json contacts
					}
				}else{
					starchat_error("User of token no longer exists");
				}
			}else{
				starchat_error("Token Expired");
			}
		}
	}else{
		starchat_error("Token not found");
	}
}else{
	starchat_error("No token provided");
}

// API Features

header('Content-type: application/json');

if (!isset($qusername)) {
	starchat_error("Username not set, possibly API error");
}else{
	if (!isset($qpassword)) {
		starchat_error("Password not set, possibly API error");
	}
}

if (isset($_GET["getcontacts"])) {
	echo $qcontact;
	$conn->close();
	exit();
}

if (isset($_GET["readmessages"])) {
	if(!isset($_GET["count"])) {
		$read_count = 25;
	}
	if(!ctype_digit($_GET["count"])) {
		if($_GET["count"] != "all") {
			starchat_error("Value is not an integer or \"all\"");
		}else{
			$read_count = -1;
		}
	}else{
		$read_count = (int)$_GET["count"];
	}
	if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["readmessages"])) {
		// yep, seems safe enough
	}else{
		starchat_error("Value of readmessages is not a valid id");
	}
	$readmessage = htmlspecialchars($_GET["readmessages"]);

	if ($read_count == -1) {
		$checkx = $conn->prepare("SELECT * FROM messages WHERE chatid = ?");
		$checkx->bind_param('s', $readmessage);
		$checkx->execute();
		$keep = $checkx->get_result();
	}else{
		$checkx = $conn->prepare("(SELECT * FROM messages WHERE chatid = ? ORDER BY id DESC LIMIT ?) ORDER BY id ASC");
		$checkx->bind_param('si', $readmessage, $read_count);
		$checkx->execute();
		$keep = $checkx->get_result();
	}


	$mbuffer[0]["username"] = "!SYSTEM";
	$mbuffer[0]["datetime"] = "None";
	$mbuffer[0]["message"] = "Conversation Created.";

	if ($keep->num_rows > 0) {
		while($row = $keep->fetch_assoc()) {
			$y = count($mbuffer);
			$mbuffer[$y]["username"] = $row["username"];
			$mbuffer[$y]["datetime"] = $row["datetime"];
			$mbuffer[$y]["message"] = $row["message"];
		}
		echo json_encode($mbuffer, JSON_PRETTY_PRINT);
	}
	$conn->close();
	exit();
}

if (isset($_GET["sendmessage"])) {
	if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["sendmessageto"])) {
		// yep, seems safe enough
	}else{
		starchat_error("The sender conversation id is invalid");
	}

	$checka = $conn->prepare("SELECT contacts FROM accounts WHERE username = ?");
	$checka->bind_param('s', $qusername);
	$checka->execute();
	$cheeka = $checka->get_result();

	$confirm = 0;
	$vale = preg_replace("/^[a-zA-Z0-9\-]/","",$_GET["sendmessageto"]);

	if ($cheeka->num_rows > 0) {
		while($row = $cheeka->fetch_assoc()) {
			if (preg_match("/$vale/", $row["contacts"])) {
				$confirm = 1;
			}
		}
	}

	if ($confirm == 0) {
		die("Error: Chat ID is unused therefor not yours");
		starchat_error("Chat ID is not in your contacts list, or might not exist");
	}

	$chatid = htmlspecialchars($_GET["sendmessageto"]);
	$messagef = htmlspecialchars($_GET["sendmessage"]);
	$susername = htmlspecialchars($qusername);

	$getn = $conn->prepare("INSERT INTO messages (chatid, username, message) VALUES (?, ?, ?)");
	$getn->bind_param('sss', $chatid, $susername, $messagef);
	$getn->execute();

	echo "0";
	$conn->close();
	exit();
}

if (isset($_GET["addcontact"])) {

	$surl = $_GET["addcontact"];

	$checkf = $conn->prepare("SELECT username, id FROM accounts WHERE username = ?");
	$checkf->bind_param('s', $_GET["addcontact"]);
	$checkf->execute();
	$quickcheck = $checkf->get_result();

	if ($quickcheck->num_rows>0) {
		// move on to next code
	}else{
		echo "1";
		exit();
	}

	if(!preg_match('/[a-zA-Z0-9]/', $_GET["addcontact"])) {
		$conn->close();
		echo "2";
		exit();
		starchat_error("The username is not valid and contains invalid characters");
	}
	$vale = generateRandomString();
	$messagesa = $conn->query("SELECT * FROM accounts"); // For something as simple as this, we should be fine to use query
	if ($messagesa->num_rows > 0) {
		while($row = $messagesa->fetch_assoc()) {
			if (preg_match("/$vale/", $row["contacts"])) {
				starchat_error("Chat ID collision, please retry and add this contact again");
			}
		}
	}
	$checka = $conn->prepare("SELECT contacts FROM accounts WHERE id = ?");
	$checka->bind_param('s', $qid);
	$checka->execute();
	$current = $checka->get_result();


	$checkc = $conn->prepare("SELECT contacts FROM accounts WHERE username = ?");
	$checkc->bind_param('s', $_GET["addcontact"]);
	$checkc->execute();
	$current2 = $checkc->get_result();

	$saddcontact = htmlspecialchars($_GET["addcontact"]);

	while ($row = $current->fetch_array(MYSQLI_NUM)) {
		$currenta = json_decode($row[0],true);
		$amo = count($currenta);
		$currenta[$amo][0] = $surl;
		$currenta[$amo][1] = $vale;
	}

	while ($row = $current2->fetch_array(MYSQLI_NUM)) {
		$currentb = json_decode($row[0],true);
		$amo = count($currentb);
		$currentb[$amo][0] = $qusername;
		$currentb[$amo][1] = $vale;
	}

	$currenta = json_encode($currenta, JSON_PRETTY_PRINT);
	$currentb = json_encode($currentb, JSON_PRETTY_PRINT);
	$cu1 = $conn->prepare("UPDATE accounts SET contacts = ? WHERE id = ?");
	$cu1->bind_param('ss', $currenta, $qid);
	$cu1->execute();


	$cu2 = $conn->prepare("UPDATE accounts SET contacts = ? WHERE username = ?");
	$cu2->bind_param('ss', $currentb, $saddcontact);

	$cu2->execute();

	$conn->close();
	exit();
}

?>
