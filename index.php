<!DOCTYPE html>
<html lang="en">
<head>
<title>Starcat</title>
<style>
body {
animation-name: look;
animation-duration: 1s;
margin:0;
background-color: #000066;
color: white;
font-family: arial;
}
@keyframes look {
0% { background-color: #000000; }
100% { background-color: #000066; }
}
.buttona {
background-color: #000066;
color: #ffffff;
border: 3px solid #ffffff;
padding: 20px;
transition: .5s;
}
.buttona:hover {
background-color: #ffffff;
color: #000000;
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
