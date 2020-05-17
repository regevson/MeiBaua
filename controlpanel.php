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
        <title>MeiBaua - ControlPanel</title>
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
		<input class="personalInfoInput" type="text" id="product" name="product" placeholder="Gruener_Salat" required>
		<br>
		<br>

		<label>Preis:</label>
		<br>
		<input class="personalInfoInput" type="text" id="price" name="price" placeholder="4.50" required>
		<br>
		<br>

		<label>Einheit:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="unit" placeholder="Gramm" required>
		<br>
		<br>

		<label>Produktart:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="type" placeholder="Obst" required>
		<br>
		<br>

		<label>Verfuegbar ja/nein:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="available" value="1" placeholder="1, 0, oder -1" required>
		<br>
		<br>

		<label>Infotext:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="info" placeholder="currently not used">
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
	$unit = $_POST["unit"];
	$available = $_POST["available"];
	$info = $_POST["info"];

	addColumn($product); //Add new column 'nameOfNewProduct' to orders-table


  $sql = "INSERT INTO products (product, price, type, unit, available, info) VALUES
                ('$product', '$price', '$type', '$unit', '$available', '$info')";

        return executeQuery($sql);

}

//right now it only changes the availability to 'not available' (0) and the info-text
function changeProduct() {

	$product = $_POST["product"];
	$price = $_POST["price"];
	$type = $_POST["type"];
	$unit = $_POST["unit"];
	$available = $_POST["available"];
	$info = $_POST["info"];
	
	$sql = "UPDATE `products` SET `price` = '$price', `type` = '$type', `unit` = '$unit', `available` = '$available', 
		`info` = '$info' WHERE `product` = '" . $product . "'";

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
            echo "Column added successfully";
        } else {
            echo "Error adding column: " . $conn->error;
        }


}

function getLastAddedProduct() {

	global $conn;


  $sql = "SELECT product FROM products ORDER BY productID DESC LIMIT 1";
  $result = $conn->query($sql);

  if($row = $result->fetch_assoc())
     return $row['product'];
  else
	  return 'orderID'; // if no product is in products-table, then add new product (in orders-table) after "orderID"-column!

}

?>



</div>

    </body>

    </html>


