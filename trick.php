<?php
// Start session
session_start();

// Check if user is logged in (i.e., if the user ID session variable is set)
if (!isset($_SESSION["user_id"])) {
    // User is not logged in, redirect to the sign-in page or display an error message
    header("Location: signin.html"); // Redirect to the sign-in page
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "agri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Get the current date
$current_date = date('l, j F Y');



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


    //get username
    $username = "SELECT Username FROM users WHERE UserID = ?";
    $stmt_username = $conn->prepare($username);
    $stmt_username->bind_param("i", $_SESSION["user_id"]);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $row_username = $result_username->fetch_assoc();
    $username = $row_username["Username"];

// Close the statement
$stmt_username->close();



// Check if any rows are returned
if ($result->num_rows == 1) {
    // Fetch FarmID
    $row = $result->fetch_assoc();
    $farm_id = $row["FarmID"];
       // Echo FarmID into JavaScript variable
       echo "<script>";
       echo "const farm_id = " . $farm_id . ";";
       echo "</script>";

$output = shell_exec("python prediction.py $farm_id");

// Output the result
echo "<pre>$output</pre>";


       
    //fetch month from weather data to get the last inserted month
    $month = "SELECT month FROM weather_data WHERE farmID = ?";
    $stmt_month = $conn->prepare($month);
    $stmt_month->bind_param("i", $farm_id);
    $stmt_month->execute();

    $result_month = $stmt_month->get_result();
    $row_month = $result_month->fetch_assoc();
    $last_month = $row_month["month"];

    // Close the statement
    $stmt_month->close();

    //echo the month to script
    echo "<script>";
    echo "const last_month = '" . $last_month . "';";
    echo "</script>";


    // Now that we have the FarmID, we can fetch farm details from the farms table

    // Prepare SQL statement to fetch farm details
    $sql_farm = "SELECT * FROM farms WHERE FarmID = ?";
    $stmt_farm = $conn->prepare($sql_farm);

    // Check if prepare() succeeded
    if ($stmt_farm === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Bind parameters
    $stmt_farm->bind_param("i", $farm_id);

    // Execute SQL statement
    $stmt_farm->execute();

    // Get result
    $result_farm = $stmt_farm->get_result();




?>
        

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- <script src="script.js"></script> -->
    <title>Dashboard</title>
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
                    <li><a href="dashboard.php" class="active"><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php"><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
        <!-- ----------------------------main content-------------------------------- -->
        <div class="main-content">
            <div class="left-main">
                <div class="top">
                    <h2>Welcome back, <span><?php echo $username ?>!</span></h2><br>
                    <!-- <p>Here's what's happening with your farm today</p>                     -->
                </div>
                <div class="date">
                    <i class='bx bx-calendar'></i>
                    <p><?php echo $current_date ?></p>
                </div>             
<?php
// Check if any rows are returned
if ($result_farm->num_rows > 0) {
    // Output data of each row
     while ($row_farm = $result_farm->fetch_assoc()) {
?> 
                <div class="weather-cards">
<?php
// OpenWeatherMap API key
$api_key = '24e1513960412ddb40d604372e879cf9';

// Location coordinates (latitude and longitude) based on the selected location
$coordinates = array(
    "Kiambu" => array("lat" => 1.1714, "lon" => 36.8356),
    "Nairobi" => array("lat" => -1.286389, "lon" => 36.817223),
    "Mombasa" => array("lat" => -4.0435, "lon" => 39.6682),
    "Kisumu" => array("lat" => -0.1022, "lon" => 34.7617)
);
 // Getting location from farm details
$location = $row_farm['Location'];

//passing location to python script to get weather data
$output = shell_exec("python retrieve_weather.py $location $farm_id");
    
// Get coordinates based on the selected location
$lat = $coordinates[$location]["lat"];
$lon = $coordinates[$location]["lon"];

echo "<script>";
echo "const latitude = " . $coordinates[$location]["lat"] . ";";
echo "const longitude = " . $coordinates[$location]["lon"] . ";";
echo "</script>";

// Construct the URL for the current day
$url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$api_key}";

// Make the API request
$response = file_get_contents($url);
if ($response !== false) {
    // Parse JSON response
    $weather_data = json_decode($response, true);

        // Extract relevant weather information
        $temperature_kelvin = $weather_data['main']['temp'];
        $temperature_celsius = $temperature_kelvin - 273.15; // Conversion from Kelvin to Celsius
        $humidity = $weather_data['main']['humidity'];
        $description = $weather_data['weather'][0]['description'];
        $rainfall_mm = isset($weather_data['rain']['1h']) ? $weather_data['rain']['1h'] : 0; // Get rainfall in the last 1 hour (default to 0 if not available)

        // Estimate sunshine duration (in hours)
        $cloudiness = $weather_data['clouds']['all'] / 100; // Convert cloudiness percentage to fraction
        $sunrise = date('Y-m-d H:i:s', $weather_data['sys']['sunrise']);
        $sunset = date('Y-m-d H:i:s', $weather_data['sys']['sunset']);
        $daylight_hours = (strtotime($sunset) - strtotime($sunrise)) / 3600; // Convert daylight duration to hours
        $sunshine_duration = $daylight_hours * (1 - $cloudiness);


} else {
    echo "Error retrieving weather data";
}
?>
                        <div class="card-temp">
                            <div class="sun-icon">
                                <i class='bx bxs-sun'></i>
                            </div>
                            <div class="title">
                                <h4 class="lbl"><? echo number_format($sunshine_duration, 2); ?> hours</h5>
                                <h4>Sunshine Duration</h4>
                            </div>
                        </div>
                        <div class="card">
                            <div class="temp-icon">
                                <i class="fa-solid fa-thermometer-half"></i>
                            </div>
                            <div class="temp">
                                <h4 class="lbl"><?php echo number_format($temperature_celsius, 2);?>&nbsp;°</h5>
                                <h4>Temperature</h4>
                            </div>
                        </div>
    
                    <div class="card">
                        <div class="rain-icon">
                            <i class="fa-solid fa-cloud-rain"></i>
                        </div>
                        <div class="title">
                            <h4 class="lbl"><?php echo  number_format($rainfall_mm, 2); ?> mm (last hour)</h5>
                            <h4>Rainfall</h4>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="humid-icon">
                            <i class="fa-solid fa-wind"></i>
                        </div>
                        <div class="humid">
                            <h4 class="lbl"><?php echo $humidity; ?>%</h5>
                            <h4>Humidity</h4>
                        </div>
                    </div>
    
                </div>
       
                <div class="down-space">
    
                    <div class="card">
                        <div class="soil">
                            <h4>Soil Conditions</h4>
                            <div class="soil-cards">
                                <div class="property">
                                    <div class="moisture-icon">
                                        <i class="fa-solid fa-droplet"></i>
                                    </div>
                                    <div class="title">
                                        <h5>Moisture level</h5>
                                        <h3>25%</h3>
                                        <h6>Optimal</h6>
                                    </div>
                                </div>
            
                                <div class="property">
                                    <div class="ph-icon">
                                        <i class="fa-solid fa-water"></i>
                                    </div>
                                    <div class="title">
                                        <h5>pH level</h5>
                                        <h3>8</h4>
                                        <h6>Neutral</h6>
                                    </div>
                                </div>
    
                                <div class="property">
                                    <div class="temp-icon">
                                        <i class="fa-solid fa-thermometer-half"></i>
                                    </div>
                                    <div class="title">
                                        <h5>Temperature</h5>
                                        <h3>25°</h3>
                                        <h6>Optimal</h6>
                                    </div>
                                </div>
                            </div>
                    </div>
    
                    </div>
   
                    <div class="card">
                        <div class="right-down">
                            <header>Farm Details</header>

                            <div class="farm-det">


                                <div class="farm-info">
                                    <div class="content">
                                        <h6>Location</h6>
                                        
                                        <h5 class="text-fn"><?php echo $row_farm['Location'];?></h5>
                                    </div>
                                </div>
                                <div class="farm-info">
                                    <div class="content">
                                        <h6>Area</h6>
                                        <h5 class="text-fn"><?php echo $row_farm['FarmSize']." acres";?></h5>
                                    </div>
                                </div>

        
                                <div class="farm-info">
                                    <div class="content">
                                        <h6>Irrigation</h6>
                                        <h5 class="text-fn"><?php echo $row_farm['IrrigationSystem'];?></h5>
                                    </div>
                                </div>
<?php
        }
    } else {
        echo "No farms found for the user.";
    }

    // Close farm statement
    $stmt_farm->close();
} else {
    echo "Farm ID not found for the user.";
}

// Close user statement and connection
$stmt->close();
$conn->close();
?>

                            </div>


                            <div class="summary">
                                <header>Activity Summary</header>
                                <ul>
                                    <li>New crop added! ---- <span class="timestamp">2024-03-19 09:12:34</span></li>
                                    <li>New task added! ---- <span class="timestamp">2024-03-19 10:23:45</span></li>
                                    <li>New task added! ---- <span class="timestamp">2024-03-19 10:23:45</span></li>
                                    <li>User account updated! ---- <span class="timestamp">2024-03-19 12:45:56</span></li>
                                    <!-- Add more summary items as needed -->
                                </ul>
                            </div>
                            

    
                          
                    </div>
    
                    </div>
    
                </div>
            </div>
        

    <div class="right-side">

           <div class="setting">
                    <a href="#" class="notification" id="notificationIcon">
                        <i class='bx bx-bell'></i>
                        <span class="badge">3</span>
                    </a>
                    <div class="dropdown" id="notificationDropdown">
                        <ul class="alerts">
                            <!-- Alerts content goes here -->
                            <li>New notification 1</li>
                            <li>New notification 2</li>
                            <li>New notification 3</li>
                        </ul>
                    </div>
    
<!-- Profile section -->
<div class="profile">
    <h4>Azra</h4>
    <a href="#" id="openModalBtn"><img src="images/banner.png" alt="Profile Picture"></a>
</div>

<!-- Modal -->
<div class="modal" id="profileModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <!-- Tab links -->
        <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'UserProfile')">User Profile</button>
            <button class="tablinks" onclick="openTab(event, 'FarmDetails')">Farm Details</button>
            <button class="tablinks" onclick="openTab(event, 'SoilDetails')">Soil Details</button>
            <button class="tablinks" onclick="openTab(event, 'CropDetails')">Crop Details</button>
            <button class="tablinks" onclick="openTab(event, 'Activity')">Activity Log</button>
            <button class="tablinks" onclick="openTab(event, 'Reports')">Reports</button>
        </div>

        <!-- Tab content -->
        <div id="UserProfile" class="tabcontent">
            <h3>User Profile</h3>
            <form method="post" action="signup.php">
            <label for="fullname">Fullname:</label>
            <input type="text" id="fullname" name="fullname" required><br>
    
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
    
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
    
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
    
            <input type="submit" name="submit" value="Edit Profile">
            <input type="submit" name="submit" value="Delete Profile">

        </form>
        </div>

        <div id="FarmDetails" class="tabcontent">
            <h3>Farm Details</h3>
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
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
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
                <input type="submit" value="Edit Farm">
            </div>
        </form>
        </div>

        <div id="SoilDetails" class="tabcontent">
            <h3>Soil Details</h3>
            <form id="crop-form" method="POST" action="crops.php">
        <label for="crop-name">Crop Name:</label>
        <input type="text" id="crop-name" name="crop-name" required><br><br>
        
        <label for="planting-date">Planting Date:</label>
        <input type="date" id="planting-date" name="planting-date" required><br><br>
        
        <label for="harvest-date">Expected Harvest Date:</label>
        <input type="date" id="harvest-date" name="harvest-date" required><br><br>

        <label for="last_yield">Last Harvest Yield:</label>
        <input type="number" id="last_yield" name="last_yield"><br><br>

        <label for="cultivated_area">Cultivated Area:</label>
        <input type="number" id="cultivated_area" name="cultivated_area"><br><br>
        
        <label for="growth-stage">Growth Stage:</label>
        <input type="text" id="growth-stage" name="growth-stage"><br><br>
              
        <label for="watering-needs">Watering Needs:</label>
        <input type="text" id="watering-needs" name="watering-needs"><br><br>
        
        <label for="health-status">Health Status:</label>
        <input type="text" id="health-status" name="health-status"><br><br>
        
        <button type="submit" name="submit">Edit Soil Data</button>
      </form>
        </div>

        <div id="CropDetails" class="tabcontent">
            <h3>Crop Details</h3>
            <form id="crop-form" method="POST" action="crops.php">
        <label for="crop-name">Crop Name:</label>
        <input type="text" id="crop-name" name="crop-name" required><br><br>
        
        <label for="planting-date">Planting Date:</label>
        <input type="date" id="planting-date" name="planting-date" required><br><br>
        
        <label for="harvest-date">Expected Harvest Date:</label>
        <input type="date" id="harvest-date" name="harvest-date" required><br><br>

        <label for="last_yield">Last Harvest Yield:</label>
        <input type="number" id="last_yield" name="last_yield"><br><br>

        <label for="cultivated_area">Cultivated Area:</label>
        <input type="number" id="cultivated_area" name="cultivated_area"><br><br>
        
        <label for="growth-stage">Growth Stage:</label>
        <input type="text" id="growth-stage" name="growth-stage"><br><br>
              
        <label for="watering-needs">Watering Needs:</label>
        <input type="text" id="watering-needs" name="watering-needs"><br><br>
        
        <label for="health-status">Health Status:</label>
        <input type="text" id="health-status" name="health-status"><br><br>
        
        <button type="submit" name="submit">Edit Crop</button>
        <button type="submit" name="submit">Delete Crop</button>
      </form>
        </div>

        <div id="Activity" class="tabcontent">
            <h3>Activity Log</h3>
            <!-- Crop details content here -->
        </div>

        <div id="Reports" class="tabcontent">
            <h3>Reports</h3>
            <!-- Reports content here -->
        </div>
    </div>
</div>


            </div>
    

        <div class="facts">
            <header>Did you know!</header>
            <p>Did you know that the average farm size in Kenya is 0.5 hectares?</p>
        </div>

        <div class="task-progress">
                      <h4>Tasks Progress</h4>
                      <div class="chart">
                          <div id="chart"></div>
                      </div>
        
        </div>
        
    </div>

        

    </div>

    </div>
    



        <script>

            // Open the modal when the profile picture is clicked
document.getElementById("openModalBtn").addEventListener("click", function() {
    document.getElementById("profileModal").style.display = "block";
});

// Close the modal when the close button is clicked
document.getElementsByClassName("close")[0].addEventListener("click", function() {
    document.getElementById("profileModal").style.display = "none";
});

// Open the default tab
document.getElementById("UserProfile").style.display = "block";

function openTab(evt, tabName) {
    // Get all elements with class="tabcontent" and hide them
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    var tablinks = document.getElementsByClassName("tablinks");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}


document.getElementById("notificationIcon").addEventListener("click", function(event) {
        event.stopPropagation(); // Prevents the click event from bubbling up to document
        var dropdown = document.getElementById("notificationDropdown");
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    });
    
    // Close the dropdown when clicking outside of it
    document.addEventListener("click", function(event) {
        var dropdown = document.getElementById("notificationDropdown");
        if (!event.target.matches('.notification')) {
            dropdown.style.display = "none";
        }
    });

            
// Function to fetch task completion percentage and update the chart
function updateTaskProgressChart() {
    // Send AJAX request to fetch task completion percentage
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'calculate_task_completion.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
          console.log(xhr.responseText);
            // Parse response JSON data
            const completionPercentage = JSON.parse(xhr.responseText);

            // Draw the chart
            const options = {
                chart: {
                    height: 280,
                    type: "radialBar"
                },
                labels: ["Tasks Completed"],
                series: [completionPercentage],
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
                                fontSize: "15px",
                                fontFamily: 'Montserrat'
                            },
                            value: {
                                offsetY: -2,
                                color: "#111",
                                fontSize: "15px",
                                fontWeight: "bold",
                                fontFamily: 'Montserrat',
                                show: true
                            },
                        }
                    }
                },
                stroke: {
                    lineCap: "round",
                },
            };

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        } else {
            console.error('Error:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send();
}

// Call the function to update the chart
updateTaskProgressChart();



// Function to fetch weather data from API and store it in the database

async function fetchAndStoreWeatherData(latitude, longitude, farm_id) {
    try {
        const apiKey = '24e1513960412ddb40d604372e879cf9';
        const apiUrl = `https://pro.openweathermap.org/data/2.5/forecast/climate?lat=${latitude}&lon=${longitude}&units=metric&appid=${apiKey}`;

        // Make the API request
        const response = await fetch(apiUrl);
        const climateData = await response.json();

        // Check if data was successfully retrieved
        if (climateData && climateData.list) {
            // Extract weather parameters for each day
            const temperatureData = climateData.list.map(day => day.temp.day);
            const precipitationData = climateData.list.map(day => day.rain);
            const humidityData = climateData.list.map(day => day.humidity);
            // Add more weather parameters as needed

            // Aggregate data to calculate summary statistics
            const temperatureMean = temperatureData.reduce((acc, val) => acc + val, 0) / temperatureData.length;
            const precipitationTotal =precipitationData.reduce((acc, val) => acc + val, 0);
            const humidityMean = humidityData.reduce((acc, val) => acc + val, 0) / humidityData.length;
            // Calculate summary statistics for other weather parameters

            // Get the name of the current month
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const currentMonth = months[new Date().getMonth() + 1];


            // Check if data for the current month has already been inserted
            if (currentMonth === last_month) {
                console.log("Data for current month already inserted.");
            } else {
                // Print or return the aggregated data with the month
                console.log("Temperature (Mean):", temperatureMean, "°C");
                console.log("Precipitation (Total):", precipitationTotal, "mm");
                console.log("Humidity (Mean):", humidityMean, "%");
                console.log("Month:", currentMonth);
                console.log("Farm ID:", farm_id);
                // Print or return summary statistics for other weather parameters

                // Insert weather data into the database
                await insertWeatherDataIntoDatabase(temperatureMean, humidityMean, precipitationTotal, currentMonth, farm_id);
            }

        } else {
            console.error("Failed to parse climate data.");
        }
    } catch (error) {
        console.error("Error fetching weather data:", error);
    }
}

async function insertWeatherDataIntoDatabase(temperatureMean, humidityMean, precipitationTotal, currentMonth, farm_id) {
    try {
        // Make a POST request to a server-side endpoint for database insertion
        const response = await fetch('weather.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                temperature: temperatureMean,
                humidity: humidityMean,
                rainfall: precipitationTotal,
                month: currentMonth,
                farm_id: farm_id
            })
        });

        // Check if the request was successful
        if (!response.ok) {
            throw new Error('Failed to insert weather data into database');
        }

        // Data successfully inserted into the database
        console.log('Weather data inserted into database successfully');
    } catch (error) {
        console.error('Error inserting weather data into database:', error);
    }
}

// Check if it's the first day of the month
function isFourthDayOfMonth() {
    const today = new Date();
    return today.getDate() === 1;
}

// Check if it's the fourth day of the month and fetch weather data
if (isFourthDayOfMonth()) {
    fetchAndStoreWeatherData(latitude, longitude, farm_id);
}






        </script>
</body>
</html>