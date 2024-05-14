<?php
session_start(); // Start the session

// Check if the user is logged in
if(isset($_SESSION['user_id'])) {
    // Log the logout action with a message
    logActivity($_SESSION['user_id'], "User logged out");
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: signin.html");
exit;

// Function to log activity
function logActivity($userID, $activityMessage) {
    // Database connection code
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "agri";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement
    $sql = "INSERT INTO activity_log (user_id, activity_message, timestamp) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    // Bind parameters and execute the statement
    $stmt->bind_param("is", $userID, $activityMessage);
    $stmt->execute();

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
