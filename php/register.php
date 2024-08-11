<?php
// An array to store all errors  
$error = array();

// start the session
// session_start();

// Check if the user logged in
if (isset($_SESSION["user_email"]) && isset($_SESSION["user_role"])) {
    if (intval($_SESSION["user_role"]) == 0) {
        header("location:index.php"); // redirect to index.php
        exit();
    } else {
        $user_role = intval($_SESSION["user_role"]);
    }
}

// If name data is set
if (isset($_POST["name"])) {
    // If name data is empty
    if (empty($_POST["name"])) {
        $error["name"] = "Please enter your name.";
    } else {
        // Get the user name 
        $name = $_POST["name"];

        if (!preg_match("/^[a-zA-Z\s]*$/", $name)) {
            $error["name"] = "Invalid name. Can only include characters and spaces.";
        } else if (strlen($name) > 35) {
            $error["name"] = "Invalid name. Name should be shorter than 35 characters.";
        } else {
            // Clear name input error message
            if (isset($error["name"])) {
                unset($error["name"]);
            }
        }
    }
}

// If email data is set
if (isset($_POST["email"])) {
    // If email data is empty
    if (empty($_POST["email"])) {
        $error["email"] = "Please enter your email address.";
    } else {
        // Get the user email address 
        $email = strtolower($_POST["email"]);

        if (!preg_match("/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$/", $email)) {
            $error["email"] = "Invalid email.";
        } else if (strlen($email) > 320) {
            $error["email"] = "Invalid email. Email should be shorter than 320 characters.";
        } else {
            // Clear email input error message
            if (isset($error["email"])) {
                unset($error["email"]);
            }
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

        if (!preg_match("/^(?=.*?[#?!@$%^&*-])/", $pass)) {
            $error["pass"] = "Invalid password. Password must contain at least 1 special character.";
        } else if (!preg_match("/^(?=.*?[A-Z])/", $pass)) {
            $error["pass"] = "Invalid password. Password must contain at least 1 upper case letter.";
        } else if (!preg_match("/^(?=.*?[a-z])/", $pass)) {
            $error["pass"] = "Invalid password. Password must contain at least 1 lower case letter.";
        } else if (!preg_match("/^(?=.*?[0-9])/", $pass)) {
            $error["pass"] = "Invalid password. Password must contain at least a number.";
        } else if (strlen($pass) < 8) {
            $error["pass"] = "Invalid password. Password must be more than or equal 8 characters.";
        } else {
            // Clear password input error message
            if (isset($error["pass"])) {
                unset($error["pass"]);
            }
        }
    }
}


// If confirm password data is set
if (isset($_POST["repass"])) {
    // If confirm password data is empty
    if (empty($_POST["repass"])) {
        $error["repass"] = "Please re-enter the password.";
    } else {
        // Get the input confirm password 
        $repass = $_POST["repass"];

        if (!isset($_POST["pass"]) || empty($_POST["pass"])) {
            $error["repass"] = "Please enter a password first.";
        } else if ($repass !== $pass) {
            $error["repass"] = "Confirm password must be match with password.";
        } else {
            // Clear confirm password input error message
            if (isset($error["repass"])) {
                unset($error["repass"]);
            }
        }
    }
}

// If phone number data is set
if (isset($_POST["phone"])) {
    // If phone number data is empty
    if (empty($_POST["phone"])) {
        $error["phone"] = "Please enter your phone number";
    } else {
        // Get the input phone number 
        $phone = $_POST["phone"];

        if (!preg_match("/^[0-9]*$/", $phone)) {
            $error["phone"] = "Invalid phone number. Can only include numbers.";
        } else if (!str_starts_with($phone, '0')) {
            $error["phone"] = "Invalid phone number. Phone numbers must start with 0.";
        } else if (strlen($phone) != 10) {
            $error["phone"] = "Invalid phone number. Phone numbers must have exactly 10 digits.";
        } else {
            // Clear phone number input error message
            if (isset($error["phone"])) {
                unset($error["phone"]);
            }
        }
    }
}

if (isset($_POST["admin"])) {

    // Get the input admin 
    $admin = intval($_POST["admin"]);

    if ($user_role == 0 && $admin == 1) {
        $error["admin"] = "Please login as an admin to register for an admin.";
    } else {
        // Clear admin input error message
        if (isset($error["admin"])) {
            unset($error["admin"]);
        }
    }
}



if (empty($error) && isset($email) && isset($pass) && isset($repass) && isset($phone) && isset($name)) {
    require_once("database.php");

    try {
        // create connection to the database
        $connection = new mysqli($host, $user, $password, $dbname);

        // Check if the connection was successful
        if ($connection->connect_errno) {
            echo "Failed to connect to MySQL: " . $connection->connect_error;
            exit();
        }

        if ($versionOK) {
            // Get all existed emails
            $query = "SELECT customer_email FROM customer WHERE customer_email=?;";
            $result = $connection->execute_query($query, [$email]);
        } else {
            $email = $connection->real_escape_string($email);
            $query = "SELECT customer_email FROM customer WHERE customer_email='$email';";
            $result = $connection->query($query);
        }

        // Check if the query executed successfully
        if ($result) {
            // Check if any rows were returned
            if ($result->num_rows > 0) {
                $canRegister = false;
                $error["email"] = "The email already belongs to an account.";
            } else {
                $canRegister = true;
            }
        } else {
            // Display the error message if the query failed
            $error["database"] = "Register Fail. Please check the database connection and reload.";
            $canRegister = false;
        }


        if (isset($canRegister) && $canRegister) {
            if ($versionOK) {
                // Create a new user using the input data 

                if ($user_role != 0) {
                    if (isset($admin) && $admin == 1) {
                        $query = "INSERT INTO customer SET customer_email=?, customer_password=?, customer_name=?, customer_phone=?, customer_is_admin=1;";
                    } else {
                        $query = "INSERT INTO customer SET customer_email=?, customer_password=?, customer_name=?, customer_phone=?, customer_is_admin=0;";
                    }
                } else {
                    $query = "INSERT INTO customer SET customer_email=?, customer_password=?, customer_name=?, customer_phone=?, customer_is_admin=0;";
                }

                $result = $connection->execute_query($query, [$email, hash("sha256", $pass), $name, $phone]);
            } else {
                $email = $connection->real_escape_string($email);
                $pass = $connection->real_escape_string($pass);
                $name = $connection->real_escape_string($name);
                $phone = $connection->real_escape_string($phone);

                if ($user_role != 0) {
                    if (isset($admin) && $admin == 1) {
                    $query = "INSERT INTO customer SET customer_email='$email', customer_password='" . hash("sha256", $pass) . "', customer_name='$name', customer_phone='$phone', customer_is_admin=1;";
                    } else {
                    $query = "INSERT INTO customer SET customer_email='$email', customer_password='" . hash("sha256", $pass) . "', customer_name='$name', customer_phone='$phone', customer_is_admin=0;";
                    }
                } else {
                    $query = "INSERT INTO customer SET customer_email='$email', customer_password='" . hash("sha256", $pass) . "', customer_name='$name', customer_phone='$phone', customer_is_admin=0;";
                }

                $result = $connection->query($query);
            }

            if ($result) {
                unset($_POST);

                if ($user_role == 1 ) {
                    $success_message = "Successfully created new account. Please log out to log in to the new account.";  
                }
                else {
                    header("location:login.php"); // redirect to login.php
                    exit();
                }
            }
        }

        $connection->close();
    } catch (Exception $e) {
        // Most of the cases for dealing with tables may not be created
        $error["database"] = "Register Fail. Please check the database connection and reload.";
    }
}

?>
