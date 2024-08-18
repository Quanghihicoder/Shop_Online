<?php

// Get the configuration file including php version and running operating system
require("config.php");

// Return the current revenue of admin user, create file if not have
function getRevenue() {
    $xmlAdminFile = '../data/admin.xml';
    $xmlAdmin = new DomDocument;

    if (!file_exists($xmlAdminFile)) {
        $admin = $xmlAdmin->createElement('admin');
		$xmlAdmin->appendChild($admin);

        $revenue = $xmlAdmin->createElement('revenue');
        $admin->appendChild($revenue);
        $revenueValue = $xmlAdmin->createTextNode(0);
        $revenue->appendChild($revenueValue);

        //save the xml file
        $xmlAdmin->formatOutput = true;
        $xmlAdmin->save($xmlAdminFile);  

        echo "0";
    } else {
        $xmlAdmin->preserveWhiteSpace = FALSE;
        $xmlAdmin->load($xmlAdminFile);

        $admin = $xmlAdmin->getElementsByTagName("admin");
        echo floatval($admin->item(0)->childNodes->item(0)->nodeValue);
    }
}

// Check if the highest bid is greater than reserve price, when the auction deadline is passed.
// If yes -> Sold
// If no -> Failed
function processAuction() {
    $xmlAuctionFile = '../data/auctions.xml';

    if (!file_exists($xmlAuctionFile)) {
        echo "<p>There are no auctions.</p>";
    } else { 
        // Load the auction file
        $xmlAuctions = new DomDocument;
        $xmlAuctions->preserveWhiteSpace = FALSE;
        $xmlAuctions->formatOutput = true;
        $xmlAuctions->load($xmlAuctionFile);

        // Get all data
        $auctionList = $xmlAuctions->getElementsByTagName("auction");

        foreach ($auctionList as $auction) {
            // Calculate all the date
            $startDate = $auction->childNodes->item(8)->nodeValue;
            $startTime = $auction->childNodes->item(9)->nodeValue;
            $duration = $auction->childNodes->item(10)->nodeValue;
            $expiryDateTime = new DateTime($startDate. "T" . $startTime);
            $expiryDateTime->add(new DateInterval("PT". $duration . "S"));
            $currentDateTime = new DateTime();

            // Get the status of this auction
            $status = $auction->childNodes->item(11)->nodeValue;

            // Get the reserve price of this auction
            $reservePrice = $auction->childNodes->item(6)->nodeValue;

            // Get all bids on this auction
            $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

            // Get the highest bid among all bids on this auction
            $highestBid= 0;

            foreach ($bidList as $bid) {
                $bidAmount =  floatval($bid->childNodes->item(1)->nodeValue);

                if ($bidAmount > $highestBid) {
                    $highestBid= $bidAmount;
                }
            }

            // Check if the auction is in_progress and passed the auction deadline
            if ($status == "in_progress" && $expiryDateTime < $currentDateTime ) {
                // Check if the highest bid is greater than the reserve price
                if (floatval($highestBid) >= floatval($reservePrice)) {
                    $auction->childNodes->item(11)->textContent = "sold";
                } else {
                    $auction->childNodes->item(11)->textContent = "failed";
                }
            }
        }
        
        // Save the file
        $xmlAuctions->save($xmlAuctionFile);
    
        // Print message
        echo "<p>The status of all auctions has been updated successfully.</p>";
    }
}

// Calculate revenue
// Refund for unsuccessful bidder
// Pay the seller
function report() {
    $xmlAuctionFile = '../data/auctions.xml';
    $xmlCustomerFile = '../data/customers.xml';
    $xmlAdminFile = '../data/admin.xml';

    if (!file_exists($xmlAuctionFile)) { 
        echo "<p>There are no auctions.</p>";
    } else if (!file_exists($xmlCustomerFile)) {
        echo "<p>There are no customers.</p>";
    } else {
        // Transform the revennue table for display later
        $xmlDoc = new DomDocument;
        $xmlDoc->load($xmlAuctionFile);
    
        $xslDoc = new DomDocument;
        $xslDoc->load("../transform/reporttable.xsl");
            
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xslDoc);

        // Load the auction file
        $xmlAuctions = new DomDocument;
        $xmlAuctions->preserveWhiteSpace = FALSE;
        $xmlAuctions->formatOutput = true;
        $xmlAuctions->load($xmlAuctionFile);

        // Load the customer file
        $xmlCustomers = new DomDocument;
        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->formatOutput = true;
        $xmlCustomers->load($xmlCustomerFile);

        // Load the admin file
        $xmlAdmin = new DomDocument;
        $xmlAdmin->preserveWhiteSpace = FALSE;
        $xmlAdmin->formatOutput = true;
        $xmlAdmin->load($xmlAdminFile);

        // Get all data
        $auctionList = $xmlAuctions->getElementsByTagName("auction");
        $customerList = $xmlCustomers->getElementsByTagName("customer");
        $admin = $xmlAdmin->getElementsByTagName("admin");

        // Get the current revenue of the admin
        $revenue = floatval($admin->item(0)->childNodes->item(0)->nodeValue);

        // Process all sold and failed auction
        foreach ($auctionList as $auction) {
            // Get the status of this auction
            $status = $auction->childNodes->item(11)->nodeValue;
          
            // For failed auction
            if ($status == "failed") { 
                // Get all bids on this auction
                $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

                // Create array for all unsuccessfully bidder, will include the bidder id and the last bid of each bid
                $refundArray = array();

                foreach ($bidList as $bid) {
                    // Check if not the default or init bid amount of the auction (which is the start price) 
                    if ($bid->childNodes->item(0)->nodeValue != "" || $bid->childNodes->item(0)->nodeValue != null) {
                        // Because the auction is failed so all bidders will be refunded
                        // Format: array[bidder id] = last bid amount
                        $refundArray[$bid->childNodes->item(0)->nodeValue] = $bid->childNodes->item(1)->nodeValue;
                    }
                }

                // Check if a customer is in the list of refund for this auction
                foreach ($customerList as $customer) {
                    // Check if the customer id match with any bidder id 
                    if (isset($refundArray[$customer->childNodes->item(0)->nodeValue])) {
                        // Get the customer balance 
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);

                        // Add the refund amount
                        $balance = $balance + floatval($refundArray[$customer->childNodes->item(0)->nodeValue]);

                        // New balance after refund
                        $customer->childNodes->item(5)->textContent = $balance;
                    }
                }

                // For a failed auction, the admin will receive 1% the reserve price from the seller
                $revenue = $revenue + floatval($auction->childNodes->item(6)->nodeValue) / 100;
            }

            // For sold auction
            if ($status == "sold") {
                // Get all bids on this auction
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

                // Create array for all unsuccessfully bidder, will include the bidder id and the last bid of each bid
                $refundArray = array();

                foreach ($bidList as $bid) {
                    // Check if not the default or init bid amount of the auction (which is the start price) 
                    if ($bid->childNodes->item(0)->nodeValue != "" || $bid->childNodes->item(0)->nodeValue != null) {
                        // Because the auction is sold, which means the highest bidder win 
                        // So the winning bidder will not be refunded
                        // Format: array[bidder id] = last bid amount
                        if (intval($bid->childNodes->item(0)->nodeValue) != intval($highestBidderID)) {
                            $refundArray[$bid->childNodes->item(0)->nodeValue] = $bid->childNodes->item(1)->nodeValue;
                        }
                    }
                }

                // Check if a customer is in the list of refund for this auction
                // Send the money to the seller, as this is a success auction
                foreach ($customerList as $customer) {
                    // Check if the customer id match with any bidder id 
                    if (isset($refundArray[$customer->childNodes->item(0)->nodeValue])) {
                        // Get the customer balance 
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);
                        
                        // Add the refund amount
                        $balance = $balance + floatval($refundArray[$customer->childNodes->item(0)->nodeValue]);

                        // New balance after refund
                        $customer->childNodes->item(5)->textContent = $balance;
                    }

                    // Pay the seller of this auction
                    if (intval($customer->childNodes->item(0)->nodeValue) == intval($auction->childNodes->item(1)->nodeValue)) {
                        // Get the customer balance 
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);

                        // Add the pay amount, after tax 
                        // As when the seller post a new listing item, the system takes 1% of reserve price
                        // So now need the refund that, and apply a new tax 3% of the sold price
                        $balance = $balance + $highestBid - $highestBid * 3 / 100 + floatval($auction->childNodes->item(6)->nodeValue) / 100;
                        
                        // New balance after pay
                        $customer->childNodes->item(5)->textContent = $balance;
                    }
                }

                // For a sold auction, the admin will receive 3% the sold price from the seller
                $revenue = $revenue + $highestBid * 3 / 100;
            }
        }

        // Update the new revenue of admin after process all failed and sold auctions
        $admin->item(0)->childNodes->item(0)->textContent = $revenue;

        // Create an array to remove all processed auctions
        $nodeToRemove = array();

        // If an auction is sold or failed, it is processed
        foreach ($auctionList as $auction) {
            $status = $auction->childNodes->item(11)->nodeValue;
          
            if ($status == "sold" || $status == "failed") {
                array_push($nodeToRemove, $auction);
            }
        }

        // If any auctions to remove
        if (!empty($nodeToRemove)) {
            foreach ($nodeToRemove as $node) {              
                $xmlAuctions->documentElement->removeChild($node);
            }
        }

        // Save all new data
        $xmlAuctions->save($xmlAuctionFile);
        $xmlCustomers->save($xmlCustomerFile);  
        $xmlAdmin->save($xmlAdminFile);  

        // Print the report
        echo $proc->transformToXML($xmlDoc);
    }
}


if (isset($_POST['action'])) {
    if ($_POST['action'] == "process_auction") {
        processAuction();
    } else if ($_POST['action'] == "report") {
        report();
    } else if ($_POST['action'] == "get_revenue") {
        getRevenue();
    }  
    else {
        echo "<p>Invalid action.</p>";
    }
}
else {
    echo "<p>Invalid action.</p>";
}
?>
