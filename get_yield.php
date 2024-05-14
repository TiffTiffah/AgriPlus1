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

    // Fetch planting date from crops table
    $fetch_date = "SELECT MONTH(PlantingDate) FROM crops WHERE CropID = ? AND FarmID = ?";
    $stmt_date = $conn->prepare($fetch_date);
    $stmt_date->bind_param("ii", $cropID, $farmID);
    $stmt_date->execute();
    $stmt_date->bind_result($plantingMonth);
    $stmt_date->fetch();
    $stmt_date->close();
    
    // Fetch yields to populate the bar chart of months >= planting month
    $fetch_yield = "SELECT yp.month, yp.yield_predicted 
                    FROM yieldprediction yp
                    JOIN crops c ON yp.CropID = c.CropID AND yp.farmID = c.farmID
                    WHERE yp.CropID = ? AND yp.farmID = ?";
    $stmt_yield = $conn->prepare($fetch_yield);
    $stmt_yield->bind_param("ii", $cropID, $farmID);
    $stmt_yield->execute();
    $result = $stmt_yield->get_result();

    // Convert month names to integers and filter by planting month
    $barChartData = [];
    while ($row = $result->fetch_assoc()) {
        $monthInt = date('n', strtotime($row['month']));
        if ($monthInt >= $plantingMonth) {
            $row['month'] = date('F', mktime(0, 0, 0, $monthInt, 1));
            $barChartData[] = $row;
        }
    }

    // Sort the barChartData array by month name
    usort($barChartData, function($a, $b) {
        return strtotime($a['month']) - strtotime($b['month']);
    });

    // Send JSON response with the fetched data
    echo json_encode($barChartData);

    // Close statement
    $stmt_yield->close();
} else {
    // Parameters not provided
    echo json_encode(array("error" => "FarmID and CropID not provided"));
}

// Close database connection
$conn->close();
?>

