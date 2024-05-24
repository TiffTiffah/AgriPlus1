<?php
// Establish database connection (Replace with your database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agri";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch farmID and cropID from the AJAX request
if (isset($_POST['farmID']) && isset($_POST['cropID'])) {
    $farmID = $_POST['farmID'];
    $cropID = $_POST['cropID'];

    //fetch farm_size
    $sql = "SELECT FarmSize FROM farms WHERE FarmID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $farmID);
    $stmt->execute();
    $result = $stmt->get_result();
    $farm = $result->fetch_assoc();
    $farm_size = $farm['FarmSize'];

    
    // Prepare SQL statement to fetch most recent yield prediction from yield_prediction table based on cropID and farmID
    $sql_yield = "SELECT yield_predicted FROM yieldprediction WHERE CropID = ? AND FarmID = ? ORDER BY month DESC LIMIT 1";
    
    // Prepare statement
    $stmt_yield = $conn->prepare($sql_yield);
    
    if ($stmt_yield) {
        // Bind parameters
        $stmt_yield->bind_param("ii", $cropID, $farmID);
        
        // Execute statement
        $stmt_yield->execute();
        
        // Bind result variables
        $stmt_yield->bind_result($expectedYield);
        
                // Fetch result
                if ($stmt_yield->fetch()) {
                    // Convert yield to bags (divide by 90 and ignore decimals)
                    $expectedYieldInBag = floor($expectedYield / 90);

                    //divide by farm size
                    $expectedYieldInBags = intval($expectedYieldInBag / $farm_size);
                    
                    // Return the expected yield in bags as JSON
                    echo json_encode(array("expectedYield" => $expectedYieldInBags));
                } else {
                    // No expected yield found for the selected crop type
                    echo json_encode(array("expectedYield" => "N/A"));
                }
        
        // Close statement
        $stmt_yield->close();
    } else {
        // Error in preparing the SQL statement for yield_prediction table
        echo json_encode(array("error" => "Failed to prepare statement for yield_prediction table"));
    }
} else {
    // Parameters not provided
    echo json_encode(array("error" => "FarmID and CropID not provided"));
}

// Close database connection
$conn->close();
?>