<?php
// Start session$cultivated_area = trim(htmlspecialchars($_POST['cultivated_area']));
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect to login page if not logged in
  header("Location: login.php");
  exit();
}
$user_id = $_SESSION["user_id"];

// Include database connection
include_once "db_connection.php";

    //fetch farm ID associated with the user
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


// Check if form is submitted for user update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        // Get form data
  $crop_id = trim(htmlspecialchars($_POST['crop-id']));
  $cultivated_area = trim(htmlspecialchars($_POST['cultivated_area']));
  $growth_stage = trim(htmlspecialchars($_POST['growth-stage']));
  $watering_needs = trim(htmlspecialchars($_POST['watering-needs']));
  $health_status = trim(htmlspecialchars($_POST['health-status']));
  $cropType = trim(htmlspecialchars($_POST['crop-type']));

    //update the crop details
    $sql = "UPDATE crops SET CultivatedArea = ?, GrowthStage = ?, WateringNeeds = ?, HealthStatus = ? WHERE CropID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssi", $cultivated_area, $growth_stage, $watering_needs, $health_status, $crop_id);

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "<script>alert('Crop details updated successfully.');</script>";

    // Log user login activity
    $login_time = date("Y-m-d H:i:s");
    $log_message = "User edited crop: $cropType";
    $log_sql = "INSERT INTO activity_log (user_id, activity_message, timestamp) VALUES (?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("iss", $user_id, $log_message, $login_time);
    $log_stmt->execute();
    $log_stmt->close();

        // Redirect to dashboard or any other page
        header("Location: dashboard.php");

    } else {
        $error_message = "Error updating crop details: " . $conn->error;
        // Display error message for debugging
        echo "Error: $error_message";
    }
    $stmt->close();

  }
     elseif (isset($_POST['delete'])) {
       $cropType = trim(htmlspecialchars($_POST['crop-type']));
       $crop_id = trim(htmlspecialchars($_POST['crop-id']));
           // Show delete confirmation modal
    echo "<script>
    document.getElementById('confirmModal').style.display = 'block';
    document.getElementById('confirmYes').addEventListener('click', function() {
        document.getElementById('crop-form').submit();
        window.location.href = 'dashboard.php';
    });
    document.getElementById('confirmNo').addEventListener('click', function() {
        window.location.href = 'dashboard.php';
    });
  </script>";
       // Delete the crop
       $sql_delete_crop = "DELETE FROM crops WHERE CropID = ?";
       $stmt_delete_crop = $conn->prepare($sql_delete_crop);

       if ($stmt_delete_crop === false) {
           die("Error preparing SQL statement: " . $conn->error);
       }

       // Bind parameters
       $stmt_delete_crop->bind_param("i", $crop_id);

       // Execute SQL statement
       if ($stmt_delete_crop->execute()) {
           // Crop deleted successfully

           // Log user activity
           $delete_time = date("Y-m-d H:i:s");
           $log_message = "User deleted crop: $cropType ";
           $log_sql = "INSERT INTO activity_log (user_id, activity_message, timestamp) VALUES (?, ?, ?)";
           $log_stmt = $conn->prepare($log_sql);
           $log_stmt->bind_param("iss", $user_id, $log_message, $delete_time);
           $log_stmt->execute();
           $log_stmt->close();

           // Redirect to dashboard or any other page
           header("Location: dashboard.php");
           exit();
       } else {
           // Error occurred while deleting crop
           $error_message = "Error deleting crop: " . $conn->error;
           // Display error message for debugging
           echo "Error: $error_message";
       }
       
       $stmt_delete_crop->close();
   } else {
       // Invalid request method
       http_response_code(405);
   }
}
?>