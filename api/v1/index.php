<?php

include '../../mysqlinfo.php';
// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starchat");
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$checkg = $conn->prepare("SELECT id, firstname, password, anonid, contacts FROM accounts WHERE firstname = ?");
$checkg->bind_param('s', $_GET["username"]);
$checkg->execute();
$row = $checkg->get_result();

if (password_verify($_GET["password"],$row['password'])) {
	// logged in, move on
	$qid = $row['id'];
	$qusername = $row['firstname'];
	$qpassword = $row['password']; // Returns our encrypted password
	$qanonid = $row['anonid'];
	$qcontact = $row['contacts'];
}else{
	$conn->close();
	echo "6";
	exit();
}

// TODO move everything, even errors, to json
header('Content-type: application/json');

// we need the function below to store conversation id
function generateRandomString($length = 25) {
	$characters = '0123456789-abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

if (isset($_GET["getcontacts"])) {
	echo $qcontact;
	exit();
}

if (isset($_GET["readmessages"])) {
	if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["readmessages"])) {
		// yep, seems safe enough
	}else{
		echo "15";
		exit();
	}
	$readmessage = $_GET["readmessages"];

	$checkx = $conn->prepare("SELECT * FROM accounts WHERE chatid = ?");
	$checkx->bind_param('s', htmlspecialchars($readmessage));
	$checkx->execute();
	$checkx->store_result();

	if ($checkx->num_rows > 0) {
		while($row = $checkx->fetch_assoc()) {
			if ($row["username"] == "x") {
				echo "\n".$row["message"];
			}else{
				echo "\n[".$row["dt"]."] ".$row["username"].": ".$row["message"];

			}
		}
	}else{
		echo "Seems empty in here, say something!";
	}
	exit();
}

if (isset($_GET["sendmessage"])) {
	// TODO Verify chat id exists, security risk
	if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["sendmessageto"])) {
		// yep, seems safe enough
	}else{
		echo "8";
		exit();
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

	$checkf = $conn->prepare("SELECT firstname, id FROM accounts WHERE firstname = ?");
	$checkf->bind_param('s', $_GET["addcontact"]);
	$checkf->execute();
	$quickcheck = $checkf->get_result();

	if (count($quickcheck)>0) {
		// move on to next code
	}else{
		echo "1";
		exit();
	}

	if(preg_match('/[a-zA-Z0-9]/', $_GET["addcontact"])) {
	}else{
		$conn->close();
		echo "2";
		exit();
	}
	$vale = generateRandomString();
	$messagesa = $conn->query("SELECT * FROM accounts"); // For something as simple as this, we should be fine to use query
	if ($messagesa->num_rows > 0) {
		while($row = $messagesa->fetch_assoc()) {
			if (preg_match("/$vale/", $row["contacts"])) {
				die("Please rerun this, this chat id has been used before");
			}
		}
	}
	$checka = $conn->prepare("SELECT contacts FROM accounts WHERE id = ?");
	$checka->bind_param('s', $qid);
	$checka->execute();
	$current = $checka->get_result();


	$checkc = $conn->prepare("SELECT contacts FROM accounts WHERE id = ?");
	$checkc->bind_param('s', $_GET["addcontact"]);
	$checkc->execute();
	$current2 = $checka->get_result();

	$saddcontact = htmlspecialchars($_GET["addcontact"]);

	$current = json_decode($current['contacts'],true);
	$amo = count($current);
	$current[$amo][0] = $surl;
	$current[$amo][1] = $vale;

	$current2 = json_decode($current2['contacts'],true);
	$amo = count($current2);
	$current2[$amo][0] = $qusername;
	$current2[$amo][1] = $vale;

	$current = $conn->real_escape_string(json_encode($current, JSON_PRETTY_PRINT));
	$current2 = $conn->real_escape_string(json_encode($current2, JSON_PRETTY_PRINT));
	$cu1 = $conn->prepare("UPDATE accounts SET contacts = ? WHERE id = ?");
	$cu1->bind_param('ss', $current, $qid);
	$cu1->execute();

	$cu2 = $conn->prepare("UPDATE accounts SET contacts = ? WHERE firstname = ?");
	$cu2->bind_param('ss', $current2, $saddcontact);
	$cu2->execute();

	$conn->close();
	echo "0";
	exit();

}

/*
if (isset($_GET["addtoconvo"])) {

	$surl = $_GET["addtoconvo"];

	$quickcheck = $conn->query("SELECT firstname, id FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["addtoconvo"])."'");

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

	$contactlist = $conn->query("SELECT contacts FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
	$contactlista = $conn->query("SELECT contacts FROM accounts WHERE firstname = '".$conn->real_escape_string($qusername)."'");

	$contactlist = $contactlist->fetch_array(MYSQLI_NUM);
	$contactlista = $contactlist->fetch_array(MYSQLI_NUM);

	if (strpos($contactlist[0], "|||||".$_GET["convoid"]."&&&&&") === false) {
		die("32");
	}else{
		if (strpos($contactlista[0], "|||||".$_GET["convoid"]."&&&&&") === true) {
			$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
			$current = $current->fetch_array(MYSQLI_NUM);
			$conn->query("INSERT INTO messages (chatid, username, message) VALUES ('".$conn->real_escape_string($_GET["convoid"])."', 'x', '".$conn->real_escape_string($_GET["addtoconvo"])." has been added to this conversation')"); // MYSQL generates timestamps for us
			$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($qusername." GC")."|||||".$conn->real_escape_string($_GET["convoid"])."' WHERE firstname = '".$conn->real_escape_string($_GET["addtoconvo"])."'");
		}else{
			die("32");
		}
	}

	$conn->close();
	echo "0";
	exit();

}
 */

// TODO rewrite the addtoconvo function

?>
