<?php

// Starchat API v0.7.2

include '../../mysqlinfo.php';
// Check if connection works
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$checkg = $conn->prepare("SELECT id, username, password, anonid, contacts FROM accounts WHERE username = ?");
$checkg->bind_param('s', $_GET["username"]);
$checkg->execute();
$resa = $checkg->get_result();
$numrow = $resa->num_rows;


if ($numrow >= 0) {
while($row = $resa->fetch_assoc()) {
if (password_verify($_GET["password"],$row['password'])) {
	// logged in, move on
	$qid = $row['id'];
	$qusername = $row['username'];
	$qpassword = $row['password']; // Returns our encrypted password
	$qanonid = $row['anonid'];
	$qcontact = $row['contacts'];
}else{
	$conn->close();
	echo "6";
	exit();
}
}
}

// TODO move everything, even errors, to json, 0.8
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
		echo "8";
		exit();
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
	echo "0";
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

// TODO version 0.8

?>
