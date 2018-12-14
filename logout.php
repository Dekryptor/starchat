<?php
session_start();
session_unset();
session_destroy();
unset($_COOKIE["stoken"]);
setcookie('stoken', null, -1, '/');
header("Location: index.php");
?>
