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

$filename = $qanonid + "......." + $qusername + "......." + $qpassword + "......." + htmlspecialchars($_GET["reciever"]) + ".txt";

if (isset($_GET["addcontact"]) && isset($_GET["url"])) {

$surl = $_GET["url"];

if (filter_var($surl, FILTER_VALIDATE_URL) !== false) {
$res = file_get_contents($surl+"api.php?isrealuser="+htmlspecialchars($_GET["addcontact"]));
if ($res == "yes") {
  // User exists
  if (preg_match('/[a-zA-Z0-9\_]/', $conn->real_escape_string($res))) {
    // Matches well
  }else{
    $conn->close();
    die("Invalid username, probably an untrusted server, avoid it");
    exit();
  }
}else{
  $conn->close();
  die("User does not exists on the responding server, Also, make sure to not append a slash to you're url");
  exit();
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
$conn->query("UPDATE accounts SET contacts = '".$current[0]."|||||".$conn->real_escape_string($surl)."&&&&&".$conn->real_escape_string(substr(htmlspecialchars($_GET["addcontact"]), "0", "30"))."' WHERE id = '".$conn->real_escape_string($qid)."'");
// First we add the url, then the username
$conn->close();
die("Success");
} else {
  $conn->close();
die("Not a valid URL");
exit();
}

}

?>
