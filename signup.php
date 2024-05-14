<?php
// Start session
session_start();

// Define variables and initialize with empty values
$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = test_input($_POST["fullname"]);
    $username = test_input($_POST["username"]);
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirm_password = test_input($_POST["confirm_password"]);

    // Check if passwords match
    if ($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email is already in use
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement with placeholders
    $sql = "SELECT UserID FROM users WHERE Email = ?";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    // Execute SQL statement
    $stmt->execute();

    // Store result
    $stmt->store_result();

    // Check if email is already taken
    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already taken. Please register with a different email.";
    } else {
        // If no validation errors, proceed with database insertion
        if (empty($errors)) {
            // Perform hash encryption for password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare SQL statement with placeholders
            $sql = "INSERT INTO users (FullName, Username, Email, PasswordHash) VALUES (?, ?, ?, ?)";

            // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fullname, $username, $email, $hashed_password);

            // Execute SQL statement
            if ($stmt->execute()) {
                // Registration successful, retrieve user ID
                $user_id = $stmt->insert_id;

                // Store user ID in session
                $_SESSION["user_id"] = $user_id;

                // Redirect user to farm registration page
                header("Location: farm_reg.php?registration=success");
                exit();
            } else {
                $errors[] = "Error: " . $stmt->error;
            }
        }
    }

    // Close statement
    $stmt->close();

    // Close connection
    $conn->close();
}

// Function to sanitize and validate input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}




?>

