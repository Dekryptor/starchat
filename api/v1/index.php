<?php

// Starchat API v0.8.0

require '../../mysqlinfo.php';
// Check if connection works
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

function starchat_error($message) {
	$conn->close();
	die($message);
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

// bumpedDate is really useful when dealing with tokens
function bumpedDate() {
	$datetime = new DateTime('tomorrow');
	return $datetime->format('Y-m-d H:i:s');
}

$check_login = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
$check_login->bind_param('s', $_GET["username"]);
$check_login->execute();
$check_login_results = $check_login->get_result();
$check_login_numrows = $check_login_results->num_rows;


if ($check_login_numrows >= 0) {
	while($row = $check_login_results->fetch_assoc()) {
		if (password_verify($_GET["password"],$row['password'])) {
			// User is logged in
			$qid = $row['id'];
			$qusername = $row['username']; // Returns username
			$qpassword = $row['password']; // Returns our encrypted password
			$qanonid = $row['anonid']; // Returns anonymous id
			$qcontact = $row['contacts']; // Returns our json contacts
			
			// At this point the only thing the user can do is generate a token.
			if (isset($_GET["gentoken"])) {
				$token = generateRandomString();
				$token_date = date("Y-m-d H:i:s");
				$token_exp_date = bumpedDate();
				
				$token_check = $conn->prepare("SELECT token FROM tokens WHERE token=?");
				$token_check->bind_param('s', $token);
				$token_check->execute();
				$token_check_results = $token_check->get_result();
				$token_check_rows = $token_check_results->num_rows;
				
				if ($token_check_rows > 0) {
					starchat_error("Token has been used, please try again.");
				}

				$token_query = $conn->prepare("INSERT INTO tokens (token, creation, expiration) VALUES (?, ?, ?)");
				$token_query->bind_param('sss', $token, $token_date, $token_exp_date);
				$token_query->execute();
			}
		}else{
			if (isset($_GET["token"])) {
				$token_use = $conn->prepare("SELECT * FROM tokens WHERE token=?");
				$token_use->bind_param('s', $token);
				$token_use->execute();
				$token_use_results = $token_use->get_result();
				$token_use_rows = $token_use_results->num_rows;
				
				if ($token_use_rows > 0) {
					while($row = $token_use_results->fetch_assoc()) {
						
					}
				}
			}

			//starchat_error("Starchat Error: Login failed");
		}
	}
}




header('Content-type: application/json');



if (isset($_GET["getcontacts"])) {
	echo $qcontact;
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

	$checkx = $conn->prepare("SELECT * FROM messages WHERE chatid = ?");
	$checkx->bind_param('s', $readmessage);
	$checkx->execute();
	$keep = $checkx->get_result();
	
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

/*
if (isset($_GET["addtoconvo"])) {

	$surl = $_GET["addtoconvo"];

	$quickcheck = $conn->query("SELECT username, id FROM accounts WHERE username = '".$conn->real_escape_string($_GET["addtoconvo"])."'");

	$somedata = $quickcheck->fetch_array(MYSQLI_NUM);

	if (count($somedata)>0) {
		// move on to next code
	}else{
		echo "1";
		exit();
	}

	// $conn->real_escape_string($_GET["username"]);
	// The code below will probably make you throw up
	if(preg_match('/[a-zA-Z0-9]/', $_GET["username"])) {
	}else{
		$conn->close();
		echo "2";
		exit();
	}

	// Addtoconvo is the person we want to add, usual username
	// convoid is the conversation id, we need to verify it exists in the current users contact so we cant just add random people to random conversations

	$contactlist = $conn->query("SELECT contacts FROM accounts WHERE username = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
	$contactlista = $conn->query("SELECT contacts FROM accounts WHERE username = '".$conn->real_escape_string($qusername)."'");

	$contactlist = $contactlist->fetch_array(MYSQLI_NUM);
	$contactlista = $contactlist->fetch_array(MYSQLI_NUM);

	if (strpos($contactlist[0], "|||||".$_GET["convoid"]."&&&&&") === false) {
		die("32");
	}else{
		if (strpos($contactlista[0], "|||||".$_GET["convoid"]."&&&&&") === true) {
			$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
			$current = $current->fetch_array(MYSQLI_NUM);
			$conn->query("INSERT INTO messages (chatid, username, message) VALUES ('".$conn->real_escape_string($_GET["convoid"])."', 'x', '".$conn->real_escape_string($_GET["addtoconvo"])." has been added to this conversation')"); // MYSQL generates timestamps for us
			$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($qusername." GC")."|||||".$conn->real_escape_string($_GET["convoid"])."' WHERE username = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
		}else{
			die("32");
		}
	}

	$conn->close();
	echo "0";
	exit();

}
 */


?>
