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

		$customers = createCustomer();
		displayData($customers);

}

if(!empty($customers)) {
	for($x = 0; $x < sizeof($customers); $x++) {
		if(isset($_POST[$x]))
				analyzeButtons($_POST[$x]);
	}
}


$customers = array();
class Customer {

	public $customerid;
	public $fn;
	public $sn;
	public $plz;
	public $city;
	public $house;
	public $tel;
	public $email;
	public $order;

}

function createCustomer() {
    	
		$orders = collectOrderData();
		global $customers;
		global $conn;
		$result = $conn->query("SELECT * FROM customers ORDER BY plz asc");

  		while($row = $result->fetch_assoc()) {
				$customer = new Customer();
				$customer->customerid = $row['customerID'];
				$customer->fn = $row['fn'];
				$customer->sn = $row['sn'];
				$customer->plz = $row['plz'];
				$customer->city = $row['city'];
				$customer->house = $row['housenumber'];
				$customer->tel = $row['tel'];
				$customer->email = $row['email'];
				$customer->order = $orders[strval($customer->customerid)];
				$customers[] = $customer;
		}

		return $customers;

}




class Order {

	public $orderid;
	public $delivery;
	public $total;
	public $date;
	public $items;
	public $paid;
	public $customerid;

}



function collectOrderData() {
		
	$pNames = downloadProductNamesByID();

	global $conn;
  	$sql = "SELECT * FROM orders";
 	$result = $conn->query($sql);

	$orders = array(); // key: customerID, value: order-obj
  	while($row = $result->fetch_assoc()) {
		$order = new Order();
		$order->orderid = $row['orderID'];
		$order->delivery= $row['delivery'];
		$order->total = $row['total'];
		$order->date = $row['timestamp'];
		$order->customerid = $row['customerID'];
		$order->items = getItems($order->orderid, $pNames);
		$order->paid= $row['paid'];
		$orders[strval($order->customerid)] = $order;
	}

	return $orders;
	
}

function downloadProductNamesByID() {


	global $conn;
  	$sql = "SELECT productID, product FROM products";
 	$result = $conn->query($sql);

	$productNames = array();
  	while($row = $result->fetch_assoc()) {
		$id = $row['productID'];
		$productNames[$id] = $row['product'];
	}

	return $productNames;

}


class Item {

	public $productname;
	public $productquantity;

}

function getItems($orderID, $pNames) {

	global $conn;
  	$sql = "SELECT * FROM orderToProductsLedger WHERE orderID='$orderID'";
 	$result = $conn->query($sql);

	$items = array(); 
  	while($row = $result->fetch_assoc()) {
		$item = new Item();
		$productID = $row['productID'];
		$item->productname = $pNames[$productID];
		$item->productquantity = $row['quantity'];
		$items[] = $item;
	}

	return $items;

}


function displayData($customers) {
		
		echo "<form method='post' action=''>";

		$currentplz = 0;
		if(empty($customers)) {
			echo "NO CUSTOMERS";
			return;
		}
		for($row = 0; $row < sizeof($customers); $row++) {
				$customer = $customers[$row];
				$order = $customer->order;
				$items = $order->items;

				$plz = $customer->plz;
				if(strcmp($plz, $currentplz) != 0){ // if different
						$currentplz = $plz;
						echo "<span class='plzh'>PLZ: " . $currentplz . "</span><br>";
				}

				$paid = "";
				if($order->paid == 1)
					$paid = "BEZAHLT";

				$output = "<div class='orderBox'>

				<p class='paidInfo'>" . $paid . "</p>

				<p class='dateInfo'>" . $order->date . "</p>
					
				<div class='labeldiv'><span class='labels'>Auftragsnummer:</span></div>
				<span class='content'>" . $order->orderid . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Kundennummer:</span></div>
				<span class='content'>" . $customer->customerid . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Vorname:</span></div>
				<span class='content'>" . $customer->fn . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Nachname:</span></div>
				<span class='content'>" . $customer->sn . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>PLZ:</span></div>
				<span class='content'>" . $customer->plz . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Ort:</span></div>
				<span class='content'>" . $customer->city . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Hausnummer:</span></div>
				<span class='content'>" . $customer->house . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Tel:</span></div>
				<span class='content'>" . $customer->tel . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>E-Mail:</span></div>
				<span class='content'>" . $customer->email . "</span>
				<br>

				<div class='labeldiv'><span class='labels'>Bestellung:</span></div>
				<div style='float: left;'>

				<ul>";
				
				for($x = 0; $x < sizeof($items); $x++) {
						$item = $items[$x];
						$pName = $item->productname;
						$pQuantity = $item->productquantity;
						if($pQuantity == 0)
							continue;
						$output = $output . "<li>" . $pQuantity . "x " . $pName. "</li>";
				}

				$output = $output . "</ul></div>

				<div class='labeldiv'><span class='labels'>Betrag:</span></div>
				<span class='content'><b>" . $order->total . '</b></span>';

						
				$output = $output . "<div style='clear: both; text-align: center;'>";
				if($order->paid == 0)
						$output = $output . "<button type='submit' id='orderbtn' class='paidbtn' value='$row,paid' name='$row'>Zahlung eingegangen</button><br>";
				$output = $output . "<button type='submit' id='orderbtn' class='deliverybtn' value='$row,done' name='$row'>Lieferung erfolgt</button><br>
				<button type='submit' id='orderbtn' class='cancelbtn' value='$row,cancel' name='$row'>stornieren</button></div></div>";
						
				echo $output;

		}

		echo "</form>";

}

//listen for clicked "Lieferung erfolgt" buttons and delete job 


function analyzeButtons($value) {

	echo "anal";
	$customerIndex = substr($value, 0, strpos($value, ",")); 

	//if $value doesnt contain "cancel" the returnval is 'false'
	if(strpos($value, "cancel"))
		removeJob($customerIndex, "cancel");
	else if(strpos($value, "done"))
		removeJob($customerIndex, "done");
	else if(strpos($value, "paid"))
		confirmPayment($customerIndex);

}

//contents of $value are of type: <customemrID>,done or <customerID>,cancel or <customerID>,paid
function removeJob($customerIndex, $value) {

		global $conn;
		global $customers;
		$customer = $customers[$customerIndex];

		/*
		 * as there are fk in orders and ledger-tables those entries get deleted as well
		 * with the deletion of the customer
		 */
  		$sql = "DELETE FROM customers WHERE customerID='" . $customer->customerid . "'";

		if ($conn->query($sql) === TRUE)
			echo "Record deleted successfully";
		else
			echo "Error deleting record: " . $conn->error;

		emailCustomer($customer->email, $customer->order->items, $value);	
			
 		echo "<meta http-equiv='refresh' content='0'>";


}


function confirmPayment($customerIndex) {

		global $customers;
		global $conn;

		$customer = $customers[$customerIndex];
		$sql = "UPDATE orders SET paid='1' WHERE orderID='" . $customer->order->orderid . "'";

		if ($conn->query($sql) !== TRUE)
			echo "Error updating record: " . $conn->error;
		else
 			echo "<meta http-equiv='refresh' content='0'>";

}

function emailCustomer($email, $items, $value) {

		if($value == "done")
        	$message = 'Deine "Da Bauernbua" Lieferung ist da!' . "\nVielen Dank für den Einkauf!";
		else
        	$message = 'Deine "Da Bauernbua" Lieferung wurde erfolgreich storniert!\n';

        for($x = 0; $x < sizeof($items); $x++) {
				$item = $items[$x];
                $pName = $item->productname;
                $pQuantity = $item->productquantity;
                $message = $message . $pQuantity . "x " . $pName . "\n";
        }


        $to      = $email;
        $subject = 'MeiBaua-Lieferung';
        $headers = 'From: meibaua.ml' . "\r\n" .
            'Reply-To: max.zeindl@gmail.com';

        mail($to, $subject, $message, $headers);

}


















function getDataFromDB($table, $where, $condition) {

  global $conn;

  $sql = "SELECT * FROM $table WHERE $where='$condition'";
  $result = $conn->query($sql);
  return $result;

}

?>

    </body>

    </html>
