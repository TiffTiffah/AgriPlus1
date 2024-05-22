<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

// Include database connection
include_once "db_connection.php";

// Check if form is submitted for user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Build the SQL query for deleting the user
        $sql = "DELETE FROM users WHERE UserID = ?";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind the parameter
            $stmt->bind_param("i", $_SESSION['user_id']);

            // Execute the statement
            if ($stmt->execute()) {
                // User deleted successfully, destroy the session and redirect to login page
                session_destroy();
                header("Location: signin.php");
                exit();
            } else {
                // Error deleting user
                echo "<script>alert('Error deleting user.');</script>";
            }
        } else {
            // Error preparing SQL statement
            echo "<script>alert('Error preparing SQL statement.');</script>";
        }
    }
}


?>