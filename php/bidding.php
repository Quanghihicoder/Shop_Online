<?php

// Get the configuration file including php version and running operating system
require("config.php");

function loadData() {
    $xmlDoc = new DomDocument;
    $xmlDoc->load("../data/auctions.xml");

    $xslDoc = new DomDocument;
    $xslDoc->load("../transform/biddingitem.xsl");
        
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xslDoc);

    echo $proc->transformToXML($xmlDoc);
}

function bid($auctionid, $bidderid, $amount) {

    // An array to store all errors  
    $errorArray = array();

    $xmlResponse = new DOMDocument;
    // It will format the output in xml format otherwise
    // the output will be in a single row
    $xmlResponse->formatOutput = true;
    
    if (is_numeric($auctionid) && is_numeric($bidderid) && is_numeric($amount)) {
        $auctionid = intval($auctionid);
        $bidderid = intval($bidderid);
        $amount = floatval($amount);
    } else {
        $errorArray["bid"] = "Please enter a number.";
    }

    if (!empty($errorArray)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
    
        foreach ($errorArray as $x => $y) {
            $errors->appendChild($xmlResponse->createElement($x, $y));
        }
    }

    if (empty($errorArray)) {
        $xmlfile = '../data/auctions.xml';
        $xmlAuctions = new DomDocument;

        if (!file_exists($xmlfile)) {
            $errors = $xmlResponse->createElement("errors");
            $xmlResponse->appendChild($errors);
            $errors->appendChild($xmlResponse->createElement("bid", "Invalid auction."));
        } else {
            $xmlAuctions->preserveWhiteSpace = FALSE;
            $xmlAuctions->load($xmlfile);

            $auctionList = $xmlAuctions->getElementsByTagName("auction");

            $foundAuction = false;

            foreach ($auctionList as $auction) {
                if ($auctionid == intval($auction->childNodes->item(0)->nodeValue)) {
                    $foundAuction = true;

                    $sellerid = $auction->childNodes->item(1)->nodeValue;

                    $startdate = $auction->childNodes->item(8)->nodeValue;
                    $starttime = $auction->childNodes->item(9)->nodeValue;
                    $duration = $auction->childNodes->item(10)->nodeValue;
                    $expirydatetime = new DateTime($startdate. "T" . $starttime);
                    $expirydatetime->add(new DateInterval("PT". $duration . "S"));
                    $currentdatetime = new DateTime();

                    $status = $auction->childNodes->item(11)->nodeValue;
                    $lastbidbidderid = $auction->childNodes->item(12)->nodeValue;
                    $lastbid = $auction->childNodes->item(13)->nodeValue;


                    if ($status == "in_progress" 
                        && intval($bidderid) != intval($sellerid)
                        && intval($bidderid) != (empty($lastbidbidderid) ? null : intval($lastbidbidderid)) 
                        && $expirydatetime > $currentdatetime
                        && $amount > floatval($lastbid)
                        ) {
                            
                        $auction->childNodes->item(12)->textContent = $bidderid;
                        $auction->childNodes->item(13)->textContent = $amount;

                        $xmlAuctions->save($xmlfile);

                        $message = $xmlResponse->createElement("message");
                        $xmlResponse->appendChild($message);
                        $message->appendChild($xmlResponse->createTextNode("Thank you! Your bid is recorded in ShopOnline."));
                    } else {
                        $errors = $xmlResponse->createElement("errors");
                        $xmlResponse->appendChild($errors);
                        $errors->appendChild($xmlResponse->createElement("bid", "Sorry, your bid is not valid."));
                    }
                }
            }
    
            if ($foundAuction == false) {
                $errors = $xmlResponse->createElement("errors");
                $xmlResponse->appendChild($errors);
                $errors->appendChild($xmlResponse->createElement("bid", "Invalid auction."));
            }
        }
    }

    echo $xmlResponse->saveXML();
}

function buy($auctionid, $buyerid) {

    // An array to store all errors  
    $errorArray = array();

    $xmlResponse = new DOMDocument;
    // It will format the output in xml format otherwise
    // the output will be in a single row
    $xmlResponse->formatOutput = true;
    
    if (is_numeric($auctionid) && is_numeric($buyerid)) {
        $auctionid = intval($auctionid);
        $buyerid = intval($buyerid);
    } else {
        $errorArray["buy"] = "Error: Something went wrong. Can not process the payment.";
    }

    if (!empty($errorArray)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
    
        foreach ($errorArray as $x => $y) {
            $errors->appendChild($xmlResponse->createElement($x, $y));
        }
    }

    if (empty($errorArray)) {
        $xmlfile = '../data/auctions.xml';
        $xmlAuctions = new DomDocument;

        if (!file_exists($xmlfile)) {
            $errors = $xmlResponse->createElement("errors");
            $xmlResponse->appendChild($errors);
            $errors->appendChild($xmlResponse->createElement("buy", "Error: Something went wrong. Can not process the payment."));
        } else {
            $xmlAuctions->preserveWhiteSpace = FALSE;
            $xmlAuctions->load($xmlfile);

            $auctionList = $xmlAuctions->getElementsByTagName("auction");

            $foundAuction = false;

            foreach ($auctionList as $auction) {
                if ($auctionid == intval($auction->childNodes->item(0)->nodeValue)) {
                    $foundAuction = true;

                    $sellerid = $auction->childNodes->item(1)->nodeValue;

                    $startdate = $auction->childNodes->item(8)->nodeValue;
                    $starttime = $auction->childNodes->item(9)->nodeValue;
                    $duration = $auction->childNodes->item(10)->nodeValue;
                    $expirydatetime = new DateTime($startdate. "T" . $starttime);
                    $expirydatetime->add(new DateInterval("PT". $duration . "S"));
                    $currentdatetime = new DateTime();

                    $status = $auction->childNodes->item(11)->nodeValue;

                    if ($status == "in_progress" 
                        && intval($buyerid) != intval($sellerid)
                        && $expirydatetime > $currentdatetime
                        ) {
                            
                        $auction->childNodes->item(12)->textContent = $buyerid;
                        $auction->childNodes->item(13)->textContent = $auction->childNodes->item(7)->textContent;

                        $auction->childNodes->item(11)->textContent = "sold";

                        $xmlAuctions->save($xmlfile);

                        $message = $xmlResponse->createElement("message");
                        $xmlResponse->appendChild($message);
                        $message->appendChild($xmlResponse->createTextNode("Thank you for purchasing this item."));
                    } else {
                        $errors = $xmlResponse->createElement("errors");
                        $xmlResponse->appendChild($errors);
                        $errors->appendChild($xmlResponse->createElement("buy", "Error: Something went wrong. Can not process the payment."));
                    }
                }
            }
    
            if ($foundAuction == false) {
                $errors = $xmlResponse->createElement("errors");
                $xmlResponse->appendChild($errors);
                $errors->appendChild($xmlResponse->createElement("buy", "Error: Something went wrong. Can not process the payment."));
            }
        }
    }

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