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




<?php

$pNames = array();
$pPrices = array();
$pTypes = array();
$pUnits = array();
$pAvailability = array();
$pImgName = array();
$pInfo = array();

downloadProducts();

function downloadProducts() {
    
    // this info gets passed to JavaScript
	global $pNames;
	global $pPrices;
	global $pTypes;
	global $pUnits;
	global $pAvailability;
	global $pImgName;
	global $pInfo;

    global $conn;
    $result = $conn->query("SELECT * FROM products");
    
    while ($row = $result->fetch_assoc()) {
		$pNames[] = $row['product'];
		$pPrices[] = $row['price'];
		$pTypes[] = $row['type'];
		$pUnits[] = $row['unit'];
		$pAvailability[] = $row['available'];
		$pImgName[] = $row['imgName'];
		$pInfo[] = $row['info'];
	}
    
}


?>
<form id="form1" action="#" method="post">

	<div id="personal" class="personalInfo" style="margin: 0 auto; height: auto; overflow: auto;">
		<label>Produkt aendern:</label>
		<br>
		<select id="products" class="products custom-select" name="products" onchange="fillFields()"></select>
		<br>
		<br>

		<label>Neues Produkt:</label>
		<br>
		<input id="newproduct" type="text" class="personalInfoInput" name="newproduct" value="" placeholder="Name des neuen Produkts">
		<br>
		<br>

		<label>Preis:</label>
		<br>
		<input class="personalInfoInput" type="text" id="price" name="price" placeholder="4.50" required>
		<br>
		<br>

		<label>Einheit:</label>
		<br>
		<input class="personalInfoInput" type="text" id="unit" name="unit" placeholder="Gramm" required>
		<br>
		<br>

		<label>Produktart:</label>
		<br>
		<input class="personalInfoInput" type="text" id="type" name="type" placeholder="Obst" required>
		<br>
		<br>

		<label>Verfuegbar ja/nein:</label>
		<br>
		<input class="personalInfoInput" type="text" id="availability" name="available" placeholder="1, 0, oder -1" required>
		<br>
		<br>

		<label>Bildname:</label>
		<br>
		<input class="personalInfoInput" type="text" id="imgname" name="imgname" placeholder="gruener_kopfsalat">
		<br>
		<br>

		<label>Infotext:</label>
		<br>
		<input class="personalInfoInput" type="text" id="info" name="info" placeholder="currently not used">
		<br>
		<br>

	<div id="addProductDiv" style="margin-bottom: 20px;">
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


function addProduct() {

	$product = $_POST["newproduct"];
	$price = $_POST["price"];
	$type = $_POST["type"];
	$unit = $_POST["unit"];
	$available = $_POST["available"];
	$imgName = $_POST["imgname"];
	$info = $_POST["info"];

  $sql = "INSERT INTO products (product, price, type, unit, available, imgName, info) VALUES
                ('$product', '$price', '$type', '$unit', '$available', '$imgName', '$info')";

        return executeQuery($sql);

}

function changeProduct() {

	$product = $_POST["products"];
	$price = $_POST["price"];
	$type = $_POST["type"];
	$unit = $_POST["unit"];
	$available = $_POST["available"];
	$imgName = $_POST["imgname"];
	$info = $_POST["info"];
	
	$sql = "UPDATE `products` SET `price` = '$price', `type` = '$type', `unit` = '$unit', `available` = '$available', 
		`imgName` = '$imgName', `info` = '$info' WHERE `product` = '" . $product . "'";

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

echo "<meta http-equiv='refresh' content='0'>";
  return $createdID;

}

?>



</div>


	
<script>

// arrays get combined to structs "product"
var pNames = <?php echo json_encode($pNames); ?> ; 
var pPrices = <?php echo json_encode($pPrices); ?> ; 
var pTypes = <?php echo json_encode($pTypes); ?> ;
var pUnits = <?php echo json_encode($pUnits); ?> ;
var pAvailability = <?php echo json_encode($pAvailability); ?> ;
var pImgName = <?php echo json_encode($pImgName); ?> ;
var pInfo = <?php echo json_encode($pInfo); ?> ;

var products = createStructs();
addOptions(products);
fillFields();

function createStructs() {

	function Product(name, price, type, unit, imgname) {
		this.name = name;
		this.price = price;
		this.type = type;
		this.unit = unit;
		this.imgname = imgname;
	}

	var products = [];
	for(var i = 0; i < pNames.length; i++) {
		products[i] = new Product(pNames[i], pPrices[i], pTypes[i], pUnits[i], pImgName[i]);
	}

	return products;

}


/* 
 * Add options (products) to option-dropdown
 */
function addOptions(products) {

	var uniqueTypes = Array.from(new Set(pTypes))

    var select = document.getElementById('products');

    for (var i = 0; i < uniqueTypes.length; i++) {
		var optGroup = document.createElement('OPTGROUP');
    	optGroup.label = uniqueTypes[i];

    	for (var j = 0; j < products.length; j++) {
			var product = products[j];
			if(uniqueTypes[i] == product.type) {
				var option = document.createElement('option');
				option.text = product.name;
				option.value = product.name;
				optGroup.appendChild(option);
			}
		}

		select.appendChild(optGroup);
    }

}

function fillFields() {

    var selectedProduct = document.getElementById('products');
	var selectedProductName = selectedProduct.value; // value of selected product (its name)
	var pIndex = pNames.indexOf(selectedProductName);

    document.getElementById('newproduct').value = "";
    document.getElementById('price').value = pPrices[pIndex];
    document.getElementById('unit').value = pUnits[pIndex];
    document.getElementById('type').value = pTypes[pIndex];
    document.getElementById('availability').value = pAvailability[pIndex];
    document.getElementById('imgname').value = pImgName[pIndex];
    document.getElementById('info').value = pInfo[pIndex];
	

}




</script>

    </body>

    </html>


