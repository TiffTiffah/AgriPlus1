<?php
// Include the database connection file
include 'db_connection.php';

// Initialize variables
$user_id = $fullname = $email = $status = "";
$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $user_id = $_POST['user_id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $status = trim($_POST['status']);

    echo $user_id;

    if (empty($fullname)) {
        $errors[] = "Fullname is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($status)) {
        $errors[] = "Status is required.";
    }

    // Update user information if no errors
    if (empty($errors)) {
        $sql = "UPDATE users SET  status = ? WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $user_id);

        if ($stmt->execute()) {
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Error updating user: " . $conn->error;
        }
    }
} else {
    // Fetch user information to pre-fill the form
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $sql = "SELECT * FROM users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $fullname = $user['fullname'];
            $email = $user['email'];
            $status = $user['status'];

            //alert
            echo "<script>alert('User updated successfully');</script>";

            //redirect to the admin dashboard
            header("Location: admin_dashboard.php");
        } else {
            $errors[] = "User not found.";
        }
    } else {
        $errors[] = "No user ID provided.";
    }
}


$conn->close();
?>