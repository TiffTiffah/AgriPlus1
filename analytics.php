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

// Prepare SQL statement to fetch farm ID associated with the user
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


//fetch crop id from crops table
$sql_fetch_crop_id = "SELECT CropID FROM crops WHERE FarmID = ?";
$stmt_fetch_crop_id = $conn->prepare($sql_fetch_crop_id);

if ($stmt_fetch_crop_id === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt_fetch_crop_id->bind_param("i", $farm_id);

// Execute SQL statement
$stmt_fetch_crop_id->execute();

// Get result
$result_fetch_crop_id = $stmt_fetch_crop_id->get_result();

// Fetch CropID
$row_fetch_crop_id = $result_fetch_crop_id->fetch_assoc();

$crop_id = $row_fetch_crop_id["CropID"];

// Close statement
$stmt_fetch_crop_id->close();



// Prepare SQL statement to fetch crop distribution data
// Fetch cropName and cultivatedArea from the crops table, and size from the farms table
$sql = "SELECT crops.cropName, crops.cultivatedArea, farms.farmSize 
        FROM crops 
        INNER JOIN farms ON crops.farmID = farms.farmID 
        WHERE crops.farmID = ?";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind farmID parameter
$stmt->bind_param("i", $farm_id);

// Execute the query
$stmt->execute();

// Store the result
$result = $stmt->get_result();

// Fetch data into an associative array
$cropDistributionData = array();
while ($row = $result->fetch_assoc()) {
    $cropDistribution[] = $row;
    $total_farm_size = $row['farmSize']; // Variable to store the total farm size
    
    while ($row = $result->fetch_assoc()) {
        $cropDistribution[] = $row;
        $total_farm_size = $row['farmSize']; // Assuming 'size' corresponds to farmSize
    }

    
    // Calculate percentage size for each crop
    foreach ($cropDistribution as &$crop) {
        $crop['percentage_size'] = ($crop['cultivatedArea'] / $total_farm_size) * 100;
    }
    // Convert PHP array to JSON string
$cropDistributionData= json_encode($cropDistribution);

// print_r($cropDistributionData);
}



// Define an array of colors
$colors = array(
    'rgb(69, 160, 73)',
    'rgba(41, 104, 52)',
    'rgb(255, 206, 86)',
    'rgb(75, 192, 192)',
    'rgb(153, 102, 255)'
);


// Fetch unique months from the database
$sql_fetch_months = "SELECT DISTINCT month FROM yields WHERE farmID = ?";
$stmt_months = $conn->prepare($sql_fetch_months);
$stmt_months->bind_param("i", $farm_id);
$stmt_months->execute();
$result_months = $stmt_months->get_result();

// Store unique months in an array
$months = [];
while ($row = $result_months->fetch_assoc()) {
    $months[] = $row["month"];
}
$stmt_months->close();

// Sort the months array chronologically
usort($months, function($a, $b) {
    return strtotime($a) - strtotime($b);
});

// Define colors for the line chart
$colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#00ffff'];

// Initialize datasets array
$datasets = [];

// Fetch unique crop IDs for the farm
$sql_fetch_crops = "SELECT DISTINCT CropID, CropName FROM crops WHERE FarmID = ?";
$stmt_fetch_crops = $conn->prepare($sql_fetch_crops);
$stmt_fetch_crops->bind_param("i", $farm_id);
$stmt_fetch_crops->execute();
$result_fetch_crops = $stmt_fetch_crops->get_result();

// Iterate over each crop
while ($row_crop = $result_fetch_crops->fetch_assoc()) {
    $cropID = $row_crop["CropID"];
    $cropName = $row_crop["CropName"];

    // Fetch planting date for this crop
    $fetch_date = "SELECT MONTH(PlantingDate) FROM crops WHERE CropID = ? AND FarmID = ?";
    $stmt_date = $conn->prepare($fetch_date);
    $stmt_date->bind_param("ii", $cropID, $farm_id);
    $stmt_date->execute();
    $stmt_date->bind_result($plantingMonth);
    $stmt_date->fetch();
    $stmt_date->close();

    // Fetch yield data for this crop
    $sql_fetch_yield_crop = "SELECT month, yield_predicted FROM yields WHERE CropID = ? AND FarmID = ?";
    $stmt_yield_crop = $conn->prepare($sql_fetch_yield_crop);
    $stmt_yield_crop->bind_param("ii", $cropID, $farm_id);
    $stmt_yield_crop->execute();
    $result_yield_crop = $stmt_yield_crop->get_result();

    // Initialize array for yield data for this crop
    $cropYieldData = array_fill(0, count($months), null);

    // Store yield data in the corresponding index of $cropYieldData array
    while ($row_yield_crop = $result_yield_crop->fetch_assoc()) {
        $monthInt = array_search($row_yield_crop["month"], $months);
        if ($monthInt !== false && $monthInt + 1 >= $plantingMonth) {
            $cropYieldData[$monthInt] = $row_yield_crop["yield_predicted"];
        }
    }

    $stmt_yield_crop->close();

    // Use modulo operator to ensure cycling through colors if there are more crops than colors defined
    $colorIndex = count($datasets) % count($colors);
    $color = $colors[$colorIndex];

    // Add dataset for this crop
    $datasets[] = array(
        'label' => $cropName,
        'data' => $cropYieldData,
        'borderColor' => $color,
        'borderWidth' => 2,
        'fill' => false
    );
}

// Combine all data into one associative array
$linechartData = array(
    'labels' => $months,
    'datasets' => $datasets
);

// Encode the array as JSON
$linechartDataJSON = json_encode($linechartData);



/// Prepare SQL statement to fetch data based on the most recent month for each crop
$sql_fetch_yield_comparison = "SELECT yc.cropName, y.lastHarvestYield, y.yield_predicted 
FROM yields y
INNER JOIN (
    SELECT cropName, MAX(month) AS max_month
    FROM yields
    WHERE farmID = ?
    GROUP BY cropName
) yc ON y.cropName = yc.cropName AND y.month = yc.max_month
WHERE y.farmID = ?";
$stmt_yield_comparison = $conn->prepare($sql_fetch_yield_comparison);

// Check if the prepare method returned false
if ($stmt_yield_comparison === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt_yield_comparison->bind_param("ii", $farm_id, $farm_id);

// Execute the query
if (!$stmt_yield_comparison->execute()) {
    die("Error executing SQL statement: " . $stmt_yield_comparison->error);
}

// Get the result
$result_yield_comparison = $stmt_yield_comparison->get_result();

// Fetch data into arrays
$expectedYields = [];
$lastHarvestYields = [];
$cropNames = [];
while ($row = $result_yield_comparison->fetch_assoc()) {
    $cropNames[] = $row["cropName"];
    $lastHarvestYields[] = $row["lastHarvestYield"] * 90;
    $expectedYields[] = $row["yield_predicted"];
}

// Close the statement
$stmt_yield_comparison->close();

// Combine data into datasets
$datasets = array(
    array(
        'label' => 'Last Harvest Yield',
        'data' => $lastHarvestYields,
        'backgroundColor' => 'rgb(69, 160, 73,0.5)',
        'borderColor' => 'rgb(69, 160, 73)',
        'borderWidth' => 1
    ),
    array(
        'label' => 'Expected Yield',
        'data' => $expectedYields,
        'backgroundColor' => 'rgba(41, 104, 52,0.7)',
        'borderColor' => 'rgba(41, 104, 52)',
        'borderWidth' => 1
    )
    
);

// Encode the array as JSON
$barchartDataJSON = json_encode(array(
    'labels' => $cropNames,
    'datasets' => $datasets
));

// Close the database connection
$conn->close();

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="analytics.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script src="script.js"></script>
    <title>Dashboard</title>
</head>
<body>
       <div class="dash-container">
        <!-- ----------------------------sidebar-------------------------------- -->
        <div class="side-bar">
            <div class="logo">
                <img src="images/logo.png" alt="logo">
                <h3>AGRI</h3><span>PLUS</span>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php" ><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php"  class="active"><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
        <!-- ----------------------------main content-------------------------------- -->
        <div class="main-content">

            <div class="top">
                <div class="crop-dist">
                    <h4>Crops Distribution</h4>
                    <canvas id="cropDistributionChart" width="250" height="250"></canvas>
                </div>
                <div class="line-chart">
                    <h4>Line Chart for Expected Yields of All Crops</h4>
                    <canvas id="cropChart" width="400" height="150"></canvas>
                </div>

            </div>
            <div class="down">

                <div class="bar-chart">
                    <h4>Bar Chart for Last Years Yield Compared to Expected Yield</h4>
                    <canvas id="yieldComparisonChart" width="800" height="200"></canvas>
                </div>

            </div>


        </div>

<script>

// Use PHP to echo the JSON data directly into the JavaScript code
const cropDistributionData = <?php echo $cropDistributionData; ?>;

// Define labels and data using the fetched data
const labels = cropDistributionData.map(crop => crop.cropName);
const data = cropDistributionData.map(crop => crop.percentage_size);

// Define the chart data using the fetched data
const chartData = {
    labels: labels,
    datasets: [{
        data: data,
        backgroundColor: [
            'rgb(69, 160, 73,0.5)',
            'rgb(59, 200, 50,0.5)',
            'rgb(31, 143, 59,0.5)',
            'rgba(41, 104, 52,0.5)',
            'rgb(227, 246, 226)'
        ],
        borderColor: [
            'rgb(69, 160, 73)',
            'rgb(59, 255, 56)',
            'rgb(31, 143, 59)',
            'rgb(69, 160, 73)',
            'rgb(227, 246, 226)'
        ],
        borderWidth: 1 // Set border width to 1 for a thin line
    }]
};

// Get the canvas element
const ctx = document.getElementById('cropDistributionChart').getContext('2d');

// Create the Donut chart
const cropDistributionChart = new Chart(ctx, {
    type: 'doughnut',
    data: chartData, // Pass chartData instead of cropDistributionData
    options: {
        responsive: false, // Ensure the chart is not responsive
        cutout: 60, // Set the cutout percentage to create a thin donut
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                align: 'start', // Align text to the left
                labels: {
                    padding: 10, // Add padding to the legend
                    boxWidth: 10, // Reduce the width of the legend color box
                    font: {
                        size: 12 // Adjust font size
                    }
                }
            }
        },
        title: {
            display: true,
            text: 'Crop Distribution'
        }
    }
});


//line chart
var ctx2 = document.getElementById('cropChart').getContext('2d');
var cropChart = new Chart(ctx2, {
    type: 'line',
    data: <?php echo $linechartDataJSON; ?>, // Replace with the JSON data fetched from the database
    options: {
        scales: {
            yAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: 'Yield (Kilograms per Acre)'
                }
            }]
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                align: 'center',
                labels: {
                    padding: 10,
                    font: {
                        size: 12 // Adjust font size
                    }
                }
            }
        },
        title: {
            display: true,
            text: 'Expected Yields of Crops'
        },
        elements: {
            line: {
                tension: 0.4 // Adjust tension for smoothness
            }
        }
    }
});


// Fetch dynamic data from PHP script
const barchartData = <?php echo $barchartDataJSON; ?>;

// Bar chart configuration
const ctx3 = document.getElementById('yieldComparisonChart').getContext('2d');
const yieldComparisonChart = new Chart(ctx3, {
    type: 'bar',
    data: barchartData,
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    stepSize: 20
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Yield (tons per hectare)'
                }
            }],
            xAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: 'Crops'
                }
            }]
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                align: 'center',
                labels: {
                    padding: 10,
                    font: {
                        size: 12
                    }
                }
            }
        },
        title: {
            display: true,
            text: 'Yield Comparison: Expected vs Last Year'
        }
    }
});


</script>
</body>
</html>
