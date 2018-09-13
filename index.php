<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat</title>
<style>
body {
animation-name: look;
animation-duration: 1s;
margin:0;
background-color: #f3f3f3;
color: #000000;
font-family: arial;
}
@keyframes look {
0% { background-color: #000000; }
100% { background-color: #f3f3f3; }
}
.buttona {
background-color: #ffffff;
color: #000000;
border: 1px solid #000000;
padding: 15px;
padding-top: 5px;
padding-bottom: 5px;
transition: .5s;
}
.buttona:hover {
background-color: #000000;
color: #ffffff;
}
.login {
position: absolute;
left: calc(50% - 180px);
padding: 20px;
background-color: #ffffff;
text-align: center;
border-radius: 10px;
box-shadow: 0px 2px 5px #000000;
top: calc(50% - 100px);
}
.textbox {
padding: 8px;
background-color: #ffffff;
color: #000000;
border: 1px solid #000000;
border-radius: 3px;
}
.starcat {
position: absolute;
left: 15px;
top: 15px;
animation-name: fallin;
animation-duration: 1s
}
@keyframes fallin {
0% { left: 15px; top: -150px; }
100% { left: 15px; top: 15px; }
}
</style>
</head>
<body>
<img src="img/logo.png" width="65" height="66" class="starcat">
<div class="login">
Username: <input type="text" id="username" class="textbox"><br><br>
Password: <input type="text" id="password" class="textbox"><br><br>
<input type="button" value="Login" class="buttona">
</div>
</body>
</html>
