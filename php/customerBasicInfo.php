<?php
// Get the configuration file including php version and running operating system
require("config.php");

// // start the session
session_start(); 

// Response to JavaScript in XML format
$xmlResponse = new DOMDocument;
$xmlResponse->formatOutput=true;

// // Check if the user logged in
if (isset($_SESSION["user_id"])) { 

    $xmlCustomerFile = '../data/customers.xml';

    if (!file_exists($xmlCustomerFile)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
        $errors->appendChild($xmlResponse->createElement("name", "Invalid account"));
    } else {
        // Load the customer data
        $xmlCustomers = new DomDocument;
        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlCustomerFile);

        // Get all data
        $customerList = $xmlCustomers->getElementsByTagName("customer");

        // Send account name and balance
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

// Send the response to JavaScript
echo $xmlResponse->saveXML();

?>