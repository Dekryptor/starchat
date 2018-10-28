<?php

include '../../mysqlinfo.php';
// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starchat");
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$doesexist = $conn->query("SELECT id, firstname, password, anonid, contacts FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["username"])."'");
$row = $doesexist->fetch_array(MYSQLI_NUM);
if (password_verify($_GET["password"],$row[2])) {
	// logged in, move on
	$qid = $row[0];
	$qusername = $row[1];
	$qpassword = $row[2];
	$qanonid = $row[3];
	$qcontact = $row[4];
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
	/*
	$splitcontact = preg_split("/&&&&&/",$qcontact);
	$i = 0;
	foreach ($splitcontact as $schold) {
		$toconvert = preg_split("/|||||/", $splitcontact[$i]);
		$splitcontact[$i] = $toconvert;
		$i++;
	}
	 */
	// $scjson = json_encode($splitcontact, JSON_PRETTY_PRINT);
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
	if(preg_match('/\.php$/', $_GET["readmessages"])) {
		echo "12";
		exit();
	}else{
		// if user bypasses, at least check if .php is disabled, most sensitive info for the most part is in php files
	}

	$readmessage = $_GET["readmessages"];
	$chatid = $conn->real_escape_string(htmlspecialchars($readmessage));
	$messages = $conn->query("SELECT * FROM messages WHERE chatid='$chatid'");
	if ($messages->num_rows > 0) {
		while($row = $messages->fetch_assoc()) {
			if ($row["username"] == "x") {
				echo "\n".$row["message"];
			}else{
				echo "\n[".$row["dt"]."] ".$row["username"].": ".$row["message"];

			}
		}
	}else{
		echo "Nothing but crickets, Say Hello";
	}
	exit();
}

if (isset($_GET["sendmessage"])) {
	// the contents of sendmessage contain the message text that we want to send
	if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["sendmessageto"])) {
		// yep, seems safe enough
	}else{
		echo "8";
		exit();
	}

	$chatid = $conn->real_escape_string(htmlspecialchars($_GET["sendmessageto"]));
	$qusernamef = $conn->real_escape_string($qusername);
	$messagef = $conn->real_escape_string(htmlspecialchars($_GET["sendmessage"]));

	$conn->query("INSERT INTO messages (chatid, username, message) VALUES ('$chatid', '$qusernamef', '$messagef')"); // MYSQL generates timestamps for us
	echo "0";
	exit();
}

if (isset($_GET["addcontact"])) {

	$surl = $_GET["addcontact"];

	$quickcheck = $conn->query("SELECT firstname, id FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["addcontact"])."'");

	$somedata = $quickcheck->fetch_array(MYSQLI_NUM);

	if (count($somedata)>0) {
		// move on to next code
	}else{
		echo "1";
		exit();
	}

	if(preg_match('/[a-zA-Z0-9]/', $_GET["username"])) {
	}else{
		$conn->close();
		echo "2";
		exit();
	}
	$vale = generateRandomString();
	$messagesa = $conn->query("SELECT * FROM accounts");
	if ($messagesa->num_rows > 0) {
		while($row = $messagesa->fetch_assoc()) {
			if (preg_match("/$vale/", $row["contacts"])) {
				die("Please rerun this, this chat id has been used before");
			}
		}
	}
	$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($qid)."'");
	$current = $current->fetch_array(MYSQLI_NUM);


	$current2 = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($_GET["addcontact"])."'");
	$current2 = $current->fetch_array(MYSQLI_NUM);

	$squrl = $conn->real_escape_string($surl);
	$squsername = $conn->real_escape_string($qusername);
	$saddcontact = $conn->real_escape_string($_GET["addcontact"]);
	$svale = $conn->real_escape_string($vale); // Not really needed but ensures extra security

	$current = json_decode($current[0]);
	$amo = count($current);
	$current[$amo][0] = $squrl;
	$current[$amo][1] = $vale;

	$current2 = json_decode($current2[0]);
	$amo = count($current2);
	$current2[$amo][0] = $squsername;
	$current2[$amo][1] = $vale;

	$current = $conn->real_escape_string(json_encode($current));
	$current2 = $conn->real_escape_string(json_encode($current2));

	$conn->query("UPDATE accounts SET contacts = '$current' WHERE id = '".$conn->real_escape_string($qid)."'");
	$conn->query("UPDATE accounts SET contacts = '$current2' WHERE firstname = '$saddcontact'");

/*
	$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($surl)."|||||".$vale."' WHERE id = '".$conn->real_escape_string($qid)."'");
	$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($qusername)."|||||".$vale."' WHERE firstname = '".$conn->real_escape_string($_GET["addcontact"])."'");
 */
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
