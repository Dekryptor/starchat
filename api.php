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
  // Before you ask, we wont pass a html stripper from this, since we dont want clients dealing with pain of striping &amp like things and stuff, and for web clients no harm will be done
  // Should i remove first 5 characters from contacts, since users first ever contact is probably just 5 ampersigns, but if we add contact removal this can become a tragedy
  echo $qcontact;
  exit();
}

if (isset($_GET["readmessages"])) {
  // to prevent reading of unauthorized files, we will check if valid id, like for example, some could input ../../../sensitiveinfo.php and get access to that, lets prevent this from happening
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
  // todo, extra important things to check if really is a valid id, for now above will work, but what if above fails, an error would probably get reported.
  // i know i sound paranoid or something, but someday, someone will figure out how to bypass this, thats when maximum security is needed
  // i mean cmon, some things have mysql passwords in plain text, a mysql password found is a whole life wasted
  die(htmlspecialchars(file_get_contents("convos/.ht".$_GET["readmessages"])));
}

if (isset($_GET["sendmessage"])) {
  // the contents of sendmessage contain the message text that we want to send
  if(preg_match('/[a-zA-Z0-9\-]{3,40}$/', $_GET["sendmessageto"])) {
    // yep, seems safe enough
  }else{
    echo "8";
    exit();
  }
  // i probably should strip out mysql commands, but this doesnt get passed anywhere through mysql, so for now this basically allows you to use commas and fancy characters
  // for future commiters, remember this, messages do not get sent through mysql, only strip html special characters

  // good idea to put timestamp but im not too sure yet, i will do a community vote and we will see what is best once this project gets popular
  $safemess = "\n".htmlspecialchars($qusername).": ".htmlspecialchars($_GET["sendmessage"]);

  if(file_exists("convos/.ht".$_GET["sendmessageto"])) {
    // we need this to make sure file exists, since the person most likely created a conversation already
    // lets move on
  }else{
    echo "4";
    exit();
  }

  file_put_contents("convos/.ht".$_GET["sendmessageto"], file_get_contents("convos/.ht".$_GET["sendmessageto"]).$safemess);
  echo "0";
  exit();
}

// preferably, we will try to keep conversations in one box, so if one person deletes the convo, it deletes it for other user as well
// $filename = $qanonid . "......." . $qusername . "......." . htmlspecialchars($_GET["contacter"]) + ".txt";

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

// $conn->real_escape_string($_GET["username"]);
// The code below will probably make you throw up
if(preg_match('/[a-zA-Z0-9]/', $_GET["username"])) {
}else{
  $conn->close();
  echo "2";
  exit();
}
$current = $conn->query("SELECT contacts FROM accounts WHERE id = '".$conn->real_escape_string($qid)."'");
$current = $current->fetch_array(MYSQLI_NUM);
$vale = generateRandomString();
$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($surl)."|||||".$vale."' WHERE id = '".$conn->real_escape_string($qid)."'");
// Most servers add a .ht* by default to the apache file blacklist, this makes sure nobody can rotate scan through conversations (blocking access for plaintext), i bet its probably pretty easy to do
file_put_contents("convos/.ht".$vale, "Conversation has been created! Nobody else can see these messages, only you and the other person.\n");
$conn->query("UPDATE accounts SET contacts = '".$conn->real_escape_string($current[0])."&&&&&".$conn->real_escape_string($qusername)."|||||".$vale."' WHERE firstname = '".$conn->real_escape_string($_GET["addcontact"])."'");
// First we add the url, then the username
$conn->close();
echo "0";
exit();

}

?>
