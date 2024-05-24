<?php

// Include database connection
include 'db_connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $feedback = $_POST['feedback'];
    
    // Insert feedback into the database
    $insertQuery = "INSERT INTO feedback (name, email, feedback_text) VALUES ('$name', '$email', '$feedback')";
    if ($conn->query($insertQuery) === TRUE) {
        echo "<script>alert('Thank you for your feedback!');</script>";

        //redirect to the feedback page
        header("Location: help.php");
    } else {
        echo "Error: " . $insertQuery . "<br>" . $conn->error;
    }
}

?>