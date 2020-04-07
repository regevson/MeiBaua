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

echo "Connected successfully";

$toNames = array("Karotten", "Kartoffeln", "Radieschen", "Erdbeeren");
//number of orders (rows in html)
$rowCount = 0;

?>

    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>MyBauer - Lieferungsuebersicht</title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>

    <body>

        <h1 style="text-align: center;">MyBauer Auftraege</h1><br><br>



		<div class="row">

			<div class="col-sm-2"></div> 

			<div class="col-sm-8">

			<?php
				collectData();
			?>

			</div> 


			<div class="col-sm-2"></div> 



</div> 



<?php
function collectData() {

		$customerData = collectCustomerData();
		$orderData = collectOrderData(end($customerData));
		displayData($customerData, $orderData);

}


function displayData($customerData, $orderData) {

		global $toNames;
		//customer data
    	$customerID_arr = $customerData[0];
		$fn_arr = $customerData[1];
		$sn_arr = $customerData[2];
		$plz_arr = $customerData[3];
		$city_arr = $customerData[4];
		$house_arr = $customerData[5];
		$tel_arr = $customerData[6];
		$email_arr = $customerData[7];

		//order data
		$orderID_arr = end($customerData);
		$quantityByName_arr = $orderData[0];
		$delivery_arr = $orderData[1];
		$total_arr = $orderData[2];
		$done_arr = $orderData[3];


		echo "<form method='post' action='' style='width: 100%; margin: 0 auto'>";
		for($row = 0; $row < count($orderID_arr); $row++) {
				$output = "<div class='orderBox'>
				<div class='labeldiv'><span class='labels'>Auftragsnummer:</span></div>
				<span class='content'>" . $orderID_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Kundennummer:</span></div>
				<span class='content'>" . $customerID_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Vorname:</span></div>
				<span class='content'>" . $fn_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Nachname:</span></div>
				<span class='content'>" . $sn_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>PLZ:</span></div>
				<span class='content'>" . $plz_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Ort:</span></div>
				<span class='content'>" . $city_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Hausnummer:</span></div>
				<span class='content'>" . $house_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Tel:</span></div>
				<span class='content'>" . $tel_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>E-Mail:</span></div>
				<span class='content'>" . $email_arr[$row] . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Bestellung:</span></div>
				<div style='float: left;'>

				<ul>";
				
				$quantityByName = $quantityByName_arr[$row];
				for($x = 0; $x < count($quantityByName); $x++) {
						$product = $toNames[$x];
						$quantity = $quantityByName[$product];
						if($quantity == 0)
								continue;
						$output = $output . "<li>" . $quantity . "x " . $product . "</li>";
				}

				$output = $output . "</ul></div>";
						
				$output = $output . "<div style='clear: both'><button type='submit' value='" . $customerID_arr[$row] . "' name='" . $row . "'>Lieferung erfolgt</button></div></div>";
						
				echo $output;

				global $rowCount;
				$rowCount += 1;

		}

		echo "</form>";

}

//listen for clicked "Lieferung erfolgt" buttons and delete job 
for($x = 0; $x < $rowCount; $x++) {
		if(isset($_POST[$x]))
				removeJob($_POST[$x]);
}


function removeJob($customerID) {

		global $conn;

		$row = getDataFromDB("customers", "customerID", $customerID);
		$orderID = $row['orderID'];

		$sql = "DELETE FROM customers WHERE customerID='" . $customerID . "'";

		if ($conn->query($sql) === TRUE) {
			echo "Record deleted successfully";
		} else {
			echo "Error deleting record: " . $conn->error;
		}


		$sql = "DELETE FROM orders WHERE orderID='" . $orderID. "'";

		if ($conn->query($sql) === TRUE) {
			echo "Record deleted successfully";
		} else {
			echo "Error deleting record: " . $conn->error;
		}
			
 		echo "<meta http-equiv='refresh' content='0'>";

}

function collectCustomerData() {

    	$customerID_arr = array();
		$fn_arr = array();
		$sn_arr = array();
		$plz_arr = array();
		$city_arr = array();
		$house_arr = array();
		$tel_arr = array();
		$email_arr = array();
		$orderID_arr = array();
		$arrIndex = 0;

		global $conn;
		$result = $conn->query("SELECT * FROM customers");

  		while($row = $result->fetch_assoc()) {
				$customerID_arr[$arrIndex] = $row['customerID'];
				$fn_arr[$arrIndex] = $row['fn'];
				$sn_arr[$arrIndex] = $row['sn'];
				$plz_arr[$arrIndex] = $row['plz'];
				$city_arr[$arrIndex] = $row['city'];
				$house_arr[$arrIndex] = $row['housenumber'];
				$tel_arr[$arrIndex] = $row['tel'];
				$email_arr[$arrIndex] = $row['email'];
				$orderID_arr[$arrIndex] = $row['orderID'];
				$arrIndex++;
		}

		$data_arr = array($customerID_arr, $fn_arr, $sn_arr, $plz_arr, $city_arr, $house_arr, $tel_arr, $email_arr, $orderID_arr);
		return $data_arr;

}




function collectOrderData($orderID_arr) {

		global $toNames;

    	$quantityByName_arr = array();
		$delivery_arr = array();
		$total_arr = array();
		$done_arr = array();
		$arrIndex = 0;

		for($i = 0; $i < count($orderID_arr); $i++) {
				$orderID = $orderID_arr[$i];

				$row = getDataFromDB("orders", "orderID", $orderID);

				$quantityByName = array("Karotten" => 0, "Kartoffeln" => 0, "Radieschen" => 0, "Erdbeeren" => 0);
				//fill up $quantityByName-array with ordered items and quantity
				for($x = 0; $x < count($quantityByName); $x++) {
						$product = $toNames[$x];
						$quantity = $row[strtolower($product)];
						$quantityByName[$product] = $quantity;
				}

				$quantityByName_arr[$i] = $quantityByName;
				$delivery_arr[$i] = $row['delivery'];
				$total_arr[$i] = $row['total'];
				$done_arr[$i] = $row['done'];

		}


		$data_arr = array($quantityByName_arr, $delivery_arr, $total_arr, $done_arr);
		return $data_arr;

}


function getDataFromDB($table, $where, $condition) {

  global $conn;

  $sql = "SELECT * FROM $table WHERE $where='$condition'";
  $result = $conn->query($sql);

  if($row = $result->fetch_assoc())
     return $row;

  return -1;

}

?>

    </body>

    </html>
