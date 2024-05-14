<?php
// Start session
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to fetch user details by email
    $sql = "SELECT UserID, PasswordHash FROM users WHERE Email = ?";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    // Execute SQL statement
    $stmt->execute();

    // Store result
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows == 1) {
        // Bind result variables
        $stmt->bind_result($user_id, $hashed_password);

        // Fetch value
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, retrieve the farmID associated with the user

            // Set farmID in the session
            $_SESSION["user_id"] = $user_id;

            // Log user login activity
            $login_time = date("Y-m-d H:i:s");
            $log_message = "User logged in at ";
            $log_sql = "INSERT INTO activity_log (user_id, activity_message, timestamp) VALUES (?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iss", $user_id, $log_message, $login_time);
            $log_stmt->execute();
            $log_stmt->close();

            // Redirect to dashboard or any other page
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle invalid password
            echo "<script>alert('Invalid email or password');</script>";
            echo "<script>window.location.href='signin.html';</script>";
        }
    } else {
        // Handle user not found
        echo "<script>alert('Invalid email or password');</script>";
        echo "<script>window.location.href='signin.html';</script>";
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
