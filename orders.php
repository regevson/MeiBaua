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
$toNames = array(); //productNames
$prices = array();
$quantityByName = array();

downloadProducts();

function downloadProducts() {

	//this info gets passed to JavaScript
	
	global $toNames;
	global $prices;
	global $quantityByName;

	global $conn;
	$result = $conn->query("SELECT * FROM products WHERE available=1");

	while($row = $result->fetch_assoc()) {
			$productName = $row['product'];
			$productPrice = $row['price'];

			$toNames[] = $productName;
			$prices[] = $productPrice;
			$quantityByName[$productName] = 0;
	} 

}

function collectData() {

	global $toNames;
	$numProducts = $_POST['productCounter'];
	echo $numProducts;
	global $quantityByName;
	$total = 0;

	$fname = $_POST['fname'];
	$sname = $_POST['sname'];
	$plz = $_POST['plz'];
	$city = $_POST['city'];
	$house = $_POST['house'];
	$tel = $_POST['tel'];
	$email = $_POST['email'];

	for($x = 0; $x < $numProducts; $x++) {
		$product = "products" . $x;
		$selectedProductNum = $_POST[$product];
		$selectedProductName = $toNames[$selectedProductNum];
		$productQuantity = "number" . $x;
		$quantity = $_POST[$productQuantity];
		$quantityByName[$selectedProductName] += $quantity;
		$total += calcSubtotal($selectedProductNum, $quantity);
	}

	$delivery = $_POST["deliveryCB"];
	$deliveryCost = 0;
	if($delivery == "letdeliver")
			$deliveryCost = evalPLZ($plz);
	$total += $deliveryCost;
	$orderID = uploadOrderData($quantityByName, $toNames, $delivery, $total);
	$customerID = uploadPersonalData($fname, $sname, $plz, $city, $house, $tel, $email, $orderID);

	if($orderID != -1 && $customerID != -1) {
			//emailCustomer($orderID, $customerID, $email, $toNames, $quantityByName, $total);
			//emailWorkers($total);
			header("Location: http://meibaua.ml/confirmation.html");
	}

}

function evalPLZ($plz) {

	$cost = 0;
	if(strcmp($plz, "6232") == 0) //string are equal
		$cost = 1.5;
	else if(strcmp($plz, "6210") == 0)
		$cost = 2;
	else if(strcmp($plz, "6230") == 0)
		$cost = 2;
	else if(strcmp($plz, "6233") == 0)
		$cost = 2;
	else if(strcmp($plz, "6235") == 0)
		$cost = 2.5;
	else if(strcmp($plz, "6200") == 0)
		$cost = 2.5;
	else
		$cost = 10;

	return $cost;

}

function uploadOrderData($quantityByName, $toNames, $delivery, $total) {

		$sql = "INSERT INTO orders ("; 
		for($x = 0; $x < count($quantityByName); $x++) {
			$product = $toNames[$x];
			$sql = $sql . $product . ", ";
		}

		$sql = $sql . "delivery, total) VALUES (";

		for($x = 0; $x < count($quantityByName); $x++) {
			$product = $toNames[$x];
			$sql = $sql . "'" . $quantityByName[$product] . "', ";
		}

		$sql = $sql . "'" . $delivery . "', '" . $total . "')";
		//echo"<br> $sql <br>";
		//echo $total;

		$orderID = 0;
		$orderID = executeQuery($sql);

		return $orderID;

}

function uploadPersonalData($fname, $sname, $plz, $city, $house, $tel, $email, $orderID) {

		$sql = "INSERT INTO customers (fn, sn, plz, city, housenumber, tel, email, orderID) VALUES
				('$fname', '$sname', '$plz', '$city', '$house', '$tel', '$email', '$orderID')";

		//echo"<br> $sql <br>";

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

function calcSubtotal($selectedProductNum, $quantity) {

	$productPrices = array(3, 4, 0.45, 5);
	$productPrice = $productPrices[$selectedProductNum];
	$subtotal = $productPrice * $quantity;
	return $subtotal;

}

function emailCustomer($orderID, $customerID, $email, $toNames, $quantityByName, $total) {

		$message = "Folgende Produkte werden geliefert:\n\n";
		for($x = 0; $x < count($quantityByName); $x++) {
				$product = $toNames[$x];
				$quantity = $quantityByName[$product];
				if($quantity == 0)
						continue;
				$message = $message . $quantity . "x " . $product . "\n";
		}

		$message = $message . "\nBitte halten Sie " . $total . " Euro bereit.\n\n Vielen Dank fuer Ihren Einkauf!\nIhr MeiBaua-Team";

		$to      = $email;
		$subject = 'MeiBaua-Auftragsbestaetigung';
		$headers = 'From: meibaua.ml' . "\r\n" .
			'Reply-To: max.zeindl@gmail.com';

		mail($to, $subject, $message, $headers);

}

function emailWorkers($total) {

		$email1 = "juwal.regev@hotmail.com";

		$message = "Es wurde ein neuer Einkauf in der Hoehe von: " . $total . " Euro getaetigt.\n\n";
		$message = $message . "Fuer mehr Informationen klicken Sie hier: http://www.meibaua.ml/login.php";

		$to      = $email1;
		$subject = 'MeiBaua-Auftrag';
		$headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

		mail($to, $subject, $message, $headers);

}


if(isset($_POST['submitbtn']))
		collectData();

?>
    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>DaBauernBua- Bestellungen</title>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
 <link rel = "stylesheet"
         href = "https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
    </head>

    <body>

        <h1 style="text-align: center; margin-top: 30px;">"DaBauernBua" Bestellungen</h1>
        <br>
        <br>



                <form id="form1" action="#" method="post"> 
                    <input type="hidden" id="productCounter" name="productCounter" value="1"></input>

<div class="left">
                        <div id="personal" class="personalInfo">
                            <label>Vorname:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="fname" name="fname" value="juwal" required>
                            <br>
                            <br>

                            <label>Nachname:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="sname" name="sname" value="regev" required>
                            <br>
                            <br>

                            <label>PLZ:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="plz" name="plz" value="6232" readonly required onchange="update()">
                            <br>
                            <br>

                            <label>Ort:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="city" name="city" readonly value="Muenster" required>
                            <br>
                            <br>

                            <label>Straße, Hausnummer:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="house" name="house" value="Bachleiten 302b" required>
                            <br>
                            <br>

                            <label>Telefonnummer:</label>
                            <br>
                            <input class="personalInfoInput" type="tel" id="tel" name="tel" value="04993849348" required>
                            <br>
                            <br>

                            <label>E-Mail:</label>
                            <br>
                            <input class="personalInfoInput" type="email" id="email" name="email" value="juwal.regev@hotmail.com" required>
                            <br>
                            <br>

<div>
								<!--
                                <input type="radio" name="deliveryCB" id="letdeliver" value="letdeliver" checked onclick="update(this)"> Liefern lassen (0 &euro; Aufpreis)<br>
                                <input type="radio" name="deliveryCB" id="collect" value="collect" onclick="update(this)"> Abholen
								-->
</div>
                        </div>
 </div>

<div class="middle">
                        <div id="duplicater" class="items" style="margin-bottom: 30px;">
                            <span id="productH" class="productH">Produkt 1</span>
                            <br>
                            <div class="productimgdiv">
                                <img id="productimg" class="img-fluid productimg" src="img/karotten.jpg" alt="Colorlib Template">
                            </div>

                            <select id="products" class="products custom-select" name="products" onchange="update()">
                            </select>

                            <input type="number" id="number" class="number" name="number" value="0" min="0" onchange="update()">
                            <div class="subtotal" align="center">
                                <span id="individualSubtotal">0</span>
                                <span>&euro;</span>

                                <div id="addProductDiv">
                                    <button id="morebtn" type="action" name="submit" onclick="duplicate()">Produkt hinzufügen</button>
                                </div>

                            </div>

                        </div>

 </div>


<div class="right">
                <div id="clipboard" style="height: 500px;">

                    <span style="display: block; text-align: center; font-weight: bold;">Einkaufsliste</span>
                    <br>
                    <span id="contents">0x Karotten</span>

					<br>
					<input type="checkbox" id="agb" required>
					<span style="text-transform: none; font-size: 15px;">Ich stimme den AGB und dem KSchG zu</span>
					<br>
					<input type="checkbox" id="datenschutz" required>
					<span style="text-transform: none; font-size: 15px;">Die Daten werden nicht an Dritte weitergegeben. Produktneuigkeiten usw.</span>

                    <input id="submitbtn" type="submit" form="form1" name="submitbtn" value="Bestellen" onclick="changeID()">

                </div>
 </div>
                </form>


            <script>
				var products_arr = <?php echo json_encode($toNames); ?>;
				var productPrices = <?php echo json_encode($prices); ?>;
                var i = 0;

				addOptions();
				//add options (products) to option-dropdown
				function addOptions() {

                    var select = document.getElementById('products');
					for(var i = 0; i < products_arr.length; i++) {
						var option = document.createElement('option');
						option.text = products_arr[i];
						option.value = i;
						select.add(option);
					}
				}

                function duplicate() {
                    var original = document.getElementById('duplicater');
                    var oldAddProductBtn = document.getElementById("addProductDiv");
                    var clone = original.cloneNode(true); // "deep" clone
                    //remove old button
                    oldAddProductBtn.outerHTML = "";

                    changeID();

                    /*
                     * new duplicator has id: duplicator (without i at the end)
                     * and elements inside have ids: ___ (without i at the end)
                     */
                    original.parentNode.appendChild(clone);
                    //Heaading: "Producti", of new div gets updated
                    document.getElementById("productH").innerHTML = "Produkt " + (i + 2);
                    //Quantity of new div gets reset
                    document.getElementById("number").value = 0;
                    //Subtotal of new div gets reset
                    document.getElementById("individualSubtotal").innerHTML = 0;
                    //productimg of new div gets reset
                    document.getElementById("productimg").src = "img/" + products_arr[0] + ".jpg";

                    //hidden productCounter (for php) gets upated
                    document.getElementById("productCounter").value = i + 2;

                    //clipboardHeight gets adjusted
                    var clipboardHeight = document.getElementById("clipboard").clientHeight;
                    clipboardHeight += 55;
                    document.getElementById("clipboard").style.height = clipboardHeight + "px";

                    i++;

                    //update clipboard with new entry of clone
                    update();

                }

                function changeID() {
                    //old duplicator gets id: duplicatori
                    document.getElementById("productH").setAttribute("id", "product" + i);
                    document.getElementById("duplicater").setAttribute("id", "duplicater" + i);
                    //elements inside old duplicator get ids: ___i
                    document.getElementById("products").setAttribute("name", "products" + i);
                    document.getElementById("products").setAttribute("id", "products" + i);

                    //old productimg gets id: ___i
                    document.getElementById("productimg").setAttribute("id", "productimg" + i);

                    document.getElementById("number").setAttribute("name", "number" + i);
                    document.getElementById("number").setAttribute("id", "number" + i);
                    document.getElementById("individualSubtotal").setAttribute("id", "individualSubtotal" + i);
                }

                var total = 0;

                function update(radiobtn) {

                    total = 0;
					var plz = document.getElementById("plz").value;
                    var clipboard = "";

                    for (var x = 0; x <= i; x++) {
                        var tmp = x;
                        var productimg = "productimg";
                        var products = "products";
                        var number = "number";
                        var individualSubtotal = "individualSubtotal";

                        //as last product has ids: "product" and "number" and ... instead of "producti" and "numberi"
                        if (x != i) {
                            productimg = productimg + x;
                            products = products + x;
                            number = number + x;
                            individualSubtotal = individualSubtotal + x;

                        }

                        //update subtotal
                        var dropdownObj = document.getElementById(products);
                        var selectedProductVal = dropdownObj.value;
                        var productPrice = productPrices[selectedProductVal];
                        var quantity = document.getElementById(number).value;
                        var subtotal = productPrice * quantity;
                        document.getElementById(individualSubtotal).innerHTML = financial(subtotal);

                        total += subtotal;

                        //updateImages
                        updateImages(productimg, selectedProductVal);

                        //update clipboard
                        clipboard = clipboard + quantity + "x " + dropdownObj.options[dropdownObj.selectedIndex].text + '<br><i>\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0' + financial(subtotal) + " &euro;</i><br>";

                    }

					//calc DeliveryCost
					var deliveryCost = checkPLZ(plz);
                    clipboard = clipboard + "<br><br>Lieferung: +" + financial(deliveryCost) + " &euro;<br>";

                    //clipboard = changeDelivery(radiobtn, clipboard, deliveryCost);

                    clipboard = clipboard + "------------------------<br>";
                    clipboard = clipboard + "<b>Gesamt: " + financial(total) + " &euro;</b><br>";
                    document.getElementById("contents").innerHTML = clipboard;

                }

				

                function financial(x) {
                    return Number.parseFloat(x).toFixed(2);
                }

				function checkPLZ(plz) {

					var cost = 0;
					if(plz.localeCompare(6232) == 0) //string are equal
						cost = 1.5;
					else if(plz.localeCompare(6210) == 0)
						cost = 2;
					else if(plz.localeCompare(6230) == 0)
						cost = 2;
					else if(plz.localeCompare(6233) == 0)
						cost = 2;
					else if(plz.localeCompare(6235) == 0)
						cost = 2.5;
					else if(plz.localeCompare(6200) == 0)
						cost = 2.5;
					else
						cost = 10;

					return cost;

				}


                function changeDelivery(radiobtn, clipboard, deliveryCost) {

                    if (radiobtn != null) {
                        var radioVal = radiobtn.value;
                        if (radioVal == "collect")
                            deliveryCost = 0;
                    }

					document.getElementById("letdeliver").nextSibling.textContent = "Liefern lassen (" + financial(deliveryCost) + " Euro)";
                    total += deliveryCost;

                    return clipboard;

                }

                //@productimgid is the id of the img to be updated, @imgIndex is the index of the img into the @products_array
                function updateImages(productimgid, imgIndex) {
                    var imgName = products_arr[imgIndex];
                    var productimg = document.getElementById(productimgid);
                    productimg.src = "img/" + imgName + ".jpg";

                }

                window.onload = update();
            </script>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </body>

    </html>
