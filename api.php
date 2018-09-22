<?php

include 'mysqlinfo.php';
// Create connection
$conn = new mysqli($mysqlurl, $user, $pass, "starcat");
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
die("Login Failure");
exit();
}

// we need the function below to store conversation id
function generateRandomString($length = 25) {
    $characters = '0123456789!?-+=abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// preferably, we will try to keep conversations in one box, so if one person deletes the convo, it deletes it for other user as well
$filename = $qanonid . "......." . $qusername . "......." . htmlspecialchars($_GET["contacter"]) + ".txt";

if (isset($_GET["addcontact"])) {

$surl = $_GET["addcontact"];

$quickcheck = $conn->query("SELECT firstname, id FROM accounts WHERE firstname = '".$conn->real_escape_string($_GET["addcontact"])."'");

$somedata = $quickcheck->fetch_array(MYSQLI_NUM);

if (count($somedata)>0) {
  // move on
}else{
  die("User non-existant");
}

// $conn->real_escape_string($_GET["username"]);
// The code below will probably make you throw up
if(preg_match('/[a-zA-Z0-9]/', $_GET["username"])) {
}else{
  $conn->close();
  die("Name contains illegal characters");
  exit;
}
$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($qid)."'");
$current = $current->fetch_array(MYSQLI_NUM);
$conn->query("UPDATE accounts SET contacts = '".$current[0]."&&&&&".$conn->real_escape_string($surl)."|||||".generateRandomString()."' WHERE id = '".$conn->real_escape_string($qid)."'");
// First we add the url, then the username
$conn->close();
die("Success");

}

?>
