<?php

// Get the configuration file including php version and running operating system
require("config.php");

$xmlDoc = new DomDocument;
$xmlDoc->load("../data/auctions.xml");

$xslDoc = new DomDocument;
$xslDoc->load("../transform/biddingitem.xsl");
    
$proc = new XSLTProcessor;
$proc->importStyleSheet($xslDoc);
    
echo $proc->transformToXML($xmlDoc);

?>