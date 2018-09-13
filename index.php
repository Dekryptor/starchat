<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat</title>
<style>
body {
animation-name: look;
animation-duration: 1s;
margin:0;
background-color: #00b33c;
color: #ffffff;
font-family: arial;
box-shadow: inset 0 -15px 3px -20px #000000;
}
@keyframes look {
0% { background-color: #000000; }
100% { background-color: #00b33c; }
}
.buttona {
background-color: #00b33c;
color: #ffffff;
border: 3px solid #ffffff;
padding: 15px;
padding-top: 5px;
padding-bottom: 5px;
transition: .5s;
}
.buttona:hover {
background-color: #ffffff;
color: #000000;
}
.login {
position: absolute;
left: calc(50% - 180px);
padding: 20px;
text-align: center;
border-left: 5px solid #006622;
top: calc(50% - 100px);
}
</style>
</head>
<body>
<div class="starcat">Starcat</div>
<div class="login">
Username: <input type="text" id="username"><br><br>
Password: <input type="text" id="password"><br><br>
<input type="button" value="Login" class="buttona">
</div>
</body>
</html>
