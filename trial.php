        <div id="Reports" class="tabcontent">
            <h3>Reports</h3>
            <?php

                // Connect to your database (modify database credentials as needed)
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

            $farm_id = 28;
// Fetch data from the yields table and order by month
$sql = "SELECT cropName, lastHarvestYield, yield_predicted, month 
        FROM yields WHERE farmID = ? ORDER BY month";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Check if the prepare() method failed
if ($stmt === false) {
    echo "Error preparing SQL statement: " . $conn->error;
    $conn->close();
    exit; // Stop execution in case of an error
}

// Bind the farm ID parameter
$stmt->bind_param("i", $farm_id);

// Execute the SQL statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

echo '<div class="table-responsive">';
echo '<h4>Yield Reports</h4>';

// Generate HTML table
$table = '<table border="1">
            <tr>
                <th>Crop Name</th>
                <th>Last Harvest Yield</th>
                <th>Yield Predicted</th>
                <th>Month</th>
            </tr>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $table .= '<tr>';
        $table .= '<td>' . $row['cropName'] . '</td>';
        $table .= '<td>' . $row['lastHarvestYield'] . '</td>';
        $table .= '<td>' . $row['yield_predicted'] . '</td>';
        $table .= '<td>' . $row['month'] . '</td>';
        $table .= '</tr>';
    }
} else {
    $table .= '<tr><td colspan="4">No data available</td></tr>';
}

$table .= '</table>';
echo $table;

echo '</div>'; // Close the table-responsive div

// Close the prepared statement
$stmt->close();

// Close database connection
$conn->close();
?>


        </div>