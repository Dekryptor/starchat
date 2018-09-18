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

if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
$res = file_get_contents($surl);
$res = $conn->real_escape_string($_GET["username"]);
// The code below will probably make you throw up
$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($qid)."'");
$current = $current->fetch_array(MYSQLI_NUM);
$conn->query("UPDATE accounts SET contacts = '".$current[0]."#####"./* we will do work here later*/"test"."' WHERE id = '".$conn->real_escape_string($qid)."'");
} else {
die("Not a valid URL");
exit();
}

}

?>
