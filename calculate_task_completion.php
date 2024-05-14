<?php

session_start();

// Check if user is logged in (assuming you have a user authentication system)
if (!isset($_SESSION["user_id"])) {
    // Redirect to the sign-in page or display an error message
    header("Location: signin.html");
    exit();
}

// Connect to your database (modify database credentials as needed)
$conn = new mysqli("localhost", "root", "", "agri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement to fetch FarmID for the user
$sql = "SELECT FarmID FROM farms WHERE UserID = ?";
$stmt = $conn->prepare($sql);

// Check if prepare() succeeded
if ($stmt === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("i", $_SESSION["user_id"]);

// Execute SQL statement
$stmt->execute();

// Get result
$result = $stmt->get_result();

// Check if any rows are returned
if ($result->num_rows == 1) {
    // Fetch FarmID
    $row = $result->fetch_assoc();
    $farm_id = $row["FarmID"];

    // Get current date
    $currentDate = date('Y-m-d');

    // Query to count completed tasks for the current date
    $sqlCompletedTasks = "SELECT COUNT(*) AS completedTasks FROM tasks WHERE FarmID = ? AND DATE(dueDate) = ? AND status = 'completed'";
    $stmtCompletedTasks = $conn->prepare($sqlCompletedTasks);
    $stmtCompletedTasks->bind_param("is", $farm_id, $currentDate);
    $stmtCompletedTasks->execute();
    $resultCompletedTasks = $stmtCompletedTasks->get_result();
    $rowCompletedTasks = $resultCompletedTasks->fetch_assoc();
    $completedTasks = $rowCompletedTasks['completedTasks'];

    // Query to count total tasks for the current date
    $sqlTotalTasks = "SELECT COUNT(*) AS totalTasks FROM tasks WHERE FarmID = ? AND DATE(dueDate) = ?";
    $stmtTotalTasks = $conn->prepare($sqlTotalTasks);
    $stmtTotalTasks->bind_param("is", $farm_id, $currentDate);
    $stmtTotalTasks->execute();
    $resultTotalTasks = $stmtTotalTasks->get_result();
    $rowTotalTasks = $resultTotalTasks->fetch_assoc();
    $totalTasks = $rowTotalTasks['totalTasks'];

    // Calculate completion percentage
    $completionPercentage = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;

    // Close prepared statements
    $stmtCompletedTasks->close();
    $stmtTotalTasks->close();

    // Output the completion percentage
    echo json_encode($completionPercentage);

} else {
    // No farm found for the user
    echo "Error: Farm not found for user with ID " . $_SESSION["user_id"];
}

// Close database connection
$conn->close();
?>
