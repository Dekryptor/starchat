<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat</title>
<style>
body {
animation-name: look;
animation-duration: 1s;
margin:0;
background-color: #0099ff;
color: white;
font-family: arial;
}
@keyframes look {
0% { background-color: #000000; }
100% { background-color: #000066; }
}
.buttona {
background-color: #0099ff;
color: #ffffff;
border: 3px solid #ffffff;
padding: 20px;
padding-top: 10px;
padding-bottom: 10px;
transition: .5s;
}
.buttona:hover {
background-color: #ffffff;
color: #000000;
}
.login {
position: absolute;
left: 50px;
margin: 20px;
border-left: 5px solid #003d66;
top: calc(50% - 250px);
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
