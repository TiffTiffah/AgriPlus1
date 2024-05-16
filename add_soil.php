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

$sql_fetch_farm_id = "SELECT FarmID FROM farms WHERE UserID = ?";
$stmt_fetch_farm_id = $conn->prepare($sql_fetch_farm_id);

if ($stmt_fetch_farm_id === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt_fetch_farm_id->bind_param("i", $_SESSION["user_id"]);

// Execute SQL statement
$stmt_fetch_farm_id->execute();

// Get result
$result_fetch_farm_id = $stmt_fetch_farm_id->get_result();

// Fetch FarmID
$row_fetch_farm_id = $result_fetch_farm_id->fetch_assoc();
$farm_id = $row_fetch_farm_id["FarmID"];

// Close statement
$stmt_fetch_farm_id->close();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {



    // Get soil data from the form
    $soil_temp = $_POST['soil-temperature'];
    $ph_level = $_POST['ph-level'];
    $soil_moisture = $_POST['soil-moisture'];

 // Prepare and bind parameters
$stmt = $conn->prepare("INSERT INTO soil_data (farmID, temperature, ph, moisture, date) VALUES (?, ?, ?, ?, NOW())");

if ($stmt === false) {
    // Error handling if prepare fails
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$bind_result = $stmt->bind_param("idis", $farm_id, $soil_temp, $ph_level, $soil_moisture);

if ($bind_result === false) {
    // Error handling if bind_param fails
    die("Error binding parameters: " . $stmt->error);
}

// Execute the prepared statement
if ($stmt->execute() === TRUE) {
    header("Location: dashboard.php");
} else {
    echo "Error executing SQL statement: " . $stmt->error;
}

// Close statement
$stmt->close();
// Close connection (if necessary, depending on how you established the connection)
$conn->close();

}
?>