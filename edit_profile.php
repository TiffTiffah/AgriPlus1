<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect to login page if not logged in
  header("Location: login.php");
  exit();
}

// Include database connection
include_once "db_connection.php";


// Check if form is submitted for user update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        // Get form data
  $fullname = trim(htmlspecialchars($_POST['fullname']));
  $email = trim(htmlspecialchars($_POST['email']));
  $username = trim(htmlspecialchars($_POST['username']));
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);

  // Basic validation
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
  } else if (!empty($password) && empty($confirm_password)) {
    $error_message = "Please confirm the new password.";
  } else if (!empty($password) && $password !== $confirm_password) {
    $error_message = "New password and confirm password do not match.";
  }

  // If no errors, proceed with update
  if (!isset($error_message)) {
    // Build the SQL query for updating user data
    $sql = "UPDATE users SET FullName = ?, Email = ?, Username = ?";
    $params = array($fullname, $email, $username);

    // Update password if provided
    if (!empty($password)) {
      // Hash password securely (replace with your hashing function)
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $sql .= ", Password = ?";
      $params[] = $hashed_password;
    }

    // Add WHERE clause to update data for the logged-in user only
    $sql .= " WHERE UserID = ?";
    $params[] = $_SESSION['user_id'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
      // Bind parameters
      $stmt->bind_param(str_repeat('s', count($params)), ...$params);
      // Execute the statement
      if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully.');</script>";
        // Redirect to profile page or display success message
        echo "<script>window.location.replace('dashboard.php');</script>";
      } else {
        $error_message = "Error updating profile: " . $conn->error;
        // Display error message for debugging
        echo "Error: $error_message";
      }
      $stmt->close();
    } else {
      // Handle error if query preparation fails
      $error_message = "Error preparing statement: " . $conn->error;
      // Display error message for debugging
      echo "<script>alert('Error: $error_message');</script>";
    }
  } else {
    // Display any validation errors
    echo "<script>alert('Error: $error_message');</script>";
  }
    } elseif (isset($_POST['delete'])) {
        // Display confirmation popup
        echo "<script>
        var confirmation = confirm('Are you sure you want to delete your account?');
        if (confirmation) {
            // User confirmed deletion, proceed with AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_user.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('User account deleted successfully.');
                    // Redirect to sign-in page or do any other necessary action
                    window.location.href = 'signin.html';
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    alert('Failed to delete user account.');
                }
            };
            xhr.send('delete=1');
        } else {
            // User cancelled deletion, redirect to dashboard.php
            window.location.href = 'dashboard.php';
        }
      </script>";
    } else {
        // Invalid request method
        http_response_code(405);
    }
}
?>
