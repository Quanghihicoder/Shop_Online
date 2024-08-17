<?php
// Get the configuration file including php version and running operating system
require("config.php");

// // start the session
session_start(); 

$xmlResponse = new DOMDocument;
    
// It will format the output in xml format otherwise
// the output will be in a single row
$xmlResponse->formatOutput=true;

// // Check if the user logged in
if (isset($_SESSION["user_id"])) { 

    $xmlfile = '../data/customers.xml';

    if (!file_exists($xmlfile)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
        $errors->appendChild($xmlResponse->createElement("name", "Invalid account"));
    } else {
        $xmlCustomers = new DomDocument;

        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlfile);

        $customerList = $xmlCustomers->getElementsByTagName("customer");

        foreach ($customerList as $customer) {
            if (intval($_SESSION["user_id"]) == intval($customer->childNodes->item(0)->nodeValue)) {
                $info = $xmlResponse->createElement("info");
                $xmlResponse->appendChild($info);
                $info->appendChild($xmlResponse->createElement("name", $customer->childNodes->item(1)->nodeValue));
                $info->appendChild($xmlResponse->createElement("balance", $customer->childNodes->item(5)->nodeValue));
            }
        }
    }

}  else {
    $errors = $xmlResponse->createElement("errors");
    $xmlResponse->appendChild($errors);
    $errors->appendChild($xmlResponse->createElement("name", "Invalid account"));
}

echo $xmlResponse->saveXML();

?>