<?php
// Get the configuration file including php version and running operating system
require("config.php");

// An array to store all errors  
$errorArray = array();

$xmlResponse = new DOMDocument;
// It will format the output in xml format otherwise
// the output will be in a single row
$xmlResponse->formatOutput = true;

// If item name data is set
if (isset($_POST["itemname"])) {
    // If item name data is empty
    if (empty($_POST["itemname"])) {
        $errorArray["itemname"] = "Please enter your item name.";
    } else {
        // Get the item name 
        $itemname = $_POST["itemname"];
    }
}

// If category data is set
if (isset($_POST["category"])) {
    // If category data is empty
    if (empty($_POST["category"])) {
        $errorArray["category"] = "Please select a category.";
    } else {
        // Get the input category 
        $category = $_POST["category"];
    }
}

// If description data is set
if (isset($_POST["desc"])) {
    // If description data is empty
    if (empty($_POST["desc"])) {
        $errorArray["desc"] = "Please enter your item description.";
    } else {
        // Get the description 
        $desc = $_POST["desc"];
    }
}


// If startprice data is set
if (isset($_POST["startprice"])) {
    // If startprice data is empty
    if (empty($_POST["startprice"])) {
        $errorArray["startprice"] = "Please enter your item start price.";
    } else {
        // Get the startprice
        $startprice = floatval($_POST["startprice"]); 

        if ($startprice < 0.00) {
            $errorArray["startprice"] = "Invalid reserve price. Start price has to be greater than or equal $0.00.";
        }
    }
}

// If reserve price data is set
if (isset($_POST["reserveprice"])) {
    // If reserve price data is empty
    if (empty($_POST["reserveprice"])) {
        $errorArray["reserveprice"] = "Please enter your item reserve price.";
    } else {
        // Get the reserve price
        $reserveprice = floatval($_POST["reserveprice"]);

        $balance = 0.00;

        $xmlfile = '../data/customers.xml';

        $xmlCustomers = new DomDocument;

        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlfile);

        $customerList = $xmlCustomers->getElementsByTagName("customer");

        foreach ($customerList as $customer) {
            if (intval($_SESSION["user_id"]) == intval($customer->childNodes->item(0)->nodeValue)) {
                $balance = floatval($customer->childNodes->item(5)->nodeValue);
            }
        }

        if ($reserveprice <= 0.00) {
            $errorArray["reserveprice"] = "Invalid reserve price. Reserve price has to be greater than $0.00.";
        } else if (isset($startprice) && $startprice > $reserveprice) { 
            $errorArray["startprice"] = "Invalid start price. Start price should be lower than reserve price.";
        } else if (($reserveprice / 100) > $balance) {
            $errorArray["reserveprice"] = "Invalid reserve price. Your balance is too low to bid.";
        }
    }
}

// If buy it now price data is set
if (isset($_POST["buyitnowprice"])) {
    // If buy it now price data is empty
    if (empty($_POST["buyitnowprice"])) {
        $errorArray["buyitnowprice"] = "Please enter your item buy it now price.";
    } else {
        // Get the buy it now price
        $buyitnowprice = floatval($_POST["buyitnowprice"]);

        if ($buyitnowprice <= 0.00) {
            $errorArray["buyitnowprice"] = "Invalid buy it now price. Buy it now price has to be greater than $0.00.";
        } else if (isset($reserveprice) && $reserveprice > $buyitnowprice) { 
            $errorArray["buyitnowprice"] = "Invalid buy it now price. Buy It Now Price should be greater than reserve price.";
        }
    }
}

// If duration data is set
if (isset($_POST["durationday"]) && isset($_POST["durationhour"]) && isset($_POST["durationmin"])) {
    // If duration data is empty
    if (empty($_POST["durationday"]) && empty($_POST["durationhour"]) && empty($_POST["durationmin"])) {
        $errorArray["duration"] = "Please enter your listing duration.";
    } else {
        // Get the duration
        $durationDay = intval($_POST["durationday"]);
        $durationHour = intval($_POST["durationhour"]);
        $durationMin = intval($_POST["durationmin"]);

        if ($durationDay == 0 && $durationHour == 0 && $durationMin == 0) {
            $errorArray["duration"] = "Please enter your listing duration.";
        } else {
            $durationSecond = $durationDay * 24 * 60 * 60 + $durationHour * 60 * 60 + $durationMin * 60;
        }
    }
}

if (!empty($errorArray)) {
    $errors = $xmlResponse->createElement("errors");
    $xmlResponse->appendChild($errors);

    foreach ($errorArray as $x => $y) {
        $errors->appendChild($xmlResponse->createElement($x, $y));
    }
}

if (empty($errorArray) 
    && isset($itemname) 
    && isset($category) 
    && isset($desc)
    && isset($startprice) 
    && isset($reserveprice) 
    && isset($buyitnowprice) 
    && isset($durationSecond)) {

    $xmlfile = '../data/auctions.xml';
    $xmlAuctions = new DomDocument;

    if (!file_exists($xmlfile)) {
        $auctions = $xmlAuctions->createElement('auctions');
		$xmlAuctions->appendChild($auctions);
    } else {
        $xmlAuctions->preserveWhiteSpace = FALSE;
        $xmlAuctions->formatOutput = true;
        $xmlAuctions->load($xmlfile);
    }

    // prepare data
    $auctionID = $xmlAuctions->getElementsByTagName("auction")->count();
    
    session_start(); 
    $sellerID = $_SESSION["user_id"];

    $createTime = new DateTime();

    $startDate = $createTime->format("Y-m-d");
    $startTime = $createTime->format("H:i:sP");

    $status = "in_progress";

    //create a auction node under auctions node
    $auctions = $xmlAuctions->getElementsByTagName('auctions')->item(0);
    $auction = $xmlAuctions->createElement('auction');
    $auctions->appendChild($auction);

    // create auction id node 
    $auctionIDNode = $xmlAuctions->createElement('auctionid');
    $auction->appendChild($auctionIDNode);
    $auctionIDValue = $xmlAuctions->createTextNode($auctionID);
    $auctionIDNode->appendChild($auctionIDValue);
    
    // create seller id node 
    $sellerIDNode = $xmlAuctions->createElement('sellerid');
    $auction->appendChild($sellerIDNode);
    $sellerIDValue = $xmlAuctions->createTextNode($sellerID);
    $sellerIDNode->appendChild($sellerIDValue);

    // create auction item name node 
    $itemnameNode = $xmlAuctions->createElement('itemname');
    $auction->appendChild($itemnameNode);
    $itemnameValue = $xmlAuctions->createTextNode($itemname);
    $itemnameNode->appendChild($itemnameValue);

    // create auction item category
    $cateNode = $xmlAuctions->createElement('category');
    $auction->appendChild($cateNode);
    $cateValue = $xmlAuctions->createTextNode($category);
    $cateNode->appendChild($cateValue);

    // create auction item desc
    $descNode = $xmlAuctions->createElement('desc');
    $auction->appendChild($descNode);
    $descValue = $xmlAuctions->createTextNode($desc);
    $descNode->appendChild($descValue);

     // create auction startprice
     $startpriceNode = $xmlAuctions->createElement('startprice');
     $auction->appendChild($startpriceNode);
     $startpriceValue = $xmlAuctions->createTextNode($startprice);
     $startpriceNode->appendChild($startpriceValue);

     // create auction reserveprice
     $reservepriceNode = $xmlAuctions->createElement('reserveprice');
     $auction->appendChild($reservepriceNode);
     $reservepriceValue = $xmlAuctions->createTextNode($reserveprice);
     $reservepriceNode->appendChild($reservepriceValue);

     // create auction buyitnowprice
     $buyitnowpriceNode = $xmlAuctions->createElement('buyitnowprice');
     $auction->appendChild($buyitnowpriceNode);
     $buyitnowpriceValue = $xmlAuctions->createTextNode($buyitnowprice);
     $buyitnowpriceNode->appendChild($buyitnowpriceValue);

     // create auction start date
     $startdateNode = $xmlAuctions->createElement('startdate');
     $auction->appendChild($startdateNode);
     $startdateValue = $xmlAuctions->createTextNode($startDate);
     $startdateNode->appendChild($startdateValue);

    // create auction start time
    $starttimeNode = $xmlAuctions->createElement('starttime');
    $auction->appendChild($starttimeNode);
    $starttimeValue = $xmlAuctions->createTextNode($startTime);
    $starttimeNode->appendChild($starttimeValue);

    // create auction duration
    $durationNode = $xmlAuctions->createElement('duration');
    $auction->appendChild($durationNode);
    $durationValue = $xmlAuctions->createTextNode($durationSecond);
    $durationNode->appendChild($durationValue);

    // create auction status
    $statusNode = $xmlAuctions->createElement('status');
    $auction->appendChild($statusNode);
    $statusValue = $xmlAuctions->createTextNode($status);
    $statusNode->appendChild($statusValue);

    // create auction bids
    $bidsNode = $xmlAuctions->createElement('bids');
    $auction->appendChild($bidsNode);
    $bidNode = $xmlAuctions->createElement('bid');
    $bidsNode->appendChild($bidNode);

    // create auction bidderID
    $bidderIDNode = $xmlAuctions->createElement('bidderid');
    $bidNode->appendChild($bidderIDNode);
    $bidderIDValue = $xmlAuctions->createTextNode("");
    $bidderIDNode->appendChild($bidderIDValue);

    // create auction last bid
    $lastbidNode = $xmlAuctions->createElement('lastbid');
    $bidNode->appendChild($lastbidNode);
    $lastbidValue = $xmlAuctions->createTextNode($startprice);
    $lastbidNode->appendChild($lastbidValue);

    //save the xml file
    $xmlAuctions->formatOutput = true;
    $xmlAuctions->save($xmlfile);  

    // send message
    $message = $xmlResponse->createElement("message");
    $xmlResponse->appendChild($message);
    $message->appendChild($xmlResponse->createTextNode("Thank you! Your item has been listed in ShopOnline. The item number is " . $auctionID . ", and the bidding starts now: " . $startTime . " on " . $startDate . "."));
}

echo $xmlResponse->saveXML();
?>
