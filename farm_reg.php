<?php
// Start session
session_start();

// Check if user is logged in (user_id session variable is set)
if (!isset($_SESSION["user_id"])) {
    // User is not logged in, redirect to login page or display an error message
    header("Location: signup.html"); // Redirect to login page
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $farm_name = test_input($_POST["farm_name"]);
    $location = test_input($_POST["location"]);
    $farm_size = test_input($_POST["farm_size"]);
    $irrigation_system = test_input($_POST["irrigation_system"]);
    $soil_type = test_input($_POST["soil_type"]);

   // Establish MySQLi connection
$conn = new mysqli("localhost", "root", "", "agri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Define SQL query string
$sql = "INSERT INTO farms (UserID, FarmName, Location, FarmSize, IrrigationSystem, SoilType) VALUES (?, ?, ?, ?, ?, ?)";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

if (!$stmt->bind_param("issdss", $_SESSION["user_id"], $farm_name, $location, $farm_size, $irrigation_system, $soil_type)) {
    die("Error binding parameters: " . $stmt->error);
}

// Execute SQL statement
if ($stmt->execute()) {
    // If farm registration is successful, retrieve the farm ID
    $farm_id = $stmt->insert_id;

    // Store farm ID in session

    $_SESSION["farm_id"] = $farm_id;

    $farmID = $_SESSION["farm_id"];



// Close statement
$stmt->close();

// Close connection
$conn->close();


    //retrieve weather of that location from weather api by calling a python script
    $output = shell_exec("python weather.py $location $farmID");




        // Redirect user to dashboard or any other page you want
echo "<script>alert('Farm registered successfully!')</script>";

echo "<script>window.location = 'dashboard.php'</script>";
        exit();
    } else {
        $errors[] = "Error: " . $stmt->error;
    }

// Close statement
$stmt->close();

// Close connection
$conn->close();

// Redirect user to dashboard
header("Location: dashboard.html");



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
    <link rel="stylesheet" href="farm_reg.css">
    <title>Farm Registration</title>
    
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo (1).png" alt=""></a>
        </div>
        
    </header>

<div class="container">

    <div class="form-container">
        <h2>Hello Farmer!</h2>
        <p>Register your farm to get started with our services.</p>
        <form method="post" action="farm_reg.php">
            <div class="form-group">
                <label for="farm_name">Farm Name:</label>
                <input type="text" id="farm_name" name="farm_name" required>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <select id="location" name="location" required>
                    <option value="">Select Location</option>
                    <option value="Nairobi">Nairobi</option>
                    <option value="Kiambu">Kiambu</option>
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
                    <option value="Trans Nzoia">Trans Nzoia</option>
                    <!-- Add more options as needed -->
                </select>
            </div>

            <div class="form-group">
                <label for="farm_name">Farm Size(in Acres):</label>
                <input type="text" id="farm_size" name="farm_size" required>
            </div>

            <div class="form-group">
                <label for="irrigation_system">Irrigation System:</label>
                <select id="irrigation_system" name="irrigation_system" required>
                    <option value="">Select Irrigation System</option>
                    <option value="Drip">Drip</option>
                    <option value="Sprinkler">Sprinkler</option>
                    <option value="Flood">Flood</option>
                </select>
            </div>

            <div class="form-group">
                <label for="soil_type">Soil Type:</label>
                <select id="soil_type" name="soil_type" required>
                    <option value="">Select Soil Type</option>
                    <option value="Drip">Loam</option>
                    <option value="Sprinkler">Clay</option>
                    <option value="Flood">Sand</option>
                </select>
            </div>

           

            <div class="form-group">
                <input type="submit" value="Register">
            </div>
        </form>
    </div>
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
