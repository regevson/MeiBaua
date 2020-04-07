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

//echo "Connected successfully";

?>

    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>MyBauer - Bestellungen</title>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
 <link rel = "stylesheet"
         href = "https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
    </head>

    <body>

        <h1 style="text-align: center;">MyBauer Bestellungen</h1>
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
                            <input class="personalInfoInput" type="text" id="plz" name="plz" value="6232" required>
                            <br>
                            <br>

                            <label>Ort:</label>
                            <br>
                            <input class="personalInfoInput" type="text" id="city" name="city" value="Muenster" required>
                            <br>
                            <br>

                            <label>Hausnummer:</label>
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
                                <input type="radio" name="deliveryCB" id="letdeliver" value="letdeliver" checked onclick="update(this)">Liefern lassen (5 &euro; Aufpreis)<br>
                                <input type="radio" name="deliveryCB" id="collect" value="collect" onclick="update(this)">Abholen
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
                            <select id="products" class="custom-select products" name="products" onchange="update()">
                                <option value="0">Karotten</option>
                                <option value="1">Kartoffeln</option>
                                <option value="2">Radieschen</option>
                                <option value="3">Erdbeeren</option>
                            </select>

                            <input type="number" id="number" class="number" name="number" value="0" min="0" onchange="update()">
                            <div class="subtotal" align="center">
                                <span id="individualSubtotal">0</span>
                                <span>&euro;</span>

                                <div id="addProductDiv">
                                    <button id="morebtn" type="action" name="submit" onclick="duplicate()">Produkt hinzufuegen</button>
                                </div>

                            </div>

                        </div>

 </div>


<div class="right">
                <div id="clipboard" style="height: 321px;">

                    <span style="display: block; text-align: center; font-weight: bold;">Einkaufsliste</span>
                    <br>
                    <span id="contents">0x Karotten</span>

                    <input id="submitbtn" type="submit" form="form1" name="submitbtn" value="Bestellen" onclick="changeID()">

                </div>
 </div>
                </form>


        <?php

function collectData() {

	$numProducts = $_POST['productCounter'];
	$toNames = array("Karotten", "Kartoffeln", "Radieschen", "Erdbeeren");
	$quantityByName = array("Karotten" => 0, "Kartoffeln" => 0, "Radieschen" => 0, "Erdbeeren" => 0);
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
			$deliveryCost = 5;
	$total += $deliveryCost;
	$orderID = uploadOrderData($quantityByName, $toNames, $delivery, $total);
	$customerID = uploadPersonalData($fname, $sname, $plz, $city, $house, $tel, $email, $orderID);

	if($orderID != -1 && $customerID != -1) {
			emailCustomer($orderID, $customerID, $email, $toNames, $quantityByName, $total);
			emailWorkers($total);
	}

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
		echo $total;

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
     echo "Query issued successfully";
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
				$message = $message . $quantity . "x " . $product . "\n";
		}

		$message = $message . "\nBitte halten Sie " . $total . " Euro bereit.\n\n Vielen Dank fuer Ihren Einkauf!\nIhr MyBauer-Team";

		$to      = $email;
		$subject = 'MyBauer-Auftragsbestaetigung';
		$headers = 'From: mybauer@shop.com' . "\r\n" .
			'Reply-To: mybauer@shop.com';

		mail($to, $subject, $message, $headers);

}

function emailWorkers($total) {

		$email1 = "juwal.regev@hotmail.com";

		$message = "Es wurde ein neuer Einkauf in der Hoehe von: " . $total . " Euro getaetigt.\n\n";
		$message = $message . "Fuer mehr Informationen klicken Sie hier: http://www.mybauer.ml/";

		$to      = $email1;
		$subject = 'MyBauer-Auftrag';
		$headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

		mail($to, $subject, $message, $headers);

}

if(isset($_POST['submitbtn']))
		collectData();

?>


            <script>
                var productPrices = [3, 4, 0.45, 5];
                var products_arr = ["Karotten", "Kartoffeln", "Radieschen", "Erdbeeren"];
                var i = 0;

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
                    document.getElementById("productimg").src = "img/karotten.jpg";

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
                        document.getElementById(individualSubtotal).innerHTML = subtotal;

                        total += subtotal;

                        //updateImages
                        updateImages(productimg, selectedProductVal);

                        //update clipboard
                        clipboard = clipboard + quantity + "x " + dropdownObj.options[dropdownObj.selectedIndex].text + '<br><i>\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0' + financial(subtotal) + " &euro;</i><br>";

                    }

                    clipboard = changeDelivery(radiobtn, clipboard);

                    clipboard = clipboard + "------------------------<br>";
                    clipboard = clipboard + "<b>Gesamt: " + financial(total) + " &euro;</b><br>";
                    document.getElementById("contents").innerHTML = clipboard;

                }

                function financial(x) {
                    return Number.parseFloat(x).toFixed(2);
                }

                var currentDeliveryCost = 5;

                function changeDelivery(radiobtn, clipboard) {

                    if (radiobtn != null) {
                        var radioVal = radiobtn.value;

                        if (radioVal == "collect")
                            currentDeliveryCost = 0;
                        else
                            currentDeliveryCost = 5;
                    }

                    clipboard = clipboard + "<br><br>Lieferung: +" + currentDeliveryCost + " &euro;<br>";
                    total += currentDeliveryCost;

                    return clipboard;

                }

                //@productimgid is the id of the img to be updated, @imgIndex is the index of the img into the @products_array
                function updateImages(productimgid, imgIndex) {
                    var imgName = products_arr[imgIndex];
                    var productimg = document.getElementById(productimgid);
                    productimg.src = "img/" + imgName.toLowerCase() + ".jpg";

                }

                window.onload = update();
            </script>

    </body>

    </html>
