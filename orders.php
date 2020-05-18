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

$products = array(); // product objects
$deliveryCost   = 1.5;
$minTotal = 7;

class Product {

	public $pid;
	public $pname;
	public $pprice;
	public $ptype;
	public $punit;
	public $pquantity;

}

// arrays get passed to JS
$pNames = array();
$pPrices = array();
$pTypes = array();
$pUnits = array();
$pimgName = array();

downloadProducts();

/*
 * Download product-info (only of available products)
 * and make it globally available
 */
function downloadProducts()
{
    
    global $products;
    // this info gets passed to JavaScript
	global $pNames;
	global $pPrices;
	global $pTypes;
	global $pUnits;
	global $pimgName;

    global $conn;
    $result = $conn->query("SELECT * FROM products WHERE available=1");
    
    while ($row = $result->fetch_assoc()) {
		$product = new Product();
		$product->pid = $row['productID'];
		$product->pname = $row['product'];
		$product->pprice = $row['price'];
		$product->ptype = $row['type'];
		$product->punit = $row['unit'];
		$product->pquantity= 0;
		$products[$product->pname] = $product;

		$pNames[] = $product->pname;
		$pPrices[] = $product->pprice;
		$pTypes[] = $product->ptype;
		$pUnits[] = $product->punit;
		$pimgName[] = $row['imgName'];
	}
    
}



// listen for customer click on "Bestellen"-button
if (isset($_POST['submitbtn'])) {
	if (isset($_POST['importantInfoCB']) == false)
		echo "<script> alert('Bitte haken Sie das Kästchen ganz oben links an!');
					   document.body.scrollTop = 0;
					   document.documentElement.scrollTop = 0;
			  </script>";
	else
    	collectData();
}

/*
 * Retrieve data from the form-fields
 * Form-fields are labeled like: "products0", "products1", ...
 */
function collectData()
{
    
    global $products;
    global $deliveryCost;
    
    $numProducts = $_POST['productCounter']; // counts number of products that were ordered
    $total       = 0; // total amount of money the purchase is worth
    $fname       = $_POST['fname'];
    $sname       = $_POST['sname'];
    $plz         = $_POST['plz'];
    $city        = $_POST['city'];
    $house       = $_POST['house'];
    $tel         = $_POST['tel'];
    $email       = $_POST['email'];
	$date		 = date("l j\. F Y");
    
    for ($x = 0; $x < $numProducts; $x++) {
        
        /*
         * JS gave the options in select a value according to their index in @toNames
         * $_POST[$product] returns the value of the selected element
         */
        $productID = "products" . $x;
        $selectedProductName  = $_POST[$productID]; // returns name of product
		$product = $products[$selectedProductName];
        $productQuantity     = "number" . $x;
		$product->pquantity += $_POST[$productQuantity];
        $total += calcSubtotal($product);
    }

	global $minTotal;
	if($total < $minTotal) {
		echo "<script>alert('Mindestbestellwert ist 7 Euro :)')</script>";
		return;
	}
    
    /* delivery is currently restricted to 6232 and at a fixed price
     * Also at the moment delivery is mandatory
     $delivery = $_POST["deliveryCB"];
     $deliveryCost = 0;
     if($delivery == "letdeliver")
     $deliveryCost = evalPLZ($plz);
     */
    $delivery = "letdeliver"; // current policy (delivery mandatory)
    $total += $deliveryCost; // is fetched from global var declared at top
    $customerID = uploadPersonalData($fname, $sname, $plz, $city, $house, $tel, $email);
    $orderID    = uploadOrderData($products, $delivery, $total, $date, $customerID);
    
    if ($orderID != -1 && $customerID != -1) {
        emailCustomer($orderID, $customerID, $email, $products, $total);
        //emailWorkers($total);
        //header("Location: http://meibaua.ml/confirmation.html");
    }
    
}

function calcSubtotal($product) {
    
    return $product->pprice * $product->pquantity;
    
}

/*
 * Get the correct deliveryCost according to entered @plz
 * (currently not in use)
 */
function evalPLZ($plz)
{
    
    $cost = 0;
    if (strcmp($plz, "6232") == 0) //string are equal
        $cost = 1.5;
    else if (strcmp($plz, "6210") == 0)
        $cost = 2;
    else if (strcmp($plz, "6230") == 0)
        $cost = 2;
    else if (strcmp($plz, "6233") == 0)
        $cost = 2;
    else if (strcmp($plz, "6235") == 0)
        $cost = 2.5;
    else if (strcmp($plz, "6200") == 0)
        $cost = 2.5;
    else
        $cost = 10;
    
    return $cost;
    
}



/*
 * Upload the entered data regarding the customer
 */
function uploadPersonalData($fname, $sname, $plz, $city, $house, $tel, $email) {
    
    $sql = "INSERT INTO customers (fn, sn, plz, city, housenumber, tel, email) VALUES
                ('$fname', '$sname', '$plz', '$city', '$house', '$tel', '$email')";
    return executeQuery($sql);
    
}


/*
 * Upload the entered data regarding the purchase
 */
function uploadOrderData($products, $delivery, $total, $date, $customerID) {
    
	$sql = "INSERT INTO orders (delivery, total, timestamp, customerID) VALUES ('$delivery', '$total', '$date', '$customerID')";
    $orderID = executeQuery($sql); // get orderID-field of newly inserted row

	foreach($products as $productName => $product) {
		if($product->pquantity == 0)
			continue;
		$sql = "INSERT INTO orderToProductsLedger (orderID, productID, quantity) VALUES ('$orderID', '$product->pid', '$product->pquantity')";
    	executeQuery($sql);
	}

	return $orderID;

}


/*
 * Function for convenience: executes a query and returns value
 * of the created id
 */
function executeQuery($sql)
{
    
    $createdID = -1;
    global $conn;
    
    if ($conn->query($sql) === true) {
        $createdID = $conn->insert_id;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    return $createdID;
    
}

function emailCustomer($orderID, $customerID, $email, $products, $total)
{
    
	$deliveryText = "\nDie Produkte werden am Samstag in einer Woche geliefert!";
	if(evaluateDeliveryDate() == true)
		$deliveryText = "\nDie Produkte werden diesen Samstag geliefert!";
		
    $headers = 'From: dabauernbua.at' . "\r\n" . 'Reply-To: max.zeindl@gmail.com';
    
    $to      = $email;
    $subject = 'Da Bauernbua Zahlungserinnerung und Bestellbestätigung';
    
    $message = "
„I g´frei mi, dass i di überzeugt hob!“

dem Unternehmen „Da Bauernbua“ und mir zu vertrauen. Ich werde alles mir Mögliche tun, um dein Vertraun mit bestem Kundenservice zu belohnen.
Ich hoffe du hast die Bestellung bis Mittwoch 23:59 Uhr abgeschickt und auch online schon bis Mittwoch 23:59 bezahlt? (Dein Zahlungseingang auf mein Geschäftskonto muss spätestens am Donnerstag vor der Lieferung sichtbar sein, denn nur dann kann ich deine Bestellung am darauffolgenden Samstag ausliefern. Alle Bestellungen ab Donnerstag werden nächsten Samstag ausgeliefert, sofern bis Mittwoch zuvor die Überweisung getätigt wurde und der Zahlungseingang bis Donnerstag am Konto sichtbar ist.) Bankverbindung findest du nochmal bei der Bestellübersicht.

Wichtig: Gefällt dir mein Gedanke an Umweltschutz? - Dann bitte ich dich darum bei erneuter Bestellung die zur Lieferung verwendete Box am Freitagabend vor die Haustür ins Trockene zu stellen. Ich werde diese Box, wenn ich sie sehe, Samstagvormittag beim Ausliefern wieder aufsammeln, sodass wir auch hierbei auf Recycling achten.
– WIR sind gegen Wegwerfgesellschaft

Alles Gute
Eicha Bauernbua
Maximilian Zeindl
Nachfolgend siehst du deinen Einkauf\n
";

    $message = $message . "Bestellübersicht:\n\n";
    $message = $message . "Kundennummer: " . $customerID . "\n";
    $message = $message . "Lieferart: Lieferung mit Liefergebühr (1,50€/Münster)\n";
    $message = $message . "Bestellte Ware:\n\n";
    
	foreach($products as $productName => $product) {
        $quantity = $product->pname;
        if ($quantity == 0)
            continue;
        $message = $message . $quantity . "x " . $productName . "\n";
    }
    
	$message = $message . $deliveryText;
    $message = $message . "\nBitte zahle bis Mittwoch " . $total . " Euro auf das Konto: IBAN xyz ein.\n\n Alles Gute!";
    
    mail($to, $subject, $message, $headers);
    
}

function evaluateDeliveryDate() {

	$currentDate = new DateTime("now", new DateTimeZone("Europe/Vienna"));
    return $currentDate->format('N') < 4; // today is before thursday (deadline)

}

/*
 * Notify workers of new order
 */
function emailWorkers($total)
{
    
    $email1 = "juwal.regev@hotmail.com";
    
    $message = "Es wurde ein neuer Einkauf in der Hoehe von: " . $total . " Euro getaetigt.\n\n";
    $message = $message . "Fuer mehr Informationen klicken Sie hier: http://www.meibaua.ml/login.php";
    
    $to      = $email1;
    $subject = 'MeiBaua-Auftrag';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
    
    mail($to, $subject, $message, $headers);
    
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DaBauernBua- Bestellungen</title>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;1,100;1,200;1,300&display=swap" rel="stylesheet">
</head>

<body>
	<h1 style="text-align: center; margin-top: 30px;">"Da Bauernbua" Bestellungen</h1>
	<br>
	<br>
		<div class="banner">

			<input form="form1" type="checkbox" id="delivery" name="importantInfoCB" autofocus> <span style="text-transform: none; font-size: 15px;">
				Ich habe verstanden, dass ich bis Mittwoch um 23:59 bestellen und bezahlen muss, damit ich die Lieferung am darauffolgenden Samstag bekomme.
				<br>Wird später bezahlt, bekomme ich die Produkte am Samstag eine Woche später.<br><br>
				<b>Empfänger:</b> Da Bauernbua<br>
				<b>IBAN:</b> AT062050800001507003<br>
				<b>Verwendungszweck:</b> Kundennummer (wird per Mail zugestellt) und Vorname/Nachname
			</span>
		</div>
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
				<input class="personalInfoInput" type="text" id="city" name="city" readonly value="Münster" required>
				<br>
				<br>
				<label>Straße, Hausnummer:</label>
				<br>
				<input class="personalInfoInput" type="text" id="house" name="house" value="Bachleiten 302b" required>
				<br>
				<br>
				<label>Telefonnummer:</label>
				<br>
				<input class="personalInfoInput" type="tel" id="tel" name="tel" value="">
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
			<div id="duplicater" class="items" style="margin-bottom: 30px;"> <span id="productH" class="productH">Produkt 1</span>
				<div class="productimgdiv">
					<img id="productimg" class="img-fluid productimg" src="img/.jpg" alt="Colorlib Template">
				</div>
				<select id="products" class="products custom-select" name="products" onchange="update()">
				</select>
				<input type="number" id="number" class="number" name="number" value="0" min="0" onchange="update()" onkeypress="update(); return (event.keyCode!=13);">
				<p class="unit">Kopf</p>
				<div style="clear: both;"></div>
				<div class="subtotal" align="center"> <span class="individualSubtotal">0</span><span> &euro;</span>
				</div>
					<div id="addProductDiv">
						<button class="addbtn morebtn" style="background: #0080001f;" type="button" onclick="duplicate()">Produkt hinzufügen</button>
						<button class="removebtn morebtn" style="display: none; background: #ff000038;" type="button" onclick="remove(this)">Produkt entfernen</button>
					</div>
				</div>
			</div>
		</div>
		<div class="right">
			<div id="clipboard"> <span style="display: block; text-align: center; font-weight: bold;">Einkaufsliste</span>
				<br> <span id="contents">0x Karotten</span>
				<br>
				<input type="checkbox" id="agb" required> <span style="text-transform: none; font-size: 15px;">Ich stimme den <a href="img/agb.pdf" target="blank">
				AGB</a> und <a href="img/datenschutz.pdf" target=_blank">Datenschutzbestimmungen</a> zu</span>
				<br>
				<input id="submitbtn" type="submit" form="form1" name="submitbtn" value="Bestellen" onclick="changeID()">
			</div>
		</div>
	</form>

	
<script>

// arrays get combined to structs "product"
var pNames = <?php echo json_encode($pNames); ?> ; // stores names of products
var pPrices = <?php echo json_encode($pPrices); ?> ; // stores prices of products
var pTypes = <?php echo json_encode($pTypes); ?> ; // stores prices of products
var pUnits = <?php echo json_encode($pUnits); ?> ; // stores prices of products
var pimgName = <?php echo json_encode($pimgName); ?> ; // stores prices of products

var products = createStructs();

var i = 0; // counts number of added products
var boxes = []; // all boxes are stored in here

var original = document.getElementById('duplicater'); 
addOptions(products);
var originalClone = original.cloneNode(true); // keep this as template for new boxes to clone from
boxes.push(original); // add first box

/*
 * Combine arrays received from PHP at same index
 * to form "product"-struct
 */
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
		products[i] = new Product(pNames[i], pPrices[i], pTypes[i], pUnits[i], pimgName[i]);
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



/*
 * Add new product-box as user requested
 */
function duplicate() {

    var oldBox = boxes[boxes.length - 1];
    var clone = originalClone.cloneNode(true); // "deep" clone from template where content is set to default
    oldBox.parentNode.appendChild(clone);
	boxes.push(clone);

    // remove old add-button
	var oldAddBtn = oldBox.getElementsByClassName("addbtn")[0];
    oldAddBtn.style.display = "none"; // only most recent product-"box" can add products
	// display remove-button
	var removeBtn = oldBox.getElementsByClassName("removebtn")[0];
    removeBtn.style.display = "inline"; // make button that removes box visible on old box

	// display new removebtn on added box
	var newRemoveBtn = clone.getElementsByClassName("removebtn")[0];
	newRemoveBtn.style.display = "inline";

    // Heaading: "Producti", of new div gets updated
	var productH = clone.getElementsByClassName("productH")[0];
	productH.innerHTML = "Produkt " + (i + 2);
	
    // hidden productCounter (for php) gets upated
    document.getElementById("productCounter").value = i + 2;

    i++;

    // update clipboard with new entry of clone
    update();

}

/*
 * Remove deleted box
 */
function remove(obj) { 

	var box = obj.parentElement.parentElement;
	var boxIndex = boxes.indexOf(box);
	boxes.splice(boxIndex, 1);
	box.parentNode.removeChild(box);
    document.getElementById("productCounter").value = boxes.length;

	updateBtns();
	update();

}

function updateBtns() {

	lastBox = boxes[boxes.length - 1];
	lastBox.getElementsByClassName("addbtn")[0].style.display = "inline";
	if(boxes.length == 1)
		lastBox.getElementsByClassName("removebtn")[0].style.display = "none";

}



/*
 * Gets called when product, quantity or plz gets changed
 * Updates prices, pictures
 */
function update(radiobtn) {

	var total = 0;
    var clipboard = ""; // shopping basket
    var plz = document.getElementById("plz").value;

	// update content inside every box
	for(var i = 0; i < boxes.length; i++) {

		var box = boxes[i];
		updateID(box, i); // so PHP can talk to individual objects
		var productH = box.getElementsByClassName("productH")[0];
		var productimg = box.getElementsByClassName("productimg")[0];
		var selectedProduct = box.getElementsByClassName("products")[0];
		var number = box.getElementsByClassName("number")[0];
		var unitp = box.getElementsByClassName("unit")[0];
		var indivSubtotal = box.getElementsByClassName("individualSubtotal")[0];

        productH.innerHTML = "Produkt " + (i+1); // update product-headings
        var selectedProductName = selectedProduct.value; // value of selected product (its name)
		var selectedProductIndex = pNames.indexOf(selectedProductName);
        var productPrice = products[selectedProductIndex].price;
        var quantity = number.value;
		
		var unit = products[selectedProductIndex].unit; // get unit
		unitp.innerHTML = unit; // update unit

        var subtotal = productPrice * quantity;
        indivSubtotal.innerHTML = financial(subtotal);

		var imgName = products[selectedProductIndex].imgname;
        updateImages(productimg, imgName);

		if(quantity == 0)
			continue;

        total += subtotal;


        // update clipboard content
        clipboard = clipboard + quantity + "x " + selectedProduct.options[selectedProduct.selectedIndex].text +
            ' (' + unit + ')' + '<br><i>\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0' + financial(subtotal) + " &euro;</i><br>";
	}

    // calc deliveryCost
    var deliveryCost = checkPLZ(plz);
    clipboard = clipboard + "<br><br>Lieferung: +" + financial(deliveryCost) + " &euro;<br>";

    // clipboard = changeDelivery(radiobtn, clipboard, deliveryCost);
    total += deliveryCost;

    clipboard = clipboard + "------------------------<br>";
    clipboard = clipboard + "<b>Gesamt: " + financial(total) + " &euro;</b><br>";
    document.getElementById("contents").innerHTML = clipboard;

}

/*
 * Enumerate the ids of the objects inside the boxes (@products and @number)
 * incrementally to allow PHP to retrieve their individual values
 */
function updateID(box, index) {

	var products = box.getElementsByClassName("products")[0];
	products.setAttribute("name", "products" + index);

	var number = box.getElementsByClassName("number")[0];
	number.setAttribute("name", "number" + index);

}

/*
 * Financial rounding
 */
function financial(x) {
    return Number.parseFloat(x).toFixed(2);
}

/*
 * Updates the image source
 * @productimg is the img to be updated, @imgIndex is the index of the img into the @products_array
 *
 */
function updateImages(productimg, imgName) { productimg.src = "img/" + imgName + ".jpeg"; }

/*
 * Returns deliveryCost according to @plz
 */
function checkPLZ(plz) {

    var cost = 0;

    if (plz.localeCompare(6232) == 0) // string are equal
        cost = 1.5;
    else if (plz.localeCompare(6210) == 0)
        cost = 2;
    else if (plz.localeCompare(6230) == 0)
        cost = 2;
    else if (plz.localeCompare(6233) == 0)
        cost = 2;
    else if (plz.localeCompare(6235) == 0)
        cost = 2.5;
    else if (plz.localeCompare(6200) == 0)
        cost = 2.5;
    else
        cost = 10;

    return cost;

}


/*
 * (currently not in use)
 */
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

function stopSubmitOnEnter (e) {
  var eve = e || window.event;
  var keycode = eve.keyCode || eve.which || eve.charCode;

  if (keycode == 13) {
    eve.cancelBubble = true;
    eve.returnValue = false;

    if (eve.stopPropagation) {   
      eve.stopPropagation();
      eve.preventDefault();
    }

    return false;
  }
}

window.onload = update();

</script>

           
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

	</body>

</html>
