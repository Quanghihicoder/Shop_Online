<?php
// Get the configuration file including php version and running operating system
// require("config.php");

// // start the session
session_start(); 

// Response to JavaScript in XML format
// $xmlResponse = new DOMDocument;
// $xmlResponse->formatOutput=true;

// // // Check if the user logged in
// if (isset($_SESSION["user_id"])) { 
//     $loggedIn = $xmlResponse->createElement("loggedIn");
//     $xmlResponse->appendChild($loggedIn);
//     $loggedIn->appendChild($xmlResponse->createTextNode("true"));
    
// }  else {
//     $loggedIn = $xmlResponse->createElement("loggedIn");
//     $xmlResponse->appendChild($loggedIn);
//     $loggedIn->appendChild($xmlResponse->createTextNode("false"));
// }

// echo $xmlResponse->saveXML();
echo isset($_SESSION["user_id"]);
?>