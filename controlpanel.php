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


?>


 <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>MeiBaua - Bestellungen</title>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
 <link rel = "stylesheet"
         href = "https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
    </head>

    <body>




<form id="form1" action="#" method="post">

	<div id="personal" class="personalInfo" style="margin: 0 auto; height: 650px;">
		<label>Produktname</label>
		<br>
		<input class="personalInfoInput" type="text" id="product" name="product" value="Pappenheimersalat" required>
		<br>
		<br>

		<label>Preis:</label>
		<br>
		<input class="personalInfoInput" type="text" id="price" name="price" value="4" required>
		<br>
		<br>

		<label>Einheit:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="type" value="Gramm" required onchange="update()">
		<br>
		<br>

		<label>Verfuegbar ja/nein:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="available" value="ja" required onchange="update()">
		<br>
		<br>

		<label>Infotext:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="info" value="info">
		<br>
		<br>

	<div id="addProductDiv">
        <button id="morebtn" type="action" name="add">Produkt hinzufügen</button>
    </div>

	<div id="addProductDiv">
        <button id="morebtn" type="action" name="change">Produkt aendern</button>
    </div>
							

</form>


<?php

if(isset($_POST["add"]))
	addProduct();
else if(isset($_POST["change"]))
	changeProduct();


function analyzeButtons($value) {

	//if $value doesnt contain "cancel" the returnval is 'false'
	if(strcmp($value, "add") == True)
		addProduct();
	else 
		changeProduct();

}

function addProduct() {

	$product = $_POST["product"];
	$price = $_POST["price"];
	$type = $_POST["type"];
	$available = $_POST["available"];
	if(strcmp($available, "ja") == 0)
		$available = 1;
	else
		$available = 0;

	$info = $_POST["info"];

	addColumn($product); //Add new column 'nameOfNewProduct' to orders-table


  $sql = "INSERT INTO products (product, price, type, available, info) VALUES
                ('$product', '$price', '$type', '$available', '$info')";

        return executeQuery($sql);

}

//right now it only changes the availability to 'not available' (0) and the info-text
function changeProduct() {

	$product = $_POST["product"];
	$available = $_POST["available"];
	if(strcmp($available, "ja") == 0)
         $available = 1;
     else
         $available = 0;

	$info = $_POST["info"];

	$sql = "UPDATE `products` SET `available` = '" . $available . "', `info` = '" . $info. "' WHERE `product` = '" . $product . "'";

    return executeQuery($sql);

}


function executeQuery($sql) {

    $createdID = -1;
  global $conn;

  if($conn->query($sql) === TRUE) {
     $createdID = $conn->insert_id;
  } else {
     echo "Error: " . $sql . "<br>" . $conn->error;
  }

  return $createdID;

}


function addColumn($name) {

	$lastAddedProduct = getLastAddedProduct();

	global $conn;
	$sql = "ALTER TABLE `orders` ADD `" . $name . "` INT NOT NULL DEFAULT '0' AFTER `" . $lastAddedProduct . "`";
	 if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }


}

function getLastAddedProduct() {

	global $conn;


  $sql = "SELECT product FROM products ORDER BY productID DESC LIMIT 1";
  $result = $conn->query($sql);

  if($row = $result->fetch_assoc())
     return $row['product'];

  return -1;


}









?>



</div>

    </body>

    </html>


