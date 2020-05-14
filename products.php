<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "remotemysql.com"; // actually hosted by 000webhost
$username   = "rdo8BYEQqz";
$password   = "EO2wg10w9L";
$dbname     = "rdo8BYEQqz";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


/*
 * Download product-info (only of available products)
 * and make it globally available
 */
function downloadProducts($avail) {

    global $conn;

	$toNames        = array(); // names of products
	$prices         = array();
	$types			= array();
	$unit		    = array();
	$availability   = array();
	$info           = array();
   
    $result = $conn->query("SELECT * FROM products WHERE available='$avail'");
    
    while ($row = $result->fetch_assoc()) {
        $toNames[] = $row['product'];
        $price = $row['price'];
        $prices[] = sprintf('%0.2f', $price);
        $types[] = $row['type'];
        $unit[] = $row['unit'];
        $availability[] = $row['available'];
        $info[] = $row['info'];
    }

	if($avail == 1)
		printAvailableProducts($toNames, $prices, $types, $unit, $info);
	else if($avail == 0)
		printUnavailableProducts($toNames, $prices, $types, $unit, $info);
    
}

/*
else 

if($printAvailableProducts == false){
			$opacity = 0.6;
			$availhtml = '<span id="productH" class="productH" style="font-weight: bold; color: #802400;">Noch im Wachstum</span>';
		}
 */

function printAvailableProducts($toNames, $prices, $types, $unit, $info) {

		
	
	$productToType = create_ProductToType_Array($toNames, $types);
	$uniqueTypes = array_unique($types);

	for($x = 0; $x < sizeof($uniqueTypes); $x++) {
		echo '<div class="wrapper" style="height: auto; overflow: auto;">';
		echo '<span class="typesubheading">' . $types[$x] . '</span><br>';
		for($y = 0; $y < sizeof($toNames); $y++) {
			if($productToType[$toNames[$y]] == $uniqueTypes[$x]) {
					echo '<div id="duplicater" class="dup_products items">
						<span id="productH" class="productH" style="font-weight: bold; color: green;">Erhältlich</span>
						<div class="productimgdiv">
							<img id="productimg" class="img-fluid productimg" src="img/' . $toNames[$y] . '.jpg" alt="' . $toNames[$y] . '">
						</div>
						<span id="products" class="products products_products" name="products">' . $toNames[$y] . '</span>
						<span id="products" class="products products_products" name="products" style="font-size: 12px;">(' . $unit[$y] . ')</span>
						<div class="subtotal" align="center" style="margin-top:22px;"> <span id="individualSubtotal">' . $prices[$y] . '</span>
							<span>&euro;</span>
						</div>
					</div>';
			}
		}
	echo '</div>';
	}

}

function create_ProductToType_Array($toNames, $types) {
	
	$productToType = array();
	for($i = 0; $i < sizeof($toNames); $i++) {
		$productToType[$toNames[$i]] = $types[$i];
	}

	return $productToType;	

}

function printUnavailableProducts($toNames, $prices, $types, $unit, $info) {

	echo '<div class="wrapper" style="height: auto; overflow: auto;">';
	echo '<h2 style="clear: both; text-align: center; font-size: 28px; padding-top: 3rem;">Noch im Wachstum:</h2>';

	for($y = 0; $y < sizeof($toNames); $y++) {
		echo '<div id="duplicater" class="dup_products items" style="opacity:0.6;">
			<span id="productH" class="productH" style="font-weight: bold; color: #6c0303;">Noch im Wachstum</span>
			<div class="productimgdiv">
				<img id="productimg" class="img-fluid productimg" src="img/' . $toNames[$y] . '.jpg" alt="' . $toNames[$y] . '">
			</div>
			<span id="products" class="products products_products" name="products">' . $toNames[$y] . '</span>
			<span id="products" class="products products_products" name="products" style="font-size: 12px;">(' . $unit[$y] . ')</span>
			<div class="subtotal" align="center" style="margin-top:22px;"> <span id="individualSubtotal">' . $prices[$y] . '</span>
				<span>&euro;</span>
			</div>
		</div>';
	}

	echo '</div>';

}



?>
<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Da Bauernbua - Produkte</title>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" type="text/css" href="css/styles_home.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
</head>

<body>
    <h1 style="text-align: center; margin-top: 30px;">"Da Bauernbua" Produkte</h1>
	<br>
	<button class="orderbtn" style="display: block; margin: 0 auto; background: #008074; border: 2px solid #008074;">
	<a href="orders.php" style="color: white;">zur Bestellung</a></button><br>
   
	<?php
		downloadProducts(1);
		downloadProducts(0);
	?>

	</body>

</html>
