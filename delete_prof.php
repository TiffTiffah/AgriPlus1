<?php
// Include the database connection file
include 'db_connection.php';

// Check if the user ID is set in the POST request
if (isset($_POST['user_id'])) {
    // Get the user ID from the POST request
    $user_id = $_POST['user_id'];

    // Prepare the SQL statement to delete the user
    $sql = "DELETE FROM users WHERE userID = ?";
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die('Prepare error: ' . htmlspecialchars($conn->error));
    }

    // Bind the user ID parameter
    $stmt->bind_param("i", $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the admin dashboard with a success message
        header("Location: users.php?message=User deleted successfully");
    } else {
        // Redirect to the admin dashboard with an error message
        header("Location: users.php?message=Error deleting user: " . htmlspecialchars($stmt->error));
    }

    // Close the statement
    $stmt->close();
} else {
    // Redirect to the admin dashboard with an error message
    header("Location: users.php?message=No user ID provided");
}

// Close the database connection
$conn->close();
?>

