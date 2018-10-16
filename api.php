<?php

include 'mysqlinfo.php';
// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starchat");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doesexist = $conn->query("SELECT id, firstname, password, anonid, contacts FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["username"])."'");
$row = $doesexist->fetch_array(MYSQLI_NUM);
if ($row[2] == hash("sha256",$_GET["password"])) {
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
if (preg_match(/$vale/, $row["contacts"])) {
die("Please rerun this, this chat id has been used before");
}
}
}
$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($qid)."'");
$current = $current->fetch_array(MYSQLI_NUM);
$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($surl)."|||||".$vale."' WHERE id = '".$conn->real_escape_string($qid)."'");
$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($qusername)."|||||".$vale."' WHERE firstname = '".$conn->real_escape_string($_GET["addcontact"])."'");
// First we add the url, then the username
$conn->close();
echo "0";
exit();

}

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

?>
