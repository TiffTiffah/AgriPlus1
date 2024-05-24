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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve form data
    $crop_name = test_input($_POST["crop-name"]);
    $planting_date = test_input($_POST["planting-date"]);
    $expected_harvest_date = test_input($_POST["harvest-date"]);
    $last_harvest_yield = test_input($_POST["last_yield"]);
    $watering_needs = test_input($_POST["watering-needs"]);
    $cultivated_area = test_input($_POST["cultivated_area"]);



// Prepare SQL statement to insert crop data
$sql_insert_crop = "INSERT INTO crops (FarmID, CropName, PlantingDate, ExpectedHarvestDate, LastHarvestYield, WateringNeeds, CultivatedArea) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insert_crop = $conn->prepare($sql_insert_crop);

if ($stmt_insert_crop === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt_insert_crop->bind_param("isssdsd", $farm_id, $crop_name, $planting_date, $expected_harvest_date, $last_harvest_yield, $watering_needs, $cultivated_area);

// Execute SQL statement
if ($stmt_insert_crop->execute()) {
    // Crop added successfully
    echo "<script>alert('Crop data added successfully.');</script>";

    // Calculate the yield in kg/acre (assuming each bag is 90 kg)
    $yields = $last_harvest_yield * 90;


        //Fetch cropID associated with the farmID
        $sql_fetch_crop_id = "SELECT CropID FROM crops WHERE farmID = ?";
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
    
        // Fetch FarmID
        $row_fetch_crop_id = $result_fetch_crop_id->fetch_assoc();
        $crop_id = $row_fetch_crop_id["CropID"];

               // Echo FarmID into JavaScript variable
       echo "<script>";
       echo "const cropID = " . $crop_id . ";";
       echo "</script>";
    
        // Close statement
        $stmt_fetch_crop_id->close();

  

    // Redirect to crops.html
    header("Location: crops.php");
} else {
    // Error inserting crop data
    echo "Error: " . $stmt_insert_crop->error;
}

// Close statement and connection
$stmt_insert_crop->close();
$conn->close();

$output = shell_exec("python prediction.py $farm_id");
 
}

// Prepare SQL statement to fetch crop names from the database
$sql = "SELECT CropName FROM crops WHERE FarmID = $farm_id";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching crops: " . $conn->error);
}

// Check if the request is a POST request and if 'crop-type' parameter is set
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crop-type'])) {
    // Retrieve crop name from POST data
    $selectedCrop = test_input($_POST['crop-type']);

    // Connect to the database (adjust database credentials as needed)
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        // Handle database connection error
        echo json_encode(array('error' => 'Database connection failed'));
        exit();
    }

    // Prepare and execute SQL query to fetch crop details
    $stmt = $conn->prepare("SELECT * FROM crops WHERE CropName = ? AND FarmID = $farm_id");
    $stmt->bind_param("s", $selectedCrop);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();

        // Store the fetched data in an array
        $cropDetails = array(
            'cropID' => $row['CropID'], // Add the cropID to the response
            'crop_name' => $row['CropName'],
            'cultivated_area' => $row['CultivatedArea'],
            'last_harvest_yield' => $row['LastHarvestYield'] * 90,
            'grow_stage' => $row['GrowthStage'],
            'water_needs' => $row['WateringNeeds'],
            'health_status' => $row['HealthStatus'],
            'last_harvest' => $row['LastHarvestYield']

        );

        

        // Send JSON response with the fetched data
        echo json_encode($cropDetails);
    } else {
        // No data found for the selected crop
        echo json_encode(array('error' => 'No data found for the selected crop'));
    }


    // Close statement and database connection
    $stmt->close();
    $conn->close();
    exit(); // Exit to prevent further execution
}




// Close connection
$conn->close();





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
    <link rel="stylesheet" href="crops.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script src="script.js"></script>
    <title>AgriPlus | Crops</title>
</head>
<body>
       <div class="dash-container">
        <!-- ----------------------------sidebar-------------------------------- -->
        <div class="side-bar">
            <div class="logo">
                <img src="images/logo (1).png" alt="logo">
                <h3>AGRI</h3><span>PLUS</span>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php" ><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php" class="active"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php"><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="help.php"><i class='bx bx-help-circle' ></i></i>Help</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
        <!-- ----------------------------main content-------------------------------- -->
        <div class="main-content">
                         
            <div class="c-top">
            <div class="form-group">
    <select id="crop-type" name="crop-type" required>
        <option value="">Select Crop</option>
        <?php

        // Check if query is successful and if there are any rows returned
        if ($result && $result->num_rows > 0) {
            // Fetch and display each crop name as an option in the dropdown
            while ($row = $result->fetch_assoc()) {
                $crop_name = $row["CropName"];
                echo "<option value='$crop_name'>$crop_name</option>";
            }
        } else {
            echo "<option value=''>No crops found</option>";
        }

        // Close the database connection
        $conn->close();
        ?>
    </select>
</div>


                <!-- Add this div for the modal -->
<div id="modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add New Crop</h2>
      <form id="crop-form" method="POST" action="crops.php">
        <label for="crop-name">Crop Name:</label>
        <input type="text" id="crop-name" name="crop-name" required><br><br>
        
        <label for="planting-date">Planting Date:</label>
        <input type="date" id="planting-date" name="planting-date" required><br><br>
        
        <label for="harvest-date">Expected Harvest Date:</label>
        <input type="date" id="harvest-date" name="harvest-date" required><br><br>

        <label for="last_yield">Last Harvest Yield (in bags):</label>
        <input type="number" id="last_yield" name="last_yield"><br><br>

        <label for="cultivated_area">Cultivated Area (in acres):</label>
        <input type="number" id="cultivated_area" step="0.001" name="cultivated_area"><br><br>
              
        <label for="watering-needs">Watering Needs:</label>
        <input type="text" id="watering-needs" name="watering-needs"><br><br>

        
        <button type="submit" name="submit">Add Crop</button>
      </form>
    </div>
  </div>
  

                <div class="add-crop-btn">
                    <button><i class='bx bx-plus'></i>Add Crop</button>
                </div>
            </div>

            <div class="crop-dets" id="crop-dets">
                <div class="card">
                    <div class="icon">
                        <i class="fa-solid fa-spa"></i>
                    </div>
                    
                    <div class="det">
                    <span>Crop Name</span>
                    <h3 id="crop_name"></h3>
                    </div>
                </div>
                <div class="card">
                    <div class="icon">
                        <i class="fa-solid fa-crop-simple"></i>
                    </div>
                    <div class="det">
                    <span>Size</span>
                    <h3 id="crop_size"></h3>
                    </div>
                </div>
                <div class="card">
                    <div class="icon">
                        <i class="fa-solid fa-sack-xmark"></i>
                    </div>
                    <div class="det">
                    <span>Last Production</span>
                    <h3 id="last_production"></h3>
                    </div>
                </div>
            </div>


            <div class="mid-space">
                <div class="crop-details">
                    <h4>Crop details</h4>
                    <div class="card">
                        <div class="icon">
                            <i class="fa-solid fa-seedling"></i>
                        </div>
                        <div class="dets">
                            <span>Growth Stage</span>
                            <h4 id="grow-stage"></h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <div class="dets">
                        <span>Plant Health</span>
                        <h4 id="plant-health"></h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <i class="fa-solid fa-droplet"></i>
                        </div>
                        <div class="dets">
                            <span>Watering Needs</span>
                        <h4 id="water-needs"></h4>
                        </div>

                    </div>

                </div>
                <div class="stats">
                    <h4>Yield Statistics</h4>
                    <span>Compared with last harvest</span>
                    <div class="stat-sects">
                        <div class="chart">
                            <div id="chart">
                            </div>
                        </div>
                        <div class="statistics">
                            <div class="stat">
                                <span>Last Harvest Yield</span>
                                <h3 id="last_harvest"></h3>
                            </div>
                            <div class="stat">
                                <span>Expected Yield</span>
                                <h3 id="expected-yield"></h3>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>

            <div class="down-sect">
                <h4>Expected Yield Details</h4>

                <canvas id="barChart" width="1000" height="180"></canvas>

            </div>

        
           

        </div>

    </div>

<script>
document.getElementById('crop-type').addEventListener('change', function() {
    var selectedCrop = this.value;
    var farmID = <?php echo $farm_id; ?>;
    var cropID;
    
    if (selectedCrop) {
        // Now, make AJAX request to fetch crop details and cropID
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    
                    // Parse the JSON response
            var cropDetails = JSON.parse(xhr.responseText);

            console.log('Crop details:', cropDetails);

            // Extract cropID
            var cropID = cropDetails.cropID; 

                    
                    // Update specific elements with fetched crop details
                    document.getElementById('crop_name').textContent = cropDetails.crop_name;
                    document.getElementById('crop_size').textContent = cropDetails.cultivated_area + ' acres';
                    document.getElementById('last_production').textContent = cropDetails.last_harvest_yield + ' kgs';
                    document.getElementById('grow-stage').textContent = cropDetails.grow_stage;
                    document.getElementById('plant-health').textContent = cropDetails.health_status;
                    document.getElementById('water-needs').textContent = cropDetails.water_needs;
                    document.getElementById('last_harvest').textContent = cropDetails.last_harvest + ' bags';

                    var cropID = cropDetails.cropID; 
                    
                    // Now, make another AJAX request to fetch the most recent expected yield
                    var xhrExpectedYield = new XMLHttpRequest();
                    xhrExpectedYield.onreadystatechange = function() {
                        if (xhrExpectedYield.readyState === XMLHttpRequest.DONE) {
                            if (xhrExpectedYield.status === 200) {
                                // Parse the JSON response
                                var response = JSON.parse(xhrExpectedYield.responseText);
                                
                                // Update the corresponding <div> with the most recent expected yield
                                document.getElementById('expected-yield').textContent = response.expectedYield + ' bags';
                                
                                // Calculate percentage increase
                                var expectedYield = response.expectedYield;
                                var lastHarvest = cropDetails.last_harvest;
                                var difference = expectedYield - lastHarvest;
                                var percentageIncrease = ((difference / lastHarvest) * 100).toFixed(2); // Round to two decimal places
                                
                                

                                
                                var data = {
    datasets: [{
        data: [percentageIncrease], // Assuming percentageIncrease is the value you want to display
        backgroundColor: ['#45a049'],
        hoverBackgroundColor: ['#45a049']
    }],
    labels: [percentageIncrease > 0 ? "Increase" : "Decrease"] // Dynamically set the label based on percentageIncrease
};

var options = {
    chart: {
        height: 200,
        type: "radialBar"
    },
    series: [percentageIncrease],
    colors: ["#45a049"],
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 10,
                size: "70%"
            },
            dataLabels: {
                showOn: "always",
                name: {
                    offsetY: -10,
                    show: true,
                    color: "#888",
                    fontSize: "12px"
                },
                value: {
                    offsetY: -3,
                    color: "#111",
                    fontSize: "15px",
                    fontWeight: "bold",
                    show: true
                },     
            }
        }
    },
    stroke: {
        lineCap: "round",
    },
    labels: [percentageIncrease > 0 ? "Increase" : "Decrease"] // Dynamically set the label based on percentageIncrease
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();

                            } else {
                                console.error('Failed to fetch most recent expected yield');
                            }
                        }
                    };
                    xhrExpectedYield.open('POST', 'get_expected_yields.php'); // Specify the correct PHP script URL here
                    xhrExpectedYield.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    // Pass farmID and cropID as parameters
                    
                    xhrExpectedYield.send('farmID=' + encodeURIComponent(farmID) + '&cropID=' + encodeURIComponent(cropID)); // Encode parameters as needed
                } else {
                    console.error('Failed to fetch crop details');
                }



                // Now, make AJAX request to fetch the data for the bar chart
        var xhrBarChart = new XMLHttpRequest();
        xhrBarChart.onreadystatechange = function() {
            if (xhrBarChart.readyState === XMLHttpRequest.DONE) {
                if (xhrBarChart.status === 200) {
                    // Parse the JSON response
                    var barChartData = JSON.parse(xhrBarChart.responseText);
                    
                    // Extract months and yields from the fetched data
                    var months = barChartData.map(item => item.month);
                    var yields = barChartData.map(item => item.yield_predicted);


                    console.log(cropID);
                    console.log(farmID);

                    console.log(months);
                    console.log(yields);
                    
                    // Plot the bar chart
                    plotBarChart(months, yields);
                } else {
                    console.error('Failed to fetch data for the bar chart');
                }
            }
        };
        
        // Initialize the request for the bar chart data
        xhrBarChart.open('POST', 'get_yield.php'); // Specify the correct PHP script URL here
        xhrBarChart.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Pass farmID and cropID as parameters
        xhrBarChart.send('farmID=' + encodeURIComponent(farmID) + '&cropID=' + encodeURIComponent(cropID));

            }
        };

        // Initialize the request for the crop details
        xhr.open('POST', 'crops.php'); // Specify the correct PHP script URL here
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // Send the selected crop as the value of 'crop-type' parameter
        xhr.send('crop-type=' + encodeURIComponent(selectedCrop)); // Encode the selectedCrop to handle special characters

         

    } else {
        // Clear crop details if no crop is selected
        document.getElementById('crop_name').textContent = '';
        document.getElementById('crop_size').textContent = '';
        document.getElementById('last_production').textContent = '';
        document.getElementById('grow-stage').textContent = '';
        document.getElementById('plant-health').textContent = '';
        document.getElementById('water-needs').textContent = '';
        document.getElementById('last_harvest').textContent = '';
    }
});






// Function to plot the bar chart
function plotBarChart(months, yields) {
    var ctx = document.getElementById('barChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Expected Yields',
                data: yields, // Assuming yields are provided
                backgroundColor: 'rgb(69, 160, 73,0.3)',
                borderColor: '#45a049',
                borderWidth: 1,
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        suggestedMin: 10000,
                        stepSize: 10000, // Set the step size to 10000
                        callback: function(value, index, values) {
                            return value.toLocaleString(); // Format the tick label
                        }
                    }
                }]
            }
        }
    });
}






        //pop up for adding new crop
const addButton = document.querySelector('.add-crop-btn button');
const modal = document.getElementById('modal');
const closeButton = document.getElementsByClassName('close')[0];
const cropForm = document.getElementById('crop-form');

// Function to open the modal
function openModal() {
  modal.style.display = 'block';
}

// Function to close the modal
function closeModal() {
  modal.style.display = 'none';
}

// Open the modal when the "Add Crop" button is clicked
addButton.addEventListener('click', openModal);

// Close the modal when clicking on the close button
closeButton.addEventListener('click', closeModal);

// Close the modal when clicking outside of it
window.addEventListener('click', (event) => {
  if (event.target === modal) {
    closeModal();
  }
});

// Close the modal when clicking the "Esc" key
window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeModal();

    }
});




    </script>
</body>
</html>
