<?php

error_reporting(E_ALL);
ini_set('display_errors', 1); 

session_start();
if($_SESSION["loggedIn"] != true) {
		echo "Bad Gateway";
		die();
}

$servername = "remotemysql.com";
$username = "rdo8BYEQqz";
$password = "EO2wg10w9L";
$dbname = "rdo8BYEQqz";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$toNames = array();
$quantityByName = array();
//number of orders (rows in html)
$rowCount = 0;


downloadProducts();

function downloadProducts() {

	global $toNames;
	global $quantityByName;

	global $conn;
	$result = $conn->query("SELECT * FROM products WHERE available=1");

	while($row = $result->fetch_assoc()) {
			$productName = $row['product'];

			$toNames[] = $productName;
			$quantityByName[$productName] = 0;
	} 

}



?>

    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>MeiBaua - Lieferungsübersicht</title>

		<link rel="stylesheet" type="text/css" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
    </head>

    <body>

		<span><a href="controlpanel.php">zum ControlPanel</a></span>
        <h1 style="text-align: center;">"MeiBaua" Aufträge</h1><br><br>




			<?php
				collectData();
			?>







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
		$paid_arr = $customerData[8];
		$date_arr = $customerData[9];

		//order data
		$orderID_arr = end($customerData);
		$quantityByName_arr = $orderData[0];
		$delivery_arr = $orderData[1];
		$total_arr = $orderData[2];
		$done_arr = $orderData[3];


		echo "<form method='post' action=''>";
		$currentplz = 0;
		for($row = 0; $row < count($orderID_arr); $row++) {
				$plz = $plz_arr[$row];
				if(strcmp($plz, $currentplz) != 0){
						$currentplz = $plz;
						echo "<span class='plzh'>PLZ: " . $currentplz . "</span><br>";
				}
				$output = "<div class='orderBox'>

				<p class='paidInfo'>" . $paid_arr[$row] . "</p>

				<p class='dateInfo'>" . $date_arr[$row] . "</p>
					
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
						
				$output = $output . "<div style='clear: both; text-align: center;'>";
				if(strcmp($paid_arr[$row], "bezahlt") == true) 
						$output = $output . "<button type='submit' id='orderbtn' class='paidbtn' value='" . $customerID_arr[$row] . ",paid' name='" . $row . "'>Zahlung eingegangen</button><br>";
				$output = $output . "<button type='submit' id='orderbtn' class='deliverybtn' value='" . $customerID_arr[$row] . ",done' name='" . $row . "'>Lieferung erfolgt</button><br>
				<button type='submit' id='orderbtn' class='cancelbtn' value='" . $customerID_arr[$row] . ",cancel' name='" . $row . "'>stornieren</button></div></div>";
						
				echo $output;

				global $rowCount;
				$rowCount += 1;

		}

		echo "</form>";

}

//listen for clicked "Lieferung erfolgt" buttons and delete job 
for($x = 0; $x < $rowCount; $x++) {
		if(isset($_POST[$x]))
				analyzeButtons($_POST[$x]);
}

function analyzeButtons($value) {

	$customerID = substr($value, 0, strpos($value, ",")); 

	//if $value doesnt contain "cancel" the returnval is 'false'
	if(strpos($value, "cancel"))
		removeJob($customerID, "cancel");
	else if(strpos($value, "done"))
		removeJob($customerID, "done");
	else
		confirmPayment($customerID);

}

//contents of $value are of type: <customemrID>,done or <customerID>,cancel or <customerID>,paid
function removeJob($customerID, $value) {

		global $conn;

		$row = getDataFromDB("customers", "customerID", $customerID);
		$orderID = $row['orderID'];
		$email = $row['email'];

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

		emailCustomer($email, $value);	
			
 		echo "<meta http-equiv='refresh' content='0'>";

}

function confirmPayment($customerID) {

		global $conn;

		$sql = "UPDATE customers SET paid='1' WHERE customerID='" . $customerID . "'";

		if ($conn->query($sql) !== TRUE) {
			echo "Error updating record: " . $conn->error;
		}
		else
 			echo "<meta http-equiv='refresh' content='0'>";

}

function emailCustomer($email, $value) {

		if($value == "done") {
        	$message = "Ihre MeiBaua-Lieferung ist da und kann verzehrt werden!\n";
        	$message = $message . "\nVielen Dank fuer Ihren Einkauf!\nIhr MeiBaua-Team";
		}
		else{
        	$message = "Ihre MeiBaua-Lieferung wurde erfolgreich storniert!\n";
        	$message = $message . "\nIhr MeiBaua-Team";
		}

        for($x = 0; $x < count($quantityByName); $x++) {
                $product = $toNames[$x];
                $quantity = $quantityByName[$product];
                $message = $message . $quantity . "x " . $product . "\n";
        }


        $to      = $email;
        $subject = 'MeiBaua-Lieferung';
        $headers = 'From: meibaua.ml' . "\r\n" .
            'Reply-To: max.zeindl@gmail.com';

        mail($to, $subject, $message, $headers);

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
		$paid_arr= array();
		$date_arr = array();
		$orderID_arr = array();
		$arrIndex = 0;

		global $conn;
		$result = $conn->query("SELECT * FROM customers ORDER BY plz asc");

  		while($row = $result->fetch_assoc()) {
				$customerID_arr[$arrIndex] = $row['customerID'];
				$fn_arr[$arrIndex] = $row['fn'];
				$sn_arr[$arrIndex] = $row['sn'];
				$plz_arr[$arrIndex] = $row['plz'];
				$city_arr[$arrIndex] = $row['city'];
				$house_arr[$arrIndex] = $row['housenumber'];
				$tel_arr[$arrIndex] = $row['tel'];
				$email_arr[$arrIndex] = $row['email'];
				$paid = $row['paid'];
				if($paid == 1)
					$paid_arr[$arrIndex] = "bezahlt";
				else
					$paid_arr[$arrIndex] = "";
				$date_arr[$arrIndex] = $row['purchaseDate'];
				$orderID_arr[$arrIndex] = $row['orderID'];
				$arrIndex++;
		}

		$data_arr = array($customerID_arr, $fn_arr, $sn_arr, $plz_arr, $city_arr, 
			$house_arr, $tel_arr, $email_arr, $paid_arr, $date_arr, $orderID_arr);
		return $data_arr;

}




function collectOrderData($orderID_arr) {

		global $toNames;
		global $quantityByName;

    	$quantityByName_arr = array();
		$delivery_arr = array();
		$total_arr = array();
		$done_arr = array();
		$arrIndex = 0;

		for($i = 0; $i < count($orderID_arr); $i++) {
				$orderID = $orderID_arr[$i];

				$row = getDataFromDB("orders", "orderID", $orderID);

				//fill up $quantityByName-array with ordered items and quantity
				for($x = 0; $x < count($quantityByName); $x++) {
						$product = $toNames[$x];
						$quantity = $row[$product];
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
