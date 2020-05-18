<?php

error_reporting(E_ALL);
ini_set('display_errors', 1); 

$servername = "remotemysql.com";
$username = "rdo8BYEQqz";
$password = "EO2wg10w9L";
$dbname = "rdo8BYEQqz";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['submitbtn']))
		checkCredentials();


function checkCredentials() {

global $conn;

$username = $_POST['username'];
$password = $_POST['password'];


 $result = $conn->query("SELECT * FROM users WHERE username='" . $username . "'");

 if($row = $result->fetch_assoc()) {

		 $userpass = $row['password'];
		 if(strcmp($userpass, $password) == 0) {
				 session_start();
				 $_SESSION["loggedIn"] = true;
				 header("Location: delivery.php");
		 }
		 else
		 	echo "Das Password ist ungültig!";

 }
 else 
		 echo "Der Benutzername ist ungültig!";

}

?>
<!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>MeiBauer - Login</title>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
 <link rel = "stylesheet"
         href = "https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
    </head>

    <body>

        <h1 style="text-align: center; margin-top: 30px; margin-bottom: 20px;">Login</h1>
<form method="post" action="#" style="
	padding: 20px;
    background: #f2f2f2;
    width: 248px;
    margin: 0 auto;
    height: 288px;
    border: 2px solid #ddd;
    border-radius: 11px;
"
>

<label>Benutzername:</label>
<br>
<input style="width:200px;"type="text" id="uername" name="username" required placeholder="username ist 'test'">
<br>
<br>

<label>Passwort:</label>
<br>
<input style="width: 200px;" type="password" id="password" name="password" required placeholder="passwort ist 'test'">


<button style="color: black; width: 200px; margin-top:54px" type="action" id="submitbtn" name="submitbtn">Lemme in</button>

</form>
        <br>
        <br>

