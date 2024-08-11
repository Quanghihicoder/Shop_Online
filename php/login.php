<?php
// define('CabsOnline', TRUE);

// require("config.php");

// An array to store all errors  
$error = array();

// // start the session
// session_start(); 

// // Check if the user logged in
// if (isset($_SESSION["user_email"])) { 
//     header("location:index.php"); // redirect to index.php
//     exit();
// } else {
    // If email data is set
    if (isset($_POST["email"])) {
        // If email data is empty
        if (empty($_POST["email"])) {
            $error["email"] = "Please enter your email address.";
        } else {
            // Get the user email address 
            $email = strtolower($_POST["email"]);

            // Clear email input error message
            if (isset($error["email"])) {
                unset($error["email"]);
            }
        }
    }


    // If password data is set
    if (isset($_POST["pass"])) {
        // If password data is empty
        if (empty($_POST["pass"])) {
            $error["pass"] = "Please enter a password.";
        } else {
            // Get the input password 
            $pass = $_POST["pass"];

            // Clear password input error message
            if (isset($error["pass"])) {
                unset($error["pass"]);
            }
        }
    }

    if (!empty($error)) {
        foreach ($error as $x => $y) {
            echo "$x:$y\n";
        }
    }

//     if (empty($error) && isset($email) && isset($pass)) {
//         require_once ("database.php");

//         try {
//             // create connection to the database
//             $connection = new mysqli($host, $user, $password, $dbname);

//             // Check if the connection was successful
//             if ($connection->connect_errno) {
//                 echo "Failed to connect to MySQL: " . $connection->connect_error;
//                 exit();
//             }

//             // Get all existed emails

//             if ($versionOK) {
//                 $query = "SELECT customer_email, customer_is_admin FROM customer WHERE customer_email=? AND customer_password=?;";
//                 $result = $connection->execute_query($query, [$email, hash("sha256", $pass)]);
//             } else {
//                 $email = $connection -> real_escape_string($email);
//                 $pass = $connection -> real_escape_string($pass);
//                 $query = "SELECT customer_email, customer_is_admin FROM customer WHERE customer_email='$email' AND customer_password='" . hash("sha256", $pass) . "';";
//                 $result = $connection->query($query);
//             } 

//             // Check if the query executed successfully
//             if ($result) {
//                 // Check if any rows were returned
//                 if ($result->num_rows > 0) {
//                     $data = $result->fetch_assoc();
//                     $user_email = $data["customer_email"];
//                     $user_role = $data["customer_is_admin"];
//                 } else {
//                     $error["pass"] = "Your email or password is incorrect. Please try again.";
//                 }
//             } else {
//                 // Display the error message if the query failed
//                 $error["database"] = "Login Fail. Please check the database connection and reload.";
//             }

//             $connection->close();

//             // If successful login with valid email and password
//             if (isset($user_email)) {
//                 session_start(); // start the session
//                 $_SESSION["user_email"] = $user_email; // update the session variable
//                 $_SESSION["user_role"] = $user_role; // update the session variable

//                 if (intval($user_role) == 1) {
//                     header("location:admin.php"); // redirect to admin.php
//                     exit();
//                 } else {
//                     header("location:booking.php"); // redirect to booking.php
//                     exit();
//                 }
//             }

//         } catch (Exception $e) {
//             // Most of the cases for dealing with tables may not be created
//             $error["database"] = "Login Fail. Please check the database connection and reload.";
//         }
//     }
// }

?>