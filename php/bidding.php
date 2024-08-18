<?php

// Get the configuration file including php version and running operating system
require("config.php");

function loadData() {
    // Transform the bidding auction for display 
    $xmlDoc = new DomDocument;
    $xmlDoc->load("../data/auctions.xml");

    $xslDoc = new DomDocument;
    $xslDoc->load("../transform/biddingitem.xsl");
        
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xslDoc);

    echo $proc->transformToXML($xmlDoc);
}

function bid($auctionID, $bidderID, $amount) {

    // An array to store all errors  
    $errorArray = array();

    // Response to JavaScript in XML format
    $xmlResponse = new DOMDocument;
    $xmlResponse->formatOutput = true;
    
    // Check if bid amount is a number
    if (is_numeric($auctionID) && is_numeric($bidderID) && is_numeric($amount)) {
        $auctionID = intval($auctionID);
        $bidderID = intval($bidderID);
        $amount = floatval($amount);
    } else {
        $errorArray["bid"] = "Please enter a number.";
    }

    // If any errors 
    if (!empty($errorArray)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
    
        foreach ($errorArray as $x => $y) {
            $errors->appendChild($xmlResponse->createElement($x, $y));
        }
    }

    // No error
    if (empty($errorArray)) {
        $xmlAuctionFile = '../data/auctions.xml';

        // If there is something wrong that the auction file is not existed 
        if (!file_exists($xmlAuctionFile)) {
            $errors = $xmlResponse->createElement("errors");
            $xmlResponse->appendChild($errors);
            $errors->appendChild($xmlResponse->createElement("bid", "Invalid auction."));
        } else {
            // Load the auction file    
            $xmlAuctions = new DomDocument;
            $xmlAuctions->preserveWhiteSpace = FALSE;
            $xmlAuctions->formatOutput = true;
            $xmlAuctions->load($xmlAuctionFile);

            // Get all data
            $auctionList = $xmlAuctions->getElementsByTagName("auction");

            // To make sure that the requested auction is existed
            $foundAuction = false;

            // Loop all auctions
            foreach ($auctionList as $auction) {
                // Check if the requested auction id is existed
                if ($auctionID == intval($auction->childNodes->item(0)->nodeValue)) {
                    $foundAuction = true;

                    // Get the seller of the auction
                    $sellerID = $auction->childNodes->item(1)->nodeValue;

                    // Get all date data
                    $startDate = $auction->childNodes->item(8)->nodeValue;
                    $startTime = $auction->childNodes->item(9)->nodeValue;
                    $duration = $auction->childNodes->item(10)->nodeValue;
                    $expiryDateTime = new DateTime($startDate. "T" . $startTime);
                    $expiryDateTime->add(new DateInterval("PT". $duration . "S"));
                    $currentDateTime = new DateTime();

                    // Get the buy it now price
                    $buyItNowPrice = floatval($auction->childNodes->item(7)->nodeValue);
                    
                    // Get the status of this auction
                    $status = $auction->childNodes->item(11)->nodeValue;

                    // Get all bids of this auction
                    $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

                    // Get the highest bid among all bids on this auction and the corresponding bidder id
                    $highestBidderID = null;
                    $highestBid = 0;

                    foreach ($bidList as $bid) {
                        $bidAmount =  floatval($bid->childNodes->item(1)->nodeValue);

                        if ($bidAmount > $highestBid) {
                            $highestBid = $bidAmount;

                            // Avoid throw error for the init bid - start price, which does not have any bidder id
                            if (!empty($bid->childNodes->item(0)->nodeValue)) {
                                $highestBidderID = $bid->childNodes->item(0)->nodeValue;
                            }
                        }
                    }

                    // Get the balance of the bidder
                    $balance = 0.00;

                    $xmlCustomerFile = '../data/customers.xml';

                    // Load the customer data
                    $xmlCustomers = new DomDocument;
                    $xmlCustomers->preserveWhiteSpace = FALSE;
                    $xmlCustomers->formatOutput = true;
                    $xmlCustomers->load($xmlCustomerFile);

                    // Get all data
                    $customerList = $xmlCustomers->getElementsByTagName("customer");

                    // Get the customer balance
                    foreach ($customerList as $customer) {
                        if (intval($bidderID) == intval($customer->childNodes->item(0)->nodeValue)) {
                            $balance = floatval($customer->childNodes->item(5)->nodeValue);
                        }
                    }

                    // Get the last bid of the bidder
                    $bidderLastBid = 0.00;

                    foreach ($bidList as $bid) {
                        if ($bidderID == intval($bid->childNodes->item(0)->nodeValue)) {
                            $bidderLastBid = floatval($bid->childNodes->item(1)->textContent);
                        }
                    }

                    // The auction must be in progress
                    // The bidder must not be the seller 
                    // The bidder is not having the highest bid
                    // The auction deadline is not passed
                    // The new bid must greater than the highest bid
                    // The new bid must not higher than the buy it now price
                    // The balance must higher than the difference between new bid and last bid of the bidder
                    if ($status == "in_progress" 
                        && intval($bidderID) != intval($sellerID)
                        && intval($bidderID) != ($highestBidderID == null ? null : intval($highestBidderID)) 
                        && $expiryDateTime > $currentDateTime
                        && $amount > floatval($highestBid)
                        && $amount < $buyItNowPrice
                        && $balance > ($amount - $bidderLastBid)
                        ) {

                        // Check if the bidder has bided before
                        $foundPreviousBid = false; 
                        
                        // Update new bid 
                        foreach ($bidList as $bid) {
                            if ($bidderID == intval($bid->childNodes->item(0)->nodeValue)) {
                                $foundPreviousBid = true;
                                $bid->childNodes->item(1)->textContent = $amount;
                            }
                        }

                        // If the bidder did not bid before 
                        if ($foundPreviousBid == false) {
                            $newBid = $xmlAuctions->createElement("bid");
                            $auction->childNodes->item(12)->appendChild($newBid);

                            $newBidID = $xmlAuctions->createElement("bidderid");
                            $newBidIDValue = $xmlAuctions->createTextNode($bidderID);
                            $newBidID->appendChild($newBidIDValue);

                            $newBidAmount = $xmlAuctions->createElement("lastbid");
                            $newBidAmountValue = $xmlAuctions->createTextNode($amount);
                            $newBidAmount->appendChild($newBidAmountValue);

                            $newBid->appendChild($newBidID);
                            $newBid->appendChild($newBidAmount);
                        }

                        // Update the balance of the bidder 
                        // Only paid the difference between last bid and new bid
                        foreach ($customerList as $customer) {
                            if (intval($bidderID) == intval($customer->childNodes->item(0)->nodeValue)) {
                                $balance = floatval($customer->childNodes->item(5)->nodeValue);
                    
                                $balance = $balance - ($amount - $bidderLastBid);
                    
                                $customer->childNodes->item(5)->textContent = $balance;
                            }
                        }

                        // Save the new data
                        $xmlAuctions->save($xmlAuctionFile);
                        $xmlCustomers->save($xmlCustomerFile);  

                        // XML response format to JavaScript
                        $message = $xmlResponse->createElement("message");
                        $xmlResponse->appendChild($message);
                        $message->appendChild($xmlResponse->createTextNode("Thank you! Your bid is recorded in ShopOnline."));
                    } 
                    else {
                        // If the new bid fails the conditions above 
                        $errors = $xmlResponse->createElement("errors");
                        $xmlResponse->appendChild($errors);
                        $errors->appendChild($xmlResponse->createElement("bid", "Sorry, your bid is not valid."));
                    }
                }
            }
            
            // If some how the auction is not existed
            if ($foundAuction == false) {
                $errors = $xmlResponse->createElement("errors");
                $xmlResponse->appendChild($errors);
                $errors->appendChild($xmlResponse->createElement("bid", "Invalid auction."));
            }
        }
    }

    // Send the response to JavaScript
    echo $xmlResponse->saveXML();
}

function buy($auctionID, $buyerID) {

    // An array to store all errors  
    $errorArray = array();

    // Response to JavaScript in XML format
    $xmlResponse = new DOMDocument;
    $xmlResponse->formatOutput = true;
    
    // Check if the request is valid
    if (is_numeric($auctionID) && is_numeric($buyerID)) {
        $auctionID = intval($auctionID);
        $buyerID = intval($buyerID);
    } else {
        $errorArray["buy"] = "Error: Something went wrong. Can not process the payment.";
    }

    // If error
    if (!empty($errorArray)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
    
        foreach ($errorArray as $x => $y) {
            $errors->appendChild($xmlResponse->createElement($x, $y));
        }
    }

    // No error
    if (empty($errorArray)) {
        $xmlAuctionFile = '../data/auctions.xml';

        if (!file_exists($xmlAuctionFile)) {
            $errors = $xmlResponse->createElement("errors");
            $xmlResponse->appendChild($errors);
            $errors->appendChild($xmlResponse->createElement("buy", "Error: Something went wrong. Can not process the payment."));
        } else {
            // Load the auction data 
            $xmlAuctions = new DomDocument;
            $xmlAuctions->preserveWhiteSpace = FALSE;
            $xmlAuctions->formatOutput = true;
            $xmlAuctions->load($xmlAuctionFile);

            // Get all data
            $auctionList = $xmlAuctions->getElementsByTagName("auction");

            // Somehow the auction is not existed
            $foundAuction = false;

            // Loop through all auctions
            foreach ($auctionList as $auction) {
                if ($auctionID == intval($auction->childNodes->item(0)->nodeValue)) {
                    $foundAuction = true;

                    // Get the seller id
                    $sellerID = $auction->childNodes->item(1)->nodeValue;

                    // Get all date data
                    $startDate = $auction->childNodes->item(8)->nodeValue;
                    $startTime = $auction->childNodes->item(9)->nodeValue;
                    $duration = $auction->childNodes->item(10)->nodeValue;
                    $expiryDateTime = new DateTime($startDate. "T" . $startTime);
                    $expiryDateTime->add(new DateInterval("PT". $duration . "S"));
                    $currentDateTime = new DateTime();

                    // Get the status of this auction
                    $status = $auction->childNodes->item(11)->nodeValue;

                    // Get the buy it now price
                    $buyItNowPrice = floatval($auction->childNodes->item(7)->nodeValue);

                    // Get the balance of the bidder
                    $balance = 0.00;

                    $xmlCustomerFile = '../data/customers.xml';

                    // Load the customer data 
                    $xmlCustomers = new DomDocument;
                    $xmlCustomers->preserveWhiteSpace = FALSE;
                    $xmlAuctions->formatOutput = true;
                    $xmlCustomers->load($xmlCustomerFile);

                    // Get all data
                    $customerList = $xmlCustomers->getElementsByTagName("customer");

                    // Get the balance
                    foreach ($customerList as $customer) {
                        if (intval($buyerID) == intval($customer->childNodes->item(0)->nodeValue)) {
                            $balance = floatval($customer->childNodes->item(5)->nodeValue);
                        }
                    }

                    // Check if the buyer has bided before
                    $buyerLastBid = 0.00;

                    // Get all bids
                    $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

                    // Update if is there any bid record 
                    foreach ($bidList as $bid) {
                        if ($buyerID == intval($bid->childNodes->item(0)->nodeValue)) {
                            $buyerLastBid = floatval($bid->childNodes->item(1)->textContent);
                        }
                    }

                    // The auction must be in progress
                    // The buyer is not the seller
                    // The bid deadline is not passed
                    // The balance must be greater than the difference between the last bid (may have or 0) and the buy it now price
                    if ($status == "in_progress" 
                        && intval($buyerID) != intval($sellerID)
                        && $expiryDateTime > $currentDateTime
                        && $balance > ($buyItNowPrice - $buyerLastBid)
                        ) {
                        
                        // Update as highest bid
                        $foundPreviousBid = false; 
                        
                        // If the buyer has bided before
                        foreach ($bidList as $bid) {
                            if ($buyerID == intval($bid->childNodes->item(0)->nodeValue)) {
                                $foundPreviousBid = true;
                                $bid->childNodes->item(1)->textContent = $buyItNowPrice;
                            }
                        }

                        // If not
                        if ($foundPreviousBid == false) {
                            $newBid = $xmlAuctions->createElement("bid");
                            $auction->childNodes->item(12)->appendChild($newBid);
    
                            $newBidID = $xmlAuctions->createElement("bidderid");
                            $newBidIDValue = $xmlAuctions->createTextNode($buyerID);
                            $newBidID->appendChild($newBidIDValue);
    
                            $newBidAmount = $xmlAuctions->createElement("lastbid");
                            $newBidAmountValue = $xmlAuctions->createTextNode($buyItNowPrice);
                            $newBidAmount->appendChild($newBidAmountValue);
    
                            $newBid->appendChild($newBidID);
                            $newBid->appendChild($newBidAmount);
                        }

                        // Update the auction to sold
                        $auction->childNodes->item(11)->textContent = "sold";

                        // Update the balance of the buyer
                        // Only takes the difference between the last bid (may have or 0) and the buy it now price
                        foreach ($customerList as $customer) {
                            if (intval($buyerID) == intval($customer->childNodes->item(0)->nodeValue)) {
                                $balance = floatval($customer->childNodes->item(5)->nodeValue);
                    
                                $balance = $balance - ($buyItNowPrice - $buyerLastBid);
                    
                                $customer->childNodes->item(5)->textContent = $balance;
                            }
                        }
                    
                        // Save the files
                        $xmlAuctions->save($xmlAuctionFile);
                        $xmlCustomers->save($xmlCustomerFile);  

                        // XML format response to JavaScript
                        $message = $xmlResponse->createElement("message");
                        $xmlResponse->appendChild($message);
                        $message->appendChild($xmlResponse->createTextNode("Thank you for purchasing this item."));
                    } else {
                        // If does not match the conditions above
                        $errors = $xmlResponse->createElement("errors");
                        $xmlResponse->appendChild($errors);
                        $errors->appendChild($xmlResponse->createElement("buy", "Error: Can not process the payment. Please check your balance."));
                    }
                }
            }
    
            // if somehow the auction is not existed
            if ($foundAuction == false) {
                $errors = $xmlResponse->createElement("errors");
                $xmlResponse->appendChild($errors);
                $errors->appendChild($xmlResponse->createElement("buy", "Error: Something went wrong. Can not process the payment."));
            }
        }
    }

    // Send the response to JavaScript
    echo $xmlResponse->saveXML();
}

if (isset($_POST['auctionid']) && isset($_POST['amount']) ) {
    // start the session
    session_start(); 

    bid($_POST['auctionid'], $_SESSION["user_id"],  $_POST['amount']);

} else if (isset($_POST['auctionid']) && !isset($_POST['amount'])) {
    // start the session
    session_start(); 

    buy($_POST['auctionid'], $_SESSION["user_id"]);
}
else {
    loadData();
}
?>