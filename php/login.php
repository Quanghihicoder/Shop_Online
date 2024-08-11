<?php
// An array to store all errors  
$errorArray = array();

// // start the session
session_start();

$xmlResponse = new DOMDocument;
// It will format the output in xml format otherwise
// the output will be in a single row
$xmlResponse->formatOutput = true;

// // Check if the user logged in

// If email data is set
if (isset($_POST["email"])) {
    // If email data is empty
    if (empty($_POST["email"])) {
        $errorArray["email"] = "Please enter your email address.";
    } else {
        // Get the user email address 
        $email = strtolower($_POST["email"]);
    }
}


// If password data is set
if (isset($_POST["pass"])) {
    // If password data is empty
    if (empty($_POST["pass"])) {
        $errorArray["pass"] = "Please enter a password.";
    } else {
        // Get the input password 
        $pass = $_POST["pass"];
    }
}

if (!empty($errorArray)) {
    $errors = $xmlResponse->createElement("errors");
    $xmlResponse->appendChild($errors);

    foreach ($errorArray as $x => $y) {
        $errors->appendChild($xmlResponse->createElement($x, $y));
    }
}

if (empty($errorArray) && isset($email) && isset($pass)) {
    $xmlfile = '../data/customers.xml';
    $xmlCustomers = new DomDocument;

    if (!file_exists($xmlfile)) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
        $errors->appendChild($xmlResponse->createElement("email", "Your email is not registered."));
    } else {
        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlfile);

        $customerList = $xmlCustomers->getElementsByTagName("customer");

        $foundEmail = false;

        foreach ($customerList as $customer) {
            if ($email == $customer->childNodes->item(3)->childNodes->item(0)->nodeValue) {
                $foundEmail = true;
                if ($pass == $customer->childNodes->item(4)->childNodes->item(0)->nodeValue) {
                    $action = $xmlResponse->createElement("action");
                    $xmlResponse->appendChild($action);
                    $action->appendChild($xmlResponse->createTextNode("redirect"));

                    $_SESSION["user_id"] = $customer->childNodes->item(0)->childNodes->item(0)->nodeValue;
                } else {
                    $errors = $xmlResponse->createElement("errors");
                    $xmlResponse->appendChild($errors);
                    $errors->appendChild($xmlResponse->createElement("pass", "Your password is incorrect. Please try again."));
                }
            }
        }

        if ($foundEmail == false) {
            $errors = $xmlResponse->createElement("errors");
            $xmlResponse->appendChild($errors);
            $errors->appendChild($xmlResponse->createElement("email", "Your email is not registered."));
        }
    }
}


echo $xmlResponse->saveXML();
