<?php

// Get the configuration file including php version and running operating system
require("config.php");

function processAuction() {
    $xmlfile = '../data/auctions.xml';

    if (!file_exists($xmlfile)) {
        echo "There are no auctions.";
    } else { 
        $xmlAuctions = new DomDocument;
        $xmlAuctions->preserveWhiteSpace = FALSE;
        $xmlAuctions->formatOutput = true;
        $xmlAuctions->load($xmlfile);

        $auctionList = $xmlAuctions->getElementsByTagName("auction");

        foreach ($auctionList as $auction) {
            $startdate = $auction->childNodes->item(8)->nodeValue;
            $starttime = $auction->childNodes->item(9)->nodeValue;
            $duration = $auction->childNodes->item(10)->nodeValue;
            $expirydatetime = new DateTime($startdate. "T" . $starttime);
            $expirydatetime->add(new DateInterval("PT". $duration . "S"));
            $currentdatetime = new DateTime();

            $status = $auction->childNodes->item(11)->nodeValue;

            $reserveprice = $auction->childNodes->item(6)->nodeValue;

            $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

            $highestbid = 0;

            foreach ($bidList as $bid) {
                $bidAmount =  floatval($bid->childNodes->item(1)->nodeValue);

                if ($bidAmount > $highestbid) {
                    $highestbid = $bidAmount;
                }
            }
            
            if ($status == "in_progress" && $expirydatetime < $currentdatetime ) {
                if (floatval($highestbid) >= floatval($reserveprice)) {
                    $auction->childNodes->item(11)->textContent = "sold";
                } else {
                    $auction->childNodes->item(11)->textContent = "failed";
                }
            }
        }

        $xmlAuctions->save($xmlfile);
    
        echo "The status of all auctions has been updated successfully.";
    }
}

function report() {
    $xmlfile = '../data/auctions.xml';

    if (!file_exists($xmlfile)) { 
        echo "There are no auctions.";
    } else {
        $xmlDoc = new DomDocument;
        $xmlDoc->load($xmlfile);
    
        $xslDoc = new DomDocument;
        $xslDoc->load("../transform/reporttable.xsl");
            
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xslDoc);

        $xmlAuctions = new DomDocument;
        $xmlAuctions->preserveWhiteSpace = FALSE;
        $xmlAuctions->load($xmlfile);

        $auctionList = $xmlAuctions->getElementsByTagName("auction");

        $xmlCustomerFile = '../data/customers.xml';

        $xmlCustomers = new DomDocument;

        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlCustomerFile);

        $customerList = $xmlCustomers->getElementsByTagName("customer");

        $xmlAdminFile = '../data/admin.xml';

        $xmlAdmin = new DomDocument;

        $xmlAdmin->preserveWhiteSpace = FALSE;
        $xmlAdmin->load($xmlAdminFile);

        $admin = $xmlAdmin->getElementsByTagName("admin");

        $revenue = floatval($admin->item(0)->childNodes->item(0)->nodeValue);

        foreach ($auctionList as $auction) {
            $status = $auction->childNodes->item(11)->nodeValue;
          
            if ($status == "failed") {
                $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");

                $refundArray = array();

                foreach ($bidList as $bid) {
                    if ($bid->childNodes->item(0)->nodeValue != "" || $bid->childNodes->item(0)->nodeValue != null) {
                        $refundArray[$bid->childNodes->item(0)->nodeValue] = $bid->childNodes->item(1)->nodeValue;
                    }
                }

                foreach ($customerList as $customer) {
                    if (isset($refundArray[$customer->childNodes->item(0)->nodeValue])) {
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);

                        $balance = $balance + floatval($refundArray[$customer->childNodes->item(0)->nodeValue]);

                        $customer->childNodes->item(5)->textContent = $balance;
                    }
                }

                $revenue = $revenue + floatval($auction->childNodes->item(6)->nodeValue) / 100;
            }


            if ($status == "sold") {
                $bidList = $auction->childNodes->item(12)->getElementsByTagName("bid");
                
                $highestbidderid = null;
                $highestbid = 0;

                foreach ($bidList as $bid) {
                    $bidAmount =  floatval($bid->childNodes->item(1)->nodeValue);

                    if ($bidAmount > $highestbid) {
                        $highestbid = $bidAmount;
                        if (!empty($bid->childNodes->item(0)->nodeValue)) {
                            $highestbidderid = $bid->childNodes->item(0)->nodeValue;
                        }
                    }
                }

                $refundArray = array();

                foreach ($bidList as $bid) {
                    if ($bid->childNodes->item(0)->nodeValue != "" || $bid->childNodes->item(0)->nodeValue != null) {
                        if (intval($bid->childNodes->item(0)->nodeValue) != intval($highestbidderid)) {
                            $refundArray[$bid->childNodes->item(0)->nodeValue] = $bid->childNodes->item(1)->nodeValue;
                        }
                    }
                }

                foreach ($customerList as $customer) {
                    if (isset($refundArray[$customer->childNodes->item(0)->nodeValue])) {
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);

                        $balance = $balance + floatval($refundArray[$customer->childNodes->item(0)->nodeValue]);

                        $customer->childNodes->item(5)->textContent = $balance;
                    }

                    if (intval($customer->childNodes->item(0)->nodeValue) == intval($auction->childNodes->item(1)->nodeValue)) {
                        $balance = floatval($customer->childNodes->item(5)->nodeValue);

                        $balance = $balance + $highestbid - $highestbid * 3 / 100 + floatval($auction->childNodes->item(6)->nodeValue) / 100;
                        
                        $customer->childNodes->item(5)->textContent = $balance;
                    }
                }

                $revenue = $revenue + $highestbid * 3 / 100;
            }
        }

        $admin->item(0)->childNodes->item(0)->textContent = $revenue;

        $nodeToRemove = array();

        foreach ($auctionList as $auction) {
            $status = $auction->childNodes->item(11)->nodeValue;
          
            if ($status == "sold" || $status == "failed") {
                array_push($nodeToRemove, $auction);
            }
        }

        if (!empty($nodeToRemove)) {
            foreach ($nodeToRemove as $node) {              
                $xmlAuctions->documentElement->removeChild($node);
            }
        }

        $xmlCustomers->formatOutput = true;
        $xmlCustomers->save($xmlCustomerFile);  

        $xmlAdmin->formatOutput = true;
        $xmlAdmin->save($xmlAdminFile);  

        $xmlAuctions->formatOutput = true;
        $xmlAuctions->save($xmlfile);

        echo $proc->transformToXML($xmlDoc);
    }
}


if (isset($_POST['action'])) {
    if ($_POST['action'] == "process") {
        processAuction();
    } else if ($_POST['action'] == "report") {
        report();
    } else {
        echo "Invalid action.";
    }
}
else {
    echo "Invalid action.";
}
?>
