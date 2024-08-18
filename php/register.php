<?php
// Get the configuration file including php version and running operating system
require("config.php");

// An array to store all errors  
$errorArray = array();

$xmlResponse = new DOMDocument;
// It will format the output in xml format otherwise
// the output will be in a single row
$xmlResponse->formatOutput = true;

// If first name data is set
if (isset($_POST["firstname"])) {
    // If first name data is empty
    if (empty($_POST["firstname"])) {
        $errorArray["firstname"] = "Please enter your first name.";
    } else {
        // Get the user first name 
        $firstname = $_POST["firstname"];

        if (!preg_match("/^[a-zA-Z\s]*$/", $firstname)) {
            $errorArray["firstname"] = "Invalid first name. Can only include characters and spaces.";
        } else if (strlen($firstname) > 35) {
            $errorArray["firstname"] = "Invalid first name. Name should be shorter than 35 characters.";
        }
    }
}

// If last name data is set
if (isset($_POST["lastname"])) {
    // If last name data is empty
    if (empty($_POST["lastname"])) {
        $errorArray["lastname"] = "Please enter your last name.";
    } else {
        // Get the user last name 
        $lastname = $_POST["lastname"];

        if (!preg_match("/^[a-zA-Z\s]*$/", $lastname)) {
            $errorArray["lastname"] = "Invalid last name. Can only include characters and spaces.";
        } else if (strlen($lastname) > 35) {
            $errorArray["lastname"] = "Invalid last name. Name should be shorter than 35 characters.";
        }
    }
}

// If email data is set
if (isset($_POST["email"])) {
    // If email data is empty
    if (empty($_POST["email"])) {
        $errorArray["email"] = "Please enter your email address.";
    } else {
        // Get the user email address 
        $email = strtolower($_POST["email"]);

        if (!preg_match("/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$/", $email)) {
            $errorArray["email"] = "Invalid email.";
        } else if (strlen($email) > 320) {
            $errorArray["email"] = "Invalid email. Email should be shorter than 320 characters.";
        }
    }
}


// If password data is set
if (isset($_POST["password"])) {
    // If password data is empty
    if (empty($_POST["password"])) {
        $errorArray["password"] = "Please enter a password.";
    } else {
        // Get the input password 
        $password = $_POST["password"];

        if (!preg_match("/^(?=.*?[#?!@$%^&*-])/", $password)) {
            $errorArray["password"] = "Invalid password. Password must contain at least 1 special character.";
        } else if (!preg_match("/^(?=.*?[A-Z])/", $password)) {
            $errorArray["password"] = "Invalid password. Password must contain at least 1 upper case letter.";
        } else if (!preg_match("/^(?=.*?[a-z])/", $password)) {
            $errorArray["password"] = "Invalid password. Password must contain at least 1 lower case letter.";
        } else if (!preg_match("/^(?=.*?[0-9])/", $password)) {
            $errorArray["password"] = "Invalid password. Password must contain at least a number.";
        } else if (strlen($password) < 8) {
            $errorArray["password"] = "Invalid password. Password must be more than or equal 8 characters.";
        } 
    }
}


// If confirm password data is set
if (isset($_POST["confirmpassword"])) {
    // If confirm password data is empty
    if (empty($_POST["confirmpassword"])) {
        $errorArray["confirmpassword"] = "Please re-enter the password.";
    } else {
        // Get the input confirm password 
        $confirmpassword = $_POST["confirmpassword"];

        if (!isset($_POST["password"]) || empty($_POST["confirmpassword"])) {
            $errorArray["confirmpassword"] = "Please enter a password first.";
        } else if ($confirmpassword !== $password) {
            $errorArray["confirmpassword"] = "Confirm password must be match with password.";
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

if (empty($errorArray) && isset($email) && isset($password) && isset($confirmpassword) && isset($firstname) && isset($lastname)) {
    $xmlfile = '../data/customers.xml';
    $xmlCustomers = new DomDocument;

    $foundEmail = false;
    
    if (!file_exists($xmlfile)) {
        $customers = $xmlCustomers->createElement('customers');
		$xmlCustomers->appendChild($customers);
    } else {
        $xmlCustomers->preserveWhiteSpace = FALSE;
        $xmlCustomers->load($xmlfile);

        $customerList = $xmlCustomers->getElementsByTagName("customer");

        foreach ($customerList as $customer) {
            if ($email == $customer->childNodes->item(3)->childNodes->item(0)->nodeValue) {
                $foundEmail = true;
            }
        }
    }

    if ($foundEmail) {
        $errors = $xmlResponse->createElement("errors");
        $xmlResponse->appendChild($errors);
        $errors->appendChild($xmlResponse->createElement("email", "The email already belongs to an account."));
    } else {
        $newID = $xmlCustomers->getElementsByTagName("customer")->count();

        //create a customer node under customers node
        $customers = $xmlCustomers->getElementsByTagName('customers')->item(0);
        $customer = $xmlCustomers->createElement('customer');
        $customers->appendChild($customer);

        // create a id node 
        $idNode = $xmlCustomers->createElement('id');
        $customer->appendChild($idNode);
        $idValue = $xmlCustomers->createTextNode($newID);
        $idNode->appendChild($idValue);

        // create a first name node 
        $firstnameNode = $xmlCustomers->createElement('firstname');
        $customer->appendChild($firstnameNode);
        $firstnameValue = $xmlCustomers->createTextNode($firstname);
        $firstnameNode->appendChild($firstnameValue);

        // create a last name node 
        $lastnameNode = $xmlCustomers->createElement('lastname');
        $customer->appendChild($lastnameNode);
        $lastnameValue = $xmlCustomers->createTextNode($lastname);
        $lastnameNode->appendChild($lastnameValue);

        // create a email node 
        $emailNode = $xmlCustomers->createElement('email');
        $customer->appendChild($emailNode);
        $emailValue = $xmlCustomers->createTextNode($email);
        $emailNode->appendChild($emailValue);

        // create a password node 
        $passwordNode = $xmlCustomers->createElement('password');
        $customer->appendChild($passwordNode);
        $passwordValue = $xmlCustomers->createTextNode(hash("sha256", $password));
        $passwordNode->appendChild($passwordValue);

        // create a balence node 
        $balanceNode = $xmlCustomers->createElement('balance');
        $customer->appendChild($balanceNode);
        $balanceValue = $xmlCustomers->createTextNode(999);
        $balanceNode->appendChild($balanceValue);

        //save the xml file
        $xmlCustomers->formatOutput = true;
        $xmlCustomers->save($xmlfile);  

        // send redirect
        $action = $xmlResponse->createElement("action");
        $xmlResponse->appendChild($action);
        $action->appendChild($xmlResponse->createTextNode("redirect"));
            
        // Prepare email message
        $to = $email;
        $subject = 'Welcome to ShopOnline!';
        $message = "Dear " . $firstname . ", welcome to use ShopOnline! Your customer id is " . $newID . " and the password is " . $password;
        $headers = 'From: registration@shoponline.com.au' . "\r\n";

        mail($to, $subject, $message, $headers);

        // // start the session
        session_start(); 
        $_SESSION["user_id"] = $newID;
    }
}

echo $xmlResponse->saveXML();

?>
