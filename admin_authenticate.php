<?php
// Start session
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Define the admin credentials
    $adminUsername = "admin";
    $adminPassword = "agriplus"; // You should hash this password for better security

    // Get the username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if the entered credentials match the admin credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        // Admin authentication successful
        // Set a session variable to indicate that the admin is logged in
        $_SESSION["adminLoggedIn"] = true;

        // Redirect to the admin dashboard or any other desired page
        header("Location: admin-dashboard.php");
        exit();
    } else {
        // Admin authentication failed
        // Redirect back to the login page with an error message
        $_SESSION["loginError"] = "Invalid username or password.";
        header("Location: admin-login.php");
        exit();
    }
} else {
    // If the form is not submitted, redirect back to the login page
    header("Location: admin-login.php");
    exit();
}
?>
