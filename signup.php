<?php
// Start session
session_start();

//include database 
include 'db_connection.php';


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = test_input($_POST["fullname"]);
    $username = test_input($_POST["username"]);
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirm_password = test_input($_POST["confirm_password"]);

// Validate form data
$validationErrors = array();

// Check if email is empty
if (empty($email)) {
    $validationErrors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Check if email is valid
    $validationErrors[] = "Invalid email format.";
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

    if ($stmt->num_rows > 0) {
        $validationErrors[] = "Email is already taken. Please register with a different email.";
    } else {
        // If no validation errors, proceed with database insertion
        if (empty($validationErrors)) { // Change $errors to $validationErrors
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
                $validationErrors[] = "Error: " . $stmt->error; // Change $errors to $validationErrors
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="signin.css">
</head>
<body>
    
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo (1).png" alt=""></a>
        </div>
        
    </header>

    <div class="container">
        
        <form method="post" action="signup.php">
            <label for="fullname">Fullname:</label>
            <input type="text" id="fullname" pattern="[A-Za-z ]+" title="Please enter only letters for fullname." name="fullname" required><br>
    
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
    
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" title="Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be at least 8 characters long." required><br>
    
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
    
            <input type="submit" name="submit" value="Sign Up">
            <br>
            <?php
// Display validation errors if any
if (!empty($validationErrors)) {
    echo '<div class="error-container">';
    foreach ($validationErrors as $error) {
        echo "<p class='error'>$error</p>";
    }
    echo '</div>';
}
?>
            <h4>Already have an  account? <a href="signin.html">&nbsp; Sign In</a></h4>
        </form>
   
    </div>
</body>
<script>
    window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 80 || document.documentElement.scrollTop > 80) {
        document.querySelector("header").classList.add("scrolled");
    } else {
        document.querySelector("header").classList.remove("scrolled");
    }
}



</script>
</html>