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

downloadProducts();

/*
 * Download product-info (only of available products)
 * and make it globally available
 */
function downloadProducts() {

    global $conn;

	$toNames        = array(); // names of products
	$prices         = array();
	$unit		    = array();
	$availability   = array();
	$info           = array();
   
    $result = $conn->query("SELECT * FROM products");
    
    while ($row = $result->fetch_assoc()) {
        $toNames[] = $row['product'];
        $prices[] = $row['price'];
        $unit[] = $row['unit'];
        $availability[] = $row['available'];
        $info[] = $row['info'];
    }

	printProducts($toNames, $prices, $unit, $availability, $info, true);
    
}


function printProducts($toNames, $prices, $unit, $availability, $info, $printAvailableProducts) {

	if($printAvailableProducts == true) {
		echo '<h1 style="text-align: center; margin-top: 30px;">"Da Bauernbua" Produkte</h1>
		<br>
		<br>';
	}
	
	else 
		echo '<h2 style="clear: both;">Noch im Wachstum:</h2>';

	echo '<div class="wrapper" style="padding-left: 30px;">';

	for($p = 0; $p < count($toNames); $p++) {

		$avail = $availability[$p];
		if(($printAvailableProducts == true && $avail == 0) || $toNames[$p] == -1 || $avail == -1)
			continue;

		$opacity = 1;
		$availhtml = '<span id="productH" class="productH" style="font-weight: bold; color: green;">Erhältlich</span>';

		if($printAvailableProducts == false){
			$opacity = 0.6;
			$availhtml = '<span id="productH" class="productH" style="font-weight: bold; color: #802400;">Noch im Wachstum</span>';
		}



		echo '<div id="duplicater" class="dup_products items" style="opacity:' . $opacity . '">
									' . $availhtml . '<br>
						<div class="productimgdiv">
							<img id="productimg" class="img-fluid productimg" src="img/' . $toNames[$p] . '.jpg" alt="' . $toNames[$p] . '">
						</div>
						<span id="products" class="products products_products" name="products">' . $toNames[$p] . '</span>
						<span id="products" class="products products_products" name="products" style="font-size: 12px;">(' . $unit[$p] . ')</span>
						<div class="subtotal" align="center" style="margin-top:22px;"> <span id="individualSubtotal">' . $prices[$p] . '</span>
							<span>&euro;</span>
						</div>
					</div>
		';

		$toNames[$p] = -1; // to indicate that the product was already printed
			
	}
	echo '</div>';

			
			if($printAvailableProducts == true)
				printProducts($toNames, $prices, $unit, $availability, $info, false);


}

?>
<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Da Bauernbua - Produkte</title>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
</head>

<body>
			
           

	</body>

</html>
